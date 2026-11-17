<?php

use App\Models\Domain;
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
    test('can display domain-ad index page', function () {
        Domain::factory()->count(3)->create();

        $response = $this->get(route('admin.domains.index'));

        $response->assertOk();
        $response->assertViewIs('admin.domains.index');
        $response->assertViewHas('domains');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.domains.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.domains.create'));

        $response->assertOk();
        $response->assertViewIs('admin.domains.create');
        $response->assertViewHas(['logicalServers']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.domains.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $container = Domain::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.domains.show', $container->id));

        $response->assertOk();
        $response->assertViewIs('admin.domains.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $container = Domain::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.domains.show', $container->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $container = Domain::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.domains.edit', $container));

        $response->assertOk();
        $response->assertViewIs('admin.domains.edit');
        $response->assertViewHas('logicalServers');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $container = Domain::factory()->create();

        $response = $this->get(route('admin.domains.edit', $container));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $container = Domain::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.domains.update', $container), $data);

        $response->assertRedirect(route('admin.domains.index'));
        $this->assertDatabaseHas('domains', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $container = Domain::factory()->create();

        $response = $this->delete(route('admin.domains.destroy', $container->id));
        $response->assertRedirect(route('admin.domains.index'));

        $this->assertSoftDeleted('domains', ['id' => $container->id]);

        $container->refresh();
        expect($container->deleted_at)->not->toBeNull()
            ->and($container->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $container = Domain::factory()->create();

        $response = $this->delete(route('admin.domains.destroy', $container));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple domain-ad', function () {
        $domain = Domain::factory()->count(3)->create();
        $ids = $domain->pluck('id')->toArray();

        $response = $this->delete(route('admin.domains.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('domains', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $domain = Domain::factory()->create();

        $response = $this->delete(route('admin.domains.massDestroy'), [
            'ids' => [$domain->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $domain = Domain::factory()->create();

        $response = $this->delete(route('admin.domains.massDestroy'), [
            'ids' => [$domain->id],
        ]);

        $response->assertForbidden();
    });

});
