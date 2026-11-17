<?php

use App\Models\ApplicationFlow;
use App\Models\Perimeter;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\PerimeterSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

function ip_in_cidr(string $ip, string $cidr): bool
{
    [$network, $prefix] = explode('/', $cidr);
    $mask = -1 << (32 - (int) $prefix);

    return (ip2long($ip) & $mask) === (ip2long($network) & $mask);
}

it('creates the requested counts for a manual configuration', function () {
    $exit = $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 10,
        '--databases' => 4,
        '--servers' => 6,
        '--flows' => 8,
        '--force' => true,
        '--seed' => 42,
    ])->run();

    expect($exit)->toBe(0)
        ->and(DB::table('perimeters')->count())->toBe(2)
        ->and(DB::table('applications')->count())->toBe(10)
        ->and(DB::table('databases')->count())->toBe(4)
        ->and(DB::table('logical_servers')->count())->toBe(6)
        ->and(DB::table('application_flows')->count())->toBe(8);
});

it('does not write anything in dry-run mode', function () {
    $exit = $this->artisan('mercator:generate-test-data', [
        '--scenario' => 'pme',
        '--dry-run' => true,
    ])->run();

    expect($exit)->toBe(0)
        ->and(DB::table('applications')->count())->toBe(0)
        ->and(DB::table('perimeters')->count())->toBe(1);
});

it('rejects an unknown scenario', function () {
    $exit = $this->artisan('mercator:generate-test-data', [
        '--scenario' => 'does-not-exist',
        '--force' => true,
    ])->run();

    expect($exit)->toBe(1);
});

it('rejects flows without enough applications', function () {
    $exit = $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 1,
        '--applications' => 1,
        '--flows' => 3,
        '--force' => true,
    ])->run();

    expect($exit)->toBe(1);
});

it('assigns every generated object to a valid perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 3,
        '--applications' => 30,
        '--databases' => 10,
        '--servers' => 15,
        '--flows' => 20,
        '--force' => true,
    ])->run();

    $perimeterIds = Perimeter::query()->pluck('id');

    expect(DB::table('applications')->whereNotIn('perimeter_id', $perimeterIds)->count())->toBe(0)
        ->and(DB::table('databases')->whereNotIn('perimeter_id', $perimeterIds)->count())->toBe(0)
        ->and(DB::table('logical_servers')->whereNotIn('perimeter_id', $perimeterIds)->count())->toBe(0)
        ->and(DB::table('application_flows')->whereNotIn('perimeter_id', $perimeterIds)->count())->toBe(0);
});

it('links application flows to existing applications, services, modules or databases with no self-loop', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 10,
        '--databases' => 10,
        '--flows' => 60,
        '--force' => true,
    ])->run();

    $endpointIdsByType = [
        'application' => DB::table('applications')->pluck('id'),
        'service' => DB::table('application_services')->pluck('id'),
        'module' => DB::table('application_modules')->pluck('id'),
        'database' => DB::table('databases')->pluck('id'),
    ];

    $usedTypes = [];

    ApplicationFlow::withoutGlobalScopes()->get()->each(function (ApplicationFlow $flow) use ($endpointIdsByType, &$usedTypes) {
        $sourceType = null;
        $destType = null;

        foreach (array_keys($endpointIdsByType) as $type) {
            $sourceValue = $flow->{"{$type}_source_id"};
            $destValue = $flow->{"{$type}_dest_id"};

            if ($sourceValue !== null) {
                expect($sourceType)->toBeNull();
                $sourceType = $type;
                expect($endpointIdsByType[$type])->toContain($sourceValue);
            }

            if ($destValue !== null) {
                expect($destType)->toBeNull();
                $destType = $type;
                expect($endpointIdsByType[$type])->toContain($destValue);
            }
        }

        expect($sourceType)->not->toBeNull()
            ->and($destType)->not->toBeNull();

        $usedTypes[$sourceType] = true;
        $usedTypes[$destType] = true;

        if ($sourceType === $destType) {
            expect($flow->{"{$sourceType}_source_id"})->not->toBe($flow->{"{$destType}_dest_id"});
        }
    });

    // Avec 60 flux et quatre pools d'extrémités de tailles comparables, on
    // s'attend statistiquement à voir plusieurs types représentés (pas
    // seulement des flux application-à-application).
    expect(count($usedTypes))->toBeGreaterThan(1);
});

it('attaches servers and databases only within the same perimeter as their application', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 3,
        '--applications' => 20,
        '--databases' => 10,
        '--servers' => 10,
        '--flows' => 0,
        '--force' => true,
    ])->run();

    $appPerimeterById = DB::table('applications')->pluck('perimeter_id', 'id');
    $serverPerimeterById = DB::table('logical_servers')->pluck('perimeter_id', 'id');
    $databasePerimeterById = DB::table('databases')->pluck('perimeter_id', 'id');

    DB::table('application_logical_server')->get()->each(function ($row) use ($appPerimeterById, $serverPerimeterById) {
        expect($appPerimeterById[$row->application_id])->toBe($serverPerimeterById[$row->logical_server_id]);
    });

    DB::table('application_database')->get()->each(function ($row) use ($appPerimeterById, $databasePerimeterById) {
        expect($appPerimeterById[$row->application_id])->toBe($databasePerimeterById[$row->database_id]);
    });
});

it('chains sites, buildings, bays and physical servers coherently within the same perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 3,
        '--applications' => 0,
        '--sites' => 6,
        '--buildings' => 12,
        '--bays' => 24,
        '--physical-servers' => 30,
        '--force' => true,
    ])->run();

    $sitePerimeterById = DB::table('sites')->pluck('perimeter_id', 'id');
    $buildingPerimeterById = DB::table('buildings')->pluck('perimeter_id', 'id');
    $bayPerimeterById = DB::table('bays')->pluck('perimeter_id', 'id');
    $serverPerimeterById = DB::table('physical_servers')->pluck('perimeter_id', 'id');

    DB::table('buildings')->whereNotNull('site_id')->get()->each(function ($building) use ($buildingPerimeterById, $sitePerimeterById) {
        expect($buildingPerimeterById[$building->id])->toBe($sitePerimeterById[$building->site_id]);
    });

    DB::table('bays')->get()->each(function ($bay) use ($bayPerimeterById, $buildingPerimeterById) {
        if ($bay->building_id !== null) {
            expect($bayPerimeterById[$bay->id])->toBe($buildingPerimeterById[$bay->building_id]);

            $expectedSiteId = DB::table('buildings')->where('id', $bay->building_id)->value('site_id');
            expect($bay->site_id)->toBe($expectedSiteId);
        }
    });

    DB::table('physical_servers')->get()->each(function ($server) use ($serverPerimeterById, $bayPerimeterById) {
        if ($server->bay_id !== null) {
            expect($serverPerimeterById[$server->id])->toBe($bayPerimeterById[$server->bay_id]);

            $bay = DB::table('bays')->where('id', $server->bay_id)->first();
            expect($server->building_id)->toBe($bay->building_id)
                ->and($server->site_id)->toBe($bay->site_id);
        }
    });
});

