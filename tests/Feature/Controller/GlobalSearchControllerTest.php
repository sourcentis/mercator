<?php

use App\Models\User;
use App\Models\ZoneAdmin;
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

    $this->user = User::query()->where('login', 'admin@admin.com')->first();
    $this->actingAs($this->user);
});

describe('GlobalSearchController', function () {
    test('can display search page without term', function () {
        $response = $this->get(route('admin.globalSearch'));

        $response->assertOk();
        $response->assertViewIs('admin.search');
        $response->assertViewHas('searchableData', function ($data) {
            return is_array($data) && count($data) === 0;
        });
    });

    test('returns results for a matching ZoneAdmin', function () {
        $zone = ZoneAdmin::factory()->create(['name' => 'AlphaZone']);

        $response = $this->get(route('admin.globalSearch', ['search' => 'Alp'])); // 3+ chars

        $response->assertOk();
        $response->assertViewIs('admin.search');
        $response->assertViewHas('searchableData', function ($data) use ($zone) {
            if (! is_array($data) || empty($data)) {
                return false;
            }
            // Look for at least one item for the ZoneAdmin model with our name
            foreach ($data as $item) {
                if (($item['model'] ?? null) === 'ZoneAdmin' && ($item['data']['name'] ?? null) === 'AlphaZone') {
                    // also ensure URL points to the show page under /admin/zone-admins/{id}
                    $expectedUrl = '/admin/zone-admins/'.$zone->id;

                    return ($item['url'] ?? '') === $expectedUrl;
                }
            }

            return false;
        });
    });

    test('redirects to login when unauthenticated', function () {
        auth()->logout();

        $response = $this->get(route('admin.globalSearch'));

        $response->assertStatus(302);
        $response->assertRedirect('/login?locale=en');
    });
});
