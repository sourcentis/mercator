@extends('layouts.admin')

@section('title')
    {{ trans("cruds.menu.application.title") }}
@endsection

@section('content')
@php($hasMultiplePerimeters = auth()->user()->hasMultiplePerimeters())
<div class="graph-card-sticky">
    <div class="card mb-3">
        <div class="card-header">
            {{ trans("cruds.menu.application.title") }}
        </div>
        <form action="/admin/report/applications">

            <div class="card-body">
                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="col-sm-5">
                    <table class="table table-bordered table-striped table-hover table-select">
                        <tr>
                            <td style="min-width: 280px;">
                                {{ trans("cruds.applicationBlock.title") }} :
                                <select name="applicationBlock" id="applicationBlock"
                                        class="form-control select2"
                                        data-autosubmit data-autosubmit-clear="application">
                                    <option value="">-- All --</option>
                                    @foreach ($all_applicationBlocks as $applicationBlock)
                                        <option value="{{$applicationBlock->id}}" {{ Session::get('applicationBlock')==$applicationBlock->id ? "selected" : "" }}>{{ $applicationBlock->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="min-width: 280px;">
                                {{ trans("cruds.application.title") }} :
                                <select name="application" id="application" class="form-control select2"
                                        data-autosubmit>
                                    <option value="">-- All --</option>
                                    @if ($all_applications!=null)
                                        @foreach ($all_applications as $application)
                                            <option value="{{$application->id}}" {{ Session::get('application')==$application->id ? "selected" : "" }}>{{ $application->name }}</option>
                                        @endforeach
                                    @endif
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
                                >
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
@canAccess(App\Models\ApplicationBlock::class)
    @if ($applicationBlocks->count()>0)
        <br>
        <div class="card">
            <div class="card-header">
                {{ trans("cruds.applicationBlock.title") }}
            </div>

            <div class="card-body">
                <p>{{ trans("cruds.applicationBlock.description") }}</p>
                @foreach($applicationBlocks as $applicationBlock)
                    <div class="row">
                        <div class="col">
                            @include('admin.applicationBlocks._details', [
                                'applicationBlock' => $applicationBlock,
                                'withLink' => true,
                                'hasMultiplePerimeters' => $hasMultiplePerimeters,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endcan

@canAccess(App\Models\Application::class)
    @if ($applications->count()>0)
        <br>
        <div class="card">
            <div class="card-header">
                {{ trans("cruds.application.title") }}
            </div>

            <div class="card-body">
                <p>{{ trans("cruds.application.description") }}</p>
                @foreach($applications as $application)
                    <div class="row">
                        <div class="col">
                            @include('admin.applications._details', [
                                'application' => $application,
                                'withLink' => true,
                                'hasMultiplePerimeters' => $hasMultiplePerimeters,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endcan

@canAccess(App\Models\ApplicationService::class)
    @if ($applicationServices->count()>0)
        <br>
        <div class="card">
            <div class="card-header">
                {{ trans("cruds.applicationService.title") }}
            </div>

            <div class="card-body">
                <p>{{ trans("cruds.applicationService.description") }}</p>
                @foreach($applicationServices as $applicationService)
                    <div class="row">
                        <div class="col">
                            @include('admin.applicationServices._details', [
                                'applicationService' => $applicationService,
                                'withLink' => true,
                                'hasMultiplePerimeters' => $hasMultiplePerimeters,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endcan

@canAccess(App\Models\ApplicationModule::class)
    @if ($applicationModules->count()>0)
        <br>
        <div class="card">
            <div class="card-header">
                {{ trans("cruds.applicationModule.title") }}
            </div>

            <div class="card-body">
                <p>{{ trans("cruds.applicationModule.description") }}</p>
                @foreach($applicationModules as $applicationModule)
                    <div class="row">
                        <div class="col">
                            @include('admin.applicationModules._details', [
                                'applicationModule' => $applicationModule,
                                'withLink' => true,
                                'hasMultiplePerimeters' => $hasMultiplePerimeters,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endcan

@canAccess(App\Models\Database::class)
    @if ($databases->count()>0)
        <br>
        <div class="card">
            <div class="card-header">
                {{ trans("cruds.database.title") }}
            </div>

            <div class="card-body">
                <p>{{ trans("cruds.database.description") }}</p>
                @foreach($databases as $database)
                    <div class="row">
                        <div class="col">
                            @include('admin.databases._details', [
                                'database' => $database,
                                'withLink' => true,
                                'hasMultiplePerimeters' => $hasMultiplePerimeters,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endcan

@canAccess(App\Models\ApplicationFlow::class)
    @if ($flows->count()>0)
        <br>
        <div class="card">
            <div class="card-header">
                {{ trans("cruds.applicationFlow.title") }}
            </div>

            <div class="card-body">
                <p>{{ trans("cruds.applicationFlow.description") }}</p>
                @foreach($flows as $flow)
                    <div class="row">
                        <div class="col">

                            @include('admin.application-flows._details', [
                                'flux' => $flow,
                                'withLink' => true,
                                'hasMultiplePerimeters' => $hasMultiplePerimeters,
                            ])
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
const dotSrc = @json($dotSrc);

document.addEventListener('graphvizReady', () => {
    window.initGraphvizReport({ dotSrc, engine: @json($engine), images: @json($imageManifest) });
});
</script>
@endsection