@extends('layouts.admin')

@section('title')
    {{ $physicalRouter->name }}
@endsection

@section('content')

<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.physical-routers.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$physicalRouter->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($physicalRouter)
        <a class="btn btn-info" href="{{ route('admin.physical-routers.edit', $physicalRouter->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('physical_router_create')
        <a class="btn btn-warning" href="{{ route('admin.physical-routers.clone', $physicalRouter->id) }}">
            {{ trans('global.clone') }}
        </a>
    @endcan

    @can('physical_router_delete')
        <form action="{{ route('admin.physical-routers.destroy', $physicalRouter->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan

</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.physicalRouter.title') }}
    </div>
    <div class="card-body">
         @include('admin.physicalRouters._details', [
             'physicalRouter' => $physicalRouter,
             'withLink' => false,
         ])
   </div>
    @include('admin._footer', ['model' => $physicalRouter])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.physical-routers.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
