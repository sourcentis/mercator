@extends('layouts.admin')

@section('title')
    {{ $application->name }}
@endsection

@section('content')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-default" href="{{ route('admin.applications.index') }}">
                {{ trans('global.back_to_list') }}
            </a>

            @can('explore_access')
            <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$application->getUID()}}">
                {{ trans('global.explore') }}
            </a>

            @endcan
            @canEdit($application)
                <a class="btn btn-info" href="{{ route('admin.applications.edit', $application->id) }}">
                    {{ trans('global.edit') }}
                </a>
            @endcanEdit

            @can('application_create')
                <a class="btn btn-warning" href="{{ route('admin.applications.clone', $application->id) }}">
                    {{ trans('global.clone') }}
                </a>
            @endcan

            @if(auth()->user()->can('application_delete'))
                <form action="{{ route('admin.applications.destroy', $application->id) }}" method="POST"
                      data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans('cruds.application.title_singular') }}
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-body">
            @include('admin.applications._details', [
                'application' => $application,
                'withLink' => false,
            ])
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans("cruds.menu.ecosystem.title_short") }}
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report">
                <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.application.fields.responsible') }}
                    </th>
                    <td>
                        {{ $application->responsible }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.application.fields.entity_resp') }}
                    </th>
                    <td>
                        @if ($application->entityResp!=null)
                            @canShow($application->entityResp)
                                <a href="{{ route('admin.entities.show', $application->entity_resp_id) }}">
                                    {{ $application->entityResp->name ?? '' }}
                                </a>
                            @elsecanShow
                                {{ $application->entityResp->name ?? '' }}
                            @endcanShow
                        @endif
                    </td>
                    <th width="10%">
                        {{ trans('cruds.application.fields.entities') }}
                    </th>
                    <td>
                        @foreach($application->entities as $entity)
                            @canShow($entity)<a href="{{ route('admin.entities.show', $entity->id) }}">{{ $entity->name }}</a>@elsecanShow{{ $entity->name }}@endcanShow
                            @if(!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.application.fields.functional_referent') }}
                    </th>
                    <td width="15%">
                        {{ $application->functional_referent }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.application.fields.editor') }}
                    </th>
                    <td width="15%">
                        {{ $application->editor }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.application.fields.users') }}
                    </th>
                    <td width="15%">
                        {{ $application->users }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.application.fields.administrators') }}
                    </th>
                    <td colspan=3>
                        @foreach($application->administrators as $administrator)
                            <a href="{{ route('admin.admin-users.show', $administrator->id) }}">{{ $administrator->firstname }} {{ $administrator->lastname }} [{{ $administrator->user_id }}]</a>@if(!$loop->last),@endif
                        @endforeach
                    </td>
                    <td colspan=2>
                    </td>
                </tbody>
            </table>
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans("cruds.menu.application.title_short") }}
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report" id="application_table">
                <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.application.fields.technology') }}
                    </th>
                    <td colspan="4">
                        {{ $application->technology }}
                    </td>
                    <th>
                        {{ trans('cruds.application.fields.external') }}
                    </th>
                    <td colspan="2">
                        {{ $application->external }}
                    </td>
                </tr>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.application.fields.hosting') }}
                    </th>
                    <td width="20%">
                        {{ $application->hosting }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.application.fields.urls') }}
                    </th>
                    <td colspan="5">
                        @include('partials.url-list', ['value' => $application->urls])
                    </td>
                </tr>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.application.fields.status') }}
                    </th>
                    <td width="15%">
                        {{ $application->status }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.application.fields.install_date') }}
                    </th>
                    <td width="15%">
                        {{ optional($application->install_date)->format('Y-m-d') }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.application.fields.prod_date') }}
                    </th>
                    <td width="15%">
                        {{ optional($application->prod_date)->format('Y-m-d') }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.application.fields.update_date') }}
                    </th>
                    <td width="15%">
                        {{ optional($application->update_date)->format('Y-m-d') }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.application.fields.events') }}
                    </th>
                    <td>
                        <button class="btn btn-info events_list_button">
                            {{ trans('cruds.application.fields.events_list_button') }}
                        </button>
                    </td>
                    <th>
                        {{ trans('cruds.application.fields.documentation') }}
                    </th>
                    <td colspan="5">
                        @include('partials.url-list', ['value' => $application->documentation])
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.application.fields.databases') }}
                    </th>
                    <td colspan="3">
                        @foreach($application->databases as $database)
                            @canShow($database)<a href="{{ route('admin.databases.show', $database->id) }}">{{ $database->name }}</a>@elsecanShow{{ $database->name }}@endcanShow
                            @if(!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                    <th>
                        {{ trans('cruds.application.fields.services') }}
                    </th>
                    <td colspan="3">
                        @foreach($application->services as $service)
                            @canShow($service)<a href="{{ route('admin.application-services.show', $service->id) }}">{{ $service->name }}</a>@elsecanShow{{ $service->name }}@endcanShow
                            @if(!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                </tbody>
            </table>
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-header">
            Sécurité
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report">
                <tbody>
                <tr>
                    <th width="60%">
                        {{ trans('cruds.application.fields.security_need') }}
                    </th>
                    <th width="20%">
                        {{ trans('cruds.application.fields.RTO') }}
                    </th>
                    <th width="20%">
                        {{ trans('cruds.application.fields.RPO') }}
                    </th>
                </tr>
                <tr>
                    <td>
                        <table class="table table-striped table-borderless" cellspacing="5" cellpadding="5" border="0">
                            <tbody>
                            <td align="right" valign="middle">
                                {{ trans("global.confidentiality") }}
                            </td>
                            <td align="left">
                                @if ($application->security_need_c==0)
                                    {{ trans('global.none') }}
                                @endif
                                @if ($application->security_need_c==1)
                                    <span class="veryLowRisk">{{ trans('global.low') }}</span>
                                @endif
                                @if ($application->security_need_c==2)
                                    <span class="lowRisk">{{ trans('global.medium') }}</span>
                                @endif
                                @if ($application->security_need_c==3)
                                    <span class="mediumRisk">{{ trans('global.strong') }}</span>
                                @endif
                                @if ($application->security_need_c==4)
                                    <span class="highRisk">{{ trans('global.very_strong') }}</span>
                                @endif
                            </td>
                            <td align="right" valign="middle">
                                {{ trans("global.integrity") }}
                            </td>
                            <td align="left">
                                @if ($application->security_need_i==0)
                                    {{ trans('global.none') }}
                                @endif
                                @if ($application->security_need_i==1)
                                    <span class="veryLowRisk">{{ trans('global.low') }}</span>
                                @endif
                                @if ($application->security_need_i==2)
                                    <span class="lowRisk">{{ trans('global.medium') }}</span>
                                @endif
                                @if ($application->security_need_i==3)
                                    <span class="mediumRisk">{{ trans('global.strong') }}</span>
                                @endif
                                @if ($application->security_need_i==4)
                                    <span class="highRisk">{{ trans('global.very_strong') }}</span>
                                @endif
                            </td>
                            <td align="right" valign="middle">
                                {{ trans('global.availability') }}
                            </td>
                            <td align="left">
                                @if ($application->security_need_a==0)
                                    {{ trans('global.none') }}
                                @endif
                                @if ($application->security_need_a==1)
                                    <span class="veryLowRisk">{{ trans('global.low') }}</span>
                                @endif
                                @if ($application->security_need_a==2)
                                    <span class="lowRisk">{{ trans('global.medium') }}</span>
                                @endif
                                @if ($application->security_need_a==3)
                                    <span class="mediumRisk">{{ trans('global.strong') }}</span>
                                @endif
                                @if ($application->security_need_a==4)
                                    <span class="highRisk">{{ trans('global.very_strong') }}</span>
                                @endif
                            </td>
                            <td align="right" valign="middle">
                                {{ trans('global.tracability') }}
                            </td>
                            <td align="left">
                                @if ($application->security_need_t==0)
                                    {{ trans('global.none') }}
                                @endif
                                @if ($application->security_need_t==1)
                                    <span class="veryLowRisk">{{ trans('global.low') }}</span>
                                @endif
                                @if ($application->security_need_t==2)
                                    <span class="lowRisk">{{ trans('global.medium') }}</span>
                                @endif
                                @if ($application->security_need_t==3)
                                    <span class="mediumRisk">{{ trans('global.strong') }}</span>
                                @endif
                                @if ($application->security_need_t==4)
                                    <span class="highRisk">{{ trans('global.very_strong') }}</span>
                                @endif
                            </td>
                            @if (config('mercator.parameters.security_need_auth'))
                                <td align="right" valign="middle">
                                    {{ trans('global.authenticity') }}
                                </td>
                                <td align="left">
                                    @if ($application->security_need_auth==0)
                                        {{ trans('global.none') }}
                                    @endif
                                    @if ($application->security_need_auth==1)
                                        <span class="veryLowRisk">{{ trans('global.low') }}</span>
                                    @endif
                                    @if ($application->security_need_auth==2)
                                        <span class="lowRisk">{{ trans('global.medium') }}</span>
                                    @endif
                                    @if ($application->security_need_auth==3)
                                        <span class="mediumRisk">{{ trans('global.strong') }}</span>
                                    @endif
                                    @if ($application->security_need_auth==4)
                                        <span class="highRisk">{{ trans('global.very_strong') }}</span>
                                    @endif
                                </td>
                            @endif
                            </tbody>
                        </table>
                    </td>
                    <td>
                        @if (intdiv($application->rto,60 * 24) > 0)
                            {{ intdiv($application->rto,60 * 24) }}
                            @if (intdiv($application->rto,60 * 24) > 1)
                                {{ trans('global.days') }}
                            @else
                                {{ trans('global.day') }}
                            @endif
                        @endif
                        @if ((intdiv($application->rto,60) % 24) > 0)
                            {{ intdiv($application->rto,60) % 24 }}
                            @if ((intdiv($application->rto,60) % 24) > 1)
                                {{ trans('global.hours') }}
                            @else
                                {{ trans('global.hour') }}
                            @endif
                        @endif
                        @if (($application->rto % 60) > 0)
                            {{ $application->rto % 60 }}
                            @if (($application->rto % 60) > 1)
                                {{ trans('global.minutes') }}
                            @else
                                {{ trans('global.minute') }}
                            @endif
                        @endif
                    </td>
                    <td>
                        @if (intdiv($application->rpo,60 * 24) > 0)
                            {{ intdiv($application->rpo,60 * 24) }}
                            @if (intdiv($application->rpo,60 * 24) > 1)
                                {{ trans('global.days') }}
                            @else
                                {{ trans('global.day') }}
                            @endif
                        @endif
                        @if ((intdiv($application->rpo,60) % 24) > 0)
                            {{ intdiv($application->rpo,60) % 24 }}
                            @if ((intdiv($application->rpo,60) % 24) > 1)
                                {{ trans('global.hours') }}
                            @else
                                {{ trans('global.hour') }}
                            @endif
                        @endif
                        @if (($application->rpo % 60) > 0)
                            {{ $application->rpo % 60 }}
                            @if (($application->rpo % 60) > 1)
                                {{ trans('global.minutes') }}
                            @else
                                {{ trans('global.minute') }}
                            @endif
                        @endif
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-header">
            Common Platform Enumeration (CPE)
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report">
                <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.application.fields.vendor') }}
                    </th>
                    <td width="22%">
                        {{ $application->vendor }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.application.fields.product') }}
                    </th>
                    <td width="22%">
                        {{ $application->product }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.application.fields.version') }}
                    </th>
                    <td width="22%">
                        {{ $application->version }}
                    </td>
                    <td>
                        <form action="{{ route('admin.cve.search','cpe:2.3:a:'. $application->vendor.':'. $application->product . ':' . $application->version) }}"
                              method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                            <input type="submit" class="btn btn-info"
                                   value="{{ trans('global.search') }}" {{ (($application->vendor==null)||($application->product==null)) ? 'disabled' : '' }} />
                        </form>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans("cruds.menu.metier.title_short") }}
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report">
                <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.application.fields.processes') }}
                    </th>
                    <td>
                        @foreach($application->processes as $process)
                            @canShow($process)<a href="{{ route('admin.processes.show', $process->id) }}">{{ $process->name }}</a>@elsecanShow{{ $process->name }}@endcanShow
                            @if(!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.application.fields.activities') }}
                    </th>
                    <td>
                        @foreach($application->activities as $activity)
                            @canShow($activity)<a href="{{ route('admin.activities.show', $activity->id) }}">{{ $activity->name }}</a>@elsecanShow{{ $activity->name }}@endcanShow
                            @if(!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans('cruds.applicationFlow.title') }}
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report">
                <tbody>
                <tr>
                    <th width="20%">
                        {{ trans('cruds.applicationFlow.fields.name') }}
                    </th>
                    <th width="10%">
                        {{ trans('cruds.applicationFlow.fields.type') }}
                    </th>
                    <th width="10%">
                        {{ trans('cruds.applicationFlow.fields.attributes') }}
                    </th>
                    <th width="20%">
                        {{ trans('cruds.applicationFlow.fields.module_source') }}
                    </th>
                    <th width="20%">
                        {{ trans('cruds.applicationFlow.fields.module_dest') }}
                    </th>
                    <th width="20%">
                        {{ trans('cruds.applicationFlow.fields.information') }}
                    </th>
                </tr>
                @foreach($application->applicationSourceFluxes->union($application->applicationDestFluxes) as $flow)
                <tr>
                    <td>
                        @canShow($flow)<a href="{{ route('admin.application-flows.show', $flow->id) }}">{{ $flow->name }}</a>@elsecanShow{{ $flow->name }}@endcanShow
                    </td>
                    <td>
                       {{ $flow->type }}
                    </td>
                    <td>
                        @foreach(explode(" ",$flow->attributes) as $attribute)
                            <span class="badge badge-info">{{ $attribute }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if ($flow->applicationSource!=null)
                            @canShow($flow->applicationSource)
                                <a href="{{ route('admin.applications.show',$flow->applicationSource->id) }}">
                                    {{ $flow->applicationSource->name }}
                                </a>
                            @elsecanShow
                                {{ $flow->applicationSource->name }}
                            @endcanShow
                        @endif
                        @if($flow->serviceSource!=null)
                            @canShow($flow->serviceSource)
                                <a href="{{ route('admin.application-services.show', $flow->serviceSource->id) }}">
                                    {{ $flow->serviceSource->name }}
                                </a>
                            @elsecanShow
                                {{ $flow->serviceSource->name }}
                            @endcanShow
                        @endif
                        @if ($flow->moduleSource!=null)
                            @canShow($flow->moduleSource)
                                <a href="{{ route('admin.application-modules.show', $flow->moduleSource->id) }}">
                                    {{ $flow->moduleSource->name }}
                                </a>
                            @elsecanShow
                                {{ $flow->moduleSource->name }}
                            @endcanShow
                        @endif
                        @if ($flow->databaseSource!=null)
                            @canShow($flow->databaseSource)
                                <a href="{{ route('admin.databases.show',$flow->databaseSource->id) }}">
                                    {{ $flow->databaseSource->name }}
                                </a>
                            @elsecanShow
                                {{ $flow->databaseSource->name }}
                            @endcanShow
                        @endif
                    </td>
                    <td>
                        @if ($flow->applicationDest!=null)
                            @canShow($flow->applicationDest)
                                <a href="{{ route('admin.applications.show',$flow->applicationDest->id) }}">
                                    {{ $flow->applicationDest->name }}
                                </a>
                            @elsecanShow
                                {{ $flow->applicationDest->name }}
                            @endcanShow
                        @endif
                        @if ($flow->serviceDest!=null)
                            @canShow($flow->serviceDest)
                                <a href="{{ route('admin.application-services.show', $flow->serviceDest->id) }}">
                                    {{ $flow->serviceDest->name }}
                                </a>
                            @elsecanShow
                                {{ $flow->serviceDest->name }}
                            @endcanShow
                        @endif
                        @if ($flow->moduleDest!=null)
                            @canShow($flow->moduleDest)
                                <a href="{{ route('admin.application-modules.show', $flow->moduleDest->id) }}">
                                    {{ $flow->moduleDest->name }}
                                </a>
                            @elsecanShow
                                {{ $flow->moduleDest->name }}
                            @endcanShow
                        @endif
                        @if ($flow->databaseDest!=null)
                            @canShow($flow->databaseDest)
                                <a href="{{ route('admin.databases.show',$flow->databaseDest->id) }}">
                                    {{ $flow->databaseDest->name }}
                                </a>
                            @elsecanShow
                                {{ $flow->databaseDest->name }}
                            @endcanShow
                        @endif
                    </td>
                    <td>
                        @foreach($flow->informations as $info)
                            @canShow($info)<a href="{{ route('admin.information.show',$info->id) }}">{{$info->name}}</a>@elsecanShow{{$info->name}}@endcanShow
                            @if (!$loop->last) , @endif
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans("cruds.menu.logical_infrastructure.title_short") }}
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report">
                <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.application.fields.logical_servers') }}
                    </th>
                    <td>
                        @foreach($application->logicalServers as $logical_server)
                            @canShow($logical_server)<a href='{{ route("admin.logical-servers.show", $logical_server->id) }}'>{{ $logical_server->name }}</a>@elsecanShow{{ $logical_server->name }}@endcanShow
                            @if(!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.application.fields.containers') }}
                    </th>
                    <td>
                        @foreach($application->containers as $container)
                            @canShow($container)<a href='{{ route("admin.containers.show", $container->id) }}'>{{ $container->name }}</a>@elsecanShow{{ $container->name }}@endcanShow
                            @if(!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.application.fields.security_devices') }}
                    </th>
                    <td>
                        @foreach($application->securityDevices as $security_device)
                            @canShow($security_device)<a href='{{ route("admin.security-devices.show", $security_device->id) }}'>{{ $security_device->name }}</a>@elsecanShow{{ $security_device->name }}@endcanShow
                            @if(!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        @include('admin._footer', ['model' => $application])
    </div>

    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.applications.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>

{{-- Modal Événements --}}
<div class="modal fade" id="eventsModal" tabindex="-1" aria-labelledby="eventsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventsModalLabel">{{ trans('cruds.application.fields.events') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="eventsModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script @nonce>
        document.addEventListener("DOMContentLoaded", function () {
            const events = @json($application->events);

            function escapeHtml(value) {
                if (value == null) {
                    return '';
                }
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderEvents() {
                if (!events.length) {
                    return '<p class="text-muted">{{ trans('cruds.application.fields.events_placeholder') }}</p>';
                }
                let ret = '<ul class="list-unstyled">';
                events.forEach(function (event) {
                    ret += `
                        <li class="mb-3 border-bottom pb-2">
                            <div>${escapeHtml(event.message)}</div>
                            <small class="text-muted">
                                Date : ${moment(event.created_at).format('DD-MM-YYYY')}
                                | Utilisateur : ${escapeHtml(event.user.name)}
                            </small>
                        </li>`;
                });
                ret += '</ul>';
                return ret;
            }

            // navigator.clipboard is only available in secure contexts (https, localhost)
            function copyText(text) {
                if (navigator.clipboard && window.isSecureContext) {
                    return navigator.clipboard.writeText(text);
                }
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                const copied = document.execCommand('copy');
                document.body.removeChild(textarea);
                return copied ? Promise.resolve() : Promise.reject();
            }

            document.querySelectorAll('.copy-path-button').forEach(function (button) {
                button.addEventListener('click', function () {
                    const icon = button.querySelector('i');
                    copyText(button.dataset.path).then(function () {
                        icon.className = 'bi bi-clipboard-check';
                        setTimeout(function () { icon.className = 'bi bi-clipboard'; }, 1500);
                    });
                });
            });

            $('.events_list_button').on('click', function (e) {
                e.preventDefault();
                document.getElementById('eventsModalBody').innerHTML = renderEvents();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('eventsModal')).show();
            });
        });
    </script>
@endsection
