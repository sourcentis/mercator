@extends('layouts.admin')

@section('title')
    {{ $process->name }}
@endsection

@section('content')

<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.processes.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$process->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($process)
        <a class="btn btn-info" href="{{ route('admin.processes.edit', $process->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('process_delete')
        <form action="{{ route('admin.processes.destroy', $process->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan

</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.process.title') }}
    </div>
    <div class="card-body">
        @include('admin.processes._details', [
            'process' => $process,
            'withLink' => false,
        ])
    </div>
    @include('admin._footer', ['model' => $process])
</div>

<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.processes.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
