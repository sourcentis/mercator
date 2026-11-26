<?php

namespace App\Services\Report;

use App\Contracts\HasUniqueIdentifierContract;
use App\Models\AdminUser;
use App\Models\Bay;
use App\Models\Building;
use App\Models\Cartographer;
use App\Models\Lan;
use App\Models\Man;
use App\Models\Peripheral;
use App\Models\Phone;
use App\Models\PhysicalLink;
use App\Models\PhysicalRouter;
use App\Models\PhysicalSecurityDevice;
use App\Models\PhysicalServer;
use App\Models\PhysicalSwitch;
use App\Models\Site;
use App\Models\StorageDevice;
use App\Models\Wan;
use App\Models\WifiTerminal;
use App\Models\Workstation;
use App\Models\Zone;
use App\Services\Graph\PhysicalInfrastructureGraphBuilder;
use App\Services\Graph\SecurityZoneGraphBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\TextRun;

class PhysicalInfrastructureSection implements ReportSection
{
    private const LINK_ENDPOINT_RELATIONS = [
        'peripheralSrc', 'phoneSrc', 'physicalRouterSrc', 'physicalSecurityDeviceSrc', 'physicalServerSrc',
        'physicalSwitchSrc', 'storageDeviceSrc', 'wifiTerminalSrc', 'workstationSrc', 'routerSrc', 'networkSwitchSrc',
        'peripheralDest', 'phoneDest', 'physicalRouterDest', 'physicalSecurityDeviceDest', 'physicalServerDest',
        'physicalSwitchDest', 'storageDeviceDest', 'wifiTerminalDest', 'workstationDest', 'routerDest', 'networkSwitchDest',
    ];

    public function build(Section $section, WordHelper $helper, array $selectedVues): void
    {
        $section->addTitle(trans('cruds.menu.physical_infrastructure.title'), 1);

        $sites = Cartographer::scopedQuery(Site::query())->with('buildings')->get()
            ->sortBy(fn (Site $item) => mb_strtolower((string) $item->name));
        $buildings = Cartographer::scopedQuery(Building::query())
            ->with(['site', 'building', 'buildings', 'bays', 'phones', 'workstations', 'wifiTerminals', 'physicalSwitches', 'physicalRouters', 'peripherals', 'physicalServers', 'storageDevices'])
            ->get()
            ->sortBy(fn (Building $item) => mb_strtolower((string) $item->name));
        $bays = Cartographer::scopedQuery(Bay::query())
            ->with(['site', 'building', 'physicalServers', 'storageDevices', 'peripherals', 'physicalSwitches', 'physicalRouters', 'physicalSecurityDevices'])
            ->get()
            ->sortBy(fn (Bay $item) => mb_strtolower((string) $item->name));
        $zones = Cartographer::scopedQuery(Zone::query())->with(['parentZones', 'childZones', 'buildings', 'adminUsers'])->get()
            ->sortBy(fn (Zone $item) => mb_strtolower((string) $item->name));
        $physicalServers = Cartographer::scopedQuery(PhysicalServer::query())
            ->with(['applications', 'clusters', 'logicalServers', 'site', 'building', 'bay'])
            ->get()
            ->sortBy(fn (PhysicalServer $item) => mb_strtolower((string) $item->name));
        $workstations = Cartographer::scopedQuery(Workstation::query())
            ->with(['entity', 'domain', 'user', 'applications', 'network', 'site', 'building'])
            ->get()
            ->sortBy(fn (Workstation $item) => mb_strtolower((string) $item->name));
        $storageDevices = Cartographer::scopedQuery(StorageDevice::query())->with(['site', 'building', 'bay'])->get()
            ->sortBy(fn (StorageDevice $item) => mb_strtolower((string) $item->name));
        $peripherals = Cartographer::scopedQuery(Peripheral::query())
            ->with(['domain', 'provider', 'applications', 'site', 'building', 'bay'])
            ->get()
            ->sortBy(fn (Peripheral $item) => mb_strtolower((string) $item->name));
        $phones = Cartographer::scopedQuery(Phone::query())->with(['site', 'building'])->get()
            ->sortBy(fn (Phone $item) => mb_strtolower((string) $item->name));
        $physicalSwitches = Cartographer::scopedQuery(PhysicalSwitch::query())->with(['site', 'building', 'bay', 'networkSwitches'])->get()
            ->sortBy(fn (PhysicalSwitch $item) => mb_strtolower((string) $item->name));
        $physicalRouters = Cartographer::scopedQuery(PhysicalRouter::query())->with(['site', 'building', 'bay', 'routers', 'vlans'])->get()
            ->sortBy(fn (PhysicalRouter $item) => mb_strtolower((string) $item->name));
        $wifiTerminals = Cartographer::scopedQuery(WifiTerminal::query())->with(['site', 'building'])->get()
            ->sortBy(fn (WifiTerminal $item) => mb_strtolower((string) $item->name));
        $physicalSecurityDevices = Cartographer::scopedQuery(PhysicalSecurityDevice::query())
            ->with(['securityDevices', 'site', 'building', 'bay'])
            ->get()
            ->sortBy(fn (PhysicalSecurityDevice $item) => mb_strtolower((string) $item->name));
        $physicalLinks = Cartographer::scopedQuery(PhysicalLink::query())->with(self::LINK_ENDPOINT_RELATIONS)->get();
        $wans = Cartographer::scopedQuery(Wan::query())->with(['mans', 'lans'])->get()
            ->sortBy(fn (Wan $item) => mb_strtolower((string) $item->name));
        $mans = Cartographer::scopedQuery(Man::query())->with(['wans', 'parentMan', 'lans'])->get()
            ->sortBy(fn (Man $item) => mb_strtolower((string) $item->name));
        $lans = Cartographer::scopedQuery(Lan::query())->get()
            ->sortBy(fn (Lan $item) => mb_strtolower((string) $item->name));
        $adminUsers = Cartographer::scopedQuery(AdminUser::query())->get();

        $iconResolver = fn (?int $iconId, string $fallback) => $helper->resolveIconPath($iconId, $fallback);
        $graphBuilder = new PhysicalInfrastructureGraphBuilder;
        $securityZoneGraphBuilder = new SecurityZoneGraphBuilder;

        $graphContext = new PhysicalInfrastructureGraphContext(
            $buildings, $bays, $physicalServers, $workstations, $storageDevices, $peripherals,
            $phones, $physicalSwitches, $physicalRouters, $wifiTerminals, $physicalSecurityDevices, $physicalLinks
        );

        $this->addGlobalGraphs($section, $helper, $graphBuilder, $iconResolver, $sites, $graphContext);
        $this->addSites($section, $helper, $graphBuilder, $iconResolver, $sites, $graphContext, $selectedVues);
        $this->addBuildingsSection($section, $helper, $graphBuilder, $iconResolver, $buildings, $graphContext, $selectedVues);
        $this->addBays($section, $helper, $graphBuilder, $iconResolver, $bays, $selectedVues);
        $this->addZones($section, $helper, $securityZoneGraphBuilder, $iconResolver, $zones, $selectedVues);
        $this->addPhysicalServers($section, $helper, $physicalServers, $selectedVues);
        $this->addWorkstations($section, $helper, $workstations, $selectedVues);
        $this->addStorageDevices($section, $helper, $storageDevices, $selectedVues);
        $this->addPeripherals($section, $helper, $peripherals, $selectedVues);
        $this->addPhones($section, $helper, $phones, $selectedVues);
        $this->addPhysicalSwitches($section, $helper, $physicalSwitches, $selectedVues);
        $this->addPhysicalRouters($section, $helper, $physicalRouters, $selectedVues);
        $this->addWifiTerminals($section, $helper, $wifiTerminals, $selectedVues);
        $this->addPhysicalSecurityDevices($section, $helper, $physicalSecurityDevices, $selectedVues);
        $this->addPhysicalLinks($section, $helper, $physicalLinks, $selectedVues);
        $this->addWans($section, $helper, $wans, $selectedVues);
        $this->addMans($section, $helper, $mans, $selectedVues);
        $this->addLans($section, $helper, $lans);
    }

