@props([
    'physicalServer',
    'withLink' => false
])
<table class="table table-bordered table-striped table-report" id="{{ $physicalServer->getUID() }}">
    <tbody>
    <tr>
        <th width="10%">
            {{ ($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters()) ? trans('cruds.perimeter.title_short').' / ' : '' }}{{ trans('cruds.physicalServer.fields.name') }}
        </th>
        <td width="20%">
                @if (($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters())){{ $physicalServer->perimeter->name }} / @endif
        @if($withLink)
            @canShow($physicalServer)
                <a href="{{ route('admin.physical-servers.show', $physicalServer) }}">{{ $physicalServer->name }}</a>
            @elsecanShow
                {{ $physicalServer->name }}
            @endcanShow
        @else
            {{ $physicalServer->name }}
        @endif
        </td>
        <th width="10%">
            {{ trans('cruds.physicalServer.fields.type') }}
        </th>
        <td width="20%">
            {{ $physicalServer->type }}
        </td>
        <th width="10%">
            {{ trans('cruds.physicalServer.fields.attributes') }}
        </th>
        <td width="30%" colspan="2">
            @foreach(explode(" ", (string) $physicalServer->attributes) as $attribute)
                @if(strlen(trim($attribute)) > 0)
                    <span class="badge badge-info">{{ $attribute }}</span>
                @endif
            @endforeach
        </td>
    </tr>
    <th>
        {{ trans('cruds.physicalServer.fields.description') }}
    </th>
    <td colspan="5">
        {!! $physicalServer->description !!}
    </td>
    <td width="10%" align="center">
        @if ($physicalServer->icon_id === null)
            <img src='/images/server.png' width='60' height='60'>
        @else
            <img src='{{ route('admin.documents.show', $physicalServer->icon_id) }}' width='60'
                 height='60'>
        @endif
    </td>
    </tbody>
</table>