it('links security zones only to buildings of the same perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 3,
        '--applications' => 0,
        '--sites' => 3,
        '--buildings' => 9,
        '--security-zones' => 6,
        '--force' => true,
    ])->run();

    $zonePerimeterById = DB::table('zones')->pluck('perimeter_id', 'id');
    $buildingPerimeterById = DB::table('buildings')->pluck('perimeter_id', 'id');

    expect(DB::table('building_zone')->count())->toBeGreaterThan(0);

    DB::table('building_zone')->get()->each(function ($row) use ($zonePerimeterById, $buildingPerimeterById) {
        expect($zonePerimeterById[$row->zone_id])->toBe($buildingPerimeterById[$row->building_id]);
    });
});

it('does not enable the perimeters feature nor create per-perimeter roles for a single perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 1,
        '--applications' => 3,
        '--force' => true,
    ])->run();

    expect(PerimeterSettings::isEnabled())->toBeFalse()
        ->and(Role::query()->where('title', 'like', 'admin.perimeter.%')->count())->toBe(0)
        ->and(Role::query()->where('title', 'like', 'user.perimeter.%')->count())->toBe(0)
        ->and(Role::query()->where('title', 'like', 'auditor.perimeter.%')->count())->toBe(0);
});

it('enables the perimeters feature and creates a fully-permissioned admin role per perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 3,
        '--applications' => 0,
        '--force' => true,
    ])->run();

    expect(PerimeterSettings::isEnabled())->toBeTrue();

    $perimeters = Perimeter::query()->get();
    $totalPermissions = Permission::query()->count();

    expect($perimeters)->toHaveCount(3);

    foreach ($perimeters as $perimeter) {
        $role = Role::query()
            ->where('perimeter_id', $perimeter->id)
            ->where('title', 'admin.perimeter.'.Str::slug($perimeter->name))
            ->first();

        expect($role)->not->toBeNull()
            ->and($role->permissions()->count())->toBe($totalPermissions);
    }
});

it('creates an auditor and a user role per perimeter, without administration permissions', function () {
    Permission::query()->insert([
        ['title' => 'user_management_access'],
        ['title' => 'role_access'],
        ['title' => 'role_edit'],
        ['title' => 'user_create'],
        ['title' => 'configuration_access'],
        ['title' => 'module_manage'],
        ['title' => 'cartographer_show'],
        ['title' => 'entity_access'],
        ['title' => 'entity_show'],
        ['title' => 'entity_create'],
        ['title' => 'entity_edit'],
        ['title' => 'entity_delete'],
        ['title' => 'application_access'],
        ['title' => 'application_edit'],
        ['title' => 'configure'],
    ]);

    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 3,
        '--applications' => 0,
        '--force' => true,
    ])->run();

    $adminOnly = [
        'user_management_access', 'role_access', 'role_edit', 'user_create',
        'configuration_access', 'module_manage', 'cartographer_show',
    ];

    $perimeters = Perimeter::query()->get();
    expect($perimeters)->toHaveCount(3);

    foreach ($perimeters as $perimeter) {
        $slug = Str::slug($perimeter->name);

        $user = Role::query()->where('perimeter_id', $perimeter->id)->where('title', 'user.perimeter.'.$slug)->first();
        $auditor = Role::query()->where('perimeter_id', $perimeter->id)->where('title', 'auditor.perimeter.'.$slug)->first();

        expect($user)->not->toBeNull()->and($auditor)->not->toBeNull();

        $userPermissions = $user->permissions()->pluck('title')->all();
        $auditorPermissions = $auditor->permissions()->pluck('title')->all();

        expect($userPermissions)
            ->toContain('entity_create', 'entity_edit', 'entity_delete', 'application_edit', 'configure')
            ->not->toContain(...$adminOnly)
            ->and($auditorPermissions)
            ->toEqualCanonicalizing(['entity_access', 'entity_show', 'application_access']);
    }
});

it('does not attach admin@admin.com to the user and auditor perimeter roles', function () {
    User::factory()->create(['login' => 'admin@admin.com']);

    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 0,
        '--force' => true,
    ])->run();

    $admin = User::query()->where('login', 'admin@admin.com')->first();

    expect($admin->roles()->where('title', 'like', 'admin.perimeter.%')->count())->toBe(2)
        ->and($admin->roles()->where('title', 'like', 'user.perimeter.%')->count())->toBe(0)
        ->and($admin->roles()->where('title', 'like', 'auditor.perimeter.%')->count())->toBe(0);
});

it('does not duplicate per-perimeter admin roles when run twice', function () {
    $options = [
        '--perimeters' => 2,
        '--applications' => 0,
        '--force' => true,
    ];

    $this->artisan('mercator:generate-test-data', $options)->run();
    $this->artisan('mercator:generate-test-data', array_merge($options, ['--perimeters' => 2]))->run();

    expect(Role::query()->where('title', 'like', 'admin.perimeter.%')->count())->toBe(2)
        ->and(Role::query()->where('title', 'like', 'user.perimeter.%')->count())->toBe(2)
        ->and(Role::query()->where('title', 'like', 'auditor.perimeter.%')->count())->toBe(2);
});

it('attaches the admin@admin.com user to every new admin.perimeter role without detaching its existing roles', function () {
    $admin = User::factory()->create(['login' => 'admin@admin.com']);
    $existingRole = Role::factory()->create(['title' => 'Admin']);
    $admin->roles()->attach($existingRole->id);

    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 3,
        '--applications' => 0,
        '--force' => true,
    ])->run();

    $admin->refresh();
    $perimeterRoleIds = Role::query()->where('title', 'like', 'admin.perimeter.%')->pluck('id');

    expect($admin->roles()->pluck('roles.id'))
        ->toContain($existingRole->id)
        ->and($admin->roles()->whereIn('roles.id', $perimeterRoleIds)->count())->toBe(3);
});

