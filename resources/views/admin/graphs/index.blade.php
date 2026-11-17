@extends('layouts.admin')

@section('title')
    {{ trans('cruds.graph.index') }}
@endsection

@section('content')
@can('graph_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.graphs.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.graph.title_singular') }}
            </a>
        </div>
    </div>
@endcan


<div class="row g-3" style="height: calc(100vh - 180px);">

    {{-- Colonne gauche : liste --}}
    <div class="col-md-5  d-flex flex-column" style="height: 100%;">

        <div class="card flex-grow-1" style="overflow: hidden; display: flex; flex-direction: column;">
            <div class="card-header py-2 px-3">
                {{ trans('cruds.graph.index') }}
            </div>
            <div class="card-body p-2" style="overflow-y: auto; flex: 1;">
                <table id="dataTable" class="table table-bordered table-striped table-hover datatable w-100">
                    <thead>
                        <tr>
                            <th width="10"></th>
                            <th>{{ trans('cruds.graph.fields.name') }}</th>
                            <th>{{ trans('cruds.graph.fields.type') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($graphs as $graph)
                        <tr data-entry-id="{{ $graph->id }}"
                            data-name="{{ e($graph->name) }}"
                            data-type="{{ e($graph->type) }}"
                            data-url-clone="{{ route('admin.graphs.clone', $graph->id) }}">
                            <td></td>
                            <td>
                            <x-show-link :model="$graph" />
                            </td>
                            <td>{{ $graph->type ?? '' }}</td>
                            <td class="text-end" nowrap>
                                @canEdit($graph)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.graphs.edit', $graph->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('graph_delete')
                                    <form action="{{ route('admin.graphs.destroy', $graph->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
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

    {{-- Colonne droite : visualisation --}}
    <div class="col-md-7 d-flex flex-column" style="height: 100%;">
        <div class="card flex-grow-1" style="display: flex; flex-direction: column; overflow: hidden;">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <span id="panel-title" class="text-muted small">
                    {{ trans('cruds.graph.title_singular') }}
                </span>
                <div id="panel-actions" class="d-none gap-1 align-items-center">
                    @can('graph_create')
                    <a id="btn-clone" href="#" class="btn btn-xs btn-warning">
                        {{ trans('global.clone') }}
                    </a>
                    @endcan
                    <button id="download-btn" type="button" class="btn btn-xs btn-secondary" title="Télécharger en SVG">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
            </div>

            <div id="graph-container"
                 style="position: relative; overflow: auto; width: 100%; flex: 1;
                        cursor: default; touch-action: none;">
                <div id="graph-placeholder"
                     class="d-flex flex-column align-items-center justify-content-center text-muted"
                     style="gap: 0.5rem;">
                    <i class="fas fa-hand-pointer fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
@vite('resources/css/mapping.css')
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
<script @nonce>
let _nodes = new Map();
@foreach($nodes as $node)
    _nodes.set("{{ $node['id'] }}", { type: "{{ $node['type'] }}" });
@endforeach

const _graphContents = {
@foreach($graphs as $graph)
    "{{ $graph->id }}": @json($graph->content),
@endforeach
};

const panelTitle   = document.getElementById('panel-title');
const panelActions = document.getElementById('panel-actions');
const btnClone     = document.getElementById('btn-clone');
const cloneGraphId = document.getElementById('clone-graph-id');
const placeholder  = document.getElementById('graph-placeholder');
</script>

<script @nonce>
@include('partials.datatable', [
    'id'        => '#dataTable',
    'title'     => trans('cruds.graph.title_singular'),
    'URL'       => route('admin.graphs.massDestroy'),
    'canDelete' => auth()->user()->can('graph_delete'),
    'hasButtons' => false
])
</script>

<script @nonce>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('#dataTable tbody').addEventListener('click', function (e) {
        const row = e.target.closest('tr');
        if (!row) return;

        // Ne pas interférer avec les liens, boutons et checkboxes
        if (e.target.closest('a, button, input[type="submit"], input[type="checkbox"]')) return;

        const id = row.dataset.entryId;
        if (!id || !_graphContents[id]) return;

        document.querySelectorAll('#dataTable tbody tr').forEach(r => r.classList.remove('selected'));
        row.classList.add('selected');


        panelTitle.textContent = row.querySelector('td:nth-child(2)').textContent.trim();
        if (btnClone)    btnClone.href          = row.dataset.urlClone;
        if (cloneGraphId) cloneGraphId.textContent = `(${id})`;

        panelActions.classList.remove('d-none');
        panelActions.classList.add('show');
        placeholder.style.display = 'none';

        loadGraph(_graphContents[id]);
    });
});
</script>
@vite('resources/graphs/map.show.ts')
@endsection