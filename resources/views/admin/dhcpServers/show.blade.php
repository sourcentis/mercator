@extends('layouts.admin')

@section('title')
    {{ $dhcpServer->name }}
@endsection

@section('content')

<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.dhcp-servers.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
    @canEdit($dhcpServer)
        <a class="btn btn-info" href="{{ route('admin.dhcp-servers.edit', $dhcpServer->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('entity_delete')
        <form action="{{ route('admin.dhcp-servers.destroy', $dhcpServer->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.dhcpServer.title') }}
    </div>
    <div class="card-body">
        @include('admin.dhcpServers._details', [
            'dhcpServer' => $dhcpServer,
            'withLink' => false,
        ])
    </div>
    @include('admin._footer', ['model' => $dhcpServer])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.dhcp-servers.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
