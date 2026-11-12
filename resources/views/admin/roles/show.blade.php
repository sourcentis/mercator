@extends('layouts.admin')

@section('title')
    {{ $role->title }}
@endsection

@section('content')
@php($disabled = true)

<div class="form-group">
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.roles.index') }}">{{ trans('global.back_to_list') }}</a>
        @canEdit($role)
            <a class="btn btn-info" href="{{ route('admin.roles.edit', $role->id) }}">{{ trans('global.edit') }}</a>
        @endcanEdit
        @can('role_create')
            <a class="btn btn-warning" href="{{ route('admin.roles.clone', $role->id) }}">
                {{ trans('global.clone') }}
            </a>
        @endcan
        @can('role_delete')
            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST"
                  data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan
    </div>
</div>

<div class="card">
    <div class="card-header">{{ trans('global.show') }} {{ trans('cruds.role.title_singular') }}</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-2">
                <label class="form-check-label"><b>{{ trans('cruds.role.fields.title') }}</b></label>
                <label>{{ $role->title }}</label>
            </div>
            <div class="col-md-2">
                <label class="form-check-label"><b>{{ trans('cruds.role.fields.perimeter') }}</b></label>
                <label>{{ $role->perimeter->name ?? '' }}</label>
            </div>
        </div>
    </div>

    {{-- RGPD --}}
    @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['gdpr'], 'label' => trans('cruds.menu.gdpr.title_short')])
    <div class="card-body"><div class="row">
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['data_processing'], 'label' => trans('cruds.dataProcessing.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['security_control'],  'label' => trans('cruds.securityControl.title')])
    </div></div>

    {{-- Ecosystème --}}
    @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['ecosystem'], 'label' => trans('cruds.menu.ecosystem.title_short')])
    <div class="card-body"><div class="row">
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['entity'],   'label' => trans('cruds.entity.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['relation'],  'label' => trans('cruds.relation.title')])
    </div></div>

    {{-- Métier --}}
    @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['metier'], 'label' => trans('cruds.menu.metier.title_short')])
    <div class="card-body">
        <div class="row">
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['macro_processus'], 'label' => trans('cruds.macroProcessus.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['process'],          'label' => trans('cruds.process.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['activity'],         'label' => trans('cruds.activity.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['operation'],        'label' => trans('cruds.operation.title')])
        </div>
        <div class="row">
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['task'],        'label' => trans('cruds.task.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['actor'],       'label' => trans('cruds.actor.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['information'], 'label' => trans('cruds.information.title')])
        </div>
    </div>

    {{-- Application --}}
    @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['application'], 'label' => trans('cruds.menu.application.title_short')])
    <div class="card-body"><div class="row">
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['application_block'],   'label' => trans('cruds.applicationBlock.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['application'],         'label' => trans('cruds.application.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['application_service'], 'label' => trans('cruds.applicationService.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['application_module'],  'label' => trans('cruds.applicationModule.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['database'],            'label' => trans('cruds.database.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['application_flow'],                'label' => trans('cruds.applicationFlow.title')])
    </div></div>

    {{-- Administration --}}
    @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['administration'], 'label' => trans('cruds.menu.administration.title_short')])
    <div class="card-body"><div class="row">
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['zone_admin'],  'label' => trans('cruds.zoneAdmin.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['annuaire'],    'label' => trans('cruds.annuaire.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['forest_ad'],   'label' => trans('cruds.forestAd.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['domain'],  'label' => trans('cruds.domain.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['admin_user'],  'label' => trans('cruds.adminUser.title')])
    </div></div>

    {{-- Infrastructure logique --}}
    @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['infrastructure'], 'label' => trans('cruds.menu.logical_infrastructure.title_short')])
    <div class="card-body">
        <div class="row">
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['network'],                   'label' => trans('cruds.network.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['subnetwork'],                'label' => trans('cruds.subnetwork.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['gateway'],                   'label' => trans('cruds.gateway.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['external_connected_entity'], 'label' => trans('cruds.externalConnectedEntity.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['network_switch'],            'label' => trans('cruds.networkSwitch.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['router'],                    'label' => trans('cruds.router.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['security_device'],           'label' => trans('cruds.securityDevice.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['dhcp_server'],               'label' => trans('cruds.dhcpServer.title'), 'deprecated' => true])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['dnsserver'],                 'label' => trans('cruds.dnsserver.title'), 'deprecated' => true])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['cluster'],                   'label' => trans('cruds.cluster.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['logical_server'],            'label' => trans('cruds.logicalServer.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['container'],                 'label' => trans('cruds.container.title')])
        </div>
        <div class="row">
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['logical_flow'],  'label' => trans('cruds.logicalFlow.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['vlan'],          'label' => trans('cruds.vlan.title_short')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['certificate'],   'label' => trans('cruds.certificate.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['backup'],        'label' => trans('cruds.backup.title')])
        </div>
    </div>

    {{-- Infrastructure physique --}}
    @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['physicalinfrastructure'], 'label' => trans('cruds.menu.physical_infrastructure.title_short')])
    <div class="card-body"><div class="row">
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['site'],                     'label' => trans('cruds.site.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['building'],                 'label' => trans('cruds.building.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['bay'],                      'label' => trans('cruds.bay.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['zone'],                     'label' => trans('cruds.zone.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['physical_server'],          'label' => trans('cruds.physicalServer.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['workstation'],              'label' => trans('cruds.workstation.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['storage_device'],           'label' => trans('cruds.storageDevice.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['peripheral'],               'label' => trans('cruds.peripheral.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['phone'],                    'label' => trans('cruds.phone.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['physical_switch'],          'label' => trans('cruds.physicalSwitch.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['physical_router'],          'label' => trans('cruds.physicalRouter.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['wifi_terminal'],            'label' => trans('cruds.wifiTerminal.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['physical_security_device'], 'label' => trans('cruds.physicalSecurityDevice.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['physical_link'],            'label' => trans('cruds.physicalLink.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['wan'],                      'label' => trans('cruds.wan.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['man'],                      'label' => trans('cruds.man.title')])
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['lan'],                      'label' => trans('cruds.lan.title')])
    </div></div>

    {{-- Outils --}}
    @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['tools'], 'label' => trans('panel.menu.tools')])
    <div class="card-body"><div class="row">
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['graph'], 'label' => (trans('cruds.bpmn.title') . ' / ' . trans('cruds.graph.title'))])
        <div class="col-md-2">
            @php($permission = $permissions_sorted['explore'])
            @include('admin.roles.partials._checkbox', ['action' => [$permission['actions'][0][0], trans('panel.menu.explore')]])
        </div>
        @include('admin.roles.partials._group', ['permission' => $permissions_sorted['query'],    'label' => trans('cruds.tools.query.title')])
        {{-- @include('admin.roles.partials._group', ['permission' => $permissions_sorted['patching'], 'label' => trans('cruds.tools.patching'), 'indices' => [0, 1]]) --}}
        <div class="col-md-2">
            @php($permission = $permissions_sorted['reports'])
            @include('admin.roles.partials._checkbox', ['action' => [$permission['actions'][0][0], trans('panel.menu.reports')]])
        </div>
    </div></div>

    {{-- Configuration --}}
    @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['configure'], 'label' => trans('cruds.menu.configuration.title')])
    <div class="card-body">
        <div class="row">
            <div class="col-md-2">
                <div class="form-check">
                    <label>Password</label>
                    <div class="form-switch">
                        <input class="form-check-input" type="checkbox" name="permissions[]"
                            data-check="profile_password_edit" id="profile_password_edit"
                            value="256" disabled @checked(in_array('profile_password_edit', old('permissions', [])) || ($role && $role->permissions->contains(256)))>
                        <label class="form-check-label" for="for_profile_password_edit }}">edit</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['user'],      'label' => trans('cruds.user.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['role'],      'label' => trans('cruds.role.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['cartographer'], 'label' => trans('cruds.cartographer.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['document'],  'label' => trans('cruds.configuration.documents.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['audit_log'], 'label' => trans('cruds.auditLog.title'),  'indices' => [0, 1]])
        </div>
    </div>
</div>

<div class="form-group mt-3">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.roles.index') }}">{{ trans('global.back_to_list') }}</a>
</div>
@endsection
