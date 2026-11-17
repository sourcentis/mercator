@extends('layouts.admin')

@section('title')
    {{ $securityControl->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.security-controls.index') }}">
        {{ trans('global.back_to_list') }}
    </a>

    @canEdit($securityControl)
        <a class="btn btn-info" href="{{ route('admin.security-controls.edit', $securityControl->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('security_control_delete')
        <form action="{{ route('admin.security-controls.destroy', $securityControl->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.securityControl.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th width="10%">
                        {{ auth()->user()->hasMultiplePerimeters() ? trans('cruds.perimeter.title_short').' / ' : '' }}{{ trans('cruds.securityControl.fields.name') }}
                    </th>
                    <td>
                @if (auth()->user()->hasMultiplePerimeters()){{ $securityControl->perimeter->name }} / @endif
                        {{ $securityControl->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.securityControl.fields.description') }}
                    </th>
                    <td>
                        {!! $securityControl->description !!}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @include('admin._footer', ['model' => $securityControl])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.security-controls.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
