@extends('layouts.admin')

@section('title')
    {{ $task->name }}
@endsection

@section('content')
<div class="form-group">
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.tasks.index') }}">
            {{ trans('global.back_to_list') }}
        </a>


        @can('explore_access')

        <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$task->getUID()}}">
            {{ trans('global.explore') }}
        </a>


        @endcan

        @canEdit($task)
            <a class="btn btn-info" href="{{ route('admin.tasks.edit', $task->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('task_delete')
            <form action="{{ route('admin.tasks.destroy', $task->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan

    </div>
</div>
<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.task.title') }}
    </div>
    <div class="card-body">
        @include('admin.tasks._details', [
            'task' => $task,
            'withLink' => false,
        ])
    </div>
    @include('admin._footer', ['model' => $task])
</div>

    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.tasks.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>

@endsection
