
<?php

use App\Models\SecurityDevice;
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
    test('can display activities index page', function () {
        SecurityDevice::factory()->count(3)->create();

        $response = $this->get(route('admin.security-devices.index'));

        $response->assertOk();
        $response->assertViewIs('admin.securityDevices.index');
        $response->assertViewHas('securityDevices');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.security-devices.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.security-devices.create'));

        $response->assertOk();
        $response->assertViewIs('admin.securityDevices.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.security-devices.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $securityDevice = SecurityDevice::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.security-devices.show', $securityDevice->id));

        $response->assertOk();
        $response->assertViewIs('admin.securityDevices.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $securityDevice = SecurityDevice::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.security-devices.show', $securityDevice->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $securityDevice = SecurityDevice::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.security-devices.edit', $securityDevice));

        $response->assertOk();
        $response->assertViewIs('admin.securityDevices.edit');
        $response->assertViewHas('securityDevice');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $securityDevice = SecurityDevice::factory()->create();

        $response = $this->get(route('admin.security-devices.edit', $securityDevice));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update SecurityDevice', function () {
        $name = fake()->word();
        $securityDevice = SecurityDevice::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentences(3, true),
        ];

        $response = $this->put(route('admin.security-devices.update', $securityDevice), $data);

        $response->assertRedirect(route('admin.security-devices.index'));
        $this->assertDatabaseHas('security_devices', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete SecurityDevice', function () {
        $securityDevice = SecurityDevice::factory()->create();

        $response = $this->delete(route('admin.security-devices.destroy', $securityDevice->id));
        $response->assertRedirect(route('admin.security-devices.index'));

        $this->assertSoftDeleted('security_devices', ['id' => $securityDevice->id]);

        $securityDevice->refresh();
        expect($securityDevice->deleted_at)->not->toBeNull()
            ->and($securityDevice->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $securityDevice = SecurityDevice::factory()->create();

        $response = $this->delete(route('admin.security-devices.destroy', $securityDevice));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple activities', function () {
        $securityDevice = SecurityDevice::factory()->count(3)->create();
        $ids = $securityDevice->pluck('id')->toArray();

        $response = $this->delete(route('admin.security-devices.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('security_devices', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $securityDevice = SecurityDevice::factory()->create();

        $response = $this->delete(route('admin.security-devices.massDestroy'), [
            'ids' => [$securityDevice->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $securityDevice = SecurityDevice::factory()->create();

        $response = $this->delete(route('admin.security-devices.massDestroy'), [
            'ids' => [$securityDevice->id],
        ]);

        $response->assertForbidden();
    });

});
