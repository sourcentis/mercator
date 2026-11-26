@props([
    'physicalSecurityDevice',
    'withLink' => false,
])
<table class="table table-bordered table-striped table-report" id="{{ $physicalSecurityDevice->getUID() }}">
    <tbody>
    <tr>
        <th width='10%'>
            {{ ($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters()) ? trans('cruds.perimeter.title_short').' / ' : '' }}{{ trans('cruds.physicalSecurityDevice.fields.name') }}
        </th>
        <td width="20%">
                @if (($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters())){{ $physicalSecurityDevice->perimeter->name }} / @endif
        @if($withLink)
        @canShow($physicalSecurityDevice)
        <a href="{{ route('admin.physical-security-devices.show', $physicalSecurityDevice->id) }}">{{ $physicalSecurityDevice->name }}</a>
        @elsecanShow
        {{ $physicalSecurityDevice->name }}
        @endcanShow
        @else
            {{ $physicalSecurityDevice->name }}
        @endif
        </td>
        <th width='10%'>
            {{ trans('cruds.physicalSecurityDevice.fields.type') }}
        </th>
        <td width="20%">
            {{ $physicalSecurityDevice->type }}
        </td>
        <th width='10%'>
            {{ trans('cruds.physicalSecurityDevice.fields.attributes') }}
        </th>
        <td width="30%" colspan="2">
            @foreach(explode(" ",$physicalSecurityDevice->attributes) as $attribute)
                <span class="badge badge-info">{{ $attribute }}</span>
            @endforeach
        </td>
    </tr>
    <tr>
        <th>
            {{ trans('cruds.physicalSecurityDevice.fields.description') }}
        </th>
        <td colspan="5">
            {!! $physicalSecurityDevice->description !!}
        </td>
        <td width="10%">
            @if ($physicalSecurityDevice->icon_id === null)
                <img src='/images/securitydevice.png' width='60' height='60'>
            @else
                <img src='{{ route('admin.documents.show', $physicalSecurityDevice->icon_id) }}'
                     width='60'
                     height='60'>
            @endif
        </td>
    </tr>
    <tr>
        <th>
            {{ trans('cruds.physicalSecurityDevice.fields.address_ip') }}
        </th>
        <td colspan="6">
            {{ $physicalSecurityDevice->address_ip }}
        </td>
    </tr>
    @canAccess(App\Models\SecurityDevice::class)
    <tr>
        <th>
            {{ trans('cruds.physicalSecurityDevice.fields.security_devices') }}
        </th>
        <td colspan="6">
            @foreach($physicalSecurityDevice->securityDevices as $device)
                @canShow($device)
                    <a href="{{ route('admin.security-devices.show', $device->id) }}">{{ $device->name }}</a>
                @elsecanShow
                    {{ $device->name }}
                @endcanShow
                @if(!$loop->last)
                    ,
                @endif
            @endforeach
        </td>
    </tr>
    @endcanAccess
    @canAccessAny(App\Models\Site::class, App\Models\Building::class, App\Models\Bay::class)
    <tr>
        <th>
            {{ trans('cruds.physicalSecurityDevice.fields.site') }}
        </th>
        <td>
            @if($physicalSecurityDevice->site!=null)
                @canShow($physicalSecurityDevice->site)
                    <a href="{{ route('admin.sites.show', $physicalSecurityDevice->site->id) }}">
                        {{ $physicalSecurityDevice->site->name ?? '' }}
                    </a>
                @elsecanShow
                    {{ $physicalSecurityDevice->site->name ?? '' }}
                @endcanShow
            @endif
        </td>
        <th>
            {{ trans('cruds.physicalSecurityDevice.fields.building') }}
        </th>
        <td>
            @if($physicalSecurityDevice->building!=null)
                @canShow($physicalSecurityDevice->building)
                    <a href="{{ route('admin.buildings.show', $physicalSecurityDevice->building->id) }}">
                        {{ $physicalSecurityDevice->building->name ?? '' }}
                    </a>
                @elsecanShow
                    {{ $physicalSecurityDevice->building->name ?? '' }}
                @endcanShow
            @endif
        </td>
        <th>
            {{ trans('cruds.physicalSecurityDevice.fields.bay') }}
        </th>
        <td colspan="2">
            @if($physicalSecurityDevice->bay!=null)
                @canShow($physicalSecurityDevice->bay)
                    <a href="{{ route('admin.bays.show', $physicalSecurityDevice->bay->id) }}">
                        {{ $physicalSecurityDevice->bay->name ?? '' }}
                    </a>
                @elsecanShow
                    {{ $physicalSecurityDevice->bay->name ?? '' }}
                @endcanShow
            @endif
        </td>
    </tr>
    @endcanAccessAny
    </tbody>
</table>
