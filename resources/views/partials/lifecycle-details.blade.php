{{--
    Lifecycle rows for a 6-column show table (th/td x 3).

    @param \Illuminate\Database\Eloquent\Model $object
    @param list<string> $fields  fields to display, in order
--}}
@foreach(array_chunk($fields, 3) as $lifecycleRow)
    <tr>
        @foreach($lifecycleRow as $field)
            @php
                $value = $object->{$field};
                if ($value instanceof \DateTimeInterface) {
                    $value = $value->format('Y-m-d');
                }
            @endphp
            <th width="10%">
                {{ trans('cruds.lifecycle.fields.'.$field) }}
            </th>
            <td @if($loop->last && count($lifecycleRow) < 3) colspan="{{ 1 + 2 * (3 - count($lifecycleRow)) }}" @endif>
                {{ $value }}
            </td>
        @endforeach
    </tr>
@endforeach
