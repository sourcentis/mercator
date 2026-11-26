@props([
    'workstation',
    'withLink' => false,
])

<table class="table table-bordered table-striped table-report" id="{{ $workstation->getUID() }}">
    <tbody>
        <tr>
            <th width="10%">
                {{ ($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters()) ? trans('cruds.perimeter.title_short').' / ' : '' }}{{ trans('cruds.workstation.fields.name') }}
            </th>
            <td width="20%">
                @if (($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters())){{ $workstation->perimeter->name }} / @endif
            @if($withLink)
                @canShow($workstation)
                    <a href="{{ route('admin.workstations.show', $workstation->id) }}">
                        {{ $workstation->name }}
                    </a>
                @elsecanShow
                    {{ $workstation->name }}
                @endcanShow
            @else
                {{ $workstation->name }}
            @endif
            </td>
            <th width="10%">{{ trans('cruds.workstation.fields.type') }}</th>
            <td width="20%">{{ $workstation->type }}</td>
            <th width="10%">{{ trans('cruds.workstation.fields.attributes') }}</th>
            <td width="30%" colspan="2">
                @foreach(explode(" ", (string) $workstation->attributes) as $attribute)
                    @if(strlen(trim($attribute)) > 0)
                        <span class="badge badge-info">{{ $attribute }}</span>
                    @endif
                @endforeach
            </td>
        </tr>
        <tr>
            <td width="10%">{{ trans('cruds.workstation.fields.description') }}</td>
            <td width="80%" colspan="5">{!! $workstation->description !!}</td>
            <td width="10%" align="center">
                <img src="{{ $workstation->icon_id === null ? '/images/workstation.png' : route('admin.documents.show', $workstation->icon_id) }}" width='60' height='60'/>
            </td>
       </tr>
    </tbody>
</table>
