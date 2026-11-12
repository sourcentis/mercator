@extends('layouts.admin')

@section('title')
    {{ $zone->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.zones.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$zone->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($zone)
        <a class="btn btn-info" href="{{ route('admin.zones.edit', $zone->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit
    @can('zone_delete')
        <form action="{{ route('admin.zones.destroy', $zone->id) }}" method="POST"
              data-confirm="{{ trans('global.areYouSure') }}"
              style="display: inline-block;">
            @method('DELETE')
            @csrf
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.zone.title_singular') }}
    </div>

    <div class="card-body">
        @include('admin.zones._details', ['zone' => $zone])
    </div>

    @include('admin._footer', ['model' => $zone])
</div>
@endsection