    /**
     * Chapter-level schemas: the full network schema and the full physical-infrastructure schema,
     * covering every site/building/bay/device in scope — no filtering, straight from the common
     * graph service, mirroring the unfiltered interactive screens.
     *
     * @param  Collection<int, Site>  $sites
     */
    private function addGlobalGraphs(Section $section, WordHelper $helper, PhysicalInfrastructureGraphBuilder $graphBuilder, callable $iconResolver, Collection $sites, PhysicalInfrastructureGraphContext $ctx): void
    {
        $locationDot = $graphBuilder->buildLocationDot(
            $sites, $ctx->buildings, $ctx->bays, $ctx->physicalServers, $ctx->workstations, $ctx->storageDevices,
            $ctx->peripherals, $ctx->phones, $ctx->physicalSwitches, $ctx->physicalRouters, $ctx->wifiTerminals,
            $ctx->physicalSecurityDevices, false, ['withHref' => false, 'iconResolver' => $iconResolver]
        );
        $helper->insertGraph($section, $locationDot);

        $networkDot = $graphBuilder->buildConnectivityDot(
            $sites, $ctx->buildings, $ctx->bays, $ctx->physicalServers, $ctx->workstations, $ctx->storageDevices,
            $ctx->peripherals, $ctx->phones, $ctx->physicalSwitches, $ctx->physicalRouters, $ctx->wifiTerminals,
            $ctx->physicalSecurityDevices, $ctx->physicalLinks, false, ['iconResolver' => $iconResolver]
        );
        $helper->insertGraph($section, $networkDot);
    }

