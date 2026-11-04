<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Bay;
use App\Models\Building;
use App\Models\Cartographer;
use App\Models\Peripheral;
use App\Models\Phone;
use App\Models\PhysicalLink;
use App\Models\PhysicalRouter;
use App\Models\PhysicalSecurityDevice;
use App\Models\PhysicalServer;
use App\Models\PhysicalSwitch;
use App\Models\Site;
use App\Models\StorageDevice;
use App\Models\WifiTerminal;
use App\Models\Workstation;
use App\Services\Graph\GraphSize;
use App\Services\Graph\PhysicalInfrastructureGraphBuilder;
use Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class NetworkInfrastructureView extends Controller
{
    /** Relations touched by admin.sites._details and the site dropdown list. */
    private const SITE_RELATIONS = ['perimeter', 'buildings'];

    /** Relations touched by admin.buildings._details and buildConnectivityDot()'s per-building clustering. */
    private const BUILDING_RELATIONS = [
        'perimeter', 'site', 'building', 'buildings',
        'phones', 'workstations', 'wifiTerminals', 'physicalSwitches', 'physicalRouters',
        'peripherals', 'physicalServers', 'storageDevices',
        'bays.perimeter', 'bays.physicalServers', 'bays.storageDevices', 'bays.physicalSwitches',
        'bays.physicalSecurityDevices', 'bays.physicalRouters', 'bays.peripherals',
    ];

    /** Relations touched by admin.bays._details and buildConnectivityDot()'s site-level orphan bays. */
    private const BAY_RELATIONS = [
        'perimeter', 'building', 'site',
        'peripherals', 'physicalRouters', 'physicalSecurityDevices', 'physicalServers', 'physicalSwitches', 'storageDevices',
    ];

    private const PHYSICAL_SERVER_RELATIONS = ['perimeter'];

    private const WORKSTATION_RELATIONS = ['perimeter'];

    private const PERIPHERAL_RELATIONS = ['perimeter'];

    private const WIFI_TERMINAL_RELATIONS = ['perimeter'];

    private const STORAGE_DEVICE_RELATIONS = ['perimeter', 'site', 'building', 'bay'];

    private const PHONE_RELATIONS = ['perimeter', 'site', 'building'];

    private const PHYSICAL_SWITCH_RELATIONS = ['perimeter', 'site', 'building', 'bay', 'networkSwitches'];

    private const PHYSICAL_ROUTER_RELATIONS = ['perimeter', 'site', 'building', 'bay', 'routers', 'vlans'];

    private const PHYSICAL_SECURITY_DEVICE_RELATIONS = ['perimeter', 'site', 'building', 'bay', 'securityDevices'];

    /** Relations touched by admin.physicalLinks table rendering (src/dest endpoint of every type). */
    private const PHYSICAL_LINK_RELATIONS = [
        'peripheralSrc', 'phoneSrc', 'physicalRouterSrc', 'physicalSecurityDeviceSrc', 'physicalServerSrc',
        'physicalSwitchSrc', 'storageDeviceSrc', 'wifiTerminalSrc', 'workstationSrc',
        'peripheralDest', 'phoneDest', 'physicalRouterDest', 'physicalSecurityDeviceDest', 'physicalServerDest',
        'physicalSwitchDest', 'storageDeviceDest', 'wifiTerminalDest', 'workstationDest',
    ];

    public function generate(Request $request)
    {
        $allowed = Gate::allows('explore_access') || Cartographer::canAccessAny([
            Site::class, Building::class, Bay::class, PhysicalServer::class, PhysicalSwitch::class,
            PhysicalRouter::class, Workstation::class, StorageDevice::class, Peripheral::class,
            Phone::class, WifiTerminal::class, PhysicalSecurityDevice::class, PhysicalLink::class,
        ]);
        abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Show ports filter
        if ($request->has('show_ports')) {
            $request->session()->put('show_ports', true);
        } else {
            $request->session()->put('show_ports', null);
        }

        // Objects filter
        $buildingIds = null;
        if ($request->site == null) {
            $request->session()->put('site', null);
            $siteId = null;
            $request->session()->put('building', null);
            $building = null;
        } else {
            if ($request->site != null) {
                $siteId = intval($request->site);
                $request->session()->put('site', $siteId);
            } else {
                $siteId = $request->session()->get('site');
            }

            if ($request->buildings == null) {
                $request->session()->put('buildings', null);
                $buildingIds = null;
            } elseif ($request->buildings != null) {
                $buildingIds = $request->buildings;
                $request->session()->put('buildings', $buildingIds);
            } else {
                $buildingIds = $request->session()->get('buildings');
            }
        }

        $all_sites = Cartographer::scopedQuery(Site::query())->orderBy('name')->pluck('name', 'id');

        if ($siteId != null) {
            $sites = Site::where('id', '=', $siteId)->with(self::SITE_RELATIONS)->get();
            $site = $sites->first();

            $all_buildings = Building::query()
                ->where('site_id', $siteId)
                ->pluck('name', 'id');

            if ($buildingIds == null || (count($buildingIds) == 0)) {
                $buildings = Building::where('site_id', '=', $site->id)
                    ->orderBy('name')->get();
            } else {
                // ----------------------------------------------------
                /*
                $roots = Building::whereIn('id', $buildingIds)
                    ->with('allChildren')
                    ->get();

                $buildings = collect();
                $seen = [];

                $flatten = function ($node) use (&$flatten, &$buildings, &$seen) {
                    if (isset($seen[$node->id])) {
                        return;
                    }
                    $seen[$node->id] = true;
                    $buildings->push($node);
                    foreach ($node->allChildren as $child) {
                        $flatten($child);
                    }
                };

                foreach ($roots as $root) {
                    $flatten($root);
                }

                $buildings = $buildings->unique('id')->values();
                */

                $buildings = new \Illuminate\Database\Eloquent\Collection;  // résultat final (Collection<Building>)
                $seen = [];              // set d'IDs déjà vus (évite doublons)

                $roots = Building::findMany($buildingIds);
                foreach ($roots as $root) {
                    $seen[$root->id] = true;
                    $buildings->push($root);
                }

                // 2) Descendants (BFS) pour toutes les racines, un aller-retour DB par palier
                // (whereIn batché) plutôt qu'un accès à la relation ->buildings par nœud.
                $frontierIds = $buildings->pluck('id')->all();

                while (! empty($frontierIds)) {
                    $children = Building::whereIn('building_id', $frontierIds)->get();
                    $frontierIds = [];

                    foreach ($children as $child) {
                        if (! isset($seen[$child->id])) {
                            $seen[$child->id] = true;
                            $buildings->push($child);
                            $frontierIds[] = $child->id;
                        }
                    }
                }

                // 3) Parents (par paliers) jusqu'à ce que building_id soit null
                $pending = $buildings
                    ->pluck('building_id')   // parents directs des nœuds déjà collectés
                    ->filter()
                    ->unique()
                    ->values();

                while ($pending->isNotEmpty()) {
                    // éviter de recharger des parents déjà vus
                    $toFetch = $pending->reject(fn ($id) => isset($seen[$id]))->values();
                    if ($toFetch->isEmpty()) {
                        break;
                    }

                    // Charger en une requête les parents du palier courant
                    $parents = Building::whereIn('id', $toFetch)->get();

                    $nextIds = [];

                    foreach ($parents as $parent) {
                        if (! isset($seen[$parent->id])) {
                            $seen[$parent->id] = true;
                            $buildings->push($parent);

                            if (! is_null($parent->building_id)) {
                                $nextIds[] = $parent->building_id; // remonte d'un cran
                            }
                        }
                    }

                    $pending = collect($nextIds)->filter()->unique()->values();
                }

                $buildings = $buildings->values();

                // ----------------------------------------------------
            }

            $buildingIds = $buildings->pluck('id');

            $bays = Bay::query()
                ->where(function ($q) use ($buildingIds, $siteId) {
                    $q->whereIn('building_id', $buildingIds)
                        ->orWhere(function ($q) use ($siteId) {
                            $q->whereNull('building_id')->where('site_id', $siteId);
                        });
                })
                ->with(self::BAY_RELATIONS)
                ->orderBy('name')
                ->get();
            $bayIds = $bays->pluck('id');

            $physicalServers = $this->withinBayOrBuilding(Cartographer::scopedQuery(PhysicalServer::query()), $buildingIds, $bayIds, $siteId)
                ->with(self::PHYSICAL_SERVER_RELATIONS)->orderBy('name')->get();
            $workstations = $this->withinBuilding(Cartographer::scopedQuery(Workstation::query()), $buildingIds, $siteId)
                ->with(self::WORKSTATION_RELATIONS)->orderBy('name')->get();
            $storageDevices = $this->withinBayOrBuilding(Cartographer::scopedQuery(StorageDevice::query()), $buildingIds, $bayIds, $siteId)
                ->with(self::STORAGE_DEVICE_RELATIONS)->orderBy('name')->get();
            $physicalSwitches = $this->withinBayOrBuilding(Cartographer::scopedQuery(PhysicalSwitch::query()), $buildingIds, $bayIds, $siteId)
                ->with(self::PHYSICAL_SWITCH_RELATIONS)->orderBy('name')->get();
            $peripherals = $this->withinBayOrBuilding(Cartographer::scopedQuery(Peripheral::query()), $buildingIds, $bayIds, $siteId)
                ->with(self::PERIPHERAL_RELATIONS)->orderBy('name')->get();
            $phones = $this->withinBuilding(Cartographer::scopedQuery(Phone::query()), $buildingIds, $siteId)
                ->with(self::PHONE_RELATIONS)->orderBy('name')->get();
            $physicalRouters = $this->withinBayOrBuilding(Cartographer::scopedQuery(PhysicalRouter::query()), $buildingIds, $bayIds, $siteId)
                ->with(self::PHYSICAL_ROUTER_RELATIONS)->orderBy('name')->get();
            $wifiTerminals = $this->withinBuilding(Cartographer::scopedQuery(WifiTerminal::query()), $buildingIds, $siteId)
                ->with(self::WIFI_TERMINAL_RELATIONS)->orderBy('name')->get();
            $physicalSecurityDevices = $this->withinBayOrBuilding(Cartographer::scopedQuery(PhysicalSecurityDevice::query()), $buildingIds, $bayIds, $siteId)
                ->with(self::PHYSICAL_SECURITY_DEVICE_RELATIONS)->orderBy('name')->get();

            // Filter physicalLinks on selected objects: a link is included only when every
            // endpoint it actually uses (src/dest) resolves inside the object set already
            // filtered above for this site/building selection.
            $physicalLinksQuery = Cartographer::scopedQuery(PhysicalLink::query());
            foreach ([
                ['physical_router_src_id', 'physical_router_dest_id', $physicalRouters->pluck('id')],
                ['physical_switch_src_id', 'physical_switch_dest_id', $physicalSwitches->pluck('id')],
                ['physical_server_src_id', 'physical_server_dest_id', $physicalServers->pluck('id')],
                ['workstation_src_id', 'workstation_dest_id', $workstations->pluck('id')],
                ['peripheral_src_id', 'peripheral_dest_id', $peripherals->pluck('id')],
                ['storage_device_src_id', 'storage_device_dest_id', $storageDevices->pluck('id')],
                ['wifi_terminal_src_id', 'wifi_terminal_dest_id', $wifiTerminals->pluck('id')],
                ['phone_src_id', 'phone_dest_id', $phones->pluck('id')],
                ['physical_security_device_src_id', 'physical_security_device_dest_id', $physicalSecurityDevices->pluck('id')],
            ] as [$srcField, $destField, $ids]) {
                $this->constrainEndpoint($physicalLinksQuery, $srcField, $ids);
                $this->constrainEndpoint($physicalLinksQuery, $destField, $ids);
            }
            $physicalLinks = $physicalLinksQuery->with(self::PHYSICAL_LINK_RELATIONS)->get();
        } else {
            $sites = Cartographer::scopedQuery(Site::query())->with(self::SITE_RELATIONS)->orderBy('name')->get();
            $buildings = Cartographer::scopedQuery(Building::query())->with(self::BUILDING_RELATIONS)->orderBy('name')->get();
            $all_buildings = null;
            $bays = Cartographer::scopedQuery(Bay::query())->with(self::BAY_RELATIONS)->orderBy('name')->get();
            $physicalServers = Cartographer::scopedQuery(PhysicalServer::query())->with(self::PHYSICAL_SERVER_RELATIONS)->orderBy('name')->get();
            $workstations = Cartographer::scopedQuery(Workstation::query())->with(self::WORKSTATION_RELATIONS)->orderBy('name')->get();
            $storageDevices = Cartographer::scopedQuery(StorageDevice::query())->with(self::STORAGE_DEVICE_RELATIONS)->orderBy('name')->get();
            $peripherals = Cartographer::scopedQuery(Peripheral::query())->with(self::PERIPHERAL_RELATIONS)->orderBy('name')->get();
            $phones = Cartographer::scopedQuery(Phone::query())->with(self::PHONE_RELATIONS)->orderBy('name')->get();
            $physicalSwitches = Cartographer::scopedQuery(PhysicalSwitch::query())->with(self::PHYSICAL_SWITCH_RELATIONS)->orderBy('name')->get();
            $physicalRouters = Cartographer::scopedQuery(PhysicalRouter::query())->with(self::PHYSICAL_ROUTER_RELATIONS)->orderBy('name')->get();
            $wifiTerminals = Cartographer::scopedQuery(WifiTerminal::query())->with(self::WIFI_TERMINAL_RELATIONS)->orderBy('name')->get();
            $physicalSecurityDevices = Cartographer::scopedQuery(PhysicalSecurityDevice::query())->with(self::PHYSICAL_SECURITY_DEVICE_RELATIONS)->orderBy('name')->get();
            $physicalLinks = Cartographer::scopedQuery(PhysicalLink::query())->with(self::PHYSICAL_LINK_RELATIONS)->get();
        }

        // $buildings is assembled from several batched queries in the site/building-filtered
        // branch above (BFS roots, descendants, ancestors), none of which carry eager loads;
        // load them here in one pass regardless of which branch built the collection.
        $buildings->load(self::BUILDING_RELATIONS);

        // Compte les nœuds avant de construire le DOT : un graphe trop grand n'est ni construit ni envoyé
        $graphTooLarge = GraphSize::tooLarge(GraphSize::count(
            $sites, $buildings, $bays, $physicalServers, $workstations, $storageDevices, $peripherals,
            $phones, $physicalSwitches, $physicalRouters, $wifiTerminals, $physicalSecurityDevices
        ));

        $graphBuilder = new PhysicalInfrastructureGraphBuilder;
        $showPorts = (bool) $request->session()->get('show_ports');
        $dotSrc = $graphTooLarge ? '' : $graphBuilder->buildConnectivityDot(
            $sites,
            $buildings,
            $bays,
            $physicalServers,
            $workstations,
            $storageDevices,
            $peripherals,
            $phones,
            $physicalSwitches,
            $physicalRouters,
            $wifiTerminals,
            $physicalSecurityDevices,
            $physicalLinks,
            $showPorts
        );
        $imageManifest = $graphBuilder->connectivityImageManifest($physicalServers, $workstations, $storageDevices, $peripherals, $physicalSwitches, $physicalSecurityDevices);

        return view('admin/reports/network_infrastructure')
            ->with('all_sites', $all_sites)
            ->with('sites', $sites)
            ->with('all_buildings', $all_buildings)
            ->with('buildings', $buildings)
            ->with('bays', $bays)
            ->with('physicalServers', $physicalServers)
            ->with('workstations', $workstations)
            ->with('storageDevices', $storageDevices)
            ->with('peripherals', $peripherals)
            ->with('phones', $phones)
            ->with('physicalSwitches', $physicalSwitches)
            ->with('physicalRouters', $physicalRouters)
            ->with('wifiTerminals', $wifiTerminals)
            ->with('physicalSecurityDevices', $physicalSecurityDevices)
            ->with('physicalLinks', $physicalLinks)
            ->with('graphTooLarge', $graphTooLarge)
            ->with('dotSrc', $dotSrc)
            ->with('imageManifest', $imageManifest);
    }

    /**
     * Restrict $query to rows attached to one of the given bays, else one of the given
     * buildings (only when bay_id is null), else the site itself (when both are null).
     * Mirrors the bay > building > site precedence objects are physically located under.
     *
     * @param  Collection<int, int>  $buildingIds
     * @param  Collection<int, int>  $bayIds
     */
    private function withinBayOrBuilding(Builder $query, $buildingIds, $bayIds, ?int $siteId): Builder
    {
        return $query->where(function ($q) use ($buildingIds, $bayIds, $siteId) {
            $q->where(function ($q2) use ($bayIds) {
                $q2->whereNotNull('bay_id')->whereIn('bay_id', $bayIds);
            })->orWhere(function ($q2) use ($buildingIds) {
                $q2->whereNull('bay_id')->whereIn('building_id', $buildingIds);
            })->orWhere(function ($q2) use ($siteId) {
                $q2->whereNull('bay_id')->whereNull('building_id')->where('site_id', $siteId);
            });
        });
    }

    /**
     * Restrict $query to rows attached to one of the given buildings, else the site itself
     * (when building_id is null). For models with no bay_id column (workstations, phones, ...).
     *
     * @param  Collection<int, int>  $buildingIds
     */
    private function withinBuilding(Builder $query, $buildingIds, ?int $siteId): Builder
    {
        return $query->where(function ($q) use ($buildingIds, $siteId) {
            $q->whereIn('building_id', $buildingIds)
                ->orWhere(function ($q2) use ($siteId) {
                    $q2->whereNull('building_id')->where('site_id', $siteId);
                });
        });
    }

    /**
     * A link endpoint field is acceptable when it's unused (null, so it refers to a different
     * link type entirely) or when it points at an object already in the filtered $ids set.
     *
     * @param  Collection<int, int>  $ids
     */
    private function constrainEndpoint(Builder $query, string $field, $ids): void
    {
        $query->where(function ($q) use ($field, $ids) {
            $q->whereNull($field)->orWhereIn($field, $ids);
        });
    }
}
