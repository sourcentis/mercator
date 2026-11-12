<?php

namespace App\Http\Controllers\Admin;

// GDPR
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Actor;
use App\Models\Annuaire;
use App\Models\Application;
use App\Models\ApplicationBlock;
use App\Models\ApplicationFlow;
use App\Models\ApplicationModule;
use App\Models\ApplicationService;
use App\Models\Bay;
use App\Models\Building;
use App\Models\Cartographer;
use App\Models\Certificate;
use App\Models\Cluster;
use App\Models\Container;
use App\Models\Database;
use App\Models\DataProcessing;
use App\Models\DhcpServer;
use App\Models\Dnsserver;
use App\Models\Domain;
use App\Models\Entity;
use App\Models\ExternalConnectedEntity;
use App\Models\ForestAd;
use App\Models\Gateway;
use App\Models\Information;
use App\Models\Lan;
use App\Models\LogicalServer;
use App\Models\MacroProcessus;
use App\Models\Man;
use App\Models\Network;
use App\Models\NetworkSwitch;
use App\Models\Operation;
use App\Models\Peripheral;
use App\Models\Phone;
use App\Models\PhysicalRouter;
use App\Models\PhysicalSecurityDevice;
use App\Models\PhysicalServer;
use App\Models\PhysicalSwitch;
use App\Models\Process;
use App\Models\Relation;
use App\Models\Router;
use App\Models\SecurityControl;
use App\Models\SecurityDevice;
use App\Models\Site;
use App\Models\StorageDevice;
use App\Models\Subnetwork;
use App\Models\Task;
use App\Models\Vlan;
use App\Models\Wan;
use App\Models\WifiTerminal;
use App\Models\Workstation;
use App\Models\Zone;
use App\Models\ZoneAdmin;
use App\Support\TrashMode;
use Illuminate\Contracts\Support\Renderable;

class HomeController extends Controller
{
    /**
     * Show maturity level 1.
     */
    public function maturity1(): Renderable
    {
        $view = view('admin/reports/maturity1');
        foreach ($this->computeMaturity() as $maturity => $level) {
            $view = $view->with($maturity, $level);
        }

        return $view;
    }

    /**
     * Show maturity level 2.
     */
    public function maturity2(): Renderable
    {
        $view = view('admin/reports/maturity2');
        foreach ($this->computeMaturity() as $maturity => $level) {
            $view = $view->with($maturity, $level);
        }

        return $view;
    }

    /**
     * Show maturity level 3.
     */
    public function maturity3(): Renderable
    {
        $view = view('admin/reports/maturity3');
        foreach ($this->computeMaturity() as $maturity => $level) {
            $view = $view->with($maturity, $level);
        }

        return $view;
    }

    /**
     * Show the application dashboard.
     */
    public function index(): Renderable
    {
        $view = view('home');
        foreach ($this->computeMaturity() as $maturity => $level) {
            $view = $view->with($maturity, $level);
        }

        return $view;
    }

    /**
     * Compute maturity levels.
     *
     * En mode corbeille, les compteurs ne portent que sur les objets supprimés
     * et les niveaux de conformité valent 0.
     */
    protected function computeMaturity(): array
    {
        if (! TrashMode::active()) {
            return $this->computeLevels();
        }

        $restores = array_map(fn (string $class) => TrashMode::scopeToTrashed($class), array_values(TrashMode::models()));
        try {
            $levels = $this->computeLevels();
        } finally {
            foreach ($restores as $restore) {
                $restore();
            }
        }

        $levels['maturity1'] = $levels['maturity2'] = $levels['maturity3'] = 0;

        return $levels;
    }

