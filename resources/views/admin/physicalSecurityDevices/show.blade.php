@extends('layouts.admin')

@section('title')
    {{ $physicalSecurityDevice->name }}
@endsection

@section('content')
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.physical-security-devices.index') }}">
            {{ trans('global.back_to_list') }}
        </a>

        @can('explore_access')
        <a class="btn btn-success"
           href="{{ route('admin.report.explore') }}?node={{$physicalSecurityDevice->getUID()}}">
            {{ trans('global.explore') }}
        </a>
        @endcan

        @canEdit($physicalSecurityDevice)
            <a class="btn btn-info"
               href="{{ route('admin.physical-security-devices.edit', $physicalSecurityDevice->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('physical_security_device_delete')
            <form action="{{ route('admin.physical-security-devices.destroy', $physicalSecurityDevice->id) }}"
                  method="POST" data-confirm="{{ trans('global.areYouSure') }}"
                  style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan
    </div>
    <div class="card">
        <div class="card-header">
            {{ trans('global.show') }} {{ trans('cruds.physicalSecurityDevice.title') }}
        </div>
        <div class="card-body">
             @include('admin.physicalSecurityDevices._details', [
                 'physicalSecurityDevice' => $physicalSecurityDevice,
                 'withLink' => false,
             ])
        </div>
        @include('admin._footer', ['model' => $physicalSecurityDevice])
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.physical-security-devices.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>
@endsection
