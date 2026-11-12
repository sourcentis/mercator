@extends('layouts.admin')

@section('title')
    {{ trans('cruds.menu.metier.title') }}
@endsection

@section('content')
<div class="graph-card-sticky">
    <div class="card mb-3">
            <div class="card-header">
                {{ trans('cruds.menu.metier.title') }}
            </div>
            <form action="/admin/report/information_system">

                <div class="card-body">
                    @if(session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="col-sm-6">
                        <table class="table table-bordered table-striped"
                               style="max-width: 600px; width:100%">
                            <tr>
                                <td style="width: 50%">
                                    {{ trans('cruds.macroProcessus.title') }} :
                                    <select name="macroprocess" id="macroprocess"
                                            onchange="this.form.process.value='';this.form.submit()"
                                            class="form-control select2">
                                        <option value="">-- All --</option>
                                        @foreach ($all_macroprocess as $macroprocess)
                                            <option value="{{$macroprocess->id}}" {{ Session::get('macroprocess')==$macroprocess->id ? "selected" : "" }}>{{ $macroprocess->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="width: 50%">
                                    {{ trans('cruds.process.title') }} :
                                    <select name="process" id="process" onchange="this.form.submit()"
                                            class="form-control select2">
                                        <option value="">-- All --</option>
                                        @if ($all_process!=null)
                                            @foreach ($all_process as $process)
                                                <option value="{{$process->id}}" {{ Session::get('process')==$process->id ? "selected" : "" }}>{{ $process->name }}</option>
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

        @canAccess(App\Models\MacroProcessus::class)
            @if ($macroProcessuses->count()>0)
                <br>
                <div class="card">
                    <div class="card-header">
                        {{ trans('cruds.macroProcessus.title') }} :
                    </div>
                    <div class="card-body">
                        <p>{{ trans('cruds.macroProcessus.description') }}</p>
                        @foreach($macroProcessuses as $item)
                            <div class="row">
                                <div class="col">
                                    @include('admin.macroProcessuses._details', [
                                        'macroProcessus' => $item,
                                        'withLink' => true,
                                    ])
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endcan
        @canAccess(App\Models\Process::class)
            @if ($processes->count()>0)
                <br>
                <div class="card">
                    <div class="card-header">
                        {{ trans('cruds.process.title') }}
                    </div>
                    <div class="card-body">
                        <p>{{ trans('cruds.process.description') }}</p>
                        @foreach($processes as $process)
                            <div class="row">
                                <div class="col">
                                    @include('admin.processes._details', [
                                        'process' => $process,
                                        'withLink' => true,
                                    ])
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endcan

        @canAccess(App\Models\Activity::class)
            @if ($activities->count()>0)
                <br>
                <div class="card">
                    <div class="card-header">
                        {{ trans('cruds.activity.title') }}
                    </div>
                    <div class="card-body">
                        <p>{{ trans('cruds.activity.description') }}</p>
                        @foreach($activities as $activity)
                            <div class="row">
                                <div class="col">
                                    @include('admin.activities._details', [
                                        'activity' => $activity,
                                        'withLink' => true,
                                    ])
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endcan

        @canAccess(App\Models\Operation::class)
            @if ($operations->count()>0)
                <br>
                <div class="card">
                    <div class="card-header">
                        {{ trans('cruds.operation.title') }}
                    </div>
                    <div class="card-body">
                        <p>{{ trans('cruds.operation.description') }}</p>
                        @foreach($operations as $operation)
                            <div class="row">
                                <div class="col">
                                    @include('admin.operations._details', [
                                        'operation' => $operation,
                                        'withLink' => true,
                                    ])
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endcan

        @canAccess(App\Models\Task::class)
            @if ($tasks->count()>0)
                <br>
                <div class="card">
                    <div class="card-header">
                        {{ trans('cruds.task.title') }}
                    </div>
                    <div class="card-body">
                        <p>{{ trans('cruds.task.description') }}</p>
                        @foreach($tasks as $task)
                            <div class="row">
                                <div class="col">
                                    @include('admin.tasks._details', [
                                        'task' => $task,
                                        'withLink' => true,
                                    ])
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endcan

        @canAccess(App\Models\Actor::class)
            @if ($actors->count()>0)
                <br>
                <div class="card">
                    <div class="card-header">
                        {{ trans('cruds.actor.title') }}
                    </div>
                    <div class="card-body">
                        <p>{{ trans('cruds.actor.description') }}</p>
                        @foreach($actors as $actor)
                            <div class="row">
                                <div class="col">
                                    @include('admin.actors._details', [
                                        'actor' => $actor,
                                        'withLink' => true,
                                    ])
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endcan

        @canAccess(App\Models\Information::class)
            @if ($informations->count()>0)
                <br>
                <div class="card">
                    <div class="card-header">
                        {{ trans('cruds.information.title') }}
                    </div>
                    <div class="card-body">
                        <p>{{ trans('cruds.information.description') }}</p>
                        @foreach($informations as $information)
                            <div class="row">
                                <div class="col">
                                    @include('admin.information._details', [
                                        'information' => $information,
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