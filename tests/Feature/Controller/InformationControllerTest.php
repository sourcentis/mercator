
<?php

use App\Models\Information;
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
    test('can display information index page', function () {
        Information::factory()->count(3)->create();

        $response = $this->get(route('admin.information.index'));

        $response->assertOk();
        $response->assertViewIs('admin.information.index');
        $response->assertViewHas('information');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.information.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.information.create'));

        $response->assertOk();
        $response->assertViewIs('admin.information.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.information.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $information = Information::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.information.show', $information->id));

        $response->assertOk();
        $response->assertViewIs('admin.information.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $information = Information::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.information.show', $information->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $information = Information::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.information.edit', $information));

        $response->assertOk();
        $response->assertViewIs('admin.information.edit');
        $response->assertViewHas('information');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $information = Information::factory()->create();

        $response = $this->get(route('admin.information.edit', $information));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Information', function () {
        $name = fake()->word();
        $information = Information::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(5),
        ];

        $response = $this->put(route('admin.information.update', $information), $data);

        $response->assertRedirect(route('admin.information.index'));
        $this->assertDatabaseHas('information', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete Information', function () {
        $information = Information::factory()->create();

        $response = $this->delete(route('admin.information.destroy', $information->id));
        $response->assertRedirect(route('admin.information.index'));

        $this->assertSoftDeleted('information', ['id' => $information->id]);

        $information->refresh();
        expect($information->deleted_at)->not->toBeNull()
            ->and($information->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $information = Information::factory()->create();

        $response = $this->delete(route('admin.information.destroy', $information));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple information', function () {
        $information = Information::factory()->count(3)->create();
        $ids = $information->pluck('id')->toArray();

        $response = $this->delete(route('admin.information.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('information', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $information = Information::factory()->create();

        $response = $this->delete(route('admin.information.massDestroy'), [
            'ids' => [$information->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $information = Information::factory()->create();

        $response = $this->delete(route('admin.information.massDestroy'), [
            'ids' => [$information->id],
        ]);

        $response->assertForbidden();
    });

});
