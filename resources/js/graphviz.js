// Graphviz (WASM)
import {Graphviz} from "@hpcc-js/wasm-graphviz";

window.graphvizReady = Graphviz.load()
    .then((graphviz) => {
        window.graphviz = graphviz;
        document.dispatchEvent(new Event('graphvizReady'));
        return graphviz;
    })
    .catch((err) => {
        console.error('Failed to load Graphviz WASM:', err);
        document.dispatchEvent(new CustomEvent('graphvizError', {detail: err}));
        throw err;
    });

/**
 * Calcule la mise en page d'un DOT et l'injecte dans #graph.
 *
 * layout() est synchrone et bloque le thread principal : on affiche d'abord le
 * curseur d'attente (classe html.graph-busy) et on laisse le navigateur le peindre
 * (double rAF) avant de lancer le calcul.
 *
 * @returns {Promise<void>} résolue une fois le SVG injecté
 */
window.renderGraphviz = function (dotSrc, engine, options = {}, target = 'graph') {
    const radios = document.querySelectorAll('input[name="engine"]');
    document.documentElement.classList.add('graph-busy');
    radios.forEach(r => r.disabled = true);

    return new Promise((resolve, reject) => {
        requestAnimationFrame(() => requestAnimationFrame(() => {
            try {
                document.getElementById(target).innerHTML =
                    window.graphviz.layout(dotSrc, "svg", engine, options);
                resolve();
            } catch (err) {
                console.error('Graphviz layout failed:', err);
                reject(err);
            } finally {
                document.documentElement.classList.remove('graph-busy');
                radios.forEach(r => r.disabled = false);
            }
        }));
    });
};

/**
 * Branche les boutons radio input[name="engine"] : un changement de moteur relance
 * seulement la mise en page côté client (le DOT n'en dépend pas) et met l'URL à
 * jour, sans renvoyer le formulaire au serveur.
 *
 * @param {(engine: string) => void} render  fonction de rendu à rappeler
 */
window.bindGraphvizEngineRadios = function (render) {
    document.querySelectorAll('input[name="engine"]').forEach(radio =>
        radio.addEventListener('change', (e) => {
            render(e.target.value);
            // Garde l'URL synchronisée (rechargement, partage du lien)
            const url = new URL(window.location.href);
            url.searchParams.set('engine', e.target.value);
            history.replaceState(null, '', url);
        })
    );
};

/**
 * Compte les nœuds distincts d'un source DOT, sans appeler Graphviz.
 *
 * Petit tokenizer linéaire (O(n)) : les labels HTML-like (DotNode::withImage())
 * contiennent des "[", "<", "=", etc., une regex globale ne suffit pas.
 * Les listes d'attributs [ … ], les mots-clés, les noms de graphe / sous-graphe,
 * les attributs de graphe (ID = ID) et les ports (:port[:compass]) sont ignorés.
 *
 * @param {string} dotSrc  source DOT
 * @returns {number}       nombre de nœuds distincts
 */
