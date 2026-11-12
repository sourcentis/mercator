@extends('layouts.admin')

@section('title')
    {{ $site->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.sites.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$site->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($site)
        <a class="btn btn-info" href="{{ route('admin.sites.edit', $site->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('site_create')
        <a class="btn btn-warning" href="{{ route('admin.sites.clone', $site->id) }}">
            {{ trans('global.clone') }}
        </a>
    @endcan

    @can('site_delete')
        <form action="{{ route('admin.sites.destroy', $site->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.site.title') }}
    </div>
    <div class="card-body">
        @include('admin.sites._details', [
            'site' => $site,
            'withLink' => false,
        ])
    </div>
    @include('admin._footer', ['model' => $site])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.sites.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
