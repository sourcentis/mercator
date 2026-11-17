@extends('layouts.admin')

@section('title')
    {{ $entity->name }}
@endsection

@section('content')

<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.entities.index') }}">
	    {{ trans('global.back_to_list') }}
   	</a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$entity->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($entity)
        <a class="btn btn-info" href="{{ route('admin.entities.edit', $entity->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('entity_delete')
        <form action="{{ route('admin.entities.destroy', $entity->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('cruds.entity.title_singular') }}
    </div>
    <div class="card-body">
        @include('admin.entities._details', [
            'entity' => $entity,
            'withLink' => false,
        ])
    </div>
    @include('admin._footer', ['model' => $entity])
</div>

<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.entities.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
