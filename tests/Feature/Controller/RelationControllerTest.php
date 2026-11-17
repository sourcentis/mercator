
<?php

use App\Models\Entity;
use App\Models\Relation;
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
    test('can display relations index page', function () {
        Relation::factory()->count(3)->create();

        $response = $this->get(route('admin.relations.index'));

        $response->assertOk();
        $response->assertViewIs('admin.relations.index');
        $response->assertViewHas('relations');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.relations.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.relations.create'));

        $response->assertOk();
        $response->assertViewIs('admin.relations.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.relations.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $relation = Relation::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.relations.show', $relation->id));

        $response->assertOk();
        $response->assertViewIs('admin.relations.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $relation = Relation::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.relations.show', $relation->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $relation = Relation::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.relations.edit', $relation));

        $response->assertOk();
        $response->assertViewIs('admin.relations.edit');
        $response->assertViewHas('relation');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $relation = Relation::factory()->create();

        $response = $this->get(route('admin.relations.edit', $relation));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Relation', function () {
        $name = fake()->word();

        $source = Entity::factory()->create();
        $destination = Entity::factory()->create();

        $relation = Relation::factory()->create(
            [
                'name' => $name,
                'description' => fake()->sentence(),
            ]
        );

        $data = [
            'name' => 'Updated Name',
            'source_id' => $source->id,
            'destination_id' => $destination->id,
        ];

        $response = $this->put(route('admin.relations.update', $relation), $data);

        $response->assertRedirect(route('admin.relations.index'));
        $this->assertDatabaseHas('relations', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete Relation', function () {
        $relation = Relation::factory()->create();

        $response = $this->delete(route('admin.relations.destroy', $relation->id));
        $response->assertRedirect(route('admin.relations.index'));

        $this->assertSoftDeleted('relations', ['id' => $relation->id]);

        $relation->refresh();
        expect($relation->deleted_at)->not->toBeNull()
            ->and($relation->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $relation = Relation::factory()->create();

        $response = $this->delete(route('admin.relations.destroy', $relation));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple relations', function () {
        $relations = Relation::factory()->count(3)->create();
        $ids = $relations->pluck('id')->toArray();

        $response = $this->delete(route('admin.relations.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('relations', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $relation = Relation::factory()->create();

        $response = $this->delete(route('admin.relations.massDestroy'), [
            'ids' => [$relation->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $relation = Relation::factory()->create();

        $response = $this->delete(route('admin.relations.massDestroy'), [
            'ids' => [$relation->id],
        ]);

        $response->assertForbidden();
    });

});
