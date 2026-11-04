<?php

use App\Models\Backup;
use App\Models\LogicalServer;
use App\Models\StorageDevice;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::forget('permissions_roles_map');

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

// ─── index ────────────────────────────────────────────────────────────────────

describe('index', function () {
    test('can display backups index page', function () {
        Backup::factory()->count(3)->create();

        $response = $this->get(route('admin.backups.index'));

        $response->assertOk();
        $response->assertViewIs('admin.backups.index');
        $response->assertViewHas('backups');
    });

    test('denies access without permission', function () {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.backups.index'))->assertForbidden();
    });
});

// ─── create ───────────────────────────────────────────────────────────────────

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.backups.create'));

        $response->assertOk();
        $response->assertViewIs('admin.backups.create');
    });

    test('denies access without permission', function () {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.backups.create'))->assertForbidden();
    });
});

// ─── store ────────────────────────────────────────────────────────────────────

describe('store', function () {
    test('can create a backup', function () {
        $server = LogicalServer::factory()->create();
        $device = StorageDevice::factory()->create();

        $response = $this->post(route('admin.backups.store'), [
            'name' => 'Daily backup plan',
            'type' => 'Full',
            'backup_frequency' => 2,
            'backup_cycle' => 1,
            'backup_retention' => 30,
            'logical_server_ids' => [$server->id],
            'storage_device_ids' => [$device->id],
        ]);

        $response->assertRedirect(route('admin.backups.index'));
        $this->assertDatabaseHas('backups', ['name' => 'Daily backup plan']);

        $backup = Backup::where('name', 'Daily backup plan')->first();
        expect($backup->logicalServers->pluck('id')->contains($server->id))->toBeTrue();
        expect($backup->storageDevices->pluck('id')->contains($device->id))->toBeTrue();
    });

    test('rejects duplicate name', function () {
        Backup::factory()->create(['name' => 'Existing plan']);

        $this->post(route('admin.backups.store'), [
            'name' => 'Existing plan',
        ])->assertSessionHasErrors('name');
    });

    test('denies access without permission', function () {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.backups.store'), [
            'name' => 'test',
        ])->assertForbidden();
    });
});

// ─── show ─────────────────────────────────────────────────────────────────────

describe('show', function () {
    test('can display a backup', function () {
        $backup = Backup::factory()->create(['name' => 'My Backup Plan']);

        $response = $this->get(route('admin.backups.show', $backup->id));

        $response->assertOk();
        $response->assertViewIs('admin.backups.show');
        $response->assertSee('My Backup Plan');
    });

    test('denies access without permission', function () {
        $this->actingAs(User::factory()->create());

        $backup = Backup::factory()->create();

        $this->get(route('admin.backups.show', $backup->id))->assertForbidden();
    });
});

// ─── edit ─────────────────────────────────────────────────────────────────────

describe('edit', function () {
    test('can display edit form', function () {
        $backup = Backup::factory()->create(['name' => 'Edit Me']);

        $response = $this->get(route('admin.backups.edit', $backup));

        $response->assertOk();
        $response->assertViewIs('admin.backups.edit');
        $response->assertViewHas('backup');
        $response->assertSee('Edit Me');
    });

    test('denies access without permission', function () {
        $this->actingAs(User::factory()->create());

        $backup = Backup::factory()->create();

        $this->get(route('admin.backups.edit', $backup))->assertForbidden();
    });
});

// ─── update ───────────────────────────────────────────────────────────────────

describe('update', function () {
    test('can update a backup', function () {
        $backup = Backup::factory()->create(['name' => 'Old name']);
        $server = LogicalServer::factory()->create();

        $response = $this->put(route('admin.backups.update', $backup), [
            'name' => 'New name',
            'backup_frequency' => 1,
            'backup_cycle' => 2,
            'backup_retention' => 90,
            'logical_server_ids' => [$server->id],
        ]);

        $response->assertRedirect(route('admin.backups.index'));
        $this->assertDatabaseHas('backups', ['id' => $backup->id, 'name' => 'New name']);
        expect($backup->fresh()->logicalServers->pluck('id')->contains($server->id))->toBeTrue();
    });

    test('syncs storage devices on update', function () {
        $backup = Backup::factory()->create();
        $device1 = StorageDevice::factory()->create();
        $device2 = StorageDevice::factory()->create();

        $backup->storageDevices()->attach($device1->id);

        $this->put(route('admin.backups.update', $backup), [
            'name' => $backup->name,
            'storage_device_ids' => [$device2->id],
        ]);

        $backup->refresh()->load('storageDevices');
        expect($backup->storageDevices->pluck('id')->contains($device1->id))->toBeFalse();
        expect($backup->storageDevices->pluck('id')->contains($device2->id))->toBeTrue();
    });
});

// ─── destroy ──────────────────────────────────────────────────────────────────

describe('destroy', function () {
    test('can soft-delete a backup', function () {
        $backup = Backup::factory()->create();

        $this->delete(route('admin.backups.destroy', $backup->id))
            ->assertRedirect(route('admin.backups.index'));

        $this->assertSoftDeleted('backups', ['id' => $backup->id]);
    });

    test('denies access without permission', function () {
        $this->actingAs(User::factory()->create());

        $backup = Backup::factory()->create();

        $this->delete(route('admin.backups.destroy', $backup))->assertForbidden();
    });
});

// ─── massDestroy ──────────────────────────────────────────────────────────────

describe('massDestroy', function () {
    test('can delete multiple backups', function () {
        $backups = Backup::factory()->count(3)->create();
        $ids = $backups->pluck('id')->toArray();

        $this->delete(route('admin.backups.massDestroy'), ['ids' => $ids])
            ->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('backups', ['id' => $id]);
        }
    });

    test('denies access without permission', function () {
        $this->actingAs(User::factory()->create());

        $backup = Backup::factory()->create();

        $this->delete(route('admin.backups.massDestroy'), ['ids' => [$backup->id]])
            ->assertForbidden();
    });
});
