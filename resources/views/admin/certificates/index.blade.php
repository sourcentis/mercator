@extends('layouts.admin')

@section('title')
    {{ trans('cruds.certificate.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('certificate_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.certificates.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.certificate.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.certificate.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="dataTable" class="table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        @if (auth()->user()->hasMultiplePerimeters())
                            <th data-column="perimeter">{{ trans('cruds.perimeter.title_short') }}</th>
                        @endif
                        <th>
                            {{ trans('cruds.certificate.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.certificate.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.certificate.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.certificate.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.certificate.fields.start_validity') }}
                        </th>
                        <th>
                            {{ trans('cruds.certificate.fields.end_validity') }}
                        </th>
                        <th>
                            {{ trans('cruds.certificate.fields.status') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($certificates as $certificate)
                        <tr data-entry-id="{{ $certificate->id }}"
                        @if(($certificate->description==null)||
                            ($certificate->type==null)||
                            ($certificate->start_validity==null)||
                            ($certificate->end_validity==null)||
                            (
                            ($certificate->logicalServers->count()==0)&&($certificate->applications->count()==0)
                            ))
                                class="table-warning"
                        @endif
                          >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $certificate->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$certificate" />
                            </td>
                            <td>
                                {{ $certificate->type ?? '' }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $certificate->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $certificate->description ?? '' !!}
                            </td>
                            <td>
                                {{ $certificate->start_validity }}
                            </td>
                            <td>
                                {{ $certificate->end_validity }}
                            </td>
                            <td>
                                @if ($certificate->status==0)
                                    {{ trans('cruds.certificate.fields.status_good') }}
                                @elseif ($certificate->status==1)
                                    {{ trans('cruds.certificate.fields.status_revoked') }}
                                @elseif ($certificate->status==2)
                                    {{ trans('cruds.certificate.fields.status_unknown') }}
                                @endif
                            </td>
                            <td nowrap>
                                @can('certificate_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.certificates.show', $certificate->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($certificate)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.certificates.edit', $certificate->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('certificate_delete')
                                    <form action="{{ route('admin.certificates.destroy', $certificate->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan

                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    
    @include('partials.pagination-footer', ['paginator' => $certificates])
</div>
</div>
@endsection

@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.certificate.title_singular"),
    'URL' => route('admin.certificates.massDestroy'),
    'canDelete' => auth()->user()->can('certificate_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'attributes'],
));
</script>
@endsection
