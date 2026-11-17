@props([
    'information',
    'withLink' => false,
])

<table class="table table-bordered table-striped table-report" id="{{ $information->getUID() }}">
    <tbody>
        <tr>
            <th style="width: 10%">
                {{ auth()->user()->hasMultiplePerimeters() ? trans('cruds.perimeter.title_short').' / ' : '' }}{{ trans('cruds.information.fields.name') }}
            </th>
            <td style="width: 20%">
                @if (auth()->user()->hasMultiplePerimeters()){{ $information->perimeter->name }} / @endif
            @if ($withLink)
                @canShow($information)
                    <a href="{{ route('admin.information.show', $information->id) }}">{{ $information->name }}</a>
                @elsecanShow
                    {{ $information->name }}
                @endcanShow
            @else
                {{ $information->name }}
            @endif
            </td>
            <th style="width: 10%">
                {{ trans('cruds.information.fields.type') }}
            </th>
            <td style="width: 20%">
                {{ $information->type }}
            </td>
            <th style="width: 10%">
                {{ trans('cruds.information.fields.attributes') }}
            </th>
            <td style="width: 30%;" colspan="2">
                @foreach(explode(" ", $information->attributes) as $attribute)
                    <span class="badge badge-info">{{ $attribute }}</span>
                @endforeach
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.information.fields.description') }}
            </th>
            <td colspan="5">
                {!! clean($information->description ?? '') !!}
            </td>
            <td style="text-align: center; width: 10%">
                @if ($information->icon_id === null)
                    <img src='/images/information.png' width='60' height='60'>
                @else
                    <img src='{{ route('admin.documents.show', $information->icon_id) }}' width='60' height='60'>
                @endif
            </td>
        </tr>

        @if($information->graphs()->count()>0)
        <tr>
            <th>
                <span style="border: 2px solid grey;
                     color: darkred;
                     padding: 6px 14px;
                     border-radius: 6px;
                     display: inline-flex;
                     align-items: center;
                     gap: 8px;
                     font-weight: 600;
                     background: #eff6ff;">
                    <i class="bi bi-diagram-2-fill" style="font-size: 1.3em;"></i>
                    <span style="color: black;">BPMN</span>
                </span>
            </th>
            <td colspan="6" style="vertical-align: middle;">
                @foreach($information->graphs() as $graph)
                    @canShow($graph)
                        <a href="{{ route('admin.bpmn.show', $graph->id) }}">
                            {{ $graph->name }}
                        </a>
                    @elsecanShow
                        {{ $graph->name }}
                    @endcanShow
                    @if (!$loop->last)
                    ,
                    @endif
                @endforeach
            </td>
        </tr>
        @endif
    </tbody>
</table>
