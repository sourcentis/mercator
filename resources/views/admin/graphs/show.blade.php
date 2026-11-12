@extends('layouts.admin')

@section('title')
    {{ trans('cruds.graph.title_singular') }} {{ $graph->name }}
@endsection

@section('content')

<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.graphs.index') }}">
        {{ trans('global.back_to_list') }}
    </a>

    @canEdit($graph)
        <a class="btn btn-info" href="{{ route('admin.graphs.edit', $graph->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('graph_create')
        <a class="btn btn-warning" href="{{ route('admin.graphs.clone', $graph->id) }}">
            {{ trans('global.clone') }}
        </a>
    @endcan

    @can('graph_delete')
        <form action="{{ route('admin.graphs.destroy', $graph->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <span>{{ trans('cruds.graph.title_singular') }} - {{ $graph->name }}</span>
        <i id="download-btn" title="Télécharger en SVG" class="mapping-icon bi bi-download" style="cursor: pointer;"></i>
    </div>
    <div id="graph-container"
         style="
         position: relative;
         overflow: hidden;
         width: 100%;
         height: 800px;
         cursor: default;
         touch-action: none;">
    </div>
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.graphs.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection

@section('styles')
@vite('resources/css/mapping.css')
@endsection

@section('scripts')
<script @nonce>
// TODO : optimize me
let _nodes = new Map();
@foreach($nodes as $node)
    _nodes.set( "{{ $node["id"] }}" ,{ type: "{{ $node["type"] }}"});
@endforeach

document.addEventListener("DOMContentLoaded", function () {
    const xmlContent = @json($graph->content);
    loadGraph(xmlContent);
});
</script>
@vite('resources/graphs/map.show.ts')
@endsection