it('does not fail when no admin@admin.com user exists', function () {
    $exit = $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 0,
        '--force' => true,
    ])->run();

    expect($exit)->toBe(0)
        ->and(Role::query()->where('title', 'like', 'admin.perimeter.%')->count())->toBe(2);
});

it('chains peripherals and workstations coherently within their own perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 3,
        '--applications' => 0,
        '--sites' => 6,
        '--buildings' => 12,
        '--bays' => 24,
        '--peripherals' => 15,
        '--workstations' => 20,
        '--force' => true,
    ])->run();

    $buildingPerimeterById = DB::table('buildings')->pluck('perimeter_id', 'id');
    $baySiteById = DB::table('bays')->pluck('site_id', 'id');
    $bayBuildingById = DB::table('bays')->pluck('building_id', 'id');
    $bayPerimeterById = DB::table('bays')->pluck('perimeter_id', 'id');

    expect(DB::table('peripherals')->count())->toBe(15)
        ->and(DB::table('workstations')->count())->toBe(20);

    DB::table('peripherals')->whereNotNull('bay_id')->get()->each(function ($peripheral) use ($bayPerimeterById, $bayBuildingById, $baySiteById) {
        expect($peripheral->perimeter_id)->toBe($bayPerimeterById[$peripheral->bay_id])
            ->and($peripheral->building_id)->toBe($bayBuildingById[$peripheral->bay_id])
            ->and($peripheral->site_id)->toBe($baySiteById[$peripheral->bay_id]);
    });

    $buildingTypeById = DB::table('buildings')->pluck('type', 'id');

    DB::table('workstations')->whereNotNull('building_id')->get()->each(function ($workstation) use ($buildingPerimeterById, $buildingTypeById) {
        expect($workstation->perimeter_id)->toBe($buildingPerimeterById[$workstation->building_id])
            ->and($buildingTypeById[$workstation->building_id])->toBe('Local');
    });
});

it('creates exactly as many phones as workstations actually created', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 0,
        '--sites' => 2,
        '--buildings' => 4,
        '--workstations' => 17,
        '--force' => true,
    ])->run();

    expect(DB::table('phones')->count())->toBe(17);
});

it('gives each floor/local an independent chance of its own wifi terminal, in the same building/site/perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 0,
        '--sites' => 10,
        '--buildings' => 100,
        '--force' => true,
        '--seed' => 7,
    ])->run();

    // Une borne WiFi se trouve dans un étage ou un local, jamais dans un
    // bâtiment "classique" : le pool éligible est whereIn(type, [Etage, Local]),
    // pas la table buildings entière.
    $eligibleCount = DB::table('buildings')->whereIn('type', ['Etage', 'Local'])->count();
    $wifiCount = DB::table('wifi_terminals')->count();

    expect($eligibleCount)->toBeGreaterThan(0)
        ->and($wifiCount)->toBeGreaterThan((int) ($eligibleCount * 0.5))
        ->and($wifiCount)->toBeLessThanOrEqual($eligibleCount);

    $buildingPerimeterById = DB::table('buildings')->pluck('perimeter_id', 'id');
    $buildingSiteById = DB::table('buildings')->pluck('site_id', 'id');
    $buildingTypeById = DB::table('buildings')->pluck('type', 'id');

    DB::table('wifi_terminals')->get()->each(function ($terminal) use ($buildingPerimeterById, $buildingSiteById, $buildingTypeById) {
        expect($terminal->perimeter_id)->toBe($buildingPerimeterById[$terminal->building_id])
            ->and($terminal->site_id)->toBe($buildingSiteById[$terminal->building_id])
            ->and($buildingTypeById[$terminal->building_id])->toBeIn(['Etage', 'Local']);
    });
});

it('creates exactly one physical switch per bay, in the same bay/building/site/perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 0,
        '--sites' => 4,
        '--buildings' => 8,
        '--bays' => 20,
        '--force' => true,
    ])->run();

    $bayCount = DB::table('bays')->count();
    $switches = DB::table('physical_switches')->whereNotNull('bay_id')->get();

    expect($switches)->toHaveCount($bayCount);

    $bayById = DB::table('bays')->get()->keyBy('id');

    $switches->each(function ($switch) use ($bayById) {
        $bay = $bayById[$switch->bay_id];

        expect($switch->perimeter_id)->toBe($bay->perimeter_id)
            ->and($switch->building_id)->toBe($bay->building_id)
            ->and($switch->site_id)->toBe($bay->site_id);
    });
});

it('creates at most one physical router per site, placed in one of that site\'s bays', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 0,
        '--sites' => 5,
        '--buildings' => 10,
        '--bays' => 15,
        '--force' => true,
    ])->run();

    $siteCount = DB::table('sites')->count();
    $routers = DB::table('physical_routers')->get();

    expect($routers->count())->toBeLessThanOrEqual($siteCount)
        ->and($routers->pluck('site_id')->unique())->toHaveCount($routers->count());

    $bayById = DB::table('bays')->get()->keyBy('id');

    $routers->each(function ($router) use ($bayById) {
        $bay = $bayById[$router->bay_id];

        expect($router->site_id)->toBe($bay->site_id)
            ->and($router->perimeter_id)->toBe($bay->perimeter_id)
            ->and($router->building_id)->toBe($bay->building_id);
    });
});

it('does not create a physical router for a site with no bays', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 1,
        '--applications' => 0,
        '--sites' => 3,
        '--buildings' => 0,
        '--bays' => 0,
        '--force' => true,
    ])->run();

    expect(DB::table('sites')->count())->toBe(3)
        ->and(DB::table('physical_routers')->count())->toBe(0);
});

