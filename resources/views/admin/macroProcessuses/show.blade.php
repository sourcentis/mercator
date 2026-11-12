@extends('layouts.admin')

@section('title')
    {{ $macroProcessus->name }}
@endsection

@section('content')

<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.macro-processuses.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$macroProcessus->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($macroProcessus)
        <a class="btn btn-info" href="{{ route('admin.macro-processuses.edit', $macroProcessus->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('macro_processus_delete')
        <form action="{{ route('admin.macro-processuses.destroy', $macroProcessus->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.macroProcessus.title') }}
    </div>
    <div class="card-body">
        @include('admin.macroProcessuses._details', [
            'macroProcessus' => $macroProcessus,
            'withLink' => false,
        ])
    </div>
    @include('admin._footer', ['model' => $macroProcessus])
</div>

<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.macro-processuses.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>

@endsection
