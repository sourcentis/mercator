@props([
    'physicalSwitch',
    'withLink' => false
])
<table class="table table-bordered table-striped table-report" id="{{ $physicalRouter->getUID() }}">
    <tbody>
        <tr>
            <th width="10%">
                {{ ($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters()) ? trans('cruds.perimeter.title_short').' / ' : '' }}{{ trans('cruds.physicalRouter.fields.name') }}
            </th>
            <td width="20%">
                @if (($hasMultiplePerimeters ?? auth()->user()->hasMultiplePerimeters())){{ $physicalRouter->perimeter->name }} / @endif
            @if($withLink)
            @canShow($physicalRouter)
            <a href="{{ route('admin.physical-routers.show', $physicalRouter->id) }}">{{ $physicalRouter->name }}</a>
            @elsecanShow
            {{ $physicalRouter->name }}
            @endcanShow
            @else
            {{ $physicalRouter->name }}
            @endif
            </td>
            <th width="10%">
                {{ trans('cruds.physicalRouter.fields.type') }}
            </th>
            <td width="20%">
                {{ $physicalRouter->type }}
            </td>
            <th width="10%">
                {{ trans('cruds.physicalRouter.fields.attributes') }}
            </th>
            <td width="30%">
                @foreach(explode(" ", (string) $physicalRouter->attributes) as $attribute)
                    @if(strlen(trim($attribute)) > 0)
                        <span class="badge badge-info">{{ $attribute }}</span>
                    @endif
                @endforeach
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.physicalRouter.fields.description') }}
            </th>
            <td colspan="5">
                {!! $physicalRouter->description !!}
            </td>
        </tr>
        @canAccessAny(App\Models\Site::class, App\Models\Building::class, App\Models\Bay::class)
        <tr>
            <th width="10%">
                {{ trans('cruds.physicalRouter.fields.site') }}
            </th>
            <td width="20%">
                @if ($physicalRouter->site!=null)
                    @canShow($physicalRouter->site)
                        <a href="{{ route('admin.sites.show', $physicalRouter->site->id) }}">
                        {{ $physicalRouter->site->name ?? '' }}
                        </a>
                    @elsecanShow
                        {{ $physicalRouter->site->name ?? '' }}
                    @endcanShow
                @endif
            </td>
            <th width="10%">
                {{ trans('cruds.physicalRouter.fields.building') }}
            </th>
            <td width="20%">
                @if ($physicalRouter->building!=null)
                    @canShow($physicalRouter->building)
                        <a href="{{ route('admin.buildings.show', $physicalRouter->building->id) }}">
                        {{ $physicalRouter->building->name ?? '' }}
                        </a>
                    @elsecanShow
                        {{ $physicalRouter->building->name ?? '' }}
                    @endcanShow
                @endif
            </td>
            <th width="10%">
                {{ trans('cruds.physicalRouter.fields.bay') }}
            </th>
            <td>
                @if ($physicalRouter->bay!=null)
                    @canShow($physicalRouter->bay)
                        <a href="{{ route('admin.bays.show', $physicalRouter->bay->id) }}">
                        {{ $physicalRouter->bay->name ?? '' }}
                        </a>
                    @elsecanShow
                        {{ $physicalRouter->bay->name ?? '' }}
                    @endcanShow
                @endif
            </td>
        </tr>
        @endcanAccessAny
        @canAccess(App\Models\Router::class)
        <tr>
            <th>
                {{ trans('cruds.physicalRouter.fields.routers') }}
            </th>
            <td colspan="5">
                @foreach($physicalRouter->routers as $router)
                    @canShow($router)
                        <a href="{{ route('admin.routers.show', $router->id) }}">{{ $router->name }}</a>
                    @elsecanShow
                        {{ $router->name }}
                    @endcanShow
                    @if(!$loop->last)
                    ,
                    @endif
                @endforeach
            </td>
        </tr>
        @endcanAccess
        @canAccess(App\Models\Vlan::class)
        <tr>
            <th>
                {{ trans('cruds.physicalRouter.fields.vlan') }}
            </th>
            <td colspan="5">
                @foreach($physicalRouter->vlans as $vlan)
                    @canShow($vlan)
                        <a href="{{ route('admin.vlans.show', $vlan->id) }}">{{ $vlan->name }}</a>
                    @elsecanShow
                        {{ $vlan->name }}
                    @endcanShow
                    @if(!$loop->last)
                    ,
                    @endif
                @endforeach
            </td>
        </tr>
        @endcanAccess
    </tbody>
</table>
