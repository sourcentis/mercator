@extends('layouts.admin')

@section('title')
    {{ $domain->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.domains.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$domain->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($domain)
        <a class="btn btn-info" href="{{ route('admin.domains.edit', $domain->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('entity_delete')
        <form action="{{ route('admin.domains.destroy', $domain->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.domain.title') }}
    </div>
    <div class="card-body">
        @include('admin.domains._details', [
            'domain' => $domain,
            'withLink' => false,
        ])
    </div>
    @include('admin._footer', ['model' => $domain])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.domains.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
