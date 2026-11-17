@extends('layouts.admin')

@section('title')
    {{ $backup->name }}
@endsection

@section('content')
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.backups.index') }}">{{ trans('global.back_to_list') }}</a>


        @can('explore_access')

        <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$backup->getUID()}}">
            {{ trans('global.explore') }}
        </a>


        @endcan

        @canEdit($backup)
            <a class="btn btn-info" href="{{ route('admin.backups.edit', $backup->id) }}">{{ trans('global.edit') }}</a>
        @endcanEdit

        @can('backup_delete')
            <form action="{{ route('admin.backups.destroy', $backup->id) }}" method="POST"
                  data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan
    </div>

    <div class="card">
        <div class="card-header">{{ trans('global.show') }} {{ trans('cruds.backup.title_singular') }}</div>
        <div class="card-body">
            @include('admin.backups._details', ['backup' => $backup, 'withLink' => false])
        </div>
        @include('admin._footer', ['model' => $backup])
    </div>

    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.backups.index') }}">{{ trans('global.back_to_list') }}</a>
    </div>
@endsection
