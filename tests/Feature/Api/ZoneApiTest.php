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
use Laravel\Passport\Passport;

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
    Passport::actingAs($this->user);
});

// ============================================================
// index
// ============================================================

it('forbids listing zones without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $this->getJson('/api/zones')->assertForbidden();
});

it('lists zones when permitted', function () {
    Zone::factory()->count(3)->create();

    $response = $this->getJson('/api/zones')->assertOk();

    $data = $response->json();
    $data = isset($data['data']) ? $data['data'] : $data;

    expect($data)->toHaveCount(3);
});

// ============================================================
// store
// ============================================================

it('forbids creating a zone without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $this->postJson('/api/zones', ['name' => 'Zone Test'])->assertForbidden();
});

it('creates a zone with scalar fields', function () {
    $this->postJson('/api/zones', [
        'name' => 'Zone DMZ',
        'type' => 'DMZ',
        'attributes' => ['firewall=true', 'vlan=10'],
        'description' => 'Zone démilitarisée.',
    ])
        ->assertCreated()
        ->assertJsonFragment(['name' => 'Zone DMZ']);

    $this->assertDatabaseHas('zones', ['name' => 'Zone DMZ', 'type' => 'DMZ']);
});

it('creates a zone with attributes as a plain string', function () {
    $this->postJson('/api/zones', [
        'name' => 'Zone String Attr',
        'attributes' => 'ovni',
    ])->assertCreated();

    $this->assertDatabaseHas('zones', ['name' => 'Zone String Attr', 'attributes' => 'ovni']);
});

it('creates a zone with relations', function () {
    $parent = Zone::factory()->create(['name' => 'Parent Zone']);
    $child = Zone::factory()->create(['name' => 'Child Zone']);
    $building = Building::factory()->create();
    $admin = AdminUser::factory()->create();

    $response = $this->postJson('/api/zones', [
        'name' => 'Zone Secure',
        'parentZones' => [$parent->id],
        'childZones' => [$child->id],
        'buildings' => [$building->id],
        'adminUsers' => [$admin->id],
    ])->assertCreated();

    $zone = Zone::where('name', 'Zone Secure')->first();
    expect($zone)->not->toBeNull();
    expect($zone->parentZones->pluck('id')->toArray())->toContain($parent->id);
    expect($zone->childZones->pluck('id')->toArray())->toContain($child->id);
    expect($zone->buildings->pluck('id')->toArray())->toContain($building->id);
    expect($zone->adminUsers->pluck('id')->toArray())->toContain($admin->id);
});

