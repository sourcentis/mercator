<?php

use App\Models\AdminUser;
use App\Models\Bay;
use App\Models\Building;
use App\Models\Peripheral;
use App\Models\PhysicalLink;
use App\Models\PhysicalServer;
use App\Models\Site;
use App\Models\StorageDevice;
use App\Models\User;
use App\Models\Workstation;
use App\Models\Zone;
use App\Services\Report\PhysicalInfrastructureSection;
use App\Services\Report\WordHelper;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    $this->actingAs(User::query()->where('login', 'admin@admin.com')->first());
});

/**
 * @return array{0: string, 1: string, 2: array<int, int>} [document.xml, document.xml.rels, media PNG sizes]
 */
function renderPhysicalInfrastructureSectionXml(array $selectedVues = ['6']): array
{
    $helper = new WordHelper;
    $phpWord = $helper->newDocument();
    $section = $phpWord->addSection();

    (new PhysicalInfrastructureSection)->build($section, $helper, $selectedVues);

    $path = tempnam(sys_get_temp_dir(), 'docx');
    WordHelper::saveDocx($phpWord, $path);
    $helper->cleanupTempFiles();

    $zip = new ZipArchive;
    $zip->open($path);
    $xml = $zip->getFromName('word/document.xml');
    $rels = $zip->getFromName('word/_rels/document.xml.rels');
    $mediaSizes = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if ($name !== false && str_starts_with($name, 'word/media/') && str_ends_with($name, '.png')) {
            $mediaSizes[] = $zip->statIndex($i)['size'];
        }
    }
    $zip->close();
    unlink($path);

    return [$xml, $rels, $mediaSizes];
}

