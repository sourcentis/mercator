@extends('layouts.admin')

@section('title', __('cruds.tools.query.title'))

@section('content')
@can('query_create')
    <div style="margin-bottom:10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.queries.create') }}">
                {{ trans('global.add') }} @lang('cruds.tools.query.title_short')
            </a>
        </div>
    </div>
@endcan

<div class="row g-3" style="height:calc(100vh - 180px);">

    {{-- ── Colonne gauche : liste ─────────────────────────────────── --}}
    <div class="col-md-5 d-flex flex-column" style="height:100%;">
        <div class="card flex-grow-1" style="overflow:hidden; display:flex; flex-direction:column;">
            <div class="card-header py-2 px-3">
                @lang('Queries')
            </div>
            <div class="card-body p-2" style="overflow-y:auto; flex:1;">
                <table id="dataTable" class="table table-bordered table-striped table-hover datatable w-100">
                    <thead>
                        <tr>
                            <th width="10"></th>
                            <th>@lang('Nom')</th>
                            <th>@lang('Entité')</th>
                            <th>@lang('Sortie')</th>
                            <th>@lang('Propriétaire')</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($queries as $q)
                        @php $isOwner = auth()->id() === $q->user_id; @endphp
                        <tr data-entry-id="{{ $q->id }}"
                            data-name="{{ e($q->name) }}"
                            data-query-dsl='{{ json_encode($q->query ?? [], JSON_HEX_APOS | JSON_HEX_TAG) }}'
                            data-url-show="{{ route('admin.queries.show', $q->id) }}"
                            data-url-run="{{ route('admin.queries.run', $q->id) }}"
                            data-url-edit="{{ route('admin.queries.edit', $q->id) }}"
                            data-url-destroy="{{ route('admin.queries.destroy', $q->id) }}"
                            data-is-owner="{{ $isOwner ? '1' : '0' }}">
                            <td></td>
                            <td>
                                @canany(['query_create', 'query_edit'])
                                    <a href="{{ route('admin.queries.show', $q->id) }}"
                                       title="@lang('Ouvrir')">
                                        {{ $q->name }}
                                    </a>
                                @else
                                    {{ $q->name }}
                                @endcanany
                                @if($q->description)
                                    <small class="d-block text-muted">{{ $q->description }}</small>
                                @endif
                            </td>
                            <td><code>{{ $q->from }}</code></td>
                            <td>
                                @if($q->output === 'graph')
                                    <span class="badge bg-info text-dark">
                                        <i class="fas fa-project-diagram"></i> Graphe
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-list"></i> Liste
                                    </span>
                                @endif
                            </td>
                            <td class="small">
                                {{ $q->user?->name ?? '—' }}
                                @if($q->is_public)
                                    <span class="badge bg-light text-muted ms-1" title="@lang('Partagée')">
                                        <i class="fas fa-globe-europe"></i>
                                    </span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                @if($isOwner)
                                    @can('query_edit')
                                        <a class="btn btn-xs btn-info"
                                           href="{{ route('admin.queries.edit', $q->id) }}">
                                            {{ trans('global.edit') }}
                                        </a>
                                    @endcan
                                    @can('query_delete')
                                        <form action="{{ route('admin.queries.destroy', $q->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('{{ trans('global.areYouSure') }}');"
                                              style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger">
                                                {{ trans('global.delete') }}
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Colonne droite : moteur de requêtes ────────────────────── --}}
    <div class="col-md-7 d-flex flex-column" style="height:100%;">
        <div class="card flex-grow-1 d-flex flex-column" style="overflow:hidden;">

            {{-- En-tête du panneau avec titre + actions --}}
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <span id="panel-title" class="text-muted small">
                    @lang('Query Engine')
                </span>
                <div id="panel-actions" class="d-none gap-1" style="display:none !important;">
                    @can('query_edit')
                        <a id="btn-panel-edit" href="#" class="btn btn-xs btn-info">
                            {{ trans('global.edit') }}
                        </a>
                    @endcan
                    <a id="btn-panel-show" href="#" class="btn btn-xs btn-secondary">
                        <i class="fas fa-external-link-alt"></i> @lang('Ouvrir')
                    </a>
                    @can('query_delete')
                        <form id="form-panel-destroy" action="#" method="POST"
                              style="display:inline-block;"
                              onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger">
                                {{ trans('global.delete') }}
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            {{-- Moteur --}}
            @include('queries._engine', ['hideEditor' => true])

        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