    /**
     * @param  Collection<int, Site>  $sites
     * @param  array<int, string>  $selectedVues
     */
    private function addSites(Section $section, WordHelper $helper, PhysicalInfrastructureGraphBuilder $graphBuilder, callable $iconResolver, Collection $sites, PhysicalInfrastructureGraphContext $ctx, array $selectedVues): void
    {
        if ($sites->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.site.title'), 2);

        foreach ($sites as $site) {
            $helper->addBookmarkedTitle($section, $site->getUID(), (string) $site->name, 3);

            $scoped = $this->siteScopedContext($site, $ctx);

            $locationDot = $graphBuilder->buildLocationDot(
                new Collection([$site]), $scoped['buildings'], $scoped['bays'], $scoped['physicalServers'],
                $scoped['workstations'], $scoped['storageDevices'], $scoped['peripherals'], $scoped['phones'],
                $scoped['physicalSwitches'], $scoped['physicalRouters'], $scoped['wifiTerminals'],
                $scoped['physicalSecurityDevices'], false, ['withHref' => false, 'iconResolver' => $iconResolver]
            );
            $helper->insertGraph($section, $locationDot);

            $networkDot = $graphBuilder->buildConnectivityDot(
                new Collection([$site]), $scoped['buildings'], $scoped['bays'], $scoped['physicalServers'],
                $scoped['workstations'], $scoped['storageDevices'], $scoped['peripherals'], $scoped['phones'],
                $scoped['physicalSwitches'], $scoped['physicalRouters'], $scoped['wifiTerminals'],
                $scoped['physicalSecurityDevices'], $ctx->physicalLinks, false, ['iconResolver' => $iconResolver]
            );
            $helper->insertGraph($section, $networkDot);

            $table = $helper->addTable($section, (string) $site->name);

            $helper->addTextRow($table, trans('cruds.site.fields.type'), $site->type);
            $helper->addTextRow($table, trans('cruds.site.fields.attributes'), $this->formatAttributes($site->attributes));
            $helper->addDescriptionCellWithIcon($table, trans('cruds.site.fields.description'), $site->description, $site, '/images/site.png');

            if ($site->buildings->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.site.fields.buildings'), $site->buildings, $selectedVues);
            }
        }
    }

    /**
     * Filters a PhysicalInfrastructureGraphContext down to everything belonging to a single Site
     * (buildings of any depth under it, their bays, and every device attached to the site itself,
     * one of its buildings, or one of its bays).
     *
     * @return array{buildings: Collection<int, Building>, bays: Collection<int, Bay>, physicalServers: Collection<int, PhysicalServer>, workstations: Collection<int, Workstation>, storageDevices: Collection<int, StorageDevice>, peripherals: Collection<int, Peripheral>, phones: Collection<int, Phone>, physicalSwitches: Collection<int, PhysicalSwitch>, physicalRouters: Collection<int, PhysicalRouter>, wifiTerminals: Collection<int, WifiTerminal>, physicalSecurityDevices: Collection<int, PhysicalSecurityDevice>}
     */
    private function siteScopedContext(Site $site, PhysicalInfrastructureGraphContext $ctx): array
    {
        $buildings = $ctx->buildings->where('site_id', $site->id);
        $buildingIds = $buildings->pluck('id');

        $bays = $ctx->bays->filter(fn (Bay $bay) => $bay->building_id !== null
            ? $buildingIds->contains($bay->building_id)
            : $bay->site_id === $site->id);
        $bayIds = $bays->pluck('id');

        $matches = fn (?int $buildingId, ?int $bayId, ?int $siteId) => $buildingId !== null
            ? $buildingIds->contains($buildingId)
            : ($bayId !== null ? $bayIds->contains($bayId) : $siteId === $site->id);

        return [
            'buildings' => $buildings,
            'bays' => $bays,
            'physicalServers' => $ctx->physicalServers->filter(fn (PhysicalServer $d) => $matches($d->building_id, $d->bay_id, $d->site_id)),
            'workstations' => $ctx->workstations->filter(fn (Workstation $d) => $matches($d->building_id, null, $d->site_id)),
            'storageDevices' => $ctx->storageDevices->filter(fn (StorageDevice $d) => $matches($d->building_id, $d->bay_id, $d->site_id)),
            'peripherals' => $ctx->peripherals->filter(fn (Peripheral $d) => $matches($d->building_id, $d->bay_id, $d->site_id)),
            'phones' => $ctx->phones->filter(fn (Phone $d) => $matches($d->building_id, null, $d->site_id)),
            'physicalSwitches' => $ctx->physicalSwitches->filter(fn (PhysicalSwitch $d) => $matches($d->building_id, $d->bay_id, $d->site_id)),
            'physicalRouters' => $ctx->physicalRouters->filter(fn (PhysicalRouter $d) => $matches($d->building_id, $d->bay_id, $d->site_id)),
            'wifiTerminals' => $ctx->wifiTerminals->filter(fn (WifiTerminal $d) => $matches($d->building_id, null, $d->site_id)),
            'physicalSecurityDevices' => $ctx->physicalSecurityDevices->filter(fn (PhysicalSecurityDevice $d) => $matches($d->building_id, $d->bay_id, $d->site_id)),
        ];
    }

    /**
     * Root buildings (schemas gated on having children or content) then child buildings (schema
     * always attempted, network schema never shown) — both groups alphabetical, under a single
     * "Building" subtitle.
     *
     * @param  Collection<int, Building>  $buildings
     * @param  array<int, string>  $selectedVues
     */
    private function addBuildingsSection(Section $section, WordHelper $helper, PhysicalInfrastructureGraphBuilder $graphBuilder, callable $iconResolver, Collection $buildings, PhysicalInfrastructureGraphContext $ctx, array $selectedVues): void
    {
        if ($buildings->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.building.title'), 2);

        $rootBuildings = $buildings->whereNull('building_id')
            ->sortBy(fn (Building $item) => mb_strtolower((string) $item->name));
        $childBuildings = $buildings->whereNotNull('building_id')
            ->sortBy(fn (Building $item) => mb_strtolower((string) $item->name));

        foreach ($rootBuildings as $building) {
            $this->addBuildingEntry($section, $helper, $graphBuilder, $iconResolver, $building, $ctx, $selectedVues, includeNetworkGraph: true, onlyIfHasContent: true);
        }

        foreach ($childBuildings as $building) {
            $this->addBuildingEntry($section, $helper, $graphBuilder, $iconResolver, $building, $ctx, $selectedVues, includeNetworkGraph: false, onlyIfHasContent: false);
        }
    }

    /**
     * @param  array<int, string>  $selectedVues
     */
    private function addBuildingEntry(Section $section, WordHelper $helper, PhysicalInfrastructureGraphBuilder $graphBuilder, callable $iconResolver, Building $building, PhysicalInfrastructureGraphContext $ctx, array $selectedVues, bool $includeNetworkGraph, bool $onlyIfHasContent): void
    {
        $helper->addBookmarkedTitle($section, $building->getUID(), (string) $building->name, 3);

        $scoped = $this->buildingSubtreeContext($building, $ctx);
        $hasContent = $scoped['buildings']->count() > 1
            || $scoped['bays']->isNotEmpty()
            || $scoped['physicalServers']->isNotEmpty()
            || $scoped['workstations']->isNotEmpty()
            || $scoped['storageDevices']->isNotEmpty()
            || $scoped['peripherals']->isNotEmpty()
            || $scoped['phones']->isNotEmpty()
            || $scoped['physicalSwitches']->isNotEmpty()
            || $scoped['physicalRouters']->isNotEmpty()
            || $scoped['wifiTerminals']->isNotEmpty()
            || $scoped['physicalSecurityDevices']->isNotEmpty();

        if ($hasContent || ! $onlyIfHasContent) {

            $locationDot = $graphBuilder->buildLocationDot(
                new Collection, $scoped['buildings'], $scoped['bays'], $scoped['physicalServers'],
                $scoped['workstations'], $scoped['storageDevices'], $scoped['peripherals'], $scoped['phones'],
                $scoped['physicalSwitches'], $scoped['physicalRouters'], $scoped['wifiTerminals'],
                $scoped['physicalSecurityDevices'], true, ['withHref' => false, 'iconResolver' => $iconResolver]
            );
            $helper->insertGraph($section, $locationDot);

            if ($includeNetworkGraph && $building->site !== null) {
                $networkDot = $graphBuilder->buildConnectivityDot(
                    new Collection([$building->site]), $scoped['buildings'], $scoped['bays'], $scoped['physicalServers'],
                    $scoped['workstations'], $scoped['storageDevices'], $scoped['peripherals'], $scoped['phones'],
                    $scoped['physicalSwitches'], $scoped['physicalRouters'], $scoped['wifiTerminals'],
                    $scoped['physicalSecurityDevices'], $ctx->physicalLinks, false, ['iconResolver' => $iconResolver]
                );
                $helper->insertGraph($section, $networkDot);
            }

        }

        $table = $helper->addTable($section, (string) $building->name);

        $helper->addTextRow($table, trans('cruds.building.fields.type'), $building->type);
        $helper->addTextRow($table, trans('cruds.building.fields.attributes'), $this->formatAttributes($building->attributes));
        $helper->addDescriptionCellWithIcon($table, trans('cruds.building.fields.description'), $building->description, $building, '/images/building.png');

        if ($building->site !== null) {
            $run = $helper->addTextRunRow($table, trans('cruds.building.fields.site'));
            $helper->linkOrText($run, $building->site, $selectedVues);
        }

        if ($building->building !== null) {
            $run = $helper->addTextRunRow($table, trans('cruds.building.fields.parent'));
            $helper->linkOrText($run, $building->building, $selectedVues);
        }

        if ($building->buildings->isNotEmpty()) {
            $helper->addLinkListRow($table, trans('cruds.building.fields.children'), $building->buildings, $selectedVues);
        }

        if ($building->bays->isNotEmpty()) {
            $helper->addLinkListRow($table, trans('cruds.building.fields.bays'), $building->bays, $selectedVues);
        }
    }

    /**
     * Filters a PhysicalInfrastructureGraphContext down to a Building's own subtree: itself, every
     * descendant building (recursively, within the same Site), their bays, and every device attached
     * to one of those buildings or bays. Deliberately excludes bare site-attached items (a
     * building's own schema shouldn't reach back up to its Site).
     *
     * @return array{buildings: Collection<int, Building>, bays: Collection<int, Bay>, physicalServers: Collection<int, PhysicalServer>, workstations: Collection<int, Workstation>, storageDevices: Collection<int, StorageDevice>, peripherals: Collection<int, Peripheral>, phones: Collection<int, Phone>, physicalSwitches: Collection<int, PhysicalSwitch>, physicalRouters: Collection<int, PhysicalRouter>, wifiTerminals: Collection<int, WifiTerminal>, physicalSecurityDevices: Collection<int, PhysicalSecurityDevice>}
     */
    private function buildingSubtreeContext(Building $building, PhysicalInfrastructureGraphContext $ctx): array
    {
        $siteBuildings = $ctx->buildings->where('site_id', $building->site_id);
        $subtree = $this->buildingSubtree($building, $siteBuildings);
        $subtreeIds = $subtree->pluck('id');

        $bays = $ctx->bays->whereIn('building_id', $subtreeIds);
        $bayIds = $bays->pluck('id');

        $inSubtree = fn (?int $buildingId, ?int $bayId) => ($buildingId !== null && $subtreeIds->contains($buildingId))
            || ($bayId !== null && $bayIds->contains($bayId));
        $inSubtreeNoBay = fn (?int $buildingId) => $buildingId !== null && $subtreeIds->contains($buildingId);

        return [
            'buildings' => $subtree,
            'bays' => $bays,
            'physicalServers' => $ctx->physicalServers->filter(fn (PhysicalServer $d) => $inSubtree($d->building_id, $d->bay_id)),
            'workstations' => $ctx->workstations->filter(fn (Workstation $d) => $inSubtreeNoBay($d->building_id)),
            'storageDevices' => $ctx->storageDevices->filter(fn (StorageDevice $d) => $inSubtree($d->building_id, $d->bay_id)),
            'peripherals' => $ctx->peripherals->filter(fn (Peripheral $d) => $inSubtree($d->building_id, $d->bay_id)),
            'phones' => $ctx->phones->filter(fn (Phone $d) => $inSubtreeNoBay($d->building_id)),
            'physicalSwitches' => $ctx->physicalSwitches->filter(fn (PhysicalSwitch $d) => $inSubtree($d->building_id, $d->bay_id)),
            'physicalRouters' => $ctx->physicalRouters->filter(fn (PhysicalRouter $d) => $inSubtree($d->building_id, $d->bay_id)),
            'wifiTerminals' => $ctx->wifiTerminals->filter(fn (WifiTerminal $d) => $inSubtreeNoBay($d->building_id)),
            'physicalSecurityDevices' => $ctx->physicalSecurityDevices->filter(fn (PhysicalSecurityDevice $d) => $inSubtree($d->building_id, $d->bay_id)),
        ];
    }

    /**
     * @param  Collection<int, Building>  $siteBuildings
     * @return Collection<int, Building>
     */
    private function buildingSubtree(Building $root, Collection $siteBuildings): Collection
    {
        $subtree = new Collection([$root]);
        foreach ($siteBuildings->where('building_id', $root->id) as $child) {
            $subtree = $subtree->concat($this->buildingSubtree($child, $siteBuildings));
        }

        return $subtree;
    }

    /**
     * @param  Collection<int, Bay>  $bays
     * @param  array<int, string>  $selectedVues
     */
    private function addBays(Section $section, WordHelper $helper, PhysicalInfrastructureGraphBuilder $graphBuilder, callable $iconResolver, Collection $bays, array $selectedVues): void
    {
        if ($bays->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.bay.title'), 2);

        foreach ($bays as $bay) {
            $helper->addBookmarkedTitle($section, $bay->getUID(), (string) $bay->name, 3);

            $hasEquipment = $bay->physicalServers->isNotEmpty() || $bay->storageDevices->isNotEmpty()
                || $bay->peripherals->isNotEmpty() || $bay->physicalSwitches->isNotEmpty()
                || $bay->physicalRouters->isNotEmpty() || $bay->physicalSecurityDevices->isNotEmpty();

            if ($hasEquipment) {
                $locationDot = $graphBuilder->buildLocationDot(
                    new Collection, new Collection, new Collection([$bay]), $bay->physicalServers, new Collection,
                    $bay->storageDevices, $bay->peripherals, new Collection, $bay->physicalSwitches, $bay->physicalRouters,
                    new Collection, $bay->physicalSecurityDevices, true, ['withHref' => false, 'iconResolver' => $iconResolver]
                );
                $helper->insertGraph($section, $locationDot);
            }

            $table = $helper->addTable($section, (string) $bay->name);

            $helper->addHTMLRow($table, trans('cruds.bay.fields.description'), $bay->description);

            if ($bay->site !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.bay.fields.site'));
                $helper->linkOrText($run, $bay->site, $selectedVues);
            }

            if ($bay->building !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.bay.fields.building'));
                $helper->linkOrText($run, $bay->building, $selectedVues);
            }

            if ($bay->physicalServers->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.physicalServer.title'), $bay->physicalServers, $selectedVues);
            }

            if ($bay->storageDevices->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.storageDevice.title'), $bay->storageDevices, $selectedVues);
            }

            if ($bay->peripherals->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.peripheral.title'), $bay->peripherals, $selectedVues);
            }

            if ($bay->physicalSwitches->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.physicalSwitch.title'), $bay->physicalSwitches, $selectedVues);
            }

            if ($bay->physicalRouters->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.physicalRouter.title'), $bay->physicalRouters, $selectedVues);
            }

            if ($bay->physicalSecurityDevices->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.physicalSecurityDevice.title'), $bay->physicalSecurityDevices, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, Zone>  $zones
     * @param  array<int, string>  $selectedVues
     */
    private function addZones(Section $section, WordHelper $helper, SecurityZoneGraphBuilder $securityZoneGraphBuilder, callable $iconResolver, Collection $zones, array $selectedVues): void
    {
        if ($zones->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.zone.title'), 2);

        foreach ($zones as $zone) {
            $helper->addBookmarkedTitle($section, $zone->getUID(), (string) $zone->name, 3);

            if ($zone->childZones->isNotEmpty()) {
                $scopedZones = (new Collection([$zone]))->concat($zone->childZones);
                $scopedBuildings = $scopedZones->flatMap(fn (Zone $z) => $z->buildings)->unique('id')->values();
                $scopedAdminUsers = $scopedZones->flatMap(fn (Zone $z) => $z->adminUsers)->unique('id')->values();

                $zoneDot = $securityZoneGraphBuilder->buildDot($scopedZones, $scopedBuildings, $scopedAdminUsers, [
                    'withHref' => false,
                    'iconResolver' => $iconResolver,
                ]);
                $helper->insertGraph($section, $zoneDot);
            }

            $table = $helper->addTable($section, (string) $zone->name);

            $helper->addTextRow($table, trans('cruds.zone.fields.type'), $zone->type);
            $helper->addTextRow($table, trans('cruds.zone.fields.attributes'), $this->formatAttributes($zone->attributes));
            $helper->addHTMLRow($table, trans('cruds.zone.fields.description'), $zone->description);

            if ($zone->parentZones->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.zone.fields.parent_zones'), $zone->parentZones, $selectedVues);
            }

            if ($zone->childZones->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.zone.fields.child_zones'), $zone->childZones, $selectedVues);
            }

            if ($zone->buildings->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.zone.fields.buildings'), $zone->buildings, $selectedVues);
            }

            if ($zone->adminUsers->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.zone.fields.admin_users'), $zone->adminUsers, $selectedVues, fn (Model&HasUniqueIdentifierContract $adminUser) => (string) $adminUser->getAttribute('user_id'));
            }
        }
    }

    /**
     * @param  Collection<int, PhysicalServer>  $physicalServers
     * @param  array<int, string>  $selectedVues
     */
    private function addPhysicalServers(Section $section, WordHelper $helper, Collection $physicalServers, array $selectedVues): void
    {
        if ($physicalServers->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.physicalServer.title'), 2);

        foreach ($physicalServers as $physicalServer) {
            $helper->addBookmarkedTitle($section, $physicalServer->getUID(), (string) $physicalServer->name, 3);
            $table = $helper->addTable($section, (string) $physicalServer->name);

            $helper->addTextRow($table, trans('cruds.physicalServer.fields.type'), $physicalServer->type);
            $helper->addDescriptionCellWithIcon($table, trans('cruds.physicalServer.fields.description'), $physicalServer->description, $physicalServer, '/images/server.png');
            $helper->addHTMLRow($table, trans('cruds.physicalServer.fields.cpu'), $physicalServer->cpu);
            $helper->addHTMLRow($table, trans('cruds.physicalServer.fields.memory'), $physicalServer->memory);
            $helper->addHTMLRow($table, trans('cruds.physicalServer.fields.disk'), $physicalServer->disk);
            $helper->addHTMLRow($table, trans('cruds.physicalServer.fields.disk_used'), $physicalServer->disk_used);
            $helper->addHTMLRow($table, trans('cruds.physicalServer.fields.configuration'), $physicalServer->configuration);

            if ($physicalServer->applications->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.physicalServer.fields.applications'), $physicalServer->applications, $selectedVues);
            }

            $helper->addHTMLRow($table, trans('cruds.physicalServer.fields.operating_system'), $physicalServer->operating_system);
            $helper->addTextRow($table, trans('cruds.lifecycle.fields.install_date'), $physicalServer->install_date);
            $helper->addTextRow($table, trans('cruds.physicalServer.fields.update_date'), $physicalServer->update_date);
            $helper->addTextRow($table, trans('cruds.physicalServer.fields.address_ip'), $physicalServer->address_ip);
            $helper->addTextRow($table, trans('cruds.physicalServer.fields.responsible'), $physicalServer->responsible);

            if ($physicalServer->clusters->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.physicalServer.fields.clusters'), $physicalServer->clusters, $selectedVues);
            }

            if ($physicalServer->logicalServers->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.physicalServer.fields.logical_servers'), $physicalServer->logicalServers, $selectedVues);
            }

            if ($physicalServer->site !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.physicalServer.fields.site'));
                $helper->linkOrText($run, $physicalServer->site, $selectedVues);
            }

            if ($physicalServer->building !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.physicalServer.fields.building'));
                $helper->linkOrText($run, $physicalServer->building, $selectedVues);
            }

            if ($physicalServer->bay !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.physicalServer.fields.bay'));
                $helper->linkOrText($run, $physicalServer->bay, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, Workstation>  $workstations
     * @param  array<int, string>  $selectedVues
     */
    private function addWorkstations(Section $section, WordHelper $helper, Collection $workstations, array $selectedVues): void
    {
        if ($workstations->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.workstation.title'), 2);

        foreach ($workstations as $workstation) {
            $helper->addBookmarkedTitle($section, $workstation->getUID(), (string) $workstation->name, 3);
            $table = $helper->addTable($section, (string) $workstation->name);

            $helper->addTextRow($table, trans('cruds.workstation.fields.type'), $workstation->type);
            $helper->addTextRow($table, trans('cruds.workstation.fields.status'), $workstation->status);
            $helper->addDescriptionCellWithIcon($table, trans('cruds.workstation.fields.description'), $workstation->description, $workstation, '/images/workstation.png');
            $helper->addTextRow($table, trans('cruds.workstation.fields.manufacturer'), $workstation->manufacturer);
            $helper->addTextRow($table, trans('cruds.workstation.fields.model'), $workstation->model);
            $helper->addTextRow($table, trans('cruds.workstation.fields.serial_number'), $workstation->serial_number);
            $helper->addTextRow($table, trans('cruds.workstation.fields.cpu'), $workstation->cpu);
            $helper->addTextRow($table, trans('cruds.workstation.fields.memory'), $workstation->memory);
            $helper->addTextRow($table, trans('cruds.workstation.fields.disk'), (string) $workstation->disk);
            $helper->addTextRow($table, trans('cruds.workstation.fields.operating_system'), $workstation->operating_system);

            if ($workstation->entity !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.workstation.fields.entity'));
                $helper->linkOrText($run, $workstation->entity, $selectedVues);
            }

            if ($workstation->domain !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.workstation.fields.domain'));
                $helper->linkOrText($run, $workstation->domain, $selectedVues);
            }

            if ($workstation->user !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.workstation.fields.user'));
                $helper->linkOrText($run, $workstation->user, $selectedVues, (string) $workstation->user->getAttribute('user_id'));
            }

            $helper->addTextRow($table, trans('cruds.workstation.fields.other_user'), $workstation->other_user);

            if ($workstation->applications->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.workstation.fields.applications'), $workstation->applications, $selectedVues);
            }

            if ($workstation->network !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.workstation.fields.network'));
                $helper->linkOrText($run, $workstation->network, $selectedVues);
            }

            $helper->addTextRow($table, trans('cruds.workstation.fields.address_ip'), $workstation->address_ip);
            $helper->addHTMLRow($table, trans('cruds.workstation.fields.mac_address'), $workstation->mac_address);
            $helper->addHTMLRow($table, trans('cruds.workstation.fields.network_port_type'), $workstation->network_port_type);

            if ($workstation->site !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.workstation.fields.site'));
                $helper->linkOrText($run, $workstation->site, $selectedVues);
            }

            if ($workstation->building !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.workstation.fields.building'));
                $helper->linkOrText($run, $workstation->building, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, StorageDevice>  $storageDevices
     * @param  array<int, string>  $selectedVues
     */
    private function addStorageDevices(Section $section, WordHelper $helper, Collection $storageDevices, array $selectedVues): void
    {
        if ($storageDevices->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.storageDevice.title'), 2);

        foreach ($storageDevices as $storageDevice) {
            $helper->addBookmarkedTitle($section, $storageDevice->getUID(), (string) $storageDevice->name, 3);
            $table = $helper->addTable($section, (string) $storageDevice->name);

            $helper->addTextRow($table, trans('cruds.storageDevice.fields.type'), $storageDevice->type);
            $helper->addHTMLRow($table, trans('cruds.storageDevice.fields.description'), $storageDevice->description);
            $helper->addTextRow($table, trans('cruds.storageDevice.fields.address_ip'), $storageDevice->address_ip);

            if ($storageDevice->site !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.storageDevice.fields.site'));
                $helper->linkOrText($run, $storageDevice->site, $selectedVues);
            }

            if ($storageDevice->building !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.storageDevice.fields.building'));
                $helper->linkOrText($run, $storageDevice->building, $selectedVues);
            }

            if ($storageDevice->bay !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.storageDevice.fields.bay'));
                $helper->linkOrText($run, $storageDevice->bay, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, Peripheral>  $peripherals
     * @param  array<int, string>  $selectedVues
     */
    private function addPeripherals(Section $section, WordHelper $helper, Collection $peripherals, array $selectedVues): void
    {
        if ($peripherals->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.peripheral.title'), 2);

        foreach ($peripherals as $peripheral) {
            $helper->addBookmarkedTitle($section, $peripheral->getUID(), (string) $peripheral->name, 3);
            $table = $helper->addTable($section, (string) $peripheral->name);

            if ($peripheral->domain !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.peripheral.fields.domain'));
                $helper->linkOrText($run, $peripheral->domain, $selectedVues);
            }

            $helper->addTextRow($table, trans('cruds.peripheral.fields.type'), $peripheral->type);
            $helper->addDescriptionCellWithIcon($table, trans('cruds.peripheral.fields.description'), $peripheral->description, $peripheral, '/images/peripheral.png');

            if ($peripheral->provider !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.peripheral.fields.provider'));
                $helper->linkOrText($run, $peripheral->provider, $selectedVues);
            }

            $helper->addTextRow($table, trans('cruds.peripheral.fields.responsible'), $peripheral->responsible);

            if ($peripheral->applications->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.peripheral.fields.applications'), $peripheral->applications, $selectedVues);
            }

            $helper->addTextRow($table, trans('cruds.peripheral.fields.vendor'), $peripheral->vendor);
            $helper->addTextRow($table, trans('cruds.peripheral.fields.product'), $peripheral->product);
            $helper->addTextRow($table, trans('cruds.peripheral.fields.version'), $peripheral->version);
            $helper->addTextRow($table, trans('cruds.peripheral.fields.address_ip'), $peripheral->address_ip);

            if ($peripheral->site !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.peripheral.fields.site'));
                $helper->linkOrText($run, $peripheral->site, $selectedVues);
            }

            if ($peripheral->building !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.peripheral.fields.building'));
                $helper->linkOrText($run, $peripheral->building, $selectedVues);
            }

            if ($peripheral->bay !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.peripheral.fields.bay'));
                $helper->linkOrText($run, $peripheral->bay, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, Phone>  $phones
     * @param  array<int, string>  $selectedVues
     */
    private function addPhones(Section $section, WordHelper $helper, Collection $phones, array $selectedVues): void
    {
        if ($phones->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.phone.title'), 2);

        foreach ($phones as $phone) {
            $helper->addBookmarkedTitle($section, $phone->getUID(), (string) $phone->name, 3);
            $table = $helper->addTable($section, (string) $phone->name);

            $helper->addTextRow($table, trans('cruds.phone.fields.type'), $phone->type);
            $helper->addHTMLRow($table, trans('cruds.phone.fields.description'), $phone->description);
            $helper->addTextRow($table, trans('cruds.phone.fields.address_ip'), $phone->address_ip);

            if ($phone->site !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.phone.fields.site'));
                $helper->linkOrText($run, $phone->site, $selectedVues);
            }

            if ($phone->building !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.phone.fields.building'));
                $helper->linkOrText($run, $phone->building, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, PhysicalSwitch>  $physicalSwitches
     * @param  array<int, string>  $selectedVues
     */
    private function addPhysicalSwitches(Section $section, WordHelper $helper, Collection $physicalSwitches, array $selectedVues): void
    {
        if ($physicalSwitches->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.physicalSwitch.title'), 2);

        foreach ($physicalSwitches as $physicalSwitch) {
            $helper->addBookmarkedTitle($section, $physicalSwitch->getUID(), (string) $physicalSwitch->name, 3);
            $table = $helper->addTable($section, (string) $physicalSwitch->name);

            $helper->addTextRow($table, trans('cruds.physicalSwitch.fields.type'), $physicalSwitch->type);
            $helper->addDescriptionCellWithIcon($table, trans('cruds.physicalSwitch.fields.description'), $physicalSwitch->description, $physicalSwitch, '/images/switch.png');

            if ($physicalSwitch->site !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.physicalSwitch.fields.site'));
                $helper->linkOrText($run, $physicalSwitch->site, $selectedVues);
            }

            if ($physicalSwitch->building !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.physicalSwitch.fields.building'));
                $helper->linkOrText($run, $physicalSwitch->building, $selectedVues);
            }

            if ($physicalSwitch->bay !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.physicalSwitch.fields.bay'));
                $helper->linkOrText($run, $physicalSwitch->bay, $selectedVues);
            }

            if ($physicalSwitch->networkSwitches->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.physicalSwitch.fields.network_switches'), $physicalSwitch->networkSwitches, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, PhysicalRouter>  $physicalRouters
     * @param  array<int, string>  $selectedVues
     */
    private function addPhysicalRouters(Section $section, WordHelper $helper, Collection $physicalRouters, array $selectedVues): void
    {
        if ($physicalRouters->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.physicalRouter.title'), 2);

        foreach ($physicalRouters as $physicalRouter) {
            $helper->addBookmarkedTitle($section, $physicalRouter->getUID(), (string) $physicalRouter->name, 3);
            $table = $helper->addTable($section, (string) $physicalRouter->name);

            $helper->addTextRow($table, trans('cruds.physicalRouter.fields.type'), $physicalRouter->type);
            $helper->addHTMLRow($table, trans('cruds.physicalRouter.fields.description'), $physicalRouter->description);

            if ($physicalRouter->site !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.physicalRouter.fields.site'));
                $helper->linkOrText($run, $physicalRouter->site, $selectedVues);
            }

            if ($physicalRouter->building !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.physicalRouter.fields.building'));
                $helper->linkOrText($run, $physicalRouter->building, $selectedVues);
            }

            if ($physicalRouter->bay !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.physicalRouter.fields.bay'));
                $helper->linkOrText($run, $physicalRouter->bay, $selectedVues);
            }

            if ($physicalRouter->routers->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.physicalRouter.fields.routers'), $physicalRouter->routers, $selectedVues);
            }

            if ($physicalRouter->vlans->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.physicalRouter.fields.vlan'), $physicalRouter->vlans, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, WifiTerminal>  $wifiTerminals
     * @param  array<int, string>  $selectedVues
     */
    private function addWifiTerminals(Section $section, WordHelper $helper, Collection $wifiTerminals, array $selectedVues): void
    {
        if ($wifiTerminals->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.wifiTerminal.title'), 2);

        foreach ($wifiTerminals as $wifiTerminal) {
            $helper->addBookmarkedTitle($section, $wifiTerminal->getUID(), (string) $wifiTerminal->name, 3);
            $table = $helper->addTable($section, (string) $wifiTerminal->name);

            $helper->addTextRow($table, trans('cruds.wifiTerminal.fields.type'), $wifiTerminal->type);
            $helper->addHTMLRow($table, trans('cruds.wifiTerminal.fields.description'), $wifiTerminal->description);
            $helper->addTextRow($table, trans('cruds.wifiTerminal.fields.address_ip'), $wifiTerminal->address_ip);
            $helper->addTextRow($table, trans('cruds.wifiTerminal.fields.vendor'), $wifiTerminal->vendor);
            $helper->addTextRow($table, trans('cruds.wifiTerminal.fields.product'), $wifiTerminal->product);
            $helper->addTextRow($table, trans('cruds.wifiTerminal.fields.version'), $wifiTerminal->version);

            if ($wifiTerminal->site !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.wifiTerminal.fields.site'));
                $helper->linkOrText($run, $wifiTerminal->site, $selectedVues);
            }

            if ($wifiTerminal->building !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.wifiTerminal.fields.building'));
                $helper->linkOrText($run, $wifiTerminal->building, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, PhysicalSecurityDevice>  $physicalSecurityDevices
     * @param  array<int, string>  $selectedVues
     */
    private function addPhysicalSecurityDevices(Section $section, WordHelper $helper, Collection $physicalSecurityDevices, array $selectedVues): void
    {
        if ($physicalSecurityDevices->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.physicalSecurityDevice.title'), 2);

        foreach ($physicalSecurityDevices as $physicalSecurityDevice) {
            $helper->addBookmarkedTitle($section, $physicalSecurityDevice->getUID(), (string) $physicalSecurityDevice->name, 3);
            $table = $helper->addTable($section, (string) $physicalSecurityDevice->name);

            $helper->addTextRow($table, trans('cruds.physicalSecurityDevice.fields.type'), $physicalSecurityDevice->type);
            $helper->addTextRow($table, trans('cruds.physicalSecurityDevice.fields.attributes'), $this->formatAttributes($physicalSecurityDevice->attributes));
            $helper->addDescriptionCellWithIcon($table, trans('cruds.physicalSecurityDevice.fields.description'), $physicalSecurityDevice->description, $physicalSecurityDevice, '/images/securitydevice.png');
            $helper->addTextRow($table, trans('cruds.physicalSecurityDevice.fields.address_ip'), $physicalSecurityDevice->address_ip);

            if ($physicalSecurityDevice->securityDevices->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.physicalSecurityDevice.fields.security_devices'), $physicalSecurityDevice->securityDevices, $selectedVues);
            }

            if ($physicalSecurityDevice->site !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.physicalSecurityDevice.fields.site'));
                $helper->linkOrText($run, $physicalSecurityDevice->site, $selectedVues);
            }

            if ($physicalSecurityDevice->building !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.physicalSecurityDevice.fields.building'));
                $helper->linkOrText($run, $physicalSecurityDevice->building, $selectedVues);
            }

            if ($physicalSecurityDevice->bay !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.physicalSecurityDevice.fields.bay'));
                $helper->linkOrText($run, $physicalSecurityDevice->bay, $selectedVues);
            }
        }
    }

    /**
     * PhysicalLink has no `name`/`getUID()` (not a HasUniqueIdentifierContract model) — uses an ad
     * hoc bookmark ('PHYSICAL_LINK_'.$link->id), per the vigilance note from Étape 2.1.
     *
     * @param  Collection<int, PhysicalLink>  $physicalLinks
     * @param  array<int, string>  $selectedVues
     */
    private function addPhysicalLinks(Section $section, WordHelper $helper, Collection $physicalLinks, array $selectedVues): void
    {
        if ($physicalLinks->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.physicalLink.title'), 2);

        foreach ($physicalLinks as $link) {
            $bookmark = 'PHYSICAL_LINK_'.$link->id;
            $title = trans('cruds.physicalLink.title').' #'.$link->id;
            $helper->addBookmarkedTitle($section, $bookmark, $title, 3);
            $table = $helper->addTable($section, $title);

            $helper->addTextRow($table, trans('cruds.physicalLink.fields.type'), $link->type);
            $helper->addTextRow($table, trans('global.color'), $link->color);
            $helper->addTextRow($table, trans('cruds.physicalLink.fields.attributes'), $this->formatAttributes($link->attributes));

            $srcRun = $helper->addTextRunRow($table, trans('cruds.physicalLink.fields.src'));
            $this->addLinkEndpointRun($srcRun, $helper, $link, 'Src', $selectedVues);
            $helper->addTextRow($table, trans('cruds.physicalLink.fields.src_port'), $link->src_port);

            $destRun = $helper->addTextRunRow($table, trans('cruds.physicalLink.fields.dest'));
            $this->addLinkEndpointRun($destRun, $helper, $link, 'Dest', $selectedVues);
            $helper->addTextRow($table, trans('cruds.physicalLink.fields.dest_port'), $link->dest_port);

            $helper->addHTMLRow($table, trans('cruds.physicalLink.fields.description'), $link->description);
        }
    }

    /**
     * @param  array<int, string>  $selectedVues
     */
    private function addLinkEndpointRun(TextRun $run, WordHelper $helper, PhysicalLink $link, string $suffix, array $selectedVues): void
    {
        $relations = $suffix === 'Src'
            ? ['peripheralSrc', 'phoneSrc', 'physicalRouterSrc', 'physicalSecurityDeviceSrc', 'physicalServerSrc', 'physicalSwitchSrc', 'storageDeviceSrc', 'wifiTerminalSrc', 'workstationSrc', 'routerSrc', 'networkSwitchSrc']
            : ['peripheralDest', 'phoneDest', 'physicalRouterDest', 'physicalSecurityDeviceDest', 'physicalServerDest', 'physicalSwitchDest', 'storageDeviceDest', 'wifiTerminalDest', 'workstationDest', 'routerDest', 'networkSwitchDest'];

        foreach ($relations as $relation) {
            $endpoint = $link->{$relation};
            if ($endpoint !== null) {
                $helper->linkOrText($run, $endpoint, $selectedVues);

                return;
            }
        }
    }

    /**
     * @param  Collection<int, Wan>  $wans
     * @param  array<int, string>  $selectedVues
     */
    private function addWans(Section $section, WordHelper $helper, Collection $wans, array $selectedVues): void
    {
        if ($wans->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.wan.title'), 2);

        foreach ($wans as $wan) {
            $helper->addBookmarkedTitle($section, $wan->getUID(), (string) $wan->name, 3);
            $table = $helper->addTable($section, (string) $wan->name);

            if ($wan->mans->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.wan.fields.mans'), $wan->mans, $selectedVues);
            }

            if ($wan->lans->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.wan.fields.lans'), $wan->lans, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, Man>  $mans
     * @param  array<int, string>  $selectedVues
     */
    private function addMans(Section $section, WordHelper $helper, Collection $mans, array $selectedVues): void
    {
        if ($mans->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.man.title'), 2);

        foreach ($mans as $man) {
            $helper->addBookmarkedTitle($section, $man->getUID(), (string) $man->name, 3);
            $table = $helper->addTable($section, (string) $man->name);

            $helper->addHTMLRow($table, trans('cruds.man.fields.description'), $man->description);

            if ($man->wans->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.man.fields.wans'), $man->wans, $selectedVues);
            }

            if ($man->parentMan !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.man.fields.parent_man'));
                $helper->linkOrText($run, $man->parentMan, $selectedVues);
            }

            if ($man->lans->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.man.fields.lans'), $man->lans, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, Lan>  $lans
     */
    private function addLans(Section $section, WordHelper $helper, Collection $lans): void
    {
        if ($lans->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.lan.title'), 2);

        foreach ($lans as $lan) {
            $helper->addBookmarkedTitle($section, $lan->getUID(), (string) $lan->name, 3);
            $table = $helper->addTable($section, (string) $lan->name);

            $helper->addHTMLRow($table, trans('cruds.lan.fields.description'), $lan->description);
        }
    }

    private function formatAttributes(?string $attributes): ?string
    {
        if ($attributes === null || trim($attributes) === '') {
            return $attributes;
        }

        return implode(', ', array_filter(explode(' ', $attributes)));
    }
}
