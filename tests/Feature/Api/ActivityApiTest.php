<?php

use App\Models\Activity;
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
    $this->user = User::query()->where('login', 'admin@admin.com')->first();
    Passport::actingAs($this->user);
});

// Helper pour créer un payload valide (création)
function validActivityPayload(): array
{
    return [
        'name' => 'Test Activity',
        'description' => 'Test Description',
    ];
}

// Helper pour récupérer les activités (avec ou sans enveloppe "data")
function activitiesFromResponse($response): array
{
    $json = $response->json();

    if (is_array($json) && array_key_exists('data', $json) && is_array($json['data'])) {
        return $json['data'];
    }

    return $json;
}

/**
 * Insère dans la DB de test quelques activités “réelles”
 * (issues de ton dump) si elles n’existent pas déjà.
 */
function ensureDemoActivities(): void
{
    if (Activity::query()->where('name', 'Helpdesk')->exists()) {
        // On suppose que si Helpdesk existe, tout le jeu de démo est déjà là
        return;
    }

    Activity::query()->insert([
        [
            'id' => 5,
            'name' => 'Helpdesk',
            'description' => '<p>User support</p>',
            'recovery_time_objective' => 1,
            'maximum_tolerable_downtime' => 1,
            'recovery_point_objective' => 1,
            'maximum_tolerable_data_loss' => 1,
            'drp' => null,
            'drp_link' => null,
            'created_at' => '2020-08-13 05:49:05',
            'updated_at' => '2020-08-13 05:49:05',
            'deleted_at' => null,
        ],
        [
            'id' => 6,
            'name' => 'Development',
            'description' => '<p>Application development</p>',
            'recovery_time_objective' => null,
            'maximum_tolerable_downtime' => null,
            'recovery_point_objective' => null,
            'maximum_tolerable_data_loss' => null,
            'drp' => null,
            'drp_link' => null,
            'created_at' => '2020-08-13 05:49:47',
            'updated_at' => '2020-08-13 05:49:47',
            'deleted_at' => null,
        ],
        [
            'id' => 7,
            'name' => 'IT monitoring',
            'description' => '<p>Check the proper functioning of IT equipment</p>',
            'recovery_time_objective' => null,
            'maximum_tolerable_downtime' => null,
            'recovery_point_objective' => null,
            'maximum_tolerable_data_loss' => null,
            'drp' => null,
            'drp_link' => null,
            'created_at' => '2020-08-13 05:52:47',
            'updated_at' => '2020-08-13 05:52:47',
            'deleted_at' => null,
        ],
        [
            'id' => 8,
            'name' => 'Application monitoring',
            'description' => '<p>Check the correct functioning of computer applications</p>',
            'recovery_time_objective' => null,
            'maximum_tolerable_downtime' => null,
            'recovery_point_objective' => null,
            'maximum_tolerable_data_loss' => null,
            'drp' => null,
            'drp_link' => null,
            'created_at' => '2020-08-13 05:53:19',
            'updated_at' => '2020-08-13 05:53:19',
            'deleted_at' => null,
        ],
        [
            'id' => 9,
            'name' => 'Admission',
            'description' => '<p>Admission of patients to the hospital</p>',
            'recovery_time_objective' => null,
            'maximum_tolerable_downtime' => null,
            'recovery_point_objective' => null,
            'maximum_tolerable_data_loss' => null,
            'drp' => null,
            'drp_link' => null,
            'created_at' => '2020-09-07 07:54:20',
            'updated_at' => '2024-10-14 08:01:04',
            'deleted_at' => null,
        ],
        [
            'id' => 10,
            'name' => 'Complaint management',
            'description' => '<p>Complaints management process</p>',
            'recovery_time_objective' => null,
            'maximum_tolerable_downtime' => null,
            'recovery_point_objective' => null,
            'maximum_tolerable_data_loss' => null,
            'drp' => null,
            'drp_link' => null,
            'created_at' => '2023-04-12 07:39:25',
            'updated_at' => '2024-10-14 08:00:35',
            'deleted_at' => null,
        ],
    ]);
}

// ============================================================
// Tests pour l'API Activities (CRUD + massDestroy)
// ============================================================
it('forbids listing activities without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $this->getJson('/api/activities')
        ->assertForbidden();
});

it('lists activities when permitted', function () {
    Passport::actingAs($this->user);

    ensureDemoActivities();

    $totalActive = Activity::query()->count();

    $response = $this->getJson('/api/activities')
        ->assertOk();

    $data = activitiesFromResponse($response);

    expect($data)->toHaveCount($totalActive);
});

it('forbids creating an activity without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $this->postJson('/api/activities', validActivityPayload())
        ->assertForbidden();
});

