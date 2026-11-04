
<?php

use App\Models\StorageDevice;
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
        StorageDevice::factory()->count(3)->create();

        $response = $this->get(route('admin.storage-devices.index'));

        $response->assertOk();
        $response->assertViewIs('admin.storageDevices.index');
        $response->assertViewHas('storageDevices');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.storage-devices.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.storage-devices.create'));

        $response->assertOk();
        $response->assertViewIs('admin.storageDevices.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.storage-devices.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $storageDevice = StorageDevice::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.storage-devices.show', $storageDevice->id));

        $response->assertOk();
        $response->assertViewIs('admin.storageDevices.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $storageDevice = StorageDevice::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.storage-devices.show', $storageDevice->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $storageDevice = StorageDevice::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.storage-devices.edit', $storageDevice));

        $response->assertOk();
        $response->assertViewIs('admin.storageDevices.edit');
        $response->assertViewHas('storageDevice');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $storageDevice = StorageDevice::factory()->create();

        $response = $this->get(route('admin.storage-devices.edit', $storageDevice));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update StorageDevice', function () {
        $name = fake()->word();
        $storageDevice = StorageDevice::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentences(3, true),
        ];

        $response = $this->put(route('admin.storage-devices.update', $storageDevice), $data);

        $response->assertRedirect(route('admin.storage-devices.index'));
        $this->assertDatabaseHas('storage_devices', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete StorageDevice', function () {
        $storageDevice = StorageDevice::factory()->create();

        $response = $this->delete(route('admin.storage-devices.destroy', $storageDevice->id));
        $response->assertRedirect(route('admin.storage-devices.index'));

        $this->assertSoftDeleted('storage_devices', ['id' => $storageDevice->id]);

        $storageDevice->refresh();
        expect($storageDevice->deleted_at)->not->toBeNull()
            ->and($storageDevice->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $storageDevice = StorageDevice::factory()->create();

        $response = $this->delete(route('admin.storage-devices.destroy', $storageDevice));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple activities', function () {
        $storageDevice = StorageDevice::factory()->count(3)->create();
        $ids = $storageDevice->pluck('id')->toArray();

        $response = $this->delete(route('admin.storage-devices.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('storage_devices', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $storageDevice = StorageDevice::factory()->create();

        $response = $this->delete(route('admin.storage-devices.massDestroy'), [
            'ids' => [$storageDevice->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $storageDevice = StorageDevice::factory()->create();

        $response = $this->delete(route('admin.storage-devices.massDestroy'), [
            'ids' => [$storageDevice->id],
        ]);

        $response->assertForbidden();
    });

});