it('links every physical server to the switch of its own bay via a physical link', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 0,
        '--sites' => 3,
        '--buildings' => 6,
        '--bays' => 10,
        '--physical-servers' => 25,
        '--force' => true,
    ])->run();

    $serverCount = DB::table('physical_servers')->count();
    $links = DB::table('physical_links')->whereNotNull('physical_server_src_id')->get();

    expect($links)->toHaveCount($serverCount);

    $serverById = DB::table('physical_servers')->get()->keyBy('id');
    $switchByBayId = DB::table('physical_switches')->pluck('id', 'bay_id');

    $links->each(function ($link) use ($serverById, $switchByBayId) {
        $server = $serverById[$link->physical_server_src_id];

        expect($link->physical_switch_dest_id)->toBe($switchByBayId[$server->bay_id])
            ->and($link->perimeter_id)->toBe($server->perimeter_id);
    });
});

it('links every switch (bay or floor) of a site to that site\'s router via a physical link', function () {
    // Un seul site : toutes les baies (et donc tous les étages, générés
    // indépendamment mais rattachés au même site) se rapportent forcément à
    // ce site, qui a donc forcément un routeur — pas de cas "site sans baie"
    // à gérer ici (couvert par un autre test).
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 1,
        '--applications' => 0,
        '--sites' => 1,
        '--buildings' => 3,
        '--bays' => 6,
        '--force' => true,
    ])->run();

    $switchCount = DB::table('physical_switches')->count();
    $floorSwitchCount = DB::table('physical_switches')->whereNotNull('building_id')->whereNull('bay_id')->count();
    $routerCount = DB::table('physical_routers')->count();
    $links = DB::table('physical_links')->whereNotNull('physical_switch_src_id')->get();

    expect($routerCount)->toBe(1)
        ->and($floorSwitchCount)->toBeGreaterThan(0)
        ->and($links)->toHaveCount($switchCount);

    $switchById = DB::table('physical_switches')->get()->keyBy('id');
    $routerBySiteId = DB::table('physical_routers')->pluck('id', 'site_id');

    $links->each(function ($link) use ($switchById, $routerBySiteId) {
        $switch = $switchById[$link->physical_switch_src_id];

        expect($link->physical_router_dest_id)->toBe($routerBySiteId[$switch->site_id])
            ->and($link->perimeter_id)->toBe($switch->perimeter_id);
    });
});

it('creates 1 to 8 sequentially named floors per site, and 5 to 10 locals per floor', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 1,
        '--applications' => 0,
        '--sites' => 5,
        '--force' => true,
    ])->run();

    $sites = DB::table('sites')->get();
    $floors = DB::table('buildings')->where('type', 'Etage')->get();
    $locals = DB::table('buildings')->where('type', 'Local')->get();

    expect($floors->count())->toBeGreaterThan(0)
        ->and($locals->count())->toBeGreaterThan(0);

    $floorsBySite = $floors->groupBy('site_id');

    foreach ($sites as $site) {
        $siteFloors = $floorsBySite->get($site->id, collect());

        expect($siteFloors->count())->toBeGreaterThanOrEqual(1)
            ->and($siteFloors->count())->toBeLessThanOrEqual(8)
            ->and($siteFloors->pluck('perimeter_id')->unique()->all())->toBe([$site->perimeter_id])
            ->and($siteFloors->pluck('building_id')->unique()->all())->toBe([null]);

        expect($siteFloors->pluck('name')->sort()->values()->all())
            ->toBe(collect(range(1, $siteFloors->count()))->map(fn ($i) => "ET{$i}")->all());
    }

    $localsByFloor = $locals->groupBy('building_id');

    foreach ($floors as $floor) {
        $floorLocals = $localsByFloor->get($floor->id, collect());

        expect($floorLocals->count())->toBeGreaterThanOrEqual(5)
            ->and($floorLocals->count())->toBeLessThanOrEqual(10);

        $floorLocals->each(function ($local) use ($floor) {
            expect($local->perimeter_id)->toBe($floor->perimeter_id)
                ->and($local->site_id)->toBe($floor->site_id)
                ->and($local->name)->toMatch('/^LOCAL\d{3}$/');
        });
    }
});

it('creates exactly one physical switch per floor', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 1,
        '--applications' => 0,
        '--sites' => 4,
        '--force' => true,
    ])->run();

    $floors = DB::table('buildings')->where('type', 'Etage')->get();
    $floorSwitches = DB::table('physical_switches')->whereNull('bay_id')->whereNotNull('building_id')->get();

    expect($floorSwitches)->toHaveCount($floors->count());

    $floorById = $floors->keyBy('id');

    $floorSwitches->each(function ($switch) use ($floorById) {
        $floor = $floorById[$switch->building_id];

        expect($switch->perimeter_id)->toBe($floor->perimeter_id)
            ->and($switch->site_id)->toBe($floor->site_id);
    });
});

it('links every workstation to the switch of the floor containing its local', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 1,
        '--applications' => 0,
        '--sites' => 3,
        '--workstations' => 25,
        '--force' => true,
    ])->run();

    $workstationCount = DB::table('workstations')->count();
    $links = DB::table('physical_links')->whereNotNull('workstation_src_id')->get();

    expect($workstationCount)->toBe(25)
        ->and($links)->toHaveCount($workstationCount);

    $workstationById = DB::table('workstations')->get()->keyBy('id');
    $localFloorById = DB::table('buildings')->where('type', 'Local')->pluck('building_id', 'id');
    $switchByFloorId = DB::table('physical_switches')->whereNull('bay_id')->pluck('id', 'building_id');

    $links->each(function ($link) use ($workstationById, $localFloorById, $switchByFloorId) {
        $workstation = $workstationById[$link->workstation_src_id];
        $floorId = $localFloorById[$workstation->building_id];

        expect($link->physical_switch_dest_id)->toBe($switchByFloorId[$floorId])
            ->and($link->perimeter_id)->toBe($workstation->perimeter_id);
    });
});

it('links every wifi terminal to the switch of its own floor or of the floor containing its local', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 1,
        '--applications' => 0,
        '--sites' => 5,
        '--force' => true,
        '--seed' => 11,
    ])->run();

    $wifiCount = DB::table('wifi_terminals')->count();
    $links = DB::table('physical_links')->whereNotNull('wifi_terminal_src_id')->get();

    expect($wifiCount)->toBeGreaterThan(0)
        ->and($links)->toHaveCount($wifiCount);

    $wifiById = DB::table('wifi_terminals')->get()->keyBy('id');
    $buildingTypeById = DB::table('buildings')->pluck('type', 'id');
    $localFloorById = DB::table('buildings')->where('type', 'Local')->pluck('building_id', 'id');
    $switchByFloorId = DB::table('physical_switches')->whereNull('bay_id')->pluck('id', 'building_id');

    $links->each(function ($link) use ($wifiById, $buildingTypeById, $localFloorById, $switchByFloorId) {
        $wifi = $wifiById[$link->wifi_terminal_src_id];
        $floorId = $buildingTypeById[$wifi->building_id] === 'Etage'
            ? $wifi->building_id
            : $localFloorById[$wifi->building_id];

        expect($link->physical_switch_dest_id)->toBe($switchByFloorId[$floorId])
            ->and($link->perimeter_id)->toBe($wifi->perimeter_id);
    });
});

