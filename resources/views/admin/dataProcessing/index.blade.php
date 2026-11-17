@extends('layouts.admin')

@section('title')
    {{ trans('cruds.dataProcessing.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('data_processing_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.data-processings.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.dataProcessing.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.dataProcessing.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.dataProcessing.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.dataProcessing.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.dataProcessing.fields.processes') }}
                        </th>
                        <th>
                            {{ trans('cruds.dataProcessing.fields.applications') }}
                        </th>
                        <th>
                            {{ trans('cruds.dataProcessing.fields.information') }}
                        </th>
                        <th data-column="responsible">
                            {{ trans('cruds.dataProcessing.fields.responsible') }}
                        </th>
                        <th data-column="purpose">
                            {{ trans('cruds.dataProcessing.fields.purpose') }}
                        </th>
                        <th data-column="categories">
                            {{ trans('cruds.dataProcessing.fields.categories') }}
                        </th>
                        <th data-column="recipients">
                            {{ trans('cruds.dataProcessing.fields.recipients') }}
                        </th>
                        <th data-column="transfert">
                            {{ trans('cruds.dataProcessing.fields.transfert') }}
                        </th>
                        <th data-column="retention">
                            {{ trans('cruds.dataProcessing.fields.retention') }}
                        </th>
                        <th data-column="controls">
                            {{ trans('cruds.dataProcessing.fields.controls') }}
                        </th>
                        <th data-column="lawfulness">
                            {{ trans('cruds.dataProcessing.fields.lawfulness') }}
                        </th>
                        <th data-column="data_source">
                            {{ trans('cruds.dataProcessing.fields.data_source') }}
                        </th>
                        <th data-column="data_collection_obligation">
                            {{ trans('cruds.dataProcessing.fields.data_collection_obligation') }}
                        </th>
                        <th data-column="data_subject_rights">
                            {{ trans('cruds.dataProcessing.fields.data_subject_rights') }}
                        </th>
                        <th data-column="automated_decision_making">
                            {{ trans('cruds.dataProcessing.fields.automated_decision_making') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($processingRegister as $processing)
                        <tr data-entry-id="{{ $processing->id }}"
                            @if (
                                ($processing->description===null)||
                                ($processing->responsible===null)||
                                ($processing->purpose===null)||
                                ($processing->categories===null)||
                                ($processing->recipients===null)||
                                ($processing->transfert===null)||
                                ($processing->retention===null)
                                )
                                class="table-warning"
                            @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $processing->perimeter->name }}</td>
                            @endif
                            <td nowrap>
                                <x-show-link :model="$processing" />
                            </td>
                            <td>
                                {!! clean($processing->description ?? '') !!}
                            </td>
                            <td>
                                @foreach($processing->processes as $p)
                                    <x-show-link :model="$p" />
                                    @if (!$loop->last)
                                    ,
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach($processing->applications as $app)
                                    <x-show-link :model="$app" />
                                    @if (!$loop->last)
                                    ,
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach($processing->informations as $info)
                                    <x-show-link :model="$info" />
                                    @if (!$loop->last)
                                    ,
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                {{ $processing->responsible }}
                            </td>
                            <td>
                                {{ $processing->purpose }}
                            </td>
                            <td>
                                {{ $processing->categories }}
                            </td>
                            <td>
                                {{ $processing->recipients }}
                            </td>
                            <td>
                                {{ $processing->transfert }}
                            </td>
                            <td>
                                {{ $processing->retention }}
                            </td>
                            <td>
                                @foreach($processing->securityControls() as $control)
                                    <x-show-link :model="$control" />
                                    @if (!$loop->last),@endif
                                        <br>       
                                @endforeach
                            </td>
                            <td>
                                {{ $processing->lawfulness }}
                            </td>
                            <td>
                                {{ $processing->data_source }}
                            </td>
                            <td>
                                {{ $processing->data_collection_obligation }}
                            </td>
                            <td>
                                {{ $processing->data_subject_rights }}
                            </td>
                            <td>
                                {{ $processing->automated_decision_making }}
                            </td>
                            <td nowrap>
                                @can('data_processing_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.data-processings.show', $processing->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($processing)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.data-processings.edit', $processing->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('data_processing_delete')
                                    <form action="{{ route('admin.data-processings.destroy', $processing->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
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
    @include('partials.pagination-footer', ['paginator' => $processingRegister])
    </div>
</div>

@endsection
@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.dataProcessing.title_singular"),
    'URL' => route('admin.data-processings.massDestroy'),
    'canDelete' => auth()->user()->can('data_processing_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'responsible', 'purpose', 'categories', 'recipients', 'transfert', 'retention', 'controls', 'lawfulness', 'data_source', 'data_collection_obligation', 'data_subject_rights', 'automated_decision_making'],
));
</script>
@endsection
