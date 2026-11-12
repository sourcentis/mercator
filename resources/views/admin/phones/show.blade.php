@extends('layouts.admin')

@section('title')
    {{ $phone->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.phones.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$phone->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan
    @canEdit($phone)
        <a class="btn btn-info" href="{{ route('admin.phones.edit', $phone->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('peripheral_create')
        <a class="btn btn-warning" href="{{ route('admin.phones.clone', $phone->id) }}">
            {{ trans('global.clone') }}
        </a>
    @endcan

    @can('phone_delete')
        <form action="{{ route('admin.phones.destroy', $phone->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>
<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.phone.title') }}
    </div>
    <div class="card-body">
        @include('admin.phones._details', [
            'phone' => $phone,
            'withLink' => false,
        ])
    </div>
    @include('admin._footer', ['model' => $phone])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.phones.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
