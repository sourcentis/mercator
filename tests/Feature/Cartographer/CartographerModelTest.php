<?php

use App\Models\Application;
use App\Models\Cartographer;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::forget('permissions_roles_map');
    Cache::forget('cartographers_last_update');

    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);
});

it('creates a cartographer entry for a user', function () {
    $user = User::factory()->create();
    $app = Application::factory()->create();

    Cartographer::create([
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $app->id,
        'user_id' => $user->id,
    ]);

    expect(Cartographer::count())->toBe(1);
});

it('creates a cartographer entry for a role', function () {
    $role = Role::factory()->create();
    $app = Application::factory()->create();

    Cartographer::create([
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $app->id,
        'role_id' => $role->id,
    ]);

    expect(Cartographer::count())->toBe(1);
});

it('updates cartographers_last_update cache on save', function () {
    Cache::forget('cartographers_last_update');
    $user = User::factory()->create();
    $app = Application::factory()->create();

    Cartographer::create([
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $app->id,
        'user_id' => $user->id,
    ]);

    expect(Cache::has('cartographers_last_update'))->toBeTrue();
});

it('updates cartographers_last_update cache on delete', function () {
    $user = User::factory()->create();
    $app = Application::factory()->create();
    $entry = Cartographer::create([
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $app->id,
        'user_id' => $user->id,
    ]);

    Cache::forget('cartographers_last_update');
    $entry->delete();

    expect(Cache::has('cartographers_last_update'))->toBeTrue();
});

it('loadSessionFor stores permissions grouped by type', function () {
    $user = User::factory()->create();
    $app1 = Application::factory()->create();
    $app2 = Application::factory()->create();

    Cartographer::create(['cartographiable_type' => Application::class, 'cartographiable_id' => $app1->id, 'user_id' => $user->id]);
    Cartographer::create(['cartographiable_type' => Application::class, 'cartographiable_id' => $app2->id, 'user_id' => $user->id]);

    $this->actingAs($user);
    Cartographer::loadSessionFor($user);

    $permissions = session('cartographer_permissions');
    expect($permissions)
        ->toHaveKey(Application::class)
        ->and($permissions[Application::class])
        ->toContain($app1->id)
        ->toContain($app2->id);
});

it('isAllowed returns true for directly assigned user', function () {
    $user = User::factory()->create();
    $app = Application::factory()->create();

    Cartographer::create(['cartographiable_type' => Application::class, 'cartographiable_id' => $app->id, 'user_id' => $user->id]);
    $this->actingAs($user);
    Cartographer::loadSessionFor($user);

    expect(Cartographer::isAllowed($user, $app))->toBeTrue();
});

it('isAllowed returns false for non-assigned user', function () {
    $user = User::factory()->create();
    $app = Application::factory()->create();

    $this->actingAs($user);
    Cartographer::loadSessionFor($user);

    expect(Cartographer::isAllowed($user, $app))->toBeFalse();
});

it('isAllowed returns true via role assignment', function () {
    $role = Role::factory()->create();
    $user = User::factory()->create();
    $user->roles()->attach($role);
    $app = Application::factory()->create();

    Cartographer::create(['cartographiable_type' => Application::class, 'cartographiable_id' => $app->id, 'role_id' => $role->id]);
    $this->actingAs($user);
    Cartographer::loadSessionFor($user);

    expect(Cartographer::isAllowed($user, $app))->toBeTrue();
});

it('allowedIdsFor returns correct ids from session', function () {
    $user = User::factory()->create();
    $app1 = Application::factory()->create();
    $app2 = Application::factory()->create();

    Cartographer::create(['cartographiable_type' => Application::class, 'cartographiable_id' => $app1->id, 'user_id' => $user->id]);
    $this->actingAs($user);
    Cartographer::loadSessionFor($user);

    $ids = Cartographer::allowedIdsFor($user, Application::class);
    expect($ids)
        ->toContain($app1->id)
        ->not->toContain($app2->id);
});