it('creates one network named after each site, with 4 to 16 subnetworks each associated with its own vlan', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 1,
        '--applications' => 0,
        '--sites' => 5,
        '--force' => true,
    ])->run();

    $sites = DB::table('sites')->get();
    $networks = DB::table('networks')->get();

    expect($networks)->toHaveCount($sites->count());

    $networkBySiteName = $networks->keyBy('name');

    foreach ($sites as $site) {
        expect($networkBySiteName->has($site->name))->toBeTrue();

        $network = $networkBySiteName[$site->name];
        expect($network->perimeter_id)->toBe($site->perimeter_id);
    }

    $subnetworksByNetwork = DB::table('subnetworks')->get()->groupBy('network_id');
    $vlanById = DB::table('vlans')->get()->keyBy('id');
    $seenVlanIds = [];

    foreach ($networks as $network) {
        $subnetworks = $subnetworksByNetwork->get($network->id, collect());

        expect($subnetworks->count())->toBeGreaterThanOrEqual(4)
            ->and($subnetworks->count())->toBeLessThanOrEqual(16);

        $subnetworks->each(function ($subnetwork) use ($network, $vlanById, &$seenVlanIds) {
            expect($subnetwork->perimeter_id)->toBe($network->perimeter_id)
                ->and($subnetwork->vlan_id)->not->toBeNull();

            $vlan = $vlanById[$subnetwork->vlan_id];

            expect($vlan)->not->toBeNull()
                ->and($vlan->perimeter_id)->toBe($subnetwork->perimeter_id)
                ->and($seenVlanIds)->not->toContain($subnetwork->vlan_id);

            $seenVlanIds[] = $subnetwork->vlan_id;
        });
    }

    expect(DB::table('vlans')->count())->toBe(DB::table('subnetworks')->count());
});

it('assigns each logical server a physical server from the same perimeter and an IP from a subnetwork on that server\'s site', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 0,
        '--sites' => 4,
        '--buildings' => 8,
        '--bays' => 16,
        '--physical-servers' => 30,
        '--servers' => 25,
        '--force' => true,
    ])->run();

    $serverCount = DB::table('logical_servers')->count();
    $links = DB::table('logical_server_physical_server')->get();

    expect($serverCount)->toBe(25)
        ->and($links)->toHaveCount($serverCount);

    $logicalServerById = DB::table('logical_servers')->get()->keyBy('id');
    $physicalServerById = DB::table('physical_servers')->get()->keyBy('id');
    $subnetworks = DB::table('subnetworks')->get();
    // Network n'a pas de colonne site_id : l'association réseau <-> site se
    // fait par le nom (voir insertNetworks()).
    $siteIdByName = DB::table('sites')->pluck('id', 'name');
    $networkSiteById = DB::table('networks')->get()->mapWithKeys(
        fn ($network) => [$network->id => $siteIdByName[$network->name]]
    );

    $links->each(function ($link) use ($logicalServerById, $physicalServerById, $subnetworks, $networkSiteById) {
        $logicalServer = $logicalServerById[$link->logical_server_id];
        $physicalServer = $physicalServerById[$link->physical_server_id];

        expect($physicalServer->perimeter_id)->toBe($logicalServer->perimeter_id);

        if ($physicalServer->site_id === null) {
            return;
        }

        $matchingSubnetwork = $subnetworks->first(
            fn ($subnetwork) => $networkSiteById[$subnetwork->network_id] === $physicalServer->site_id
                && ip_in_cidr($logicalServer->address_ip, $subnetwork->address)
        );

        expect($matchingSubnetwork)->not->toBeNull();
    });
});

it('gives every cartography object 3 to 5 attribute tags from the fixed list', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 1,
        '--applications' => 5,
        '--databases' => 5,
        '--servers' => 5,
        '--sites' => 2,
        '--buildings' => 3,
        '--bays' => 4,
        '--physical-servers' => 4,
        '--peripherals' => 3,
        '--workstations' => 5,
        '--security-zones' => 2,
        '--force' => true,
    ])->run();

    $allowedTags = ['OK', 'Checked', 'Dev', 'Test', 'T1', 'T2', 'T3', 'O1', 'Saved', 'A1', 'A2', 'A3', 'A4', 'A5', 'Sec', 'Lab'];

    $tables = [
        'applications', 'databases', 'logical_servers', 'sites', 'networks', 'subnetworks', 'vlans',
        'buildings', 'bays', 'physical_switches', 'physical_routers', 'physical_servers', 'peripherals',
        'workstations', 'phones', 'wifi_terminals', 'zones', 'application_blocks', 'application_services',
        'application_modules', 'zone_admins', 'annuaires', 'forest_ads', 'domains', 'admin_users',
        'macro_processuses', 'processes', 'activities', 'operations', 'tasks', 'actors', 'information',
        'entities', 'relations',
    ];

    foreach ($tables as $table) {
        $rows = DB::table($table)->get();

        expect($rows->count())->toBeGreaterThan(0);

        $rows->each(function ($row) use ($allowedTags) {
            $tags = explode(' ', $row->attributes);

            expect(count($tags))->toBeGreaterThanOrEqual(3)
                ->and(count($tags))->toBeLessThanOrEqual(5);

            foreach ($tags as $tag) {
                expect($tag)->toBeIn($allowedTags);
            }
        });
    }
});

