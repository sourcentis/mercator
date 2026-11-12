@extends('layouts.admin')

@section('title')
    {{ trans('cruds.dhcpServer.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('dhcp_server_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.dhcp-servers.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.dhcpServer.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.dhcpServer.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="dataTable" class=" table table-bordered table-striped table-hover datatable datatable-DhcpServer">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        @if (auth()->user()->hasMultiplePerimeters())
                            <th data-column="perimeter">{{ trans('cruds.perimeter.title_short') }}</th>
                        @endif
                        <th>
                            {{ trans('cruds.dhcpServer.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.dhcpServer.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.dhcpServer.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.dhcpServer.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.dhcpServer.fields.address_ip') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dhcpServers as $key => $dhcpServer)
                        <tr data-entry-id="{{ $dhcpServer->id }}">
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $dhcpServer->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$dhcpServer" />
                            </td>
                            <td>
                                {{ $dhcpServer->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $dhcpServer->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $dhcpServer->description !!}
                            </td>
                            <td>
                                {{ $dhcpServer->address_ip ?? '' }}
                            </td>
                            <td>
                                @can('dhcp_server_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.dhcp-servers.show', $dhcpServer->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($dhcpServer)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.dhcp-servers.edit', $dhcpServer->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('dhcp_server_delete')
                                    <form action="{{ route('admin.dhcp-servers.destroy', $dhcpServer->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan

                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    
    @include('partials.pagination-footer', ['paginator' => $dhcpServers])
</div>
</div>



@endsection
@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.dhcpServer.title_singular"),
    'URL' => route('admin.dhcp-servers.massDestroy'),
    'canDelete' => auth()->user()->can('dhcp_server_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes'],
));
</script>
@endsection
