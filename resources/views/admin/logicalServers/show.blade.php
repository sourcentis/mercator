@extends('layouts.admin')

@section('title')
    {{ $logicalServer->name }}
@endsection

@section('content')
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.logical-servers.index') }}">
            {{ trans('global.back_to_list') }}
        </a>


        @can('explore_access')

        <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$logicalServer->getUID()}}">
            {{ trans('global.explore') }}
        </a>


        @endcan

        @canEdit($logicalServer)
            <a class="btn btn-info" href="{{ route('admin.logical-servers.edit', $logicalServer->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('logical_server_delete')
            <form action="{{ route('admin.logical-servers.destroy', $logicalServer->id) }}" method="POST"
                  data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan
    </div>

    <div class="card">
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans('cruds.logicalServer.title_singular') }}
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
            @include('admin.logicalServers._details', [
                'logicalServer' => $logicalServer,
                'withLink' => false,
            ])
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans("cruds.menu.application.title_short") }}
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report">
                <tbody>
                <tr>
                    <th width="10%">
                        <dt>{{ trans('cruds.logicalServer.fields.applications') }}</dt>
                    </th>
                    <td width="40%">
                        @foreach($logicalServer->applications as $application)
                            @canShow($application)
                                <a href="{{ route('admin.applications.show', $application->id) }}">
                                    {{ $application->name }}
                                </a>
                            @elsecanShow
                                {{ $application->name }}
                            @endcanShow
                            @if(!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                    <th width="10%">
                        <dt>{{ trans('cruds.logicalServer.fields.databases') }}</dt>
                    </th>
                    <td width="40%">
                        @foreach($logicalServer->databases as $database)
                            @canShow($database)
                                <a href="{{ route('admin.databases.show', $database->id) }}">
                                    {{ $database->name }}
                                </a>
                            @elsecanShow
                                {{ $database->name }}
                            @endcanShow
                            @if(!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans("cruds.menu.administration.title_short") }}
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report">
                <tbody>
                <tr>
                    <th width="10%">
                        <dt>{{ trans('cruds.logicalServer.fields.domain') }}</dt>
                    </th>
                    <td>
                        @if ($logicalServer->domain!==null)
                            @canShow($logicalServer->domain)
                                <a href="{{ route('admin.domains.show', $logicalServer->domain_id) }}">
                                    {{ $logicalServer->domain->name }}
                                </a>
                            @elsecanShow
                                {{ $logicalServer->domain->name }}
                            @endcanShow
                        @endif
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans("cruds.menu.physical_infrastructure.title_short") }}
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report">
                <tbody>
                <tr>
                    <th width="10%">
                        <dt>{{ trans('cruds.logicalServer.fields.servers') }}</dt>
                    </th>
                    <td>
                        @foreach($logicalServer->physicalServers as $server)
                            @canShow($server)
                                <a href="{{ route('admin.physical-servers.show', $server->id) }}">
                                    {{ $server->name }}
                                </a>
                            @elsecanShow
                                {{ $server->name }}
                            @endcanShow
                            @if(!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        @can('backup_show')
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans("cruds.backup.title") }}
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
            @if ($logicalServer->backups->count()>0)
            <div class="row">
                <div class="col-8">
                    <table class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                <th width="25%">{{ trans('cruds.backup.fields.name') }}</th>
                                <th width="25%">{{ trans('cruds.storageDevice.title_singular') }}</th>
                                <th width="20%">{{ trans('cruds.backup.frequency') }}</th>
                                <th width="15%">{{ trans('cruds.backup.cycle') }}</th>
                                <th width="15%">{{ trans('cruds.backup.retention') }}</th>
                            </tr>
                            @foreach($logicalServer->backups as $backup)
                            <tr>
                                <td>{{ $backup->name }}</td>
                                <td>
                                    @foreach($backup->storageDevices as $device)
                                        @canShow($device)
                                            <a href="{{ route('admin.storage-devices.show', $device->id) }}">{{ $device->name }}</a>
                                        @elsecanShow
                                            {{ $device->name }}
                                        @endcanShow
                                        @if(!$loop->last), @endif
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
        @include('admin._footer', ['model' => $logicalServer])
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.logical-servers.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>
@endsection
