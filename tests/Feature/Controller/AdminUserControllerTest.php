
<?php

use App\Models\AdminUser;
use App\Models\User;
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

    $this->user = User::query()->where('login', 'admin@admin.com')->first();
    $this->actingAs($this->user);

});

describe('index', function () {
    test('can display admin-users index page', function () {
        AdminUser::factory()->count(3)->create();

        $response = $this->get(route('admin.admin-users.index'));

        $response->assertOk();
        $response->assertViewIs('admin.adminUser.index');
        $response->assertViewHas('users');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.admin-users.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.admin-users.create'));

        $response->assertOk();
        $response->assertViewIs('admin.adminUser.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.admin-users.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $user_id = fake()->word();
        $adminUser = AdminUser::factory()->create(['user_id' => $user_id]);

        $response = $this->get(route('admin.admin-users.show', $adminUser->id));

        $response->assertOk();
        $response->assertViewIs('admin.adminUser.show');
        $response->assertSee($user_id);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user_id = fake()->word();
        $adminUser = AdminUser::factory()->create(['user_id' => $user_id]);

        $response = $this->get(route('admin.admin-users.show', $adminUser->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $user_id = fake()->word();
        $adminUser = AdminUser::factory()->create(['user_id' => $user_id]);

        $response = $this->get(route('admin.admin-users.edit', $adminUser));

        $response->assertOk();
        $response->assertViewIs('admin.adminUser.edit');
        $response->assertViewHas('adminUser');
        $response->assertSee($user_id);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $adminUser = AdminUser::factory()->create();

        $response = $this->get(route('admin.admin-users.edit', $adminUser));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update AdminUser', function () {
        $user_id = fake()->word();
        $adminUser = AdminUser::factory()->create(['user_id' => $user_id]);

        $data = [
            'user_id' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.admin-users.update', $adminUser), $data);

        $response->assertRedirect(route('admin.admin-users.index'));
        $this->assertDatabaseHas('admin_users', ['user_id' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete AdminUser', function () {
        $adminUser = AdminUser::factory()->create();

        $response = $this->delete(route('admin.admin-users.destroy', $adminUser->id));
        $response->assertRedirect(route('admin.admin-users.index'));

        $this->assertSoftDeleted('admin_users', ['id' => $adminUser->id]);

        $adminUser->refresh();
        expect($adminUser->deleted_at)->not->toBeNull()
            ->and($adminUser->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $adminUser = AdminUser::factory()->create();

        $response = $this->delete(route('admin.admin-users.destroy', $adminUser));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple admin-users', function () {
        $adminUsers = AdminUser::factory()->count(3)->create();
        $ids = $adminUsers->pluck('id')->toArray();

        $response = $this->delete(route('admin.admin-users.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('admin_users', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $adminUser = AdminUser::factory()->create();

        $response = $this->delete(route('admin.admin-users.massDestroy'), [
            'ids' => [$adminUser->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $adminUser = AdminUser::factory()->create();

        $response = $this->delete(route('admin.admin-users.massDestroy'), [
            'ids' => [$adminUser->id],
        ]);

        $response->assertForbidden();
    });

});