it('creates an activity when permitted', function () {
    Passport::actingAs($this->user);

    $this->postJson('/api/activities', validActivityPayload())
        ->assertCreated()
        ->assertJson(['name' => 'Test Activity']);

    $this->assertDatabaseHas('activities', ['name' => 'Test Activity']);
});

it('shows a single activity when permitted', function () {
    Passport::actingAs($this->user);

    ensureDemoActivities();

    $activity = Activity::query()->where('name', 'Helpdesk')->firstOrFail();

    $this->getJson("/api/activities/{$activity->id}")
        ->assertOk()
        ->assertJsonFragment(['name' => 'Helpdesk']);
});

it('forbids showing an activity without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    ensureDemoActivities();

    $activity = Activity::where('name', 'Helpdesk')->firstOrFail();

    $this->getJson("/api/activities/{$activity->id}")
        ->assertForbidden();
});

it('updates an activity when permitted', function () {
    Passport::actingAs($this->user);

    ensureDemoActivities();

    $activity = Activity::query()->where('name', 'Helpdesk')->firstOrFail();

    $this->putJson("/api/activities/{$activity->id}", ['name' => 'Helpdesk – updated'])
        ->assertOk();

    $this->assertDatabaseHas('activities', [
        'id' => $activity->id,
        'name' => 'Helpdesk – updated',
    ]);
});

it('forbids updating an activity without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    ensureDemoActivities();

    $activity = Activity::where('name', 'Helpdesk')->firstOrFail();

    $this->putJson("/api/activities/{$activity->id}", ['name' => 'Helpdesk – updated'])
        ->assertForbidden();

    $this->assertDatabaseHas('activities', [
        'id' => $activity->id,
        'name' => 'Helpdesk',
    ]);
});

it('deletes an activity when permitted', function () {
    Passport::actingAs($this->user);

    ensureDemoActivities();

    $activity = Activity::query()->where('name', 'Development')->firstOrFail();

    $this->deleteJson("/api/activities/{$activity->id}")
        ->assertOk();

    $this->assertSoftDeleted('activities', ['id' => $activity->id]);
});

it('forbids deleting an activity without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    ensureDemoActivities();

    $activity = Activity::query()->where('name', 'Development')->firstOrFail();

    $this->deleteJson("/api/activities/{$activity->id}")
        ->assertForbidden();

    $this->assertDatabaseHas('activities', [
        'id' => $activity->id,
        'deleted_at' => null,
    ]);
});

it('mass destroys activities when permitted', function () {
    Passport::actingAs($this->user);

    ensureDemoActivities();

    $activities = Activity::query()->whereIn('name', [
        'Helpdesk',
        'Development',
        'IT monitoring',
    ])->get();

    $ids = $activities->pluck('id')->toArray();

    $this->deleteJson('/api/activities/mass-destroy', ['ids' => $ids])
        ->assertNoContent();

    foreach ($ids as $id) {
        $this->assertSoftDeleted('activities', ['id' => $id]);
    }
});

it('forbids mass destroy without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    ensureDemoActivities();

    $activities = Activity::whereIn('name', [
        'Helpdesk',
        'Development',
        'IT monitoring',
    ])->get();

    $ids = $activities->pluck('id')->toArray();

    $this->deleteJson('/api/activities/mass-destroy', ['ids' => $ids])
        ->assertForbidden();

    foreach ($ids as $id) {
        $this->assertDatabaseHas('activities', [
            'id' => $id,
            'deleted_at' => null,
        ]);
    }
});

// ============================================================
// Tests des filtres sur l'index
// ============================================================

it('filters activities by name contains', function () {
    Passport::actingAs($this->user);

    ensureDemoActivities();

    // "monitoring" est contenu dans "IT monitoring" et "Application monitoring"
    $response = $this->getJson('/api/activities?name__contains=monitoring')
        ->assertOk();

    $data = activitiesFromResponse($response);
    $names = collect($data)->pluck('name');

    expect($names)->toContain('Application monitoring');
    expect($names)->toContain('IT monitoring');
});

it('filters activities by exact name', function () {
    Passport::actingAs($this->user);

    ensureDemoActivities();

    $response = $this->getJson('/api/activities?filter[name]=Complaint%20management')
        ->assertOk();

    $data = activitiesFromResponse($response);
    $names = collect($data)->pluck('name');

    expect($names)->toHaveCount(1);
    expect($names->first())->toBe('Complaint management');
});

it('does not match partial name with the exact filter', function () {
    Passport::actingAs($this->user);

    ensureDemoActivities();

    // "monitoring" n'est pas un nom exact d'activité
    $response = $this->getJson('/api/activities?filter[name]=monitoring')
        ->assertOk();

    $data = activitiesFromResponse($response);

    expect($data)->toBeEmpty();
});

