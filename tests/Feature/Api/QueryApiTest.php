<?php

use App\Models\SavedQuery;
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

function makeQuery(array $overrides = []): SavedQuery
{
    return SavedQuery::create(array_merge([
        'name' => 'Test Query',
        'query' => ['from' => 'applications', 'output' => 'list'],
        'is_public' => false,
        'user_id' => null,
    ], $overrides));
}

// ============================================================
// massDestroy
// ============================================================

it('mass destroys saved queries when permitted', function () {
    $q1 = makeQuery(['name' => 'Query One']);
    $q2 = makeQuery(['name' => 'Query Two']);
    $q3 = makeQuery(['name' => 'Query Three']);

    $this->deleteJson('/api/queries/mass-destroy', ['ids' => [$q1->id, $q2->id, $q3->id]])
        ->assertNoContent();

    $this->assertDatabaseMissing('saved_queries', ['id' => $q1->id]);
    $this->assertDatabaseMissing('saved_queries', ['id' => $q2->id]);
    $this->assertDatabaseMissing('saved_queries', ['id' => $q3->id]);
});

it('does not delete unrelated saved queries on mass destroy', function () {
    $target = makeQuery(['name' => 'To Delete']);
    $survivor = makeQuery(['name' => 'To Keep']);

    $this->deleteJson('/api/queries/mass-destroy', ['ids' => [$target->id]])
        ->assertNoContent();

    $this->assertDatabaseMissing('saved_queries', ['id' => $target->id]);
    $this->assertDatabaseHas('saved_queries', ['id' => $survivor->id]);
});

it('forbids mass destroy without permission', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $query = makeQuery();

    $this->deleteJson('/api/queries/mass-destroy', ['ids' => [$query->id]])
        ->assertForbidden();

    $this->assertDatabaseHas('saved_queries', ['id' => $query->id]);
});
