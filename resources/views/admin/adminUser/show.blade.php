@extends('layouts.admin')

@section('title')
    {{ $adminUser->user_id }}
@endsection

@section('content')
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.admin-users.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$adminUser->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($adminUser)
        <a id="btn-cancel" class="btn btn-info" href="{{ route('admin.admin-users.edit', $adminUser->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('admin_user_delete')
        <form action="{{ route('admin.admin-users.destroy', $adminUser->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.adminUser.title') }}
    </div>
    <div class="card-body">
    @include('admin.adminUser._details', [
        'adminUser' => $adminUser,
        'withLink' => false,
    ])
    </div>
    @include('admin._footer', ['model' => $adminUser])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.admin-users.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
