@props([
    'process',
    'withLink' => false,
])

<table class="table table-bordered table-striped table-report" id="{{ $process->getUID() }}">
    <tbody>
        <tr>
            <th width="10%">
                {{ auth()->user()->hasMultiplePerimeters() ? trans('cruds.perimeter.title_short').' / ' : '' }}{{ trans('cruds.process.fields.name') }}
            </th>
            <td width="20%">
                @if (auth()->user()->hasMultiplePerimeters()){{ $process->perimeter->name }} / @endif
                @if ($withLink)
                @canShow($process)
                <a href="{{ route('admin.processes.show', $process->id) }}">{{ $process->name }}</a>
                @elsecanShow
                {{ $process->name }}
                @endcanShow
                @else
                {{ $process->name }}
                @endif
            </td>
            <th width="10%">
                {{ trans('cruds.process.fields.type') }}
            </th>
            <td width="20%">
                {{ $process->type }}
            </td>
            <th width="10%">
                {{ trans('cruds.process.fields.attributes') }}
            </th>
            <td colspan="30%" colspan="2">
                @foreach(explode(" ", (string) $process->attributes) as $attribute)
                    @if(strlen(trim($attribute)) > 0)
                        <span class="badge badge-info">{{ $attribute }}</span>
                    @endif
                @endforeach
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.process.fields.description') }}
            </th>
            <td colspan="5">
                {!! clean($process->description ?? '') !!}
            </td>
            <td align="center" width="10%">
                @if ($process->icon_id === null)
                <img src='/images/process.png' width='60' height='60'>
                @else
                <img src='{{ route('admin.documents.show', $process->icon_id) }}' width='60' height='60'>
                @endif
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.process.fields.in_out') }}
            </th>
            <td colspan="6">
                {!! clean($process->in_out ?? '') !!}
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.process.fields.security_need') }}
            </th>
            <td colspan="3">
            {{ trans('global.confidentiality') }} :
                @if ($process->security_need_c==0){{ trans('global.none') }}@endif
                @if ($process->security_need_c==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                @if ($process->security_need_c==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                @if ($process->security_need_c==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                @if ($process->security_need_c==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
            &nbsp;
            {{ trans('global.integrity') }} :
                @if ($process->security_need_i==0){{ trans('global.none') }}@endif
                @if ($process->security_need_i==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                @if ($process->security_need_i==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                @if ($process->security_need_i==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                @if ($process->security_need_i==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
            &nbsp;
            {{ trans('global.availability') }} :
                @if ($process->security_need_a==0){{ trans('global.none') }}@endif
                @if ($process->security_need_a==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                @if ($process->security_need_a==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                @if ($process->security_need_a==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                @if ($process->security_need_a==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
            &nbsp;
            {{ trans('global.tracability') }} :
                @if ($process->security_need_t==0){{ trans('global.none') }}@endif
                @if ($process->security_need_t==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                @if ($process->security_need_t==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                @if ($process->security_need_t==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                @if ($process->security_need_t==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
            @if (config('mercator-config.parameters.security_need_auth'))
            &nbsp;
            {{ trans('global.authenticity') }} :
                @if ($process->security_need_auth==0){{ trans('global.none') }}@endif
                @if ($process->security_need_auth==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                @if ($process->security_need_auth==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                @if ($process->security_need_auth==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                @if ($process->security_need_auth==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
            @endif
            </td>
        </tr>
        <tr>
            <th>
                {{ trans('cruds.process.fields.owner') }}
            </th>
            <td colspan="6">
                {{ $process->owner }}
            </td>
        </tr>

        <tr>
            <th>
                {{ trans('cruds.process.fields.macroprocessus') }}
            </th>
            <td colspan="6">
                @if($process->macroProcess!=null)
                    @canShow($process->macroProcess)
                        <a href="{{ route('admin.macro-processuses.show', $process->macroProcess->id) }}">
                            {{ $process->macroProcess->name }}
                        </a>
                    @elsecanShow
                        {{ $process->macroProcess->name }}
                    @endcanShow
                @endif
            </td>

        </tr>
        @canAccess(App\Models\Activity::class)
        <tr>
            <th>
                {{ trans('cruds.process.fields.activities') }}
            </th>
            <td colspan="6">
                @foreach($process->activities as $activity)
                    @canShow($activity)
                        <a href="{{ route('admin.activities.show', $activity->id) }}">
                        {{ $activity->name }}
                        </a>
                    @elsecanShow
                        {{ $activity->name }}
                    @endcanShow
                    @if (!$loop->last)
                    ,
                    @endif
                @endforeach
            </td>
        </tr>
        @endcanAccess

        @canAccess(App\Models\Entity::class)
        <tr>
            <th>
                {{ trans('cruds.process.fields.entities') }}
            </th>
            <td colspan="6">
                @foreach($process->entities as $entity)
                    @canShow($entity)
                        <a href="{{ route('admin.entities.show', $entity->id) }}">
                        {{ $entity->name }}
                        </a>
                    @elsecanShow
                        {{ $entity->name }}
                    @endcanShow
                    @if (!$loop->last)
                    ,
                    @endif
                @endforeach
            </td>
        </tr>
        @endcanAccess
        @canAccess(App\Models\Information::class)
        <tr>
            <th>
                {{ trans('cruds.process.fields.informations') }}
            </th>
            <td colspan="6">
                @foreach($process->information as $info)
                    @canShow($info)
                        <a href="{{ route('admin.information.show', $info->id) }}">
                        {{ $info->name }}
                        </a>
                    @elsecanShow
                        {{ $info->name }}
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
                {{ trans('cruds.process.fields.applications') }}
            </th>
            <td colspan="6">
                @foreach($process->applications as $application)
                    @canShow($application)
                        <a href="{{ route('admin.applications.show', $application->id) }}">
                        {{ $application->name }}
                        </a>
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
        @if($process->graphs()->count()>0)
        <tr>
            <th>
                <span style="border: 2px solid grey;
                     color: darkred;
                     padding: 6px 14px;
                     border-radius: 6px;
                     display: inline-flex;
                     align-items: center;
                     gap: 8px;
                     font-weight: 600;
                     background: #eff6ff;">
                    <i class="bi bi-diagram-2-fill" style="font-size: 1.3em;"></i>
                    <span style="color: black;">
                        BPMN
                    </span>
                </span>
            </th>
            <td colspan="5" style="vertical-align: middle;">
                @foreach($process->graphs() as $graph)
                    @canShow($graph)
                        <a href="{{ route('admin.bpmn.show', $graph->id) }}">
                            {{ $graph->name }}
                        </a>
                    @elsecanShow
                        {{ $graph->name }}
                    @endcanShow
                    @if (!$loop->last)
                    ,
                    @endif
                @endforeach
            </td>
        </tr>
        @endif
    </tbody>
</table>
