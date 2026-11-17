<?php

use App\Models\ExternalConnectedEntity;
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
    test('can display external-connected-entities index page', function () {
        ExternalConnectedEntity::factory()->count(3)->create();

        $response = $this->get(route('admin.external-connected-entities.index'));

        $response->assertOk();
        $response->assertViewIs('admin.externalConnectedEntities.index');
        $response->assertViewHas('externalConnectedEntities');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.external-connected-entities.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.external-connected-entities.create'));

        $response->assertOk();
        $response->assertViewIs('admin.externalConnectedEntities.create');
        $response->assertViewHas(['entities', 'type_list']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.external-connected-entities.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $entity = ExternalConnectedEntity::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.external-connected-entities.show', $entity->id));

        $response->assertOk();
        $response->assertViewIs('admin.externalConnectedEntities.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $entity = ExternalConnectedEntity::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.external-connected-entities.show', $entity->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $entity = ExternalConnectedEntity::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.external-connected-entities.edit', $entity));

        $response->assertOk();
        $response->assertViewIs('admin.externalConnectedEntities.edit');
        $response->assertViewHas('externalConnectedEntity');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $entity = ExternalConnectedEntity::factory()->create();

        $response = $this->get(route('admin.external-connected-entities.edit', $entity));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $entity = ExternalConnectedEntity::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.external-connected-entities.update', $entity), $data);

        $response->assertRedirect(route('admin.external-connected-entities.index'));
        $this->assertDatabaseHas('external_connected_entities', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $entity = ExternalConnectedEntity::factory()->create();

        $response = $this->delete(route('admin.external-connected-entities.destroy', $entity->id));
        $response->assertRedirect(route('admin.external-connected-entities.index'));

        $this->assertSoftDeleted('external_connected_entities', ['id' => $entity->id]);

        $entity->refresh();
        expect($entity->deleted_at)->not->toBeNull()
            ->and($entity->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $entity = ExternalConnectedEntity::factory()->create();

        $response = $this->delete(route('admin.external-connected-entities.destroy', $entity));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple external-connected-entities', function () {
        $entity = ExternalConnectedEntity::factory()->count(3)->create();
        $ids = $entity->pluck('id')->toArray();

        $response = $this->delete(route('admin.external-connected-entities.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('external_connected_entities', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $entity = ExternalConnectedEntity::factory()->create();

        $response = $this->delete(route('admin.external-connected-entities.massDestroy'), [
            'ids' => [$entity->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $entity = ExternalConnectedEntity::factory()->create();

        $response = $this->delete(route('admin.external-connected-entities.massDestroy'), [
            'ids' => [$entity->id],
        ]);

        $response->assertForbidden();
    });

});
