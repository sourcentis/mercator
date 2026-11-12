@extends('layouts.admin')
@section('content')
    @can('graph_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.bpmn.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.bpmn.title_singular') }}
                </a>
            </div>
        </div>
    @endcan

    {{--
        row g-3 avec col-5/col-7 Bootstrap.
        Les deux overrides critiques sur chaque colonne :
          • min-width:0  — sans cela, un flex-item peut dépasser sa largeur allouée
          • overflow:hidden — coupe visuellement tout débordement de contenu
        Le fix JS (graphContainer en pixels avant loadGraph) bloque la propagation
        du min-width SVG injecté par MaxGraph.
    --}}
    <div class="row g-3" style="height:calc(100vh - 180px);">

        {{-- ── Colonne gauche : liste ──────────────────────────────────── --}}
        <div class="col-5 d-flex flex-column" style="min-width:0; overflow:hidden; height:100%;">
            <div class="card flex-grow-1" style="min-width:0; overflow:hidden; display:flex; flex-direction:column;">
                <div class="card-header py-2 px-3">
                    {{ trans('cruds.bpmn.title') }} {{ trans('global.list') }}
                </div>
                <div class="card-body p-2" style="overflow-y:auto; flex:1; min-height:0;">
                    <table id="dataTable" class="table table-bordered table-striped table-hover datatable w-100">
                        <thead>
                        <tr>
                            <th width="10"></th>
                            <th>{{ trans('cruds.bpmn.fields.name') }}</th>
                            <th>{{ trans('cruds.bpmn.fields.type') }}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($graphs as $graph)
                            <tr data-entry-id="{{ $graph->id }}"
                                data-name="{{ e($graph->name) }}"
                                data-url-data="{{ route('admin.bpmn.data', $graph->id) }}"
                                data-url-show="{{ route('admin.bpmn.show', $graph->id) }}"
                                data-url-edit="{{ route('admin.bpmn.edit', $graph->id) }}"
                                data-url-raw="{{ route('admin.bpmn.raw', $graph->id) }}"
                                data-url-destroy="{{ route('admin.bpmn.destroy', $graph->id) }}">
                                <td></td>
                                <td>
                                    <a href="{{ route('admin.bpmn.show', $graph->id) }}"
                                       title="{{ trans('global.show') }}">
                                        {{ $graph->name ?? '' }}
                                    </a>
                                </td>
                                <td>{{ $graph->type ?? '' }}</td>
                                <td class="text-end text-nowrap">
                                {{--
                                    <a class="btn btn-xs btn-secondary" href="{{ route('admin.bpmn.raw', $graph->id) }}">
                                        RAW
                                    </a>
                                --}}
                                    @can('graph_edit')
                                        <a class="btn btn-xs btn-info" href="{{ route('admin.bpmn.edit', $graph->id) }}">
                                            {{ trans('global.edit') }}
                                        </a>
                                    @endcan
                                    @can('graph_delete')
                                        <form action="{{ route('admin.bpmn.destroy', $graph->id) }}" method="POST"
                                              onsubmit="return confirm('{{ trans('global.areYouSure') }}');"
                                              style="display:inline-block;">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="submit" class="btn btn-xs btn-danger"
                                                   value="{{ trans('global.delete') }}">
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── Colonne droite : aperçu BPMN ────────────────────────────── --}}
        <div class="col-7 d-flex flex-column" style="min-width:0; overflow:hidden; height:100%;">
            <div class="card flex-grow-1 d-flex flex-column" style="min-width:0; overflow:hidden;">

                {{-- En-tête avec titre + actions --}}
                <div class="card-header d-flex justify-content-between align-items-center py-2" style="flex-shrink:0;">
                    <span id="panel-title" class="text-muted small fst-italic">
                        {{ trans('cruds.bpmn.title_singular') }}
                    </span>
                    <div id="panel-actions" class="d-none gap-1" style="display:none !important;">
                        @can('graph_edit')
                            <a id="btn-panel-edit" href="#" class="btn btn-xs btn-info">
                                {{ trans('global.edit') }}
                            </a>
                        @endcan
                        <a id="btn-panel-show" href="#" class="btn btn-xs btn-secondary">
                            <i class="fas fa-external-link-alt"></i> {{ trans('global.show') }}
                        </a>
                        <a id="btn-panel-raw" href="#" class="btn btn-xs btn-secondary">
                            RAW
                        </a>
                        @can('graph_delete')
                            <form id="form-panel-destroy" action="#" method="POST"
                                  style="display:inline-block;"
                                  onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="submit" class="btn btn-xs btn-danger"
                                       value="{{ trans('global.delete') }}">
                            </form>
                        @endcan
                    </div>
                </div>

                {{-- Wrapper scrollable — dimensions fixées en JS avant loadGraph --}}
                <div id="graph-scroll-wrapper"
                     style="flex:1 1 0; height:0; min-height:0; min-width:0; overflow:auto; position:relative;">
                    <div id="graph-container"
                         style="display:none; position:relative; width:100%; min-height:100%;
                                cursor:default; touch-action:none;">
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('styles')
    @vite('resources/css/mapping.css')
    <style>
        @font-face {
            font-family: "bpmn";
            src: url("/build/fonts/bpmn.ttf") format("truetype");
            font-display: block;
        }

        #dataTable tbody tr {
            cursor: pointer;
        }

        #dataTable tbody tr.selected {
            background-color: var(--bs-primary) !important;
            color: #fff;
        }

        #dataTable tbody tr.selected a {
            color: #fff;
        }

        #panel-actions.show {
            display: flex !important;
        }
    </style>
