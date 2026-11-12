@extends('layouts.admin')
@section('content')
    <div class="form-group p-0">
        <a class="btn btn-default" href="{{ route('admin.bpmn.index') }}">
            {{ trans('global.back_to_list') }}
        </a>

        @can('graph_edit')
            <a class="btn btn-info" href="{{ route('admin.bpmn.edit', $graph->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcan

        @can('graph_create')
            <a class="btn btn-warning" href="{{ route('admin.bpmn.clone', $graph->id) }}">
                {{ trans('global.clone') }}
            </a>
        @endcan

        @can('graph_delete')
            <form action="{{ route('admin.bpmn.destroy', $graph->id) }}" method="POST"
                  onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan
    </div>

    <div class="card">
        <div class="card-header">
            BPMN - {{ $graph->name }}
        </div>
        <div id="graph-container"
             style="
             position: relative;
             overflow: auto;
             width: 100%;
             min-height: 200px;
             height: auto;
             cursor: default;
             touch-action: none;">
        </div>
    </div>

            @can('macro_processus_access')
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
                                        @include('mercator::admin.macroProcessuses._details', [
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
            @can('process_access')
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
                                        @include('mercator::admin.processes._details', [
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

            @can('activity_access')
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
                                        @include('mercator::admin.activities._details', [
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

            @can('operation_access')
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
                                        @include('mercator::admin.operations._details', [
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

            @can('task_access')
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
                                        @include('mercator::admin.tasks._details', [
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

            @can('actor_access')
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
                                        @include('mercator::admin.actors._details', [
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

            @can('information_access')
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
                                        @include('mercator::admin.information._details', [
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



    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.bpmn.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>
@endsection

@section('styles')
    @vite('resources/css/mapping.css')
{{-- No @font-face here anymore: @sourcentis/bpmn-editor bundles and injects
     its own BPMN glyph font; this page has no other element depending on
     the "bpmn" font-family. --}}
@endsection

@section('scripts')
@vite('resources/BPMN/bpmn-show.ts')

<script @nonce>
document.addEventListener("DOMContentLoaded", function () {
@if (!empty($graph->content))
    const xmlContent = @json($graph->content);
    loadGraph(xmlContent);
@endif
});
</script>
@endsection
