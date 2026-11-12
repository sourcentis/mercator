@extends('layouts.admin')

@section('title')
    {{ trans('global.show') }} {{ trans('cruds.physicalLink.title') }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.physical-links.index') }}">
        {{ trans('global.back_to_list') }}
    </a>

    @can('explore_access')
    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$link->sourceId()}},{{$link->destinationId()}}">
        {{ trans('global.explore') }}
    </a>
    @endcan

    @canEdit($link)
        <a class="btn btn-info" href="{{ route('admin.physical-links.edit', $link->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('physical_link_delete')
        <form action="{{ route('admin.physical-links.destroy', $link->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.physicalLink.title') }}
    </div>
    <div class="card-body">
        @include('admin.physical-links._details', [
            'link' => $link,
            'withLink' => false,
        ])
    </div>
    @include('admin._footer', ['model' => $link])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.physical-links.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
