@extends('layouts.admin')

@section('title')
    {{ $zoneAdmin->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.zone-admins.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$zoneAdmin->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($zoneAdmin)
        <a class="btn btn-info" href="{{ route('admin.zone-admins.edit', $zoneAdmin->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('zone_admin_edit')
        <form action="{{ route('admin.zone-admins.destroy', $zoneAdmin->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.zoneAdmin.title') }}
    </div>
    <div class="card-body">
        @include('admin.zoneAdmins._details', [
            'zoneAdmin' => $zoneAdmin,
            'withLink' => false,
        ])
    </div>
    @include('admin._footer', ['model' => $zoneAdmin])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.zone-admins.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
