<?php

use App\Models\ApplicationBlock;
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
    test('can display activities index page', function () {
        ApplicationBlock::factory()->count(3)->create();

        $response = $this->get(route('admin.application-blocks.index'));

        $response->assertOk();
        $response->assertViewIs('admin.applicationBlocks.index');
        $response->assertViewHas('applicationBlocks');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.application-blocks.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.application-blocks.create'));

        $response->assertOk();
        $response->assertViewIs('admin.applicationBlocks.create');
        $response->assertViewHas(['applications']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.application-blocks.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $activity = ApplicationBlock::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.application-blocks.show', $activity->id));

        $response->assertOk();
        $response->assertViewIs('admin.applicationBlocks.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $activity = ApplicationBlock::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.application-blocks.show', $activity->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $activity = ApplicationBlock::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.application-blocks.edit', $activity));

        $response->assertOk();
        $response->assertViewIs('admin.applicationBlocks.edit');
        $response->assertViewHas('applications');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $activity = ApplicationBlock::factory()->create();

        $response = $this->get(route('admin.application-blocks.edit', $activity));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $activity = ApplicationBlock::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.application-blocks.update', $activity), $data);

        $response->assertRedirect(route('admin.application-blocks.index'));
        $this->assertDatabaseHas('application_blocks', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $activity = ApplicationBlock::factory()->create();

        $response = $this->delete(route('admin.application-blocks.destroy', $activity->id));
        $response->assertRedirect(route('admin.application-blocks.index'));

        $this->assertSoftDeleted('application_blocks', ['id' => $activity->id]);

        $activity->refresh();
        expect($activity->deleted_at)->not->toBeNull()
            ->and($activity->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $activity = ApplicationBlock::factory()->create();

        $response = $this->delete(route('admin.application-blocks.destroy', $activity));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple activities', function () {
        $activities = ApplicationBlock::factory()->count(3)->create();
        $ids = $activities->pluck('id')->toArray();

        $response = $this->delete(route('admin.application-blocks.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('application_blocks', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $activity = ApplicationBlock::factory()->create();

        $response = $this->delete(route('admin.application-blocks.massDestroy'), [
            'ids' => [$activity->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $activity = ApplicationBlock::factory()->create();

        $response = $this->delete(route('admin.application-blocks.massDestroy'), [
            'ids' => [$activity->id],
        ]);

        $response->assertForbidden();
    });

});