it('filters activities by partial name with the _like operator', function () {
    Passport::actingAs($this->user);

    ensureDemoActivities();

    // "monitoring" est contenu dans "IT monitoring" et "Application monitoring"
    $response = $this->getJson('/api/activities?filter[name_like]=monitoring')
        ->assertOk();

    $data = activitiesFromResponse($response);
    $names = collect($data)->pluck('name');

    expect($names)->toContain('Application monitoring');
    expect($names)->toContain('IT monitoring');
    expect($names)->not->toContain('Helpdesk');
});

it('escapes LIKE meta-characters in the _like operator', function () {
    Passport::actingAs($this->user);

    ensureDemoActivities();

    $response = $this->getJson('/api/activities?'.http_build_query([
        'filter' => ['name_like' => '%'],
    ]))->assertOk();

    $data = activitiesFromResponse($response);

    // Le caractère "%" est échappé, donc aucune activité ne doit correspondre
    expect($data)->toBeEmpty();
});

it('filters activities by numeric gte on recovery_time_objective', function () {
    Passport::actingAs($this->user);

    ensureDemoActivities();

    $helpdesk = Activity::query()->where('name', 'Helpdesk')->firstOrFail();
    $development = Activity::query()->where('name', 'Development')->firstOrFail();

    $helpdesk->update(['recovery_time_objective' => 4]);
    $development->update(['recovery_time_objective' => 24]);

    $response = $this->getJson('/api/activities?filter[recovery_time_objective_gte]=8')
        ->assertOk();

    $data = activitiesFromResponse($response);
    $names = collect($data)->pluck('name');

    expect($names)->toContain('Development');
    expect($names)->not->toContain('Helpdesk');
});

it('ignores unknown filter fields', function () {
    Passport::actingAs($this->user);

    ensureDemoActivities();

    $totalActive = Activity::query()->whereNull('deleted_at')->count();

    $response = $this->getJson('/api/activities?foo=bar')
        ->assertOk();

    $data = activitiesFromResponse($response);

    expect($data)->toHaveCount($totalActive);
});

// ============================================================
// Tests massStore
// ============================================================

it('mass stores activities when permitted', function () {
    Passport::actingAs($this->user);

    $payload = [
        'items' => [
            validActivityPayload(),
            [
                'name' => 'Another Activity',
                'description' => 'Another Description',
            ],
        ],
    ];

    $response = $this->postJson('/api/activities/mass-store', $payload)
        ->assertCreated()
        ->assertJson([
            'status' => 'ok',
            'count' => 2,
        ]);

    $ids = $response->json('ids');
    expect($ids)->toBeArray()->toHaveCount(2);

    $this->assertDatabaseHas('activities', ['name' => 'Test Activity']);
    $this->assertDatabaseHas('activities', ['name' => 'Another Activity']);
});

it('forbids mass store without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $payload = [
        'items' => [
            validActivityPayload(),
        ],
    ];

    $this->postJson('/api/activities/mass-store', $payload)
        ->assertForbidden();
});

// ============================================================
// Tests massUpdate
// ============================================================

it('mass updates activities when permitted', function () {
    Passport::actingAs($this->user);

    ensureDemoActivities();

    $helpdesk = Activity::query()->where('name', 'Helpdesk')->firstOrFail();
    $development = Activity::query()->where('name', 'Development')->firstOrFail();

    $payload = [
        'items' => [
            ['id' => $helpdesk->id, 'name' => 'Helpdesk – updated'],
            ['id' => $development->id, 'name' => 'Development – updated'],
        ],
    ];

    $this->putJson('/api/activities/mass-update', $payload)
        ->assertOk()
        ->assertJson(['status' => 'ok']);

    $this->assertDatabaseHas('activities', [
        'id' => $helpdesk->id,
        'name' => 'Helpdesk – updated',
    ]);
    $this->assertDatabaseHas('activities', [
        'id' => $development->id,
        'name' => 'Development – updated',
    ]);
});

it('forbids mass update without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    ensureDemoActivities();

    $helpdesk = Activity::query()->where('name', 'Helpdesk')->firstOrFail();
    $development = Activity::query()->where('name', 'Development')->firstOrFail();

    $payload = [
        'items' => [
            ['id' => $helpdesk->id, 'name' => 'Helpdesk – updated'],
            ['id' => $development->id, 'name' => 'Development – updated'],
        ],
    ];

    $this->putJson('/api/activities/mass-update', $payload)
        ->assertForbidden();

    $this->assertDatabaseHas('activities', [
        'id' => $helpdesk->id,
        'name' => 'Helpdesk',
    ]);
    $this->assertDatabaseHas('activities', [
        'id' => $development->id,
        'name' => 'Development',
    ]);
});
