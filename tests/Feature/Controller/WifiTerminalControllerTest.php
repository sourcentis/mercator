
<?php

use App\Models\User;
use App\Models\WifiTerminal;
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
    test('can display wifi-terminals index page', function () {
        WifiTerminal::factory()->count(3)->create();

        $response = $this->get(route('admin.wifi-terminals.index'));

        $response->assertOk();
        $response->assertViewIs('admin.wifiTerminals.index');
        $response->assertViewHas('wifiTerminals');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.wifi-terminals.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.wifi-terminals.create'));

        $response->assertOk();
        $response->assertViewIs('admin.wifiTerminals.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.wifi-terminals.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $wifiTerminal = WifiTerminal::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.wifi-terminals.show', $wifiTerminal->id));

        $response->assertOk();
        $response->assertViewIs('admin.wifiTerminals.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $wifiTerminal = WifiTerminal::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.wifi-terminals.show', $wifiTerminal->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $wifiTerminal = WifiTerminal::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.wifi-terminals.edit', $wifiTerminal));

        $response->assertOk();
        $response->assertViewIs('admin.wifiTerminals.edit');
        $response->assertViewHas('wifiTerminal');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $wifiTerminal = WifiTerminal::factory()->create();

        $response = $this->get(route('admin.wifi-terminals.edit', $wifiTerminal));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update WifiTerminal', function () {
        $name = fake()->word();
        $wifiTerminal = WifiTerminal::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.wifi-terminals.update', $wifiTerminal), $data);

        $response->assertRedirect(route('admin.wifi-terminals.index'));
        $this->assertDatabaseHas('wifi_terminals', ['name' => 'Updated Name']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $wifiTerminal = WifiTerminal::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.wifi-terminals.update', $wifiTerminal), $data);

        $response->assertForbidden();
    });
});

describe('destroy', function () {
    test('can delete WifiTerminal', function () {
        $wifiTerminal = WifiTerminal::factory()->create();

        $response = $this->delete(route('admin.wifi-terminals.destroy', $wifiTerminal->id));
        $response->assertRedirect(route('admin.wifi-terminals.index'));

        $this->assertSoftDeleted('wifi_terminals', ['id' => $wifiTerminal->id]);

        $wifiTerminal->refresh();
        expect($wifiTerminal->deleted_at)->not->toBeNull()
            ->and($wifiTerminal->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $wifiTerminal = WifiTerminal::factory()->create();

        $response = $this->delete(route('admin.wifi-terminals.destroy', $wifiTerminal));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple wifi-terminals', function () {
        $wifiTerminals = WifiTerminal::factory()->count(3)->create();
        $ids = $wifiTerminals->pluck('id')->toArray();

        $response = $this->delete(route('admin.wifi-terminals.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('wifi_terminals', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $wifiTerminal = WifiTerminal::factory()->create();

        $response = $this->delete(route('admin.wifi-terminals.massDestroy'), [
            'ids' => [$wifiTerminal->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $wifiTerminal = WifiTerminal::factory()->create();

        $response = $this->delete(route('admin.wifi-terminals.massDestroy'), [
            'ids' => [$wifiTerminal->id],
        ]);

        $response->assertForbidden();
    });

});
