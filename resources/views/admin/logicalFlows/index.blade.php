@extends('layouts.admin')

@section('title')
    {{ trans('cruds.logicalFlow.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('logical_flow_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.logical-flows.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.logicalFlow.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.logicalFlow.title_singular') }} {{ trans('global.list') }}
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable" class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.logicalFlow.fields.name') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.logicalFlow.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalFlow.fields.chain') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalFlow.fields.interface') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalFlow.fields.router') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalFlow.fields.priority') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalFlow.fields.protocol') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalFlow.fields.source_ip_range') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalFlow.fields.source_port') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalFlow.fields.dest_ip_range') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalFlow.fields.dest_port') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalFlow.fields.action') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($logicalFlows as $logicalFlow)
                        <tr data-entry-id="{{ $logicalFlow->id }}">
                            <td>

                            </td>
                            <td>
                                <a href="{{ route('admin.logical-flows.show', $logicalFlow->id) }}">
                                    {{ $logicalFlow->name ?? "NONAME" }}
                                </a>
                            </td>
                            <td>
                                {!! $logicalFlow->description !!}
                            </td>
                            <td>
                                {{ $logicalFlow->chain }}
                            </td>
                            <td>
                                {{ $logicalFlow->interface }}
                            </td>
                            <td>
                                @if ($logicalFlow->router_id !== null)
                                    <a href="{{ route('admin.routers.show', $logicalFlow->router_id) }}">
                                        {{ $logicalFlow->router->name }}
                                    </a>
                                @endif
                            </td>
                            <td>
                                {{ $logicalFlow->priority }}
                            </td>
                            <td>
                                {{ $logicalFlow->protocol }}
                            </td>
                            <td>
                                @if ($logicalFlow->source_ip_range!==null)
                                    {{ $logicalFlow->source_ip_range }}
                                @elseif ($logicalFlow->logicalServerSource!==null)
                                    {{ $logicalFlow->logicalServerSource->address_ip }}
                                    (
                                    <a href="{{ route('admin.logical-servers.show',$logicalFlow->logicalServerSource->id) }}">
                                        {{ $logicalFlow->logicalServerSource->name }}
                                    </a>)
                                @elseif ($logicalFlow->peripheralSource!==null)
                                    {{ $logicalFlow->peripheralSource->address_ip }}
                                    (<a href="{{ route('admin.peripherals.show',$logicalFlow->peripheralSource->id) }}">
                                        {{ $logicalFlow->peripheralSource->name }}
                                    </a>)
                                @elseif ($logicalFlow->physicalServerSource!==null)
                                    {{ $logicalFlow->physicalServerSource->address_ip ?? "" }}
                                    (
                                    <a href="{{ route('admin.physical-servers.show',$logicalFlow->physicalServerSource->id) }}">
                                        {{ $logicalFlow->physicalServerSource->name }}
                                    </a>)
                                @elseif ($logicalFlow->storageDeviceSource!==null)
                                    {{ $logicalFlow->storageDeviceSource->address_ip }}
                                    (
                                    <a href="{{ route('admin.storage-devices.show',$logicalFlow->storageDeviceSource->id) }}">
                                        {{ $logicalFlow->storageDeviceSource->name }}
                                    </a>)
                                @elseif ($logicalFlow->workstationSource!==null)
                                    {{ $logicalFlow->workstationSource->address_ip }}
                                    (
                                    <a href="{{ route('admin.workstations.show',$logicalFlow->workstationSource->id) }}">
                                        {{ $logicalFlow->workstationSource->name }}
                                    </a>)
                                @elseif ($logicalFlow->physicalSecurityDeviceSource!==null)
                                    {{ $logicalFlow->physicalSecurityDeviceSource->address_ip }}
                                    (
                                    <a href="{{ route('admin.physical-security-devices.show',$logicalFlow->physicalSecurityDeviceSource->id) }}">
                                        {{ $logicalFlow->physicalSecurityDeviceSource->name }}
                                    </a>)
                                @elseif ($logicalFlow->subnetworkSource!==null)
                                    {{ $logicalFlow->subnetworkSource->address }}
                                    (
                                    <a href="{{ route('admin.subnetworks.show',$logicalFlow->subnetworkSource->id) }}">
                                        {{ $logicalFlow->subnetworkSource->name }}
                                    </a>)
                                @endif
                            </td>
                            <td>
                                {{ $logicalFlow->source_port ?? "ANY"  }}
                            </td>
                            <td>
                                @if ($logicalFlow->dest_ip_range!==null)
                                    {{ $logicalFlow->dest_ip_range }}
                                @elseif ($logicalFlow->logicalServerDest!==null)
                                    {{ $logicalFlow->logicalServerDest->address_ip }}
                                    (
                                    <a href="{{ route('admin.logical-servers.show',$logicalFlow->logicalServerDest->id) }}">
                                        {{ $logicalFlow->logicalServerDest->name }}
                                    </a>)
                                @elseif ($logicalFlow->peripheralDest!==null)
                                    {{ $logicalFlow->peripheralDest->address_ip }}
                                    (<a href="{{ route('admin.peripherals.show',$logicalFlow->peripheralDest->id) }}">
                                        {{ $logicalFlow->peripheralDest->name }}
                                    </a>)
                                @elseif ($logicalFlow->physicalServerDest!==null)
                                    {{ $logicalFlow->physicalServerDest->address_ip }}
                                    (
                                    <a href="{{ route('admin.physical-servers.show',$logicalFlow->physicalServerDest->id) }}">
                                        {{ $logicalFlow->physicalServerDest->name }}
                                    </a>)
                                @elseif ($logicalFlow->storageDeviceDest!==null)
                                    {{ $logicalFlow->storageDeviceDest->address_ip }}
                                    (
                                    <a href="{{ route('admin.storage-devices.show',$logicalFlow->storageDeviceDest->id) }}">
                                        {{ $logicalFlow->storageDeviceDest->name }}
                                    </a>)
                                @elseif ($logicalFlow->workstationDest!==null)
                                    {{ $logicalFlow->workstationDest->address_ip }}
                                    (<a href="{{ route('admin.workstations.show',$logicalFlow->workstationDest->id) }}">
                                        {{ $logicalFlow->workstationDest->name }}
                                    </a>)
                                @elseif ($logicalFlow->physicalSecurityDeviceDest!==null)
                                    {{ $logicalFlow->physicalSecurityDeviceDest->address_ip }}
                                    (
                                    <a href="{{ route('admin.physical-security-devices.show',$logicalFlow->physicalSecurityDeviceDest->id) }}">
                                        {{ $logicalFlow->physicalSecurityDeviceDest->name }}
                                    </a>)
                                @elseif ($logicalFlow->subnetworkDest!==null)
                                    {{ $logicalFlow->subnetworkDest->address }}
                                    (
                                    <a href="{{ route('admin.subnetworks.show',$logicalFlow->subnetworkDest->id) }}">
                                        {{ $logicalFlow->subnetworkDest->name }}
                                    </a>)
                                @endif
                            </td>
                            <td>
                                {{ $logicalFlow->dest_port ?? "ANY" }}
                            </td>
                            <td>
                                {{ $logicalFlow->action }}
                            </td>
                            <td nowrap>
                                @can('logical_flow_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.logical-flows.show', $logicalFlow->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('logical_flow_edit')
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.logical-flows.edit', $logicalFlow->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('logical_flow_delete')
                                    <form action="{{ route('admin.logical-flows.destroy', $logicalFlow->id) }}"
                                          method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');"
                                          style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger"
                                               value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('partials.pagination-footer', ['paginator' => $logicalFlows])
        </div>
    </div>
@endsection

@section('scripts')
@parent
<script>
@include('partials.datatable', array(
    'id' => '#dataTable',
    'title' => trans("cruds.logicalFlow.title_singular"),
    'URL' => route('admin.logical-flows.massDestroy'),
    'canDelete' => auth()->user()->can('logical_flow_delete') ? true : false,
    'serverSidePagination' => true,
));
document.addEventListener("DOMContentLoaded", function () {
    if (typeof table !== 'undefined' && !table.state.loaded()) {
        table.column('[data-column="description"]').visible(false);
        }
    });
</script>
@endsection
