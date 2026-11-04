
<?php

use App\Models\Task;
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
    test('can display tasks index page', function () {
        Task::factory()->count(3)->create();

        $response = $this->get(route('admin.tasks.index'));

        $response->assertOk();
        $response->assertViewIs('admin.tasks.index');
        $response->assertViewHas('tasks');
        $response->assertSee(trans('cruds.task.fields.type'));
        $response->assertSee(trans('cruds.task.fields.attributes'));
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.tasks.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.tasks.create'));

        $response->assertOk();
        $response->assertViewIs('admin.tasks.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.tasks.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $Task = Task::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.tasks.show', $Task->id));

        $response->assertOk();
        $response->assertViewIs('admin.tasks.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $Task = Task::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.tasks.show', $Task->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $Task = Task::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.tasks.edit', $Task));

        $response->assertOk();
        $response->assertViewIs('admin.tasks.edit');
        $response->assertViewHas('task');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Task = Task::factory()->create();

        $response = $this->get(route('admin.tasks.edit', $Task));

        $response->assertForbidden();
    });
});

describe('store', function () {
    test('can create a Task with type and attributes', function () {
        $data = [
            'name' => 'Reconcile ledgers',
            'type' => 'Comptabilité',
            'attributes' => ['urgent', 'mensuel'],
        ];

        $response = $this->post(route('admin.tasks.store'), $data);

        $response->assertRedirect(route('admin.tasks.index'));
        $this->assertDatabaseHas('tasks', [
            'name' => 'Reconcile ledgers',
            'type' => 'Comptabilité',
            'attributes' => 'urgent mensuel',
        ]);
    });
});

describe('update', function () {
    test('can update Task', function () {
        $name = fake()->word();
        $Task = Task::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.tasks.update', $Task), $data);

        $response->assertRedirect(route('admin.tasks.index'));
        $this->assertDatabaseHas('tasks', ['name' => 'Updated Name']);
    });

    test('can update Task type and attributes', function () {
        $Task = Task::factory()->create(['type' => 'Old', 'attributes' => 'old tag']);

        $data = [
            'name' => $Task->name,
            'type' => 'Nouveau',
            'attributes' => ['nouveau', 'tag'],
        ];

        $response = $this->put(route('admin.tasks.update', $Task), $data);

        $response->assertRedirect(route('admin.tasks.index'));
        $this->assertDatabaseHas('tasks', [
            'id' => $Task->id,
            'type' => 'Nouveau',
            'attributes' => 'nouveau tag',
        ]);
    });
});

describe('destroy', function () {
    test('can delete Task', function () {
        $Task = Task::factory()->create();

        $response = $this->delete(route('admin.tasks.destroy', $Task->id));
        $response->assertRedirect(route('admin.tasks.index'));

        $this->assertSoftDeleted('tasks', ['id' => $Task->id]);

        $Task->refresh();
        expect($Task->deleted_at)->not->toBeNull()
            ->and($Task->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Task = Task::factory()->create();

        $response = $this->delete(route('admin.tasks.destroy', $Task));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple tasks', function () {
        $tasks = Task::factory()->count(3)->create();
        $ids = $tasks->pluck('id')->toArray();

        $response = $this->delete(route('admin.tasks.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('tasks', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $Task = Task::factory()->create();

        $response = $this->delete(route('admin.tasks.massDestroy'), [
            'ids' => [$Task->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Task = Task::factory()->create();

        $response = $this->delete(route('admin.tasks.massDestroy'), [
            'ids' => [$Task->id],
        ]);

        $response->assertForbidden();
    });

});
