<?php

use App\Models\DhcpServer;
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

    // Create user
    $this->user = User::query()->where('login', 'admin@admin.com')->first();

    // Add missing roles (deprecatd)
    DB::table('permission_role')->insert([
        ['role_id' => $this->user->roles[0]->id, 'permission_id' => DB::table('permissions')->where('title', 'dhcp_server_create')->value('id')],
        ['role_id' => $this->user->roles[0]->id, 'permission_id' => DB::table('permissions')->where('title', 'dhcp_server_edit')->value('id')],
        ['role_id' => $this->user->roles[0]->id, 'permission_id' => DB::table('permissions')->where('title', 'dhcp_server_show')->value('id')],
        ['role_id' => $this->user->roles[0]->id, 'permission_id' => DB::table('permissions')->where('title', 'dhcp_server_delete')->value('id')],
        ['role_id' => $this->user->roles[0]->id, 'permission_id' => DB::table('permissions')->where('title', 'dhcp_server_access')->value('id')],
    ]);

    // Log the user
    $this->actingAs($this->user);

});

describe('index', function () {
    test('can display dhcpServers index page', function () {
        DhcpServer::factory()->count(3)->create();

        $response = $this->get(route('admin.dhcp-servers.index'));

        $response->assertOk();
        $response->assertViewIs('admin.dhcpServers.index');
        $response->assertViewHas('dhcpServers');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.dhcp-servers.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.dhcp-servers.create'));

        $response->assertOk();
        $response->assertViewIs('admin.dhcpServers.create');
        // $response->assertViewHas(['type_list']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.dhcp-servers.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $dhcpServer = DhcpServer::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.dhcp-servers.show', $dhcpServer->id));

        $response->assertOk();
        $response->assertViewIs('admin.dhcpServers.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $dhcpServer = DhcpServer::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.dhcp-servers.show', $dhcpServer->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $dhcpServer = DhcpServer::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.dhcp-servers.edit', $dhcpServer));

        $response->assertOk();
        $response->assertViewIs('admin.dhcpServers.edit');
        $response->assertViewHas('dhcpServer');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $dhcpServer = DhcpServer::factory()->create();

        $response = $this->get(route('admin.dhcp-servers.edit', $dhcpServer));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $dhcpServer = DhcpServer::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.dhcp-servers.update', $dhcpServer), $data);

        $response->assertRedirect(route('admin.dhcp-servers.index'));
        $this->assertDatabaseHas('dhcp_servers', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $dhcpServer = DhcpServer::factory()->create();

        $response = $this->delete(route('admin.dhcp-servers.destroy', $dhcpServer->id));
        $response->assertRedirect(route('admin.dhcp-servers.index'));

        $this->assertSoftDeleted('dhcp_servers', ['id' => $dhcpServer->id]);

        $dhcpServer->refresh();
        expect($dhcpServer->deleted_at)->not->toBeNull()
            ->and($dhcpServer->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $dhcpServer = DhcpServer::factory()->create();

        $response = $this->delete(route('admin.dhcp-servers.destroy', $dhcpServer));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple dhcpServers', function () {
        $dhcpServers = DhcpServer::factory()->count(3)->create();
        $ids = $dhcpServers->pluck('id')->toArray();

        $response = $this->delete(route('admin.dhcp-servers.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('dhcp_servers', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $dhcpServer = DhcpServer::factory()->create();

        $response = $this->delete(route('admin.dhcp-servers.massDestroy'), [
            'ids' => [$dhcpServer->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $dhcpServer = DhcpServer::factory()->create();

        $response = $this->delete(route('admin.dhcp-servers.massDestroy'), [
            'ids' => [$dhcpServer->id],
        ]);

        $response->assertForbidden();
    });

});
