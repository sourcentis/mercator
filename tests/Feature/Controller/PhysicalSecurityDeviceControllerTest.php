
<?php

use App\Models\PhysicalSecurityDevice;
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
    test('can display physical-security-devices index page', function () {
        PhysicalSecurityDevice::factory()->count(3)->create();

        $response = $this->get(route('admin.physical-security-devices.index'));

        $response->assertOk();
        $response->assertViewIs('admin.physicalSecurityDevices.index');
        $response->assertViewHas('physicalSecurityDevices');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.physical-security-devices.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.physical-security-devices.create'));

        $response->assertOk();
        $response->assertViewIs('admin.physicalSecurityDevices.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.physical-security-devices.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $physicalSecurityDevice = PhysicalSecurityDevice::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.physical-security-devices.show', $physicalSecurityDevice->id));

        $response->assertOk();
        $response->assertViewIs('admin.physicalSecurityDevices.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $physicalSecurityDevice = PhysicalSecurityDevice::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.physical-security-devices.show', $physicalSecurityDevice->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $physicalSecurityDevice = PhysicalSecurityDevice::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.physical-security-devices.edit', $physicalSecurityDevice));

        $response->assertOk();
        $response->assertViewIs('admin.physicalSecurityDevices.edit');
        $response->assertViewHas(['sites', 'buildings', 'bays']);
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $physicalSecurityDevice = PhysicalSecurityDevice::factory()->create();

        $response = $this->get(route('admin.physical-security-devices.edit', $physicalSecurityDevice));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update PhysicalSecurityDevice', function () {
        $name = fake()->word();
        $physicalSecurityDevice = PhysicalSecurityDevice::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.physical-security-devices.update', $physicalSecurityDevice), $data);

        $response->assertRedirect(route('admin.physical-security-devices.index'));
        $this->assertDatabaseHas('physical_security_devices', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete PhysicalSecurityDevice', function () {
        $physicalSecurityDevice = PhysicalSecurityDevice::factory()->create();

        $response = $this->delete(route('admin.physical-security-devices.destroy', $physicalSecurityDevice->id));
        $response->assertRedirect(route('admin.physical-security-devices.index'));

        $this->assertSoftDeleted('physical_security_devices', ['id' => $physicalSecurityDevice->id]);

        $physicalSecurityDevice->refresh();
        expect($physicalSecurityDevice->deleted_at)->not->toBeNull()
            ->and($physicalSecurityDevice->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $physicalSecurityDevice = PhysicalSecurityDevice::factory()->create();

        $response = $this->delete(route('admin.physical-security-devices.destroy', $physicalSecurityDevice));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple physical-security-devices', function () {
        $physicalSecurityDevices = PhysicalSecurityDevice::factory()->count(3)->create();
        $ids = $physicalSecurityDevices->pluck('id')->toArray();

        $response = $this->delete(route('admin.physical-security-devices.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('physical_security_devices', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $physicalSecurityDevice = PhysicalSecurityDevice::factory()->create();

        $response = $this->delete(route('admin.physical-security-devices.massDestroy'), [
            'ids' => [$physicalSecurityDevice->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $physicalSecurityDevice = PhysicalSecurityDevice::factory()->create();

        $response = $this->delete(route('admin.physical-security-devices.massDestroy'), [
            'ids' => [$physicalSecurityDevice->id],
        ]);

        $response->assertForbidden();
    });

});
