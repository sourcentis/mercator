@props([
    'network',
    'withLink' => false,
])

<table class="table table-bordered table-striped table-report" id="{{ $network->getUID() }}">
    <tbody>
        <tr>
            <th width="10%">
                {{ auth()->user()->hasMultiplePerimeters() ? trans('cruds.perimeter.title_short').' / ' : '' }}{{ trans('cruds.network.fields.name') }}
            </th>
            <td width="20%">
                @if (auth()->user()->hasMultiplePerimeters()){{ $network->perimeter->name }} / @endif
            @if ($withLink)
            @canShow($network)
            <a href="{{ route('admin.networks.show', $network->id) }}">{{ $network->name }}</a>
            @elsecanShow
            {{ $network->name }}
            @endcanShow
            @else
                {{ $network->name }}
            @endif
            </td>
            <th width="10%">
                {{ trans('cruds.network.fields.type') }}
            </th>
            <th width="20%">
                {{ $network->type }}
            </td>
            <th width="10%">
                {{ trans('cruds.network.fields.attributes') }}
            </th>
            <td>
                @foreach(explode(" ", $network->attributes) as $attribute)
                    <span class="badge badge-info">{{ $attribute }}</span>
                @endforeach
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.network.fields.description') }}
            </th>
            <td colspan="5">
                {!! clean($network->description ?? '') !!}
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.network.fields.protocol_type') }}
            </th>
            <td colspan="5">
                {{ $network->protocol_type }}
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.network.fields.responsible') }}
            </th>
            <td colspan="5">
                {{ $network->responsible }}
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.network.fields.responsible_sec') }}
            </th>
            <td colspan="5">
                {{ $network->responsible_sec }}
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.information.fields.security_need') }}
                @if (config('mercator-config.parameters.security_need_auth'))
                + {{ trans("global.authenticity_short") }}
                @endif
            </th>
            <td colspan="5">
                {{ trans('global.confidentiality') }} :
                    @if ($network->security_need_c==0){{ trans('global.none') }}@endif
                    @if ($network->security_need_c==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                    @if ($network->security_need_c==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                    @if ($network->security_need_c==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                    @if ($network->security_need_c==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
                &nbsp;
                {{ trans('global.integrity') }} :
                    @if ($network->security_need_i==0){{ trans('global.none') }}@endif
                    @if ($network->security_need_i==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                    @if ($network->security_need_i==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                    @if ($network->security_need_i==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                    @if ($network->security_need_i==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
                &nbsp;
                {{ trans('global.availability') }} :
                    @if ($network->security_need_a==0){{ trans('global.none') }}@endif
                    @if ($network->security_need_a==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                    @if ($network->security_need_a==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                    @if ($network->security_need_a==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                    @if ($network->security_need_a==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
                &nbsp;
                {{ trans('global.tracability') }} :
                    @if ($network->security_need_t==0){{ trans('global.none') }}@endif
                    @if ($network->security_need_t==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                    @if ($network->security_need_t==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                    @if ($network->security_need_t==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                    @if ($network->security_need_t==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
                @if (config('mercator-config.parameters.security_need_auth'))
                &nbsp;
                {{ trans('global.authenticity') }} :
                    @if ($network->security_need_auth==0){{ trans('global.none') }}@endif
                    @if ($network->security_need_auth==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                    @if ($network->security_need_auth==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                    @if ($network->security_need_auth==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                    @if ($network->security_need_auth==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
                @endif
                </td>
            </tr>
        @canAccess(App\Models\Subnetwork::class)
        <tr>
            <th>
                {{ trans('cruds.network.fields.subnetworks') }}
            </th>
            <td colspan="5">
                @foreach($network->subnetworks as $subnetwork)
                    @canShow($subnetwork)
                        <a href="{{ route('admin.subnetworks.show', $subnetwork->id) }}">
                            {{ $subnetwork->name }}
                        </a>
                    @elsecanShow
                        {{ $subnetwork->name }}
                    @endcanShow
                    @if ($network->subnetworks->last()<>$subnetwork)
                    ,
                    @endif
                @endforeach
            </td>
        </tr>
        @endcanAccess
    </tbody>
</table>