it('creates 5 to 7 application blocks per perimeter and assigns each application to a block of its own perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 3,
        '--applications' => 60,
        '--servers' => 0,
        '--force' => true,
    ])->run();

    $blocksByPerimeter = DB::table('application_blocks')->get()->groupBy('perimeter_id');

    expect($blocksByPerimeter)->toHaveCount(3);

    foreach ($blocksByPerimeter as $blocks) {
        expect($blocks->count())->toBeGreaterThanOrEqual(5)
            ->and($blocks->count())->toBeLessThanOrEqual(7);
    }

    $applications = DB::table('applications')->get();
    $blockById = DB::table('application_blocks')->get()->keyBy('id');

    expect($applications)->toHaveCount(60);

    foreach ($applications as $application) {
        expect($application->application_block_id)->not->toBeNull();

        $block = $blockById[$application->application_block_id];

        expect($block->perimeter_id)->toBe($application->perimeter_id);
    }
});

it('attaches each application to 1, 2 or 3 logical servers of its own perimeter, with about 90% having exactly one', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 1,
        '--applications' => 500,
        '--servers' => 500,
        '--force' => true,
    ])->run();

    $appPerimeterById = DB::table('applications')->pluck('perimeter_id', 'id');
    $serverPerimeterById = DB::table('logical_servers')->pluck('perimeter_id', 'id');

    $linksByApplication = DB::table('application_logical_server')->get()->groupBy('application_id');

    expect($linksByApplication)->toHaveCount($appPerimeterById->count());

    $countTally = [];

    foreach ($linksByApplication as $applicationId => $links) {
        $howMany = $links->count();

        expect($howMany)->toBeGreaterThanOrEqual(1)
            ->and($howMany)->toBeLessThanOrEqual(3);

        $countTally[$howMany] = ($countTally[$howMany] ?? 0) + 1;

        foreach ($links as $link) {
            expect($serverPerimeterById[$link->logical_server_id])->toBe($appPerimeterById[$applicationId]);
        }
    }

    // Tolérance statistique large (tirage aléatoire sur 500 applications) :
    // ~90% devraient n'avoir qu'un seul serveur logique.
    $exactlyOnePercent = ($countTally[1] ?? 0) / $appPerimeterById->count() * 100;

    expect($exactlyOnePercent)->toBeGreaterThan(80);
});

it('creates 1 to 3 admin zones, one directory and one AD forest per site, with 3 to 5 domains attached to that forest', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 10,
        '--sites' => 5,
        '--workstations' => 40,
        '--force' => true,
    ])->run();

    $siteCount = DB::table('sites')->count();

    expect(DB::table('zone_admins')->count())->toBeGreaterThanOrEqual($siteCount * 1)
        ->and(DB::table('zone_admins')->count())->toBeLessThanOrEqual($siteCount * 3)
        ->and(DB::table('annuaires')->count())->toBe($siteCount)
        ->and(DB::table('forest_ads')->count())->toBe($siteCount)
        ->and(DB::table('domains')->count())->toBeGreaterThanOrEqual($siteCount * 3)
        ->and(DB::table('domains')->count())->toBeLessThanOrEqual($siteCount * 5);

    $domainsPerForest = DB::table('domain_forest_ad')->get()->groupBy('forest_ad_id');

    expect($domainsPerForest)->toHaveCount($siteCount);

    foreach ($domainsPerForest as $domains) {
        expect($domains->count())->toBeGreaterThanOrEqual(3)
            ->and($domains->count())->toBeLessThanOrEqual(5);
    }
});

it('keeps admin zones, directories, AD forests and domains within the same perimeter as their parent site', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 3,
        '--applications' => 15,
        '--sites' => 6,
        '--workstations' => 30,
        '--force' => true,
    ])->run();

    $zoneAdminPerimeterById = DB::table('zone_admins')->pluck('perimeter_id', 'id');
    $applicationPerimeterById = DB::table('applications')->pluck('perimeter_id', 'id');
    $domainPerimeterById = DB::table('domains')->pluck('perimeter_id', 'id');
    $forestAdPerimeterById = DB::table('forest_ads')->pluck('perimeter_id', 'id');

    DB::table('annuaires')->get()->each(function ($annuaire) use ($zoneAdminPerimeterById, $applicationPerimeterById) {
        expect($zoneAdminPerimeterById[$annuaire->zone_admin_id])->toBe($annuaire->perimeter_id);

        if ($annuaire->application_id !== null) {
            expect($applicationPerimeterById[$annuaire->application_id])->toBe($annuaire->perimeter_id);
        }
    });

    DB::table('forest_ads')->get()->each(function ($forestAd) use ($zoneAdminPerimeterById) {
        expect($zoneAdminPerimeterById[$forestAd->zone_admin_id])->toBe($forestAd->perimeter_id);
    });

    DB::table('domain_forest_ad')->get()->each(function ($link) use ($domainPerimeterById, $forestAdPerimeterById) {
        expect($domainPerimeterById[$link->domain_id])->toBe($forestAdPerimeterById[$link->forest_ad_id]);
    });
});

it('creates exactly as many admin_users as workstations, each attached to a domain of its own perimeter, with domains.user_count matching', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 5,
        '--sites' => 4,
        '--workstations' => 50,
        '--force' => true,
    ])->run();

    $workstationCount = DB::table('workstations')->count();
    $adminUserCount = DB::table('admin_users')->count();

    expect($adminUserCount)->toBe($workstationCount);

    $domainPerimeterById = DB::table('domains')->pluck('perimeter_id', 'id');
    $adminUsers = DB::table('admin_users')->get();

    $adminUsers->each(function ($adminUser) use ($domainPerimeterById) {
        expect($adminUser->domain_id)->not->toBeNull()
            ->and($domainPerimeterById[$adminUser->domain_id])->toBe($adminUser->perimeter_id);
    });

    $actualCountByDomain = $adminUsers->countBy('domain_id');

    DB::table('domains')->get()->each(function ($domain) use ($actualCountByDomain) {
        expect((int) $domain->user_count)->toBe($actualCountByDomain->get($domain->id, 0));
    });
});

