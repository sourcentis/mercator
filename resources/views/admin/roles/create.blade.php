@extends('layouts.admin')

@section('title')
    {{ trans('global.create') }} {{ trans('cruds.role.title_singular') }}
@endsection

@section('content')
@php($role = null)
@php($disabled = false)
<form method="POST" action="{{ route("admin.roles.store") }}" enctype="multipart/form-data">
    @csrf
    <div class="card">
        <div class="card-header">{{ trans('global.create') }} {{ trans('cruds.role.title_singular') }}</div>
        <div class="card-body">
            <div class="form-check">
                <div class="row">
                    <div class="col-md-4">
                        <label class="label-required" for="title">{{ trans('cruds.role.fields.title') }}</label>
                        <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text"
                               name="title" id="title" value="{{ old('title') }}" required autofocus/>
                        @if($errors->has('title'))
                            <div class="invalid-feedback">{{ $errors->first('title') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.role.fields.title_helper') }}</span>
                    </div>
                    <div class="col-md-4">
                        <label class="label-required" for="perimeter_id">{{ trans('cruds.role.fields.perimeter') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id" required>
                            @foreach ($perimeters as $perimeter)
                                <option value="{{ $perimeter->id }}"
                                        {{ (int) old('perimeter_id', \App\Models\Perimeter::DEFAULT_ID) === $perimeter->id ? 'selected' : '' }}>
                                    {{ $perimeter->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('perimeter_id'))
                            <div class="invalid-feedback">{{ $errors->first('perimeter_id') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.role.fields.perimeter_helper') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- RGPD --}}
        <div class="perm-section">
        @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['gdpr'], 'label' => trans('cruds.menu.gdpr.title_short')])
        <div class="card-body"><div class="row">
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['data_processing'], 'label' => trans('cruds.dataProcessing.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['security_control'],  'label' => trans('cruds.securityControl.title')])
        </div></div>
        </div>

        {{-- Ecosystème --}}
        <div class="perm-section">
        @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['ecosystem'], 'label' => trans('cruds.menu.ecosystem.title_short')])
        <div class="card-body"><div class="row">
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['entity'],   'label' => trans('cruds.entity.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['relation'],  'label' => trans('cruds.relation.title')])
        </div></div>
        </div>

        {{-- Métier --}}
        <div class="perm-section">
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
        </div>

        {{-- Application --}}
        <div class="perm-section">
        @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['application'], 'label' => trans('cruds.menu.application.title_short')])
        <div class="card-body"><div class="row">
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['application_block'],   'label' => trans('cruds.applicationBlock.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['application'],         'label' => trans('cruds.application.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['application_service'], 'label' => trans('cruds.applicationService.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['application_module'],  'label' => trans('cruds.applicationModule.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['database'],            'label' => trans('cruds.database.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['application_flow'],                'label' => trans('cruds.applicationFlow.title')])
        </div></div>
        </div>

        {{-- Administration --}}
        <div class="perm-section">
        @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['administration'], 'label' => trans('cruds.menu.administration.title_short')])
        <div class="card-body"><div class="row">
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['zone_admin'],  'label' => trans('cruds.zoneAdmin.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['annuaire'],    'label' => trans('cruds.annuaire.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['forest_ad'],   'label' => trans('cruds.forestAd.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['domain'],  'label' => trans('cruds.domain.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['admin_user'],  'label' => trans('cruds.adminUser.title')])
        </div></div>
        </div>

        {{-- Infrastructure logique --}}
        <div class="perm-section">
        @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['infrastructure'], 'label' => trans('cruds.menu.logical_infrastructure.title_short')])
        <div class="card-body">
            <div class="row">
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['network'],                   'label' => trans('cruds.network.title')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['subnetwork'],                'label' => trans('cruds.subnetwork.title')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['gateway'],                   'label' => trans('cruds.gateway.title')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['external_connected_entity'], 'label' => trans('cruds.externalConnectedEntity.title')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['network_switch'],            'label' => trans('cruds.networkSwitch.title')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['router'],                    'label' => trans('cruds.router.title')])
            </div>
            <div class="row">
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['security_device'],  'label' => trans('cruds.securityDevice.title')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['dhcp_server'],      'label' => trans('cruds.dhcpServer.title'), 'deprecated' => true])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['dnsserver'],        'label' => trans('cruds.dnsserver.title'), 'deprecated' => true])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['cluster'],          'label' => trans('cruds.cluster.title')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['logical_server'],   'label' => trans('cruds.logicalServer.title')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['container'],        'label' => trans('cruds.container.title')])
            </div>
            <div class="row">
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['logical_flow'],  'label' => trans('cruds.logicalFlow.title')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['vlan'],          'label' => trans('cruds.vlan.title_short')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['certificate'],   'label' => trans('cruds.certificate.title')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['backup'],        'label' => trans('cruds.backup.title')])
            </div>
        </div>
        </div>

        {{-- Infrastructure physique (sans physical_link) --}}
        <div class="perm-section">
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
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['wan'],                      'label' => trans('cruds.wan.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['man'],                      'label' => trans('cruds.man.title')])
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['lan'],                      'label' => trans('cruds.lan.title')])
        </div></div>
        </div>

        {{-- Outils  --}}
        <div class="card-header">
            <label><b>{{ trans('panel.menu.tools') }}</b></label>
        </div>
        <div class="card-body"><div class="row">
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['graph'], 'label' => (trans('cruds.bpmn.title') . ' / ' . trans('cruds.graph.title'))])
            <div class="col-md-2">
                @php($permission = $permissions_sorted['explore'])
                @include('admin.roles.partials._checkbox', ['action' => [$permission['actions'][0][0], trans('panel.menu.explore')]])
            </div>
            @isset($permissions_sorted['cairn'])
            <div class="col-md-2">
                @php($permission = $permissions_sorted['cairn'])
                @include('admin.roles.partials._checkbox', ['action' => [$permission['actions'][0][0], trans('panel.menu.cairn')]])
            </div>
            @endisset
            @include('admin.roles.partials._group', ['permission' => $permissions_sorted['query'],   'label' => trans('cruds.tools.query.title'), 'indices' => [0, 5, 1, 3, 2, 4]])
            {{-- @include('admin.roles.partials._group', ['permission' => $permissions_sorted['patching'], 'label' => trans('cruds.tools.patching'), 'indices' => [0, 1]]) --}}
            <div class="col-md-2">
                @php($permission = $permissions_sorted['reports'])
                @include('admin.roles.partials._checkbox', ['action' => [$permission['actions'][0][0], trans('panel.menu.reports')]])
            </div>
        </div></div>

        {{-- Configuration --}}
        <div class="perm-section">
        @include('admin.roles.partials._section_header', ['permission' => $permissions_sorted['configure'], 'label' => trans('cruds.menu.configuration.title')])
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-check">
                        <label>Password</label>
                        <div class="form-switch form-switch-lg">
                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                data-check="profile_password_edit" data-action="edit" id="profile_password_edit"
                                value="256" {{ in_array('profile_password_edit', old('permissions', [])) ? 'checked' : '' }}
                                @if(isset($grantable) && ! in_array(256, $grantable, true)) disabled title="{{ trans('cruds.role.permission_not_held') }}" @endif>
                            <label class="form-check-label" for="for_profile_password_edit }}">edit</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['user'],      'label' => trans('cruds.user.title')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['role'],      'label' => trans('cruds.role.title')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['cartographer'],      'label' => trans('cruds.cartographer.title')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['document'],  'label' => trans('cruds.configuration.documents.title')])
                @include('admin.roles.partials._group', ['permission' => $permissions_sorted['audit_log'], 'label' => trans('cruds.auditLog.title'),  'indices' => [0, 1]])
            </div>
            <div class="row">
                @if($errors->has('permissions'))
                    <div class="invalid-feedback">{{ $errors->first('permissions') }}</div>
                @endif
                <span class="help-block">{{ trans('cruds.role.fields.permissions_helper') }}</span>
            </div>
        </div>
        </div>
    </div>

    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.roles.index') }}">{{ trans('global.back_to_list') }}</a>
        <button id="btn-save" class="btn btn-success" type="submit">{{ trans('global.save') }}</button>
    </div>
</form>
<br><br><br>
@endsection
