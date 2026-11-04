<?php

use App\Models\Application;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Auth\SessionGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Passport;

/*
 * Régression #2754 : GET /api/<objets> renvoyait une liste vide à un non-administrateur
 * dont la session contenait les permissions cartographe (posées par l'événement Login de
 * /api/login) mais pas les permissions de rôle.
 */

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    Application::factory()->count(3)->create();

    $this->reader = User::factory()->create(['login' => 'svc', 'password' => bcrypt('secret-pass')]);
    $role = Role::factory()->create();
    $role->permissions()->attach(Permission::query()
        ->whereIn('title', ['application_access', 'application_show'])->pluck('id'));
    $this->reader->roles()->attach($role->id);
});

it('lists applications for a non-admin holding application_access', function () {
    Passport::actingAs($this->reader);

    expect($this->getJson('/api/applications')->assertOk()->json())->toHaveCount(3);
});

it('lists applications when the session only holds cartographer permissions', function () {
    Passport::actingAs($this->reader);

    $this->withSession([
        'cartographer_permissions' => [],
        'cartographer_permissions_at' => now()->timestamp,
    ]);

    expect($this->getJson('/api/applications')->assertOk()->json())->toHaveCount(3);
});

it('does not open a web session when logging in through the API', function () {
    Artisan::call('passport:client', ['--personal' => true, '--name' => 'pat', '--provider' => 'users', '--no-interaction' => true]);

    $this->postJson('/api/login', ['login' => 'svc', 'password' => 'secret-pass'])
        ->assertOk()
        ->assertJsonStructure(['access_token']);

    expect(session()->has('cartographer_permissions_at'))->toBeFalse()
        ->and(session()->has('login_web_'.sha1(SessionGuard::class)))->toBeFalse();
});

it('rejects API login with a wrong password', function () {
    $this->postJson('/api/login', ['login' => 'svc', 'password' => 'wrong'])->assertStatus(400);
});
