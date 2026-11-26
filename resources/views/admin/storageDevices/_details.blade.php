@props([
    'storageDevice',
    'withLink' => false
])
<table class="table table-bordered table-striped table-report" id="{{ $storageDevice->getUID() }}">
    <tbody>
        <tr>
            <th width="10%">
                {{ ($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters()) ? trans('cruds.perimeter.title_short').' / ' : '' }}{{ trans('cruds.storageDevice.fields.name') }}
            </th>
            <td width="20%">
                @if (($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters())){{ $storageDevice->perimeter->name }} / @endif
            @if ($withLink)
                @canShow($storageDevice)
                <a href="{{ route('admin.storage-devices.show', $storageDevice->id) }}">{{ $storageDevice->name }}</a>
                @elsecanShow
                    {{ $storageDevice->name }}
                @endcanShow
            @else
                {{ $storageDevice->name }}
            @endif
            </td>
            <th width="10%">
                {{ trans('cruds.storageDevice.fields.type') }}
            </th>
            <td width="20%">
                {{ $storageDevice->type }}
            </td>
            <th width="10%">
                {{ trans('cruds.storageDevice.fields.attributes') }}
            </th>
            <td width="30%"colspan="2">
                @foreach(explode(" ", (string) $storageDevice->attributes) as $attribute)
                    @if(strlen(trim($attribute)) > 0)
                        <span class="badge badge-info">{{ $attribute }}</span>
                    @endif
                @endforeach
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.storageDevice.fields.description') }}
            </th>
            <td colspan='5'>
                {!! $storageDevice->description !!}
            </td>
            <td width="10%" align="center">
                @if ($storageDevice->icon_id === null)
                    <img src='/images/storagedev.png' width='60' height='60'>
                @else
                    <img src='{{ route('admin.documents.show', $storageDevice->icon_id) }}' width='60'
                         height='60'>
                @endif
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.storageDevice.fields.address_ip') }}
            </th>
            <td colspan="6">
                {{ $storageDevice->address_ip }}
            </td>
        </tr>
        @canAccessAny(App\Models\Site::class, App\Models\Building::class, App\Models\Bay::class)
        <tr>
            <th width="10%">
                {{ trans('cruds.storageDevice.fields.site') }}
            </th>
            <td width="22%">
                @if ($storageDevice->site!=null)
                    @canShow($storageDevice->site)
                        <a href="{{ route('admin.sites.show', $storageDevice->site->id) }}">
                        {{ $storageDevice->site->name ?? '' }}
                        </a>
                    @elsecanShow
                        {{ $storageDevice->site->name ?? '' }}
                    @endcanShow
                @endif
            </td>
            <th width="10%">
                {{ trans('cruds.storageDevice.fields.building') }}
            </th>
            <td width="22%">
                @if ($storageDevice->building!=null)
                    @canShow($storageDevice->building)
                        <a href="{{ route('admin.buildings.show', $storageDevice->building->id) }}">
                        {{ $storageDevice->building->name ?? '' }}
                        </a>
                    @elsecanShow
                        {{ $storageDevice->building->name ?? '' }}
                    @endcanShow
                @endif
            </td>
            <th width="10%">
                {{ trans('cruds.storageDevice.fields.bay') }}
            </th>
            <td width="22%" colspan="2">
                @if ($storageDevice->bay!=null)
                    @canShow($storageDevice->bay)
                        <a href="{{ route('admin.bays.show', $storageDevice->bay->id) }}">
                        {{ $storageDevice->bay->name ?? '' }}
                        </a>
                    @elsecanShow
                        {{ $storageDevice->bay->name ?? '' }}
                    @endcanShow
                @endif
            </td>
        </tr>
        @endcanAccessAny
    </tbody>
</table>
