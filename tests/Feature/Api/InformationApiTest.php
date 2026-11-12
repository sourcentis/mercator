<?php

use App\Models\Information;
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

test('can create information with retention fields', function () {
    $response = $this->postJson('/api/information', [
        'name' => 'Candidatures spontanées',
        'retention_period' => '6 mois',
        'final_disposition' => 'Destruction',
        'retention_notes' => 'Après visa du service des archives',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('information', [
        'name' => 'Candidatures spontanées',
        'retention_period' => '6 mois',
        'final_disposition' => 'Destruction',
    ]);
});

test('can update and read retention fields', function () {
    $information = Information::factory()->create(['name' => 'Convention partenaires']);

    $this->putJson("/api/information/{$information->id}", [
        'name' => 'Convention partenaires',
        'retention_period' => 'Durée de la convention + 5 ans',
        'final_disposition' => 'Versement aux archives',
    ])->assertOk();

    $this->getJson("/api/information/{$information->id}")
        ->assertOk()
        ->assertJsonPath('data.retention_period', 'Durée de la convention + 5 ans')
        ->assertJsonPath('data.final_disposition', 'Versement aux archives');
});

test('can filter information by retention period', function () {
    Information::factory()->create(['name' => 'Info A', 'retention_period' => '6 mois']);
    Information::factory()->create(['name' => 'Info B', 'retention_period' => '10 ans']);

    $response = $this->getJson('/api/information?filter[retention_period]=10 ans');

    $response->assertOk();
    $names = collect($response->json('data') ?? $response->json())->pluck('name');
    expect($names)->toContain('Info B')->not->toContain('Info A');
});
