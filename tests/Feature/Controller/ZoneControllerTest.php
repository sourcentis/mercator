<?php

use App\Models\AdminUser;
use App\Models\Building;
use App\Models\User;
use App\Models\Zone;
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
    test('can display zones index page', function () {
        Zone::factory()->count(3)->create();

        $response = $this->get(route('admin.zones.index'));

        $response->assertOk();
        $response->assertViewIs('admin.zones.index');
        $response->assertViewHas('zones');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.zones.index'));

        $response->assertForbidden();
    });
});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.zones.create'));

        $response->assertOk();
        $response->assertViewIs('admin.zones.create');
        $response->assertViewHas(['zones', 'buildings', 'adminUsers']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.zones.create'));

        $response->assertForbidden();
    });
});

describe('store', function () {
    test('can create a zone with scalar fields', function () {
        $data = [
            'name' => 'Zone DMZ',
            'type' => 'DMZ',
            'attributes' => ['firewall=true', 'vlan=10'],
            'description' => 'Zone démilitarisée.',
        ];

        $response = $this->post(route('admin.zones.store'), $data);

        $response->assertRedirect(route('admin.zones.index'));
        $this->assertDatabaseHas('zones', ['name' => 'Zone DMZ', 'type' => 'DMZ', 'attributes' => 'firewall=true vlan=10']);
    });

    test('can create a zone with relations', function () {
        $parent = Zone::factory()->create(['name' => 'Parent Zone']);
        $child = Zone::factory()->create(['name' => 'Child Zone']);
        $building = Building::factory()->create();
        $admin = AdminUser::factory()->create();

        $data = [
            'name' => 'Zone Secure',
            'parentZones' => [$parent->id],
            'childZones' => [$child->id],
            'buildings' => [$building->id],
            'adminUsers' => [$admin->id],
        ];

        $response = $this->post(route('admin.zones.store'), $data);

        $response->assertRedirect(route('admin.zones.index'));

        $zone = Zone::where('name', 'Zone Secure')->first();
        expect($zone)->not->toBeNull();
        expect($zone->parentZones->pluck('id')->toArray())->toContain($parent->id);
        expect($zone->childZones->pluck('id')->toArray())->toContain($child->id);
        expect($zone->buildings->pluck('id')->toArray())->toContain($building->id);
        expect($zone->adminUsers->pluck('id')->toArray())->toContain($admin->id);
    });

    test('rejects missing name', function () {
        $response = $this->post(route('admin.zones.store'), ['type' => 'DMZ']);

        $response->assertSessionHasErrors('name');
    });

    test('rejects duplicate name', function () {
        Zone::factory()->create(['name' => 'Existing Zone']);

        $response = $this->post(route('admin.zones.store'), ['name' => 'Existing Zone']);

        $response->assertSessionHasErrors('name');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('admin.zones.store'), ['name' => 'Zone Test']);

        $response->assertForbidden();
    });
});

describe('show', function () {
    test('can display a zone', function () {
        $zone = Zone::factory()->create(['name' => 'Zone Visible']);

        $response = $this->get(route('admin.zones.show', $zone->id));

        $response->assertOk();
        $response->assertViewIs('admin.zones.show');
        $response->assertSee('Zone Visible');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $zone = Zone::factory()->create();

        $response = $this->get(route('admin.zones.show', $zone->id));

        $response->assertForbidden();
    });
});

describe('edit', function () {
    test('can display edit form', function () {
        $zone = Zone::factory()->create(['name' => 'Zone Edit']);

        $response = $this->get(route('admin.zones.edit', $zone));

        $response->assertOk();
        $response->assertViewIs('admin.zones.edit');
        $response->assertViewHas(['zone', 'zones', 'buildings', 'adminUsers']);
        $response->assertSee('Zone Edit');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $zone = Zone::factory()->create();

        $response = $this->get(route('admin.zones.edit', $zone));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update scalar fields', function () {
        $zone = Zone::factory()->create(['name' => 'Old Name']);

        $response = $this->put(route('admin.zones.update', $zone), [
            'name' => 'New Name',
            'type' => 'LAN',
        ]);

        $response->assertRedirect(route('admin.zones.index'));
        $this->assertDatabaseHas('zones', ['id' => $zone->id, 'name' => 'New Name', 'type' => 'LAN']);
    });

    test('can update relations', function () {
        $zone = Zone::factory()->create();
        $building = Building::factory()->create();
        $admin = AdminUser::factory()->create();

        $this->put(route('admin.zones.update', $zone), [
            'name' => $zone->name,
            'buildings' => [$building->id],
            'adminUsers' => [$admin->id],
        ]);

        $zone->refresh();
        expect($zone->buildings->pluck('id')->toArray())->toContain($building->id);
        expect($zone->adminUsers->pluck('id')->toArray())->toContain($admin->id);
    });

    test('rejects missing name on update', function () {
        $zone = Zone::factory()->create();

        $response = $this->put(route('admin.zones.update', $zone), ['name' => '']);

        $response->assertSessionHasErrors('name');
    });
});

describe('destroy', function () {
    test('can soft-delete a zone', function () {
        $zone = Zone::factory()->create();

        $response = $this->delete(route('admin.zones.destroy', $zone->id));

        $response->assertRedirect(route('admin.zones.index'));
        $this->assertSoftDeleted('zones', ['id' => $zone->id]);

        $zone->refresh();
        expect($zone->deleted_at)->not->toBeNull()
            ->and($zone->trashed())->toBeTrue();
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $zone = Zone::factory()->create();

        $response = $this->delete(route('admin.zones.destroy', $zone));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple zones', function () {
        $zones = Zone::factory()->count(3)->create();
        $ids = $zones->pluck('id')->toArray();

        $response = $this->delete(route('admin.zones.massDestroy'), ['ids' => $ids]);

        $response->assertNoContent();
        foreach ($ids as $id) {
            $this->assertSoftDeleted('zones', ['id' => $id]);
        }
    });

    test('returns 204 no content', function () {
        $zone = Zone::factory()->create();

        $response = $this->delete(route('admin.zones.massDestroy'), ['ids' => [$zone->id]]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $zone = Zone::factory()->create();

        $response = $this->delete(route('admin.zones.massDestroy'), ['ids' => [$zone->id]]);

        $response->assertForbidden();
    });
});
