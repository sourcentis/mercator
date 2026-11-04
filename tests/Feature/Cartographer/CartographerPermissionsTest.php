<?php

use App\Models\Application;
use App\Models\Cartographer;
use App\Models\LogicalServer;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Database\Eloquent\Model;
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

// ── Helpers ────────────────────────────────────────────────────────────────

function makeViewerUser(): User
{
    $user = User::factory()->create();
    // Use a role with no permissions for strict testing
    $role = Role::firstOrCreate(['title' => 'viewer']);
    $user->roles()->attach($role);

    return $user;
}

function makeAdminUser(): User
{
    return User::query()->where('login', 'admin@admin.com')->first();
}

function assignCartographerEntry(?User $user, ?Role $role, Model $object): void
{
    Cartographer::create([
        'cartographiable_type' => get_class($object),
        'cartographiable_id' => $object->getKey(),
        'user_id' => $user?->id,
        'role_id' => $role?->id,
    ]);
}

// ── Cartographer::canAccessAny ─────────────────────────────────────────────

it('canAccessAny returns false for user with no rights', function () {
    $user = makeViewerUser();
    $this->actingAs($user);
    Cartographer::loadSessionFor($user);
    expect(Cartographer::canAccessAny([Application::class]))->toBeFalse();
});

it('canAccessAny returns true when user has cartographer entry for that type', function () {
    $user = makeViewerUser();
    $app = Application::factory()->create();
    assignCartographerEntry($user, null, $app);
    $this->actingAs($user);
    Cartographer::loadSessionFor($user);
    expect(Cartographer::canAccessAny([Application::class]))->toBeTrue();
});

it('canAccessAny returns true when user has cartographer role for that type', function () {
    $role = Role::factory()->create(['title' => 'app-managers']);
    $user = makeViewerUser();
    $user->roles()->attach($role);
    $app = Application::factory()->create();
    assignCartographerEntry(null, $role, $app);
    Cartographer::loadSessionFor($user);
    $this->actingAs($user);
    expect(Cartographer::canAccessAny([Application::class]))->toBeTrue();
});

// ── index() ────────────────────────────────────────────────────────────────

it('index returns 403 for user with no rights and no cartographer entry', function () {
    $user = makeViewerUser();
    $this->actingAs($user);
    Cartographer::loadSessionFor($user);

    $this->get(route('admin.applications.index'))
        ->assertForbidden();
});

it('index returns only assigned application for cartographer user', function () {
    $user = makeViewerUser();
    $app1 = Application::factory()->create(['name' => 'App Alpha']);
    $app2 = Application::factory()->create(['name' => 'App Beta']);
    assignCartographerEntry($user, null, $app1);

    $this->actingAs($user);
    Cartographer::loadSessionFor($user);

    $response = $this->get(route('admin.applications.index'));
    $response->assertOk()
        ->assertSee('App Alpha')
        ->assertDontSee('App Beta');
});

it('index returns all applications for admin', function () {
    $app1 = Application::factory()->create(['name' => 'App One']);
    $app2 = Application::factory()->create(['name' => 'App Two']);

    $admin = makeAdminUser();
    $this->actingAs($admin);
    Cartographer::loadSessionFor($admin);

    $this->get(route('admin.applications.index'))
        ->assertOk()
        ->assertSee('App One')
        ->assertSee('App Two');
});

// ── show() / edit() ────────────────────────────────────────────────────────

it('show returns 403 for user not cartographer of that object', function () {
    $user = makeViewerUser();
    $app = Application::factory()->create();
    $this->actingAs($user);
    Cartographer::loadSessionFor($user);

    $this->get(route('admin.applications.show', $app))
        ->assertForbidden();
});

it('show is accessible for cartographer of that specific object', function () {
    $user = makeViewerUser();
    $app = Application::factory()->create();
    assignCartographerEntry($user, null, $app);

    $this->actingAs($user);
    Cartographer::loadSessionFor($user);

    $this->get(route('admin.applications.show', $app))
        ->assertOk();
});

it('show is forbidden for cartographer of a different object', function () {
    $user = makeViewerUser();
    $app1 = Application::factory()->create();
    $app2 = Application::factory()->create();
    assignCartographerEntry($user, null, $app1);

    $this->actingAs($user);
    Cartographer::loadSessionFor($user);

    $this->get(route('admin.applications.show', $app2))
        ->assertForbidden();
});

it('edit is accessible for cartographer of that specific object', function () {
    $user = makeViewerUser();
    $app = Application::factory()->create();
    assignCartographerEntry($user, null, $app);

    $this->actingAs($user);
    Cartographer::loadSessionFor($user);

    $this->get(route('admin.applications.edit', $app))
        ->assertOk();
});

// ── create() / destroy() bloqués pour cartographe ─────────────────────────

it('create is forbidden for cartographer user', function () {
    $user = makeViewerUser();
    $app = Application::factory()->create();
    assignCartographerEntry($user, null, $app);

    $this->actingAs($user);
    Cartographer::loadSessionFor($user);

    $this->get(route('admin.applications.create'))
        ->assertForbidden();
});

it('destroy is forbidden for cartographer user', function () {
    $user = makeViewerUser();
    $app = Application::factory()->create();
    assignCartographerEntry($user, null, $app);

    $this->actingAs($user);
    Cartographer::loadSessionFor($user);

    $this->post(route('admin.applications.destroy', $app), ['_method' => 'DELETE'])
        ->assertForbidden();
});

// ── Droits via rôle ────────────────────────────────────────────────────────

it('role-based cartographer grants access to all users with that role', function () {
    $role = Role::factory()->create(['title' => 'server-managers']);
    $user1 = makeViewerUser();
    $user2 = makeViewerUser();
    $user1->roles()->attach($role);
    $user2->roles()->attach($role);
    $server = LogicalServer::factory()->create();
    assignCartographerEntry(null, $role, $server);
    Cartographer::loadSessionFor($user1);
    Cartographer::loadSessionFor($user2);

    $this->actingAs($user1);
    $this->get(route('admin.logical-servers.show', $server))
        ->assertOk();

    $this->actingAs($user2);
    $this->get(route('admin.logical-servers.show', $server))
        ->assertOk();
});

// ── Invalidation de session par middleware ─────────────────────────────────

it('middleware reloads session when cartographers table changes after login', function () {
    $user = makeViewerUser();
    $app = Application::factory()->create();

    $this->actingAs($user);
    Cartographer::loadSessionFor($user);

    // Avant assignation : 403
    $this->get(route('admin.applications.show', $app))
        ->assertForbidden();

    // Assignation après login — booted() met à jour le cache timestamp
    assignCartographerEntry($user, null, $app);
    // Force cache timestamp to be newer than session timestamp
    Cache::put('cartographers_last_update', now()->timestamp + 1);

    // La prochaine requête déclenche le rechargement de session via le middleware
    $this->get(route('admin.applications.show', $app))
        ->assertOk();
});

// ── Admin non affecté ──────────────────────────────────────────────────────

it('admin can access everything regardless of cartographer entries', function () {
    $admin = makeAdminUser();
    $app = Application::factory()->create();

    $this->actingAs($admin);
    Cartographer::loadSessionFor($admin);

    $this->get(route('admin.applications.show', $app))
        ->assertOk();

    $this->get(route('admin.applications.edit', $app))
        ->assertOk();
});
