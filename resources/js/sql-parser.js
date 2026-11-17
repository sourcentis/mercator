/**
 * Mercator Query Engine — Parser SQL-like
 * Exposé via window.MercatorQuery.parse() et window.MercatorQuery.dslToSql()
 *
 * Syntaxe supportée :
 *   FROM <Model>
 *   [FIELDS <field>, <relation.field>, ...]
 *   [WHERE <conditions>]
 *   [WITH <relation>, <relation.relation>, ...]
 *   [GROUP BY <field>, ...]            (OUTPUT list uniquement)
 *   [OUTPUT graph|list]
 *   [LIMIT <n>]
 *
 * Conditions :
 *   field = "value"                    égalité
 *   field != "value"                   différent
 *   field >= 3                         comparaison
 *   field LIKE "%val%"                 like
 *   field NOT LIKE "%val%"             not like
 *   field IN (1, 2, 3)                 in
 *   field NOT IN (1, 2)                not in
 *   field IS NULL                      null
 *   field IS NOT NULL                  not null
 *   EXISTS relation                    whereHas
 *   NOT EXISTS relation                whereDoesntHave
 *   EXISTS relation WHERE field = x    whereHas avec condition
 *   cond AND cond                      et
 *   cond OR cond                       ou
 *   NOT cond                           négation
 *   (cond OR cond) AND cond            groupes
 *   relation.field = "value"           filtre sur sous-objet
 */

