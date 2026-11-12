@extends('layouts.admin')

@section('title')
    {{ $certificate->name }}
@endsection

@section('content')
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.certificates.index') }}">
            {{ trans('global.back_to_list') }}
        </a>


        @can('explore_access')

        <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$certificate->getUID()}}">
            {{ trans('global.explore') }}
        </a>


        @endcan

        @canEdit($certificate)
            <a class="btn btn-info" href="{{ route('admin.certificates.edit', $certificate->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('audit_log_show')
            <a class="btn btn-secondary" href="{{ route('admin.audit-logs.history',
            ['type' => 'App\Models\Certificate', 'id' => $certificate->id]) }}">
                {{ trans('global.history') }}
            </a>
        @endcan

        @can('certificate_delete')
            <form action="{{ route('admin.certificates.destroy', $certificate->id) }}" method="POST"
                  data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan
    </div>

    <div class="card">
        <div class="card-header">
            {{ trans('global.show') }} {{ trans('cruds.certificate.title') }}
        </div>
        <div class="card-body">
            @include('admin.certificates._details', [
                'certificate' => $certificate,
                'withLink' => false,
            ])
        </div>
        @include('admin._footer', ['model' => $certificate])
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.certificates.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>
@endsection