it('rejects creating a zone without name', function () {
    $this->postJson('/api/zones', ['type' => 'DMZ'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('rejects duplicate zone name', function () {
    Zone::factory()->create(['name' => 'Existing Zone']);

    $this->postJson('/api/zones', ['name' => 'Existing Zone'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

// ============================================================
// show
// ============================================================

it('forbids showing a zone without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $zone = Zone::factory()->create();

    $this->getJson("/api/zones/{$zone->id}")->assertForbidden();
});

it('shows a zone when permitted', function () {
    $zone = Zone::factory()->create(['name' => 'Zone Visible']);

    $this->getJson("/api/zones/{$zone->id}")
        ->assertOk()
        ->assertJsonFragment(['name' => 'Zone Visible']);
});

it('show includes relations', function () {
    $parent = Zone::factory()->create(['name' => 'Parent Zone']);
    $child = Zone::factory()->create(['name' => 'Child Zone']);
    $building = Building::factory()->create();
    $admin = AdminUser::factory()->create();

    $zone = Zone::factory()->create(['name' => 'Zone With Relations']);
    $zone->parentZones()->sync([$parent->id]);
    $zone->childZones()->sync([$child->id]);
    $zone->buildings()->sync([$building->id]);
    $zone->adminUsers()->sync([$admin->id]);

    $data = $this->getJson("/api/zones/{$zone->id}")->assertOk()->json('data');

    expect($data['parentZones'])->toContain($parent->id);
    expect($data['childZones'])->toContain($child->id);
    expect($data['buildings'])->toContain($building->id);
    expect($data['adminUsers'])->toContain($admin->id);
});

// ============================================================
// update
// ============================================================

it('forbids updating a zone without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $zone = Zone::factory()->create();

    $this->putJson("/api/zones/{$zone->id}", ['name' => 'New Name'])->assertForbidden();
});

it('updates scalar fields', function () {
    $zone = Zone::factory()->create(['name' => 'Old Name']);

    $this->putJson("/api/zones/{$zone->id}", [
        'name' => 'New Name',
        'type' => 'LAN',
    ])->assertOk();

    $this->assertDatabaseHas('zones', ['id' => $zone->id, 'name' => 'New Name', 'type' => 'LAN']);
});

it('updates relations', function () {
    $zone = Zone::factory()->create();
    $building = Building::factory()->create();
    $admin = AdminUser::factory()->create();

    $this->putJson("/api/zones/{$zone->id}", [
        'name' => $zone->name,
        'buildings' => [$building->id],
        'adminUsers' => [$admin->id],
    ])->assertOk();

    $zone->refresh();
    expect($zone->buildings->pluck('id')->toArray())->toContain($building->id);
    expect($zone->adminUsers->pluck('id')->toArray())->toContain($admin->id);
});

// ============================================================
// destroy
// ============================================================

it('forbids deleting a zone without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $zone = Zone::factory()->create();

    $this->deleteJson("/api/zones/{$zone->id}")->assertForbidden();
});

it('soft-deletes a zone when permitted', function () {
    $zone = Zone::factory()->create();

    $this->deleteJson("/api/zones/{$zone->id}")->assertOk();

    $this->assertSoftDeleted('zones', ['id' => $zone->id]);
});

// ============================================================
// massDestroy
// ============================================================

it('forbids mass destroy without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $zone = Zone::factory()->create();

    $this->deleteJson('/api/zones/mass-destroy', ['ids' => [$zone->id]])->assertForbidden();
});

it('mass destroys zones when permitted', function () {
    $zones = Zone::factory()->count(3)->create();
    $ids = $zones->pluck('id')->toArray();

    $this->deleteJson('/api/zones/mass-destroy', ['ids' => $ids])->assertNoContent();

    foreach ($ids as $id) {
        $this->assertSoftDeleted('zones', ['id' => $id]);
    }
});

// ============================================================
// massStore
// ============================================================

it('forbids mass store without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $this->postJson('/api/zones/mass-store', [
        'items' => [['name' => 'Zone A']],
    ])->assertForbidden();
});

it('mass stores zones when permitted', function () {
    $response = $this->postJson('/api/zones/mass-store', [
        'items' => [
            ['name' => 'Zone A', 'type' => 'LAN'],
            ['name' => 'Zone B', 'type' => 'DMZ'],
        ],
    ])
        ->assertCreated()
        ->assertJson(['status' => 'ok', 'count' => 2]);

    $ids = $response->json('ids');
    expect($ids)->toBeArray()->toHaveCount(2);

    $this->assertDatabaseHas('zones', ['name' => 'Zone A']);
    $this->assertDatabaseHas('zones', ['name' => 'Zone B']);
});

it('mass stores zones with relations', function () {
    $building = Building::factory()->create();
    $admin = AdminUser::factory()->create();

    $response = $this->postJson('/api/zones/mass-store', [
        'items' => [[
            'name' => 'Zone With Relations',
            'buildings' => [$building->id],
            'adminUsers' => [$admin->id],
        ]],
    ])->assertCreated();

    $zone = Zone::where('name', 'Zone With Relations')->first();
    expect($zone->buildings->pluck('id')->toArray())->toContain($building->id);
    expect($zone->adminUsers->pluck('id')->toArray())->toContain($admin->id);
});

// ============================================================
// massUpdate
// ============================================================

it('forbids mass update without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $zone = Zone::factory()->create();

    $this->putJson('/api/zones/mass-update', [
        'items' => [['id' => $zone->id, 'name' => 'Updated']],
    ])->assertForbidden();
});

it('mass updates zones when permitted', function () {
    $zoneA = Zone::factory()->create(['name' => 'Zone Alpha']);
    $zoneB = Zone::factory()->create(['name' => 'Zone Beta']);

    $this->putJson('/api/zones/mass-update', [
        'items' => [
            ['id' => $zoneA->id, 'name' => 'Zone Alpha – updated'],
            ['id' => $zoneB->id, 'name' => 'Zone Beta – updated'],
        ],
    ])
        ->assertOk()
        ->assertJson(['status' => 'ok']);

    $this->assertDatabaseHas('zones', ['id' => $zoneA->id, 'name' => 'Zone Alpha – updated']);
    $this->assertDatabaseHas('zones', ['id' => $zoneB->id, 'name' => 'Zone Beta – updated']);
});

it('mass updates zones relations', function () {
    $zone = Zone::factory()->create();
    $building = Building::factory()->create();

    $this->putJson('/api/zones/mass-update', [
        'items' => [[
            'id' => $zone->id,
            'name' => $zone->name,
            'buildings' => [$building->id],
        ]],
    ])->assertOk();

    $zone->refresh();
    expect($zone->buildings->pluck('id')->toArray())->toContain($building->id);
});
