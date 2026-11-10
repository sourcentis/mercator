{{--
    Partiel : panneau moteur de requêtes
    Fournit : toolbar (card-header), éditeur, zone de résultat, barre de statut (card-footer).
    La card englobante (et son éventuel card-header de titre) est fournie par le parent.
--}}

{{-- ── Keyframes inline (pas de @push/@stack disponible dans ce layout) --}}
<style>
@keyframes spin-loop {
    from { transform: rotate(0deg);   }
    to   { transform: rotate(360deg); }
}
</style>

{{-- $readOnly : compte sans droit d'édition, seuls le résultat et le lien d'export restent visibles --}}
@php
    $readOnly = $readOnly ?? false;
    $readOnlyClass = $readOnly ? ' d-none' : '';
@endphp

{{-- ── Barre d'outils (conservée dans le DOM si masquée : le JS lit ses champs) --}}
<div class="card-header py-2 d-flex gap-2 align-items-center flex-wrap{{ (($hideEditor ?? false) || ($readOnly && ! isset($exportUrl))) ? ' d-none' : '' }}">

    {{-- Mode sortie --}}
    <div class="btn-group btn-group-sm{{ $readOnlyClass }}" role="group">
        <input type="radio" class="btn-check" name="output" id="out-list"  value="list"  autocomplete="off" checked>
        <label class="btn btn-outline-secondary" for="out-list">
            <i class="fas fa-list"></i> @lang('Liste')
        </label>
        <input type="radio" class="btn-check" name="output" id="out-graph" value="graph" autocomplete="off">
        <label class="btn btn-outline-info" for="out-graph">
            <i class="fas fa-project-diagram"></i> @lang('Graphe')
        </label>
    </div>

    <div class="flex-grow-1"></div>

    <button class="btn btn-sm btn-outline-secondary{{ $readOnlyClass }}" id="btn-toggle-editor"
            title="@lang('Afficher/masquer l\'éditeur')">
        <i class="fas fa-code"></i> @lang('Éditeur')
    </button>

    @isset($exportUrl)
        <button class="btn btn-sm btn-outline-secondary" id="btn-copy-url" data-url="{{ $exportUrl }}">
            <i class="fas fa-link"></i> {{ trans('global.copyUrl') }}
        </button>
    @endisset

    <button class="btn btn-sm btn-outline-secondary{{ $readOnlyClass }}" id="btn-format"
            title="@lang('Formater la requête')">
        <i class="fas fa-indent"></i> @lang('Format')
    </button>

    <button class="btn btn-sm btn-primary{{ $readOnlyClass }}" id="btn-run">
        <i class="fas fa-play"></i> @lang('Exécuter')
    </button>
</div>

{{-- ── Éditeur DSL ─────────────────────────────────────────────────── --}}
<div id="editor-panel" class="border-bottom d-none">
    <textarea id="query-editor"
              class="form-control font-monospace border-0 rounded-0{{ ($hideEditor ?? false) ? ' d-none' : '' }}"
              rows="8"
              style="font-size:0.85rem; resize:vertical;"
              spellcheck="false"
              @readonly($readOnly)
              placeholder="FROM logical-servers
WHERE environment = &quot;production&quot;
AND (operating_system LIKE &quot;%Linux%&quot; OR os LIKE &quot;%Windows%&quot;)
WITH applications
DEPTH 2
OUTPUT graph
LIMIT 100"></textarea>
    <div id="parse-error" class="d-none px-3 py-1 bg-danger text-white small"></div>
</div>

{{-- ── Zone de résultat ────────────────────────────────────────────── --}}
<div class="flex-grow-1 position-relative" style="min-height:0;">

    <div id="result-placeholder"
         class="d-flex align-items-center justify-content-center text-muted"
         style="position:absolute; inset:0;">
        <div class="text-center">
            <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
            @if($hideEditor ?? false)
                <p>@lang('Sélectionnez une requête dans la liste.')</p>
            @elseif(! $readOnly)
                <p>@lang('Écrivez une requête et cliquez sur Exécuter.')</p>
            @endif
        </div>
    </div>

    {{-- Spinner affiché pendant le traitement par le contrôleur (id unique) --}}
    <div id="result-spinner"
         class="d-none"
         style="position:absolute; inset:0; z-index:10;
                background:rgba(255,255,255,.82); backdrop-filter:blur(3px);
                display:flex; align-items:center; justify-content:center;">
        <div class="d-flex flex-column align-items-center justify-content-center gap-2">
            <i class="bi bi-arrow-repeat text-primary"
               style="font-size:2.8rem; animation:spin-loop .8s linear infinite; display:block;"></i>
        </div>
    </div>

    <div id="result-error"
         class="d-none p-3"
         style="position:absolute; inset:0; overflow:auto;">
        <div class="alert alert-danger mb-0" id="result-error-msg"></div>
    </div>

    <div id="result-list"
         class="d-none p-2"
         style="position:absolute; inset:0; overflow:auto;">
        <table id="result-datatable" class="table table-sm table-hover table-striped">
            <thead></thead>
            <tbody></tbody>
        </table>
    </div>

    <div id="result-graph"
         class="d-none p-2 text-center"
         style="position:absolute; inset:0; overflow:auto;">
        <div id="graph"></div>
    </div>

</div>

{{-- ── Barre de statut ─────────────────────────────────────────────── --}}
<div class="card-footer py-1 px-3 d-flex justify-content-between align-items-center flex-wrap gap-1">

    <div class="d-flex align-items-center gap-3 flex-wrap">
        <a href="#" id="downloadSvg" class="d-none small text-decoration-none"
           title="@lang('Télécharger le graphe en SVG')">
            <i class="bi bi-download"></i>
        </a>

        {{-- Sélection du moteur Graphviz — visible uniquement en mode graphe --}}
        <div id="engine-panel" class="d-none d-flex align-items-center gap-2 small">
            <span class="text-muted">@lang('Rendu') :</span>
            @foreach(['dot', 'fdp', 'osage', 'circo'] as $eng)
                <label class="d-flex align-items-center gap-1 mb-0 user-select-none">
                    <input type="radio" name="graph-engine"
                           value="{{ $eng }}" id="engine-{{ $eng }}"
                           @if($eng === 'dot') checked @endif>
                    <span>{{ $eng }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <small class="text-muted" id="status-msg">—</small>
        <small class="text-muted" id="status-count"></small>
    </div>

</div>