window.countDotNodes = function (dotSrc) {
    const src = String(dotSrc ?? '');
    const n = src.length;
    const tokens = []; // {t: 'id' | 'kw' | 'p', v: string}
    const isIdStart = (c) => (c >= 'A' && c <= 'Z') || (c >= 'a' && c <= 'z') || c === '_' || c >= '\u0080';
    const isIdChar = (c) => isIdStart(c) || (c >= '0' && c <= '9');
    const isDigit = (c) => c >= '0' && c <= '9';
    const KEYWORDS = new Set(['strict', 'graph', 'digraph', 'node', 'edge', 'subgraph']);

    // --- Tokenisation ---
    let i = 0;
    let lineStart = true; // seulement des blancs depuis le début de la ligne
    while (i < n) {
        const c = src[i];

        if (c === '\n') { lineStart = true; i++; continue; }
        if (c === ' ' || c === '\t' || c === '\r' || c === '\f' || c === '\v') { i++; continue; }

        // Ligne de préprocesseur "# …"
        if (c === '#' && lineStart) {
            while (i < n && src[i] !== '\n') i++;
            continue;
        }
        lineStart = false;

        // Commentaires
        if (c === '/' && src[i + 1] === '/') {
            while (i < n && src[i] !== '\n') i++;
            continue;
        }
        if (c === '/' && src[i + 1] === '*') {
            const end = src.indexOf('*/', i + 2);
            i = end < 0 ? n : end + 2;
            continue;
        }

        // Chaîne entre guillemets (\" échappé, \ + fin de ligne = continuation)
        if (c === '"') {
            let v = '';
            i++;
            while (i < n && src[i] !== '"') {
                if (src[i] === '\\' && src[i + 1] === '"') { v += '"'; i += 2; continue; }
                if (src[i] === '\\' && src[i + 1] === '\n') { i += 2; continue; }
                v += src[i++];
            }
            i++; // guillemet fermant
            const prev = tokens[tokens.length - 1];
            const prev2 = tokens[tokens.length - 2];
            // Concaténation "a" + "b"
            if (prev && prev.t === 'p' && prev.v === '+' && prev2 && prev2.t === 'id') {
                tokens.pop();
                prev2.v += v;
            } else {
                tokens.push({t: 'id', v});
            }
            continue;
        }

        // Chaîne HTML <…> avec gestion de l'imbrication
        if (c === '<') {
            let depth = 0;
            const start = i;
            while (i < n) {
                if (src[i] === '<') depth++;
                else if (src[i] === '>' && --depth === 0) { i++; break; }
                i++;
            }
            tokens.push({t: 'id', v: src.slice(start, i)});
            continue;
        }

        // Opérateurs d'arête
        if (c === '-' && (src[i + 1] === '>' || src[i + 1] === '-')) {
            tokens.push({t: 'p', v: '-' + src[i + 1]});
            i += 2;
            continue;
        }

        // Numéral : -?(.[0-9]+ | [0-9]+(.[0-9]*)?)
        if (isDigit(c) || ((c === '-' || c === '.') && (isDigit(src[i + 1]) || (src[i + 1] === '.' && isDigit(src[i + 2]))))) {
            const start = i;
            if (src[i] === '-') i++;
            while (i < n && isDigit(src[i])) i++;
            if (src[i] === '.') { i++; while (i < n && isDigit(src[i])) i++; }
            tokens.push({t: 'id', v: src.slice(start, i)});
            continue;
        }

        // Identifiant ou mot-clé
        if (isIdStart(c)) {
            const start = i;
            while (i < n && isIdChar(src[i])) i++;
            const v = src.slice(start, i);
            tokens.push(KEYWORDS.has(v.toLowerCase()) ? {t: 'kw', v: v.toLowerCase()} : {t: 'id', v});
            continue;
        }

        // Ponctuation : { } [ ] ; , = : +
        if ('{}[];,=:+'.includes(c)) tokens.push({t: 'p', v: c});
        i++; // tout autre caractère est ignoré
    }

    // --- Analyse ---
    const nodes = new Set();
    const isP = (tok, v) => tok !== undefined && tok.t === 'p' && tok.v === v;
    const m = tokens.length;
    let k = 0;
    while (k < m) {
        const tok = tokens[k];

        // Liste d'attributs : on saute tout jusqu'au "]"
        if (isP(tok, '[')) {
            while (k < m && !isP(tokens[k], ']')) k++;
            k++;
            continue;
        }

        if (tok.t === 'kw') {
            k++;
            // Nom du graphe / sous-graphe
            if ((tok.v === 'graph' || tok.v === 'digraph' || tok.v === 'subgraph') && tokens[k]?.t === 'id') k++;
            continue;
        }

        if (tok.t === 'id') {
            // Attribut de graphe : ID = ID
            if (isP(tokens[k + 1], '=')) {
                k += tokens[k + 2]?.t === 'id' ? 3 : 2;
                continue;
            }
            nodes.add(tok.v);
            k++;
            // Port éventuel :port[:compass]
            for (let p = 0; p < 2 && isP(tokens[k], ':') && tokens[k + 1]?.t === 'id'; p++) k += 2;
            continue;
        }

        k++; // { } ; , -> -- : +
    }

    return nodes.size;
};

/**
 * Vérifie que le graphe ne dépasse pas la limite window.mercatorGraph.maxNodes
 * (0 ou absente = pas de limite). Si elle est dépassée, remplace le contenu de
 * #target par un message d'avertissement et l'écrit dans la console.
 *
 * @param {string} dotSrc  source DOT
 * @param {string} target  id du conteneur du graphe
 * @returns {boolean}      true si le graphe peut être rendu
 */
window.checkGraphSize = function (dotSrc, target = 'graph') {
    const max = window.mercatorGraph?.maxNodes ?? 0;
    if (max <= 0) {
        return true;
    }

    const count = window.countDotNodes(dotSrc);
    if (count <= max) {
        return true;
    }

    console.warn(`Graphviz: graph too large (${count} nodes, limit ${max})`);

    const container = document.getElementById(target);
    if (container) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-warning m-3';
        alert.setAttribute('role', 'alert');

        const icon = document.createElement('i');
        icon.className = 'bi bi-exclamation-triangle me-2';

        const text = document.createElement('span');
        // Placeholders :count / :max laissés tels quels par trans() côté Blade
        text.textContent = (window._lang?.graphTooLarge ?? 'Graph too large: :count nodes (maximum :max).')
            .replace(':count', count)
            .replace(':max', max);

        alert.append(icon, text);
        container.replaceChildren(alert);
    }

    return false;
};

/**
 * Cas standard des rapports : un DOT fixe calculé côté serveur.
 *
 * @param {object}   params
 * @param {string}   params.dotSrc     source DOT
 * @param {string}   params.engine     moteur Graphviz initial
 * @param {Array}    params.images     images référencées par le DOT
 * @param {boolean}  params.checkSize  true (défaut) : ne rien rendre si le graphe
 *                                     dépasse window.mercatorGraph.maxNodes ;
 *                                     false : ignorer la limite
 */
window.initGraphvizReport = function ({dotSrc, engine, images = [], checkSize = true}) {
    // Graphe trop grand détecté côté serveur (GraphSize) : le DOT n'a pas été
    // construit et le message est déjà dans #graph, on ne fait rien d'autre
    const tooLarge = document.querySelector('#graph [data-graph-too-large]');
    if (tooLarge) {
        console.warn(`Graphviz: graph too large (${tooLarge.dataset.nodeCount} nodes, limit ${tooLarge.dataset.maxNodes})`);
        return;
    }

    const render = (eng) => window.renderGraphviz(dotSrc, eng, {images});

    window.graphvizReady.then(() => {
        if (checkSize && !window.checkGraphSize(dotSrc)) {
            return; // ni rendu, ni branchement des radios moteur
        }
        render(engine);
        window.bindGraphvizEngineRadios(render);
    });
};
