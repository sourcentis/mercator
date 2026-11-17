<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Report\Concerns\AutoloadsRelations;
use App\Models\Bay;
use App\Models\Building;
use App\Models\Cartographer;
use App\Models\Peripheral;
use App\Models\Phone;
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
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PhysicalInfrastructureView extends Controller
{
    use AutoloadsRelations;

    public function generate(Request $request)
    {
        $allowed = Gate::allows('explore_access') || Cartographer::canAccessAny([
            Site::class, Building::class, Bay::class, PhysicalServer::class,
            PhysicalSwitch::class, PhysicalRouter::class, Workstation::class,
            StorageDevice::class, Peripheral::class, Phone::class,
            WifiTerminal::class, PhysicalSecurityDevice::class,
        ]);
        abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->site == null) {
            $request->session()->put('site', null);
            $site = null;
            $request->session()->put('building', null);
            $buildingId = null;
        } else {
            if ($request->site != null) {
                $site = intval($request->site);
                $request->session()->put('site', $site);
            } else {
                $site = $request->session()->get('site');
            }

            if ($request->building == null) {
                $request->session()->put('building', null);
                $buildingId = null;
            } elseif ($request->building != null) {
                $buildingId = intval($request->building);
                $request->session()->put('building', $buildingId);
            } else {
                $buildingId = $request->session()->get('building');
            }
        }

        $all_sites = Cartographer::scopedQuery(Site::query())->orderBy('name')->pluck('name', 'id');

        if ($site !== null) {
            $sites = Cartographer::scopedQuery(Site::query()->where('id', '=', $site))->orderBy('name')->get();

            $all_buildings = Cartographer::scopedQuery(Building::query()->where('site_id', '=', $site))->orderBy('name')->pluck('name', 'id');
            if ($buildingId === null) {
                $buildings = Building::query()->where('site_id', '=', $site)->orderBy('name')->get();
            } else {
                $root = Building::find($buildingId);
                if ($root !== null) {
                    $buildings = collect();
                    // Get children
                    $frontier = collect([$root]);

                    while ($frontier->isNotEmpty()) {
                        $next = collect();

                        foreach ($frontier as $node) {
                            if (! $buildings->contains('id', $node->id)) {
                                $buildings->push($node);
                                $next = $next->merge($node->buildings);
                            }
                        }

                        $frontier = $next;
                    }
                } else {
                    return redirect()->back()->with('error', 'Building not found');
                }
            }

            // Get all bays
            $bays = Cartographer::scopedQuery(Bay::query())->orderBy('name')->get()
                ->filter(function ($item) use ($site, $buildings) {
                    if (($item->building_id === null) && ($item->site_id === $site)) {
                        return true;
                    }
                    foreach ($buildings as $building) {
                        if ($item->building_id === $building->id) {
                            return true;
                        }
                    }

                    return false;
                });

            $physicalServers = Cartographer::scopedQuery(PhysicalServer::query())->orderBy('name')->get()
                ->filter(function ($item) use ($site, $buildings, $bays) {
                    if (($buildings == null) && ($item->site_id == $site)) {
                        return true;
                    }
                    if ($item->bay_id === null) {
                        foreach ($buildings as $building) {
                            if ($item->building_id === $building->id) {
                                return true;
                            }
                        }
                    } else {
                        foreach ($bays as $bay) {
                            if ($item->bay_id === $bay->id) {
                                return true;
                            }
                        }
                    }

                    return false;
                });

            $workstations = Cartographer::scopedQuery(Workstation::query())->orderBy('name')->get()
                ->filter(function ($item) use ($site, $buildings) {
                    if (($item->building_id === null) && ($item->site_id === $site)) {
                        return true;
                    }
                    foreach ($buildings as $building) {
                        if ($item->building_id === $building->id) {
                            return true;
                        }
                    }

                    return false;
                });

            $storageDevices = Cartographer::scopedQuery(StorageDevice::query())->orderBy('name')->get()
                ->filter(function ($item) use ($site, $buildings, $bays) {
                    if (($item->bay_id === null) &&
                        ($item->building_id === null) &&
                        ($item->site_id === $site)) {
                        return true;
                    }
                    if ($item->bay_id === null) {
                        foreach ($buildings as $building) {
                            if ($item->building_id === $building->id) {
                                return true;
                            }
                        }
                    } else {
                        foreach ($bays as $bay) {
                            if ($item->bay_id === $bay->id) {
                                return true;
                            }
                        }
                    }

                    return false;
                });

            $peripherals = Cartographer::scopedQuery(Peripheral::query())->orderBy('name')->get()
                ->filter(function ($item) use ($site, $buildings, $bays) {
                    if (($item->bay_id === null) &&
                        ($item->building_id === null) &&
                        ($item->site_id === $site)) {
                        return true;
                    }
                    if ($item->bay_id === null) {
                        foreach ($buildings as $building) {
                            if ($item->building_id === $building->id) {
                                return true;
                            }
                        }
                    } else {
                        foreach ($bays as $bay) {
                            if ($item->bay_id === $bay->id) {
                                return true;
                            }
                        }
                    }

                    return false;
                });

            $phones = Cartographer::scopedQuery(Phone::query())->orderBy('name')->get()
                ->filter(function ($item) use ($site, $buildings) {
                    if (($item->building_id === null) && ($item->site_id === $site)) {
                        return true;
                    }
                    foreach ($buildings as $building) {
                        if ($item->building_id === $building->id) {
                            return true;
                        }
                    }

                    return false;
                });

            $physicalSwitches = Cartographer::scopedQuery(PhysicalSwitch::query())->orderBy('name')->get()
                ->filter(function ($item) use ($site, $buildings, $bays) {
                    if (($item->bay_id === null) &&
                        ($item->building_id === null) &&
                        ($item->site_id === $site)) {
                        return true;
                    }
                    if ($item->bay_id === null) {
                        foreach ($buildings as $building) {
                            if ($item->building_id === $building->id) {
                                return true;
                            }
                        }
                    } else {
                        foreach ($bays as $bay) {
                            if ($item->bay_id === $bay->id) {
                                return true;
                            }
                        }
                    }

                    return false;
                });

            $physicalRouters = Cartographer::scopedQuery(PhysicalRouter::query())->orderBy('name')->get()
                ->filter(function ($item) use ($site, $buildings, $bays) {
                    if (($item->bay_id === null) &&
                        ($item->building_id === null) &&
                        ($item->site_id === $site)) {
                        return true;
                    }
                    if ($item->bay_id === null) {
                        foreach ($buildings as $building) {
                            if ($item->building_id === $building->id) {
                                return true;
                            }
                        }
                    } else {
                        foreach ($bays as $bay) {
                            if ($item->bay_id === $bay->id) {
                                return true;
                            }
                        }
                    }

                    return false;
                });

            $wifiTerminals = Cartographer::scopedQuery(WifiTerminal::query())->orderBy('name')->get()
                ->filter(function ($item) use ($site, $buildings) {
                    if (($item->building_id === null) && ($item->site_id === $site)) {
                        return true;
                    }
                    foreach ($buildings as $building) {
                        if ($item->building_id === $building->id) {
                            return true;
                        }
                    }

                    return false;
                });

            $physicalSecurityDevices = Cartographer::scopedQuery(PhysicalSecurityDevice::query())->orderBy('name')->get()
                ->filter(function ($item) use ($site, $buildings, $bays) {
                    if (($item->bay_id === null) &&
                        ($item->building_id === null) &&
                        ($item->site_id === $site)) {
                        return true;
                    }
                    if ($item->bay_id === null) {
                        foreach ($buildings as $building) {
                            if ($item->building_id === $building->id) {
                                return true;
                            }
                        }
                    } else {
                        foreach ($bays as $bay) {
                            if ($item->bay_id === $bay->id) {
                                return true;
                            }
                        }
                    }

                    return false;
                });
        } else {
            $sites = Cartographer::scopedQuery(Site::query())->orderBy('name')->get();
            $buildings = Cartographer::scopedQuery(Building::query())->orderBy('name')->get();
            $all_buildings = null;
            $bays = Cartographer::scopedQuery(Bay::query())->orderBy('name')->get();
            $physicalServers = Cartographer::scopedQuery(PhysicalServer::query())->orderBy('name')->get();
            $workstations = Cartographer::scopedQuery(Workstation::query())->orderBy('name')->get();
            $storageDevices = Cartographer::scopedQuery(StorageDevice::query())->orderBy('name')->get();
            $peripherals = Cartographer::scopedQuery(Peripheral::query())->orderBy('name')->get();
            $phones = Cartographer::scopedQuery(Phone::query())->orderBy('name')->get();
            $physicalSwitches = Cartographer::scopedQuery(PhysicalSwitch::query())->orderBy('name')->get();
            $physicalRouters = Cartographer::scopedQuery(PhysicalRouter::query())->orderBy('name')->get();
            $wifiTerminals = Cartographer::scopedQuery(WifiTerminal::query())->orderBy('name')->get();
            $physicalSecurityDevices = Cartographer::scopedQuery(PhysicalSecurityDevice::query())->orderBy('name')->get();
        }

        $this->autoloadRelations(
            $sites, $buildings, $bays, $physicalServers, $workstations, $storageDevices, $peripherals,
            $phones, $physicalSwitches, $physicalRouters, $wifiTerminals, $physicalSecurityDevices
        );

        // Compte les nœuds avant de construire le DOT : un graphe trop grand n'est ni construit ni envoyé
        $graphTooLarge = GraphSize::tooLarge(GraphSize::count(
            $sites, $buildings, $bays, $physicalServers, $workstations, $storageDevices, $peripherals,
            $phones, $physicalSwitches, $physicalRouters, $wifiTerminals, $physicalSecurityDevices
        ));

        $graphBuilder = new PhysicalInfrastructureGraphBuilder;
        $dotSrc = $graphTooLarge ? '' : $graphBuilder->buildLocationDot(
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
            $buildingId !== null
        );
        $imageManifest = $graphBuilder->locationImageManifest($sites, $buildings, $workstations, $storageDevices, $peripherals, $physicalSwitches, $physicalSecurityDevices);

        return view('admin/reports/physical_infrastructure')
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
            ->with('graphTooLarge', $graphTooLarge)
            ->with('dotSrc', $dotSrc)
            ->with('imageManifest', $imageManifest);
    }
}