describe('PhysicalInfrastructureSection content', function () {
    test('renders the site-to-building-to-bay chain with the 6 bay-equipment relations as separate rows', function () {
        $site = Site::factory()->create(['name' => 'Paris DC']);
        $building = Building::factory()->create(['name' => 'Building A', 'site_id' => $site->id]);
        $bay = Bay::factory()->create(['name' => 'Rack 1', 'site_id' => $site->id, 'building_id' => $building->id]);
        $server = PhysicalServer::factory()->create(['name' => 'srv-01', 'bay_id' => $bay->id, 'site_id' => $site->id, 'building_id' => $building->id]);
        $storage = StorageDevice::factory()->create(['name' => 'san-01', 'bay_id' => $bay->id, 'site_id' => $site->id, 'building_id' => $building->id]);

        [$xml] = renderPhysicalInfrastructureSectionXml();

        expect($xml)
            ->toContain('Paris DC')
            ->toContain('Building A')
            ->toContain('Rack 1')
            ->toContain('srv-01')
            ->toContain('san-01')
            ->toContain($site->getUID())
            ->toContain($building->getUID())
            ->toContain($bay->getUID())
            ->toContain($server->getUID())
            ->toContain($storage->getUID());
    });

    test('renders Zone parent/child zones and admin users labeled by user_id', function () {
        $parentZone = Zone::factory()->create(['name' => 'Parent Zone']);
        $childZone = Zone::factory()->create(['name' => 'Child Zone']);
        $parentZone->childZones()->attach($childZone->id);
        $adminUser = AdminUser::factory()->create(['user_id' => 'jsmith']);
        $parentZone->adminUsers()->attach($adminUser->id);

        [$xml] = renderPhysicalInfrastructureSectionXml();

        expect($xml)
            ->toContain('Parent Zone')
            ->toContain('Child Zone')
            ->toContain('jsmith')
            ->toContain($parentZone->getUID())
            ->toContain($childZone->getUID());
    });

    test('renders PhysicalServer HTML configuration fields, Workstation user link by user_id, and Peripheral version (not the broken pversion)', function () {
        $physicalServer = PhysicalServer::factory()->create(['name' => 'app-srv', 'cpu' => '<p>8 vCPU</p>']);
        $adminUser = AdminUser::factory()->create(['user_id' => 'wuser']);
        $workstation = Workstation::factory()->create(['name' => 'WKS-01', 'user_id' => $adminUser->id]);
        $peripheral = Peripheral::factory()->create(['name' => 'Printer 1', 'version' => 'v2.3']);

        [$xml] = renderPhysicalInfrastructureSectionXml();

        expect($xml)
            ->toContain('app-srv')
            ->toContain('8 vCPU')
            ->toContain('WKS-01')
            ->toContain('wuser')
            ->toContain('Printer 1')
            ->toContain('v2.3')
            ->toContain($physicalServer->getUID())
            ->toContain($workstation->getUID())
            ->toContain($peripheral->getUID());
    });

    test('renders a standalone PhysicalLink with an ad hoc bookmark (no getUID) resolved via its polymorphic endpoints', function () {
        $server = PhysicalServer::factory()->create(['name' => 'link-src-server']);
        $storage = StorageDevice::factory()->create(['name' => 'link-dest-storage']);
        $link = PhysicalLink::factory()->create([
            'type' => 'fiber',
            'color' => '#ff0000',
            'physical_server_src_id' => $server->id,
            'storage_device_dest_id' => $storage->id,
        ]);

        [$xml] = renderPhysicalInfrastructureSectionXml();

        expect($xml)
            ->toContain('link-src-server')
            ->toContain('link-dest-storage')
            ->toContain('fiber')
            ->toContain('#ff0000')
            ->toContain('PHYSICAL_LINK_'.$link->id)
            ->toContain($server->getUID())
            ->toContain($storage->getUID());
    });

    test('orders sections global -> sites -> root buildings -> child buildings -> other objects, alphabetically within each group', function () {
        $siteB = Site::factory()->create(['name' => 'Site B']);
        $siteA = Site::factory()->create(['name' => 'Site A']);
        $rootZ = Building::factory()->create(['name' => 'Root Z', 'site_id' => $siteA->id]);
        $rootA = Building::factory()->create(['name' => 'Root A', 'site_id' => $siteA->id]);
        $childZ = Building::factory()->create(['name' => 'Child Z', 'site_id' => $siteA->id, 'building_id' => $rootA->id]);
        $childA = Building::factory()->create(['name' => 'Child A', 'site_id' => $siteA->id, 'building_id' => $rootA->id]);
        $bay = Bay::factory()->create(['name' => 'Some Bay', 'site_id' => $siteA->id, 'building_id' => $rootA->id]);

        [$xml] = renderPhysicalInfrastructureSectionXml();

        // A UID also shows up as a hyperlink *target* wherever another object links to it (e.g. a
        // Site's "buildings" row links to every building under it) — searching for the bare UID
        // string would find that earlier reference instead of the object's own bookmarked title.
        // Bookmarks are the only place a UID appears as a `w:name` attribute (links use `w:anchor`),
        // so anchor the search on that to find each object's actual section.
        $bookmarkPos = fn (string $uid) => strpos($xml, 'name="'.$uid.'"');

        $posSiteA = $bookmarkPos($siteA->getUID());
        $posSiteB = $bookmarkPos($siteB->getUID());
        $posRootA = $bookmarkPos($rootA->getUID());
        $posRootZ = $bookmarkPos($rootZ->getUID());
        $posChildA = $bookmarkPos($childA->getUID());
        $posChildZ = $bookmarkPos($childZ->getUID());
        $posBay = $bookmarkPos($bay->getUID());

        expect([$posSiteA, $posSiteB, $posRootA, $posRootZ, $posChildA, $posChildZ, $posBay])->each->toBeInt();

        // Sites alphabetical: A before B.
        expect($posSiteA)->toBeLessThan($posSiteB);
        // Both sites (any order within "sites") must precede the buildings section.
        expect(max($posSiteA, $posSiteB))->toBeLessThan(min($posRootA, $posRootZ));
        // Root buildings alphabetical: A before Z.
        expect($posRootA)->toBeLessThan($posRootZ);
        // Root buildings (as a group) precede child buildings (as a group).
        expect(max($posRootA, $posRootZ))->toBeLessThan(min($posChildA, $posChildZ));
        // Child buildings alphabetical: A before Z.
        expect($posChildA)->toBeLessThan($posChildZ);
        // Buildings section precedes the "other objects" section (Bay is first of the remaining types).
        expect(max($posChildA, $posChildZ))->toBeLessThan($posBay);
    });

    test('root building without children or content gets no schema of its own, but the site/global skeleton still shows it', function () {
        $site = Site::factory()->create(['name' => 'Empty Site']);
        $building = Building::factory()->create(['name' => 'Empty Root', 'site_id' => $site->id]);

        [$xml, , $mediaSizes] = renderPhysicalInfrastructureSectionXml();

        expect($xml)->toContain('Empty Root')->toContain($building->getUID());
        // The building itself gets no dedicated schema (no children, no bays/equipment). But the
        // global and site-scoped physical-infrastructure schemas still draw the Site->Building
        // containment edge (2 nodes each, not "empty"/isolated) — the matching network schemas stay
        // empty (a bare building contributes no node to a connectivity graph) and are skipped.
        // Graphs: global infra (1) + site infra (1) = 2.
        // Icons: site.png + building.png = 2.
        expect($mediaSizes)->toHaveCount(4);
    });

    test('root building with content gets 2 schemas (network + physical infrastructure), a child building gets only 1', function () {
        $site = Site::factory()->create(['name' => 'Populated Site']);
        $root = Building::factory()->create(['name' => 'Populated Root', 'site_id' => $site->id]);
        $child = Building::factory()->create(['name' => 'Populated Child', 'site_id' => $site->id, 'building_id' => $root->id]);
        Workstation::factory()->create(['name' => 'WKS-1', 'site_id' => $site->id, 'building_id' => $root->id]);
        PhysicalServer::factory()->create(['name' => 'SRV-1', 'site_id' => $site->id, 'building_id' => $root->id, 'bay_id' => null]);
        Workstation::factory()->create(['name' => 'WKS-2', 'site_id' => $site->id, 'building_id' => $child->id]);
        PhysicalServer::factory()->create(['name' => 'SRV-2', 'site_id' => $site->id, 'building_id' => $child->id, 'bay_id' => null]);

        [$xml, , $mediaSizes] = renderPhysicalInfrastructureSectionXml();

        expect($xml)->toContain('Populated Root')->toContain('Populated Child');
        // 2 global (network+infra) + 2 site-scoped (network+infra) + 2 root-building (network+infra,
        // subtree includes the child) + 1 child-building (infra only, no network) = 7 graphs.
        // Icons: site.png + building.png (root+child dedupe) + workstation.png + server.png = 4.
        expect($mediaSizes)->toHaveCount(11);
    });

    test('an object type with no containment relation (PhysicalServer) never gets a schema', function () {
        PhysicalServer::factory()->create(['name' => 'lone-server', 'site_id' => null, 'building_id' => null, 'bay_id' => null]);

        [$xml, , $mediaSizes] = renderPhysicalInfrastructureSectionXml();

        expect($xml)->toContain('lone-server');
        // No site at all in this scenario, so the connectivity (network) graph draws nothing (it
        // only ever iterates per-site); the location graph draws the server as a single unattached
        // node, which insertGraph() then discards as an isolated node. Only the server.png
        // description icon remains.
        expect($mediaSizes)->toHaveCount(1);
    });

    test('a Bay with mounted equipment gets a physical-infrastructure schema; an empty Bay does not', function () {
        $emptyBay = Bay::factory()->create(['name' => 'Empty Bay', 'site_id' => null, 'building_id' => null]);
        $fullBay = Bay::factory()->create(['name' => 'Full Bay', 'site_id' => null, 'building_id' => null]);
        PhysicalServer::factory()->create(['name' => 'in-bay-server', 'bay_id' => $fullBay->id, 'site_id' => null, 'building_id' => null]);
        StorageDevice::factory()->create(['name' => 'in-bay-storage', 'bay_id' => $fullBay->id, 'site_id' => null, 'building_id' => null]);

        [$xml, , $mediaSizes] = renderPhysicalInfrastructureSectionXml();

        expect($xml)->toContain('Empty Bay')->toContain('Full Bay');
        // No site exists, so the connectivity (network) graph draws nothing at all (site-scoped
        // loop only). The global physical-infrastructure schema still draws both bays (containment
        // is site/building-independent) plus the 2 mounted devices = 4 nodes (1 graph). Empty Bay
        // gets no dedicated schema; Full Bay gets one (bay + server + storage = 3 nodes, 1 graph).
        // Icons: server.png only (StorageDevice/Bay don't render a description icon).
        expect($mediaSizes)->toHaveCount(3);
    });

    test('a Zone with child zones gets a security-zone schema scoped to it; a childless zone does not', function () {
        $parentZone = Zone::factory()->create(['name' => 'Parent Zone']);
        $childZone = Zone::factory()->create(['name' => 'Child Zone']);
        $parentZone->childZones()->attach($childZone->id);
        Zone::factory()->create(['name' => 'Lonely Zone']);

        [$xml, , $mediaSizes] = renderPhysicalInfrastructureSectionXml();

        expect($xml)->toContain('Parent Zone')->toContain('Child Zone')->toContain('Lonely Zone');
        expect($mediaSizes)->toHaveCount(1);
    });
});
