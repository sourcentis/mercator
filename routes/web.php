<?php

use App\Http\Controllers;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\SsoController;
use App\Http\Controllers\BPMNController;
use App\Http\Controllers\QueryController;
use App\Http\Controllers\Report;
use App\Http\Controllers\Report\AuditController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Support\ReportTemplateSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

Route::redirect('/', '/login');

Route::get('/home', function () {
    if (session('status')) {
        return redirect()->route('admin.home')->with('status', session('status'));
    }

    return redirect()->route('admin.home');
});

// Authentification routes
Auth::routes([
    'login' => true,
    'logout' => true,
    'register' => false, // Désactiver l'inscription
    'reset' => false, // Custom version
    'confirm' => false, // Désactiver la confirmation de mot de passe
    'verify' => false, // Désactiver la vérification par e-mail
]);

// Add get logout route
Route::get('/logout', fn () => redirect('/'));

// Forget password
Route::get('forget-password', [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('forget.password.get');
Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post');
Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
Route::post('reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');

// keycloak
Route::get('login/keycloak', [SsoController::class, 'redirectToKeycloak'])->name('login.keycloak');
Route::get('login/keycloak/callback', [SsoController::class, 'handleKeycloakCallback'])->name('keycloak.callback');

// CSP
Route::post('/csp-report', function (Request $request) {
    Log::channel('security')->warning('CSP Violation', [
        'report' => $request->getContent(),
        'ip' => $request->ip(),
    ]);

    return response()->noContent();
})->withoutMiddleware([VerifyCsrfToken::class]);

// Admin
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['web.protected']], function (): void {
    // Dashboard
    Route::get('/', [Admin\HomeController::class, 'index'])->name('home');

    // Test page
    Route::get('/test', function () {
        return view('test');
    });

    // Roles
    Route::get('roles/clone/{id}', [Admin\RolesController::class, 'clone'])->name('roles.clone');
    Route::resource('roles', Admin\RolesController::class);
    Route::delete('roles-destroy', [Admin\RolesController::class, 'massDestroy'])->name('roles.massDestroy');

    // Users
    Route::resource('users', Admin\UsersController::class);
    Route::delete('users-destroy', [Admin\UsersController::class, 'massDestroy'])->name('users.massDestroy');

    // DataProcessing
    Route::resource('data-processings', Admin\DataProcessingController::class);
    Route::delete('data-processings-destroy', [Admin\DataProcessingController::class, 'massDestroy'])->name('data-processings.massDestroy');

    // SecurityControls
    Route::resource('security-controls', Admin\SecurityControlController::class);
    Route::delete('security-controls-destroy', [Admin\SecurityControlController::class, 'massDestroy'])->name('security-controls.massDestroy');
    Route::get('security-controls-assign', [Admin\SecurityControlController::class, 'assign'])->name('security-controls.assign');
    Route::get('security-controls-list', [Admin\SecurityControlController::class, 'list'])->name('security-controls.list');
    Route::put('security-controls-associate', [Admin\SecurityControlController::class, 'associate'])->name('security-controls-associate');

    // Entities
    Route::resource('entities', Admin\EntityController::class);
    Route::delete('entities-destroy', [Admin\EntityController::class, 'massDestroy'])->name('entities.massDestroy');

    // Relations
    Route::resource('relations', Admin\RelationController::class);
    Route::delete('relations-destroy', [Admin\RelationController::class, 'massDestroy'])->name('relations.massDestroy');

    // Processes
    Route::resource('processes', Admin\ProcessController::class);
    Route::delete('processes-destroy', [Admin\ProcessController::class, 'massDestroy'])->name('processes.massDestroy');

    // Operations
    Route::resource('operations', Admin\OperationController::class);
    Route::delete('operations-destroy', [Admin\OperationController::class, 'massDestroy'])->name('operations.massDestroy');

    // Actors
    Route::resource('actors', Admin\ActorController::class);
    Route::delete('actors-destroy', [Admin\ActorController::class, 'massDestroy'])->name('actors.massDestroy');

    // Activities
    Route::resource('activities', Admin\ActivityController::class);
    Route::delete('activities-destroy', [Admin\ActivityController::class, 'massDestroy'])->name('activities.massDestroy');

    // Tasks
    Route::resource('tasks', Admin\TaskController::class);
    Route::delete('tasks-destroy', [Admin\TaskController::class, 'massDestroy'])->name('tasks.massDestroy');

    // Information
    Route::resource('information', Admin\InformationController::class);
    Route::delete('information-destroy', [Admin\InformationController::class, 'massDestroy'])->name('information.massDestroy');

    // Application Blocks
    Route::resource('application-blocks', Admin\ApplicationBlockController::class);
    Route::delete('application-blocks-destroy', [Admin\ApplicationBlockController::class, 'massDestroy'])->name('application-blocks.massDestroy');

    // Applications
    Route::resource('applications', Admin\ApplicationController::class);
    Route::get('applications-clone/{id}', [Admin\ApplicationController::class, 'clone'])->name('applications.clone');
    Route::delete('applications-destroy', [Admin\ApplicationController::class, 'massDestroy'])->name('applications.massDestroy');

    // Application Services
    Route::resource('application-services', Admin\ApplicationServiceController::class);
    Route::delete('application-services-destroy', [Admin\ApplicationServiceController::class, 'massDestroy'])->name('application-services.massDestroy');

    // Application Events
    Route::resource('application-events', Admin\ApplicationEventController::class);

    // Databases
    Route::resource('databases', Admin\DatabaseController::class);
    Route::delete('databases-destroy', [Admin\DatabaseController::class, 'massDestroy'])->name('databases.massDestroy');

    // Application Flows
    Route::resource('application-flows', Admin\ApplicationFlowController::class)->parameters(['application-flows' => 'flow']);
    Route::delete('application-flows-destroy', [Admin\ApplicationFlowController::class, 'massDestroy'])->name('application-flows.massDestroy');

    // Zone Admins
    Route::resource('zone-admins', Admin\ZoneAdminController::class);
    Route::delete('zone-admins-destroy', [Admin\ZoneAdminController::class, 'massDestroy'])->name('zone-admins.massDestroy');

    // Annuaires
    Route::resource('annuaires', Admin\AnnuaireController::class);
    Route::delete('annuaires-destroy', [Admin\AnnuaireController::class, 'massDestroy'])->name('annuaires.massDestroy');

    // Forest Ads
    Route::resource('forest-ads', Admin\ForestAdController::class);
    Route::delete('forest-ads-destroy', [Admin\ForestAdController::class, 'massDestroy'])->name('forest-ads.massDestroy');

    // Domain Ads
    Route::resource('domains', Admin\DomainController::class);
    Route::delete('domains-destroy', [Admin\DomainController::class, 'massDestroy'])->name('domains.massDestroy');

    // Admin User
    Route::resource('admin-users', Admin\AdminUserController::class);
    Route::delete('admin-users-destroy', [Admin\AdminUserController::class, 'massDestroy'])->name('admin-users.massDestroy');

    // Networks
    Route::resource('networks', Admin\NetworkController::class);
    Route::delete('networks-destroy', [Admin\NetworkController::class, 'massDestroy'])->name('networks.massDestroy');

    // Subnetworks
    Route::resource('subnetworks', Admin\SubnetworkController::class);
    Route::delete('subnetworks-destroy', [Admin\SubnetworkController::class, 'massDestroy'])->name('subnetworks.massDestroy');

    // Gateways
    Route::resource('gateways', Admin\GatewayController::class);
    Route::delete('gateways-destroy', [Admin\GatewayController::class, 'massDestroy'])->name('gateways.massDestroy');

    // External-Connected Entities
    Route::resource('external-connected-entities', Admin\ExternalConnectedEntityController::class);
    Route::delete('external-connected-entities-destroy', [Admin\ExternalConnectedEntityController::class, 'massDestroy'])->name('external-connected-entities.massDestroy');

    // Network Switches
    Route::resource('network-switches', Admin\NetworkSwitchController::class);
    Route::delete('network-switches-destroy', [Admin\NetworkSwitchController::class, 'massDestroy'])->name('network-switches.massDestroy');

    // Routers
    Route::resource('routers', Admin\RouterController::class);
    Route::delete('routers-destroy', [Admin\RouterController::class, 'massDestroy'])->name('routers.massDestroy');

    // Security Devices
    Route::resource('security-devices', Admin\SecurityDeviceController::class);
    Route::delete('security-devices-destroy', [Admin\SecurityDeviceController::class, 'massDestroy'])->name('security-devices.massDestroy');

    // Dhcp Servers
    Route::resource('dhcp-servers', Admin\DhcpServerController::class);
    Route::delete('dhcp-servers-destroy', [Admin\DhcpServerController::class, 'massDestroy'])->name('dhcp-servers.massDestroy');

    // Dnsservers
    Route::resource('dnsservers', Admin\DnsserverController::class);
    Route::delete('dnsservers-destroy', [Admin\DnsserverController::class, 'massDestroy'])->name('dnsservers.massDestroy');

    // Clusters
    Route::resource('clusters', Admin\ClusterController::class);
    Route::delete('clusters-destroy', [Admin\ClusterController::class, 'massDestroy'])->name('clusters.massDestroy');

    // Logical Servers
    Route::resource('logical-servers', Admin\LogicalServerController::class);
    Route::post('logical-servers-data', [Admin\LogicalServerController::class, 'getData'])->name('logical-servers.data');
    Route::delete('logical-servers-destroy', [Admin\LogicalServerController::class, 'massDestroy'])->name('logical-servers.massDestroy');

    // Backups
    Route::resource('backups', Admin\BackupController::class);
    Route::delete('backups-destroy', [Admin\BackupController::class, 'massDestroy'])->name('backups.massDestroy');

    // Containers
    Route::resource('containers', Admin\ContainerController::class);
    Route::delete('containers-destroy', [Admin\ContainerController::class, 'massDestroy'])->name('containers.massDestroy');

    // Logical Flows
    Route::resource('logical-flows', Admin\LogicalFlowController::class);
    Route::delete('logical-flows-destroy', [Admin\LogicalFlowController::class, 'massDestroy'])->name('logical-flows.massDestroy');

    // Certificates
    Route::resource('certificates', Admin\CertificateController::class);
    Route::delete('certificates-destroy', [Admin\CertificateController::class, 'massDestroy'])->name('certificates.massDestroy');

    // Sites
    Route::resource('sites', Admin\SiteController::class);
    Route::get('sites/{site}/clone', [Admin\SiteController::class, 'clone'])->name('sites.clone');
    Route::delete('sites-destroy', [Admin\SiteController::class, 'massDestroy'])->name('sites.massDestroy');

    // Buildings
    Route::resource('buildings', Admin\BuildingController::class);
    Route::get('buildings/{building}/clone', [Admin\BuildingController::class, 'clone'])->name('buildings.clone');
    Route::delete('buildings-destroy', [Admin\BuildingController::class, 'massDestroy'])->name('buildings.massDestroy');

    // Bays
    Route::resource('bays', Admin\BayController::class);
    Route::get('bays/{bay}/clone', [Admin\BayController::class, 'clone'])->name('bays.clone');
    Route::delete('bays-destroy', [Admin\BayController::class, 'massDestroy'])->name('bays.massDestroy');

    // Zones
    Route::resource('zones', Admin\ZoneController::class);
    Route::delete('zones-destroy', [Admin\ZoneController::class, 'massDestroy'])->name('zones.massDestroy');

    // Physical Servers
    Route::resource('physical-servers', Admin\PhysicalServerController::class);
    Route::get('physical-servers-clone/{id}', [Admin\PhysicalServerController::class, 'clone'])->name('physical-servers.clone');
    Route::delete('physical-servers-destroy', [Admin\PhysicalServerController::class, 'massDestroy'])->name('physical-servers.massDestroy');

    // Workstations
    Route::resource('workstations', Admin\WorkstationController::class);
    Route::get('workstations-clone/{id}', [Admin\WorkstationController::class, 'clone'])->name('workstations.clone');
    Route::delete('workstations-destroy', [Admin\WorkstationController::class, 'massDestroy'])->name('workstations.massDestroy');

    // Storage Devices
    Route::resource('storage-devices', Admin\StorageDeviceController::class);
    Route::delete('storage-devices-destroy', [Admin\StorageDeviceController::class, 'massDestroy'])->name('storage-devices.massDestroy');

    // Peripherals
    Route::resource('peripherals', Admin\PeripheralController::class);
    Route::get('peripherals-clone/{id}', [Admin\PeripheralController::class, 'clone'])->name('peripherals.clone');
    Route::delete('peripherals-destroy', [Admin\PeripheralController::class, 'massDestroy'])->name('peripherals.massDestroy');

    // Phones
    Route::resource('phones', Admin\PhoneController::class);
    Route::get('phones-clone/{id}', [Admin\PhoneController::class, 'clone'])->name('phones.clone');
    Route::delete('phones-destroy', [Admin\PhoneController::class, 'massDestroy'])->name('phones.massDestroy');

    // Physical Switches
    Route::resource('physical-switches', Admin\PhysicalSwitchController::class);
    Route::get('physical-switches-clone/{id}', [Admin\PhysicalSwitchController::class, 'clone'])->name('physical-switches.clone');
    Route::delete('physical-switches-destroy', [Admin\PhysicalSwitchController::class, 'massDestroy'])->name('physical-switches.massDestroy');

    // Physical Routers
    Route::resource('physical-routers', Admin\PhysicalRouterController::class);
    Route::get('physical-routers-clone/{id}', [Admin\PhysicalRouterController::class, 'clone'])->name('physical-routers.clone');
    Route::delete('physical-routers-destroy', [Admin\PhysicalRouterController::class, 'massDestroy'])->name('physical-routers.massDestroy');

    // Wifi Terminals
    Route::resource('wifi-terminals', Admin\WifiTerminalController::class);
    Route::get('wifi-terminals-clone/{id}', [Admin\WifiTerminalController::class, 'clone'])->name('wifi-terminals.clone');
    Route::delete('wifi-terminals-destroy', [Admin\WifiTerminalController::class, 'massDestroy'])->name('wifi-terminals.massDestroy');

    // Physical Security Devices
    Route::resource('physical-security-devices', Admin\PhysicalSecurityDeviceController::class);
    Route::delete('physical-security-devices-destroy', [Admin\PhysicalSecurityDeviceController::class, 'massDestroy'])->name('physical-security-devices.massDestroy');

    // Physical Links
    Route::resource('physical-links', Admin\PhysicalLinkController::class)
        ->parameters(['physical-links' => 'link']);
    Route::delete('physical-links-destroy', [Admin\PhysicalLinkController::class, 'massDestroy'])->name('physical-links.massDestroy');

    // Rétro-compat : anciennes URL /admin/links* → /admin/physical-links* (301)
    Route::permanentRedirect('links', 'admin/physical-links');
    Route::get('links/{path}', fn (string $path) => redirect('admin/physical-links/'.$path, 301))
        ->where('path', '.*');

    // WANs
    Route::resource('wans', Admin\WanController::class);
    Route::delete('wans-destroy', [Admin\WanController::class, 'massDestroy'])->name('wans.massDestroy');

    // MANs
    Route::resource('mans', Admin\ManController::class);
    Route::delete('mans-destroy', [Admin\ManController::class, 'massDestroy'])->name('mans.massDestroy');

    // LANs
    Route::resource('lans', Admin\LanController::class);
    Route::delete('lans-destroy', [Admin\LanController::class, 'massDestroy'])->name('lans.massDestroy');

    // VLANs
    Route::resource('vlans', Admin\VlanController::class);
    Route::get('vlans/clone/{id}', [Admin\VlanController::class, 'clone'])->name('vlans.clone');
    Route::delete('vlans-destroy', [Admin\VlanController::class, 'massDestroy'])->name('vlans.massDestroy');

    // Application Modules
    Route::resource('application-modules', Admin\ApplicationModuleController::class);
    Route::delete('application-modules-destroy', [Admin\ApplicationModuleController::class, 'massDestroy'])->name('application-modules.massDestroy');

    // Macro Processuses
    Route::resource('macro-processuses', Admin\MacroProcessusController::class);
    Route::delete('macro-processuses-destroy', [Admin\MacroProcessusController::class, 'massDestroy'])->name('macro-processuses.massDestroy');

    // Global Search engine !
    Route::get('global-search', [Admin\GlobalSearchController::class, 'search'])->name('globalSearch');

    // Certificate Configuration page
    Route::get('config/cert', [Admin\ConfigurationController::class, 'getCertConfig'])->name('config.cert');
    Route::put('config/cert/save', [Admin\ConfigurationController::class, 'saveCertConfig'])->name('config.cert.save');

    // CVE
    // Route::put('cve', [Admin\CVEController::class, 'show'])->name('cve.show');
    Route::post('cve/search/{cpe}', [Admin\CVEController::class, 'search'])->name('cve.search');

    // Configuration
    Route::get('config/parameters', [Admin\ConfigurationController::class, 'getParameters'])
        ->name('config.parameters');
    Route::put('config/parameters', [Admin\ConfigurationController::class, 'saveConfig']);

    // Mode corbeille (exploration et restauration des objets supprimés)
    Route::get('trash', [Admin\TrashController::class, 'index'])->name('trash.index');
    Route::post('trash/enter', [Admin\TrashController::class, 'enter'])->name('trash.enter');
    Route::post('trash/exit', [Admin\TrashController::class, 'exit'])->name('trash.exit');
    Route::post('trash/{type}/{id}/restore', [Admin\TrashController::class, 'restore'])
        ->whereNumber('id')->name('trash.restore');

    // Périmètres (cloisonnement multi-établissement)
    Route::put('config/perimeters/activation', [Admin\PerimeterController::class, 'activation'])
        ->name('perimeters.activation');
    Route::post('config/perimeters', [Admin\PerimeterController::class, 'store'])
        ->name('perimeters.store');
    Route::put('config/perimeters/{perimeter}', [Admin\PerimeterController::class, 'update'])
        ->name('perimeters.update');
    Route::delete('config/perimeters/{perimeter}', [Admin\PerimeterController::class, 'destroy'])
        ->name('perimeters.destroy');

    // Périmètre de travail actif (mémorisé en session)
    Route::post('perimeter/active', [Admin\PerimeterActiveController::class, 'update'])
        ->name('perimeter.active');

    // Views
    Route::get('report/gdpr', [Report\GDPRView::class, 'generate'])->name('report.gdpr');
    Route::get('report/ecosystem', [Report\EcosystemView::class, 'generate'])->name('report.view.ecosystem');
    Route::get('report/information_system', [Report\InformationSystemView::class, 'generate'])->name('report.view.information-system');
    Route::get('report/applications', [Report\ApplicationView::class, 'generate'])->name('report.view.applications');
    Route::get('report/application_flows', [Report\ApplicationFlowView::class, 'generate'])->name('report.view.application-flows');
    Route::get('report/logical_infrastructure', [Report\LogicalInfrastructureView::class, 'generate'])->name('report.view.logical-infrastructure');
    Route::get('report/administration', [Report\AdministrationView::class, 'generate'])->name('report.view.administration');
    Route::get('report/physical_infrastructure', [Report\PhysicalInfrastructureView::class, 'generate'])->name('report.view.physical-infrastructure');
    Route::get('report/network_infrastructure', [Report\NetworkInfrastructureView::class, 'generate'])->name('report.view.network-infrastructure');
    Route::get('report/security_zones', [Report\SecurityZoneView::class, 'generate'])->name('report.view.security-zones');

    Route::get('report/impacts', [Report\ImpactList::class, 'generateExcel'])->name('report.view.impacts');
    Route::get('report/rto', [Report\RTO::class, 'generateExcel'])->name('report.view.rto');

    // Experimental views
    Route::get('report/heatmap', [Admin\HeatmapController::class, 'index'])->name('report.view.heatmap');
    Route::get('report/heatmap2', [Admin\HeatmapController::class, 'index2'])->name('report.view.heatmap2');
    Route::get('report/heatmap/values', [Admin\HeatmapController::class, 'values']);
    Route::get('report/zones', [Report\ZoneList::class, 'generate'])->name('report.view.zones');

    // Graphs
    Route::resource('graphs', Admin\GraphController::class);
    Route::delete('graphs-destroy', [Admin\GraphController::class, 'massDestroy'])->name('graphs.massDestroy');
    Route::get('graphs/clone/{id}', [Admin\GraphController::class, 'clone'])->name('graphs.clone');
    // Graphs test
    Route::view('graph/test', 'admin.graphs.test')->name('graphs.test');

    // Cairn — routes littérales déclarées avant le futur Route::resource('cairn', ...) (CRUD),
    // pour ne pas être capturées par le segment cairn/{cairn}.
    Route::get('cairn/search', [Admin\CairnController::class, 'search'])->name('cairn.search');
    Route::post('cairn/generate', [Admin\CairnController::class, 'generate'])->name('cairn.generate');
    // TEMPORAIRE (vérification Phase 3) — à retirer quand Route::resource('cairn', ...) sera
    // ajoutée par le CRUD resourceful (prompt "sauvegarde"), qui redéfinira cairn.create.
    Route::get('cairn/create', [Admin\CairnController::class, 'create'])->name('cairn.create');

    // Explorer
    Route::get('report/explore', [Admin\ExplorerController::class, 'explore'])->name('report.explore');

    Route::get('/reports/explore/data', [Admin\ExplorerController::class, 'getGraphData'])
        ->name('reports.explore.data');
    Route::get('/reports/explore/attributes', [Admin\ExplorerController::class, 'getAttributes'])
        ->name('reports.explore.attributes');

    // Dependency
    Route::get('report/dependency', [Admin\DependencyController::class, 'index'])
        ->name('report.dependency');

    // Maturity levels
    Route::get('report/maturity1', [Admin\HomeController::class, 'maturity1'])->name('report.maturity1');
    Route::get('report/maturity2', [Admin\HomeController::class, 'maturity2'])->name('report.maturity2');
    Route::get('report/maturity3', [Admin\HomeController::class, 'maturity3'])->name('report.maturity3');

    // Reporting
    Route::put('report/cartography', [Admin\CartographyController::class, 'cartography'])->name('report.cartography');
    Route::get('report/cartography/template/default', [Admin\CartographyController::class, 'downloadDefaultTemplate'])->name('report.cartography.template.default');
    Route::get('report/cartography/template/current', [Admin\CartographyController::class, 'downloadCurrentTemplate'])->name('report.cartography.template.current');
    Route::post('report/cartography/template', [Admin\CartographyController::class, 'uploadTemplate'])->name('report.cartography.template.upload');
    Route::get('report/entities', [Report\EntityList::class, 'generateExcel'])->name('report.entities');
    Route::get('report/applicationsByBlocks', [Report\ApplicationList::class, 'generate'])->name('report.applicationsByBlocks');
    Route::get('report/directory', [Report\Directory::class, 'generateDocx'])->name('report.directory');
    Route::get('report/logicalServers', [Report\LogicalServers::class, 'generate'])->name('report.logicalServers');
    Route::get('report/backups', [Report\BackupList::class, 'generateExcel'])->name('report.backups');
    Route::get('report/securityNeeds', [Report\SecurityNeeds::class, 'generate'])->name('report.securityNeeds');
    Route::get('report/logicalServerConfigs', [Report\LogicalServerConfigs::class, 'generate'])->name('report.logicalServerConfigs');
    Route::get('report/externalAccess', [Report\ExternalAccess::class, 'generate'])->name('report.externalAccess');
    Route::get('report/physicalInventory', [Report\PhysicalInventory::class, 'generate'])->name('report.physicalInventory');
    Route::get('report/vlans', [Report\VLANList::class, 'generate'])->name('report.vlans');
    Route::get('report/workstations', [Report\WorkstationList::class, 'generate'])->name('report.workstations');
    Route::get('report/cve', [Admin\CVEController::class, 'list'])->name('report.view.cve');

    // GDPR
    Route::get('report/activityList', [Report\ActivityList::class, 'generate'])->name('report.activityList');
    Route::get('report/activityReport', [Report\ActivityReport::class, 'generateDocx'])->name('report.activityReport');

    // CPE
    Route::get('/cpe/search/vendors', [Admin\CPEController::class, 'vendors']);
    Route::get('/cpe/search/products', [Admin\CPEController::class, 'products']);
    Route::get('/cpe/search/versions', [Admin\CPEController::class, 'versions']);
    Route::get('/cpe/search/guess', [Admin\CPEController::class, 'guess']);

    // Patching
    Route::get('/patching', [Admin\PatchingController::class, 'index']);
    Route::get('/patching/index', [Admin\PatchingController::class, 'index'])->name('patching.index');
    Route::get('/patching/edit/server/{id}', [Admin\PatchingController::class, 'editServer'])->name('patching.edit.server');
    Route::put('/patching/server', [Admin\PatchingController::class, 'updateServer'])->name('patching.server');
    Route::get('/patching/edit/application/{id}', [Admin\PatchingController::class, 'editApplication'])->name('patching.edit.application');
    Route::put('/patching/application', [Admin\PatchingController::class, 'updateApplication'])->name('patching.application');
    Route::get('/patching/dashboard', [Admin\PatchingController::class, 'dashboard'])->name('patching.dashboard');

    // Auditing
    Route::get('audit/maturity', [AuditController::class, 'maturity'])->name('audit.maturity');
    Route::get('audit/changes', [AuditController::class, 'changes'])->name('audit.changes');

    // Documents
    Route::post('/documents/store', [Admin\DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/delete/{id}', [Admin\DocumentController::class, 'delete'])->name('documents.delete');
    Route::get('/documents/{id}', [Admin\DocumentController::class, 'get'])->name('documents.show');
    Route::get('/config/documents', [Admin\DocumentController::class, 'stats'])->name('config.documents');
    Route::get('/config/documents/check', [Admin\DocumentController::class, 'check'])->name('config.documents.check');

    // Audit Logs
    Route::get('audit-logs/history/{type}/{id}', [Admin\AuditLogsController::class, 'history'])->name('audit-logs.history');
    Route::resource('audit-logs', Admin\AuditLogsController::class, ['except' => ['create', 'store', 'update', 'destroy']]);

    // Monarc
    Route::get('monarc', [Admin\MonarcController::class, 'index'])->name('monarc');
    Route::post('monarc/export', [Admin\MonarcController::class, 'export'])->name('monarc.export');
    Route::post('monarc/save', [Admin\MonarcController::class, 'saveSelection'])->name('monarc.save');
    Route::post('monarc/sync', [Admin\MonarcController::class, 'sync'])->name('monarc.sync');
    Route::post('monarc/sync/reset', [Admin\MonarcController::class, 'resetSync'])->name('monarc.sync.reset');
    Route::get('monarc/anr/{anrId}/rows', [Admin\MonarcController::class, 'anrRows'])->whereNumber('anrId')->name('monarc.anr-rows');
    Route::post('monarc/test-connection', [Admin\MonarcController::class, 'testConnection'])->name('monarc.test-connection');

    // Reporting
    Route::get('doc/report', function () {
        return view('doc/report', [
            'reportTemplate' => ReportTemplateSettings::load(),
        ]);
    })->name('doc.report');
    Route::get('doc/lists', function () {
        return view('doc/lists');
    })->name('doc.lists');

    // Doc
    Route::get('doc/schema', function () {
        return view('doc/schema');
    })->name('doc.schema');

    Route::get('doc/guide', function () {
        return view('doc/guide');
    })->name('doc.guide');

    Route::get('doc/about', function () {
        return view('doc/about');
    })->name('doc.about');

    Route::get('doc/info', [Admin\InfoController::class, 'show'])->name('doc.info');

    // Import
    Route::get('config/import', [Admin\ImportController::class, 'show'])
        ->name('config.import.form');
    Route::post('config/import', [Admin\ImportController::class, 'import'])
        ->name('config.import');

    // Export
    Route::post('config/export', [Admin\ImportController::class, 'export'])
        ->name('config.export');

    // Configuration page
    Route::get('config', function () {
        return null;
        // return view('config');
    });

    // BPMN routes
    Route::get('bpmn', [BPMNController::class, 'index'])->name('bpmn.index');
    Route::get('bpmn/create', [BPMNController::class, 'create'])->name('bpmn.create');
    Route::get('bpmn/raw/{id}', [BPMNController::class, 'raw'])->name('bpmn.raw');
    Route::get('bpmn/edit/{id}', [BPMNController::class, 'edit'])->name('bpmn.edit');
    Route::get('bpmn/{id}/data', [BPMNController::class, 'data'])->name('bpmn.data');

    // API
    Route::get('bpmn/objects', [BPMNController::class, 'objects'])->name('bpmn.objects');
    Route::get('bpmn/information', [BPMNController::class, 'information'])->name('bpmn.information');
    Route::get('bpmn/actors', [BPMNController::class, 'actors'])->name('bpmn.actors');
    Route::get('bpmn/process', [BPMNController::class, 'process'])->name('bpmn.process');

    // Show and update a graph
    Route::put('bpmn/{id}', [BPMNController::class, 'update'])->name('bpmn.update');
    Route::get('bpmn/{id}', [BPMNController::class, 'show'])->name('bpmn.show');
    Route::delete('bpmn/destroy/{id}', [BPMNController::class, 'destroy'])->name('bpmn.destroy');

    // Clone a graph
    Route::get('bpmn/clone/{id}', [BPMNController::class, 'clone'])->name('bpmn.clone');

    // Queries
    Route::post('/queries/{query}/duplicate', [QueryController::class, 'duplicate'])->name('queries.duplicate');
    Route::post('/queries/{query}/run', [QueryController::class, 'run'])->name('queries.run');
    Route::post('/queries/execute', [QueryController::class, 'execute'])->name('queries.execute');
    Route::get('/queries/schema', [QueryController::class, 'schema'])->name('queries.schema');
    Route::get('/queries/schema/{model}', [QueryController::class, 'schemaModel'])->name('queries.schema.model');
    Route::resource('/queries', QueryController::class);
    Route::delete('queries-modules-destroy', [QueryController::class, 'massDestroy'])->name('queries.massDestroy');

    // Cartographers
    Route::prefix('cartographers')->name('cartographers.')->group(function () {
        Route::get('/associated-objects', [Admin\CartographerController::class, 'associatedObjects'])->name('associated-objects');
        Route::get('/', [Admin\CartographerController::class, 'index'])->name('index');
        Route::get('/create', [Admin\CartographerController::class, 'create'])->name('create');
        Route::post('/', [Admin\CartographerController::class, 'store'])->name('store');
        Route::get('/objects', [Admin\CartographerController::class, 'getObjects'])->name('objects');
        Route::delete('/{cartographer}', [Admin\CartographerController::class, 'destroy'])->name('destroy');
    });
    Route::delete('cartographers-destroy', [Admin\CartographerController::class, 'massDestroy'])->name('cartographers.massDestroy');
    Route::get('cartographer/list', [Admin\CartographerController::class, 'list'])->name('cartographer.list');

});

// Queries — signed CSV export (signature = autorisation, hors groupe admin)
Route::middleware('signed')->group(function (): void {
    Route::get('/queries/{query}/export.csv', [Controllers\API\QueryController::class, 'exportSigned'])
        ->name('queries.export.signed');
});

// Profile
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'middleware' => ['auth']], function (): void {
    Route::get('password', [Controllers\Auth\ChangePasswordController::class, 'edit'])->name('password.edit');
    Route::post('password', [Controllers\Auth\ChangePasswordController::class, 'update'])->name('password.update');
    Route::get('preferences', [Controllers\Auth\ProfileController::class, 'showProfile']);
    Route::post('preferences', [Controllers\Auth\ProfileController::class, 'saveProfile']);
});
