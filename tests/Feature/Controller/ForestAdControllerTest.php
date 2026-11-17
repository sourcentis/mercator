<?php

use App\Models\ForestAd;
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
    test('can display forest-ads index page', function () {
        ForestAd::factory()->count(3)->create();

        $response = $this->get(route('admin.forest-ads.index'));

        $response->assertOk();
        $response->assertViewIs('admin.forestAds.index');
        $response->assertViewHas('forestAds');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.forest-ads.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.forest-ads.create'));

        $response->assertOk();
        $response->assertViewIs('admin.forestAds.create');
        $response->assertViewHas(['zone_admins', 'domains']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.forest-ads.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $forestAd = ForestAd::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.forest-ads.show', $forestAd->id));

        $response->assertOk();
        $response->assertViewIs('admin.forestAds.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $forestAd = ForestAd::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.forest-ads.show', $forestAd->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $forestAd = ForestAd::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.forest-ads.edit', $forestAd));

        $response->assertOk();
        $response->assertViewIs('admin.forestAds.edit');
        $response->assertViewHas(['forestAd', 'zone_admins', 'domains']);
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $forestAd = ForestAd::factory()->create();

        $response = $this->get(route('admin.forest-ads.edit', $forestAd));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $forestAd = ForestAd::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.forest-ads.update', $forestAd), $data);

        $response->assertRedirect(route('admin.forest-ads.index'));
        $this->assertDatabaseHas('forest_ads', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $forestAd = ForestAd::factory()->create();

        $response = $this->delete(route('admin.forest-ads.destroy', $forestAd->id));
        $response->assertRedirect(route('admin.forest-ads.index'));

        $this->assertSoftDeleted('forest_ads', ['id' => $forestAd->id]);

        $forestAd->refresh();
        expect($forestAd->deleted_at)->not->toBeNull()
            ->and($forestAd->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $forestAd = ForestAd::factory()->create();

        $response = $this->delete(route('admin.forest-ads.destroy', $forestAd));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple forest-ads', function () {
        $forestAds = ForestAd::factory()->count(3)->create();
        $ids = $forestAds->pluck('id')->toArray();

        $response = $this->delete(route('admin.forest-ads.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('forest_ads', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $forestAd = ForestAd::factory()->create();

        $response = $this->delete(route('admin.forest-ads.massDestroy'), [
            'ids' => [$forestAd->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $forestAd = ForestAd::factory()->create();

        $response = $this->delete(route('admin.forest-ads.massDestroy'), [
            'ids' => [$forestAd->id],
        ]);

        $response->assertForbidden();
    });

});
