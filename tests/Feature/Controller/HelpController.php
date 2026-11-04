
<?php

use App\Models\User;
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

describe('help', function () {
    test('can display schema page', function () {
        $response = $this->get(route('admin.doc.schema'));
        $response->assertOk();
        $response->assertViewIs('doc.schema');
    });
    test('can display about page', function () {
        $response = $this->get(route('admin.doc.about'));
        $response->assertOk();
        $response->assertViewIs('doc.about');
    });
    test('can display guide page', function () {
        $response = $this->get(route('admin.doc.guide'));
        $response->assertOk();
        $response->assertViewIs('doc.guide');
    });
});