@endsection

@section('scripts')
    @vite('resources/BPMN/bpmn-show.ts')

    <script @nonce>
        @include('partials.datatable', [
            'id'         => '#dataTable',
            'title'      => trans('cruds.bpmn.title_singular'),
            'URL'        => route('admin.graphs.massDestroy'),
            'canDelete'  => auth()->user()->can('graph_delete') ? true : false,
            'hasButtons' => false,
        ])

        document.addEventListener('DOMContentLoaded', function () {
            const panelTitle      = document.getElementById('panel-title');
            const panelActions    = document.getElementById('panel-actions');
            const btnPanelEdit    = document.getElementById('btn-panel-edit');
            const btnPanelShow    = document.getElementById('btn-panel-show');
            const btnPanelRaw     = document.getElementById('btn-panel-raw');
            const formDestroy     = document.getElementById('form-panel-destroy');
            const graphContainer   = document.getElementById('graph-container');

            let currentId = null;

            function showPlaceholder(msg) {
                graphContainer.style.display = 'none';
            }

            document.addEventListener('click', function (e) {
                const row = e.target.closest('#dataTable tbody tr');
                if (!row) return;
                if (e.target.closest('a, button, input[type="submit"], input[type="checkbox"]')) return;

                const id = row.dataset.entryId;
                if (id === currentId) return;
                currentId = id;

                // Mise en évidence de la ligne sélectionnée
                document.querySelectorAll('#dataTable tbody tr.selected')
                    .forEach(r => r.classList.remove('selected'));
                row.classList.add('selected');

                // Mise à jour des actions du panneau
                panelTitle.textContent = row.dataset.name;
                panelTitle.classList.remove('text-muted', 'fst-italic');
                if (btnPanelEdit)  btnPanelEdit.href  = row.dataset.urlEdit;
                if (btnPanelShow)  btnPanelShow.href  = row.dataset.urlShow;
                if (btnPanelRaw)   btnPanelRaw.href   = row.dataset.urlRaw;
                if (formDestroy)   formDestroy.action = row.dataset.urlDestroy;
                panelActions.classList.remove('d-none');
                panelActions.classList.add('show');

                // Masquer le conteneur le temps du chargement
                graphContainer.style.display = 'none';

                // Chargement AJAX
                fetch(row.dataset.urlData, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(r => r.json())
                .then(data => {

                    if (!data.content) {
                        showPlaceholder(@json(trans('global.no_data_available')));
                        return;
                    }

                    // Rendre le conteneur visible AVANT loadGraph
                    graphContainer.style.display = 'block';

                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            const wrapper = document.getElementById('graph-scroll-wrapper');
                            const allocW  = wrapper.offsetWidth;
                            const allocH  = wrapper.offsetHeight;

                            graphContainer.style.width  = allocW + 'px';
                            graphContainer.style.height = allocH + 'px';

                            // bpmn-show.ts pose min-width/min-height sur le SVG dans un rAF
                            // ultérieur, ce qui dilate #content-home (flex-grow:1) et toute
                            // la page. Le MutationObserver intercepte cette mutation dès
                            // qu'elle se produit, efface les min-* et réimpose les dimensions
                            // allouées — le SVG overflowe alors dans le wrapper scrollable.
                            const observer = new MutationObserver((mutations) => {
                                for (const m of mutations) {
                                    if (m.type === 'attributes' && m.attributeName === 'style') {
                                        const svg = m.target;
                                        if (svg.style.minWidth || svg.style.minHeight) {
                                            svg.style.minWidth  = '';
                                            svg.style.minHeight = '';
                                            graphContainer.style.width  = allocW + 'px';
                                            graphContainer.style.height = allocH + 'px';
                                            observer.disconnect();
                                        }
                                    }
                                }
                            });

                            observer.observe(graphContainer, {
                                subtree:         true,
                                attributes:      true,
                                attributeFilter: ['style'],
                            });

                            loadGraph(data.content);
                        });
                    });
                })
                .catch(() => {
                    showPlaceholder(@json(trans('global.no_data_available')));
                });
            });
        });
    </script>
@endsection