@extends('layouts.admin')

@section('title')
    {{ $physicalServer->name }}
@endsection

@section('content')
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.physical-servers.index') }}">
            {{ trans('global.back_to_list') }}
        </a>


        @can('explore_access')

        <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$physicalServer->getUID()}}">
            {{ trans('global.explore') }}
        </a>


        @endcan

        @canEdit($physicalServer)
            <a class="btn btn-info" href="{{ route('admin.physical-servers.edit', $physicalServer->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('physical_server_create')
            <a class="btn btn-warning" href="{{ route('admin.physical-servers.clone', $physicalServer->id) }}">
                {{ trans('global.clone') }}
            </a>
        @endcan

        @can('physical_server_delete')
            <form action="{{ route('admin.physical-servers.destroy', $physicalServer->id) }}" method="POST"
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
            {{ trans('global.show') }} {{ trans('cruds.physicalServer.title') }}
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
             @include('admin.physicalServers._details', [
                 'physicalServer' => $physicalServer,
                 'withLink' => false,
             ])
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans('cruds.physicalServer.fields.configuration') }}
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report">
                <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.physicalServer.fields.cpu') }}
                    </th>
                    <td width="15%">
                        {{ $physicalServer->cpu }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.physicalServer.fields.memory') }}
                    </th>
                    <td width="15%">
                        {{ $physicalServer->memory }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.physicalServer.fields.disk') }}
                    </th>
                    <td width="15%">
                        {{ $physicalServer->disk }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.physicalServer.fields.disk_used') }}
                    </th>
                    <td width="15%">
                        {{ $physicalServer->disk_used }}
                    </td>
                </tr>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.physicalServer.fields.configuration') }}
                    </th>
                    <td colspan="7">
                        {!! $physicalServer->configuration !!}
                    </td>
                </tr>
                </tbody>
            </table>
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
                        {{ trans('cruds.physicalServer.fields.applications') }}
                    </th>
                    <td>
                        @foreach($physicalServer->applications as $application)
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
                </tr>
                </tbody>
            </table>
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans("cruds.menu.logical_infrastructure.title_short") }}
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report">
                <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.physicalServer.fields.operating_system') }}
                    </th>
                    <td width="22%">
                        {{ $physicalServer->operating_system }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.physicalServer.fields.install_date') }}
                    </th>
                    <td width="22%">
                        {{ $physicalServer->install_date }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.physicalServer.fields.update_date') }}
                    </th>
                    <td width="22%">
                        {{ $physicalServer->update_date }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.physicalServer.fields.address_ip') }}
                    </th>
                    <td colspan="5">
                        {{ $physicalServer->address_ip }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.physicalServer.fields.responsible') }}
                    </th>
                    <td colspan="5">
                        {{ $physicalServer->responsible }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.physicalServer.fields.clusters') }}
                    </th>
                    <td>
                        @foreach($physicalServer->clusters as $cluster)
                            @canShow($cluster)
                                <a href="{{ route('admin.clusters.show', $cluster->id) }}">
                                    {{ $cluster->name }}
                                </a>
                            @elsecanShow
                                {{ $cluster->name }}
                            @endcanShow
                            @if(!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                    <th>
                        {{ trans('cruds.physicalServer.fields.logical_servers') }}
                    </th>
                    <td colspan="3">
                        @foreach($physicalServer->logicalServers as $logicalServer)
                            @canShow($logicalServer)
                                <a href="{{ route('admin.logical-servers.show', $logicalServer->id) }}">
                                    {{ $logicalServer->name }}
                                </a>
                            @elsecanShow
                                {{ $logicalServer->name }}
                            @endcanShow
                            @if (!$loop->last)
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
            {{ trans("cruds.menu.physical_infrastructure.title_short") }}
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report">
                <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.physicalServer.fields.site') }}
                    </th>
                    <td width="22%">
                        @if ($physicalServer->site!=null)
                            @canShow($physicalServer->site)
                                <a href="{{ route('admin.sites.show', $physicalServer->site->id) }}">
                                    {{ $physicalServer->site->name ?? '' }}
                                </a>
                            @elsecanShow
                                {{ $physicalServer->site->name ?? '' }}
                            @endcanShow
                        @endif
                    </td>
                    <th width="10%">
                        {{ trans('cruds.physicalServer.fields.building') }}
                    </th>
                    <td width="22%">
                        @if ($physicalServer->building!=null)
                            @canShow($physicalServer->building)
                                <a href="{{ route('admin.buildings.show', $physicalServer->building->id) }}">
                                    {{ $physicalServer->building->name ?? '' }}
                                </a>
                            @elsecanShow
                                {{ $physicalServer->building->name ?? '' }}
                            @endcanShow
                        @endif
                    </td>
                    <th width="10%">
                        {{ trans('cruds.physicalServer.fields.bay') }}
                    </th>
                    <td width="22%">
                        @if ($physicalServer->bay!=null)
                            @canShow($physicalServer->bay)
                                <a href="{{ route('admin.bays.show', $physicalServer->bay->id) }}">
                                    {{ $physicalServer->bay->name ?? '' }}
                                </a>
                            @elsecanShow
                                {{ $physicalServer->bay->name ?? '' }}
                            @endcanShow
                        @endif
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        @include('admin._footer', ['model' => $physicalServer])
    </div>

    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.physical-servers.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>

@endsection
