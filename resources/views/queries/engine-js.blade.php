{{--
    Partiel JS : moteur de requêtes
    À inclure à l'intérieur d'un @section('scripts').

    Variables :
      $queryDsl     array|null   – DSL à charger + exécuter au démarrage (null = aucun)
      $saveFormId   string|null  – id du <form> PUT de mise à jour (ex: 'form-save-update')
      $createRoute  string|null  – URL de création (redirect avec DSL en paramètre)
      $runUrl       string|null  – URL d'exécution de la requête enregistrée (null = texte de l'éditeur)
--}}
@php
    $runUrl      = $runUrl      ?? null;
    $queryDsl    = $queryDsl    ?? null;
    $saveFormId  = $saveFormId  ?? null;
    $createRoute = $createRoute ?? null;
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
(function () {
    'use strict';

    const parseSql  = (sql) => window.MercatorQuery.parse(sql);
    const dslToSql  = (dsl) => window.MercatorQuery.dslToSql(dsl);

    const editor       = document.getElementById('query-editor');
    const parseError   = document.getElementById('parse-error');
    const btnRun       = document.getElementById('btn-run');
    const btnFormat    = document.getElementById('btn-format');
    const btnSave      = document.getElementById('btn-save');
    const btnToggle    = document.getElementById('btn-toggle-editor');
    const btnCopyUrl   = document.getElementById('btn-copy-url');
    const btnExportSvg = document.getElementById('downloadSvg');
    const statusMsg    = document.getElementById('status-msg');
    const statusCount  = document.getElementById('status-count');

    let savedRunUrl       = @json($runUrl);
    let datatableInstance = null;
    let lastSvgContent    = '';
    let lastGraphData     = null;   // cache pour re-rendu lors du changement d'engine
    let graphvizReady     = false;

    document.addEventListener('graphvizReady', () => { graphvizReady = true; });

    // ── Changement de moteur Graphviz → re-rendu ─────────────────
    document.querySelectorAll('input[name="graph-engine"]').forEach(r => {
        r.addEventListener('change', () => {
            if (lastGraphData) renderGraph(lastGraphData);
        });
    });

    // ── Parsing / validation ─────────────────────────────────────
    function parseDsl() {
        const sql = editor.value.trim();
        if (!sql) throw new Error('Requête vide.');
        const dsl = parseSql(sql);
        const radio = document.querySelector('input[name="output"]:checked');
        if (radio) dsl.output = radio.value;
        return dsl;
    }

    function showParseError(msg) {
        parseError.textContent = msg;
        parseError.classList.remove('d-none');
        document.getElementById('editor-panel').classList.remove('d-none');
    }

    function clearParseError() {
        parseError.textContent = '';
        parseError.classList.add('d-none');
    }

    editor.addEventListener('input', clearParseError);

    // ── Mode sortie ──────────────────────────────────────────────
    document.querySelectorAll('input[name="output"]').forEach(r => {
        r.addEventListener('change', () => {
            try {
                const dsl = parseSql(editor.value.trim());
                dsl.output = r.value;
                editor.value = dslToSql(dsl);
                clearParseError();
            } catch (e) {
                // éditeur vide ou syntaxe invalide → on laisse runQuery gérer l'erreur
            }
            runQuery();
        });
    });

    // ── Toggle éditeur ───────────────────────────────────────────
    btnToggle?.addEventListener('click', () => {
        document.getElementById('editor-panel').classList.toggle('d-none');
    });

    // ── Copier l'URL d'export signée ─────────────────────────────
    btnCopyUrl?.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(btnCopyUrl.dataset.url);
            setStatus('URL copiée.');
        } catch (e) {
            setStatus('Erreur : impossible de copier l\'URL.');
        }
    });

    // ── Formater ────────────────────────────────────────────────
    btnFormat?.addEventListener('click', () => {
        try {
            const dsl    = parseSql(editor.value);
            editor.value = dslToSql(dsl);
            clearParseError();
        } catch (e) {
            showParseError('Erreur : ' + e.message);
        }
    });

    // ── Sauvegarder ─────────────────────────────────────────────
    @if($saveFormId || $createRoute)
    btnSave?.addEventListener('click', () => {
        try {
            const dsl = parseDsl();
            @if($saveFormId)
            document.getElementById('{{ $saveFormId }}-query-json').value = JSON.stringify(dsl);
            document.getElementById('{{ $saveFormId }}').submit();
            @elseif($createRoute)
            const params = new URLSearchParams({ dsl: JSON.stringify(dsl) });
            window.location.href = '{!! $createRoute !!}?' + params.toString();
            @endif
        } catch (e) {
            showParseError('Erreur : ' + e.message);
        }
    });
    @endif

    /**
     * Parcourt les éléments <image> du SVG et remplace chaque href
     * par un data-URI base64 afin d'obtenir un SVG autonome.
     */
    async function embedSvgImages(svgStr) {
        const parser = new DOMParser();
        const doc    = parser.parseFromString(svgStr, 'image/svg+xml');

        const imageEls = [...doc.querySelectorAll('image')];
        if (!imageEls.length) return svgStr;

        // Fetch + encode en parallèle
        await Promise.all(imageEls.map(async img => {
            const href = img.getAttribute('href')
                      || img.getAttributeNS('http://www.w3.org/1999/xlink', 'href');
            if (!href || href.startsWith('data:')) return;

            try {
                const resp = await fetch(href);
                if (!resp.ok) return;
                const blob    = await resp.blob();
                const dataUrl = await new Promise((res, rej) => {
                    const reader   = new FileReader();
                    reader.onload  = () => res(reader.result);
                    reader.onerror = rej;
                    reader.readAsDataURL(blob);
                });
                img.setAttribute('href', dataUrl);
                // Nettoie l'attribut xlink:href obsolète si présent
                img.removeAttributeNS('http://www.w3.org/1999/xlink', 'href');
            } catch (err) {
                console.warn('embedSvgImages: impossible de charger', href, err);
            }
        }));

        return new XMLSerializer().serializeToString(doc.documentElement);
    }

    // ── Exécuter ─────────────────────────────────────────────────
    btnRun.addEventListener('click', runQuery);

    async function runQuery() {
        let dsl;
        try {
            dsl = parseDsl();
            clearParseError();
        } catch (e) {
            showParseError('Erreur de syntaxe : ' + e.message);
            return;
        }

        showSpinner();
        setStatus('Exécution en cours…');

        try {
            const res = await fetch(savedRunUrl ?? '{!! route('admin.queries.execute') !!}', {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':       'application/json',
                },
                body: JSON.stringify(dsl),
            });

            if (!res.ok) {
                const err = await res.json().catch(() => ({ message: res.statusText }));
                throw new Error(err.message ?? JSON.stringify(err));
            }

            const data = await res.json();
            dsl.output === 'graph' ? renderGraph(data) : renderList(data);
            setStatus('Terminé');
            setCount(data.meta?.count ?? 0, dsl.output);

        } catch (e) {
            showError(e.message);
            setStatus('Erreur');
        }
    }

    // ── Rendu liste ──────────────────────────────────────────────
    function renderList({ columns, rows }) {
        hideAll();
        document.getElementById('result-list').classList.remove('d-none');
        document.getElementById('engine-panel').classList.add('d-none');
        btnExportSvg.classList.add('d-none');

        if (datatableInstance) { datatableInstance.destroy(); datatableInstance = null; }

        const table = document.getElementById('result-datatable');
        table.querySelector('thead').innerHTML =
            '<tr>' + columns.map(c => `<th>${c}</th>`).join('') + '</tr>';
        table.querySelector('tbody').innerHTML = '';

        datatableInstance = $('#result-datatable').DataTable({
            data:    rows,
            columns: columns.map(c => ({
                title:          c,
                defaultContent: '',
                data:           null,
                render:         (data, type, row) => row[c] ?? '',
            })),
            order:      [[0, 'asc']],
            pageLength: 250,
            lengthMenu: [10, 100, 250, 1000],
            buttons: [
                { extend: 'colvis', className: 'btn-default' },
                { extend: 'copy',   className: 'btn-default' },
                { extend: 'csv',    className: 'btn-default' },
                { extend: 'excel',  className: 'btn-default' },
            ],
            layout:  { paging: true },
        });

        datatableInstance.buttons(0, null).container()
            .prependTo(datatableInstance.table().container());
    }

    // ── Rendu graphe ─────────────────────────────────────────────
    function renderGraph({ nodes, edges }) {
        if (!graphvizReady) {
            document.addEventListener('graphvizReady', () => renderGraph({ nodes, edges }), { once: true });
            return;
        }
        lastGraphData = { nodes, edges };   // cache pour re-rendu
        const engine = document.querySelector('input[name="graph-engine"]:checked')?.value ?? 'dot';
        try {
            const svgStr = window.graphviz.layout(
                buildDot(nodes, edges), 'svg', engine, { images: buildImagesArray(nodes) }
            );
            lastSvgContent = svgStr;
            hideAll();
            document.getElementById('graph').innerHTML = svgStr;
            document.getElementById('result-graph').classList.remove('d-none');
            document.getElementById('engine-panel').classList.remove('d-none');
            btnExportSvg.classList.remove('d-none');
        } catch (e) {
            showError('Erreur Graphviz : ' + e.message);
        }
    }

    // ── DOT ──────────────────────────────────────────────────────

    /**
     * Résout l'icône d'un nœud en garantissant toujours une string.
     * n.icon est fourni par PHP (HasIconContract ou default.png).
     */
    function resolveIcon(n) {
        return String(n.icon || '/images/default.png');
    }

    function buildDot(nodes, edges) {
        const lines = [
            'digraph G {',
            '  graph [bgcolor="transparent" fontname="Helvetica"]',
            '  node  [shape=none fontname="Helvetica" fontsize=11 labelloc=b width=1 height=1.1]',
            '  edge  [color="#666666" arrowsize=0.7]', '',
        ];
        for (const n of nodes)
            lines.push(`  "${n.id}" [label="${esc(n.label??n.id)}" image="${resolveIcon(n)}" tooltip="${esc(n.label??n.id)}" URL="${String(n.data?.url??'')}"]`);
        lines.push('');
        for (const e of edges) lines.push(`  "${e.from}" -> "${e.to}"`);
        lines.push('}');
        return lines.join('\n');
    }

    function buildImagesArray(nodes) {
        const seen = new Set(), images = [];
        for (const n of nodes) {
            const icon = resolveIcon(n);
            if (!seen.has(icon)) { seen.add(icon); images.push({ path: icon, width: '64px', height: '64px' }); }
        }
        return images;
    }

    // ── Helpers ──────────────────────────────────────────────────
    function esc(s) { return String(s).replace(/\\/g,'\\\\').replace(/"/g,'\\"').replace(/\n/g,' '); }

    function showSpinner() { hideAll(); document.getElementById('result-spinner').classList.remove('d-none'); }
    function showError(msg) {
        hideAll();
        document.getElementById('result-error').classList.remove('d-none');
        document.getElementById('result-error-msg').textContent = msg;
    }
    function hideAll() {
        ['result-placeholder','result-spinner','result-error','result-list','result-graph']
            .forEach(id => document.getElementById(id).classList.add('d-none'));
    }
    function setStatus(msg) { statusMsg.textContent = msg; }
    function setCount(n, output) {
        statusCount.textContent = `${n} ${output === 'graph' ? 'nœud(s)' : 'ligne(s)'}`;
    }

    // ── API publique (utilisée par l'index pour charger depuis la liste) ──
    window.QueryEngine = {
        load(dsl, runUrl = savedRunUrl) {
            savedRunUrl  = runUrl;
            const output = dsl.output ?? 'list';
            editor.value = dslToSql(dsl);
            clearParseError();
            const radio = document.getElementById('out-' + output);
            if (radio) radio.checked = true;
            runQuery();
        }
    };

    // ── Chargement initial (contexte show) ───────────────────────
    @if($queryDsl)
    window.QueryEngine.load(@json($queryDsl));
    @endif

})();
});
</script>
