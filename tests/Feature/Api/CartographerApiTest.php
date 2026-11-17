<?php

use App\Models\Application;
use App\Models\Cartographer;
use App\Models\Role;
use App\Models\User;
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
    $this->admin = User::query()->where('login', 'admin@admin.com')->first();
    $this->application = Application::factory()->create(['name' => 'App Test']);
    Passport::actingAs($this->admin);
});

// ── helpers ──────────────────────────────────────────────────────────────────

function makeCartographer(array $attrs = []): Cartographer
{
    $app = Application::factory()->create(['name' => fake()->unique()->word()]);

    return Cartographer::create(array_merge([
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $app->id,
        'user_id' => null,
        'role_id' => null,
    ], $attrs));
}

// ═══════════════════════════════════════════════════════════════════════════════
// index
// ═══════════════════════════════════════════════════════════════════════════════

it('forbids listing cartographers without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $this->getJson('/api/cartographers')->assertForbidden();
});

it('lists cartographers when permitted', function () {
    $user = User::factory()->create();
    makeCartographer(['user_id' => $user->id]);
    makeCartographer(['user_id' => $user->id]);

    $response = $this->getJson('/api/cartographers')->assertOk();

    $data = $response->json();
    $data = isset($data['data']) ? $data['data'] : $data;
    expect(count($data))->toBeGreaterThanOrEqual(2);
});

// ═══════════════════════════════════════════════════════════════════════════════
// store
// ═══════════════════════════════════════════════════════════════════════════════

it('forbids creating a cartographer without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $this->postJson('/api/cartographers', [
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $this->application->id,
        'user_id' => $user->id,
    ])->assertForbidden();
});

it('creates a cartographer assigned to a user', function () {
    $user = User::factory()->create();

    $this->postJson('/api/cartographers', [
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $this->application->id,
        'user_id' => $user->id,
    ])->assertCreated();

    $this->assertDatabaseHas('cartographers', [
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $this->application->id,
        'user_id' => $user->id,
        'role_id' => null,
    ]);
});

it('creates a cartographer assigned to a role', function () {
    $role = Role::factory()->create();

    $this->postJson('/api/cartographers', [
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $this->application->id,
        'role_id' => $role->id,
    ])->assertCreated();

    $this->assertDatabaseHas('cartographers', [
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $this->application->id,
        'role_id' => $role->id,
    ]);
});

