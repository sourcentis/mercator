@extends('layouts.admin')

@section('title')
    {{ trans('cruds.logicalServer.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('logical_server_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.logical-servers.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.logicalServer.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.logicalServer.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.logicalServer.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.type') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.configuration') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.applications') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.cluster') }} /
                            {{ trans('cruds.logicalServer.fields.servers') }}
                        </th>
                        <th data-column="net_services">
                            {{ trans('cruds.logicalServer.fields.net_services') }}
                        </th>
                        <th data-column="address_ip">
                            {{ trans('cruds.logicalServer.fields.address_ip') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($logicalServers as $key => $logicalServer)
                        <tr data-entry-id="{{ $logicalServer->id }}"
                            @if ($logicalServer->active==0)
                                class="table-secondary"
                            @elseif (
                            ($logicalServer->description==null)||
                            ($logicalServer->operating_system==null)||
                            ($logicalServer->environment==null)||
                            ($logicalServer->address_ip==null)||
                            ($logicalServer->applications->count()==0)||
                            (
                                ($logicalServer->physicalServers->count()==0) && ($logicalServer->clusters->count()>0)
                            ))
                                class="table-warning"
                                @endif
                        >
                            <td></td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $logicalServer->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$logicalServer" />
                            </td>
                            <td>{{ $logicalServer->type }}</td>
                            <td>@php
                                    foreach(explode(" ",$logicalServer->attributes) as $attribute)
                                        echo "<span class='badge badge-info'>$attribute</span> ";
                                @endphp
                            </td>
                            <td>{!! $logicalServer->description !!}</td>
                            <td>{!! $logicalServer->configuration !!}</td>
                            <td>@foreach($logicalServer->applications as $application)
                                    <x-show-link :model="$application" />{{ $loop->last ? null : ',' }}
                                @endforeach
                            </td>
                            <td>
                                @if(count($logicalServer->clusters)>0)
                                    @foreach($logicalServer->clusters as $cluster)
                                        <x-show-link :model="$cluster" />
                                        @if (!$loop->last)
                                            ,
                                        @endif
                                    @endforeach
                                @endif
                                @if(count($logicalServer->physicalServers)>0)
                                    @if(count($logicalServer->clusters)>0)
                                        /
                                    @endif
                                    @foreach($logicalServer->physicalServers as $server)
                                        <x-show-link :model="$server" />{{ $loop->last ? null : ',' }}
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                {{ $logicalServer->net_services }}
                            </td>
                            <td>
                                {{ $logicalServer->address_ip }}
                            </td>
                            <td nowrap>
                                @can('logical_server_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.logical-servers.show', $logicalServer->id) }}">{{ trans('global.view') }}</a>
                                @endcan
                                @canEdit($logicalServer)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.logical-servers.edit', $logicalServer->id) }}">{{ trans('global.edit') }}</a>
                                @endcanEdit
                                @can('logical_server_delete')
                                    <form action="{{ route('admin.logical-servers.destroy', $logicalServer->id) }}"
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
        
        @include('partials.pagination-footer', ['paginator' => $logicalServers])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.logicalServer.title_singular"),
            'URL' => route('admin.logical-servers.massDestroy'),
            'canDelete' => auth()->user()->can('logical_server_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'net_services', 'address_ip'],
));
    </script>
@endsection