#dataTable tbody tr.selected {
    background-color: var(--bs-primary) !important;
    color: #fff;
}
#dataTable tbody tr {
    cursor: pointer;
}
#panel-actions.show {
    display: flex !important;
}
</style>
@endsection

@section('scripts')
@vite(['resources/js/graphviz.js', 'resources/js/sql-parser.js'])
@include('queries.engine-js')
<script>

@include('partials.datatable', [
    'id'         => '#dataTable',
    'title'      => trans('cruds.tools.query.title_short'),
    'URL'        => route('admin.queries.massDestroy'),
    'canDelete'  => auth()->user()->can('query_delete'),
    'hasButtons' => false,
])

{{-- Sélection d'une ligne → charge et exécute la requête dans le panneau --}}
document.addEventListener('DOMContentLoaded', function () {
    const panelTitle       = document.getElementById('panel-title');
    const panelActions     = document.getElementById('panel-actions');
    const btnPanelEdit     = document.getElementById('btn-panel-edit');
    const btnPanelShow     = document.getElementById('btn-panel-show');
    const formPanelDestroy = document.getElementById('form-panel-destroy');

    document.addEventListener('click', function (e) {
        const row = e.target.closest('#dataTable tbody tr');
        if (!row) return;
        if (e.target.closest('a, button, input[type="submit"], input[type="checkbox"]')) return;

        // console.group('[QueryEngine] Clic sur ligne');

        // 1. Données brutes sur la ligne
        // console.log('row.dataset:', JSON.parse(JSON.stringify(row.dataset)));

        const dslRaw = row.dataset.queryDsl;
        // console.log('dslRaw:', dslRaw);

        if (!dslRaw) { console.warn('⛔ dslRaw vide — attribut data-query-dsl manquant ?'); console.groupEnd(); return; }

        let dsl;
        try {
            // Décode les éventuelles entités HTML (&quot; → ") avant de parser
            const ta = document.createElement('textarea');
            ta.innerHTML = dslRaw;
            dsl = JSON.parse(ta.value);
        } catch (err) {
            console.error('JSON.parse échoué :', err, dslRaw);
            return;
        }

        // 2. Vérification QueryEngine
        // console.log('window.QueryEngine :', window.QueryEngine);
        if (!window.QueryEngine) { console.error('⛔ window.QueryEngine non défini !'); console.groupEnd(); return; }
        if (typeof window.QueryEngine.load !== 'function') { console.error('⛔ QueryEngine.load n\'est pas une fonction !'); console.groupEnd(); return; }

        // 3. Vérification dslToSql / parseSql
        //console.log('window.MercatorQuery :', window.MercatorQuery);
        if (window.MercatorQuery?.dslToSql) {
            try {
                const sql = window.MercatorQuery.dslToSql(dsl);
                // console.log('dslToSql() =>', sql);
            } catch (err) {
                console.error('⛔ dslToSql() a levé une exception :', err);
            }
        } else {
            console.warn('⚠️ MercatorQuery.dslToSql non disponible à ce stade');
        }

        // 4. Vérification de l'éditeur
        const editorPanel = document.getElementById('editor-panel');
        const editor      = document.getElementById('query-editor');
        // console.log('editor-panel :', editorPanel, '| classes :', editorPanel?.className);
        // console.log('query-editor :', editor, '| valeur avant load :', editor?.value);

        // Mise à jour panneau
        const isOwner = row.dataset.isOwner === '1';
        panelTitle.textContent = row.dataset.name;
        if (btnPanelEdit) {
            btnPanelEdit.href  = row.dataset.urlEdit;
            btnPanelEdit.style.display = isOwner ? '' : 'none';
        }
        if (btnPanelShow)     btnPanelShow.href           = row.dataset.urlShow;
        if (formPanelDestroy) {
            formPanelDestroy.action     = row.dataset.urlDestroy;
            formPanelDestroy.style.display = isOwner ? '' : 'none';
        }
        panelActions.classList.remove('d-none');
        panelActions.classList.add('show');

        // 5. Appel load
        // console.log('→ Appel QueryEngine.load(dsl)');
        try {
            window.QueryEngine.load(dsl, row.dataset.urlRun);
            // console.log('✅ QueryEngine.load() terminé');
            // console.log('query-editor valeur après load :', editor?.value);
        } catch (err) {
            console.error('⛔ QueryEngine.load() a levé une exception :', err);
        }

        console.groupEnd();
    });
});
</script>

@vite(['resources/js/graphviz.js', 'resources/js/sql-parser.js'])
@parent
@endsection