
<?php

use App\Models\Activity;
use App\Models\AuditLog;
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
        Activity::factory()->count(3)->create();

        $response = $this->get(route('admin.activities.index'));

        $response->assertOk();
        $response->assertViewIs('admin.activities.index');
        $response->assertViewHas('activities');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.activities.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.activities.create'));

        $response->assertOk();
        $response->assertViewIs('admin.activities.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.activities.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $Activity = Activity::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.activities.show', $Activity->id));

        $response->assertOk();
        $response->assertViewIs('admin.activities.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $Activity = Activity::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.activities.show', $Activity->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $Activity = Activity::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.activities.edit', $Activity));

        $response->assertOk();
        $response->assertViewIs('admin.activities.edit');
        $response->assertViewHas('activity');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Activity = Activity::factory()->create();

        $response = $this->get(route('admin.activities.edit', $Activity));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Activity', function () {
        $name = fake()->word();
        $Activity = Activity::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.activities.update', $Activity), $data);

        $response->assertRedirect(route('admin.activities.index'));
        $this->assertDatabaseHas('activities', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete Activity', function () {
        $Activity = Activity::factory()->create();

        $response = $this->delete(route('admin.activities.destroy', $Activity->id));
        $response->assertRedirect(route('admin.activities.index'));

        $this->assertSoftDeleted('activities', ['id' => $Activity->id]);

        $Activity->refresh();
        expect($Activity->deleted_at)->not->toBeNull()
            ->and($Activity->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Activity = Activity::factory()->create();

        $response = $this->delete(route('admin.activities.destroy', $Activity));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple activities', function () {
        $activities = Activity::factory()->count(3)->create();
        $ids = $activities->pluck('id')->toArray();

        $response = $this->delete(route('admin.activities.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('activities', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $Activity = Activity::factory()->create();

        $response = $this->delete(route('admin.activities.massDestroy'), [
            'ids' => [$Activity->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Activity = Activity::factory()->create();

        $response = $this->delete(route('admin.activities.massDestroy'), [
            'ids' => [$Activity->id],
        ]);

        $response->assertForbidden();
    });

    test('creates an audit log entry for each deleted activity', function () {
        $activities = Activity::factory()->count(3)->create();
        $ids = $activities->pluck('id')->toArray();

        $this->delete(route('admin.activities.massDestroy'), ['ids' => $ids]);

        foreach ($ids as $id) {
            expect(AuditLog::query()
                ->where('subject_type', Activity::class)
                ->where('subject_id', $id)
                ->where('description', 'deleted')
                ->exists()
            )->toBeTrue();
        }
    });

});
