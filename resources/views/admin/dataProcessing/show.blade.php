@extends('layouts.admin')

@section('title')
    {{ $dataProcessing->name }}
@endsection

@section('content')
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.data-processings.index') }}">
            {{ trans('global.back_to_list') }}
        </a>

        @canEdit($dataProcessing)
            <a class="btn btn-info" href="{{ route('admin.data-processings.edit', $dataProcessing->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('data_processing_delete')
            <form action="{{ route('admin.data-processings.destroy', $dataProcessing->id) }}" method="POST"
                  data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan
    </div>
    <div class="card">
        <div class="card-header">
            {{ trans('global.show') }} {{ trans('cruds.dataProcessing.title') }}
        </div>
        <div class="card-body">
            @include('admin.dataProcessing._details', [
                'dataProcessing' => $dataProcessing,
                'withLink' => false,
            ])


<table class="table table-bordered table-striped table-report">
    <tbody>

    <tr>
        <th width="10%">
            {{ trans('cruds.dataProcessing.fields.responsible') }}
        </th>
        <td colspan='3'>
            {!! clean($dataProcessing->responsible ?? '') !!}
        </td>
    </tr>


    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.purpose') }}
        </th>
        <td colspan='3'>
            {!! clean($dataProcessing->purpose ?? '') !!}
        </td>
    </tr>

    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.lawfulness') }}
        </th>
        <td colspan="3">
            <table width="100%">
                <td>
                    <input class="form-check-input" type="checkbox"
                           {{ $dataProcessing->lawfulness_consent ? "checked" : "" }} disabled>
                    {{ trans('cruds.dataProcessing.fields.lawfulness_consent') }}
                </td>
                <td>
                    <input class="form-check-input" type="checkbox"
                           {{ $dataProcessing->lawfulness_contract ? "checked" : "" }} disabled>
                    {{ trans('cruds.dataProcessing.fields.lawfulness_contract') }}
                </td>
                <td>
                    <input class="form-check-input" type="checkbox"
                           {{ $dataProcessing->lawfulness_legal_obligation ? "checked" : "" }} disabled>
                    {{ trans('cruds.dataProcessing.fields.lawfulness_legal_obligation') }}
                </td>
                <td>
                    <input class="form-check-input" type="checkbox"
                           {{ $dataProcessing->lawfulness_vital_interest ? "checked" : "" }} disabled>
                    {{ trans('cruds.dataProcessing.fields.lawfulness_vital_interest') }}
                </td>
                <td>
                    <input class="form-check-input" type="checkbox"
                           {{ $dataProcessing->lawfulness_public_interest ? "checked" : "" }} disabled>
                    {{ trans('cruds.dataProcessing.fields.lawfulness_public_interest') }}
                </td>
                <td>
                    <input class="form-check-input" type="checkbox"
                           {{ $dataProcessing->lawfulness_legitimate_interest ? "checked" : "" }} disabled>
                    {{ trans('cruds.dataProcessing.fields.lawfulness_legitimate_interest') }}
                </td>
            </table>
        </td>
    </tr>
    <tr>
        <th>
            &nbsp;
        </th>
        <td colspan='3'>
            {!! clean($dataProcessing->lawfulness ?? '') !!}
        </td>
    </tr>

    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.categories') }}
        </th>
        <td colspan='3'>
            {!! clean($dataProcessing->categories ?? '') !!}
        </td>
    </tr>

    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.data_source') }}
        </th>
        <td colspan='3'>
            {!! clean($dataProcessing->data_source ?? '') !!}
        </td>
    </tr>

    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.data_collection_obligation') }}
        </th>
        <td colspan='3'>
            {!! clean($dataProcessing->data_collection_obligation ?? '') !!}
        </td>
    </tr>


    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.recipients') }}
        </th>
        <td colspan='3'>
            {!! clean($dataProcessing->recipients ?? '') !!}
        </td>
    </tr>

    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.transfert') }}
        </th>
        <td colspan='3'>
            {!! clean($dataProcessing->transfert ?? '') !!}
        </td>
    </tr>

    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.automated_decision_making') }}
        </th>
        <td colspan='3'>
            {!! clean($dataProcessing->automated_decision_making ?? '') !!}
        </td>
    </tr>


    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.retention') }}
        </th>
        <td colspan='3'>
            {!! clean($dataProcessing->retention ?? '') !!}
        </td>
    </tr>

    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.data_subject_rights') }}
        </th>
        <td colspan='3'>
            {!! clean($dataProcessing->data_subject_rights ?? '') !!}
        </td>
    </tr>

    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.update_date') }}
        </th>
        <td colspan='3'>
            {{ $dataProcessing->update_date?->format('d-m-Y') }}
        </td>
    </tr>

    </tbody>
    </table>

    <table class="table table-bordered table-striped table-report">
        <tbody>

        <tr>
            <th width="10%">
            {{ trans('cruds.dataProcessing.fields.processes') }}
        </th>
        <td colspan='3'>
            @foreach($dataProcessing->processes as $process)
                @canShow($process)<a href="{{ route('admin.processes.show', $process->id) }}">{{ $process->name }}</a>@elsecanShow{{ $process->name }}@endcanShow
                @if (!$loop->last)
                    ,
                @endif
            @endforeach
        </td>
    </tr>

    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.applications') }}
        </th>
        <td colspan='3'>
            @foreach($dataProcessing->applications as $application)
                @canShow($application)<a href="{{ route('admin.applications.show', $application->id) }}">{{ $application->name }}</a>@elsecanShow{{ $application->name }}@endcanShow
                @if (!$loop->last)
                    ,
                @endif
            @endforeach
        </td>
    </tr>

    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.information') }}
        </th>
        <td colspan='3'>
            @foreach($dataProcessing->informations as $information)
                @canShow($information)<a href="{{ route('admin.information.show', $information->id) }}">{{ $information->name }}</a>@elsecanShow{{ $information->name }}@endcanShow
                @if (!$loop->last)
                    ,
                @endif
            @endforeach
        </td>
    </tr>

    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.controls') }}
        </th>
        <td colspan='3'>
            @foreach($dataProcessing->securityControls() as $control)
                @canShow($control)<a href="{{ route('admin.security-controls.show', $control->id) }}">{{ $control->name }}</a>@elsecanShow{{ $control->name }}@endcanShow
                @if (!$loop->last)
                    ,
                @endif
            @endforeach
        </td>
    </tr>    

    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.documents') }}
        </th>
        <td colspan='3'>
            @foreach($dataProcessing->documents as $document)
                <a href="{{ route('admin.documents.show', $document->id) }}">{{ $document->filename }}</a>
                @if (!$loop->last)
                    ,
                @endif
            @endforeach
        </td>
    </tr>
    </tbody>
    </table>


    </div>
        @include('admin._footer', ['model' => $dataProcessing])
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.data-processings.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>
@endsection
