@props([
    'physicalSwitch',
    'withLink' => false
])
<table class="table table-bordered table-striped table-report" id="{{ $physicalSwitch->getUID() }}">
    <tbody>
        <tr>
            <th width="10%">
                {{ ($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters()) ? trans('cruds.perimeter.title_short').' / ' : '' }}{{ trans('cruds.physicalSwitch.fields.name') }}
            </th>
            <td width="20%">
                @if (($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters())){{ $physicalSwitch->perimeter->name }} / @endif
            @if ($withLink)
            @canShow($physicalSwitch)
            <a href="{{ route('admin.physical-switches.show', $physicalSwitch->id) }}">{{ $physicalSwitch->name }}</a>
            @elsecanShow
            {{ $physicalSwitch->name }}
            @endcanShow
            @else
            {{ $physicalSwitch->name }}
            @endif
            </td>
            <th width="10%">
                {{ trans('cruds.physicalSwitch.fields.type') }}
            </th>
            <td width="20%">
                {{ $physicalSwitch->type }}
            </td>
            <th width="10%">
                {{ trans('cruds.physicalSwitch.fields.attributes') }}
            </th>
            <td width="30%" colspan="2">
                @foreach(explode(" ", (string) $physicalSwitch->attributes) as $attribute)
                    @if(strlen(trim($attribute)) > 0)
                        <span class="badge badge-info">{{ $attribute }}</span>
                    @endif
                @endforeach
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.physicalSwitch.fields.description') }}
            </th>
            <td colspan="5">
                {!! $physicalSwitch->description !!}
            </td>
            <td style="text-align: center; width: 10%">
                @if ($physicalSwitch->icon_id === null)
                    <img src='/images/switch.png' width='60' height='60'>
                @else
                    <img src='{{ route('admin.documents.show', $physicalSwitch->icon_id) }}' width='60' height='60'>
                @endif
            </td>
        </tr>
        @canAccess(App\Models\NetworkSwitch::class)
        <tr>
            <th>
                {{ trans('cruds.physicalSwitch.fields.network_switches') }}
            </th>
            <td colspan="6">
                @foreach($physicalSwitch->networkSwitches as $networkSwitch)
                    @canShow($networkSwitch)
                        <a href="{{ route('admin.network-switches.show', $networkSwitch->id) }}">
                        {{ $networkSwitch->name }}
                        </a>
                    @elsecanShow
                        {{ $networkSwitch->name }}
                    @endcanShow
                    @if (!$loop->last)
                    ,
                    @endif
                @endforeach
            </td>
        </tr>
        @endcanAccess
        @canAccessAny(App\Models\Site::class, App\Models\Building::class, App\Models\Bay::class)
        <tr>
            <th>
                {{ trans('cruds.physicalSwitch.fields.site') }}
            </th>
            <td>
                @if ($physicalSwitch->site!=null)
                    @canShow($physicalSwitch->site)
                        <a href="{{ route('admin.sites.show', $physicalSwitch->site->id) }}">
                        {{ $physicalSwitch->site->name ?? '' }}
                        </a>
                    @elsecanShow
                        {{ $physicalSwitch->site->name ?? '' }}
                    @endcanShow
                @endif
            </td>
            <th>
                {{ trans('cruds.physicalSwitch.fields.building') }}
            </th>
            <td>
                @if ($physicalSwitch->building!=null)
                    @canShow($physicalSwitch->building)
                        <a href="{{ route('admin.buildings.show', $physicalSwitch->building->id) }}">
                        {{ $physicalSwitch->building->name ?? '' }}
                        </a>
                    @elsecanShow
                        {{ $physicalSwitch->building->name ?? '' }}
                    @endcanShow
                @endif
            </td>
            <th>
                {{ trans('cruds.physicalSwitch.fields.bay') }}
            </th>
            <td colspan="2">
                @if ($physicalSwitch->bay!=null)
                    @canShow($physicalSwitch->bay)
                        <a href="{{ route('admin.bays.show', $physicalSwitch->bay->id) }}">
                        {{ $physicalSwitch->bay->name ?? '' }}
                        </a>
                    @elsecanShow
                        {{ $physicalSwitch->bay->name ?? '' }}
                    @endcanShow
                @endif
            </td>
        </tr>
        @endcanAccessAny
    </tbody>
</table>
