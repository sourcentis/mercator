<?php

use App\Models\Container;
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
    test('can display containers index page', function () {
        Container::factory()->count(3)->create();

        $response = $this->get(route('admin.containers.index'));

        $response->assertOk();
        $response->assertViewIs('admin.containers.index');
        $response->assertViewHas('containers');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.containers.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.containers.create'));

        $response->assertOk();
        $response->assertViewIs('admin.containers.create');
        $response->assertViewHas(['type_list']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.containers.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $container = Container::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.containers.show', $container->id));

        $response->assertOk();
        $response->assertViewIs('admin.containers.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $container = Container::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.containers.show', $container->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $container = Container::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.containers.edit', $container));

        $response->assertOk();
        $response->assertViewIs('admin.containers.edit');
        $response->assertViewHas('container');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $container = Container::factory()->create();

        $response = $this->get(route('admin.containers.edit', $container));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $container = Container::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.containers.update', $container), $data);

        $response->assertRedirect(route('admin.containers.index'));
        $this->assertDatabaseHas('containers', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $container = Container::factory()->create();

        $response = $this->delete(route('admin.containers.destroy', $container->id));
        $response->assertRedirect(route('admin.containers.index'));

        $this->assertSoftDeleted('containers', ['id' => $container->id]);

        $container->refresh();
        expect($container->deleted_at)->not->toBeNull()
            ->and($container->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $container = Container::factory()->create();

        $response = $this->delete(route('admin.containers.destroy', $container));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple containers', function () {
        $containers = Container::factory()->count(3)->create();
        $ids = $containers->pluck('id')->toArray();

        $response = $this->delete(route('admin.containers.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('containers', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $container = Container::factory()->create();

        $response = $this->delete(route('admin.containers.massDestroy'), [
            'ids' => [$container->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $container = Container::factory()->create();

        $response = $this->delete(route('admin.containers.massDestroy'), [
            'ids' => [$container->id],
        ]);

        $response->assertForbidden();
    });

});
