@extends('layouts.admin')

@section('title')
    {{ trans('cruds.menu.application_flow.title') }}
@endsection

@section('content')
<div class="graph-card-sticky">
    <div class="card mb-3">
        <div class="card-header">
            {{ trans('cruds.menu.application_flow.title') }}
        </div>
        <form action="/admin/report/application_flows">

            <div class="card-body">
                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="col-sm-8" style="max-width: 1200px;">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <td>
                                {{ trans('cruds.applicationBlock.title') }}
                                <div style="padding-bottom: 4px">
                                    <span class="btn btn-info btn-xs select-all"
                                          style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                    <span class="btn btn-info btn-xs deselect-all"
                                          style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                                </div>
                                <select class="form-control select2 " name="applicationBlocks[]"
                                        class="form-control select2"
                                        id="applicationBlocks" multiple onchange="this.form.submit();">
                                    @foreach($all_applicationBlocks as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, Session::get('applicationBlocks')) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                {{ trans('cruds.application.title') }}
                                <div style="padding-bottom: 4px">
                                    <span class="btn btn-info btn-xs select-all"
                                          style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                    <span class="btn btn-info btn-xs deselect-all"
                                          style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                                </div>
                                <select class="form-control select2 " name="applications[]" id="applications"
                                        multiple onchange="this.form.submit();" class="form-control select2">
                                    @foreach($all_applications as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, Session::get('applications')) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                {{ trans('cruds.database.title') }}
                                <div style="padding-bottom: 4px">
                                    <span class="btn btn-info btn-xs select-all"
                                          style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                    <span class="btn btn-info btn-xs deselect-all"
                                          style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                                </div>
                                <select class="form-control select2 " name="databases[]" id="databases" multiple
                                        onchange="this.form.submit();" class="form-control select2">
                                    @foreach($all_databases as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, Session::get('databases')) ? 'selected' : '' }}>{{ $name }}</option>
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

    @canAccess(App\Models\ApplicationFlow::class)
        @if($flows->count()>0)
            <div class="card">
                <div class="card-header">
                    {{ trans('cruds.applicationFlow.title') }}
                </div>

                <div class="card-body">
                    <p>{{ trans('cruds.applicationFlow.description') }}</p>
                    @foreach($flows as $flow)
                    <div class="row">
                        <div class="col">
                            @include('admin.application-flows._details', [
                                'applicationFlow' => $flow,
                                'withLink' => true,
                            ])
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Application::class)
        @if($applications->count()>0)
            <div class="card">
                <div class="card-header">
                    {{ trans('cruds.application.title') }}
                </div>

                <div class="card-body">
                    <p>{{ trans('cruds.application.description') }}</p>
                    @foreach($applications as $application)
                    <div class="row">
                        <div class="col">
                            @include('admin.applications._details', [
                                'application' => $application,
                                'withLink' => true,
                            ])
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\ApplicationService::class)
        @if($applicationServices->count()>0)
            <div class="card">
                <div class="card-header">
                    {{ trans('cruds.applicationService.title') }}
                </div>

                <div class="card-body">
                    <p>{{ trans('cruds.applicationService.description') }}</p>
                    @foreach($applicationServices as $applicationService)
                    <div class="row">
                        <div class="col">
                            @include('admin.applicationServices._details', [
                                'applicationService' => $applicationService,
                                'withLink' => true,
                            ])
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\ApplicationModule::class)
        @if($applicationModules->count()>0)
            <div class="card">
                <div class="card-header">
                    {{ trans('cruds.applicationModule.title') }}
                </div>

                <div class="card-body">
                    <p>{{ trans('cruds.applicationModule.description') }}</p>
                    @foreach($applicationModules as $applicationModule)
                    <div class="row">
                        <div class="col">
                            @include('admin.applicationModules._details', [
                                'applicationModule' => $applicationModule,
                                'withLink' => true,
                            ])
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Database::class)
        @if($databases->count()>0)
            <div class="card">
                <div class="card-header">
                    {{ trans('cruds.database.title') }}
                </div>

                <div class="card-body">
                    <p>{{ trans('cruds.database.description') }}</p>
                    @foreach($databases as $database)
                    <div class="row">
                        <div class="col">
                            @include('admin.databases._details', [
                                'database' => $database,
                                'withLink' => true,
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
        let dotSrc = @json($dotSrc);

        document.addEventListener('graphvizReady', () => {
            window.initGraphvizReport({ dotSrc, engine: @json($engine), images: @json($imageManifest) });
        });
    </script>
    @parent
@endsection