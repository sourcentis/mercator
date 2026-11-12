@extends('layouts.admin')

@section('title')
    {{ trans('cruds.zone.title') }}
@endsection

@section('content')
<div class="graph-card-sticky">
    <div class="card mb-3">
        <div class="card-header">
            {{ trans('cruds.zone.title') }}
        </div>
        <form action="{{ route('admin.report.view.security-zones') }}" method="GET" id="filter-form">
            <input type="hidden" name="filter" value="1">
            <div class="card-body">

                <div class="col-sm-6">
                    <table class="table table-bordered table-striped" style="max-width: 700px; width: 100%;">
                        <tr>
                            <td>
                                {{ trans('cruds.zone.title') }} :
                                <select name="zones[]" id="zones" class="form-control select2" multiple
                                        data-autosubmit>
                                    @foreach($allZones as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, $selectedIds) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="graph-container">
                    <div id="graph" class="graphviz">
                        @include('admin.reports._graph_too_large')
                    </div>
                    <div class="graph-resize-handle"></div>
                </div>

                <div class="row p-1">
                    <div class="col-4">
                        @php
                            $engines = ['dot', 'fdp', 'osage', 'circo'];
                            $engine  = request()->get('engine', 'dot');
                        @endphp

                        <label class="inline-flex items-center ps-1 pe-1">
                            <a href="#" id="downloadSvg"><i class="bi bi-download"></i></a>
                        </label>
                        <label class="inline-flex items-center">Rendu :</label>
                        @foreach($engines as $value)
                            <label class="inline-flex items-center ps-1">
                                <input type="radio" name="engine" value="{{ $value }}"
                                       @checked($engine === $value)>
                                <span>{{ $value }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- Graphe trop grand : les objets ne sont pas listés non plus --}}
@if(empty($graphTooLarge))
<div class="report-scroll-area">

    @canAccess(App\Models\Zone::class)
        @if($zones->count() > 0)
        <br>
        <div class="card">
            <div class="card-header">{{ trans('cruds.zone.title') }}</div>
            <div class="card-body">
                <p>{{ trans('cruds.zone.description') }}</p>
                @foreach($zones as $zone)
                    <div class="row">
                        <div class="col">
                            @include('admin.zones._details', ['zone' => $zone, 'withLink' => true])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    @endcan

    @canAccess(App\Models\Building::class)
        @if($buildings->count() > 0)
        <br>
        <div class="card">
            <div class="card-header">{{ trans('cruds.building.title') }}</div>
            <div class="card-body">
                <p>{{ trans('cruds.building.description') }}</p>
                @foreach($buildings as $building)
                    <div class="row">
                        <div class="col">
                            @include('admin.buildings._details', ['building' => $building, 'withLink' => true])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    @endcan

    @canAccess(App\Models\AdminUser::class)
        @if($adminUsers->count() > 0)
        <br>
        <div class="card">
            <div class="card-header">{{ trans('cruds.adminUser.title') }}</div>
            <div class="card-body">
                @foreach($adminUsers as $adminUser)
                    <div class="row">
                        <div class="col">
                            @include('admin.adminUser._details', ['adminUser' => $adminUser, 'withLink' => true])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    @endcan

</div>
@endif
@endsection

@section('scripts')
@vite(['resources/js/graphviz.js'])
<script @nonce>
let dotSrc = @json($dotSrc);

document.addEventListener('graphvizReady', () => {
    window.initGraphvizReport({ dotSrc, engine: @json($engine), images: @json($imageManifest) });

    // Download SVG
    document.getElementById('downloadSvg').addEventListener('click', (e) => {
        e.preventDefault();
        const svg = document.getElementById('graph').querySelector('svg');
        if (!svg) return;
        const blob = new Blob([svg.outerHTML], { type: 'image/svg+xml' });
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = 'security-zones.svg';
        a.click();
        URL.revokeObjectURL(url);
    });
});
</script>
@parent
@endsection
