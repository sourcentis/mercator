<?php

// Non-régression GHSA-4x92-9hqx-5f58 : un gestionnaire d'utilisateurs non administrateur ne
// doit pas pouvoir s'attribuer (ni attribuer) le rôle Admin ou un rôle plus privilégié que lui,
// ni modifier/supprimer un utilisateur plus privilégié.

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    $this->admin = User::query()->where('login', 'admin@admin.com')->first();

    $userPermissions = Permission::query()
        ->whereIn('title', ['user_access', 'user_create', 'user_edit', 'user_show', 'user_delete'])
        ->pluck('id');

    $this->managerRole = Role::query()->create(['title' => 'User manager']);
    $this->managerRole->permissions()->sync($userPermissions);

    $this->lesserRole = Role::query()->create(['title' => 'User reader']);
    $this->lesserRole->permissions()->sync(
        Permission::query()->whereIn('title', ['user_access', 'user_show'])->pluck('id')
    );

    $this->strongerRole = Role::query()->create(['title' => 'Stronger']);
    $this->strongerRole->permissions()->sync(
        $userPermissions->merge(Permission::query()->where('title', 'role_edit')->pluck('id'))
    );

    $this->bob = User::factory()->create(['login' => 'bob', 'name' => 'bob', 'email' => 'bob@test.com']);
    $this->bob->roles()->sync([$this->managerRole->id]);
});

function userPayload(User $user, array $roles): array
{
    return [
        'name' => $user->name,
        'login' => $user->login,
        'email' => $user->email,
        'granularity' => 1,
        'roles' => $roles,
    ];
}

describe('web', function () {
    beforeEach(fn () => $this->actingAs($this->bob));

    test('manager cannot grant himself the Admin role', function () {
        $this->put(route('admin.users.update', $this->bob), userPayload($this->bob, [1]))
            ->assertForbidden();

        expect($this->bob->fresh()->isAdmin())->toBeFalse();
    });

    test('manager cannot grant a role more privileged than his own', function () {
        $this->put(route('admin.users.update', $this->bob), userPayload($this->bob, [$this->strongerRole->id]))
            ->assertForbidden();
    });

    test('manager cannot create an admin user', function () {
        $this->post(route('admin.users.store'), [
            'name' => 'eve', 'login' => 'eve', 'email' => 'eve@test.com',
            'password' => 'secret-password', 'granularity' => 1, 'roles' => [1],
        ])->assertForbidden();

        expect(User::query()->where('login', 'eve')->exists())->toBeFalse();
    });

    test('manager cannot edit or delete an admin', function () {
        $this->get(route('admin.users.edit', $this->admin))->assertForbidden();
        $this->put(route('admin.users.update', $this->admin), userPayload($this->admin, [1]) + ['password' => 'pwned-password'])
            ->assertForbidden();
        $this->delete(route('admin.users.destroy', $this->admin))->assertForbidden();

        expect($this->admin->fresh())->not->toBeNull();
    });

    test('manager can still assign roles within his own privileges', function () {
        $alice = User::factory()->create();

        $this->put(route('admin.users.update', $alice), userPayload($alice, [$this->lesserRole->id]))
            ->assertRedirect(route('admin.users.index'));

        expect($alice->fresh()->roles->pluck('id')->all())->toBe([$this->lesserRole->id]);
    });

    test('edit form only lists grantable roles', function () {
        $alice = User::factory()->create();

        $roles = $this->get(route('admin.users.edit', $alice))->assertOk()->viewData('roles');

        expect($roles->keys()->all())->not->toContain(1)
            ->not->toContain($this->strongerRole->id)
            ->toContain($this->lesserRole->id);
    });

    test('admin can still grant the Admin role', function () {
        $this->actingAs($this->admin);

        $this->put(route('admin.users.update', $this->bob), userPayload($this->bob, [1]))
            ->assertRedirect(route('admin.users.index'));

        expect($this->bob->fresh()->isAdmin())->toBeTrue();
    });
});

describe('api', function () {
    beforeEach(fn () => Passport::actingAs($this->bob));

    test('manager cannot grant himself the Admin role', function () {
        $this->putJson("/api/users/{$this->bob->id}", userPayload($this->bob, [1]))->assertForbidden();

        expect($this->bob->fresh()->isAdmin())->toBeFalse();
    });

    test('manager cannot create an admin via store or mass-store', function () {
        $item = ['name' => 'eve', 'login' => 'eve', 'email' => 'eve@test.com', 'granularity' => 1, 'roles' => [1]];

        $this->postJson('/api/users', $item)->assertForbidden();
        $this->postJson('/api/users/mass-store', ['items' => [$item]])->assertForbidden();

        expect(User::query()->where('login', 'eve')->exists())->toBeFalse();
    });

    test('manager cannot escalate via mass-update', function () {
        $this->putJson('/api/users/mass-update', [
            'items' => [[
                'id' => $this->bob->id, 'name' => 'bob2', 'email' => 'bob2@test.com',
                'granularity' => 1, 'roles' => [1],
            ]],
        ])->assertForbidden();

        expect($this->bob->fresh()->isAdmin())->toBeFalse();
    });

    test('manager cannot modify or delete an admin', function () {
        $this->putJson("/api/users/{$this->admin->id}", ['password' => 'pwned-password'] + userPayload($this->admin, [1]))
            ->assertForbidden();
        $this->deleteJson("/api/users/{$this->admin->id}")->assertForbidden();
        $this->deleteJson('/api/users/mass-destroy', ['ids' => [$this->admin->id]])->assertForbidden();

        expect($this->admin->fresh())->not->toBeNull();
    });
});
