<?php

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

/**
 * Helper pour vérifier si un modèle a un champ spécifique
 */
function modelHasField(string $modelClass, string $field): bool
{
    $model = new $modelClass;

    return in_array($field, $model->getFillable());
}

/**
 * Test accès index avec permissions
 */
it('allows access to index with proper permissions', function (array $config) {
    $modelClass = $config['model'];
    $items = $modelClass::factory()->count(10)->create();

    $response = $this->getJson("/api/{$config['route']}")
        ->assertOk()
        ->assertJsonStructure([
            '*' => ['id'], // Au minimum id
        ]);

    $data = $response->json();
    expect($data)->toHaveCount(10);

})->with('api_endpoints');

/**
 * Test refus d'accès sans permissions
 */
it('denies access to index without proper permissions', function (array $config) {
    $unauthorizedUser = User::factory()->create();
    Passport::actingAs($unauthorizedUser);

    $this->getJson("/api/{$config['route']}")
        ->assertForbidden();

})->with('api_endpoints');

/**
 * Test filtre par nom (seulement si le modèle a un champ name)
 */
it('filters index by name', function (array $config) {
    $modelClass = $config['model'];

    // Skip si pas de champ 'name'
    if (! modelHasField($modelClass, 'name')) {
        $this->markTestSkipped("Model {$modelClass} does not have 'name' field");
    }

    $items = $modelClass::factory()->count(10)->create();
    $targetItem = $items->first();
    $targetName = $targetItem->name;

    $response = $this->getJson("/api/{$config['route']}?filter[name]={$targetName}")
        ->assertOk();

    $data = $response->json();

    expect($data)->toBeArray();
    expect(count($data))->toBeGreaterThanOrEqual(1);

    $names = collect($data)->pluck('name');
    expect($names)->toContain($targetName);

})->with('api_endpoints');

/**
 * Test filtre avec nom inexistant
 */
it('returns empty array when filtering with non-existent name', function (array $config) {
    $modelClass = $config['model'];

    // Skip si pas de champ 'name'
    if (! modelHasField($modelClass, 'name')) {
        $this->markTestSkipped("Model {$modelClass} does not have 'name' field");
    }

    $modelClass::factory()->count(5)->create();

    $response = $this->getJson("/api/{$config['route']}?filter[name]=NONEXISTENT_NAME_12345")
        ->assertOk();

    $data = $response->json();

    expect($data)->toBeArray();
    expect($data)->toBeEmpty();

})->with('api_endpoints');

/**
 * Test tri ascendant par nom
 */
it('sorts index by name ascending', function (array $config) {
    $modelClass = $config['model'];

    // Skip si pas de champ 'name'
    if (! modelHasField($modelClass, 'name')) {
        $this->markTestSkipped("Model {$modelClass} does not have 'name' field");
    }

    $modelClass::factory()->count(5)->create();

    $response = $this->getJson("/api/{$config['route']}?sort=name")
        ->assertOk();

    $data = $response->json();
    $names = collect($data)->pluck('name')->toArray();

    $sortedNames = $names;
    sort($sortedNames);

    expect($names)->toBe($sortedNames);

})->with('api_endpoints');

/**
 * Test tri descendant par nom
 */
it('sorts index by name descending', function (array $config) {
    $modelClass = $config['model'];

    // Skip si pas de champ 'name'
    if (! modelHasField($modelClass, 'name')) {
        $this->markTestSkipped("Model {$modelClass} does not have 'name' field");
    }

    $modelClass::factory()->count(5)->create();

    $response = $this->getJson("/api/{$config['route']}?sort=-name")
        ->assertOk();

    $data = $response->json();
    $names = collect($data)->pluck('name')->toArray();

    $sortedNames = $names;
    rsort($sortedNames);

    expect($names)->toBe($sortedNames);

})->with('api_endpoints');

/**
 * Test tri par ID (tous les modèles ont un ID)
 */
it('sorts index by id ascending', function (array $config) {
    $modelClass = $config['model'];

    $modelClass::factory()->count(5)->create();

    $response = $this->getJson("/api/{$config['route']}?sort=id")
        ->assertOk();

    $data = $response->json();
    $ids = collect($data)->pluck('id')->toArray();

    $sortedIds = $ids;
    sort($sortedIds);

    expect($ids)->toBe($sortedIds);

})->with('api_endpoints');

/**
 * Test tri par ID descendant
 */
it('sorts index by id descending', function (array $config) {
    $modelClass = $config['model'];

    $modelClass::factory()->count(5)->create();

    $response = $this->getJson("/api/{$config['route']}?sort=-id")
        ->assertOk();

    $data = $response->json();
    $ids = collect($data)->pluck('id')->toArray();

    $sortedIds = $ids;
    rsort($sortedIds);

    expect($ids)->toBe($sortedIds);

})->with('api_endpoints');
