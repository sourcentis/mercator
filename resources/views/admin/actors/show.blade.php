@extends('layouts.admin')

@section('title')
    {{ $actor->name }}
@endsection

@section('content')
<div class="form-group">
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.actors.index') }}">
            {{ trans('global.back_to_list') }}
        </a>


        @can('explore_access')

        <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$actor->getUID()}}">
            {{ trans('global.explore') }}
        </a>


        @endcan

        @canEdit($actor)
            <a class="btn btn-info" href="{{ route('admin.actors.edit', $actor->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('entity_delete')
            <form action="{{ route('admin.actors.destroy', $actor->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan

    </div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.actor.title') }}
    </div>

    <div class="card-body">

    @include('admin.actors._details', [
        'actor' => $actor,
        'withLink' => false,
    ])

    </div>
    @include('admin._footer', ['model' => $actor])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.actors.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>



@endsection
