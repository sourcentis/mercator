
<?php

use App\Models\User;
use App\Models\ZoneAdmin;
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
    test('can display zone-admins index page', function () {
        ZoneAdmin::factory()->count(3)->create();

        $response = $this->get(route('admin.zone-admins.index'));

        $response->assertOk();
        $response->assertViewIs('admin.zoneAdmins.index');
        $response->assertViewHas('zoneAdmins');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.zone-admins.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.zone-admins.create'));

        $response->assertOk();
        $response->assertViewIs('admin.zoneAdmins.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.zone-admins.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $zoneAdmin = ZoneAdmin::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.zone-admins.show', $zoneAdmin->id));

        $response->assertOk();
        $response->assertViewIs('admin.zoneAdmins.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $zoneAdmin = ZoneAdmin::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.zone-admins.show', $zoneAdmin->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $zoneAdmin = ZoneAdmin::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.zone-admins.edit', $zoneAdmin));

        $response->assertOk();
        $response->assertViewIs('admin.zoneAdmins.edit');
        $response->assertViewHas('zoneAdmin');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $zoneAdmin = ZoneAdmin::factory()->create();

        $response = $this->get(route('admin.zone-admins.edit', $zoneAdmin));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update ZoneAdmin', function () {
        $name = fake()->word();
        $zoneAdmin = ZoneAdmin::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.zone-admins.update', $zoneAdmin), $data);

        $response->assertRedirect(route('admin.zone-admins.index'));
        $this->assertDatabaseHas('zone_admins', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete ZoneAdmin', function () {
        $zoneAdmin = ZoneAdmin::factory()->create();

        $response = $this->delete(route('admin.zone-admins.destroy', $zoneAdmin->id));
        $response->assertRedirect(route('admin.zone-admins.index'));

        $this->assertSoftDeleted('zone_admins', ['id' => $zoneAdmin->id]);

        $zoneAdmin->refresh();
        expect($zoneAdmin->deleted_at)->not->toBeNull()
            ->and($zoneAdmin->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $zoneAdmin = ZoneAdmin::factory()->create();

        $response = $this->delete(route('admin.zone-admins.destroy', $zoneAdmin));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple zone-admins', function () {
        $zoneAdmins = ZoneAdmin::factory()->count(3)->create();
        $ids = $zoneAdmins->pluck('id')->toArray();

        $response = $this->delete(route('admin.zone-admins.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('zone_admins', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $zoneAdmin = ZoneAdmin::factory()->create();

        $response = $this->delete(route('admin.zone-admins.massDestroy'), [
            'ids' => [$zoneAdmin->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $zoneAdmin = ZoneAdmin::factory()->create();

        $response = $this->delete(route('admin.zone-admins.massDestroy'), [
            'ids' => [$zoneAdmin->id],
        ]);

        $response->assertForbidden();
    });

});
