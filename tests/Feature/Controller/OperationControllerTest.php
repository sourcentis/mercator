
<?php

use App\Models\Operation;
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
    test('can display operations index page', function () {
        Operation::factory()->count(3)->create();

        $response = $this->get(route('admin.operations.index'));

        $response->assertOk();
        $response->assertViewIs('admin.operations.index');
        $response->assertViewHas('operations');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.operations.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.operations.create'));

        $response->assertOk();
        $response->assertViewIs('admin.operations.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.operations.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $operation = Operation::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.operations.show', $operation->id));

        $response->assertOk();
        $response->assertViewIs('admin.operations.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $operation = Operation::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.operations.show', $operation->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $operation = Operation::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.operations.edit', $operation));

        $response->assertOk();
        $response->assertViewIs('admin.operations.edit');
        $response->assertViewHas(['actors', 'tasks', 'activities']);
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $operation = Operation::factory()->create();

        $response = $this->get(route('admin.operations.edit', $operation));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Operation', function () {
        $name = fake()->word();
        $operation = Operation::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.operations.update', $operation), $data);

        $response->assertRedirect(route('admin.operations.index'));
        $this->assertDatabaseHas('operations', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete Operation', function () {
        $operation = Operation::factory()->create();

        $response = $this->delete(route('admin.operations.destroy', $operation->id));
        $response->assertRedirect(route('admin.operations.index'));

        $this->assertSoftDeleted('operations', ['id' => $operation->id]);

        $operation->refresh();
        expect($operation->deleted_at)->not->toBeNull()
            ->and($operation->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $operation = Operation::factory()->create();

        $response = $this->delete(route('admin.operations.destroy', $operation));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple operations', function () {
        $operations = Operation::factory()->count(3)->create();
        $ids = $operations->pluck('id')->toArray();

        $response = $this->delete(route('admin.operations.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('operations', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $operation = Operation::factory()->create();

        $response = $this->delete(route('admin.operations.massDestroy'), [
            'ids' => [$operation->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $operation = Operation::factory()->create();

        $response = $this->delete(route('admin.operations.massDestroy'), [
            'ids' => [$operation->id],
        ]);

        $response->assertForbidden();
    });

});
