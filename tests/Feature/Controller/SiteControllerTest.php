
<?php

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
    test('can display sites index page', function () {
        Site::factory()->count(3)->create();

        $response = $this->get(route('admin.sites.index'));

        $response->assertOk();
        $response->assertViewIs('admin.sites.index');
        $response->assertViewHas('sites');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.sites.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.sites.create'));

        $response->assertOk();
        $response->assertViewIs('admin.sites.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.sites.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $Site = Site::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.sites.show', $Site->id));

        $response->assertOk();
        $response->assertViewIs('admin.sites.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $Site = Site::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.sites.show', $Site->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $Site = Site::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.sites.edit', $Site));

        $response->assertOk();
        $response->assertViewIs('admin.sites.edit');
        $response->assertViewHas('site');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Site = Site::factory()->create();

        $response = $this->get(route('admin.sites.edit', $Site));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Site', function () {
        $name = fake()->word();
        $Site = Site::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.sites.update', $Site), $data);

        $response->assertRedirect(route('admin.sites.index'));
        $this->assertDatabaseHas('sites', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete Site', function () {
        $Site = Site::factory()->create();

        $response = $this->delete(route('admin.sites.destroy', $Site->id));
        $response->assertRedirect(route('admin.sites.index'));

        $this->assertSoftDeleted('sites', ['id' => $Site->id]);

        $Site->refresh();
        expect($Site->deleted_at)->not->toBeNull()
            ->and($Site->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Site = Site::factory()->create();

        $response = $this->delete(route('admin.sites.destroy', $Site));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple sites', function () {
        $sites = Site::factory()->count(3)->create();
        $ids = $sites->pluck('id')->toArray();

        $response = $this->delete(route('admin.sites.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('sites', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $Site = Site::factory()->create();

        $response = $this->delete(route('admin.sites.massDestroy'), [
            'ids' => [$Site->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Site = Site::factory()->create();

        $response = $this->delete(route('admin.sites.massDestroy'), [
            'ids' => [$Site->id],
        ]);

        $response->assertForbidden();
    });

});
