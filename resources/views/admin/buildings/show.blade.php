@extends('layouts.admin')

@section('title')
    {{ $building->name }}
@endsection

@section('content')
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.buildings.index') }}">
            {{ trans('global.back_to_list') }}
        </a>


        @can('explore_access')

        <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$building->getUID()}}">
            {{ trans('global.explore') }}
        </a>


        @endcan

        @canEdit($building)
            <a class="btn btn-info" href="{{ route('admin.buildings.edit', $building->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('building_create')
            <a class="btn btn-warning" href="{{ route('admin.buildings.clone', $building->id) }}">
                {{ trans('global.clone') }}
            </a>
        @endcan

        @can('building_delete')
            <form action="{{ route('admin.buildings.destroy', $building->id) }}" method="POST"
                  data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan
    </div>

    <div class="card">
        <div class="card-header">
            {{ trans('global.show') }} {{ trans('cruds.building.title') }}
        </div>
        <div class="card-body">
            @include('admin.buildings._details', [
                'building' => $building,
                'withLink' => false,
            ])
        </div>
        @include('admin._footer', ['model' => $building])
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.buildings.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>

@endsection
