<?php

use App\Models\Database;
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
    test('can display databases index page', function () {
        Database::factory()->count(3)->create();

        $response = $this->get(route('admin.databases.index'));

        $response->assertOk();
        $response->assertViewIs('admin.databases.index');
        $response->assertViewHas('databases');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.databases.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.databases.create'));

        $response->assertOk();
        $response->assertViewIs('admin.databases.create');
        $response->assertViewHas(['type_list']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.databases.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $database = Database::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.databases.show', $database->id));

        $response->assertOk();
        $response->assertViewIs('admin.databases.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $database = Database::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.databases.show', $database->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $database = Database::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.databases.edit', $database));

        $response->assertOk();
        $response->assertViewIs('admin.databases.edit');
        $response->assertViewHas('database');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $database = Database::factory()->create();

        $response = $this->get(route('admin.databases.edit', $database));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $database = Database::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.databases.update', $database), $data);

        $response->assertRedirect(route('admin.databases.index'));
        $this->assertDatabaseHas('databases', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $database = Database::factory()->create();

        $response = $this->delete(route('admin.databases.destroy', $database->id));
        $response->assertRedirect(route('admin.databases.index'));

        $this->assertSoftDeleted('databases', ['id' => $database->id]);

        $database->refresh();
        expect($database->deleted_at)->not->toBeNull()
            ->and($database->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $database = Database::factory()->create();

        $response = $this->delete(route('admin.databases.destroy', $database));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple databases', function () {
        $databases = Database::factory()->count(3)->create();
        $ids = $databases->pluck('id')->toArray();

        $response = $this->delete(route('admin.databases.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('databases', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $database = Database::factory()->create();

        $response = $this->delete(route('admin.databases.massDestroy'), [
            'ids' => [$database->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $database = Database::factory()->create();

        $response = $this->delete(route('admin.databases.massDestroy'), [
            'ids' => [$database->id],
        ]);

        $response->assertForbidden();
    });

});
