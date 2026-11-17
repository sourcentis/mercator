<?php

use App\Models\Man;
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
    test('can display man index page', function () {
        Man::factory()->count(3)->create();

        $response = $this->get(route('admin.mans.index'));

        $response->assertOk();
        $response->assertViewIs('admin.mans.index');
        $response->assertViewHas('mans');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.mans.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.mans.create'));

        $response->assertOk();
        $response->assertViewIs('admin.mans.create');
        $response->assertViewHas(['lans']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.mans.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $container = Man::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.mans.show', $container->id));

        $response->assertOk();
        $response->assertViewIs('admin.mans.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $container = Man::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.mans.show', $container->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $container = Man::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.mans.edit', $container));

        $response->assertOk();
        $response->assertViewIs('admin.mans.edit');
        $response->assertViewHas('man');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $container = Man::factory()->create();

        $response = $this->get(route('admin.mans.edit', $container));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $container = Man::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.mans.update', $container), $data);

        $response->assertRedirect(route('admin.mans.index'));
        $this->assertDatabaseHas('mans', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $container = Man::factory()->create();

        $response = $this->delete(route('admin.mans.destroy', $container->id));
        $response->assertRedirect(route('admin.mans.index'));

        $this->assertSoftDeleted('mans', ['id' => $container->id]);

        $container->refresh();
        expect($container->deleted_at)->not->toBeNull()
            ->and($container->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $container = Man::factory()->create();

        $response = $this->delete(route('admin.mans.destroy', $container));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple man', function () {
        $mans = Man::factory()->count(3)->create();
        $ids = $mans->pluck('id')->toArray();

        $response = $this->delete(route('admin.mans.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('mans', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $man = Man::factory()->create();

        $response = $this->delete(route('admin.mans.massDestroy'), [
            'ids' => [$man->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $man = Man::factory()->create();

        $response = $this->delete(route('admin.mans.massDestroy'), [
            'ids' => [$man->id],
        ]);

        $response->assertForbidden();
    });

});
