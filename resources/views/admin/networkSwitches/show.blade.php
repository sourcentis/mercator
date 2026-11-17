@extends('layouts.admin')

@section('title')
    {{ $networkSwitch->name }}
@endsection

@section('content')

<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.network-switches.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$networkSwitch->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($networkSwitch)
        <a class="btn btn-info" href="{{ route('admin.network-switches.edit', $networkSwitch->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('entity_delete')
        <form action="{{ route('admin.network-switches.destroy', $networkSwitch->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan

</div>
<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.networkSwitch.title') }}
    </div>
    <div class="card-body">
        @include('admin.networkSwitches._details', [
            'networkSwitch' => $networkSwitch,
            'withLink' => false,
        ])
    </div>
    @include('admin._footer', ['model' => $networkSwitch])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.network-switches.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
