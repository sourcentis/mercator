@extends('layouts.admin')

@section('title')
    {{ $wan->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.wans.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$wan->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($wan)
        <a class="btn btn-info" href="{{ route('admin.wans.edit', $wan->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('wan_delete')
        <form action="{{ route('admin.wans.destroy', $wan->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>
<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.wan.title') }}
    </div>
    <div class="card-body">
    @include('admin.wans._details', [
        'wan' => $wan,
        'withLink' => false,
    ])
    </div>
    @include('admin._footer', ['model' => $wan])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.wans.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
