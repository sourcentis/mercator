
<?php

use App\Models\LogicalServer;
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
    test('can display logical servers index page', function () {
        LogicalServer::factory()->count(3)->create();

        $response = $this->get(route('admin.logical-servers.index'));

        $response->assertOk();
        $response->assertViewIs('admin.logicalServers.index');
        $response->assertViewHas('logicalServers');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.logical-servers.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.logical-servers.create'));

        $response->assertOk();
        $response->assertViewIs('admin.logicalServers.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.logical-servers.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $logicalServer = LogicalServer::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.logical-servers.show', $logicalServer->id));

        $response->assertOk();
        $response->assertViewIs('admin.logicalServers.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $logicalServer = LogicalServer::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.logical-servers.show', $logicalServer->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $logicalServer = LogicalServer::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.logical-servers.edit', $logicalServer));

        $response->assertOk();
        $response->assertViewIs('admin.logicalServers.edit');
        $response->assertViewHas('logicalServer');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $logicalServer = LogicalServer::factory()->create();

        $response = $this->get(route('admin.logical-servers.edit', $logicalServer));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update LogicalServer', function () {
        $name = fake()->word();
        $logicalServer = LogicalServer::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentences(3, true),
            'source_ip_range' => fake()->ipv4().'/32',
            'dest_ip_range' => fake()->ipv4().'/32',
        ];

        $response = $this->put(route('admin.logical-servers.update', $logicalServer), $data);

        $response->assertRedirect(route('admin.logical-servers.index'));
        $this->assertDatabaseHas('logical_servers', ['name' => 'Updated Name']);
    });

    test('keeps the server active when the active checkbox is submitted', function () {
        $logicalServer = LogicalServer::factory()->create(['active' => true]);

        $data = [
            'name' => $logicalServer->name,
            'description' => $logicalServer->description,
            'active' => '1',
        ];

        $this->put(route('admin.logical-servers.update', $logicalServer), $data);

        $this->assertDatabaseHas('logical_servers', ['id' => $logicalServer->id, 'active' => true]);
    });

    test('edit form contains an active checkbox reflecting the current state', function () {
        $logicalServer = LogicalServer::factory()->create(['active' => true]);

        $response = $this->get(route('admin.logical-servers.edit', $logicalServer));

        $response->assertOk();
        $response->assertSee('name="active"', false);
    });
});

describe('destroy', function () {
    test('can delete LogicalServer', function () {
        $logicalServer = LogicalServer::factory()->create();

        $response = $this->delete(route('admin.logical-servers.destroy', $logicalServer->id));
        $response->assertRedirect(route('admin.logical-servers.index'));

        $this->assertSoftDeleted('logical_servers', ['id' => $logicalServer->id]);

        $logicalServer->refresh();
        expect($logicalServer->deleted_at)->not->toBeNull()
            ->and($logicalServer->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $logicalServer = LogicalServer::factory()->create();

        $response = $this->delete(route('admin.logical-servers.destroy', $logicalServer));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple logical servers', function () {
        $logicalServers = LogicalServer::factory()->count(3)->create();
        $ids = $logicalServers->pluck('id')->toArray();

        $response = $this->delete(route('admin.logical-servers.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('logical_servers', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $logicalServer = LogicalServer::factory()->create();

        $response = $this->delete(route('admin.logical-servers.massDestroy'), [
            'ids' => [$logicalServer->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $logicalServer = LogicalServer::factory()->create();

        $response = $this->delete(route('admin.logical-servers.massDestroy'), [
            'ids' => [$logicalServer->id],
        ]);

        $response->assertForbidden();
    });

});
