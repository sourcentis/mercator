<?php

use App\Models\Application;
use App\Models\DataProcessing;
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

describe('edit', function () {
    test('lists all applications, not only the first page (#2692)', function () {
        Application::factory()->count(30)->create();
        $dataProcessing = DataProcessing::factory()->create();

        $response = $this->get(route('admin.data-processings.edit', $dataProcessing));

        $response->assertOk();
        $response->assertViewIs('admin.dataProcessing.edit');
        expect($response->viewData('applications'))->toHaveCount(Application::count());
    });

    test('keeps linked applications beyond the 25th selected', function () {
        Application::factory()->count(30)->create();
        $last = Application::query()->orderBy('name', 'desc')->first();
        $dataProcessing = DataProcessing::factory()->create();
        $dataProcessing->applications()->attach($last->id);

        $response = $this->get(route('admin.data-processings.edit', $dataProcessing));

        $response->assertOk();
        $response->assertSee('<option value="'.$last->id.'" selected', false);
    });
});
