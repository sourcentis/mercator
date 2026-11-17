<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Report\Concerns\AutoloadsRelations;
use App\Models\Cartographer;
use App\Models\Certificate;
use App\Models\Cluster;
use App\Models\Container;
use App\Models\DhcpServer;
use App\Models\Dnsserver;
use App\Models\ExternalConnectedEntity;
use App\Models\Gateway;
use App\Models\LogicalServer;
use App\Models\Network;
use App\Models\NetworkSwitch;
use App\Models\Peripheral;
use App\Models\Phone;
use App\Models\PhysicalSecurityDevice;
use App\Models\Router;
use App\Models\SecurityDevice;
use App\Models\StorageDevice;
use App\Models\Subnetwork;
use App\Models\Vlan;
use App\Models\WifiTerminal;
use App\Models\Workstation;
use App\Services\Graph\GraphSize;
use App\Services\Graph\LogicalInfrastructureGraphBuilder;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class LogicalInfrastructureView extends Controller
{
    use AutoloadsRelations;

    /**
     * Generates data for the logical infrastructure report and returns the report view.
     *
     * Prepares networks, subnetworks and related resource collections filtered by the
     * selected network/subnetwork (from request or session). Persists 'network', 'subnetwork'
     * and 'show_ip' selections to session and applies VLAN-aware filtering to network switches
     * when a specific network is selected.
     */
    public function generate(Request $request): View|RedirectResponse
    {
        $allowed = Gate::allows('explore_access') || Cartographer::canAccessAny([
            Network::class, Subnetwork::class, Gateway::class, Router::class,
            NetworkSwitch::class, Cluster::class, LogicalServer::class, Certificate::class,
            Container::class, ExternalConnectedEntity::class, Workstation::class, Phone::class,
            PhysicalSecurityDevice::class, SecurityDevice::class, StorageDevice::class,
            WifiTerminal::class, Peripheral::class, DhcpServer::class, Dnsserver::class, Vlan::class,
        ]);
        abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

        try {

            if ($request->network == null) {
                $request->session()->put('network', null);
                $network = null;
                $request->session()->put('subnetwork', null);
                $subnetwork = null;
            } else {
                if ($request->network != null) {
                    $network = intval($request->network);
                    $request->session()->put('network', $network);
                } else {
                    $network = $request->session()->get('network');
                }

                if ($request->subnetwork == null) {
                    $request->session()->put('subnetwork', null);
                    $subnetwork = null;
                } elseif ($request->subnetwork != null) {
                    $subnetwork = intval($request->subnetwork);
                    $request->session()->put('subnetwork', $subnetwork);
                } else {
                    $subnetwork = $request->session()->get('subnetwork');
                }
            }
            if ($request->has('show_ip')) {
                $request->session()->put('show_ip', true);
            } else {
                $request->session()->put('show_ip', null);
            }

            $all_networks = Cartographer::scopedQuery(Network::query())->orderBy('name')->pluck('name', 'id');
            if ($network !== null) {
                $all_subnetworks = Cartographer::scopedQuery(Subnetwork::query()->where('network_id', '=', $network))->orderBy('name')->pluck('name', 'id');

                $networks = Cartographer::scopedQuery(Network::query()->where('id', '=', $network)->with('subnetworks'))->orderBy('name')->get();

                $externalConnectedEntities = Cartographer::scopedQuery(ExternalConnectedEntity::query()
                    ->where('network_id', '=', $network))
                    ->orderBy('name')
                    ->get();

                if ($subnetwork === null) {
                    $subnetworks = Cartographer::scopedQuery(Subnetwork::query()->where('network_id', '=', $network))->orderBy('name')->get();
                } else {
                    $root = Subnetwork::query()->find($subnetwork);
                    if ($root !== null) {
                        // Un seul aller-retour DB pour toute l'arborescence (au lieu d'une requête
                        // `$node->subnetworks` par nœud du frontier), puis parcours en mémoire avec
                        // un set de visités en O(1) (au lieu du `contains()` en O(n) par nœud) :
                        // sur une hiérarchie large/profonde, la version précédente pouvait dépasser
                        // le temps d'exécution max (N+1 + scan quadratique) et provoquer un 500.
                        $childrenByParentId = Cartographer::scopedQuery(Subnetwork::query())
                            ->get()
                            ->groupBy('subnetwork_id');

                        $subnetworks = collect();
                        $visited = [];
                        $frontier = collect([$root]);

                        while ($frontier->isNotEmpty()) {
                            $next = collect();
                            foreach ($frontier as $node) {
                                if (! isset($visited[$node->id])) {
                                    $visited[$node->id] = true;
                                    $subnetworks->push($node);
                                    $next = $next->merge($childrenByParentId->get($node->id, collect()));
                                }
                            }
                            $frontier = $next;
                        }
                    } else {
                        return redirect()->back()->with('error', 'Subnetwork not found');
                    }
                }
                $subnetworks = $subnetworks->sortByDesc(function ($subnet) {
                    return $subnet->getMaskLength();
                });

                // Get Gateways
                $gateways = Cartographer::scopedQuery(Gateway::query())->orderBy('name')->get()
                    ->filter(function ($item) use ($subnetworks) {
                        foreach ($subnetworks as $subnetwork) {
                            if ($subnetwork->gateway_id === $item->id) {
                                return true;
                            }
                        }

                        return false;
                    });

                // Get VLANS
                $vlans = Cartographer::scopedQuery(Vlan::query()->with(['subnetworks', 'networkSwitches']))->orderBy('name')->get()
                    ->filter(function ($item) use ($subnetworks) {
                        return $subnetworks->pluck('vlan_id')->contains($item->id);
                    });

                // Get NetworkSwitches
                $networkSwitches = Cartographer::scopedQuery(NetworkSwitch::query()->with('vlans'))->orderBy('name')->get()
                    ->filter(function ($item) use ($subnetworks, $vlans) {
                        if ($item->vlans->isNotEmpty()) {
                            foreach ($item->vlans as $v) {
                                if ($vlans->pluck('id')->contains($v->id)) {
                                    return true;
                                }
                            }
                        } else {
                            foreach (explode(',', $item->ip) as $ip) {
                                foreach ($subnetworks as $subnetwork) {
                                    if ($subnetwork->contains($ip)) {
                                        return true;
                                    }
                                }
                            }
                        }

                        return false;
                    });

                // Get Workstations
                $workstations = Cartographer::scopedQuery(Workstation::query())->orderBy('name')->get()
                    ->filter(function ($item) use ($subnetworks) {
                        foreach (explode(',', $item->address_ip) as $ip) {
                            foreach ($subnetworks as $subnetwork) {
                                if ($subnetwork->contains($ip)) {
                                    return true;
                                }
                            }
                        }

                        return false;
                    });

                // Get WifiTerminals
                $wifiTerminals = Cartographer::scopedQuery(WifiTerminal::query())->orderBy('name')->get()
                    ->filter(function ($item) use ($subnetworks) {
                        foreach (explode(',', $item->address_ip) as $ip) {
                            foreach ($subnetworks as $subnetwork) {
                                if ($subnetwork->contains($ip)) {
                                    return true;
                                }
                            }
                        }

                        return false;
                    });

                // Get Phones
                $phones = Cartographer::scopedQuery(Phone::query())->orderBy('name')->get()
                    ->filter(function ($item) use ($subnetworks) {
                        foreach (explode(',', $item->address_ip) as $ip) {
                            foreach ($subnetworks as $subnetwork) {
                                if ($subnetwork->contains($ip)) {
                                    return true;
                                }
                            }
                        }

                        return false;
                    });

                // Get peripherals
                $peripherals = Cartographer::scopedQuery(Peripheral::query())->orderBy('name')->get()
                    ->filter(function ($item) use ($subnetworks) {
                        foreach (explode(',', $item->address_ip) as $ip) {
                            foreach ($subnetworks as $subnetwork) {
                                if ($subnetwork->contains($ip)) {
                                    return true;
                                }
                            }
                        }

                        return false;
                    });

                // Get Physical Security Devices
                $physicalSecurityDevices = Cartographer::scopedQuery(PhysicalSecurityDevice::query()->with(['site', 'building', 'bay', 'securityDevices']))->orderBy('name')->get()
                    ->filter(function ($item) use ($subnetworks) {
                        foreach (explode(',', $item->address_ip) as $ip) {
                            foreach ($subnetworks as $subnetwork) {
                                if ($subnetwork->contains($ip)) {
                                    return true;
                                }
                            }
                        }

                        return false;
                    });

                // Get routers
                $routers = Cartographer::scopedQuery(Router::query()->with('physicalRouters'))->orderBy('name')->get()
                    ->filter(function ($item) use ($subnetworks) {
                        foreach (explode(',', $item->ip_addresses) as $ip) {
                            foreach ($subnetworks as $subnetwork) {
                                if ($subnetwork->contains($ip)) {
                                    return true;
                                }
                            }
                        }

                        return false;
                    });

                // Get Security Devices
                $securityDevices = Cartographer::scopedQuery(SecurityDevice::query()->with(['applications', 'physicalSecurityDevices']))->orderBy('name')->get()
                    ->filter(function ($item) use ($subnetworks) {
                        foreach ($subnetworks as $subnetwork) {
                            if ($subnetwork->contains($item->address_ip)) {
                                return true;
                            }
                        }

                        return false;
                    });

                // Get StorageDevices
                $storageDevices = Cartographer::scopedQuery(StorageDevice::query()->with(['site', 'building', 'bay']))->orderBy('name')->get()
                    ->filter(function ($item) use ($subnetworks) {
                        foreach ($subnetworks as $subnetwork) {
                            if ($subnetwork->contains($item->address_ip)) {
                                return true;
                            }
                        }

                        return false;
                    });

                // Get DHCP Servers
                $dhcpServers = Cartographer::scopedQuery(DhcpServer::query())->orderBy('name')->get()
                    ->filter(function ($item) use ($subnetworks) {
                        foreach ($subnetworks as $subnetwork) {
                            foreach (explode(',', $item->address_ip) as $address) {
                                if ($subnetwork->contains($item->address_ip)) {
                                    return true;
                                }
                            }
                        }

                        return false;
                    });

                // Get DNS Servers
                $dnsservers = Cartographer::scopedQuery(Dnsserver::query())->orderBy('name')->get()
                    ->filter(function ($item) use ($subnetworks) {
                        foreach ($subnetworks as $subnetwork) {
                            // foreach (explode(',', $item->address_ip) as $address) {
                            if ($subnetwork->contains($item->address_ip)) {
                                return true;
                            }
                            // }
                        }

                        return false;
                    });

                $clusters = Cartographer::scopedQuery(Cluster::query()->with(['logicalServers', 'routers', 'physicalServers']))->orderBy('name')->get();

                // Get Logical serveurs
                $logicalServers = Cartographer::scopedQuery(LogicalServer::query()->with(['clusters', 'certificates', 'containers']))->orderBy('name')->get()
                    ->filter(function ($item) use ($subnetworks) {
                        foreach ($subnetworks as $subnetwork) {
                            foreach (explode(',', $item->address_ip) as $address) {
                                if ($subnetwork->contains($address)) {
                                    return true;
                                }
                            }
                        }

                        return false;
                    });

                // Get Certificates
                $certificates = Cartographer::scopedQuery(Certificate::query()->with(['logicalServers', 'applications']))->orderBy('name')->get()
                    ->filter(function ($item) use ($logicalServers) {
                        foreach ($logicalServers as $logical_server) {
                            foreach ($logical_server->certificates as $cert) {
                                if ($cert->id === $item->id) {
                                    return true;
                                }
                            }
                        }

                        return false;
                    });

                // Get Containers
                $containers = Cartographer::scopedQuery(Container::query()->with('logicalServers'))->orderBy('name')->get()
                    ->filter(function ($item) use ($logicalServers) {
                        foreach ($logicalServers as $logical_server) {
                            foreach ($logical_server->containers as $container) {
                                if ($container->id === $item->id) {
                                    return true;
                                }
                            }
                        }

                        return false;
                    });

            } else {
                $all_subnetworks = Cartographer::scopedQuery(Subnetwork::query())->orderBy('name')->pluck('name', 'id');

                // all
                $networks = Cartographer::scopedQuery(Network::query()->with('subnetworks'))->orderBy('name')->get();
                $subnetworks = Cartographer::scopedQuery(Subnetwork::query())->orderBy('name')->get();
                $subnetworks = $subnetworks->sortByDesc(function ($subnet) {
                    return $subnet->getMaskLength();
                });
                $gateways = Cartographer::scopedQuery(Gateway::query())->orderBy('name')->get();
                $externalConnectedEntities = Cartographer::scopedQuery(ExternalConnectedEntity::query())->orderBy('name')->get();
                $networkSwitches = Cartographer::scopedQuery(NetworkSwitch::query()->with(['vlans', 'physicalSwitches']))->orderBy('name')->get();
                $workstations = Cartographer::scopedQuery(Workstation::query()->with('site', 'building'))->orderBy('name')->get();
                $wifiTerminals = Cartographer::scopedQuery(WifiTerminal::query()->with('site', 'building'))->orderBy('name')->get();
                $phones = Cartographer::scopedQuery(Phone::query()->with('site', 'building'))->orderBy('name')->get();
                $physicalSecurityDevices = Cartographer::scopedQuery(PhysicalSecurityDevice::query()->with(['site', 'building', 'bay', 'securityDevices']))->orderBy('name')->get();
                $peripherals = Cartographer::scopedQuery(Peripheral::query()->with('site', 'building', 'bay'))->orderBy('name')->get();
                $routers = Cartographer::scopedQuery(Router::query()->with('physicalRouters'))->orderBy('name')->get();
                $securityDevices = Cartographer::scopedQuery(SecurityDevice::query()->with(['applications', 'physicalSecurityDevices']))->orderBy('name')->get();
                $storageDevices = Cartographer::scopedQuery(StorageDevice::query()->with('site', 'building', 'bay'))->orderBy('name')->get();
                $dhcpServers = Cartographer::scopedQuery(DhcpServer::query())->orderBy('name')->get();
                $dnsservers = Cartographer::scopedQuery(Dnsserver::query())->orderBy('name')->get();
                $clusters = Cartographer::scopedQuery(Cluster::query()->with(['logicalServers', 'routers', 'physicalServers']))->orderBy('name')->get();
                $logicalServers = Cartographer::scopedQuery(LogicalServer::query()->with(['clusters', 'certificates', 'containers']))->orderBy('name')->get();
                $containers = Cartographer::scopedQuery(Container::query()->with('logicalServers'))->orderBy('name')->get();
                $certificates = Cartographer::scopedQuery(Certificate::query()->with(['logicalServers', 'applications']))->orderBy('name')->get();
                $vlans = Cartographer::scopedQuery(Vlan::query()->with(['subnetworks', 'networkSwitches']))->orderBy('name')->get();

            }

            $showIp = (bool) $request->session()->get('show_ip');

            $this->autoloadRelations(
                $networks, $subnetworks, $gateways, $externalConnectedEntities, $vlans, $networkSwitches,
                $clusters, $logicalServers, $dhcpServers, $dnsservers, $certificates, $containers, $routers,
                $securityDevices, $workstations, $wifiTerminals, $phones, $peripherals, $physicalSecurityDevices,
                $storageDevices
            );

            // Compte les nœuds avant de construire le DOT : un graphe trop grand n'est ni construit ni envoyé
            $graphTooLarge = GraphSize::tooLarge(GraphSize::count(
                $networks, $subnetworks, $gateways, $externalConnectedEntities, $vlans, $networkSwitches,
                $clusters, $logicalServers, $dhcpServers, $dnsservers, $certificates, $containers, $routers,
                $securityDevices, $workstations, $wifiTerminals, $phones, $peripherals, $physicalSecurityDevices,
                $storageDevices
            ));

            $graphBuilder = new LogicalInfrastructureGraphBuilder;
            $dotSrc = $graphTooLarge ? '' : $graphBuilder->buildDot(
                $networks,
                $subnetworks,
                $gateways,
                $externalConnectedEntities,
                $vlans,
                $networkSwitches,
                $clusters,
                $logicalServers,
                $dhcpServers,
                $dnsservers,
                $certificates,
                $containers,
                $routers,
                $securityDevices,
                $workstations,
                $wifiTerminals,
                $phones,
                $peripherals,
                $physicalSecurityDevices,
                $storageDevices,
                $showIp
            );
            $imageManifest = $graphBuilder->imageManifest($containers, $logicalServers, $securityDevices, $physicalSecurityDevices, $peripherals, $workstations, $storageDevices);

            return view(
                'admin/reports/logical_infrastructure',
                compact(
                    'all_networks',
                    'all_subnetworks',
                    'networks',
                    'subnetworks',
                    'gateways',
                    'externalConnectedEntities',
                    'networkSwitches',
                    'workstations',
                    'phones',
                    'physicalSecurityDevices',
                    'peripherals',
                    'wifiTerminals',
                    'routers',
                    'securityDevices',
                    'storageDevices',
                    'dhcpServers',
                    'dnsservers',
                    'clusters',
                    'logicalServers',
                    'certificates',
                    'containers',
                    'vlans',
                    'dotSrc',
                    'graphTooLarge',
                    'imageManifest'
                )
            );
        } catch (\Throwable $e) {
            // Diagnostic : capture l'état de toutes les variables locales déjà résolues au
            // moment du plantage (comptage pour les Collection, valeur brute pour les scalaires)
            // afin d'identifier la section et le volume de données en cause, sans avoir à
            // reproduire le jeu de données en prod pour chaque hypothèse.
            Log::error('LogicalInfrastructureView::generate a échoué', [
                'user_id' => auth()->id(),
                'params' => $request->only(['network', 'subnetwork', 'show_ip']),
                'state' => collect(get_defined_vars())
                    ->except(['request', 'allowed', 'e'])
                    ->map(fn ($v) => match (true) {
                        $v instanceof Collection => $v->count().' item(s)',
                        is_scalar($v) || $v === null => $v,
                        default => get_debug_type($v),
                    })
                    ->toArray(),
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
