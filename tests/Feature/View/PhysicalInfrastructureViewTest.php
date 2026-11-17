<?php

use App\Models\Bay;
use App\Models\Building;
use App\Models\PhysicalServer;
use App\Models\Site;
use App\Models\User;
use App\Models\Workstation;
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

describe('Physical Infrastructure View', function () {
    test('can display physical-infrastructure view page', function () {
        $response = $this->get(route('admin.report.view.physical-infrastructure'));

        $response->assertOk();
        $response->assertViewIs('admin.reports.physical_infrastructure');
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
        ]);
    });

    test('can display filtered view for a selected site with bays, buildings and 5+ workstations', function () {
        $site = Site::factory()->create();
        $building = Building::factory()->create(['site_id' => $site->id]);
        $bay = Bay::factory()->create(['building_id' => $building->id, 'site_id' => null]);
        PhysicalServer::factory()->create(['bay_id' => $bay->id, 'building_id' => null, 'site_id' => null]);
        Workstation::factory()->count(6)->create(['building_id' => $building->id, 'site_id' => null]);

        $response = $this->get(route('admin.report.view.physical-infrastructure', ['site' => $site->id]));

        $response->assertOk();
        $response->assertViewIs('admin.reports.physical_infrastructure');
        $response->assertViewHas('buildings', function ($buildings) use ($building) {
            return $buildings->contains('id', $building->id);
        });
    });

    test('denies access without permission', function () {
        // New user without the reports_access permission
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.report.view.physical-infrastructure'));

        $response->assertForbidden();
    });
});
