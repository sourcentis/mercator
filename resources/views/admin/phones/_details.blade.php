@props([
    'phone',
    'withLink' => false,
])
<table class="table table-bordered table-striped table-report" id="{{ $phone->getUID() }}">
    <tbody>
        <tr>
            <th width="10%">
                {{ ($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters()) ? trans('cruds.perimeter.title_short').' / ' : '' }}{{ trans('cruds.phone.fields.name') }}
            </th>
            <td width="20%">
                @if (($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters())){{ $phone->perimeter->name }} / @endif
            @if($withLink)
            @canShow($phone)
            <a href="{{ route('admin.phones.show', $phone) }}">{{ $phone->name }}</a>
            @elsecanShow
            {{ $phone->name }}
            @endcanShow
            @else
            {{ $phone->name }}
            @endif
            </td>
            <th width="10%">
                {{ trans('cruds.phone.fields.type') }}
            </th>
            <td width="20%">
                {{ $phone->type }}
            </td>
            <th width="10%">
                {{ trans('cruds.phone.fields.attributes') }}
            </th>
            <td width="30%">
                @foreach(explode(" ", (string) $phone->attributes) as $attribute)
                    @if(strlen(trim($attribute)) > 0)
                        <span class="badge badge-info">{{ $attribute }}</span>
                    @endif
                @endforeach
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.phone.fields.description') }}
            </th>
            <td colspan="5">
                {!! $phone->description !!}
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.phone.fields.address_ip') }}
            </th>
            <td colspan="5">
                {{ $phone->address_ip }}
            </td>
        </tr>
        @canAccess(App\Models\Site::class)
        <tr>
            <th>
                {{ trans('cruds.phone.fields.site') }}
            </th>
            <td>
                @if ($phone->site!==null)
                    @canShow($phone->site)
                        <a href="{{ route('admin.sites.show', $phone->site_id) }}">{{ $phone->site->name }}</a>
                    @elsecanShow
                        {{ $phone->site->name }}
                    @endcanShow
                @endif
            </td>
        @endcanAccess
        @canAccess(App\Models\Building::class)
            <th>
                {{ trans('cruds.phone.fields.building') }}
            </th>
            <td>
                @if ($phone->building!==null)
                    @canShow($phone->building)
                        <a href="{{ route('admin.buildings.show', $phone->building_id) }}">{{ $phone->building->name }}</a>
                    @elsecanShow
                        {{ $phone->building->name }}
                    @endcanShow
                @endif
            </td>
        </tr>
        @endcanAccess
    </tbody>
</table>