it('gives each application 0 to 5 dedicated application services, and each service 0 to 3 dedicated modules, within the same perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 30,
        '--force' => true,
    ])->run();

    $applicationPerimeterById = DB::table('applications')->pluck('perimeter_id', 'id');
    $servicesByApplication = DB::table('application_application_service')->get()->groupBy('application_id');

    foreach ($servicesByApplication as $services) {
        expect($services->count())->toBeLessThanOrEqual(5);
    }

    $servicePerimeterById = DB::table('application_services')->pluck('perimeter_id', 'id');

    DB::table('application_application_service')->get()->each(function ($link) use ($applicationPerimeterById, $servicePerimeterById) {
        expect($servicePerimeterById[$link->application_service_id])->toBe($applicationPerimeterById[$link->application_id]);
    });

    // Chaque service applicatif appartient à exactement une application
    // (pas partagé) : autant de liens que de services créés.
    expect(DB::table('application_application_service')->count())->toBe(DB::table('application_services')->count());

    $modulesByService = DB::table('application_module_application_service')->get()->groupBy('application_service_id');

    foreach ($modulesByService as $modules) {
        expect($modules->count())->toBeLessThanOrEqual(3);
    }

    $modulePerimeterById = DB::table('application_modules')->pluck('perimeter_id', 'id');

    DB::table('application_module_application_service')->get()->each(function ($link) use ($servicePerimeterById, $modulePerimeterById) {
        expect($modulePerimeterById[$link->application_module_id])->toBe($servicePerimeterById[$link->application_service_id]);
    });

    // Chaque module applicatif appartient à exactement un service (pas
    // partagé) : autant de liens que de modules créés.
    expect(DB::table('application_module_application_service')->count())->toBe(DB::table('application_modules')->count());
});

it('chains macro-processes, processes, activities, operations and tasks coherently within their own perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 0,
        '--force' => true,
    ])->run();

    $macroProcessesByPerimeter = DB::table('macro_processuses')->get()->groupBy('perimeter_id');

    expect($macroProcessesByPerimeter)->toHaveCount(2);

    // Les bornes viennent de la config : seuls les parents ayant au moins un
    // enfant apparaissent dans les regroupements ci-dessous, un minimum à 0
    // reste donc compatible.
    $macroProcessRange = config('data-scenarios.macro_processes_per_perimeter');
    $processRange = config('data-scenarios.processes_per_macro_process');
    $activityRange = config('data-scenarios.activities_per_process');
    $operationRange = config('data-scenarios.operations_per_activity');
    $taskRange = config('data-scenarios.tasks_per_operation');

    foreach ($macroProcessesByPerimeter as $macroProcesses) {
        expect($macroProcesses->count())->toBeGreaterThanOrEqual($macroProcessRange['min'])
            ->and($macroProcesses->count())->toBeLessThanOrEqual($macroProcessRange['max']);
    }

    $macroProcessPerimeterById = DB::table('macro_processuses')->pluck('perimeter_id', 'id');
    $processesByMacroProcess = DB::table('processes')->get()->groupBy('macroprocess_id');

    foreach ($processesByMacroProcess as $macroProcessId => $processes) {
        expect($processes->count())->toBeGreaterThanOrEqual($processRange['min'])
            ->and($processes->count())->toBeLessThanOrEqual($processRange['max']);

        foreach ($processes as $process) {
            expect($process->perimeter_id)->toBe($macroProcessPerimeterById[$macroProcessId]);
        }
    }

    $processPerimeterById = DB::table('processes')->pluck('perimeter_id', 'id');
    $activityPerimeterById = DB::table('activities')->pluck('perimeter_id', 'id');
    $activitiesByProcess = DB::table('activity_process')->get()->groupBy('process_id');

    // Chaque activité est dédiée à un seul processus (pas partagée) :
    // autant de liens que d'activités créées.
    expect(DB::table('activity_process')->count())->toBe(DB::table('activities')->count());

    foreach ($activitiesByProcess as $processId => $links) {
        expect($links->count())->toBeGreaterThanOrEqual($activityRange['min'])
            ->and($links->count())->toBeLessThanOrEqual($activityRange['max']);

        foreach ($links as $link) {
            expect($activityPerimeterById[$link->activity_id])->toBe($processPerimeterById[$processId]);
        }
    }

    $operationPerimeterById = DB::table('operations')->pluck('perimeter_id', 'id');
    $operationProcessById = DB::table('operations')->pluck('process_id', 'id');
    $operationsByActivity = DB::table('activity_operation')->get()->groupBy('activity_id');

    // Chaque opération est dédiée à une seule activité (pas partagée) :
    // autant de liens que d'opérations créées.
    expect(DB::table('activity_operation')->count())->toBe(DB::table('operations')->count());

    $activityProcessByActivity = DB::table('activity_process')->pluck('process_id', 'activity_id');

    foreach ($operationsByActivity as $activityId => $links) {
        expect($links->count())->toBeGreaterThanOrEqual($operationRange['min'])
            ->and($links->count())->toBeLessThanOrEqual($operationRange['max']);

        foreach ($links as $link) {
            expect($operationPerimeterById[$link->operation_id])->toBe($activityPerimeterById[$activityId]);
            // L'opération hérite du même processus que son activité parente.
            expect($operationProcessById[$link->operation_id])->toBe($activityProcessByActivity[$activityId]);
        }
    }

    $taskPerimeterById = DB::table('tasks')->pluck('perimeter_id', 'id');
    $tasksByOperation = DB::table('operation_task')->get()->groupBy('operation_id');

    // Chaque tâche est dédiée à une seule opération (pas partagée) : autant
    // de liens que de tâches créées.
    expect(DB::table('operation_task')->count())->toBe(DB::table('tasks')->count());

    foreach ($tasksByOperation as $operationId => $links) {
        expect($links->count())->toBeGreaterThanOrEqual($taskRange['min'])
            ->and($links->count())->toBeLessThanOrEqual($taskRange['max']);

        foreach ($links as $link) {
            expect($taskPerimeterById[$link->task_id])->toBe($operationPerimeterById[$operationId]);
        }
    }
});

it('creates actors per perimeter, each assigned to operations of its own perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 0,
        '--force' => true,
    ])->run();

    $actorsByPerimeter = DB::table('actors')->get()->groupBy('perimeter_id');

    expect($actorsByPerimeter)->toHaveCount(2);

    $actorRange = config('data-scenarios.actors_per_perimeter');

    foreach ($actorsByPerimeter as $actors) {
        expect($actors->count())->toBeGreaterThanOrEqual($actorRange['min'])
            ->and($actors->count())->toBeLessThanOrEqual($actorRange['max']);
    }

    $actorPerimeterById = DB::table('actors')->pluck('perimeter_id', 'id');
    $operationPerimeterById = DB::table('operations')->pluck('perimeter_id', 'id');
    $operationsByActor = DB::table('actor_operation')->get()->groupBy('actor_id');

    foreach ($actorsByPerimeter->flatten(1) as $actor) {
        $links = $operationsByActor->get($actor->id) ?? collect();

        expect($links->count())->toBeLessThanOrEqual(config('data-scenarios.actor_operations_per_actor.max'));

        foreach ($links as $link) {
            expect($operationPerimeterById[$link->operation_id])->toBe($actorPerimeterById[$actor->id]);
        }
    }
});

