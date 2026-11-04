
<?php

use App\Models\Actor;
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
    test('can display actors index page', function () {
        Actor::factory()->count(3)->create();

        $response = $this->get(route('admin.actors.index'));

        $response->assertOk();
        $response->assertViewIs('admin.actors.index');
        $response->assertViewHas('actors');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.actors.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.actors.create'));

        $response->assertOk();
        $response->assertViewIs('admin.actors.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.actors.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $Actor = Actor::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.actors.show', $Actor->id));

        $response->assertOk();
        $response->assertViewIs('admin.actors.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $Actor = Actor::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.actors.show', $Actor->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $Actor = Actor::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.actors.edit', $Actor));

        $response->assertOk();
        $response->assertViewIs('admin.actors.edit');
        $response->assertViewHas('actor');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Actor = Actor::factory()->create();

        $response = $this->get(route('admin.actors.edit', $Actor));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Actor', function () {
        $name = fake()->word();
        $Actor = Actor::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.actors.update', $Actor), $data);

        $response->assertRedirect(route('admin.actors.index'));
        $this->assertDatabaseHas('actors', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete Actor', function () {
        $Actor = Actor::factory()->create();

        $response = $this->delete(route('admin.actors.destroy', $Actor->id));
        $response->assertRedirect(route('admin.actors.index'));

        $this->assertSoftDeleted('actors', ['id' => $Actor->id]);

        $Actor->refresh();
        expect($Actor->deleted_at)->not->toBeNull()
            ->and($Actor->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Actor = Actor::factory()->create();

        $response = $this->delete(route('admin.actors.destroy', $Actor));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple actors', function () {
        $actors = Actor::factory()->count(3)->create();
        $ids = $actors->pluck('id')->toArray();

        $response = $this->delete(route('admin.actors.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('actors', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $Actor = Actor::factory()->create();

        $response = $this->delete(route('admin.actors.massDestroy'), [
            'ids' => [$Actor->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Actor = Actor::factory()->create();

        $response = $this->delete(route('admin.actors.massDestroy'), [
            'ids' => [$Actor->id],
        ]);

        $response->assertForbidden();
    });

});
