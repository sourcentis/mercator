<?php

use App\Models\Building;
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
    test('can display buildings index page', function () {
        Building::factory()->count(3)->create();

        $response = $this->get(route('admin.buildings.index'));

        $response->assertOk();
        $response->assertViewIs('admin.buildings.index');
        $response->assertViewHas('buildings');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.buildings.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.buildings.create'));

        $response->assertOk();
        $response->assertViewIs('admin.buildings.create');
        $response->assertViewHas(['sites', 'buildings', 'icons']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.buildings.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $building = Building::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.buildings.show', $building->id));

        $response->assertOk();
        $response->assertViewIs('admin.buildings.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $building = Building::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.buildings.show', $building->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $building = Building::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.buildings.edit', $building));

        $response->assertOk();
        $response->assertViewIs('admin.buildings.edit');
        $response->assertViewHas('building');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $building = Building::factory()->create();

        $response = $this->get(route('admin.buildings.edit', $building));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $building = Building::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.buildings.update', $building), $data);

        $response->assertRedirect(route('admin.buildings.index'));
        $this->assertDatabaseHas('buildings', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $building = Building::factory()->create();

        $response = $this->delete(route('admin.buildings.destroy', $building->id));
        $response->assertRedirect(route('admin.buildings.index'));

        $this->assertSoftDeleted('buildings', ['id' => $building->id]);

        $building->refresh();
        expect($building->deleted_at)->not->toBeNull()
            ->and($building->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $building = Building::factory()->create();

        $response = $this->delete(route('admin.buildings.destroy', $building));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple buildings', function () {
        $buildings = Building::factory()->count(3)->create();
        $ids = $buildings->pluck('id')->toArray();

        $response = $this->delete(route('admin.buildings.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('buildings', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $building = Building::factory()->create();

        $response = $this->delete(route('admin.buildings.massDestroy'), [
            'ids' => [$building->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $building = Building::factory()->create();

        $response = $this->delete(route('admin.buildings.massDestroy'), [
            'ids' => [$building->id],
        ]);

        $response->assertForbidden();
    });

});
