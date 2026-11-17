<?php

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

describe('Application Flows View', function () {
    test('can display application-flows view page', function () {
        $response = $this->get(route('admin.report.view.application-flows'));

        $response->assertOk();
        $response->assertViewIs('admin.reports.application_flows');
        $response->assertViewHasAll([
            'all_applicationBlocks',
            'all_applications',
            'all_databases',
            'applications',
            'applicationServices',
            'applicationModules',
            'databases',
            'flows',
        ]);
    });

    test('denies access without permission', function () {
        // New user without the reports_access permission
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.report.view.application-flows'));

        $response->assertForbidden();
    });
});
