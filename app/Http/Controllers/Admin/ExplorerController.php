<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Actor;
use App\Models\AdminUser;
use App\Models\Annuaire;
use App\Models\Application;
use App\Models\ApplicationBlock;
use App\Models\ApplicationFlow;
use App\Models\ApplicationModule;
use App\Models\ApplicationService;
use App\Models\Backup;
use App\Models\Bay;
use App\Models\Building;
use App\Models\Cartographer;
use App\Models\Certificate;
use App\Models\Cluster;
use App\Models\Container;
use App\Models\Database;
use App\Models\Domain;
use App\Models\Entity;
use App\Models\ExternalConnectedEntity;
use App\Models\ForestAd;
use App\Models\Gateway;
use App\Models\Information;
use App\Models\Lan;
use App\Models\LogicalFlow;
use App\Models\LogicalServer;
use App\Models\MacroProcessus;
use App\Models\Man;
use App\Models\Network;
use App\Models\NetworkSwitch;
use App\Models\Operation;
use App\Models\Peripheral;
use App\Models\Phone;
use App\Models\PhysicalLink;
use App\Models\PhysicalRouter;
use App\Models\PhysicalSecurityDevice;
use App\Models\PhysicalServer;
use App\Models\PhysicalSwitch;
use App\Models\Process;
use App\Models\Relation;
use App\Models\Router;
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
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExplorerController extends Controller
{
    private array $nodes = [];

    private array $edges = [];

    // When set, addNode() writes directly to output instead of buffering in $nodes
    private ?\Closure $nodeWriter = null;

    // Shared objects
    private Collection $subnetworks;

    private Collection $logicalServers;

    private Collection $workstations;

    private Collection $peripherals;

    public function explore(Request $request)
    {
        abort_if(Gate::denies('explore_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Vue optimisée - pas de données, chargement AJAX
        return view('admin.reports.explore');
    }

    /**
     * API endpoint — streams graph data as JSON without accumulating nodes in memory.
     *
     * Nodes are written to the output buffer one by one via $nodeWriter; edges are
     * still buffered (they are lightweight) so they can be appended after the nodes
     * array. getData() is unaffected: it leaves $nodeWriter null and keeps the old
     * array-accumulation path, as GraphController / BPMNController rely on it.
     */
    public function getGraphData(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('explore_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response()->stream(function () {
            $this->edges = [];
            $this->subnetworks = Cartographer::scopedQuery(Subnetwork::query())->select(['id', 'name', 'address', 'subnetwork_id', 'network_id', 'vlan_id', 'gateway_id'])->get();

            $first = true;
            $count = 0;
            $attributes = [];
            $emitted = [];
            $this->nodeWriter = function (array $node) use (&$first, &$count, &$attributes, &$emitted): void {
                if (! $first) {
                    echo ',';
                }
                $first = false;
                $emitted[$node['id']] = true;
                echo json_encode($node, JSON_UNESCAPED_UNICODE);
                // Collect unique, non-empty attribute tokens while streaming
                if (! empty($node['attributes'])) {
                    foreach (array_filter(array_map('trim', explode(' ', $node['attributes']))) as $attr) {
                        $attributes[$attr] = true;
                    }
                }
                // Flush every 200 nodes so the output buffer does not re-accumulate
                // what we are trying to avoid keeping in the $nodes array.
                if (++$count % 200 === 0) {
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
            };

            echo '{"nodes":[';

            $this->buildPhysicalView();
            $this->buildLogicalView();
            $this->buildApplicationView();
            $this->buildAdministrativeView();
            $this->buildProcessView();
            $this->buildEcosystemView();

            $this->nodeWriter = null;

            ksort($attributes);
            echo '],"edges":';
            echo json_encode($this->edgesBetweenNodes($this->edges, $emitted), JSON_UNESCAPED_UNICODE);
            echo ',"attributes":';
            echo json_encode(array_keys($attributes), JSON_UNESCAPED_UNICODE);
            echo '}';

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            $mb = round(memory_get_peak_usage(true) / 1048576, 2);
            logger("Memory peak [getGraphData]: {$mb} MB");
        }, 200, [
            'Content-Type' => 'application/json',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function getAttributes(): JsonResponse
    {
        abort_if(Gate::denies('explore_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Uniquement les attributs des objets que l'utilisateur peut voir (type + périmètre).
        $models = [
            Application::class, LogicalServer::class, Cluster::class, Entity::class,
            Building::class, PhysicalSecurityDevice::class, Relation::class,
            SecurityDevice::class, ApplicationFlow::class,
        ];
        $allAttributes = collect();
        foreach ($models as $model) {
            $values = Cartographer::scopedQueryByClass($model)
                ->whereNotNull('attributes')
                ->where('attributes', '!=', '')
                ->pluck('attributes');

            $allAttributes = $allAttributes->merge(
                $values->flatMap(fn ($value) => array_map('trim', explode(' ', $value)))->filter()
            );
        }
        $result = $allAttributes->unique()->sort()->values();

        return response()->json($result);
    }

    /**
     * Build node and edge collections representing the system graph across all views.
     *
     * @return array [nodes, edges]
     */
    public function getData(): array
    {
        $this->nodes = [];
        $this->edges = [];
        $this->subnetworks = Cartographer::scopedQuery(Subnetwork::query())->select(['id', 'name', 'address', 'subnetwork_id', 'network_id', 'vlan_id', 'gateway_id'])->get();

        $this->buildPhysicalView();
        $this->buildLogicalView();
        $this->buildApplicationView();
        $this->buildAdministrativeView();
        $this->buildProcessView();
        $this->buildEcosystemView();

        // Sort elements by name
        usort($this->nodes, fn ($a, $b) => strcmp($a['label'], $b['label']));

        return [$this->nodes, $this->edgesBetweenNodes($this->edges, array_flip(array_column($this->nodes, 'id')))];
    }

    /**
     * Ne garde que les arêtes dont les deux extrémités sont des nœuds effectivement présents.
     * Les nœuds sont filtrés par droit d'accès (type + périmètre), mais les liaisons viennent de
     * tables de jointure brutes : sans ce filtre, une arête pointerait vers un objet caché et
     * révélerait son existence et son identifiant.
     *
     * @param  array<int, array<string, mixed>>  $edges
     * @param  array<string, mixed>  $nodeIds  identifiants de nœuds en clés
     * @return array<int, array<string, mixed>>
     */
    private function edgesBetweenNodes(array $edges, array $nodeIds): array
    {
        return array_values(array_filter(
            $edges,
            fn (array $edge) => $edge['from'] !== null && $edge['to'] !== null
                && isset($nodeIds[$edge['from']], $nodeIds[$edge['to']])
        ));
    }

    /**
     * Build Physical view (view 6) - Infrastructure physique
     */
    private function buildPhysicalView(): void
    {
        $this->buildSites();
        $this->buildBuildings();
        $this->buildBays();
        $this->buildSecurityZones();
        $this->buildPhysicalServers();
        $this->buildPhones();
        $this->buildStorageDevices();
        $this->buildWorkstations();
        $this->buildPeripherals();
        $this->buildPhysicalSwitches();
        $this->buildPhysicalRouters();
        $this->buildPhysicalSecurityDevices();
        $this->buildWifiTerminals();
        $this->buildWANs();
        $this->buildMAN();
        $this->buildLAN();
        $this->buildVLAN();
        $this->buildPhysicalLinks();
    }

    private function buildSites(): void
    {
        $sites = Cartographer::scopedQuery(Site::query())
            ->select('id', 'name', 'icon_id')
            ->get();

        foreach ($sites as $site) {
            $this->addNode(
                6,
                $this->formatId(Site::$prefix, $site->id),
                $site->name,
                $this->getIcon($site->icon_id, '/images/site.png'),
                'sites',
                600
            );
        }
    }

    private function buildBuildings(): void
    {
        $buildings = Cartographer::scopedQuery(Building::query())
            ->select('id', 'name', 'building_id', 'site_id', 'icon_id', 'attributes')
            ->get();

        foreach ($buildings as $building) {
            $this->addNode(
                6,
                $this->formatId(Building::$prefix, $building->id),
                $building->name,
                $this->getIcon($building->icon_id, '/images/building.png'),
                'buildings',
                605,
                null,
                $building->attributes
            );

            if ($building->building_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Building::$prefix, $building->id),
                    $this->formatId(Building::$prefix, $building->building_id)
                );
            } elseif ($building->site_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Building::$prefix, $building->id),
                    $this->formatId(Site::$prefix, $building->site_id)
                );
            }
        }
    }

    private function buildBays(): void
    {
        $bays = Cartographer::scopedQuery(Bay::query())
            ->select('id', 'name', 'building_id', 'site_id')
            ->get();

        foreach ($bays as $bay) {
            $this->addNode(6,
                $this->formatId(Bay::$prefix, $bay->id),
                $bay->name,
                '/images/bay.png',
                'bays',
                610);

            if ($bay->building_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Bay::$prefix, $bay->id),
                    $this->formatId(Building::$prefix, $bay->building_id)
                );
            } elseif ($bay->site_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Bay::$prefix, $bay->id),
                    $this->formatId(Site::$prefix, $bay->site_id)
                );
            }
        }
    }

    private function buildSecurityZones(): void
    {
        $zones = Cartographer::scopedQuery(Zone::query())
            ->select('id', 'name', 'attributes')
            ->get();

        foreach ($zones as $zone) {
            $this->addNode(
                6,
                $this->formatId(Zone::$prefix, $zone->id),
                $zone->name,
                Zone::$icon,
                'zones',
                613,
                null,
                $zone->attributes
            );
        }

        // Zone-Zone (parent/child)
        $links = DB::table('zone_zone as zz')
            ->join('zones as parent', 'parent.id', '=', 'zz.zone_id')
            ->join('zones as child', 'child.id', '=', 'zz.related_zone_id')
            ->whereNull('parent.deleted_at')
            ->whereNull('child.deleted_at')
            ->select('zz.zone_id', 'zz.related_zone_id')
            ->get();

        foreach ($links as $link) {
            $this->addFluxEdge(null, false,
                $this->formatId(Zone::$prefix, $link->zone_id),
                $this->formatId(Zone::$prefix, $link->related_zone_id)
            );
        }

        $this->linkJoinTable('building_zone',
            Zone::$prefix, Building::$prefix,
            'zone_id', 'building_id');

        $this->linkJoinTable('admin_user_zone',
            Zone::$prefix, AdminUser::$prefix,
            'zone_id', 'admin_user_id');
    }

    private function buildPhysicalServers(): void
    {
        $servers = Cartographer::scopedQuery(PhysicalServer::query())
            ->select('id', 'name', 'icon_id', 'bay_id', 'building_id', 'site_id')
            ->get();

        foreach ($servers as $server) {
            $this->addNode(
                6,
                $this->formatId(PhysicalServer::$prefix, $server->id),
                $server->name,
                $this->getIcon($server->icon_id, '/images/server.png'),
                'physical-servers',
                615
            );

            $this->linkToLocationOrSite(
                $this->formatId(PhysicalServer::$prefix, $server->id),
                $server->site_id,
                $server->building_id,
                $server->bay_id
            );
        }
    }

    private function buildWorkstations(): void
    {
        $this->workstations = Cartographer::scopedQuery(Workstation::query())
            ->select('id', 'name', 'icon_id', 'address_ip', 'building_id', 'site_id')
            ->get();

        foreach ($this->workstations as $workstation) {
            $this->addNode(
                6,
                $this->formatId(Workstation::$prefix, $workstation->id),
                $workstation->name,
                $this->getIcon($workstation->icon_id, '/images/workstation.png'),
                'workstations',
                620,
                $workstation->address_ip
            );

            $this->linkToLocationOrSite(
                $this->formatId(Workstation::$prefix, $workstation->id),
                $workstation->site_id,
                $workstation->building_id
            );

            foreach (explode(',', $workstation->address_ip ?? '') as $ip) {
                $this->linkDeviceToSubnetworks(
                    $ip,
                    $this->formatId(Workstation::$prefix, $workstation->id));
            }
        }

        $this->linkJoinTable('application_workstation',
            Application::$prefix, Workstation::$prefix,
            'application_id', 'workstation_id');
    }

    private function buildPhones(): void
    {
        $phones = Cartographer::scopedQuery(Phone::query())
            ->select('id', 'name', 'address_ip', 'building_id', 'site_id')
            ->get();

        foreach ($phones as $phone) {
            $this->addNode(
                6,
                $this->formatId(Phone::$prefix, $phone->id),
                $phone->name,
                $this->getIcon(null, '/images/phone.png'),
                'phones',
                625,
                $phone->address_ip
            );

            $this->linkToLocationOrSite(
                $this->formatId(Phone::$prefix, $phone->id),
                $phone->site_id,
                $phone->building_id
            );

            $this->linkDeviceToSubnetworks($phone->address_ip,
                $this->formatId(Phone::$prefix, $phone->id));

        }
    }

    private function buildPeripherals(): void
    {
        $this->peripherals = Cartographer::scopedQuery(Peripheral::query())
            ->select('id', 'name', 'icon_id', 'address_ip', 'bay_id', 'site_id', 'building_id', 'provider_id')
            ->get();

        foreach ($this->peripherals as $peripheral) {
            $this->addNode(
                6,
                $this->formatId(Peripheral::$prefix, $peripheral->id),
                $peripheral->name,
                $this->getIcon($peripheral->icon_id, '/images/peripheral.png'),
                'peripherals',
                630,
                $peripheral->address_ip
            );
            if ($peripheral->bay_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Peripheral::$prefix, $peripheral->id),
                    $this->formatId(Bay::$prefix, $peripheral->bay_id));
            } elseif ($peripheral->building_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Peripheral::$prefix, $peripheral->id),
                    $this->formatId(Building::$prefix, $peripheral->building_id));
            } elseif ($peripheral->site_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Peripheral::$prefix, $peripheral->id),
                    $this->formatId(Site::$prefix, $peripheral->site_id));
            }
            if ($peripheral->provider_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Peripheral::$prefix, $peripheral->id),
                    $this->formatId(Entity::$prefix, $peripheral->provider_id));
            }

            foreach (explode(',', $peripheral->address_ip ?? '') as $address) {
                $this->linkDeviceToSubnetworks(
                    $address,
                    $this->formatId(Peripheral::$prefix, $peripheral->id));

            }
        }

        // application_peripheral
        $joins = DB::table('application_peripheral')
            ->select('application_id', 'peripheral_id')
            ->get();

        foreach ($joins as $join) {
            $this->addLinkEdge(
                $this->formatId(Application::$prefix, $join->application_id),
                $this->formatId(Peripheral::$prefix, $join->peripheral_id));
        }

    }

    private function buildStorageDevices(): void
    {
        // Storage devices
        $storageDevices = Cartographer::scopedQuery(StorageDevice::query())
            ->select('id', 'name', 'icon_id', 'bay_id', 'building_id', 'site_id', 'address_ip')
            ->get();

        foreach ($storageDevices as $storageDevice) {
            $this->addNode(
                6,
                $this->formatId(StorageDevice::$prefix, $storageDevice->id),
                $storageDevice->name,
                $this->getIcon($storageDevice->icon_id, '/images/storagedev.png'),
                'storage-devices',
                635,
                $storageDevice->address_ip);

            $this->linkToLocationOrSite(
                $this->formatId(StorageDevice::$prefix, $storageDevice->id),
                $storageDevice->site_id,
                $storageDevice->building_id,
                $storageDevice->bay_id
            );

            foreach (explode(',', $storageDevice->address_ip ?? '') as $address) {
                $this->linkDeviceToSubnetworks(
                    $address,
                    $this->formatId(StorageDevice::$prefix, $storageDevice->id));
            }
        }
    }

    private function buildPhysicalSwitches(): void
    {
        $switches = Cartographer::scopedQuery(PhysicalSwitch::query())
            ->select('id', 'name', 'icon_id', 'bay_id', 'building_id', 'site_id')
            ->get();

        foreach ($switches as $switch) {
            $this->addNode(
                6,
                $this->formatId(PhysicalSwitch::$prefix, $switch->id),
                $switch->name,
                $this->getIcon($switch->icon_id, '/images/switch.png'),
                'physical-switches',
                640
            );

            $this->linkToLocationOrSite(
                $this->formatId(PhysicalSwitch::$prefix, $switch->id),
                $switch->site_id,
                $switch->building_id,
                $switch->bay_id
            );
        }

        $this->linkJoinTable('network_switch_physical_switch', NetworkSwitch::$prefix, PhysicalSwitch::$prefix, 'network_switch_id', 'physical_switch_id');
    }

    private function buildPhysicalRouters(): void
    {
        $routers = Cartographer::scopedQuery(PhysicalRouter::query())
            ->select('id', 'name', 'bay_id', 'building_id', 'site_id')
            ->get();

        foreach ($routers as $router) {
            $this->addNode(
                6,
                $this->formatId(PhysicalRouter::$prefix, $router->id),
                $router->name,
                '/images/router.png',
                'physical-routers',
                650
            );

            $this->linkToLocationOrSite(
                $this->formatId(PhysicalRouter::$prefix, $router->id),
                $router->site_id,
                $router->building_id,
                $router->bay_id
            );
        }
    }

    private function buildWifiTerminals(): void
    {
        $wifiTerminals = Cartographer::scopedQuery(WifiTerminal::query())
            ->select('id', 'name', 'address_ip', 'site_id', 'building_id')
            ->get();

        foreach ($wifiTerminals as $wifiTerminal) {
            $this->addNode(
                6,
                $this->formatId(WifiTerminal::$prefix, $wifiTerminal->id),
                $wifiTerminal->name, '/images/wifi.png',
                'wifi-terminals',
                655,
                $wifiTerminal->address_ip);

            $this->linkToLocationOrSite(
                $this->formatId(WifiTerminal::$prefix, $wifiTerminal->id),
                $wifiTerminal->site_id,
                $wifiTerminal->building_id
            );

            foreach (explode(',', $wifiTerminal->address_ip ?? '') as $address) {
                $this->linkDeviceToSubnetworks(
                    $address,
                    $this->formatId(WifiTerminal::$prefix, $wifiTerminal->id));
            }
        }
    }

    private function buildPhysicalSecurityDevices(): void
    {
        $devices = Cartographer::scopedQuery(PhysicalSecurityDevice::query())
            ->select('id', 'name', 'icon_id', 'address_ip', 'bay_id', 'site_id', 'building_id', 'attributes')
            ->get();

        foreach ($devices as $device) {
            $this->addNode(
                6,
                $this->formatId(PhysicalSecurityDevice::$prefix, $device->id),
                $device->name,
                $this->getIcon($device->icon_id, '/images/security.png'),
                'physical-security-devices',
                660,
                $device->address_ip,
                $device->attributes
            );

            $this->linkToLocationOrSite(
                $this->formatId(PhysicalSecurityDevice::$prefix, $device->id),
                $device->site_id,
                $device->building_id,
                $device->bay_id
            );

            $this->linkDeviceToSubnetworks(
                $device->address_ip,
                $this->formatId(PhysicalSecurityDevice::$prefix, $device->id));
        }
    }

    private function buildWANs(): void
    {
        $wans = Cartographer::scopedQuery(Wan::query())
            ->select('id', 'name')
            ->get();

        foreach ($wans as $wan) {
            $this->addNode(
                6,
                $this->formatId(Wan::$prefix, $wan->id),
                $wan->name,
                '/images/vlan.png',
                'wans',
                670
            );
        }

    }

    private function buildMAN(): void
    {
        $mans = Cartographer::scopedQuery(Man::query())
            ->select('id', 'name', 'parent_man_id')
            ->get();

        $wanLinksByMan = DB::table('man_wan')
            ->select('man_id', 'wan_id')
            ->get()
            ->groupBy('man_id');

        foreach ($mans as $man) {
            $this->addNode(
                6,
                $this->formatId(Man::$prefix, $man->id),
                $man->name,
                '/images/vlan.png',
                'mans',
                680
            );

            if ($man->parent_man_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Man::$prefix, $man->id),
                    $this->formatId(Man::$prefix, $man->parent_man_id));
            } else {
                foreach (($wanLinksByMan->get($man->id) ?? collect()) as $wan) {
                    $this->addLinkEdge(
                        $this->formatId(Man::$prefix, $man->id),
                        $this->formatId(Wan::$prefix, $wan->wan_id));
                }
            }

        }

        $this->linkJoinTable('lan_man', Lan::$prefix, Man::$prefix, 'lan_id', 'man_id');
    }

    private function buildLAN(): void
    {
        $lans = Cartographer::scopedQuery(Lan::query())
            ->select('id', 'name')
            ->get();

        foreach ($lans as $lan) {
            $this->addNode(
                6,
                $this->formatId(Lan::$prefix, $lan->id),
                $lan->name,
                '/images/vlan.png',
                'lans',
                690
            );
        }

        $this->linkJoinTable('lan_wan',
            Lan::$prefix, Wan::$prefix,
            'lan_id', 'wan_id');
    }

    private function buildVLAN(): void
    {
        $vlans = Cartographer::scopedQuery(Vlan::query())
            ->select('id', 'name')
            ->get();

        foreach ($vlans as $vlan) {
            $this->addNode(
                5,
                $this->formatId(Vlan::$prefix, $vlan->id),
                $vlan->name,
                '/images/vlan.png',
                'vlans',
                520
            );
        }
    }

    private function buildPhysicalLinks(): void
    {
        $links = Cartographer::scopedQuery(PhysicalLink::query())->get();

        foreach ($links as $link) {
            $src = $link->sourceId();
            $dest = $link->destinationId();
            if ($src !== null && $dest !== null) {
                $this->addPhysicalLinkEdge(
                    $src,
                    $dest,
                    $link->color,
                    $link->type
                );
            }
        }
    }

    /**
     * Build Logical view (view 5) - Infrastructure logique
     */
    private function buildLogicalView(): void
    {
        $this->buildNetworks();
        $this->buildSubnetworks();
        $this->buildNetworkSwitches();
        $this->buildGateways();
        $this->buildExternalConnectedEntities();
        $this->buildLogicalServers();
        $this->buildBackups();
        $this->buildLogicalSecurityDevices();
        $this->buildRouters();
        $this->buildCertificates();
        $this->buildLogicalFlows();
        $this->buildContainers();
        $this->buildClusters();
    }

    private function buildNetworks(): void
    {
        $networks = Cartographer::scopedQuery(Network::query())
            ->select('id', 'name')
            ->get();

        foreach ($networks as $network) {
            $this->addNode(
                5,
                $this->formatId(Network::$prefix, $network->id),
                $network->name,
                '/images/cloud.png',
                'networks', 500);
        }
    }

    private function buildSubnetworks(): void
    {
        // Sort once before iterating so linkDeviceToSubnetworks() matches the most specific subnet first
        $this->subnetworks = $this->subnetworks->sortByDesc(
            fn ($subnet) => ExplorerController::getMaskLength($subnet->address)
        );

        foreach ($this->subnetworks as $subnetwork) {
            $this->addNode(
                5,
                $this->formatId(Subnetwork::$prefix, $subnetwork->id),
                $subnetwork->name,
                '/images/network.png',
                'subnetworks', 510,
                $subnetwork->address
            );

            if ($subnetwork->subnetwork_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Subnetwork::$prefix, $subnetwork->id),
                    $this->formatId(Subnetwork::$prefix, $subnetwork->subnetwork_id));
            } elseif ($subnetwork->network_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Subnetwork::$prefix, $subnetwork->id),
                    $this->formatId(Network::$prefix, $subnetwork->network_id));
            }

            if ($subnetwork->vlan_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Subnetwork::$prefix, $subnetwork->id),
                    $this->formatId(Vlan::$prefix, $subnetwork->vlan_id)
                );
            }

            if ($subnetwork->gateway_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Subnetwork::$prefix, $subnetwork->id),
                    $this->formatId(Gateway::$prefix, $subnetwork->gateway_id)
                );
            }
        }
    }

    private function buildNetworkSwitches(): void
    {
        $switches = Cartographer::scopedQuery(NetworkSwitch::query())
            ->select('id', 'name', 'ip')
            ->get();

        foreach ($switches as $switch) {
            $this->addNode(
                5,
                $this->formatId(NetworkSwitch::$prefix, $switch->id),
                $switch->name,
                '/images/switch.png',
                'network-switches', 520,
                $switch->ip
            );

            if ($switch->ip != null) {
                $this->linkDeviceToSubnetworks(
                    $switch->ip,
                    $this->formatId(NetworkSwitch::$prefix, $switch->id));
            }

        }

        $this->linkJoinTable('network_switch_vlan',
            NetworkSwitch::$prefix, Vlan::$prefix,
            'network_switch_id', 'vlan_id');
    }

    private function buildGateways(): void
    {
        $gateways = Cartographer::scopedQuery(Gateway::query())
            ->select('id', 'name', 'ip')
            ->get();

        foreach ($gateways as $gateway) {
            $this->addNode(
                5,
                $this->formatId(Gateway::$prefix, $gateway->id),
                $gateway->name,
                '/images/gateway.png',
                'gateways', 530,
                $gateway->ip
            );
        }
    }

    private function buildExternalConnectedEntities(): void
    {
        $entities = Cartographer::scopedQuery(ExternalConnectedEntity::query())
            ->select('id', 'name', 'network_id', 'entity_id')
            ->get();

        // Charge tous les pivots en une seule requête, groupés par entity ID → élimine le N+1
        $subnetworksByEntity = DB::table('external_connected_entity_subnetwork')
            ->select('external_connected_entity_id', 'subnetwork_id')
            ->get()
            ->groupBy('external_connected_entity_id');

        foreach ($entities as $entity) {
            $nodeId = $this->formatId(ExternalConnectedEntity::$prefix, $entity->id);

            $this->addNode(5, $nodeId, $entity->name, '/images/entity.png', 'external-connected-entities', 540);

            if ($entity->network_id !== null) {
                $this->addLinkEdge($nodeId, $this->formatId(Network::$prefix, $entity->network_id));
            } else {
                foreach ($subnetworksByEntity->get($entity->id, collect()) as $pivot) {
                    $this->addLinkEdge($nodeId, $this->formatId(Subnetwork::$prefix, $pivot->subnetwork_id));
                }
            }

            if ($entity->entity_id !== null) {
                $this->addLinkEdge($nodeId, $this->formatId(Entity::$prefix, $entity->entity_id));
            }
        }
    }

    private function buildContainers(): void
    {
        $containers = Cartographer::scopedQuery(Container::query())
            ->select('id', 'name', 'icon_id')
            ->get();

        foreach ($containers as $container) {
            $this->addNode(
                5,
                $this->formatId(Container::$prefix, $container->id),
                $container->name,
                $this->getIcon($container->icon_id, '/images/container.png'),
                'containers', 550
            );
        }

        // Container - Logical Servers
        $joins = DB::table('container_logical_server')
            ->select('container_id', 'logical_server_id')
            ->get();
        foreach ($joins as $join) {
            $this->addLinkEdge(
                $this->formatId(Container::$prefix, $join->container_id),
                $this->formatId(LogicalServer::$prefix, $join->logical_server_id));
        }

        // Container - Applications
        $joins = DB::table('application_container')
            ->select('container_id', 'application_id')
            ->get();
        foreach ($joins as $join) {
            $this->addLinkEdge(
                $this->formatId(Container::$prefix, $join->container_id),
                $this->formatId(Application::$prefix, $join->application_id));
        }

        // Container - Databases
        $joins = DB::table('container_database')
            ->select('container_id', 'database_id')
            ->get();
        foreach ($joins as $join) {
            $this->addLinkEdge(
                $this->formatId(Container::$prefix, $join->container_id),
                $this->formatId(Database::$prefix, $join->database_id));
        }
    }

    private function buildClusters(): void
    {
        // Clusters
        $clusters = Cartographer::scopedQuery(Cluster::query())
            ->select('id', 'name', 'icon_id', 'address_ip', 'attributes')
            ->get();
        foreach ($clusters as $cluster) {
            $this->addNode(
                5,
                $this->formatId(Cluster::$prefix, $cluster->id),
                $cluster->name,
                $this->getIcon($cluster->icon_id, '/images/cluster.png'),
                'clusters', 580,
                $cluster->address_ip,
                $cluster->attributes
            );
        }

        // Cluster - Logical Servers
        $joins = DB::table('cluster_logical_server')->select('cluster_id', 'logical_server_id')->get();
        foreach ($joins as $join) {
            $this->addLinkEdge(
                $this->formatId(Cluster::$prefix, $join->cluster_id),
                $this->formatId(LogicalServer::$prefix, $join->logical_server_id));
        }

        // Cluster - Physical Servers
        $joins = DB::table('cluster_physical_server')->select('cluster_id', 'physical_server_id')->get();
        foreach ($joins as $join) {
            $this->addLinkEdge(
                $this->formatId(Cluster::$prefix, $join->cluster_id),
                $this->formatId(PhysicalServer::$prefix, $join->physical_server_id));
        }

        // Cluster - Routers
        $joins = DB::table('cluster_router')->select('cluster_id', 'router_id')->get();
        foreach ($joins as $join) {
            $this->addLinkEdge(
                $this->formatId(Cluster::$prefix, $join->cluster_id),
                $this->formatId(Router::$prefix, $join->router_id));
        }

    }

    private function buildLogicalServers(): void
    {
        $this->logicalServers = Cartographer::scopedQuery(LogicalServer::query())
            ->select('id', 'name', 'icon_id', 'address_ip', 'domain_id', 'attributes')
            ->get();

        foreach ($this->logicalServers as $server) {
            $this->addNode(
                5,
                $this->formatId(LogicalServer::$prefix, $server->id),
                $server->name,
                $this->getIcon($server->icon_id, '/images/lserver.png'),
                'logical-servers', 560,
                $server->address_ip,
                $server->attributes
            );

            foreach (explode(',', $server->address_ip ?? '') as $address) {
                $this->linkDeviceToSubnetworks(
                    $address,
                    $this->formatId(LogicalServer::$prefix, $server->id));
            }

            if ($server->domain_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(LogicalServer::$prefix, $server->id),
                    $this->formatId(Domain::$prefix, $server->domain_id)
                );
            }
        }

        $this->linkJoinTable('logical_server_physical_server',
            LogicalServer::$prefix, PhysicalServer::$prefix,
            'logical_server_id', 'physical_server_id');

    }

    private function buildBackups(): void
    {
        $backups = Cartographer::scopedQuery(Backup::query())
            ->select('id', 'name', 'attributes')
            ->get();

        foreach ($backups as $backup) {
            $this->addNode(
                5,
                $this->formatId(Backup::$prefix, $backup->id),
                $backup->name,
                '/images/backup.png',
                'backups',
                555,
                null,
                $backup->attributes
            );
        }

        // Backup ↔ LogicalServer edges
        $serverLinks = DB::table('backup_logical_server')->get();
        foreach ($serverLinks as $link) {
            $this->addLinkEdge(
                $this->formatId(LogicalServer::$prefix, $link->logical_server_id),
                $this->formatId(Backup::$prefix, $link->backup_id)
            );
        }

        // Backup ↔ StorageDevice edges
        $deviceLinks = DB::table('backup_storage_device')->get();
        foreach ($deviceLinks as $link) {
            $this->addLinkEdge(
                $this->formatId(Backup::$prefix, $link->backup_id),
                $this->formatId(StorageDevice::$prefix, $link->storage_device_id)
            );
        }
    }

    private function buildLogicalSecurityDevices(): void
    {
        $securityDevices = Cartographer::scopedQuery(SecurityDevice::query())
            ->select('id', 'name', 'attributes', 'icon_id', 'address_ip')
            ->get();

        foreach ($securityDevices as $securityDevice) {
            $this->addNode(
                5,
                $this->formatId(SecurityDevice::$prefix, $securityDevice->id),
                $securityDevice->name,
                $this->getIcon($securityDevice->icon_id, '/images/security.png'),
                'security-devices', 560,
                $securityDevice->address_ip,
                $securityDevice->attributes
            );

            $this->linkDeviceToSubnetworks(
                $securityDevice->address_ip,
                $this->formatId(SecurityDevice::$prefix, $securityDevice->id));
        }

        $this->linkJoinTable('physical_security_device_security_device',
            PhysicalSecurityDevice::$prefix, SecurityDevice::$prefix,
            'physical_security_device_id', 'security_device_id');

        $this->linkJoinTable('application_security_device',
            Application::$prefix, SecurityDevice::$prefix,
            'application_id', 'security_device_id');

    }

    private function buildRouters(): void
    {
        $routers = Cartographer::scopedQuery(Router::query())
            ->select('id', 'name', 'ip_addresses')
            ->get();

        foreach ($routers as $router) {
            $this->addNode(
                5,
                $this->formatId(Router::$prefix, $router->id),
                $router->name,
                '/images/router.png',
                'routers', 560
            );

            foreach (explode(',', $router->ip_addresses ?? '') as $ip) {
                $this->linkDeviceToSubnetworks(
                    $ip,
                    $this->formatId(Router::$prefix, $router->id));
            }
        }

        $this->linkJoinTable('physical_router_router',
            PhysicalRouter::$prefix, Router::$prefix,
            'physical_router_id', 'router_id');

        $this->linkJoinTable('physical_router_vlan',
            PhysicalRouter::$prefix, Vlan::$prefix,
            'physical_router_id', 'vlan_id');

    }

    private function buildCertificates(): void
    {
        $certificates = Cartographer::scopedQuery(Certificate::query())
            ->select('id', 'name')
            ->get();

        foreach ($certificates as $certificate) {
            $this->addNode(
                5,
                $this->formatId(Certificate::$prefix, $certificate->id),
                $certificate->name,
                '/images/certificate.png',
                'certificates', 570
            );
        }

        $this->linkJoinTable('certificate_logical_server',
            Certificate::$prefix, LogicalServer::$prefix,
            'certificate_id', 'logical_server_id');

        $this->linkJoinTable('application_certificate',
            Certificate::$prefix, Application::$prefix,
            'certificate_id', 'application_id');

    }

    private function buildLogicalFlows(): void
    {
        $flows = Cartographer::scopedQuery(LogicalFlow::query())->get();

        foreach ($flows as $flow) {
            if (! empty($flow->action) && $flow->action !== 'Permit') {
                continue;
            }

            // Get sources
            $sources = [];
            if ($flow->source_ip_range !== null) {
                foreach ($this->logicalServers as $server) {
                    foreach (explode(',', $server->address_ip ?? '') as $ip) {
                        if ($flow->isSource($ip)) {
                            array_push($sources, $this->formatId('LSERVER_', $server->id));
                        }
                    }
                }
                foreach ($this->workstations as $workstation) {
                    if ($flow->isSource($workstation->address_ip)) {
                        array_push($sources, $this->formatId('WORK_', $workstation->id));
                    }
                }
                foreach ($this->peripherals as $peripheral) {
                    if ($flow->isSource($peripheral->address_ip)) {
                        array_push($sources, $this->formatId('PERIF_', $peripheral->id));
                    }
                }
                // TODO: other objects
            } elseif ($flow->sourceId() !== null) {
                array_push($sources, $flow->sourceId());
            }

            // Get destinations
            $destinations = [];
            if ($flow->dest_ip_range !== null) {
                foreach ($this->logicalServers as $server) {
                    foreach (explode(',', $server->address_ip ?? '') as $ip) {
                        if ($flow->isDestination($ip)) {
                            array_push($destinations, $this->formatId('LSERVER_', $server->id));
                        }
                    }
                }
                foreach ($this->workstations as $workstation) {
                    if ($flow->isDestination($workstation->address_ip)) {
                        array_push($destinations, $this->formatId('WORK_', $workstation->id));
                    }
                }
                foreach ($this->peripherals as $peripheral) {
                    if ($flow->isDestination($peripheral->address_ip)) {
                        array_push($destinations, $this->formatId('PERIF_', $peripheral->id));
                    }
                }
                // TODO: other objects
            } elseif ($flow->destinationId() !== null) {
                array_push($destinations, $flow->destinationId());
            }

            // Guard: skip flow if the Cartesian product would generate an excessive number of edges
            $edgeCount = count($sources) * count($destinations);
            if ($edgeCount > 1000) {
                Log::warning('ExplorerController: flow skipped — too many edges would be generated', [
                    'flow_id' => $flow->id,
                    'flow_name' => $flow->name,
                    'sources' => count($sources),
                    'destinations' => count($destinations),
                    'edge_count' => $edgeCount,
                ]);

                continue;
            }

            // Add source <-> destination flows
            foreach ($sources as $source) {
                foreach ($destinations as $destination) {
                    $this->addLogicalFluxEdge($flow->name, $source, $destination);
                }
            }
        }

    }

    /**
     * Build Application view (view 4) - Applications et services
     */
    private function buildApplicationView(): void
    {
        $this->buildApplications();
        $this->buildApplicationBlocks();
        $this->buildApplicationServices();
        $this->buildApplicationModules();
        $this->buildDatabases();
        $this->buildApplicationFlows();
    }

    private function buildApplicationFlows(): void
    {
        // Préférence utilisateur : libellé des flux applicatifs ('name' ou 'nature').
        // Repli 'nature' pour préserver le comportement courant (et couvrir CLI/tests sans utilisateur authentifié).
        $useName = (auth()->user()->flow_label ?? 'nature') === 'name';

        // Fluxes
        $flows = Cartographer::scopedQuery(ApplicationFlow::query())->get();
        foreach ($flows as $flow) {
            if ($flow->application_source_id !== null) {
                $src_id = $this->formatId(Application::$prefix, $flow->application_source_id);
            } elseif ($flow->service_source_id !== null) {
                $src_id = $this->formatId(ApplicationService::$prefix, $flow->service_source_id);
            } elseif ($flow->module_source_id !== null) {
                $src_id = $this->formatId(ApplicationModule::$prefix, $flow->module_source_id);
            } elseif ($flow->database_source_id !== null) {
                $src_id = $this->formatId(Database::$prefix, $flow->database_source_id);
            } else {
                continue;
            }

            if ($flow->application_dest_id !== null) {
                $dest_id = $this->formatId(Application::$prefix, $flow->application_dest_id);
            } elseif ($flow->service_dest_id !== null) {
                $dest_id = $this->formatId(ApplicationService::$prefix, $flow->service_dest_id);
            } elseif ($flow->module_dest_id !== null) {
                $dest_id = $this->formatId(ApplicationModule::$prefix, $flow->module_dest_id);
            } elseif ($flow->database_dest_id !== null) {
                $dest_id = $this->formatId(Database::$prefix, $flow->database_dest_id);
            } else {
                continue;
            }

            $label = $useName ? $flow->name : $flow->type;
            $this->addFluxEdge($label, $flow->bidirectional ?? false, $src_id, $dest_id);
        }
    }

    private function buildApplicationBlocks(): void
    {
        $blocks = Cartographer::scopedQuery(ApplicationBlock::query())
            ->select('id', 'name')
            ->get();

        foreach ($blocks as $block) {
            $this->addNode(
                3,
                $this->formatId(ApplicationBlock::$prefix, $block->id),
                $block->name,
                '/images/applicationblock.png',
                'application-blocks', 300
            );
        }
    }

    private function buildApplications(): void
    {
        $applications = Cartographer::scopedQuery(Application::query())
            ->select('id', 'name', 'icon_id', 'application_block_id', 'attributes', 'entity_resp_id')
            ->get();

        foreach ($applications as $app) {
            $this->addNode(
                3,
                $this->formatId(Application::$prefix, $app->id),
                $app->name,
                $this->getIcon($app->icon_id, '/images/application.png'),
                'applications', 310,
                null,
                $app->attributes
            );

            if ($app->application_block_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Application::$prefix, $app->id),
                    $this->formatId(ApplicationBlock::$prefix, $app->application_block_id)
                );

            }
            if ($app->entity_resp_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Application::$prefix, $app->id),
                    $this->formatId(Entity::$prefix, $app->entity_resp_id)
                );
            }

        }

        $this->linkJoinTable('application_application_service',
            ApplicationService::$prefix, Application::$prefix,
            'application_service_id', 'application_id');
        $this->linkJoinTable('application_logical_server',
            LogicalServer::$prefix, Application::$prefix,
            'logical_server_id', 'application_id');
        $this->linkJoinTable('application_process',
            Process::$prefix, Application::$prefix,
            'process_id', 'application_id');
    }

    private function buildApplicationServices(): void
    {
        $services = Cartographer::scopedQuery(ApplicationService::query())
            ->select('id', 'name')
            ->get();

        foreach ($services as $service) {
            $this->addNode(
                3,
                $this->formatId(ApplicationService::$prefix, $service->id),
                $service->name,
                '/images/applicationservice.png',
                'application-services', 320
            );
        }

        $this->linkJoinTable('application_module_application_service',
            ApplicationModule::$prefix, ApplicationService::$prefix,
            'application_module_id', 'application_service_id');

    }

    private function buildApplicationModules(): void
    {
        $modules = Cartographer::scopedQuery(ApplicationModule::query())
            ->select('id', 'name')
            ->get();

        foreach ($modules as $module) {
            $this->addNode(
                3,
                $this->formatId(ApplicationModule::$prefix, $module->id),
                $module->name,
                '/images/module.png',
                'application-modules', 330
            );
        }

        $this->linkJoinTable('application_module_entity',
            ApplicationModule::$prefix, Entity::$prefix,
            'application_module_id', 'entity_id');

    }

    private function buildDatabases(): void
    {
        $databases = Cartographer::scopedQuery(Database::query())
            ->select('id', 'name', 'icon_id', 'entity_resp_id')
            ->get();

        foreach ($databases as $database) {
            $this->addNode(
                3,
                $this->formatId(Database::$prefix, $database->id),
                $database->name,
                $this->getIcon($database->icon_id, '/images/database.png'),
                'databases', 340
            );

            if ($database->entity_resp_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Database::$prefix, $database->id),
                    $this->formatId(Entity::$prefix, $database->entity_resp_id)
                );
            }

        }

        $this->linkJoinTable('database_logical_server',
            Database::$prefix, LogicalServer::$prefix,
            'database_id', 'logical_server_id');
        $this->linkJoinTable('application_database',
            Database::$prefix, Application::$prefix,
            'database_id', 'application_id');
        $this->linkJoinTable('database_entity',
            Database::$prefix, Entity::$prefix,
            'database_id', 'entity_id');
    }

    /**
     * Build Administrative view (view 3) - Annuaires et zones
     */
    private function buildAdministrativeView(): void
    {
        $this->buildZoneAdmins();
        $this->buildAnnuaires();
        $this->buildForests();
        $this->buildDomains();
        $this->buildAdminUsers();
    }

    private function buildZoneAdmins(): void
    {
        $zones = Cartographer::scopedQuery(ZoneAdmin::query())
            ->select('id', 'name')
            ->get();

        foreach ($zones as $zone) {
            $this->addNode(
                4,
                $this->formatId(ZoneAdmin::$prefix, $zone->id),
                $zone->name,
                '/images/zoneadmin.png',
                'zone-admins', 400
            );
        }
    }

    private function buildAnnuaires(): void
    {
        $annuaires = Cartographer::scopedQuery(Annuaire::query())
            ->select('id', 'name', 'zone_admin_id', 'application_id')
            ->get();

        foreach ($annuaires as $annuaire) {
            $this->addNode(
                4,
                $this->formatId(Annuaire::$prefix, $annuaire->id),
                $annuaire->name,
                '/images/annuaire.png',
                'annuaires', 420
            );

            if ($annuaire->zone_admin_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Annuaire::$prefix, $annuaire->id),
                    $this->formatId(ZoneAdmin::$prefix, $annuaire->zone_admin_id)
                );
            }

            if ($annuaire->application_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Annuaire::$prefix, $annuaire->id),
                    $this->formatId(Application::$prefix, $annuaire->application_id)
                );
            }
        }
    }

    private function buildForests(): void
    {
        $forests = Cartographer::scopedQuery(ForestAd::query())
            ->select('id', 'name', 'zone_admin_id')
            ->get();

        foreach ($forests as $forest) {
            $this->addNode(
                4,
                $this->formatId(ForestAd::$prefix, $forest->id),
                $forest->name,
                '/images/ldap.png',
                'forest-ads', 440
            );

            if ($forest->zone_admin_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(ForestAd::$prefix, $forest->id),
                    $this->formatId(ZoneAdmin::$prefix, $forest->zone_admin_id)
                );
            }
        }
    }

    private function buildDomains(): void
    {
        $domains = Cartographer::scopedQuery(Domain::query())
            ->select('id', 'name')
            ->get();

        foreach ($domains as $domain) {
            $this->addNode(
                4,
                $this->formatId(Domain::$prefix, $domain->id),
                $domain->name,
                '/images/domain.png',
                'domains', 460
            );
        }

        $this->linkJoinTable('domain_forest_ad',
            Domain::$prefix, ForestAd::$prefix,
            'domain_id', 'forest_ad_id');
    }

    private function buildAdminUsers(): void
    {
        $adminUsers = Cartographer::scopedQuery(AdminUser::query())
            ->select('id', 'user_id', 'icon_id', 'domain_id')
            ->get();

        foreach ($adminUsers as $adminUser) {
            $this->addNode(
                4,
                $this->formatId(AdminUser::$prefix, $adminUser->id),
                $adminUser->user_id,
                $this->getIcon($adminUser->icon_id, AdminUser::$icon),
                'admin-users', 465
            );
            if ($adminUser->domain_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(AdminUser::$prefix, $adminUser->id),
                    $this->formatId(Domain::$prefix, $adminUser->domain_id)
                );
            }

        }

        $this->linkJoinTable('admin_user_application',
            AdminUser::$prefix, Application::$prefix,
            'admin_user_id', 'application_id');
    }

    /**
     * Build Process view (view 2) - Processus et informations
     */
    private function buildProcessView(): void
    {
        $this->buildInformation();
        $this->buildProcesses();
        $this->buildMacroProcesses();
        $this->buildActivities();
        $this->buildOperations();
        $this->buildTasks();
        $this->buildActors();
    }

    private function buildInformation(): void
    {
        $information = Cartographer::scopedQuery(Information::query())
            ->select('id', 'name', 'icon_id')
            ->get();

        foreach ($information as $info) {
            $this->addNode(
                2,
                $this->formatId(Information::$prefix, $info->id),
                $info->name,
                $this->getIcon($info->icon_id, '/images/information.png'),
                'information', 260
            );
        }

        $this->linkJoinTable('database_information',
            Information::$prefix, Database::$prefix,
            'information_id', 'database_id');

        $links = DB::table('information_information')
            ->select('information_id', 'child_information_id')
            ->get();

        foreach ($links as $link) {
            $this->addEdge(null, false,
                $this->formatId(Information::$prefix, $link->information_id),
                $this->formatId(Information::$prefix, $link->child_information_id),
                'FLUX', null);
        }

    }

    private function buildProcesses(): void
    {
        $processes = Cartographer::scopedQuery(Process::query())
            ->select('id', 'name', 'icon_id', 'macroprocess_id')
            ->get();

        foreach ($processes as $process) {
            $this->addNode(
                2,
                $this->formatId(Process::$prefix, $process->id),
                $process->name,
                $this->getIcon($process->icon_id, '/images/process.png'),
                'processes', 210
            );

            if ($process->macroprocess_id !== null) {
                $this->addLinkEdge(
                    $this->formatId(Process::$prefix, $process->id),
                    $this->formatId(MacroProcessus::$prefix, $process->macroprocess_id)
                );
            }
        }

        $this->linkJoinTable('information_process', Information::$prefix, Process::$prefix, 'information_id', 'process_id');
    }

    private function buildMacroProcesses(): void
    {
        $macroProcesses = Cartographer::scopedQuery(MacroProcessus::query())
            ->select('id', 'name')
            ->get();

        foreach ($macroProcesses as $macroProcess) {
            $this->addNode(
                2,
                $this->formatId(MacroProcessus::$prefix, $macroProcess->id),
                $macroProcess->name,
                '/images/macroprocess.png',
                'macro-processuses', 200
            );
        }
    }

    private function buildActivities(): void
    {
        $activities = Cartographer::scopedQuery(Activity::query())
            ->select('id', 'name')
            ->get();

        foreach ($activities as $activity) {
            $this->addNode(
                2,
                $this->formatId(Activity::$prefix, $activity->id),
                $activity->name,
                Activity::$icon,
                'activities', 220
            );
        }

        $this->linkJoinTable('activity_process',
            Activity::$prefix, Process::$prefix,
            'activity_id', 'process_id');

        $this->linkJoinTable('activity_application',
            Activity::$prefix, Application::$prefix,
            'activity_id', 'application_id');
    }

    private function buildOperations(): void
    {
        $operations = Cartographer::scopedQuery(Operation::query())
            ->select('id', 'name')
            ->get();

        foreach ($operations as $operation) {
            $this->addNode(
                2,
                $this->formatId(Operation::$prefix, $operation->id),
                $operation->name,
                '/images/operation.png',
                'operations', 230
            );
        }

        $this->linkJoinTable('activity_operation',
            Activity::$prefix, Operation::$prefix,
            'activity_id', 'operation_id');
    }

    private function buildTasks(): void
    {
        $tasks = Cartographer::scopedQuery(Task::query())
            ->select('id', 'name')
            ->get();

        foreach ($tasks as $task) {
            $this->addNode(
                2,
                $this->formatId(Task::$prefix, $task->id),
                $task->name,
                '/images/task.png',
                'tasks', 240
            );
        }

        $this->linkJoinTable('operation_task',
            Operation::$prefix, Task::$prefix,
            'operation_id', 'task_id');
    }

    private function buildActors(): void
    {
        $actors = Cartographer::scopedQuery(Actor::query())
            ->select('id', 'name')
            ->get();

        foreach ($actors as $actor) {
            $this->addNode(
                2,
                $this->formatId(Actor::$prefix, $actor->id),
                $actor->name,
                Actor::$icon,
                'actors', 250
            );
        }

        $this->linkJoinTable('actor_operation', Actor::$prefix, Operation::$prefix, 'actor_id', 'operation_id');
    }

    /**
     * Build Ecosystem view (view 1) - Entités et relations
     */
    private function buildEcosystemView(): void
    {
        $this->buildEntities();
        $this->buildRelations();
    }

    private function buildEntities(): void
    {
        $entities = Cartographer::scopedQuery(Entity::query())
            ->select('id', 'name', 'icon_id', 'parent_entity_id', 'attributes')
            ->get();

        foreach ($entities as $entity) {
            $this->addNode(
                1,
                $this->formatId(Entity::$prefix, $entity->id),
                $entity->name,
                $this->getIcon($entity->icon_id, '/images/entity.png'),
                'entities', 150,
                null,
                $entity->attributes
            );

            if ($entity->parent_entity_id !== null) {
                $this->addFluxEdge(
                    null,
                    false,
                    $this->formatId(Entity::$prefix, $entity->parent_entity_id),
                    $this->formatId(Entity::$prefix, $entity->id)
                );
            }
        }

        $this->linkJoinTable('entity_process',
            Entity::$prefix, Process::$prefix,
            'entity_id', 'process_id');
        $this->linkJoinTable('application_entity',
            Entity::$prefix, Application::$prefix,
            'entity_id', 'application_id');
    }

    private function buildRelations(): void
    {
        $relations = Cartographer::scopedQuery(Relation::query())
            ->select('id', 'name', 'source_id', 'destination_id', 'attributes')
            ->get();

        foreach ($relations as $relation) {
            $this->addNode(
                1,
                $this->formatId(Relation::$prefix, $relation->id),
                $relation->name,
                '/images/relation.png',
                'relations', 100,
                null,
                $relation->attributes
            );

            $this->addFluxEdge(null, false,
                $this->formatId(Entity::$prefix, $relation->source_id),
                $this->formatId(Relation::$prefix, $relation->id)
            );

            $this->addFluxEdge(null, false,
                $this->formatId(Relation::$prefix, $relation->id),
                $this->formatId(Entity::$prefix, $relation->destination_id)
            );
        }
    }

    /**
     * Helper methods
     */
    private function addNode(int $vue, string $id, string $label, string $image, string $type, int $order, ?string $title = null, ?string $attributes = null): void
    {
        $node = [
            'vue' => $vue,
            'id' => $id,
            'label' => $label,
            'image' => $image,
            'type' => $type,
            'order' => $order,
            'title' => $title,
            'attributes' => $attributes,
        ];

        if ($this->nodeWriter !== null) {
            ($this->nodeWriter)($node);
        } else {
            $this->nodes[] = $node;
        }
    }

    private function addEdge(?string $name, bool $bidirectional,
        string $from, string $to,
        string $type, ?string $color): void
    {
        $this->edges[] = [
            'name' => $name,
            'bidirectional' => $bidirectional,
            'from' => $from,
            'to' => $to,
            'type' => $type,
            'color' => $color,
        ];
    }

    private function addLinkEdge(string $from, string $to): void
    {
        $this->addEdge(null, false, $from, $to, 'LINK', null);
    }

    private function addPhysicalLinkEdge(string $from, string $to, ?string $color, ?string $type): void
    {
        $this->addEdge($type, false, $from, $to, 'CABLE', $color);
    }

    private function addFluxEdge(?string $name, bool $bidirectional, string $from, string $to): void
    {
        $this->addEdge($name, $bidirectional, $from, $to, 'FLUX', null);
    }

    private function addLogicalFluxEdge(?string $name, string $from, string $to): void
    {
        $this->addEdge($name, false, $from, $to, 'LFLUX', null);
    }

    private function formatId(string $prefix, $id): ?string
    {
        return $id !== null ? $prefix.$id : null;
    }

    private function getIcon(?int $iconId, string $defaultIcon): string
    {
        return $iconId === null ? $defaultIcon : "/admin/documents/{$iconId}";
    }

    private function linkToLocationOrSite(string $nodeId, $siteId = null, $buildingId = null, $bayId = null): void
    {
        if ($bayId !== null) {
            $this->addLinkEdge($nodeId, $this->formatId(Bay::$prefix, $bayId));
        } elseif ($buildingId !== null) {
            $this->addLinkEdge($nodeId, $this->formatId(Building::$prefix, $buildingId));
        } elseif ($siteId !== null) {
            $this->addLinkEdge($nodeId, $this->formatId(Site::$prefix, $siteId));
        }
    }

    private function linkDeviceToSubnetworks(?string $addressIp, string $id)
    {
        if ($addressIp === null) {
            return;
        }

        foreach ($this->subnetworks as $subnetwork) {
            if ($subnetwork->contains($addressIp)) {
                $this->addLinkEdge(
                    $id,
                    $this->formatId(Subnetwork::$prefix, $subnetwork->id));

                // Only one link per subnet
                return;
            }
        }
    }

    private function linkJoinTable(string $table, string $fromPrefix, string $toPrefix, string $fromColumn, string $toColumn): void
    {
        $joins = DB::table($table)->select($fromColumn, $toColumn)->get();

        foreach ($joins as $join) {
            $this->addLinkEdge(
                $this->formatId($fromPrefix, $join->{$fromColumn}),
                $this->formatId($toPrefix, $join->{$toColumn})
            );
        }
    }

    private static function getMaskLength(?string $address): int
    {
        // No address defined
        if ($address === null) {
            return 0;
        }

        // Split CIDR notation
        $parts = explode('/', $address);

        // Invalid format (no prefix length)
        if (count($parts) < 2) {
            return 0;
        }

        // Return the mask length as integer
        return (int) $parts[1];
    }
}
