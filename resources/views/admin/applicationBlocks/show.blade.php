@extends('layouts.admin')

@section('title')
    {{ $applicationBlock->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.application-blocks.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$applicationBlock->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($applicationBlock)
        <a class="btn btn-info" href="{{ route('admin.application-blocks.edit', $applicationBlock->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('application_block_delete')
        <form action="{{ route('admin.application-blocks.destroy', $applicationBlock->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.applicationBlock.title') }}
    </div>
    <div class="card-body">
    @include('admin.applicationBlocks._details', [
        'applicationBlock' => $applicationBlock,
        'withLink' => false,
    ])
    </div>
    @include('admin._footer', ['model' => $applicationBlock])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.application-blocks.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
