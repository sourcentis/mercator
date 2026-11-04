
<?php

use App\Models\Lan;
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
    test('can display lans index page', function () {
        Lan::factory()->count(3)->create();

        $response = $this->get(route('admin.lans.index'));

        $response->assertOk();
        $response->assertViewIs('admin.lans.index');
        $response->assertViewHas('lans');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.lans.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.lans.create'));

        $response->assertOk();
        $response->assertViewIs('admin.lans.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.lans.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $lan = Lan::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.lans.show', $lan->id));

        $response->assertOk();
        $response->assertViewIs('admin.lans.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $lan = Lan::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.lans.show', $lan->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $lan = Lan::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.lans.edit', $lan));

        $response->assertOk();
        $response->assertViewIs('admin.lans.edit');
        $response->assertViewHas('lan');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $lan = Lan::factory()->create();

        $response = $this->get(route('admin.lans.edit', $lan));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Lan', function () {
        $name = fake()->word();
        $lan = Lan::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.lans.update', $lan), $data);

        $response->assertRedirect(route('admin.lans.index'));
        $this->assertDatabaseHas('lans', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete Lan', function () {
        $lan = Lan::factory()->create();

        $response = $this->delete(route('admin.lans.destroy', $lan->id));
        $response->assertRedirect(route('admin.lans.index'));

        $this->assertSoftDeleted('lans', ['id' => $lan->id]);

        $lan->refresh();
        expect($lan->deleted_at)->not->toBeNull()
            ->and($lan->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $lan = Lan::factory()->create();

        $response = $this->delete(route('admin.lans.destroy', $lan));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple lans', function () {
        $lans = Lan::factory()->count(3)->create();
        $ids = $lans->pluck('id')->toArray();

        $response = $this->delete(route('admin.lans.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('lans', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $lan = Lan::factory()->create();

        $response = $this->delete(route('admin.lans.massDestroy'), [
            'ids' => [$lan->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $lan = Lan::factory()->create();

        $response = $this->delete(route('admin.lans.massDestroy'), [
            'ids' => [$lan->id],
        ]);

        $response->assertForbidden();
    });

});
