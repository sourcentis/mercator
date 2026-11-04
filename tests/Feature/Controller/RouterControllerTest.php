
<?php

use App\Models\Router;
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
    test('can display routers index page', function () {
        Router::factory()->count(3)->create();

        $response = $this->get(route('admin.routers.index'));

        $response->assertOk();
        $response->assertViewIs('admin.routers.index');
        $response->assertViewHas('routers');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.routers.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.routers.create'));

        $response->assertOk();
        $response->assertViewIs('admin.routers.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.routers.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $Router = Router::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.routers.show', $Router->id));

        $response->assertOk();
        $response->assertViewIs('admin.routers.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $Router = Router::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.routers.show', $Router->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $Router = Router::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.routers.edit', $Router));

        $response->assertOk();
        $response->assertViewIs('admin.routers.edit');
        $response->assertViewHas('router');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Router = Router::factory()->create();

        $response = $this->get(route('admin.routers.edit', $Router));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Router', function () {
        $name = fake()->word();
        $Router = Router::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.routers.update', $Router), $data);

        $response->assertRedirect(route('admin.routers.index'));
        $this->assertDatabaseHas('routers', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete Router', function () {
        $Router = Router::factory()->create();

        $response = $this->delete(route('admin.routers.destroy', $Router->id));
        $response->assertRedirect(route('admin.routers.index'));

        $this->assertSoftDeleted('routers', ['id' => $Router->id]);

        $Router->refresh();
        expect($Router->deleted_at)->not->toBeNull()
            ->and($Router->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Router = Router::factory()->create();

        $response = $this->delete(route('admin.routers.destroy', $Router));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple routers', function () {
        $routers = Router::factory()->count(3)->create();
        $ids = $routers->pluck('id')->toArray();

        $response = $this->delete(route('admin.routers.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('routers', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $Router = Router::factory()->create();

        $response = $this->delete(route('admin.routers.massDestroy'), [
            'ids' => [$Router->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Router = Router::factory()->create();

        $response = $this->delete(route('admin.routers.massDestroy'), [
            'ids' => [$Router->id],
        ]);

        $response->assertForbidden();
    });

});
