@extends('layouts.admin')

@section('title')
    {{ $network->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.networks.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$network->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($network)
        <a class="btn btn-info" href="{{ route('admin.networks.edit', $network->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('network_delete')
        <form action="{{ route('admin.networks.destroy', $network->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>
    <div class="card">
        <div class="card-header">
            {{ trans('global.show') }} {{ trans('cruds.network.title') }}
        </div>
        <div class="card-body">
            @include('admin.networks._details', [
                'network' => $network,
                'withLink' => false,
            ])
        </div>
        @include('admin._footer', ['model' => $network])
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.networks.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>
@endsection
