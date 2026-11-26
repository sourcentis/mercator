@props([
    'database',
    'withLink' => false,
    'hasMultiplePerimeters' => null,
])
@php($hasMultiplePerimeters ??= auth()->user()->hasMultiplePerimeters())
<table class="table table-bordered table-striped table-report" id="{{ $database->getUID() }}">
    <tbody>
        <tr>
            <th style="width: 10%;">
                {{ $hasMultiplePerimeters ? trans('cruds.perimeter.title_short').' / ' : '' }}{{ trans('cruds.database.fields.name') }}
            </th>
            <td style="width: 20%;">
                @if ($hasMultiplePerimeters){{ $database->perimeter->name }} / @endif
            @if ($withLink)
                @canShow($database)
                <a href="{{ route('admin.databases.show', $database) }}">
                {{ $database->name }}
                </a>
                @elsecanShow
                {{ $database->name }}
                @endcanShow
            @else
                {{ $database->name }}
            @endif
            </td>
            <th style="width: 10%;">
                {{ trans('cruds.database.fields.type') }}
            </th>
            <td style="width: 20%;">
                {{ $database->type }}
            </td>
            <th style="width: 10%;">
                {{ trans('cruds.database.fields.attributes') }}
            </th>
            <td style="width: 30%;" colspan="2">
                @foreach(explode(" ", (string) $database->attributes) as $attribute)
                    @if(strlen(trim($attribute)) > 0)
                        <span class="badge badge-info">{{ $attribute }}</span>
                    @endif
                @endforeach
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.database.fields.description') }}
            </th>
            <td colspan=5>
                {!! $database->description !!}
            </td>
            <td style="text-align: center; width: 10%">
                @if ($database->icon_id === null)
                    <img src='/images/database.png' width='60' height='60'>
                @else
                    <img src='{{ route('admin.documents.show', $database->icon_id) }}' width='60' height='60'>
                @endif
            </td>
        </tr>
        <tr>
            <th style="width: 10%;">
                {{ trans('cruds.database.fields.entities') }}
            </th>
            <td style="width: 20%;">
                @foreach($database->entities as $key => $entities)
                    <span class="label label-info">{{ $entities->name }}</span>
                    @if (!$loop->last)
                    ,
                    @endif
                @endforeach
            </td>
            <th style="width: 10%;">
                {{ trans('cruds.database.fields.entity_resp') }}
            </th>
            <td style="width: 20%;">
                {{ $database->entityResp->name ?? '' }}
            </td>
            <th style="width: 10%;">
                {{ trans('cruds.database.fields.responsible') }}
            </th>
            <td colspan="2">
                {{ $database->responsible }}
            </td>
        </tr>
        @canAccess(App\Models\Information::class)
        <tr>
            <th>
                {{ trans('cruds.database.fields.informations') }}
            </th>
            <td colspan=6>
                @foreach($database->informations as $information)
                    @canShow($information)
                        <a href="{{ route('admin.information.show', $information->id) }}">{{ $information->name }}</a>
                    @elsecanShow
                        {{ $information->name }}
                    @endcanShow
                    @if (!$loop->last)
                    ,
                    @endif
                @endforeach
            </td>
        </tr>
        @endcanAccess
        @canAccess(App\Models\Application::class)
        <tr>
            <th>
                {{ trans('cruds.database.fields.applications') }}
            </th>
            <td colspan=6>
                @foreach($database->applications as $application)
                    @canShow($application)
                        <a href="{{ route('admin.applications.show', $application->id) }}">{{ $application->name }}</a>
                    @elsecanShow
                        {{ $application->name }}
                    @endcanShow
                    @if (!$loop->last)
                    ,
                    @endif
                @endforeach
            </td>
        </tr>
        @endcanAccess
        @canAccess(App\Models\LogicalServer::class)
        <tr>
            <th>
                {{ trans('cruds.database.fields.logical_servers') }}
            </th>
            <td colspan="3">
                @foreach($database->logicalServers as $logicalServer)
                    @canShow($logicalServer)
                        <a href="{{ route('admin.logical-servers.show', $logicalServer->id) }}">{{ $logicalServer->name }}</a>
                    @elsecanShow
                        {{ $logicalServer->name }}
                    @endcanShow
                    @if (!$loop->last)
                    ,
                    @endif
                @endforeach
            </td>
            <th>
                {{ trans('cruds.database.fields.external') }}
            </th>
            <td colspan="2">
                {{ $database->external }}
            </td>
        </tr>
        @endcanAccess
        @canAccess(App\Models\Container::class)
        <tr>
            <th>
                {{ trans('cruds.database.fields.containers') }}
            </th>
            <td colspan="6">
                @foreach($database->containers as $container)
                    @canShow($container)
                        <a href="{{ route('admin.containers.show', $container->id) }}">{{ $container->name }}</a>
                    @elsecanShow
                        {{ $container->name }}
                    @endcanShow
                    @if (!$loop->last)
                    ,
                    @endif
                @endforeach
            </td>
        </tr>
        @endcanAccess
        <tr>
            <th>
                {{ trans('cruds.database.fields.security_need') }}
            </th>
            <td colspan="6">
            {{ trans('global.confidentiality') }} :
                @if ($database->security_need_c==0){{ trans('global.none') }}@endif
                @if ($database->security_need_c==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                @if ($database->security_need_c==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                @if ($database->security_need_c==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                @if ($database->security_need_c==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
            &nbsp;
            {{ trans('global.integrity') }} :
                @if ($database->security_need_i==0){{ trans('global.none') }}@endif
                @if ($database->security_need_i==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                @if ($database->security_need_i==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                @if ($database->security_need_i==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                @if ($database->security_need_i==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
            &nbsp;
            {{ trans('global.availability') }} :
                @if ($database->security_need_a==0){{ trans('global.none') }}@endif
                @if ($database->security_need_a==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                @if ($database->security_need_a==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                @if ($database->security_need_a==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                @if ($database->security_need_a==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
            &nbsp;
            {{ trans('global.tracability') }} :
                @if ($database->security_need_t==0){{ trans('global.none') }}@endif
                @if ($database->security_need_t==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                @if ($database->security_need_t==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                @if ($database->security_need_t==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                @if ($database->security_need_t==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
            &nbsp;
            @if (config('mercator.parameters.security_need_auth'))
            {{ trans('global.authenticity') }} :
                @if ($database->security_need_auth==0){{ trans('global.none') }}@endif
                @if ($database->security_need_auth==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                @if ($database->security_need_auth==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                @if ($database->security_need_auth==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                @if ($database->security_need_auth==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
            @endif
            </td>
        </tr>
    </tbody>
</table>
