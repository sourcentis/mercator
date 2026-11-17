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
                        @if (auth()->user()->hasMultiplePerimeters())
                            <th data-column="perimeter">{{ trans('cruds.perimeter.title_short') }}</th>
                        @endif
                        <th>
                            {{ trans('cruds.logicalFlow.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalFlow.fields.type') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalFlow.fields.attributes') }}
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
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $logicalFlow->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$logicalFlow" />
                            </td>
                            <td>
                                {{ $logicalFlow->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", $logicalFlow->attributes) as $attribute) {
                                    echo "<span class='badge badge-info'>";
                                    echo $attribute;
                                    echo "</span> ";
                                }
                                ?>
                            </td>
                            <td data-column="description">
                                {!! $logicalFlow->description !!}
                            </td>
                            <td>
                                {{ $logicalFlow->chain }}
                            </td>
                            <td>
                                {{ $logicalFlow->interface }}
                            </td>
                            <td>
                                @if ($logicalFlow->router !== null)
                                    <x-show-link :model="$logicalFlow->router" />
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
                                    <x-show-link :model="$logicalFlow->logicalServerSource" />)
                                @elseif ($logicalFlow->peripheralSource!==null)
                                    {{ $logicalFlow->peripheralSource->address_ip }}
                                    (<x-show-link :model="$logicalFlow->peripheralSource" />)
                                @elseif ($logicalFlow->physicalServerSource!==null)
                                    {{ $logicalFlow->physicalServerSource->address_ip ?? "" }}
                                    (
                                    <x-show-link :model="$logicalFlow->physicalServerSource" />)
                                @elseif ($logicalFlow->storageDeviceSource!==null)
                                    {{ $logicalFlow->storageDeviceSource->address_ip }}
                                    (
                                    <x-show-link :model="$logicalFlow->storageDeviceSource" />)
                                @elseif ($logicalFlow->workstationSource!==null)
                                    {{ $logicalFlow->workstationSource->address_ip }}
                                    (
                                    <x-show-link :model="$logicalFlow->workstationSource" />)
                                @elseif ($logicalFlow->physicalSecurityDeviceSource!==null)
                                    {{ $logicalFlow->physicalSecurityDeviceSource->address_ip }}
                                    (
                                    <x-show-link :model="$logicalFlow->physicalSecurityDeviceSource" />)
                                @elseif ($logicalFlow->subnetworkSource!==null)
                                    {{ $logicalFlow->subnetworkSource->address }}
                                    (
                                    <x-show-link :model="$logicalFlow->subnetworkSource" />)
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
                                    <x-show-link :model="$logicalFlow->logicalServerDest" />)
                                @elseif ($logicalFlow->peripheralDest!==null)
                                    {{ $logicalFlow->peripheralDest->address_ip }}
                                    (<x-show-link :model="$logicalFlow->peripheralDest" />)
                                @elseif ($logicalFlow->physicalServerDest!==null)
                                    {{ $logicalFlow->physicalServerDest->address_ip }}
                                    (
                                    <x-show-link :model="$logicalFlow->physicalServerDest" />)
                                @elseif ($logicalFlow->storageDeviceDest!==null)
                                    {{ $logicalFlow->storageDeviceDest->address_ip }}
                                    (
                                    <x-show-link :model="$logicalFlow->storageDeviceDest" />)
                                @elseif ($logicalFlow->workstationDest!==null)
                                    {{ $logicalFlow->workstationDest->address_ip }}
                                    (<x-show-link :model="$logicalFlow->workstationDest" />)
                                @elseif ($logicalFlow->physicalSecurityDeviceDest!==null)
                                    {{ $logicalFlow->physicalSecurityDeviceDest->address_ip }}
                                    (
                                    <x-show-link :model="$logicalFlow->physicalSecurityDeviceDest" />)
                                @elseif ($logicalFlow->subnetworkDest!==null)
                                    {{ $logicalFlow->subnetworkDest->address }}
                                    (
                                    <x-show-link :model="$logicalFlow->subnetworkDest" />)
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

                                @canEdit($logicalFlow)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.logical-flows.edit', $logicalFlow->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('logical_flow_delete')
                                    <form action="{{ route('admin.logical-flows.destroy', $logicalFlow->id) }}"
                                          method="POST" data-confirm="{{ trans('global.areYouSure') }}"
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
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.logicalFlow.title_singular"),
    'URL' => route('admin.logical-flows.massDestroy'),
    'canDelete' => auth()->user()->can('logical_flow_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'description'],
));
</script>
@endsection
