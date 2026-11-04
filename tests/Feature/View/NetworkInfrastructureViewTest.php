<?php

use App\Models\Bay;
use App\Models\Building;
use App\Models\Peripheral;
use App\Models\PhysicalLink;
use App\Models\PhysicalServer;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed base permissions/roles and users as in other feature tests
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    // Login as an admin (id=1 seeded by UsersTableSeeder)
    $this->user = User::query()->where('login', 'admin@admin.com')->first();
    $this->actingAs($this->user);
});

describe('Network Infrastructure View', function () {
    test('can display network-infrastructure view page', function () {
        $response = $this->get(route('admin.report.view.network-infrastructure'));

        $response->assertOk();
        $response->assertViewIs('admin.reports.network_infrastructure');
        $response->assertViewHasAll([
            'all_sites',
            'sites',
            'all_buildings',
            'buildings',
            'bays',
            'physicalServers',
            'workstations',
            'storageDevices',
            'peripherals',
            'phones',
            'physicalSwitches',
            'physicalRouters',
            'wifiTerminals',
            'physicalSecurityDevices',
            'physicalLinks',
        ]);
    });

    test('can display filtered connectivity view for a selected site with a physical link', function () {
        $site = Site::factory()->create();
        $building = Building::factory()->create(['site_id' => $site->id]);
        $bay = Bay::factory()->create(['building_id' => $building->id, 'site_id' => null]);
        $server = PhysicalServer::factory()->create(['bay_id' => $bay->id, 'building_id' => null, 'site_id' => null]);
        $peripheral = Peripheral::factory()->create(['bay_id' => $bay->id, 'building_id' => null, 'site_id' => null]);
        PhysicalLink::factory()->create([
            'physical_server_src_id' => $server->id,
            'peripheral_dest_id' => $peripheral->id,
        ]);

        $response = $this->get(route('admin.report.view.network-infrastructure', [
            'site' => $site->id,
            'show_ports' => 1,
        ]));

        $response->assertOk();
        $response->assertViewIs('admin.reports.network_infrastructure');
        $response->assertViewHas('physicalLinks', function ($physicalLinks) {
            return $physicalLinks->count() === 1;
        });
    });

    test('denies access without permission', function () {
        // New user without the reports_access permission
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.report.view.network-infrastructure'));

        $response->assertForbidden();
    });
});
