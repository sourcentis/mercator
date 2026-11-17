@props([
    'dataProcessing',
    'withLink' => false,
])
<table class="table table-bordered table-striped table-report" id="{{ $dataProcessing->getUID() }}">
    <tbody>
    <tr>
        <th width="10%">
            {{ auth()->user()->hasMultiplePerimeters() ? trans('cruds.perimeter.title_short').' / ' : '' }}{{ trans('cruds.dataProcessing.fields.name') }}
        </th>
        <td width="50%">
                @if (auth()->user()->hasMultiplePerimeters()){{ $dataProcessing->perimeter->name }} / @endif
        @if($withLink)
            @canShow($dataProcessing)
                <a href="{{ route('admin.data-processings.show', $dataProcessing->id) }}">{{ $dataProcessing->name }}</a>
            @elsecanShow
                {{ $dataProcessing->name }}
            @endcanShow
        @else
            {{ $dataProcessing->name }}
        @endif
        </td>
        <th width="10%">
            {{ trans('cruds.dataProcessing.fields.legal_basis') }}
        </th>
        <td width="30%">
            {{ $dataProcessing->legal_basis }}
        </td>
    </tr>
    <tr>
        <th>
            {{ trans('cruds.dataProcessing.fields.description') }}
        </th>
        <td colspan='3'>
            {!! clean($dataProcessing->description ?? '') !!}
        </td>
    </tr>

    </tbody>
</table>
