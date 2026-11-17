@extends('layouts.admin')

@section('title')
    {{ trans('cruds.menu.ecosystem.title') }}
@endsection

@section('content')
<div class="graph-card-sticky">
    <div class="card mb-3">
        <form action="/admin/report/ecosystem">
            <div class="card-header">
                {{ trans('cruds.menu.ecosystem.title') }}
            </div>

            <div class="card-body">
                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="col-sm-6" style="max-width: 800px;">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <td>
                                <label for="entities">{{ trans('cruds.entity.filters.title.start') }}</label>
                                <select name="entities[]" id="entities" class="form-control select2" multiple>
                                    @foreach($all_entities as $id => $name)
                                        <option value="{{ $id }}" @selected(in_array($id, $selectedEntities, true))>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="graph-container">
                    <div class="graphviz" id="graph">
                        @include('admin.reports._graph_too_large')
                    </div>
                    <div class="graph-resize-handle"></div>
                </div>
                <div class="row p-1">
                    <div class="col-4">
                        @php($engines=["dot", "fdp",  "osage", "circo" ])
                        @php($engine = request()->get('engine', 'dot'))
                        <label class="inline-flex items-center ps-1 pe-1">
                            <a href="#" id="downloadSvg"><i class="bi bi-download"></i></a>
                        </label>

                        <label class="inline-flex items-center">
                            Rendu :
                        </label>
                        @foreach($engines as $value)
                            <label class="inline-flex items-center ps-1">
                                <input
                                        type="radio"
                                        name="engine"
                                        value="{{ $value }}"
                                        @checked($engine === $value)
                                />
                                <span>{{ $value }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>{{-- .graph-card-sticky --}}

{{-- Graphe trop grand : les objets ne sont pas listés non plus --}}
@if(empty($graphTooLarge))
<div class="report-scroll-area">
    @if($entities->count()>0)
        <div class="card">
            <div class="card-header">
                {{ trans('cruds.entity.title') }}
            </div>
            <div class="card-body">
                <p>{{ trans('cruds.entity.description') }}</p>
                @foreach($entities as $entity)
                    <div class="row">
                        <div class="col">
                            @include('admin.entities._details', [
                                'entity' => $entity,
                                'withLink' => true,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($relations->count()>0)
        <div class="card">
            <div class="card-header">
                {{ trans('cruds.relation.title') }}
            </div>
            <div class="card-body">
                <p>{{ trans('cruds.relation.description') }}</p>
                @foreach($relations as $relation)
                    <div class="row">
                        <div class="col">
                            @include('admin.relations._details', [
                                'relation' => $relation,
                                'withLink' => true,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>{{-- .report-scroll-area --}}
@endif
@endsection

@section('scripts')
@vite(['resources/js/graphviz.js'])
<script id="dot-input" @nonce>
let dotSrc = @json($dotSrc);

// Select2 déclenche un événement jQuery : on l'écoute via jQuery, une fois
// les modules Vite (jQuery) chargés
document.addEventListener('DOMContentLoaded', () => {
    $('#entities').on('change', function () {
        this.form.submit();
    });
});

document.addEventListener('graphvizReady', () => {
    const images = @json($imageManifest);

    window.initGraphvizReport({ dotSrc, engine: @json($engine), images: images });
});
</script>
@endsection