@props([
    'peripheral',
    'withLink' => false,
])

<table class="table table-bordered table-striped table-report" id="{{ $peripheral->getUID() }}">
    <tbody>
        <tr>
            <th width="10%">
                {{ ($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters()) ? trans('cruds.perimeter.title_short').' / ' : '' }}{{ trans('cruds.peripheral.fields.name') }}
            </th>
            <td width="20%">
                @if (($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters())){{ $peripheral->perimeter->name }} / @endif
            @if($withLink)
                @canShow($peripheral)
                    <a href="{{ route('admin.peripherals.show', $peripheral) }}">{{ $peripheral->name }}</a>
                @elsecanShow
                    {{ $peripheral->name }}
                @endcanShow
            @else
                {{ $peripheral->name }}
            @endif
            </td>
            <th width="10%">
                {{ trans('cruds.peripheral.fields.type') }}
            </th>
            <td width="20%">
                {{ $peripheral->type }}
            </td>
            <th width="10%">
                {{ trans('cruds.peripheral.fields.attributes') }}
            </th>
            <td width="30%" colspan="2">
                @foreach(explode(" ", (string) $peripheral->attributes) as $attribute)
                    @if(strlen(trim($attribute)) > 0)
                        <span class="badge badge-info">{{ $attribute }}</span>
                    @endif
                @endforeach
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.peripheral.fields.description') }}
            </th>
            <td colspan='5'>
                {!! $peripheral->description !!}
            </td>
            <td width="10%" align="center">
                @if ($peripheral->icon_id === null)
                <img src='/images/peripheral.png' width='60' height='60'>
                @else
                <img src='{{ route('admin.documents.show', $peripheral->icon_id) }}' width='60' height='60'>
                @endif
            </td>

        </tr>
    </tbody>
</table>
