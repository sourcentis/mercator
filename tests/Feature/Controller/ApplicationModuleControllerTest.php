<?php

use App\Models\ApplicationModule;
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

describe('index', function () {
    test('can display application-modules index page', function () {
        ApplicationModule::factory()->count(3)->create();

        $response = $this->get(route('admin.application-modules.index'));

        $response->assertOk();
        $response->assertViewIs('admin.applicationModules.index');
        $response->assertViewHas('applicationModules');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.application-modules.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.application-modules.create'));

        $response->assertOk();
        $response->assertViewIs('admin.applicationModules.create');
        // $response->assertViewHas();
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.application-modules.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $applicationModule = ApplicationModule::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.application-modules.show', $applicationModule->id));

        $response->assertOk();
        $response->assertViewIs('admin.applicationModules.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $applicationModule = ApplicationModule::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.application-modules.show', $applicationModule->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $applicationModule = ApplicationModule::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.application-modules.edit', $applicationModule));

        $response->assertOk();
        $response->assertViewIs('admin.applicationModules.edit');
        $response->assertViewHas('applicationModule');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $applicationModule = ApplicationModule::factory()->create();

        $response = $this->get(route('admin.application-modules.edit', $applicationModule));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $applicationModule = ApplicationModule::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.application-modules.update', $applicationModule), $data);

        $response->assertRedirect(route('admin.application-modules.index'));
        $this->assertDatabaseHas('application_modules', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $applicationModule = ApplicationModule::factory()->create();

        $response = $this->delete(route('admin.application-modules.destroy', $applicationModule->id));
        $response->assertRedirect(route('admin.application-modules.index'));

        $this->assertSoftDeleted('application_modules', ['id' => $applicationModule->id]);

        $applicationModule->refresh();
        expect($applicationModule->deleted_at)->not->toBeNull()
            ->and($applicationModule->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $applicationModule = ApplicationModule::factory()->create();

        $response = $this->delete(route('admin.application-modules.destroy', $applicationModule));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple application-modules', function () {
        $applicationModules = ApplicationModule::factory()->count(3)->create();
        $ids = $applicationModules->pluck('id')->toArray();

        $response = $this->delete(route('admin.application-modules.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('application_modules', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $applicationModule = ApplicationModule::factory()->create();

        $response = $this->delete(route('admin.application-modules.massDestroy'), [
            'ids' => [$applicationModule->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $applicationModule = ApplicationModule::factory()->create();

        $response = $this->delete(route('admin.application-modules.massDestroy'), [
            'ids' => [$applicationModule->id],
        ]);

        $response->assertForbidden();
    });

});
