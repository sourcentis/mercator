<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\API;
use App\Http\Controllers\Report;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('login', [API\AuthController::class, 'login']);

Route::middleware(['api.protected'])->group(function () {
    // Data Processing & Security
    Route::post('data-processings/mass-store', [API\DataProcessingController::class, 'massStore'])->name('data-processings.mass-store');
    Route::put('data-processings/mass-update', [API\DataProcessingController::class, 'massUpdate'])->name('data-processings.mass-update');
    Route::delete('data-processings/mass-destroy', [API\DataProcessingController::class, 'massDestroy'])->name('data-processings.mass-destroy');
    Route::resource('data-processings', API\DataProcessingController::class);

    // Security Controls
    Route::post('security-controls/mass-store', [API\SecurityControlController::class, 'massStore'])->name('security-controls.mass-store');
    Route::put('security-controls/mass-update', [API\SecurityControlController::class, 'massUpdate'])->name('security-controls.mass-update');
    Route::delete('security-controls/mass-destroy', [API\SecurityControlController::class, 'massDestroy'])->name('security-controls.mass-destroy');
    Route::resource('security-controls', API\SecurityControlController::class);

    // Entities
    Route::post('entities/mass-store', [API\EntityController::class, 'massStore'])->name('entities.mass-store');
    Route::put('entities/mass-update', [API\EntityController::class, 'massUpdate'])->name('entities.mass-update');
    Route::delete('entities/mass-destroy', [API\EntityController::class, 'massDestroy'])->name('entities.mass-destroy');
    Route::resource('entities', API\EntityController::class);

    // Relations
    Route::post('relations/mass-store', [API\RelationController::class, 'massStore'])->name('relations.mass-store');
    Route::put('relations/mass-update', [API\RelationController::class, 'massUpdate'])->name('relations.mass-update');
    Route::delete('relations/mass-destroy', [API\RelationController::class, 'massDestroy'])->name('relations.mass-destroy');
    Route::resource('relations', API\RelationController::class);

    // Macro-Processuses
    Route::post('macro-processuses/mass-store', [API\MacroProcessusController::class, 'massStore'])->name('macro-processuses.mass-store');
    Route::put('macro-processuses/mass-update', [API\MacroProcessusController::class, 'massUpdate'])->name('macro-processuses.mass-update');
    Route::delete('macro-processuses/mass-destroy', [API\MacroProcessusController::class, 'massDestroy'])->name('macro-processuses.mass-destroy');
    Route::resource('macro-processuses', API\MacroProcessusController::class);

    // Processes
    Route::post('processes/mass-store', [API\ProcessController::class, 'massStore'])->name('processes.mass-store');
    Route::put('processes/mass-update', [API\ProcessController::class, 'massUpdate'])->name('processes.mass-update');
    Route::delete('processes/mass-destroy', [API\ProcessController::class, 'massDestroy'])->name('processes.mass-destroy');
    Route::resource('processes', API\ProcessController::class);

    // Operations
    Route::post('operations/mass-store', [API\OperationController::class, 'massStore'])->name('operations.mass-store');
    Route::put('operations/mass-update', [API\OperationController::class, 'massUpdate'])->name('operations.mass-update');
    Route::delete('operations/mass-destroy', [API\OperationController::class, 'massDestroy'])->name('operations.mass-destroy');
    Route::resource('operations', API\OperationController::class);

    // Actors
    Route::post('actors/mass-store', [API\ActorController::class, 'massStore'])->name('actors.mass-store');
    Route::put('actors/mass-update', [API\ActorController::class, 'massUpdate'])->name('actors.mass-update');
    Route::delete('actors/mass-destroy', [API\ActorController::class, 'massDestroy'])->name('actors.mass-destroy');
    Route::resource('actors', API\ActorController::class);

    // Activities
    Route::post('activities/mass-store', [API\ActivityController::class, 'massStore'])->name('activities.mass-store');
    Route::put('activities/mass-update', [API\ActivityController::class, 'massUpdate'])->name('activities.mass-update');
    Route::delete('activities/mass-destroy', [API\ActivityController::class, 'massDestroy'])->name('activities.mass-destroy');
    Route::resource('activities', API\ActivityController::class);

    // Tasks
    Route::post('tasks/mass-store', [API\TaskController::class, 'massStore'])->name('tasks.mass-store');
    Route::put('tasks/mass-update', [API\TaskController::class, 'massUpdate'])->name('tasks.mass-update');
    Route::delete('tasks/mass-destroy', [API\TaskController::class, 'massDestroy'])->name('tasks.mass-destroy');
    Route::resource('tasks', API\TaskController::class);

    // Information
    Route::post('information/mass-store', [API\InformationController::class, 'massStore'])->name('information.mass-store');
    Route::put('information/mass-update', [API\InformationController::class, 'massUpdate'])->name('information.mass-update');
    Route::delete('information/mass-destroy', [API\InformationController::class, 'massDestroy'])->name('information.mass-destroy');
    Route::resource('information', API\InformationController::class);

    // Applications
    Route::post('applications/mass-store', [API\ApplicationController::class, 'massStore'])->name('applications.mass-store');
    Route::put('applications/mass-update', [API\ApplicationController::class, 'massUpdate'])->name('applications.mass-update');
    Route::delete('applications/mass-destroy', [API\ApplicationController::class, 'massDestroy'])->name('applications.mass-destroy');
    Route::resource('applications', API\ApplicationController::class);

    // Application Blocks
    Route::post('application-blocks/mass-store', [API\ApplicationBlockController::class, 'massStore'])->name('application-blocks.mass-store');
    Route::put('application-blocks/mass-update', [API\ApplicationBlockController::class, 'massUpdate'])->name('application-blocks.mass-update');
    Route::delete('application-blocks/mass-destroy', [API\ApplicationBlockController::class, 'massDestroy'])->name('application-blocks.mass-destroy');
    Route::resource('application-blocks', API\ApplicationBlockController::class);

    // Application Modules
    Route::post('application-modules/mass-store', [API\ApplicationModuleController::class, 'massStore'])->name('application-modules.mass-store');
    Route::put('application-modules/mass-update', [API\ApplicationModuleController::class, 'massUpdate'])->name('application-modules.mass-update');
    Route::delete('application-modules/mass-destroy', [API\ApplicationModuleController::class, 'massDestroy'])->name('application-modules.mass-destroy');
    Route::resource('application-modules', API\ApplicationModuleController::class);

    // Application Services
    Route::post('application-services/mass-store', [API\ApplicationServiceController::class, 'massStore'])->name('application-services.mass-store');
    Route::put('application-services/mass-update', [API\ApplicationServiceController::class, 'massUpdate'])->name('application-services.mass-update');
    Route::delete('application-services/mass-destroy', [API\ApplicationServiceController::class, 'massDestroy'])->name('application-services.mass-destroy');
    Route::resource('application-services', API\ApplicationServiceController::class);

    // Databases
    Route::post('databases/mass-store', [API\DatabaseController::class, 'massStore'])->name('databases.mass-store');
    Route::put('databases/mass-update', [API\DatabaseController::class, 'massUpdate'])->name('databases.mass-update');
    Route::delete('databases/mass-destroy', [API\DatabaseController::class, 'massDestroy'])->name('databases.mass-destroy');
    Route::resource('databases', API\DatabaseController::class);

    // Application Flows
    Route::post('application-flows/mass-store', [API\ApplicationFlowController::class, 'massStore'])->name('fluxes.mass-store');
    Route::put('application-flows/mass-update', [API\ApplicationFlowController::class, 'massUpdate'])->name('fluxes.mass-update');
    Route::delete('application-flows/mass-destroy', [API\ApplicationFlowController::class, 'massDestroy'])->name('fluxes.mass-destroy');
    Route::resource('application-flows', API\ApplicationFlowController::class);

    // Zone Admins
    Route::post('zone-admins/mass-store', [API\ZoneAdminController::class, 'massStore'])->name('zone-admins.mass-store');
    Route::put('zone-admins/mass-update', [API\ZoneAdminController::class, 'massUpdate'])->name('zone-admins.mass-update');
    Route::delete('zone-admins/mass-destroy', [API\ZoneAdminController::class, 'massDestroy'])->name('zone-admins.mass-destroy');
    Route::resource('zone-admins', API\ZoneAdminController::class);

    // Annuaires
    Route::post('annuaires/mass-store', [API\AnnuaireController::class, 'massStore'])->name('annuaires.mass-store');
    Route::put('annuaires/mass-update', [API\AnnuaireController::class, 'massUpdate'])->name('annuaires.mass-update');
    Route::delete('annuaires/mass-destroy', [API\AnnuaireController::class, 'massDestroy'])->name('annuaires.mass-destroy');
    Route::resource('annuaires', API\AnnuaireController::class);

    // Forest Ads
    Route::post('forest-ads/mass-store', [API\ForestAdController::class, 'massStore'])->name('forest-ads.mass-store');
    Route::put('forest-ads/mass-update', [API\ForestAdController::class, 'massUpdate'])->name('forest-ads.mass-update');
    Route::delete('forest-ads/mass-destroy', [API\ForestAdController::class, 'massDestroy'])->name('forest-ads.mass-destroy');
    Route::resource('forest-ads', API\ForestAdController::class);

    // Domain Ads
    Route::post('domains/mass-store', [API\DomainController::class, 'massStore'])->name('domains.mass-store');
    Route::put('domains/mass-update', [API\DomainController::class, 'massUpdate'])->name('domains.mass-update');
    Route::delete('domains/mass-destroy', [API\DomainController::class, 'massDestroy'])->name('domains.mass-destroy');
    Route::resource('domains', API\DomainController::class);

    // Admin User
    Route::post('admin-users/mass-store', [API\AdminUserController::class, 'massStore'])->name('admin-users.mass-store');
    Route::put('admin-users/mass-update', [API\AdminUserController::class, 'massUpdate'])->name('admin-users.mass-update');
    Route::delete('admin-users/mass-destroy', [API\AdminUserController::class, 'massDestroy'])->name('admin-users.mass-destroy');
    Route::resource('admin-users', API\AdminUserController::class);

    // Networks
    Route::post('networks/mass-store', [API\NetworkController::class, 'massStore'])->name('networks.mass-store');
    Route::put('networks/mass-update', [API\NetworkController::class, 'massUpdate'])->name('networks.mass-update');
    Route::delete('networks/mass-destroy', [API\NetworkController::class, 'massDestroy'])->name('networks.mass-destroy');
    Route::resource('networks', API\NetworkController::class);

    // Subnetworks
    Route::post('subnetworks/mass-store', [API\SubnetworkController::class, 'massStore'])->name('subnetworks.mass-store');
    Route::put('subnetworks/mass-update', [API\SubnetworkController::class, 'massUpdate'])->name('subnetworks.mass-update');
    Route::delete('subnetworks/mass-destroy', [API\SubnetworkController::class, 'massDestroy'])->name('subnetworks.mass-destroy');
    Route::resource('subnetworks', API\SubnetworkController::class);

    // Gateways
    Route::post('gateways/mass-store', [API\GatewayController::class, 'massStore'])->name('gateways.mass-store');
    Route::put('gateways/mass-update', [API\GatewayController::class, 'massUpdate'])->name('gateways.mass-update');
    Route::delete('gateways/mass-destroy', [API\GatewayController::class, 'massDestroy'])->name('gateways.mass-destroy');
    Route::resource('gateways', API\GatewayController::class);

    // External-Connected Entities
    Route::post('external-connected-entities/mass-store', [API\ExternalConnectedEntityController::class, 'massStore'])->name('external-connected-entities.mass-store');
    Route::put('external-connected-entities/mass-update', [API\ExternalConnectedEntityController::class, 'massUpdate'])->name('external-connected-entities.mass-update');
    Route::delete('external-connected-entities/mass-destroy', [API\ExternalConnectedEntityController::class, 'massDestroy'])->name('external-connected-entities.mass-destroy');
    Route::resource('external-connected-entities', API\ExternalConnectedEntityController::class);

    // Network Switches
    Route::post('network-switches/mass-store', [API\NetworkSwitchController::class, 'massStore'])->name('network-switches.mass-store');
    Route::put('network-switches/mass-update', [API\NetworkSwitchController::class, 'massUpdate'])->name('network-switches.mass-update');
    Route::delete('network-switches/mass-destroy', [API\NetworkSwitchController::class, 'massDestroy'])->name('network-switches.mass-destroy');
    Route::resource('network-switches', API\NetworkSwitchController::class);

    // Routers
    Route::post('routers/mass-store', [API\RouterController::class, 'massStore'])->name('routers.mass-store');
    Route::put('routers/mass-update', [API\RouterController::class, 'massUpdate'])->name('routers.mass-update');
    Route::delete('routers/mass-destroy', [API\RouterController::class, 'massDestroy'])->name('routers.mass-destroy');
    Route::resource('routers', API\RouterController::class);

    // Security Devices
    Route::post('security-devices/mass-store', [API\SecurityDeviceController::class, 'massStore'])->name('security-devices.mass-store');
    Route::put('security-devices/mass-update', [API\SecurityDeviceController::class, 'massUpdate'])->name('security-devices.mass-update');
    Route::delete('security-devices/mass-destroy', [API\SecurityDeviceController::class, 'massDestroy'])->name('security-devices.mass-destroy');
    Route::resource('security-devices', API\SecurityDeviceController::class);

    // DHCP Servers
    Route::post('dhcp-servers/mass-store', [API\DhcpServerController::class, 'massStore'])->name('dhcp-servers.mass-store');
    Route::put('dhcp-servers/mass-update', [API\DhcpServerController::class, 'massUpdate'])->name('dhcp-servers.mass-update');
    Route::delete('dhcp-servers/mass-destroy', [API\DhcpServerController::class, 'massDestroy'])->name('dhcp-servers.mass-destroy');
    Route::resource('dhcp-servers', API\DhcpServerController::class);

    // DNS Servers
    Route::post('dnsservers/mass-store', [API\DnsserverController::class, 'massStore'])->name('dnsservers.mass-store');
    Route::put('dnsservers/mass-update', [API\DnsserverController::class, 'massUpdate'])->name('dnsservers.mass-update');
    Route::delete('dnsservers/mass-destroy', [API\DnsserverController::class, 'massDestroy'])->name('dnsservers.mass-destroy');
    Route::resource('dnsservers', API\DnsserverController::class);

    // Clusters
    Route::post('clusters/mass-store', [API\ClusterController::class, 'massStore'])->name('clusters.mass-store');
    Route::put('clusters/mass-update', [API\ClusterController::class, 'massUpdate'])->name('clusters.mass-update');
    Route::delete('clusters/mass-destroy', [API\ClusterController::class, 'massDestroy'])->name('clusters.mass-destroy');
    Route::resource('clusters', API\ClusterController::class);

    // Containers
    Route::delete('containers/mass-destroy', [API\ContainerController::class, 'massDestroy'])->name('containers.mass-destroy');
    Route::resource('containers', API\ContainerController::class);

    // Logical Servers
    Route::post('logical-servers/mass-store', [API\LogicalServerController::class, 'massStore'])->name('logical-servers.mass-store');
    Route::put('logical-servers/mass-update', [API\LogicalServerController::class, 'massUpdate'])->name('logical-servers.mass-update');
    Route::delete('logical-servers/mass-destroy', [API\LogicalServerController::class, 'massDestroy'])->name('logical-servers.mass-destroy');
    Route::resource('logical-servers', API\LogicalServerController::class);

    // Backups
    Route::post('backups/mass-store', [API\BackupController::class, 'massStore'])->name('backups.mass-store');
    Route::put('backups/mass-update', [API\BackupController::class, 'massUpdate'])->name('backups.mass-update');
    Route::delete('backups/mass-destroy', [API\BackupController::class, 'massDestroy'])->name('backups.mass-destroy');
    Route::resource('backups', API\BackupController::class);

    // Logical Flows
    Route::post('logical-flows/mass-store', [API\LogicalFlowController::class, 'massStore'])->name('logical-flows.mass-store');
    Route::put('logical-flows/mass-update', [API\LogicalFlowController::class, 'massUpdate'])->name('logical-flows.mass-update');
    Route::delete('logical-flows/mass-destroy', [API\LogicalFlowController::class, 'massDestroy'])->name('logical-flows.mass-destroy');
    Route::resource('logical-flows', API\LogicalFlowController::class);

    // Certificates
    Route::post('certificates/mass-store', [API\CertificateController::class, 'massStore'])->name('certificates.mass-store');
    Route::put('certificates/mass-update', [API\CertificateController::class, 'massUpdate'])->name('certificates.mass-update');
    Route::delete('certificates/mass-destroy', [API\CertificateController::class, 'massDestroy'])->name('certificates.mass-destroy');
    Route::resource('certificates', API\CertificateController::class);

    // Sites
    Route::post('sites/mass-store', [API\SiteController::class, 'massStore'])->name('sites.mass-store');
    Route::put('sites/mass-update', [API\SiteController::class, 'massUpdate'])->name('sites.mass-update');
    Route::delete('sites/mass-destroy', [API\SiteController::class, 'massDestroy'])->name('sites.mass-destroy');
    Route::resource('sites', API\SiteController::class);

    // Buildings
    Route::post('buildings/mass-store', [API\BuildingController::class, 'massStore'])->name('buildings.mass-store');
    Route::put('buildings/mass-update', [API\BuildingController::class, 'massUpdate'])->name('buildings.mass-update');
    Route::delete('buildings/mass-destroy', [API\BuildingController::class, 'massDestroy'])->name('buildings.mass-destroy');
    Route::resource('buildings', API\BuildingController::class);

    // Zones
    Route::post('zones/mass-store', [API\ZoneController::class, 'massStore'])->name('zones.mass-store');
    Route::put('zones/mass-update', [API\ZoneController::class, 'massUpdate'])->name('zones.mass-update');
    Route::delete('zones/mass-destroy', [API\ZoneController::class, 'massDestroy'])->name('zones.mass-destroy');
    Route::resource('zones', API\ZoneController::class);

    // Bays
    Route::post('bays/mass-store', [API\BayController::class, 'massStore'])->name('bays.mass-store');
    Route::put('bays/mass-update', [API\BayController::class, 'massUpdate'])->name('bays.mass-update');
    Route::delete('bays/mass-destroy', [API\BayController::class, 'massDestroy'])->name('bays.mass-destroy');
    Route::resource('bays', API\BayController::class);

    // Physical Servers
    Route::post('physical-servers/mass-store', [API\PhysicalServerController::class, 'massStore'])->name('physical-servers.mass-store');
    Route::put('physical-servers/mass-update', [API\PhysicalServerController::class, 'massUpdate'])->name('physical-servers.mass-update');
    Route::delete('physical-servers/mass-destroy', [API\PhysicalServerController::class, 'massDestroy'])->name('physical-servers.mass-destroy');
    Route::resource('physical-servers', API\PhysicalServerController::class);

    // Workstations
    Route::post('workstations/mass-store', [API\WorkstationController::class, 'massStore'])->name('workstations.mass-store');
    Route::put('workstations/mass-update', [API\WorkstationController::class, 'massUpdate'])->name('workstations.mass-update');
    Route::delete('workstations/mass-destroy', [API\WorkstationController::class, 'massDestroy'])->name('workstations.mass-destroy');
    Route::resource('workstations', API\WorkstationController::class);

    // Storage Devices
    Route::post('storage-devices/mass-store', [API\StorageDeviceController::class, 'massStore'])->name('storage-devices.mass-store');
    Route::put('storage-devices/mass-update', [API\StorageDeviceController::class, 'massUpdate'])->name('storage-devices.mass-update');
    Route::delete('storage-devices/mass-destroy', [API\StorageDeviceController::class, 'massDestroy'])->name('storage-devices.mass-destroy');
    Route::resource('storage-devices', API\StorageDeviceController::class);

    // Peripherals
    Route::post('peripherals/mass-store', [API\PeripheralController::class, 'massStore'])->name('peripherals.mass-store');
    Route::put('peripherals/mass-update', [API\PeripheralController::class, 'massUpdate'])->name('peripherals.mass-update');
    Route::delete('peripherals/mass-destroy', [API\PeripheralController::class, 'massDestroy'])->name('peripherals.mass-destroy');
    Route::resource('peripherals', API\PeripheralController::class);

    // Phones
    Route::post('phones/mass-store', [API\PhoneController::class, 'massStore'])->name('phones.mass-store');
    Route::put('phones/mass-update', [API\PhoneController::class, 'massUpdate'])->name('phones.mass-update');
    Route::delete('phones/mass-destroy', [API\PhoneController::class, 'massDestroy'])->name('phones.mass-destroy');
    Route::resource('phones', API\PhoneController::class);

    // Physical Switches
    Route::post('physical-switches/mass-store', [API\PhysicalSwitchController::class, 'massStore'])->name('physical-switches.mass-store');
    Route::put('physical-switches/mass-update', [API\PhysicalSwitchController::class, 'massUpdate'])->name('physical-switches.mass-update');
    Route::delete('physical-switches/mass-destroy', [API\PhysicalSwitchController::class, 'massDestroy'])->name('physical-switches.mass-destroy');
    Route::resource('physical-switches', API\PhysicalSwitchController::class);

    // Physical Routers
    Route::post('physical-routers/mass-store', [API\PhysicalRouterController::class, 'massStore'])->name('physical-routers.mass-store');
    Route::put('physical-routers/mass-update', [API\PhysicalRouterController::class, 'massUpdate'])->name('physical-routers.mass-update');
    Route::delete('physical-routers/mass-destroy', [API\PhysicalRouterController::class, 'massDestroy'])->name('physical-routers.mass-destroy');
    Route::resource('physical-routers', API\PhysicalRouterController::class);

    // Wifi Terminals
    Route::post('wifi-terminals/mass-store', [API\WifiTerminalController::class, 'massStore'])->name('wifi-terminals.mass-store');
    Route::put('wifi-terminals/mass-update', [API\WifiTerminalController::class, 'massUpdate'])->name('wifi-terminals.mass-update');
    Route::delete('wifi-terminals/mass-destroy', [API\WifiTerminalController::class, 'massDestroy'])->name('wifi-terminals.mass-destroy');
    Route::resource('wifi-terminals', API\WifiTerminalController::class);

    // Physical Security Devices
    Route::post('physical-security-devices/mass-store', [API\PhysicalSecurityDeviceController::class, 'massStore'])->name('physical-security-devices.mass-store');
    Route::put('physical-security-devices/mass-update', [API\PhysicalSecurityDeviceController::class, 'massUpdate'])->name('physical-security-devices.mass-update');
    Route::delete('physical-security-devices/mass-destroy', [API\PhysicalSecurityDeviceController::class, 'massDestroy'])->name('physical-security-devices.mass-destroy');
    Route::resource('physical-security-devices', API\PhysicalSecurityDeviceController::class);

    // Wans
    Route::post('wans/mass-store', [API\WanController::class, 'massStore'])->name('wans.mass-store');
    Route::put('wans/mass-update', [API\WanController::class, 'massUpdate'])->name('wans.mass-update');
    Route::delete('wans/mass-destroy', [API\WanController::class, 'massDestroy'])->name('wans.mass-destroy');
    Route::resource('wans', API\WanController::class);

    // Mans
    Route::post('mans/mass-store', [API\ManController::class, 'massStore'])->name('mans.mass-store');
    Route::put('mans/mass-update', [API\ManController::class, 'massUpdate'])->name('mans.mass-update');
    Route::delete('mans/mass-destroy', [API\ManController::class, 'massDestroy'])->name('mans.mass-destroy');
    Route::resource('mans', API\ManController::class);

    // Lans
    Route::post('lans/mass-store', [API\LanController::class, 'massStore'])->name('lans.mass-store');
    Route::put('lans/mass-update', [API\LanController::class, 'massUpdate'])->name('lans.mass-update');
    Route::delete('lans/mass-destroy', [API\LanController::class, 'massDestroy'])->name('lans.mass-destroy');
    Route::resource('lans', API\LanController::class);

    // VLANs
    Route::post('vlans/mass-store', [API\VlanController::class, 'massStore'])->name('vlans.mass-store');
    Route::put('vlans/mass-update', [API\VlanController::class, 'massUpdate'])->name('vlans.mass-update');
    Route::delete('vlans/mass-destroy', [API\VlanController::class, 'massDestroy'])->name('vlans.mass-destroy');
    Route::resource('vlans', API\VlanController::class);

    // Physical Links
    Route::post('physical-links/mass-store', [API\PhysicalLinkController::class, 'massStore'])->name('physical-links.mass-store');
    Route::put('physical-links/mass-update', [API\PhysicalLinkController::class, 'massUpdate'])->name('physical-links.mass-update');
    Route::delete('physical-links/mass-destroy', [API\PhysicalLinkController::class, 'massDestroy'])->name('physical-links.mass-destroy');
    Route::resource('physical-links', API\PhysicalLinkController::class);

    // Users
    Route::post('users/mass-store', [API\UserController::class, 'massStore'])->name('users.mass-store');
    Route::put('users/mass-update', [API\UserController::class, 'massUpdate'])->name('users.mass-update');
    Route::delete('users/mass-destroy', [API\UserController::class, 'massDestroy'])->name('users.mass-destroy');
    Route::resource('users', API\UserController::class);

    // Permissions
    Route::resource('permissions', API\PermissionController::class);

    // Roles
    Route::resource('roles', API\RoleController::class);

    // Perimeters
    Route::resource('perimeters', API\PerimeterController::class);

    // Cartographers
    Route::post('cartographers/mass-store', [API\CartographerController::class, 'massStore'])->name('cartographers.mass-store');
    Route::put('cartographers/mass-update', [API\CartographerController::class, 'massUpdate'])->name('cartographers.mass-update');
    Route::delete('cartographers/mass-destroy', [API\CartographerController::class, 'massDestroy'])->name('cartographers.mass-destroy');
    Route::resource('cartographers', API\CartographerController::class);

    // Graphs
    Route::post('graphs/mass-store', [API\GraphController::class, 'massStore'])->name('graphs.mass-store');
    Route::put('graphs/mass-update', [API\GraphController::class, 'massUpdate'])->name('graphs.mass-update');
    Route::delete('graphs/mass-destroy', [API\GraphController::class, 'massDestroy'])->name('graphs.mass-destroy');
    Route::resource('graphs', API\GraphController::class);

    // Queries
    Route::put('queries/mass-store', [API\QueryController::class, 'massStore'])->name('queries.mass-store');
    Route::put('queries/mass-update', [API\QueryController::class, 'massUpdate'])->name('queries.mass-update');
    Route::delete('queries/mass-destroy', [API\QueryController::class, 'massDestroy'])->name('queries.mass-destroy');
    Route::get('queries/execute/{query}', [API\QueryController::class, 'execute'])->name('queries.execute');
    Route::post('queries/execute', [API\QueryController::class, 'executeDsl'])->name('queries.execute-dsl');
    Route::resource('queries', API\QueryController::class);

    // =======================================
    // Reports
    // =======================================
    Route::get('report/cartography', [Admin\CartographyController::class, 'cartography']);
    Route::get('report/entities', [Report\EntityList::class, 'generateExcel']);
    Route::get('report/applicationsByBlocks', [Report\ApplicationList::class, 'generate']);
    Route::get('report/directory', [Report\Directory::class, 'generateDocx']);
    Route::get('report/logicalServers', [Report\LogicalServers::class, 'generate']);
    Route::get('report/securityNeeds', [Report\SecurityNeeds::class, 'generate']);
    Route::get('report/logicalServerConfigs', [Report\LogicalServerConfigs::class, 'generate']);
    Route::get('report/externalAccess', [Report\ExternalAccess::class, 'generate']);
    Route::get('report/physicalInventory', [Report\PhysicalInventory::class, 'generate']);
    Route::get('report/vlans', [Report\VLANList::class, 'generate']);
    Route::get('report/workstations', [Report\WorkstationList::class, 'generate']);
    Route::get('report/cve', [Admin\CVEController::class, 'list']);

    // GDPR
    Route::get('report/activityList', [Report\ActivityList::class, 'generate'])->name('report.activityList');
    Route::get('report/activityReport', [Report\ActivityReport::class, 'generateDocx'])->name('report.activityReport');

    Route::get('report/impacts', [Report\ImpactList::class, 'generateExcel'])->name('report.view.impacts');
    Route::get('report/rto', [Report\RTO::class, 'generateExcel'])->name('report.view.rto');

    // Documents
    Route::get('documents/{document}/download', [API\DocumentController::class, 'download'])->name('documents.download');
    Route::post('documents/mass-store', [API\DocumentController::class, 'massStore'])->name('documents.mass-store');
    Route::put('documents/mass-update', [API\DocumentController::class, 'massUpdate'])->name('documents.mass-update');
    Route::delete('documents/mass-destroy', [API\DocumentController::class, 'massDestroy'])->name('documents.mass-destroy');
    Route::resource('documents', API\DocumentController::class);

});
