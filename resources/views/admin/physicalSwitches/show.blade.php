@extends('layouts.admin')

@section('title')
    {{ $physicalSwitch->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.physical-switches.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$physicalSwitch->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($physicalSwitch)
        <a class="btn btn-info" href="{{ route('admin.physical-switches.edit', $physicalSwitch->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('physical_switch_create')
        <a class="btn btn-warning" href="{{ route('admin.physical-switches.clone', $physicalSwitch->id) }}">
            {{ trans('global.clone') }}
        </a>
    @endcan

    @can('physical_switch_delete')
        <form action="{{ route('admin.physical-switches.destroy', $physicalSwitch->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>
<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.physicalSwitch.title') }}
    </div>
    <div class="card-body">
         @include('admin.physicalSwitches._details', [
             'physicalSwitch' => $physicalSwitch,
             'withLink' => false,
         ])
    </div>
    @include('admin._footer', ['model' => $physicalSwitch])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.physical-switches.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