it('creates informations per perimeter within the configured range', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 3,
        '--applications' => 0,
        '--force' => true,
    ])->run();

    $informationsByPerimeter = DB::table('information')->get()->groupBy('perimeter_id');

    expect($informationsByPerimeter)->toHaveCount(3);

    $informationRange = config('data-scenarios.informations_per_perimeter');

    foreach ($informationsByPerimeter as $informations) {
        expect($informations->count())->toBeGreaterThanOrEqual($informationRange['min'])
            ->and($informations->count())->toBeLessThanOrEqual($informationRange['max']);
    }
});

it('creates about a hundred entities per perimeter, each with 0 to 3 relations to another entity of the same perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 0,
        '--force' => true,
    ])->run();

    $entitiesByPerimeter = DB::table('entities')->get()->groupBy('perimeter_id');

    expect($entitiesByPerimeter)->toHaveCount(2);

    foreach ($entitiesByPerimeter as $entities) {
        expect($entities->count())->toBe(100);
    }

    $entityPerimeterById = DB::table('entities')->pluck('perimeter_id', 'id');
    $relationsBySource = DB::table('relations')->get()->groupBy('source_id');

    foreach ($relationsBySource as $sourceId => $relations) {
        expect($relations->count())->toBeLessThanOrEqual(3);

        foreach ($relations as $relation) {
            expect($relation->source_id)->not->toBe($relation->destination_id)
                ->and($entityPerimeterById[$relation->destination_id])->toBe($entityPerimeterById[$sourceId])
                ->and($relation->perimeter_id)->toBe($entityPerimeterById[$sourceId]);
        }
    }

    // Avec 100 entités par périmètre et 0 à 3 relations chacune, on
    // s'attend statistiquement à un volume de relations non négligeable.
    expect(DB::table('relations')->count())->toBeGreaterThan(0);
});

it('links each database to 1 to 5 informations, and each flow to 0 to 2 informations, all from the same perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 10,
        '--databases' => 10,
        '--flows' => 30,
        '--force' => true,
    ])->run();

    $databasePerimeterById = DB::table('databases')->pluck('perimeter_id', 'id');
    $informationPerimeterById = DB::table('information')->pluck('perimeter_id', 'id');
    $informationsByDatabase = DB::table('database_information')->get()->groupBy('database_id');

    foreach ($databasePerimeterById as $databaseId => $perimeterId) {
        $links = $informationsByDatabase->get($databaseId) ?? collect();

        expect($links->count())->toBeGreaterThanOrEqual(1)
            ->and($links->count())->toBeLessThanOrEqual(5);

        foreach ($links as $link) {
            expect($informationPerimeterById[$link->information_id])->toBe($perimeterId);
        }
    }

    $flowPerimeterById = DB::table('application_flows')->pluck('perimeter_id', 'id');
    $informationsByFlow = DB::table('application_flow_information')->get()->groupBy('flux_id');

    foreach ($flowPerimeterById as $flowId => $perimeterId) {
        $links = $informationsByFlow->get($flowId) ?? collect();

        expect($links->count())->toBeLessThanOrEqual(2);

        foreach ($links as $link) {
            expect($informationPerimeterById[$link->information_id])->toBe($perimeterId);
        }
    }
});

it('creates 20 to 50 data processing records per perimeter, each linked to 1-3 applications, processes and informations of its own perimeter', function () {
    $this->artisan('mercator:generate-test-data', [
        '--perimeters' => 2,
        '--applications' => 15,
        '--force' => true,
    ])->run();

    $dataProcessingByPerimeter = DB::table('data_processing')->get()->groupBy('perimeter_id');

    expect($dataProcessingByPerimeter)->toHaveCount(2);

    foreach ($dataProcessingByPerimeter as $records) {
        expect($records->count())->toBeGreaterThanOrEqual(20)
            ->and($records->count())->toBeLessThanOrEqual(50);
    }

    $lawfulnessColumns = [
        'Consentement' => 'lawfulness_consent',
        'Contrat' => 'lawfulness_contract',
        'Obligation legale' => 'lawfulness_legal_obligation',
        'Interet vital' => 'lawfulness_vital_interest',
        'Mission d\'interet public' => 'lawfulness_public_interest',
        'Interet legitime' => 'lawfulness_legitimate_interest',
    ];

    DB::table('data_processing')->get()->each(function ($record) use ($lawfulnessColumns) {
        $trueColumns = [];

        foreach ($lawfulnessColumns as $legalBasis => $column) {
            if ((bool) $record->$column) {
                $trueColumns[] = $legalBasis;
            }
        }

        expect($trueColumns)->toHaveCount(1)
            ->and($trueColumns[0])->toBe($record->legal_basis);
    });

    $dataProcessingPerimeterById = DB::table('data_processing')->pluck('perimeter_id', 'id');
    $applicationPerimeterById = DB::table('applications')->pluck('perimeter_id', 'id');
    $processPerimeterById = DB::table('processes')->pluck('perimeter_id', 'id');
    $informationPerimeterById = DB::table('information')->pluck('perimeter_id', 'id');

    $checkLinks = function (string $table, string $relatedColumn, $relatedPerimeterById) use ($dataProcessingPerimeterById) {
        $byDataProcessing = DB::table($table)->get()->groupBy('data_processing_id');

        foreach ($dataProcessingPerimeterById as $dataProcessingId => $perimeterId) {
            $links = $byDataProcessing->get($dataProcessingId) ?? collect();

            expect($links->count())->toBeGreaterThanOrEqual(1)
                ->and($links->count())->toBeLessThanOrEqual(3);

            foreach ($links as $link) {
                expect($relatedPerimeterById[$link->$relatedColumn])->toBe($perimeterId);
            }
        }
    };

    $checkLinks('application_data_processing', 'application_id', $applicationPerimeterById);
    $checkLinks('data_processing_process', 'process_id', $processPerimeterById);
    $checkLinks('data_processing_information', 'information_id', $informationPerimeterById);
});
