
<?php

use App\Models\Network;
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
    test('can display networks index page', function () {
        Network::factory()->count(3)->create();

        $response = $this->get(route('admin.networks.index'));

        $response->assertOk();
        $response->assertViewIs('admin.networks.index');
        $response->assertViewHas('networks');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.networks.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.networks.create'));

        $response->assertOk();
        $response->assertViewIs('admin.networks.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.networks.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $network = Network::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.networks.show', $network->id));

        $response->assertOk();
        $response->assertViewIs('admin.networks.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $network = Network::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.networks.show', $network->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $network = Network::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.networks.edit', $network));

        $response->assertOk();
        $response->assertViewIs('admin.networks.edit');
        $response->assertViewHas('subnetworks');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $network = Network::factory()->create();

        $response = $this->get(route('admin.networks.edit', $network));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Network', function () {
        $name = fake()->word();
        $network = Network::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.networks.update', $network), $data);

        $response->assertRedirect(route('admin.networks.index'));
        $this->assertDatabaseHas('networks', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete Network', function () {
        $network = Network::factory()->create();

        $response = $this->delete(route('admin.networks.destroy', $network->id));
        $response->assertRedirect(route('admin.networks.index'));

        $this->assertSoftDeleted('networks', ['id' => $network->id]);

        $network->refresh();
        expect($network->deleted_at)->not->toBeNull()
            ->and($network->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $network = Network::factory()->create();

        $response = $this->delete(route('admin.networks.destroy', $network));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple networks', function () {
        $networks = Network::factory()->count(3)->create();
        $ids = $networks->pluck('id')->toArray();

        $response = $this->delete(route('admin.networks.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('networks', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $network = Network::factory()->create();

        $response = $this->delete(route('admin.networks.massDestroy'), [
            'ids' => [$network->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $network = Network::factory()->create();

        $response = $this->delete(route('admin.networks.massDestroy'), [
            'ids' => [$network->id],
        ]);

        $response->assertForbidden();
    });

});
