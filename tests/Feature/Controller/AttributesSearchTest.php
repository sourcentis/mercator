<?php

// Issue #2750 : la recherche dans les listes ignorait les champs « attributs » et « type »
// de la plupart des objets.

use App\Models\LogicalServer;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('every model with a given column can search it', function (string $column) {
    $missing = [];

    foreach (glob(app_path('Models/*.php')) as $file) {
        $class = 'App\\Models\\'.basename($file, '.php');
        if (! is_subclass_of($class, Model::class) || (new ReflectionClass($class))->isAbstract()) {
            continue;
        }
        if (! Schema::hasColumn((new $class)->getTable(), $column)) {
            continue;
        }
        if (! property_exists($class, 'searchable') || ! in_array($column, $class::$searchable)) {
            $missing[] = $class;
        }
    }

    expect($missing)->toBe([]);
})->with(['attributes', 'type']);

test('the logical server list can be searched by attributes', function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    LogicalServer::factory()->create(['name' => 'SRV-MATCH', 'attributes' => 'Production Critique']);
    LogicalServer::factory()->create(['name' => 'SRV-OTHER', 'attributes' => 'Recette']);

    $this->actingAs(User::query()->where('login', 'admin@admin.com')->first())
        ->get(route('admin.logical-servers.index', ['search' => 'critique']))
        ->assertOk()
        ->assertSee('SRV-MATCH')
        ->assertDontSee('SRV-OTHER');
});
