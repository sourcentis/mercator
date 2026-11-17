<?php

use App\Models\Bay;
use App\Models\Building;
use App\Models\Site;
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
    test('can display bays index page', function () {
        Bay::factory()->count(3)->create();

        $response = $this->get(route('admin.bays.index'));

        $response->assertOk();
        $response->assertViewIs('admin.bays.index');
        $response->assertViewHas('bays');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.bays.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.bays.create'));

        $response->assertOk();
        $response->assertViewIs('admin.bays.create');
        $response->assertViewHas(['sites', 'buildings']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.bays.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $bay = Bay::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.bays.show', $bay->id));

        $response->assertOk();
        $response->assertViewIs('admin.bays.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $bay = Bay::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.bays.show', $bay->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $bay = Bay::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.bays.edit', $bay));

        $response->assertOk();
        $response->assertViewIs('admin.bays.edit');
        $response->assertViewHas('bay');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $bay = Bay::factory()->create();

        $response = $this->get(route('admin.bays.edit', $bay));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $bay = Bay::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.bays.update', $bay), $data);

        $response->assertRedirect(route('admin.bays.index'));
        $this->assertDatabaseHas('bays', ['name' => 'Updated Name']);
    });

    test('can update building and site', function () {
        $bay = Bay::factory()->create();
        $building = Building::factory()->create();
        $site = Site::factory()->create();

        $data = [
            'name' => $bay->name,
            'description' => $bay->description,
            'building_id' => $building->id,
            'site_id' => $site->id,
        ];

        $response = $this->put(route('admin.bays.update', $bay), $data);

        $response->assertRedirect(route('admin.bays.index'));
        $this->assertDatabaseHas('bays', [
            'id' => $bay->id,
            'building_id' => $building->id,
            'site_id' => $site->id,
        ]);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $bay = Bay::factory()->create();

        $response = $this->delete(route('admin.bays.destroy', $bay->id));
        $response->assertRedirect(route('admin.bays.index'));

        $this->assertSoftDeleted('bays', ['id' => $bay->id]);

        $bay->refresh();
        expect($bay->deleted_at)->not->toBeNull()
            ->and($bay->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $bay = Bay::factory()->create();

        $response = $this->delete(route('admin.bays.destroy', $bay));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple bays', function () {
        $bays = Bay::factory()->count(3)->create();
        $ids = $bays->pluck('id')->toArray();

        $response = $this->delete(route('admin.bays.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('bays', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $bay = Bay::factory()->create();

        $response = $this->delete(route('admin.bays.massDestroy'), [
            'ids' => [$bay->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $bay = Bay::factory()->create();

        $response = $this->delete(route('admin.bays.massDestroy'), [
            'ids' => [$bay->id],
        ]);

        $response->assertForbidden();
    });

});
