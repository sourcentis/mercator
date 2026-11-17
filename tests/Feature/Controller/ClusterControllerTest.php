<?php

use App\Models\Cluster;
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
    test('can display clusters index page', function () {
        Cluster::factory()->count(3)->create();

        $response = $this->get(route('admin.clusters.index'));

        $response->assertOk();
        $response->assertViewIs('admin.clusters.index');
        $response->assertViewHas('clusters');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.clusters.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.clusters.create'));

        $response->assertOk();
        $response->assertViewIs('admin.clusters.create');
        $response->assertViewHas(['type_list']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.clusters.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $cluster = Cluster::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.clusters.show', $cluster->id));

        $response->assertOk();
        $response->assertViewIs('admin.clusters.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $cluster = Cluster::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.clusters.show', $cluster->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $cluster = Cluster::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.clusters.edit', $cluster));

        $response->assertOk();
        $response->assertViewIs('admin.clusters.edit');
        $response->assertViewHas('type_list');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cluster = Cluster::factory()->create();

        $response = $this->get(route('admin.clusters.edit', $cluster));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $cluster = Cluster::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.clusters.update', $cluster), $data);

        $response->assertRedirect(route('admin.clusters.index'));
        $this->assertDatabaseHas('clusters', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $cluster = Cluster::factory()->create();

        $response = $this->delete(route('admin.clusters.destroy', $cluster->id));
        $response->assertRedirect(route('admin.clusters.index'));

        $this->assertSoftDeleted('clusters', ['id' => $cluster->id]);

        $cluster->refresh();
        expect($cluster->deleted_at)->not->toBeNull()
            ->and($cluster->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cluster = Cluster::factory()->create();

        $response = $this->delete(route('admin.clusters.destroy', $cluster));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple clusters', function () {
        $clusters = Cluster::factory()->count(3)->create();
        $ids = $clusters->pluck('id')->toArray();

        $response = $this->delete(route('admin.clusters.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('clusters', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $cluster = Cluster::factory()->create();

        $response = $this->delete(route('admin.clusters.massDestroy'), [
            'ids' => [$cluster->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cluster = Cluster::factory()->create();

        $response = $this->delete(route('admin.clusters.massDestroy'), [
            'ids' => [$cluster->id],
        ]);

        $response->assertForbidden();
    });

});