(function () {
    'use strict';

    // ─────────────────────────────────────────────────────────────
    // Tokenizer
    // ─────────────────────────────────────────────────────────────

    const KEYWORDS = new Set([
        'FROM', 'WHERE', 'AND', 'OR', 'NOT', 'WITH', 'TRAVERSE',
        'OUTPUT', 'LIMIT', 'LIKE', 'IN', 'IS', 'EXISTS',
        'FIELDS', 'SELECT', 'NULL', 'TRUE', 'FALSE',
        'GROUP', 'BY',
    ]);

    function tokenize(input) {
        const tokens = [];
        let i = 0;

        while (i < input.length) {

            // Espaces et sauts de ligne
            if (/\s/.test(input[i])) {
                i++;
                continue;
            }

            // Commentaires --
            if (input[i] === '-' && input[i + 1] === '-') {
                while (i < input.length && input[i] !== '\n') i++;
                continue;
            }

            // Chaînes "..." ou '...'
            if (input[i] === '"' || input[i] === "'") {
                const q = input[i++];
                let s = '';
                while (i < input.length && input[i] !== q) {
                    if (input[i] === '\\') i++;
                    s += input[i++];
                }
                i++;
                tokens.push({type: 'STRING', value: s});
                continue;
            }

            // Nombres (entiers et décimaux, négatifs si contexte correct)
            const prev = tokens[tokens.length - 1];
            const canBeNeg = input[i] === '-'
                && /[0-9]/.test(input[i + 1])
                && (!prev || ['COMMA', 'LPAREN', 'LBRACKET', 'OP'].includes(prev.type));

            if (/[0-9]/.test(input[i]) || canBeNeg) {
                let n = '';
                if (input[i] === '-') n += input[i++];
                while (i < input.length && /[0-9.]/.test(input[i])) n += input[i++];
                tokens.push({type: 'NUMBER', value: parseFloat(n)});
                continue;
            }

            // Opérateurs à 2 caractères
            const two = input.substring(i, i + 2);
            if (['>=', '<=', '!=', '<>'].includes(two)) {
                tokens.push({type: 'OP', value: two === '<>' ? '!=' : two});
                i += 2;
                continue;
            }

            // Opérateurs à 1 caractère
            if ('=<>-'.includes(input[i])) {
                tokens.push({type: 'OP', value: input[i]});
                i++;
                continue;
            }

            // Ponctuation
            const punct = {'(': 'LPAREN', ')': 'RPAREN', '[': 'LBRACKET', ']': 'RBRACKET', ',': 'COMMA', '.': 'DOT'};
            if (punct[input[i]]) {
                tokens.push({type: punct[input[i]]});
                i++;
                continue;
            }

            // Identifiants et mots-clés (sans point — le DOT est un token séparé)
            if (/[a-zA-Z_]/.test(input[i])) {
                let id = '';
                while (i < input.length && /[a-zA-Z0-9_]/.test(input[i])) id += input[i++];
                const up = id.toUpperCase();
                if (KEYWORDS.has(up)) {
                    tokens.push({type: up});
                } else {
                    tokens.push({type: 'IDENT', value: id});
                }
                continue;
            }

            i++; // caractère inconnu ignoré
        }

        tokens.push({type: 'EOF'});
        return tokens;
    }

    // ─────────────────────────────────────────────────────────────
    // Parser (descente récursive)
    // ─────────────────────────────────────────────────────────────

    class Parser {
        constructor(tokens) {
            this.tokens = tokens;
            this.current = 0;
        }

        peek() {
            return this.tokens[this.current];
        }

        advance() {
            return this.tokens[this.current++];
        }

        check(type) {
            return this.peek().type === type;
        }

        match(...types) {
            if (types.includes(this.peek().type)) return this.advance();
            return null;
        }

        expect(type) {
            if (!this.check(type)) {
                throw new Error(
                    `Attendu "${type}", obtenu "${this.peek().type}"` +
                    (this.peek().value != null ? ` (valeur: "${this.peek().value}")` : '')
                );
            }
            return this.advance();
        }

        // ── Clause principale ────────────────────────────────────

        parse() {
            const dsl = {};

            this.expect('FROM');
            dsl.from = this.parseModelName();

            while (!this.check('EOF')) {
                if (this.match('WHERE')) dsl.filters = this.parseWhereClause();
                else if (this.match('WITH', 'TRAVERSE')) dsl.traverse = this.parseTraverseList();
                else if (this.match('FIELDS', 'SELECT')) dsl.fields = this.parseIdentList();
                else if (this.match('GROUP')) {
                    this.expect('BY');
                    dsl.group_by = this.parseIdentList();
                }
                else if (this.match('OUTPUT')) dsl.output = this.expect('IDENT').value.toLowerCase();
                else if (this.match('LIMIT')) dsl.limit = this.expect('NUMBER').value;
                else throw new Error(`Clause inconnue : "${this.peek().value ?? this.peek().type}"`);
            }

            return dsl;
        }

        // Lit un nom de modèle pouvant contenir des tirets : logical-servers, physical-server, etc.
        parseModelName() {
            let name = this.expect('IDENT').value;
            while (this.check('OP') && this.peek().value === '-') {
                this.advance();                    // consommer le tiret
                name += '-' + this.expect('IDENT').value;
            }
            return name;
        }

        // ── FIELDS : liste de chemins pointés simples ────────────
        // Retourne des strings "relation.field" ou "field"

        parseIdentList() {
            const items = [this.parseDotPath()];
            while (this.match('COMMA')) items.push(this.parseDotPath());
            return items;
        }

        parseDotPath() {
            let path = this.expect('IDENT').value;
            while (this.check('DOT')) {
                this.advance();
                path += '.' + this.expect('IDENT').value;
            }
            return path;
        }

        // ── WITH : liste de chemins avec support des nœuds masqués ─
        // Syntaxe : rel | rel.rel | (rel).rel | (rel).(rel).rel
        //
        // Retour :
        //   - string "rel.rel"     si aucun segment masqué (rétrocompat)
        //   - { segments: [{name, hidden}] }  si parenthèses présentes

        parseTraverseList() {
            const items = [this.parseTraversePath()];
            while (this.match('COMMA')) items.push(this.parseTraversePath());
            return items;
        }

        parseTraversePath() {
            const segments = [this._parseTraverseSegment()];

            while (this.check('DOT')) {
                this.advance(); // consommer DOT
                segments.push(this._parseTraverseSegment());
            }

            // Validations sémantiques
            const hasHidden = segments.some(s => s.hidden);
            const allHidden = segments.every(s => s.hidden);
            const lastHidden = segments[segments.length - 1].hidden;

            if (allHidden) console.warn('[QueryEngine] WITH : chemin entièrement masqué (no-op) :', segments.map(s => s.name).join('.'));
            if (lastHidden) console.warn('[QueryEngine] WITH : dernier segment masqué (no-op partiel) :', segments.map(s => s.name).join('.'));

            // Rétrocompatibilité : pas de parenthèses → string simple
            if (!hasHidden) return segments.map(s => s.name).join('.');
            return {segments};
        }

        _parseTraverseSegment() {
            if (this.match('LPAREN')) {
                if (this.check('LPAREN')) throw new Error('Parenthèses imbriquées interdites dans WITH');
                if (!this.check('IDENT')) throw new Error(`Identifiant attendu dans "()", obtenu "${this.peek().type}"`);
                const name = this.advance().value;
                this.expect('RPAREN');
                return {name, hidden: true};
            }
            return {name: this.expect('IDENT').value, hidden: false};
        }

        // ── Clause WHERE ─────────────────────────────────────────

        parseWhereClause() {
            const result = this.parseOrExpr();
            return Array.isArray(result) ? result : [result];
        }

        // OR — priorité la plus faible
        parseOrExpr() {
            let left = this.parseAndExpr();
            while (this.match('OR')) {
                const right = this.parseAndExpr();
                left = this.buildOrGroup(left, right);
            }
            return left;
        }

        // AND — priorité plus élevée que OR
        parseAndExpr() {
            let left = this.parseUnary();
            while (this.match('AND')) {
                const right = this.parseUnary();
                left = this.buildAndGroup(left, right);
            }
            return left;
        }

        // NOT, EXISTS, NOT EXISTS, parenthèses
        parseUnary() {

            // NOT EXISTS <relation> [WHERE <conditions>]
            if (this.match('NOT')) {
                if (this.check('EXISTS')) {
                    this.advance(); // consommer EXISTS
                    const relation = this.expect('IDENT').value;
                    const conditions = this.check('WHERE')
                        ? (this.advance(), this.parseSubConditions())
                        : [];
                    return {not_exists: relation, ...(conditions.length ? {conditions} : {})};
                }
                // NOT ordinaire (groupes logiques)
                const inner = this.parseUnary();
                return {boolean: 'not', group: Array.isArray(inner) ? inner : [inner]};
            }

            // EXISTS <relation> [WHERE <conditions>]
            if (this.match('EXISTS')) {
                const relation = this.expect('IDENT').value;
                const conditions = this.check('WHERE')
                    ? (this.advance(), this.parseSubConditions())
                    : [];
                return {exists: relation, ...(conditions.length ? {conditions} : {})};
            }

            // Groupe entre parenthèses
            if (this.match('LPAREN')) {
                const inner = this.parseOrExpr();
                this.expect('RPAREN');
                const group = Array.isArray(inner) ? inner : [inner];
                return {boolean: 'and', group};
            }

            return this.parseCondition();
        }

        // Sous-conditions après EXISTS relation WHERE ...
        // S'arrête aux mots-clés de clause ou opérateurs logiques de niveau supérieur
        parseSubConditions() {
            const stopTokens = new Set([
                'AND', 'OR', 'RPAREN', 'EOF',
                'WITH', 'TRAVERSE', 'OUTPUT', 'LIMIT', 'FIELDS', 'SELECT', 'FROM', 'GROUP',
            ]);
            const conditions = [];

            while (!stopTokens.has(this.peek().type)) {
                conditions.push(this.parseCondition());
                if (!this.match('AND')) break;
            }

            return conditions;
        }

        // ── Fusion de deux expressions ───────────────────────────

        buildAndGroup(left, right) {
            const leftArr = Array.isArray(left) ? left : [left];
            const rightArr = Array.isArray(right) ? right : [right];
            return [...leftArr, ...rightArr];
        }

        buildOrGroup(left, right) {
            const leftArr = Array.isArray(left) ? left : [left];
            const rightItem = Array.isArray(right)
                ? {boolean: 'or', group: right}
                : {...right, boolean: 'or'};
            return [...leftArr, rightItem];
        }

        // ── Condition simple : field OP value ────────────────────

        parseCondition() {
            const field = this.parseDotPath();

            // IS NULL / IS NOT NULL
            if (this.match('IS')) {
                if (this.match('NOT')) {
                    this.expect('NULL');
                    return {field, operator: '!=', value: null};
                }
                this.expect('NULL');
                return {field, operator: '=', value: null};
            }

            // LIKE
            if (this.match('LIKE')) return {field, operator: 'like', value: this.parseScalar()};

            // NOT LIKE / NOT IN
            if (this.match('NOT')) {
                if (this.match('LIKE')) return {field, operator: 'not like', value: this.parseScalar()};
                this.expect('IN');
                return {field, operator: 'not in', value: this.parseArray()};
            }

            // IN
            if (this.match('IN')) return {field, operator: 'in', value: this.parseArray()};

            // Opérateur standard
            const op = this.expect('OP').value;
            return {field, operator: op, value: this.parseScalar()};
        }

        // ── Valeur scalaire ──────────────────────────────────────

        parseScalar() {
            const tok = this.peek();
            if (['STRING', 'NUMBER'].includes(tok.type)) {
                this.advance();
                return tok.value;
            }
            if (tok.type === 'TRUE') {
                this.advance();
                return true;
            }
            if (tok.type === 'FALSE') {
                this.advance();
                return false;
            }
            if (tok.type === 'NULL') {
                this.advance();
                return null;
            }
            if (tok.type === 'IDENT') {
                this.advance();
                return tok.value;
            }
            throw new Error(`Valeur attendue, obtenu : "${tok.type}"`);
        }

        // ── Tableau (1, 2, 3) ou [1, 2, 3] ─────────────────────

        parseArray() {
            const open = this.check('LPAREN') ? 'LPAREN' : 'LBRACKET';
            const close = open === 'LPAREN' ? 'RPAREN' : 'RBRACKET';
            this.expect(open);
            const values = [this.parseScalar()];
            while (this.match('COMMA')) values.push(this.parseScalar());
            this.expect(close);
            return values;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // DSL JSON → SQL-like lisible
    // ─────────────────────────────────────────────────────────────

    function traverseItemToSql(item) {
        if (typeof item === 'string') return item;
        return item.segments.map(s => s.hidden ? `(${s.name})` : s.name).join('.');
    }

    function dslToSql(dsl) {
        if (!dsl || !dsl.from) return '';
        const lines = [];

        lines.push(`FROM ${dsl.from}`);
        if (dsl.fields?.length) lines.push(`FIELDS ${dsl.fields.join(', ')}`);
        if (dsl.filters?.length) lines.push(`WHERE ${filtersToSql(dsl.filters, '')}`);
        if (dsl.traverse?.length) lines.push(`WITH ${dsl.traverse.map(traverseItemToSql).join(', ')}`);
        if (dsl.group_by?.length) lines.push(`GROUP BY ${dsl.group_by.join(', ')}`);
        if (dsl.output != null) lines.push(`OUTPUT ${dsl.output}`);
        if (dsl.limit != null) lines.push(`LIMIT ${dsl.limit}`);

        return lines.join('\n');
    }

    function filtersToSql(filters, indent) {
        if (!Array.isArray(filters)) filters = [filters];

        return filters.map((f, i) => {
            const bool = (f.boolean ?? 'and').toUpperCase();
            const prefix = i === 0 ? '' : `${bool} `;

            // EXISTS / NOT EXISTS
            if (f.exists != null) {
                const sub = f.conditions?.length
                    ? ` WHERE ${filtersToSql(f.conditions, indent + '    ')}`
                    : '';
                return `${prefix}EXISTS ${f.exists}${sub}`;
            }
            if (f.not_exists != null) {
                const sub = f.conditions?.length
                    ? ` WHERE ${filtersToSql(f.conditions, indent + '    ')}`
                    : '';
                return `${prefix}NOT EXISTS ${f.not_exists}${sub}`;
            }

            // Groupe entre parenthèses
            if (f.group) {
                const inner = filtersToSql(f.group, indent + '    ');
                return `${prefix}(\n${indent}    ${inner}\n${indent})`;
            }

            // Condition simple
            const val = fmtVal(f.value);
            const op = (f.operator ?? '').toUpperCase();

            if (f.operator === 'like') return `${prefix}${f.field} LIKE ${val}`;
            if (f.operator === 'not like') return `${prefix}${f.field} NOT LIKE ${val}`;
            if (f.operator === 'in') return `${prefix}${f.field} IN (${f.value.map(fmtVal).join(', ')})`;
            if (f.operator === 'not in') return `${prefix}${f.field} NOT IN (${f.value.map(fmtVal).join(', ')})`;
            if (f.value === null && f.operator === '=') return `${prefix}${f.field} IS NULL`;
            if (f.value === null && f.operator === '!=') return `${prefix}${f.field} IS NOT NULL`;

            return `${prefix}${f.field} ${op} ${val}`;
        }).join(`\n${indent}`);
    }

    function fmtVal(v) {
        if (v === null) return 'NULL';
        if (typeof v === 'boolean') return v ? 'TRUE' : 'FALSE';
        if (typeof v === 'number') return String(v);
        return `"${v}"`;
    }

    // ─────────────────────────────────────────────────────────────
    // Exposition globale
    // ─────────────────────────────────────────────────────────────

    window.MercatorQuery = {
        parse(input) {
            const parser = new Parser(tokenize(input));
            return parser.parse();
        },
        dslToSql,
    };

})();