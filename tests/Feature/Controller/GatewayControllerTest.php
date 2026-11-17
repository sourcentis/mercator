<?php

use App\Models\Gateway;
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
    test('can display gateways index page', function () {
        Gateway::factory()->count(3)->create();

        $response = $this->get(route('admin.gateways.index'));

        $response->assertOk();
        $response->assertViewIs('admin.gateways.index');
        $response->assertViewHas('gateways');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.gateways.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.gateways.create'));

        $response->assertOk();
        $response->assertViewIs('admin.gateways.create');
        $response->assertViewHas(['subnetworks']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.gateways.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $bay = Gateway::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.gateways.show', $bay->id));

        $response->assertOk();
        $response->assertViewIs('admin.gateways.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $bay = Gateway::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.gateways.show', $bay->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $bay = Gateway::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.gateways.edit', $bay));

        $response->assertOk();
        $response->assertViewIs('admin.gateways.edit');
        $response->assertViewHas('gateway');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $bay = Gateway::factory()->create();

        $response = $this->get(route('admin.gateways.edit', $bay));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $bay = Gateway::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.gateways.update', $bay), $data);

        $response->assertRedirect(route('admin.gateways.index'));
        $this->assertDatabaseHas('gateways', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $bay = Gateway::factory()->create();

        $response = $this->delete(route('admin.gateways.destroy', $bay->id));
        $response->assertRedirect(route('admin.gateways.index'));

        $this->assertSoftDeleted('gateways', ['id' => $bay->id]);

        $bay->refresh();
        expect($bay->deleted_at)->not->toBeNull()
            ->and($bay->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $bay = Gateway::factory()->create();

        $response = $this->delete(route('admin.gateways.destroy', $bay));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple gateways', function () {
        $gateways = Gateway::factory()->count(3)->create();
        $ids = $gateways->pluck('id')->toArray();

        $response = $this->delete(route('admin.gateways.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('gateways', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $bay = Gateway::factory()->create();

        $response = $this->delete(route('admin.gateways.massDestroy'), [
            'ids' => [$bay->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $bay = Gateway::factory()->create();

        $response = $this->delete(route('admin.gateways.massDestroy'), [
            'ids' => [$bay->id],
        ]);

        $response->assertForbidden();
    });

});
