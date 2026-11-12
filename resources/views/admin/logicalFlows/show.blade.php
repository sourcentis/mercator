@extends('layouts.admin')

@section('title')
    {{ $logicalFlow->name }}
@endsection

@section('content')
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.logical-flows.index') }}">
            {{ trans('global.back_to_list') }}
        </a>

        @can('explore_access')
        <a class="btn btn-success"
           href="{{ route('admin.report.explore') }}?node={{$logicalFlow->sourceId()}},{{$logicalFlow->destinationId()}}">
            {{ trans('global.explore') }}
        </a>
        @endcan

        @canEdit($logicalFlow)
            <a class="btn btn-info" href="{{ route('admin.logical-flows.edit', $logicalFlow->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('lan_delete')
            <form action="{{ route('admin.logical-flows.destroy', $logicalFlow->id) }}" method="POST"
                  data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan
    </div>

    <div class="card">
        <div class="card-header">
            {{ trans('global.show') }} {{ trans('cruds.logicalFlow.title_singular') }}
        </div>
        <div class="card-body">
            @include('admin.logicalFlows._details', [
                'logicalFlow' => $logicalFlow,
                'withLink' => false,
            ])
        </div>
        @include('admin._footer', ['model' => $logicalFlow])
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.logical-flows.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>
@endsection