    protected function computeLevels(): array
    {
        $levels = [
            // GDPR
            'data_processing' => Cartographer::scopedQuery(DataProcessing::query())->count(),
            'security_controls' => Cartographer::scopedQuery(SecurityControl::query())->count(),

            // Ecosystem
            'entities' => Cartographer::scopedQuery(Entity::query())->count(),
            'entities_lvl1' => Cartographer::scopedQuery(Entity::maturityLevel1())->count(),

            'relations' => Cartographer::scopedQuery(Relation::query())->count(),
            'relations_lvl1' => Cartographer::scopedQuery(Relation::maturityLevel1())->count(),
            'relations_lvl2' => Cartographer::scopedQuery(Relation::maturityLevel2())->count(),

            // Information system
            'macroProcessuses' => Cartographer::scopedQuery(MacroProcessus::query())->count(),
            'macroProcessuses_lvl2' => Cartographer::scopedQuery(MacroProcessus::maturityLevel2())->count(),
            'macroProcessuses_lvl3' => Cartographer::scopedQuery(MacroProcessus::maturityLevel3())->count(),

            'processes' => Cartographer::scopedQuery(Process::query())->count(),
            'processes_lvl1' => Cartographer::scopedQuery(Process::maturityLevel1())->count(),
            'processes_lvl2' => Cartographer::scopedQuery(Process::maturityLevel2())->count(),

            'activities' => Cartographer::scopedQuery(Activity::query())->count(),
            'activities_lvl2' => Cartographer::scopedQuery(Activity::maturityLevel2())->count(),

            'operations' => Cartographer::scopedQuery(Operation::query())->count(),
            'operations_lvl1' => Cartographer::scopedQuery(Operation::maturityLevel1())->count(),
            'operations_lvl2' => Cartographer::scopedQuery(Operation::maturityLevel2())->count(),
            'operations_lvl3' => Cartographer::scopedQuery(Operation::maturityLevel3())->count(),

            'tasks' => Cartographer::scopedQuery(Task::query())->count(),
            'tasks_lvl3' => Cartographer::scopedQuery(Task::maturityLevel3())->count(),

            'actors' => Cartographer::scopedQuery(Actor::query())->count(),
            'actors_lvl2' => Cartographer::scopedQuery(Actor::maturityLevel2())->count(),

            'informations' => Cartographer::scopedQuery(Information::query())->count(),
            'informations_lvl1' => Cartographer::scopedQuery(Information::maturityLevel1())->count(),
            'informations_lvl2' => Cartographer::scopedQuery(Information::maturityLevel2())->count(),

            // Applications
            'applicationBlocks' => Cartographer::scopedQuery(ApplicationBlock::query())->count(),
            'applicationBlocks_lvl2' => Cartographer::scopedQuery(ApplicationBlock::maturityLevel2())->count(),

            'applications' => Cartographer::scopedQuery(Application::query())->count(),
            'applications_lvl1' => Cartographer::scopedQuery(Application::maturityLevel1())->count(),
            'applications_lvl2' => Cartographer::scopedQuery(Application::maturityLevel2())->count(),
            'applications_lvl3' => Cartographer::scopedQuery(Application::maturityLevel3())->count(),

            'applicationServices' => Cartographer::scopedQuery(ApplicationService::query())->count(),
            'applicationServices_lvl2' => Cartographer::scopedQuery(ApplicationService::maturityLevel2())->count(),

            'applicationModules' => Cartographer::scopedQuery(ApplicationModule::query())->count(),
            'applicationModules_lvl2' => Cartographer::scopedQuery(ApplicationModule::maturityLevel2())->count(),

            'databases' => Cartographer::scopedQuery(Database::query())->count(),
            'databases_lvl1' => Cartographer::scopedQuery(Database::maturityLevel1())->count(),
            'databases_lvl2' => Cartographer::scopedQuery(Database::maturityLevel2())->count(),

            'flows' => Cartographer::scopedQuery(ApplicationFlow::query())->count(),
            'flows_lvl1' => Cartographer::scopedQuery(ApplicationFlow::maturityLevel1())->count(),

            // Administration
            'zones_ad' => Cartographer::scopedQuery(ZoneAdmin::query())->count(),
            'zones_ad_lvl1' => Cartographer::scopedQuery(ZoneAdmin::maturityLevel1())->count(),

            'annuaires' => Cartographer::scopedQuery(Annuaire::query())->count(),
            'annuaires_lvl1' => Cartographer::scopedQuery(Annuaire::maturityLevel1())->count(),
            'annuaires_lvl2' => Cartographer::scopedQuery(Annuaire::maturityLevel2())->count(),

            'forests' => Cartographer::scopedQuery(ForestAd::query())->count(),
            'forests_lvl1' => Cartographer::scopedQuery(ForestAd::maturityLevel1())->count(),

            'domains' => Cartographer::scopedQuery(Domain::query())->count(),
            'domaines_lvl1' => Cartographer::scopedQuery(Domain::maturityLevel1())->count(),

            // Logique
            'networks' => Cartographer::scopedQuery(Network::query())->count(),
            'networks_lvl1' => Cartographer::scopedQuery(Network::maturityLevel1())->count(),

            'subnetworks' => Cartographer::scopedQuery(Subnetwork::query())->count(),
            'subnetworks_lvl1' => Cartographer::scopedQuery(Subnetwork::maturityLevel1())->count(),

            'gateways' => Cartographer::scopedQuery(Gateway::query())->count(),
            'gateways_lvl1' => Cartographer::scopedQuery(Gateway::maturityLevel1())->count(),

            'externalConnectedEntities' => Cartographer::scopedQuery(ExternalConnectedEntity::query())->count(),
            'externalConnectedEntities_lvl2' => Cartographer::scopedQuery(ExternalConnectedEntity::maturityLevel2())->count(),

            'switches' => Cartographer::scopedQuery(NetworkSwitch::query())->count(),
            'switches_lvl1' => Cartographer::scopedQuery(NetworkSwitch::maturityLevel1())->count(),

            'routers' => Cartographer::scopedQuery(Router::query())->count(),
            'routers_lvl1' => Cartographer::scopedQuery(Router::maturityLevel1())->count(),

            'securityDevices' => Cartographer::scopedQuery(SecurityDevice::query())->count(),
            'securityDevices_lvl1' => Cartographer::scopedQuery(SecurityDevice::maturityLevel1())->count(),

            'DHCPServers' => Cartographer::scopedQuery(DhcpServer::query())->count(),
            'DHCPServers_lvl2' => Cartographer::scopedQuery(DhcpServer::maturityLevel2())->count(),

            'DNSServers' => Cartographer::scopedQuery(Dnsserver::query())->count(),
            'DNSServers_lvl2' => Cartographer::scopedQuery(Dnsserver::maturityLevel2())->count(),

            'clusters' => Cartographer::scopedQuery(Cluster::query())->count(),
            'clusters_lvl1' => Cartographer::scopedQuery(Cluster::maturityLevel1())->count(),

            'logicalServers' => Cartographer::scopedQuery(LogicalServer::query())->count(),
            'logicalServers_lvl1' => Cartographer::scopedQuery(LogicalServer::maturityLevel1())->count(),

            'containers' => Cartographer::scopedQuery(Container::query())->count(),
            'containers_lvl1' => Cartographer::scopedQuery(Container::maturityLevel1())->count(),

            'certificates' => Cartographer::scopedQuery(Certificate::query())->count(),
            'certificates_lvl2' => Cartographer::scopedQuery(Certificate::maturityLevel2())->count(),

            // Physical
            'sites' => Cartographer::scopedQuery(Site::query())->count(),
            'sites_lvl1' => Cartographer::scopedQuery(Site::maturityLevel1())->count(),

            'buildings' => Cartographer::scopedQuery(Building::query())->count(),
            'buildings_lvl1' => Cartographer::scopedQuery(Building::maturityLevel1())->count(),

            'bays' => Cartographer::scopedQuery(Bay::query())->count(),
            'bays_lvl1' => Cartographer::scopedQuery(Bay::maturityLevel1())->count(),

            'zones' => Cartographer::scopedQuery(Zone::query())->count(),

            'physicalServers' => Cartographer::scopedQuery(PhysicalServer::query())->count(),
            'physicalServers_lvl1' => Cartographer::scopedQuery(PhysicalServer::maturityLevel1())->count(),

            'workstations' => Cartographer::scopedQuery(Workstation::query())->count(),
            'workstations_lvl1' => Cartographer::scopedQuery(Workstation::maturityLevel1())->count(),

            'storageDevices' => Cartographer::scopedQuery(StorageDevice::query())->count(),
            'storageDevices_lvl1' => Cartographer::scopedQuery(StorageDevice::maturityLevel1())->count(),

            'peripherals' => Cartographer::scopedQuery(Peripheral::query())->count(),
            'peripherals_lvl1' => Cartographer::scopedQuery(Peripheral::maturityLevel1())->count(),

            'phones' => Cartographer::scopedQuery(Phone::query())->count(),
            'phones_lvl1' => Cartographer::scopedQuery(Phone::maturityLevel1())->count(),

            'physicalSwitchs' => Cartographer::scopedQuery(PhysicalSwitch::query())->count(),
            'physicalSwitchs_lvl1' => Cartographer::scopedQuery(PhysicalSwitch::maturityLevel1())->count(),

            'physicalRouters' => Cartographer::scopedQuery(PhysicalRouter::query())->count(),
            'physicalRouters_lvl1' => Cartographer::scopedQuery(PhysicalRouter::maturityLevel1())->count(),

            'wifiTerminals' => Cartographer::scopedQuery(WifiTerminal::query())->count(),
            'wifiTerminals_lvl1' => Cartographer::scopedQuery(WifiTerminal::maturityLevel1())->count(),

            'physicalSecurityDevices' => Cartographer::scopedQuery(PhysicalSecurityDevice::query())->count(),
            'physicalSecurityDevices_lvl1' => Cartographer::scopedQuery(PhysicalSecurityDevice::maturityLevel1())->count(),

            'wans' => ($wan_count = Cartographer::scopedQuery(Wan::query())->count()),
            'wans_lvl1' => $wan_count,

            'mans' => ($man_count = Cartographer::scopedQuery(Man::query())->count()),
            'mans_lvl1' => $man_count,

            'lans' => Cartographer::scopedQuery(Lan::query())->count(),
            'lans_lvl1' => Cartographer::scopedQuery(Lan::maturityLevel1())->count(),

            'vlans' => Cartographer::scopedQuery(Vlan::query())->count(),
            'vlans_lvl1' => Cartographer::scopedQuery(Vlan::maturityLevel1())->count(),
        ];

        // Maturity Level 1
        $denominator =
            $levels['entities'] + $levels['relations'] +
            $levels['processes'] + $levels['operations'] + $levels['informations'] +
            $levels['applications'] + $levels['databases'] + $levels['flows'] +
            $levels['zones_ad'] + $levels['annuaires'] + $levels['forests'] + $levels['domains'] +
            $levels['networks'] + $levels['subnetworks'] + $levels['gateways'] + $levels['switches'] + $levels['routers'] + $levels['securityDevices'] + $levels['clusters'] + $levels['logicalServers'] + $levels['containers'] +
            $levels['sites'] + $levels['buildings'] + $levels['bays'] + $levels['physicalServers'] + $levels['physicalRouters'] + $levels['physicalSwitchs'] + $levels['physicalSecurityDevices'] +
            $levels['wans'] + $levels['mans'] + $levels['lans'] + $levels['vlans'];

        $levels['maturity1'] =
            $denominator > 0 ?
            number_format(
                ($levels['entities_lvl1'] + $levels['relations_lvl1'] +
            $levels['processes_lvl1'] + $levels['operations_lvl1'] + $levels['informations_lvl1'] +
            $levels['applications_lvl1'] + $levels['databases_lvl1'] + $levels['flows_lvl1'] +
            $levels['zones_ad_lvl1'] + $levels['annuaires_lvl1'] + $levels['forests_lvl1'] + $levels['domaines_lvl1'] +
            $levels['networks_lvl1'] + $levels['subnetworks_lvl1'] + $levels['gateways_lvl1'] + $levels['switches_lvl1'] + $levels['routers_lvl1'] + $levels['securityDevices_lvl1'] + $levels['clusters_lvl1'] + $levels['logicalServers_lvl1'] + $levels['containers_lvl1'] +
            $levels['sites_lvl1'] + $levels['buildings_lvl1'] + $levels['bays_lvl1'] + $levels['physicalServers_lvl1'] + $levels['physicalRouters_lvl1'] + $levels['physicalSwitchs_lvl1'] + $levels['physicalSecurityDevices_lvl1'] +
            $levels['wans_lvl1'] + $levels['mans_lvl1'] + $levels['lans_lvl1'] + $levels['vlans_lvl1']) * 100 / $denominator,
                0
            ) : 0;

        // Maturity Level 2
        $denominator =
            $levels['entities'] + $levels['relations'] +
            $levels['macroProcessuses'] + $levels['processes'] + $levels['activities'] + $levels['operations'] + $levels['actors'] + $levels['informations'] +
            $levels['applicationBlocks'] + $levels['applications'] + $levels['applicationServices'] + $levels['applicationModules'] + $levels['databases'] + $levels['flows'] +
            $levels['zones_ad'] + $levels['annuaires'] + $levels['forests'] + $levels['domains'] +
            $levels['networks'] + $levels['subnetworks'] + $levels['gateways'] + $levels['externalConnectedEntities'] + $levels['switches'] + $levels['routers'] + $levels['securityDevices'] + $levels['DHCPServers'] + $levels['DNSServers'] + $levels['clusters'] + $levels['logicalServers'] + $levels['certificates'] + $levels['containers'] +
            $levels['sites'] + $levels['buildings'] + $levels['bays'] + $levels['physicalServers'] + $levels['physicalRouters'] + $levels['physicalSwitchs'] + $levels['physicalSecurityDevices'] +
            $levels['wans'] + $levels['mans'] + $levels['lans'] + $levels['vlans'];

        $levels['maturity2'] =
            $denominator > 0 ?
            number_format(
                ($levels['entities_lvl1'] + $levels['relations_lvl2'] +
            $levels['macroProcessuses_lvl2'] + $levels['processes_lvl2'] + $levels['activities_lvl2'] + $levels['operations_lvl2'] + $levels['actors_lvl2'] + $levels['informations_lvl2'] +
            $levels['applicationBlocks_lvl2'] + $levels['applications_lvl2'] + $levels['applicationServices_lvl2'] + $levels['applicationModules_lvl2'] + $levels['databases_lvl2'] + $levels['flows_lvl1'] +
            $levels['zones_ad_lvl1'] + $levels['annuaires_lvl2'] + $levels['forests_lvl1'] + $levels['domaines_lvl1'] +
            $levels['networks_lvl1'] + $levels['subnetworks_lvl1'] + $levels['gateways_lvl1'] + $levels['externalConnectedEntities_lvl2'] + $levels['switches_lvl1'] + $levels['routers_lvl1'] + $levels['securityDevices_lvl1'] + $levels['DHCPServers_lvl2'] + $levels['DNSServers_lvl2'] + $levels['clusters_lvl1'] + $levels['logicalServers_lvl1'] + $levels['containers_lvl1'] + $levels['certificates_lvl2'] +
            $levels['sites_lvl1'] + $levels['buildings_lvl1'] + $levels['bays_lvl1'] + $levels['physicalServers_lvl1'] + $levels['physicalRouters_lvl1'] + $levels['physicalSwitchs_lvl1'] + $levels['physicalSecurityDevices_lvl1'] +
            $levels['wans_lvl1'] + $levels['mans_lvl1'] + $levels['lans_lvl1'] + $levels['vlans_lvl1']) * 100 / $denominator,
                0
            ) : 0;

        // Maturity Level 3
        $denominator =
            $levels['entities'] + $levels['relations'] +
            $levels['macroProcessuses'] + $levels['processes'] + $levels['activities'] + $levels['tasks'] + $levels['operations'] + $levels['actors'] + $levels['informations'] +
            $levels['applicationBlocks'] + $levels['applications'] + $levels['applicationServices'] + $levels['applicationModules'] + $levels['databases'] + $levels['flows'] +
            $levels['zones_ad'] + $levels['annuaires'] + $levels['forests'] + $levels['domains'] +
            $levels['networks'] + $levels['subnetworks'] + $levels['gateways'] + $levels['externalConnectedEntities'] + $levels['switches'] + $levels['routers'] + $levels['securityDevices'] + $levels['DHCPServers'] + $levels['DNSServers'] + $levels['clusters'] + $levels['logicalServers'] + $levels['containers'] + $levels['certificates'] +
            $levels['sites'] + $levels['buildings'] + $levels['bays'] + $levels['physicalServers'] + $levels['physicalRouters'] + $levels['physicalSwitchs'] + $levels['physicalSecurityDevices']
            + $levels['wans'] + $levels['mans'] + $levels['lans'] + $levels['vlans'];

        $levels['maturity3'] =
            $denominator > 0 ?
            number_format(
                ($levels['entities_lvl1'] + $levels['relations_lvl2'] +
            $levels['macroProcessuses_lvl3'] + $levels['processes_lvl2'] + $levels['activities_lvl2'] + $levels['tasks_lvl3'] + $levels['operations_lvl2'] + $levels['actors_lvl2'] + $levels['informations_lvl2'] +
            $levels['applicationBlocks_lvl2'] + $levels['applications_lvl3'] + $levels['applicationServices_lvl2'] + $levels['applicationModules_lvl2'] + $levels['databases_lvl2'] + $levels['flows_lvl1'] +
            $levels['zones_ad_lvl1'] + $levels['annuaires_lvl2'] + $levels['forests_lvl1'] + $levels['domaines_lvl1'] +
            $levels['networks_lvl1'] + $levels['subnetworks_lvl1'] + $levels['gateways_lvl1'] + $levels['externalConnectedEntities_lvl2'] + $levels['switches_lvl1'] + $levels['routers_lvl1'] + $levels['securityDevices_lvl1'] + $levels['DHCPServers_lvl2'] + $levels['DNSServers_lvl2'] + $levels['clusters_lvl1'] + $levels['logicalServers_lvl1'] + $levels['containers_lvl1'] + $levels['certificates_lvl2'] +
            $levels['sites_lvl1'] + $levels['buildings_lvl1'] + $levels['bays_lvl1'] + $levels['physicalServers_lvl1'] + $levels['physicalRouters_lvl1'] + $levels['physicalSwitchs_lvl1'] + $levels['physicalSecurityDevices_lvl1'] + $levels['wans_lvl1'] + $levels['mans_lvl1'] + $levels['lans_lvl1'] + $levels['vlans_lvl1']) * 100 / $denominator,
                0
            ) : 0;

        return $levels;
    }
}
