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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

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
    Passport::actingAs($this->user);
});

// ============================================================
// created_at doit être rempli à la création (API)
// ============================================================

it('fills created_at when a backup is created via API', function () {
    $response = $this->postJson('/api/backups', [
        'name' => 'Daily backup',
        'backup_frequency' => 1,
        'backup_cycle' => 1,
        'backup_retention' => 30,
    ])->assertCreated();

    $id = $response->json('id');
    $backup = Backup::find($id);

    expect($backup)->not->toBeNull();
    expect($backup->created_at)->not->toBeNull();
});

it('fills created_at when a backup is created directly', function () {
    $backup = Backup::factory()->create();

    expect($backup->created_at)->not->toBeNull();
});

// ============================================================
// Relations n-m : logicalServers et storageDevices
// ============================================================

it('links a backup to a logical server and a storage device via API', function () {
    $server = LogicalServer::factory()->create(['name' => 'WebServer']);
    $device = StorageDevice::factory()->create(['name' => 'NAS-01']);

    $response = $this->postJson('/api/backups', [
        'name' => 'WebServer NAS backup',
        'backup_frequency' => 1,
        'backup_cycle' => 1,
        'backup_retention' => 30,
        'logical_server_ids' => [$server->id],
        'storage_device_ids' => [$device->id],
    ])->assertCreated();

    $backup = Backup::findOrFail($response->json('id'));

    expect($backup->logicalServers->pluck('id')->contains($server->id))->toBeTrue();
    expect($backup->storageDevices->pluck('id')->contains($device->id))->toBeTrue();
});

// ============================================================
// Unicité du champ name (éléments non supprimés seulement)
// ============================================================

it('rejects a store with a name already used by an active backup', function () {
    Backup::factory()->create(['name' => 'BACKUP-HEBDO']);

    $this->postJson('/api/backups', [
        'name' => 'BACKUP-HEBDO',
        'backup_frequency' => 1,
        'backup_cycle' => 1,
        'backup_retention' => 30,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

it('allows creating a backup with the name of a soft-deleted backup', function () {
    $deleted = Backup::factory()->create(['name' => 'BACKUP-HEBDO']);
    $deleted->delete();

    $this->postJson('/api/backups', [
        'name' => 'BACKUP-HEBDO',
        'backup_frequency' => 2,
        'backup_cycle' => 2,
        'backup_retention' => 35,
    ])->assertCreated();

    expect(Backup::where('name', 'BACKUP-HEBDO')->count())->toBe(1);
});

it('rejects an update with a name already used by another active backup', function () {
    Backup::factory()->create(['name' => 'BACKUP-A']);
    $b = Backup::factory()->create(['name' => 'BACKUP-B']);

    $this->putJson("/api/backups/{$b->id}", [
        'name' => 'BACKUP-A',
        'backup_frequency' => 1,
        'backup_cycle' => 1,
        'backup_retention' => 30,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

it('allows updating a backup with the name of a soft-deleted backup', function () {
    $deleted = Backup::factory()->create(['name' => 'BACKUP-OLD']);
    $deleted->delete();

    $b = Backup::factory()->create(['name' => 'BACKUP-B']);

    $this->putJson("/api/backups/{$b->id}", [
        'name' => 'BACKUP-OLD',
        'backup_frequency' => 1,
        'backup_cycle' => 1,
        'backup_retention' => 30,
    ])->assertOk();

    expect($b->fresh()->name)->toBe('BACKUP-OLD');
});

// ============================================================
// L'update via l'interface admin doit synchroniser les backups
// ============================================================

it('syncs backup links on admin update', function () {
    $this->actingAs($this->user);

    $logicalServer = LogicalServer::factory()->create(['name' => 'TestServer']);
    $oldBackup = Backup::factory()->create(['name' => 'Old Backup']);
    $newBackup = Backup::factory()->create(['name' => 'New Backup']);

    // Lier l'ancien backup au serveur
    $logicalServer->backups()->attach($oldBackup->id);

    // Mise à jour du serveur logique : on remplace par le nouveau backup
    $this->put(route('admin.logical-servers.update', $logicalServer), array_merge(
        $logicalServer->only(['name']),
        ['backup_ids' => [$newBackup->id]]
    ))->assertRedirect();

    $fresh = $logicalServer->fresh();

    // L'ancien backup est détaché
    expect($fresh->backups->pluck('id')->contains($oldBackup->id))->toBeFalse();
    // Le nouveau backup est attaché
    expect($fresh->backups->pluck('id')->contains($newBackup->id))->toBeTrue();
    // Un seul backup lié
    expect($fresh->backups->count())->toBe(1);
    // Les deux backups existent toujours en base (sync ne supprime pas)
    $this->assertDatabaseHas('backups', ['id' => $oldBackup->id]);
    $this->assertDatabaseHas('backups', ['id' => $newBackup->id]);
});
