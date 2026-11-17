
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
    test('can display users index page', function () {
        User::factory()->count(3)->create();

        $response = $this->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.users.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.users.create'));

        $response->assertOk();
        $response->assertViewIs('admin.users.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.users.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $User = User::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.users.show', $User->id));

        $response->assertOk();
        $response->assertViewIs('admin.users.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $User = User::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.users.show', $User->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $User = User::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.users.edit', $User));

        $response->assertOk();
        $response->assertViewIs('admin.users.edit');
        $response->assertViewHas('user');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $User = User::factory()->create();

        $response = $this->get(route('admin.users.edit', $User));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update User', function () {
        $name = fake()->word();
        $User = User::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'email' => fake()->email(),
            'roles' => [$this->user->roles[0]->id],
            'granularity' => 1,
        ];

        $response = $this->put(route('admin.users.update', $User), $data);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete User', function () {
        $User = User::factory()->create();

        $response = $this->delete(route('admin.users.destroy', $User->id));
        $response->assertRedirect(route('admin.users.index'));

        $this->assertSoftDeleted('users', ['id' => $User->id]);

        $User->refresh();
        expect($User->deleted_at)->not->toBeNull()
            ->and($User->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $User = User::factory()->create();

        $response = $this->delete(route('admin.users.destroy', $User));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple users', function () {
        $users = User::factory()->count(3)->create();
        $ids = $users->pluck('id')->toArray();

        $response = $this->delete(route('admin.users.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('users', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $User = User::factory()->create();

        $response = $this->delete(route('admin.users.massDestroy'), [
            'ids' => [$User->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $User = User::factory()->create();

        $response = $this->delete(route('admin.users.massDestroy'), [
            'ids' => [$User->id],
        ]);

        $response->assertForbidden();
    });

});
