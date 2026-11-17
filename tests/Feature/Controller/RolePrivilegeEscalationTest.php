<?php

// Non-régression : un gestionnaire de rôles non administrateur (role_create / role_edit /
// role_delete) ne doit pas pouvoir ajouter à un rôle — en particulier le sien — une permission
// qu'il ne détient pas, déplacer un rôle vers un périmètre où il n'a pas ces droits, ni modifier
// ou supprimer le rôle Admin ou un rôle plus privilégié que lui.

use App\Models\Perimeter;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\PerimeterSettings;
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

    $this->rolePermissionIds = Permission::query()
        ->whereIn('title', ['role_access', 'role_create', 'role_edit', 'role_show', 'role_delete'])
        ->pluck('id')
        ->all();
    $this->userEditId = Permission::query()->where('title', 'user_edit')->value('id');

    $this->managerRole = Role::query()->create(['title' => 'Role manager', 'perimeter_id' => 1]);
    $this->managerRole->permissions()->sync($this->rolePermissionIds);

    $this->strongerRole = Role::query()->create(['title' => 'Stronger', 'perimeter_id' => 1]);
    $this->strongerRole->permissions()->sync([...$this->rolePermissionIds, $this->userEditId]);

    $this->carol = User::factory()->create();
    $this->carol->roles()->sync([$this->managerRole->id]);
});

function rolePayload(Role $role, array $permissions, ?int $perimeterId = null): array
{
    return [
        'title' => $role->title,
        'perimeter_id' => $perimeterId ?? $role->perimeter_id,
        'permissions' => $permissions,
    ];
}

describe('web', function () {
    beforeEach(fn () => $this->actingAs($this->carol));

    test('manager cannot add a permission he does not hold to his own role', function () {
        $this->put(
            route('admin.roles.update', $this->managerRole),
            rolePayload($this->managerRole, [...$this->rolePermissionIds, $this->userEditId])
        )->assertForbidden();

        expect($this->managerRole->permissions()->pluck('permissions.id')->all())
            ->not->toContain($this->userEditId);
    });

    test('manager cannot create a role with a permission he does not hold', function () {
        $this->post(route('admin.roles.store'), [
            'title' => 'Escalated', 'perimeter_id' => 1, 'permissions' => [$this->userEditId],
        ])->assertForbidden();

        expect(Role::query()->where('title', 'Escalated')->exists())->toBeFalse();
    });

    test('manager cannot edit or delete the Admin role or a more privileged role', function () {
        $admin = Role::query()->find(1);

        $this->get(route('admin.roles.edit', $admin))->assertForbidden();
        $this->put(route('admin.roles.update', $admin), rolePayload($admin, []))->assertForbidden();
        $this->delete(route('admin.roles.destroy', $admin))->assertForbidden();
        $this->put(
            route('admin.roles.update', $this->strongerRole),
            rolePayload($this->strongerRole, $this->rolePermissionIds)
        )->assertForbidden();

        expect(Role::query()->find(1)->permissions()->count())->toBeGreaterThan(0);
    });

    test('manager cannot move his role to a perimeter where he has no rights', function () {
        PerimeterSettings::setEnabled(true);
        $other = Perimeter::factory()->create(['name' => 'Site B']);

        $this->put(
            route('admin.roles.update', $this->managerRole),
            rolePayload($this->managerRole, $this->rolePermissionIds, $other->id)
        )->assertForbidden();

        expect($this->managerRole->fresh()->perimeter_id)->toBe(1);
    });

    test('manager can still manage roles within his own privileges', function () {
        $this->post(route('admin.roles.store'), [
            'title' => 'Role reader', 'perimeter_id' => 1, 'permissions' => [$this->rolePermissionIds[0]],
        ])->assertRedirect(route('admin.roles.index'));

        $this->put(
            route('admin.roles.update', $this->managerRole),
            rolePayload($this->managerRole, array_slice($this->rolePermissionIds, 0, 4))
        )->assertRedirect(route('admin.roles.index'));

        expect(Role::query()->where('title', 'Role reader')->exists())->toBeTrue()
            ->and($this->managerRole->permissions()->count())->toBe(4);
    });

    test('form greys out permissions the manager does not hold', function () {
        $roleEditId = Permission::query()->where('title', 'role_edit')->value('id');
        $checkbox = fn (string $html, int $id) => preg_match('/<input[^>]*id="perm_'.$id.'"[^>]*>/s', $html, $m) ? $m[0] : '';

        foreach ([route('admin.roles.create'), route('admin.roles.edit', $this->managerRole)] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            expect($checkbox($html, $this->userEditId))->toContain('disabled')
                ->and($checkbox($html, $roleEditId))->not->toContain('disabled');
        }
    });

    test('admin sees every permission enabled', function () {
        $this->actingAs($this->admin);

        $html = $this->get(route('admin.roles.create'))->assertOk()->getContent();

        expect(preg_match('/<input[^>]*id="perm_'.$this->userEditId.'"[^>]*>/s', $html, $m))->toBe(1)
            ->and($m[0])->not->toContain('disabled');
    });

    test('admin can still add any permission to any role', function () {
        $this->actingAs($this->admin);

        $this->put(
            route('admin.roles.update', $this->managerRole),
            rolePayload($this->managerRole, [...$this->rolePermissionIds, $this->userEditId])
        )->assertRedirect(route('admin.roles.index'));

        expect($this->managerRole->permissions()->pluck('permissions.id')->all())
            ->toContain($this->userEditId);
    });
});

describe('api', function () {
    beforeEach(fn () => Passport::actingAs($this->carol));

    test('manager cannot add a permission he does not hold to his own role', function () {
        $this->putJson(
            "/api/roles/{$this->managerRole->id}",
            rolePayload($this->managerRole, [...$this->rolePermissionIds, $this->userEditId])
        )->assertForbidden();

        expect($this->managerRole->permissions()->pluck('permissions.id')->all())
            ->not->toContain($this->userEditId);
    });

    test('manager cannot move his role to a perimeter where he has no rights', function () {
        PerimeterSettings::setEnabled(true);
        $other = Perimeter::factory()->create(['name' => 'Site B']);

        $this->putJson(
            "/api/roles/{$this->managerRole->id}",
            ['title' => $this->managerRole->title, 'perimeter_id' => $other->id]
        )->assertForbidden();

        expect($this->managerRole->fresh()->perimeter_id)->toBe(1);
    });

    test('manager cannot modify or delete the Admin role', function () {
        $this->putJson('/api/roles/1', ['title' => 'Owned', 'perimeter_id' => 1, 'permissions' => []])
            ->assertForbidden();
        $this->deleteJson('/api/roles/1')->assertForbidden();

        expect(Role::query()->find(1))->not->toBeNull();
    });
});
