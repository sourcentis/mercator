@extends('layouts.admin')

@section('title')
    {{ $cluster->name }}
@endsection

@section('content')
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.clusters.index') }}">
            {{ trans('global.back_to_list') }}
        </a>


        @can('explore_access')

        <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$cluster->getUID()}}">
            {{ trans('global.explore') }}
        </a>


        @endcan

        @canEdit($cluster)
            <a class="btn btn-info" href="{{ route('admin.clusters.edit', $cluster->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('cluster_delete')
            <form action="{{ route('admin.clusters.destroy', $cluster->id) }}" method="POST"
                  data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan
    </div>

    <div class="card">
        <div class="card-header">
            {{ trans('global.show') }} {{ trans('cruds.cluster.title') }}
        </div>
        <div class="card-body">
            @include('admin.clusters._details', [
                'clusters' => $cluster,
                'withLink' => false,
            ])
        </div>
        @include('admin._footer', ['model' => $cluster])
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.clusters.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>

@endsection
