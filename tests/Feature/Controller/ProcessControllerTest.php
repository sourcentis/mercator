
<?php

use App\Models\Process;
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
    test('can display processes index page', function () {
        Process::factory()->count(3)->create();

        $response = $this->get(route('admin.processes.index'));

        $response->assertOk();
        $response->assertViewIs('admin.processes.index');
        $response->assertViewHas('processes');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.processes.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.processes.create'));

        $response->assertOk();
        $response->assertViewIs('admin.processes.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.processes.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $rocess = Process::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.processes.show', $rocess->id));

        $response->assertOk();
        $response->assertViewIs('admin.processes.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $rocess = Process::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.processes.show', $rocess->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $rocess = Process::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.processes.edit', $rocess));

        $response->assertOk();
        $response->assertViewIs('admin.processes.edit');
        $response->assertViewHas('process');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $rocess = Process::factory()->create();

        $response = $this->get(route('admin.processes.edit', $rocess));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Process', function () {
        $name = fake()->word();
        $rocess = Process::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.processes.update', $rocess), $data);

        $response->assertRedirect(route('admin.processes.index'));
        $this->assertDatabaseHas('processes', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete Process', function () {
        $rocess = Process::factory()->create();

        $response = $this->delete(route('admin.processes.destroy', $rocess->id));
        $response->assertRedirect(route('admin.processes.index'));

        $this->assertSoftDeleted('processes', ['id' => $rocess->id]);

        $rocess->refresh();
        expect($rocess->deleted_at)->not->toBeNull()
            ->and($rocess->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $rocess = Process::factory()->create();

        $response = $this->delete(route('admin.processes.destroy', $rocess));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple processes', function () {
        $processes = Process::factory()->count(3)->create();
        $ids = $processes->pluck('id')->toArray();

        $response = $this->delete(route('admin.processes.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('processes', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $rocess = Process::factory()->create();

        $response = $this->delete(route('admin.processes.massDestroy'), [
            'ids' => [$rocess->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $rocess = Process::factory()->create();

        $response = $this->delete(route('admin.processes.massDestroy'), [
            'ids' => [$rocess->id],
        ]);

        $response->assertForbidden();
    });

});
