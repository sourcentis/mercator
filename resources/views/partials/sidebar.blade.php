<nav id="sidebar" class="sidebar">
    @if (\App\Support\PerimeterSettings::isEnabled())
        @php
            $sidebarPerimeterIds = auth()->user()->perimeterIds();
        @endphp
        @if (count($sidebarPerimeterIds) >= 2)
            <div class="perimeter-box px-0 pt-0">
                <form id="active-perimeter-form" method="POST" action="{{ route('admin.perimeter.active') }}">
                    @csrf
                    <select name="perimeter" id="active-perimeter" class="form-control select2">
                        <option value="0" {{ (int) session('active_perimeter', 0) === 0 ? 'selected' : '' }}>&nbsp;</option>
                        @foreach (\App\Models\Perimeter::whereIn('id', $sidebarPerimeterIds)->orderBy('name')->get() as $sidebarPerimeter)
                            <option value="{{ $sidebarPerimeter->id }}"
                                    {{ (int) session('active_perimeter', 0) === $sidebarPerimeter->id ? 'selected' : '' }}>
                                {{ $sidebarPerimeter->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        {{--
        @elseif (count($sidebarPerimeterIds) === 1)
            <div class="perimeter-box px-2 pt-2">
                <span class="badge bg-secondary">
                    {{ \App\Models\Perimeter::find($sidebarPerimeterIds[0])->name ?? '' }}
                </span>
            </div>
        --}}
        @endif
    @endif
    <div class="search-box">
        <form id="search-form" action="/admin/global-search" method="GET">
            @if (\App\Support\TrashMode::active())
                {{-- Recherche globale désactivée en mode corbeille --}}
                <input type="text" name="search" class="form-control" disabled
                       placeholder="{{ trans('cruds.trash.search_disabled') }}">
            @else
                <input type="text" name="search" class="form-control" placeholder="Rechercher...">
            @endif
        </form>
    </div>
    <div class="flex-grow-1">
        <a href="{{ route('admin.home') }}"><i class="bi bi-speedometer2"></i><span
                    class="menu-text">{{ trans('global.dashboard') }}</span></a>
        @canAccessAny(\App\Models\DataProcessing::class, \App\Models\SecurityControl::class)
            <a class="dropdown-toggle" data-bs-toggle="collapse" href="#submenu1" role="button" aria-expanded="false">
                <i class="bi bi-folder-fill"></i><span class="menu-text">{{ trans('cruds.menu.gdpr.title') }}</span>
            </a>
            <div id="submenu1" class="collapse {{
            (
                request()->is('admin/data-processing*')||
                request()->is('admin/security-control*')
            ) ? 'show' : '' }}">
                @canAccess(\App\Models\DataProcessing::class)
                    <a href="{{ route('admin.data-processings.index') }}"
                       class="ps-4 {{ request()->is('admin/data-processings*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.dataProcessing.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\SecurityControl::class)
                    <a href="{{ route('admin.security-controls.index') }}"
                       class="ps-4 {{ request()->is('admin/security-control*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.securityControl.title') }}</span>
                    </a>
                @endcanAccess
            </div>
        @endcanAccessAny
        @canAccessAny(\App\Models\Entity::class, \App\Models\Relation::class)
            <a class="dropdown-toggle" data-bs-toggle="collapse" href="#submenu2" role="button" aria-expanded="false">
                <i class="bi bi-folder-fill"></i><span
                        class="menu-text">{{ trans('cruds.menu.ecosystem.title') }}</span>
            </a>
            <div id="submenu2" class="collapse {{
            (
                request()->is('admin/entities*')||
                request()->is('admin/relations*')
            ) ? 'show' : '' }}">
                @canAccess(\App\Models\Entity::class)
                    <a href="{{ route('admin.entities.index') }}"
                       class="ps-4 {{ request()->is('admin/entities*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.entity.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Relation::class)
                    <a href="{{ route('admin.relations.index') }}"
                       class="ps-4 {{ request()->is('admin/relations*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.relation.title') }}</span>
                    </a>
                @endcanAccess
            </div>
        @endcanAccessAny
        @canAccessAny(\App\Models\MacroProcessus::class, \App\Models\Process::class, \App\Models\Activity::class, \App\Models\Operation::class, \App\Models\Task::class, \App\Models\Actor::class, \App\Models\Information::class)
            <a class="dropdown-toggle" data-bs-toggle="collapse" href="#submenu3" role="button" aria-expanded="false">
                <i class="bi bi-folder-fill"></i><span class="menu-text">{{ trans('cruds.menu.metier.title') }}</span>
            </a>
            <div id="submenu3" class="collapse {{
            (
                request()->is('admin/macro-processuses*')||
                request()->is('admin/processes*')||
                request()->is('admin/activities*')||
                request()->is('admin/operations*')||
                request()->is('admin/tasks*')||
                request()->is('admin/actors*')||
                request()->is('admin/information*')
            ) ? 'show' : '' }}">
                @canAccess(\App\Models\MacroProcessus::class)
                    <a href="{{ route('admin.macro-processuses.index') }}"
                       class="ps-4 {{ request()->is('admin/macro-processuses*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.macroProcessus.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Process::class)
                    <a href="{{ route('admin.processes.index') }}"
                       class="ps-4 {{ request()->is('admin/processes*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.process.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Activity::class)
                    <a href="{{ route('admin.activities.index') }}"
                       class="ps-4 {{ request()->is('admin/activities*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.activity.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Operation::class)
                    <a href="{{ route('admin.operations.index') }}"
                       class="ps-4 {{ request()->is('admin/operations*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.operation.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Task::class)
                    <a href="{{ route('admin.tasks.index') }}"
                       class="ps-4 {{ request()->is('admin/tasks*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span class="menu-text">{{ trans('cruds.task.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Actor::class)
                    <a href="{{ route('admin.actors.index') }}"
                       class="ps-4 {{ request()->is('admin/actors*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.actor.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Information::class)
                    <a href="{{ route('admin.information.index') }}"
                       class="ps-4 {{ request()->is('admin/information*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.information.title') }}</span>
                    </a>
                @endcanAccess
            </div>
        @endcanAccessAny
        @canAccessAny(\App\Models\ApplicationBlock::class, \App\Models\Application::class, \App\Models\ApplicationService::class, \App\Models\ApplicationModule::class, \App\Models\Database::class, \App\Models\ApplicationFlow::class)
            <a class="dropdown-toggle" data-bs-toggle="collapse" href="#submenu4" role="button" aria-expanded="false">
                <i class="bi bi-folder-fill"></i><span
                        class="menu-text">{{ trans('cruds.menu.application.title') }}</span>
            </a>
            <div id="submenu4" class="collapse {{
            (
            request()->is('admin/application-blocks*')||
            request()->is('admin/applications*')||
            request()->is('admin/application-services*')||
            request()->is('admin/application-modules*')||
            request()->is('admin/databases*')||
            request()->is('admin/fluxes*')
            ) ? 'show' : '' }}">
                @canAccess(\App\Models\ApplicationBlock::class)
                    <a href="{{ route('admin.application-blocks.index') }}"
                       class="ps-4 {{ request()->is('admin/application-blocks*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.applicationBlock.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Application::class)
                    <a href="{{ route('admin.applications.index') }}"
                       class="ps-4 {{ request()->is('admin/applications*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.application.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\ApplicationService::class)
                    <a href="{{ route('admin.application-services.index') }}"
                       class="ps-4 {{ request()->is('admin/application-services*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.applicationService.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\ApplicationModule::class)
                    <a href="{{ route('admin.application-modules.index') }}"
                       class="ps-4 {{ request()->is('admin/application-modules*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.applicationModule.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Database::class)
                    <a href="{{ route('admin.databases.index') }}"
                       class="ps-4 {{ request()->is('admin/databases*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.database.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\ApplicationFlow::class)
                    <a href="{{ route('admin.application-flows.index') }}"
                       class="ps-4 {{ request()->is('admin/fluxes*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span class="menu-text">{{ trans('cruds.applicationFlow.title') }}</span>
                    </a>
                @endcanAccess
            </div>
        @endcanAccessAny
        @canAccessAny(\App\Models\ZoneAdmin::class, \App\Models\Annuaire::class, \App\Models\ForestAd::class, \App\Models\Domain::class, \App\Models\AdminUser::class)
            <a class="dropdown-toggle" data-bs-toggle="collapse" href="#submenu5" role="button" aria-expanded="false">
                <i class="bi bi-folder-fill"></i><span
                        class="menu-text">{{ trans('cruds.administration.title') }}</span>
            </a>
            <div id="submenu5" class="collapse {{ (
            request()->is('admin/zone-admins*')||
            request()->is('admin/annuaires*')||
            request()->is('admin/forest-ads*')||
            request()->is('admin/domains*')||
            request()->is('admin/admin-users*')
            ) ? 'show' : '' }}">
                @canAccess(\App\Models\ZoneAdmin::class)
                    <a href="{{ route('admin.zone-admins.index') }}"
                       class="ps-4 {{ request()->is('admin/zone-admins*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.zoneAdmin.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Annuaire::class)
                    <a href="{{ route('admin.annuaires.index') }}"
                       class="ps-4 {{ request()->is('admin/annuaires*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.annuaire.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\ForestAd::class)
                    <a href="{{ route('admin.forest-ads.index') }}"
                       class="ps-4 {{ request()->is('admin/forest-ads*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.forestAd.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Domain::class)
                    <a href="{{ route('admin.domains.index') }}"
                       class="ps-4 {{ request()->is('admin/domains*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.domain.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\AdminUser::class)
                    <a href="{{ route('admin.admin-users.index') }}"
                       class="ps-4 {{ request()->is('admin/admin-users*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.adminUser.title') }}</span>
                    </a>
                @endcanAccess
            </div>
        @endcanAccessAny
        @canAccessAny(\App\Models\Network::class, \App\Models\Subnetwork::class, \App\Models\Gateway::class, \App\Models\ExternalConnectedEntity::class, \App\Models\Router::class, \App\Models\NetworkSwitch::class, \App\Models\SecurityDevice::class, \App\Models\DhcpServer::class, \App\Models\Dnsserver::class, \App\Models\Cluster::class, \App\Models\LogicalServer::class, \App\Models\Backup::class, \App\Models\Container::class, \App\Models\LogicalFlow::class, \App\Models\Vlan::class, \App\Models\Certificate::class)
            <a class="dropdown-toggle" data-bs-toggle="collapse" href="#submenu6" role="button" aria-expanded="false">
                <i class="bi bi-folder-fill"></i><span
                        class="menu-text">{{ trans('cruds.menu.logical_infrastructure.title') }}</span>
            </a>
            <div id="submenu6" class="collapse {{
            (
            request()->is('admin/networks*')||
            request()->is('admin/subnetworks*')||
            request()->is('admin/gateways*')||
            request()->is('admin/external-connected*')||
            request()->is('admin/routers*')||
            request()->is('admin/network-switches*')||
            request()->is('admin/security-devices*')||
            request()->is('admin/dnsservers*')||
            request()->is('admin/dhcp-servers*')||
            request()->is('admin/clusters*')||
            request()->is('admin/logical-servers*')||
            request()->is('admin/backups*')||
            request()->is('admin/containers*')||
            request()->is('admin/logical-flows*')||
            request()->is('admin/vlans*')||
            request()->is('admin/certificates*')
            ) ? 'show' : '' }}">
                @canAccess(\App\Models\Network::class)
                    <a href="{{ route('admin.networks.index') }}"
                       class="ps-4 {{ request()->is('admin/networks*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.network.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Subnetwork::class)
                    <a href="{{ route('admin.subnetworks.index') }}"
                       class="ps-4 {{ request()->is('admin/subnetworks*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.subnetwork.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Gateway::class)
                    <a href="{{ route('admin.gateways.index') }}"
                       class="ps-4 {{ request()->is('admin/gateways*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.gateway.title_short') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\ExternalConnectedEntity::class)
                    <a href="{{ route('admin.external-connected-entities.index') }}"
                       class="ps-4 {{ request()->is('admin/external-connected*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.externalConnectedEntity.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Router::class)
                    <a href="{{ route('admin.routers.index') }}"
                       class="ps-4 {{ request()->is('admin/routers*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.router.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\NetworkSwitch::class)
                    <a href="{{ route('admin.network-switches.index') }}"
                       class="ps-4 {{ request()->is('admin/network-switches*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.networkSwitch.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\SecurityDevice::class)
                    <a href="{{ route('admin.security-devices.index') }}"
                       class="ps-4 {{ request()->is('admin/security-devices*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.securityDevice.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\DhcpServer::class)
                    <a href="{{ route('admin.dhcp-servers.index') }}"
                       class="ps-4 {{ request()->is('admin/dhcp-servers*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.dhcpServer.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Dnsserver::class)
                    <a href="{{ route('admin.dnsservers.index') }}"
                       class="ps-4 {{ request()->is('admin/dnsservers*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.dnsserver.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Cluster::class)
                    <a href="{{ route('admin.clusters.index') }}"
                       class="ps-4 {{ request()->is('admin/clusters*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.cluster.title_short') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\LogicalServer::class)
                    <a href="{{ route('admin.logical-servers.index') }}"
                       class="ps-4 {{ request()->is('admin/logical-servers*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.logicalServer.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Backup::class)
                    <a href="{{ route('admin.backups.index') }}"
                       class="ps-4 {{ request()->is('admin/backups*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.backup.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Container::class)
                    <a href="{{ route('admin.containers.index') }}"
                       class="ps-4 {{ request()->is('admin/containers*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.container.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\LogicalFlow::class)
                    <a href="{{ route('admin.logical-flows.index') }}"
                       class="ps-4 {{ request()->is('admin/logical-flows*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.logicalFlow.title_short') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Vlan::class)
                    <a href="{{ route('admin.vlans.index') }}"
                       class="ps-4 {{ request()->is('admin/vlans*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.vlan.title_short') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Certificate::class)
                    <a href="{{ route('admin.certificates.index') }}"
                       class="ps-4 {{ request()->is('admin/certificates*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.certificate.title') }}</span>
                    </a>
                @endcanAccess
            </div>
        @endcanAccessAny
        @canAccessAny(\App\Models\Site::class, \App\Models\Building::class, \App\Models\Bay::class, \App\Models\Zone::class, \App\Models\PhysicalServer::class, \App\Models\Workstation::class, \App\Models\StorageDevice::class, \App\Models\Peripheral::class, \App\Models\Phone::class, \App\Models\PhysicalSwitch::class, \App\Models\PhysicalRouter::class, \App\Models\WifiTerminal::class, \App\Models\PhysicalSecurityDevice::class, \App\Models\PhysicalLink::class, \App\Models\Wan::class, \App\Models\Man::class, \App\Models\Lan::class)
            <a class="dropdown-toggle" data-bs-toggle="collapse" href="#submenu7" role="button" aria-expanded="false">
                <i class="bi bi-folder-fill"></i><span
                        class="menu-text">{{ trans('cruds.menu.physical_infrastructure.title') }}</span>
            </a>
            <div id="submenu7" class="collapse {{ (
            request()->is('admin/sites*')||
            request()->is('admin/buildings*')||
            request()->is('admin/bays*')||
            request()->is('admin/zones*')||
            request()->is('admin/physical*')||
            request()->is('admin/workstations*')||
            request()->is('admin/storage-devices*')||
            request()->is('admin/peripherals*')||
            request()->is('admin/phones*')||
            request()->is('admin/wifi-terminals*')||
            request()->is('admin/physical-links*')||
            request()->is('admin/wans*')||
            request()->is('admin/mans*')||
            request()->is('admin/lans*')
            ) ? 'show' : '' }}">
                @canAccess(\App\Models\Site::class)
                    <a href="{{ route('admin.sites.index') }}"
                       class="ps-4 {{ request()->is('admin/sites*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span class="menu-text">{{ trans('cruds.site.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Building::class)
                    <a href="{{ route('admin.buildings.index') }}"
                       class="ps-4 {{ request()->is('admin/buildings*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.building.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Bay::class)
                    <a href="{{ route('admin.bays.index') }}"
                       class="ps-4 {{ request()->is('admin/bays*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span class="menu-text">{{ trans('cruds.bay.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Zone::class)
                    <a href="{{ route('admin.zones.index') }}"
                       class="ps-4 {{ request()->is('admin/zones*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span class="menu-text">{{ trans('cruds.zone.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\PhysicalServer::class)
                    <a href="{{ route('admin.physical-servers.index') }}"
                       class="ps-4 {{ request()->is('admin/physical-servers*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.physicalServer.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Workstation::class)
                    <a href="{{ route('admin.workstations.index') }}"
                       class="ps-4 {{ request()->is('admin/workstations*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.workstation.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\StorageDevice::class)
                    <a href="{{ route('admin.storage-devices.index') }}"
                       class="ps-4 {{ request()->is('admin/storage-devices*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.storageDevice.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Peripheral::class)
                    <a href="{{ route('admin.peripherals.index') }}"
                       class="ps-4 {{ request()->is('admin/peripherals*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.peripheral.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Phone::class)
                    <a href="{{ route('admin.phones.index') }}"
                       class="ps-4 {{ request()->is('admin/phones*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.phone.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\PhysicalSwitch::class)
                    <a href="{{ route('admin.physical-switches.index') }}"
                       class="ps-4 {{ request()->is('admin/physical-switches*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.physicalSwitch.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\PhysicalRouter::class)
                    <a href="{{ route('admin.physical-routers.index') }}"
                       class="ps-4 {{ request()->is('admin/physical-routers*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.physicalRouter.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\WifiTerminal::class)
                    <a href="{{ route('admin.wifi-terminals.index') }}"
                       class="ps-4 {{ request()->is('admin/wifi-terminals*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.wifiTerminal.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\PhysicalSecurityDevice::class)
                    <a href="{{ route('admin.physical-security-devices.index') }}"
                       class="ps-4 {{ request()->is('admin/physical-security-devices*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.physicalSecurityDevice.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\PhysicalLink::class)
                    <a href="{{ route('admin.physical-links.index') }}"
                       class="ps-4 {{ request()->is('admin/physical-links*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span
                                class="menu-text">{{ trans('cruds.physicalLink.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Wan::class)
                    <a href="{{ route('admin.wans.index') }}"
                       class="ps-4 {{ request()->is('admin/wans*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span class="menu-text">{{ trans('cruds.wan.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Man::class)
                    <a href="{{ route('admin.mans.index') }}"
                       class="ps-4 {{ request()->is('admin/mans*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span class="menu-text">{{ trans('cruds.man.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Lan::class)
                    <a href="{{ route('admin.lans.index') }}"
                       class="ps-4 {{ request()->is('admin/lans*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns"></i><span class="menu-text">{{ trans('cruds.lan.title') }}</span>
                    </a>
                @endcanAccess
            </div>
        @endcanAccessAny
        {{-- configure n'est pas un modèle : @canany reste approprié ici --}}
        @canany([\App\Models\User::class, \App\Models\Role::class, \App\Models\Cartographer::class, \App\Models\AuditLog::class, 'configure'])
            <a class="dropdown-toggle" data-bs-toggle="collapse" href="#submenu9" role="button" aria-expanded="false">
                <i class="bi bi-gear-fill"></i><span
                        class="menu-text">{{ trans('cruds.menu.configuration.title') }}</span>
            </a>
            <div id="submenu9" class="collapse {{ (
            request()->is('admin/users*')||
            request()->is('admin/roles*')||
            request()->is('admin/cartographers*')||
            request()->is('admin/config*')||
            request()->is('admin/audit-logs*')||
            request()->is('admin/history*')
            ) ? 'show' : '' }}">
                @canAccess(\App\Models\User::class)
                    <a href="{{ route("admin.users.index") }}"
                       class="ps-4 {{ request()->is('admin/users*') ? 'active' : '' }}">
                        <i class="bi bi-person-fill"></i><span class="menu-text">{{ trans('cruds.user.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Role::class)
                    <a href="{{ route("admin.roles.index") }}"
                       class="ps-4 {{ request()->is('admin/roles*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i><span
                                class="menu-text">{{ trans('cruds.role.title') }}</span>
                    </a>
                @endcanAccess
                @canAccess(\App\Models\Cartographer::class)
                    <a href="{{ route('admin.cartographers.index') }}"
                       class="ps-4 {{ request()->is('admin/cartographers*') ? 'active' : '' }}">
                        <i class="bi bi-pin-map-fill"></i><span class="menu-text">{{ trans('cruds.cartographer.title') }}</span>
                    </a>
                @endcanAccess
                @can('configure')
                    <a href="{{ route("admin.config.parameters") }}"
                       class="ps-4 {{ request()->is('admin/config/parameters') ? 'active' : '' }}">
                        <i class="bi bi-wrench-adjustable"></i><span
                                class="menu-text">{{ trans('cruds.configuration.parameters.title_short') }}</span>
                    </a>
                    <a href="{{ route("admin.config.import") }}"
                       class="ps-4 {{ request()->is('admin/config/import') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-arrow-up-fill"></i><span
                                class="menu-text">{{ trans("cruds.configuration.import.title_short") }}</span>
                    </a>
                @endcan
                @canAccess(\App\Models\AuditLog::class)
                    <a href="{{ route("admin.audit-logs.index") }}"
                       class="ps-4 {{ request()->is('admin/audit-logs*') ? 'active' : '' }}">
                        <i class="bi bi-archive-fill"></i><span
                                class="menu-text">{{ trans('cruds.auditLog.title') }}</span>
                    </a>
                @endcanAccess
            </div>
        @endcanany
        <a href="#" onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
            <i class="bi bi-box-arrow-right"></i><span class="menu-text">{{ trans('global.logout') }}</span>
        </a>
    </div>
    @if (\App\Support\TrashMode::active())
        {{-- Mode corbeille : bouton de sortie au-dessus du numéro de version --}}
        <div class="sidebar-bottom" style="position: fixed; bottom: 0; width: 230px;">
            <div class="bg-white border-top p-2">
                <form method="POST" action="{{ route('admin.trash.exit') }}">
                    @csrf
                    <button type="submit" id="btn-trash-exit" class="btn btn-danger btn-sm w-100">
                        <i class="bi bi-box-arrow-left me-1"></i>{{ trans('cruds.trash.exit') }}
                    </button>
                </form>
            </div>
            <div class="sidebar-footer" style="position: static;">
                Open Source<br>
                Version {{ app('mercator.version') }}
            </div>
        </div>
    @else
        <div class="sidebar-footer">
            Open Source<br>
            Version {{ app('mercator.version') }}
        </div>
    @endif
</nav>

<script @nonce>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    var form = document.getElementById('active-perimeter-form');
    var $select = $('#active-perimeter');
    if (! form || ! $select.length) return;

    // Le select est initialisé en Select2 (voir app.js) : le changement doit
    // être écouté via jQuery, pas addEventListener, pour être fiable avec le
    // widget. Soumission immédiate et inconditionnelle — perte de saisie ou
    // 403 éventuel sur la page rechargée sont assumés (résolution périmètre).
    $select.on('change', function () {
        form.submit();
    });
});
</script>
