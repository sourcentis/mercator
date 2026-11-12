@extends('layouts.admin')

@section('title')
    {{ $dnsserver->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.dnsservers.index') }}">
        {{ trans('global.back_to_list') }}
    </a>

    @canEdit($dnsserver)
        <a class="btn btn-info" href="{{ route('admin.dnsservers.edit', $dnsserver->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('entity_delete')
        <form action="{{ route('admin.dnsservers.destroy', $dnsserver->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan

</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.dnsserver.title') }}
    </div>
    <div class="card-body">
        @include('admin.dnsservers._details', [
            'dnsserver' => $dnsserver,
            'withLink' => false,
        ])
    </div>
    @include('admin._footer', ['model' => $dnsserver])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.dnsservers.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
