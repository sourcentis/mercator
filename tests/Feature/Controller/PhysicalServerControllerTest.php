
<?php

use App\Models\PhysicalServer;
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
    test('can display physical-servers index page', function () {
        PhysicalServer::factory()->count(3)->create();

        $response = $this->get(route('admin.physical-servers.index'));

        $response->assertOk();
        $response->assertViewIs('admin.physicalServers.index');
        $response->assertViewHas('physicalServers');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.physical-servers.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.physical-servers.create'));

        $response->assertOk();
        $response->assertViewIs('admin.physicalServers.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.physical-servers.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $physicalServer = PhysicalServer::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.physical-servers.show', $physicalServer->id));

        $response->assertOk();
        $response->assertViewIs('admin.physicalServers.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $physicalServer = PhysicalServer::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.physical-servers.show', $physicalServer->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $physicalServer = PhysicalServer::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.physical-servers.edit', $physicalServer));

        $response->assertOk();
        $response->assertViewIs('admin.physicalServers.edit');
        $response->assertViewHas(['sites', 'buildings', 'bays']);
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $physicalServer = PhysicalServer::factory()->create();

        $response = $this->get(route('admin.physical-servers.edit', $physicalServer));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update PhysicalServer', function () {
        $name = fake()->word();
        $physicalServer = PhysicalServer::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.physical-servers.update', $physicalServer), $data);

        $response->assertRedirect(route('admin.physical-servers.index'));
        $this->assertDatabaseHas('physical_servers', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete PhysicalServer', function () {
        $physicalServer = PhysicalServer::factory()->create();

        $response = $this->delete(route('admin.physical-servers.destroy', $physicalServer->id));
        $response->assertRedirect(route('admin.physical-servers.index'));

        $this->assertSoftDeleted('physical_servers', ['id' => $physicalServer->id]);

        $physicalServer->refresh();
        expect($physicalServer->deleted_at)->not->toBeNull()
            ->and($physicalServer->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $physicalServer = PhysicalServer::factory()->create();

        $response = $this->delete(route('admin.physical-servers.destroy', $physicalServer));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple physical-servers', function () {
        $physicalServers = PhysicalServer::factory()->count(3)->create();
        $ids = $physicalServers->pluck('id')->toArray();

        $response = $this->delete(route('admin.physical-servers.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('physical_servers', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $physicalServer = PhysicalServer::factory()->create();

        $response = $this->delete(route('admin.physical-servers.massDestroy'), [
            'ids' => [$physicalServer->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $physicalServer = PhysicalServer::factory()->create();

        $response = $this->delete(route('admin.physical-servers.massDestroy'), [
            'ids' => [$physicalServer->id],
        ]);

        $response->assertForbidden();
    });

});
