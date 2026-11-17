@extends('layouts.admin')

@section('title')
    {{ $storageDevice->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.storage-devices.index') }}">
        {{ trans('global.back_to_list') }}
    </a>

    @can('explore_access')
    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$storageDevice->getUID()}}">
        {{ trans('global.explore') }}
    </a>
    @endcan

    @canEdit($storageDevice)
        <a class="btn btn-info" href="{{ route('admin.storage-devices.edit', $storageDevice->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('storage_device_delete')
        <form action="{{ route('admin.storage-devices.destroy', $storageDevice->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>
<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.storageDevice.title') }}
    </div>
    <div class="card-body">
        @include('admin.storageDevices._details', [
            'storageDevice' => $storageDevice,
            'withLink' => false,
        ])
    </div>
        @can('backup_show')
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans("cruds.backup.title") }}
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
            @if ($storageDevice->backups->count()>0)
            <div class="row">
                <div class="col-8">
                    <table class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                <th width="25%">{{ trans('cruds.backup.fields.name') }}</th>
                                <th width="25%">{{ trans('cruds.logicalServer.title') }}</th>
                                <th width="20%">{{ trans('cruds.backup.frequency') }}</th>
                                <th width="15%">{{ trans('cruds.backup.cycle') }}</th>
                                <th width="15%">{{ trans('cruds.backup.retention') }}</th>
                            </tr>
                            @foreach($storageDevice->backups as $backup)
                            <tr>
                                <td>{{ $backup->name }}</td>
                                <td>
                                    @foreach($backup->logicalServers as $server)
                                        @canShow($server)
                                            <a href="{{ route('admin.logical-servers.show', $server->id) }}">{{ $server->name }}</a>
                                        @elsecanShow
                                            {{ $server->name }}
                                        @endcanShow
                                        @if (!$loop->last), @endif
                                    @endforeach
                                </td>
                                <td>{{ $backup->backup_frequency ? trans("cruds.backup.frequencies.{$backup->backup_frequency}") : '' }}</td>
                                <td>{{ $backup->backup_cycle ? trans("cruds.backup.cycles.{$backup->backup_cycle}") : '' }}</td>
                                <td>{{ $backup->backup_retention ? $backup->backup_retention . ' ' . trans("cruds.backup.retention_unit") : '' }}</td>
                             </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        @endcan
    @include('admin._footer', ['model' => $storageDevice])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.storage-devices.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
