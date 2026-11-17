<?php

use App\Models\Entity;
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
    test('can display entities index page', function () {
        Entity::factory()->count(3)->create();

        $response = $this->get(route('admin.entities.index'));

        $response->assertOk();
        $response->assertViewIs('admin.entities.index');
        $response->assertViewHas('entities');
        $response->assertSee(trans('cruds.entity.fields.type'));
        $response->assertSee(trans('cruds.entity.fields.attributes'));
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.entities.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.entities.create'));

        $response->assertOk();
        $response->assertViewIs('admin.entities.create');
        $response->assertViewHas(['entities', 'icons']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.entities.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $entity = Entity::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.entities.show', $entity->id));

        $response->assertOk();
        $response->assertViewIs('admin.entities.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $entity = Entity::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.entities.show', $entity->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $entity = Entity::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.entities.edit', $entity));

        $response->assertOk();
        $response->assertViewIs('admin.entities.edit');
        $response->assertViewHas('entity');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $entity = Entity::factory()->create();

        $response = $this->get(route('admin.entities.edit', $entity));

        $response->assertForbidden();
    });
});

describe('store', function () {
    test('can create an Entity with type and attributes', function () {
        $data = [
            'name' => 'Subcontractor Corp',
            'type' => 'Fournisseur',
            'attributes' => ['externe', 'audite'],
        ];

        $response = $this->post(route('admin.entities.store'), $data);

        $response->assertRedirect(route('admin.entities.index'));
        $this->assertDatabaseHas('entities', [
            'name' => 'Subcontractor Corp',
            'type' => 'Fournisseur',
            'attributes' => 'externe audite',
        ]);
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $entity = Entity::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.entities.update', $entity), $data);

        $response->assertRedirect(route('admin.entities.index'));
        $this->assertDatabaseHas('entities', ['name' => 'Updated Name']);
    });

    test('can update Entity type and attributes', function () {
        $entity = Entity::factory()->create(['type' => 'Old', 'attributes' => 'old tag']);

        $data = [
            'name' => $entity->name,
            'type' => 'Client',
            'attributes' => ['nouveau', 'tag'],
        ];

        $response = $this->put(route('admin.entities.update', $entity), $data);

        $response->assertRedirect(route('admin.entities.index'));
        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'type' => 'Client',
            'attributes' => 'nouveau tag',
        ]);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $entity = Entity::factory()->create();

        $response = $this->delete(route('admin.entities.destroy', $entity->id));
        $response->assertRedirect(route('admin.entities.index'));

        $this->assertSoftDeleted('entities', ['id' => $entity->id]);

        $entity->refresh();
        expect($entity->deleted_at)->not->toBeNull()
            ->and($entity->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $entity = Entity::factory()->create();

        $response = $this->delete(route('admin.entities.destroy', $entity));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple entities', function () {
        $entities = Entity::factory()->count(3)->create();
        $ids = $entities->pluck('id')->toArray();

        $response = $this->delete(route('admin.entities.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('entities', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $entity = Entity::factory()->create();

        $response = $this->delete(route('admin.entities.massDestroy'), [
            'ids' => [$entity->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $entity = Entity::factory()->create();

        $response = $this->delete(route('admin.entities.massDestroy'), [
            'ids' => [$entity->id],
        ]);

        $response->assertForbidden();
    });

});