it('rejects creation when neither user_id nor role_id is provided', function () {
    $this->postJson('/api/cartographers', [
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $this->application->id,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('user_id');
});

it('rejects an invalid cartographiable_type', function () {
    $this->postJson('/api/cartographers', [
        'cartographiable_type' => 'App\\Models\\User',
        'cartographiable_id' => 1,
        'user_id' => $this->admin->id,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('cartographiable_type');
});

it('deduplicates via firstOrCreate on second store', function () {
    $user = User::factory()->create();
    $payload = [
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $this->application->id,
        'user_id' => $user->id,
    ];

    $this->postJson('/api/cartographers', $payload)->assertCreated();
    $this->postJson('/api/cartographers', $payload)->assertOk();   // 200 = already exists

    $this->assertDatabaseCount('cartographers', 1);
});

// ═══════════════════════════════════════════════════════════════════════════════
// show
// ═══════════════════════════════════════════════════════════════════════════════

it('forbids showing a cartographer without permission', function () {
    $user = User::factory()->create();
    $carto = makeCartographer(['user_id' => $user->id]);
    Passport::actingAs($user);

    $this->getJson("/api/cartographers/{$carto->id}")->assertForbidden();
});

it('shows a cartographer when permitted', function () {
    $user = User::factory()->create();
    $carto = makeCartographer(['user_id' => $user->id]);

    $this->getJson("/api/cartographers/{$carto->id}")
        ->assertOk()
        ->assertJsonFragment(['id' => $carto->id]);
});

// ═══════════════════════════════════════════════════════════════════════════════
// update
// ═══════════════════════════════════════════════════════════════════════════════

it('forbids updating a cartographer without permission', function () {
    $user = User::factory()->create();
    $carto = makeCartographer(['user_id' => $user->id]);
    Passport::actingAs($user);

    $this->putJson("/api/cartographers/{$carto->id}", [
        'user_id' => $user->id,
    ])->assertForbidden();
});

it('updates the user_id of a cartographer', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $carto = makeCartographer(['user_id' => $userA->id]);

    $this->putJson("/api/cartographers/{$carto->id}", [
        'user_id' => $userB->id,
    ])->assertOk();

    $this->assertDatabaseHas('cartographers', [
        'id' => $carto->id,
        'user_id' => $userB->id,
    ]);
});

it('rejects update when neither user_id nor role_id is provided', function () {
    $user = User::factory()->create();
    $carto = makeCartographer(['user_id' => $user->id]);

    $this->putJson("/api/cartographers/{$carto->id}", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('user_id');
});

// ═══════════════════════════════════════════════════════════════════════════════
// destroy
// ═══════════════════════════════════════════════════════════════════════════════

it('forbids deleting a cartographer without permission', function () {
    $user = User::factory()->create();
    $carto = makeCartographer(['user_id' => $user->id]);
    Passport::actingAs($user);

    $this->deleteJson("/api/cartographers/{$carto->id}")->assertForbidden();
});

it('deletes a cartographer when permitted', function () {
    $user = User::factory()->create();
    $carto = makeCartographer(['user_id' => $user->id]);

    $this->deleteJson("/api/cartographers/{$carto->id}")->assertOk();

    $this->assertSoftDeleted('cartographers', ['id' => $carto->id]);
});

// ═══════════════════════════════════════════════════════════════════════════════
// massDestroy
// ═══════════════════════════════════════════════════════════════════════════════

it('forbids mass destroy without permission', function () {
    $user = User::factory()->create();
    $carto = makeCartographer(['user_id' => $user->id]);
    Passport::actingAs($user);

    $this->deleteJson('/api/cartographers/mass-destroy', ['ids' => [$carto->id]])->assertForbidden();
});

it('mass destroys cartographers when permitted', function () {
    $user = User::factory()->create();
    $cartoA = makeCartographer(['user_id' => $user->id]);
    $cartoB = makeCartographer(['user_id' => $user->id]);

    $this->deleteJson('/api/cartographers/mass-destroy', [
        'ids' => [$cartoA->id, $cartoB->id],
    ])->assertNoContent();

    $this->assertSoftDeleted('cartographers', ['id' => $cartoA->id]);
    $this->assertSoftDeleted('cartographers', ['id' => $cartoB->id]);
});

// ═══════════════════════════════════════════════════════════════════════════════
// massStore
// ═══════════════════════════════════════════════════════════════════════════════

it('forbids mass store without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $this->postJson('/api/cartographers/mass-store', [
        'items' => [[
            'cartographiable_type' => Application::class,
            'cartographiable_id' => $this->application->id,
            'user_id' => $user->id,
        ]],
    ])->assertForbidden();
});

it('mass stores cartographers when permitted', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $appB = Application::factory()->create(['name' => 'App B']);

    $this->postJson('/api/cartographers/mass-store', [
        'items' => [
            [
                'cartographiable_type' => Application::class,
                'cartographiable_id' => $this->application->id,
                'user_id' => $userA->id,
            ],
            [
                'cartographiable_type' => Application::class,
                'cartographiable_id' => $appB->id,
                'user_id' => $userB->id,
            ],
        ],
    ])->assertCreated()
        ->assertJsonFragment(['count' => 2]);

    $this->assertDatabaseHas('cartographers', [
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $this->application->id,
        'user_id' => $userA->id,
    ]);
});

// ═══════════════════════════════════════════════════════════════════════════════
// massUpdate
// ═══════════════════════════════════════════════════════════════════════════════

it('forbids mass update without permission', function () {
    $user = User::factory()->create();
    $carto = makeCartographer(['user_id' => $user->id]);
    Passport::actingAs($user);

    $this->putJson('/api/cartographers/mass-update', [
        'items' => [['id' => $carto->id, 'user_id' => $user->id]],
    ])->assertForbidden();
});

it('mass updates cartographers when permitted', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $cartoA = makeCartographer(['user_id' => $userA->id]);
    $cartoB = makeCartographer(['user_id' => $userA->id]);

    $this->putJson('/api/cartographers/mass-update', [
        'items' => [
            ['id' => $cartoA->id, 'user_id' => $userB->id],
            ['id' => $cartoB->id, 'user_id' => $userB->id],
        ],
    ])->assertOk();

    $this->assertDatabaseHas('cartographers', ['id' => $cartoA->id, 'user_id' => $userB->id]);
    $this->assertDatabaseHas('cartographers', ['id' => $cartoB->id, 'user_id' => $userB->id]);
});
