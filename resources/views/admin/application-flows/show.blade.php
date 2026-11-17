@extends('layouts.admin')

@section('title')
    {{ $flow->name }}
@endsection

@section('content')
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.application-flows.index') }}">
            {{ trans('global.back_to_list') }}
        </a>

        @can('explore_access')
        <a class="btn btn-success"
           href="{{ route('admin.report.explore') }}?node={{$flow->sourceId()}},{{$flow->destId()}}">
            {{ trans('global.explore') }}
        </a>
        @endcan

        @canEdit($flow)
            <a class="btn btn-info" href="{{ route('admin.application-flows.edit', $flow->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('application_flow_delete')
            <form action="{{ route('admin.application-flows.destroy', $flow->id) }}" method="POST"
                  data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan
    </div>
    <div class="card">
        <div class="card-header">
            {{ trans('global.show') }} {{ trans('cruds.applicationFlow.title') }}
        </div>
        <div class="card-body">
            @include('admin.application-flows._details', [
                'flux' => $flow,
                'withLink' => false,
            ])
        </div>
        @include('admin._footer', ['model' => $flow])
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.application-flows.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>
@endsection
