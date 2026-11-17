@extends('layouts.admin')

@section('title')
    {{ $annuaire->name }}
@endsection

@section('content')
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.annuaires.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$annuaire->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($annuaire)
        <a class="btn btn-info" href="{{ route('admin.annuaires.edit', $annuaire->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('annuaire_edit')
        <form action="{{ route('admin.annuaires.destroy', $annuaire->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.annuaire.title') }}
    </div>
    <div class="card-body">
    @include('admin.annuaires._details', [
        'annuaire' => $annuaire,
        'withLink' => false,
    ])
    </div>
    @include('admin._footer', ['model' => $annuaire])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.annuaires.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
