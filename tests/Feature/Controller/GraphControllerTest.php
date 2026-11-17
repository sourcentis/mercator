<?php

use App\Models\Graph;
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
    test('can display graphs index page', function () {
        Graph::factory()->count(3)->create();

        $response = $this->get(route('admin.graphs.index'));

        $response->assertOk();
        $response->assertViewIs('admin.graphs.index');
        $response->assertViewHas('graphs');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.graphs.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.graphs.create'));

        $response->assertOk();
        $response->assertViewIs('admin.graphs.edit');
        $response->assertViewHas(['type_list', 'nodes', 'edges']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.graphs.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $graph = Graph::factory()->create(['name' => $name, 'class' => 1]);

        $response = $this->get(route('admin.graphs.show', $graph->id));

        $response->assertOk();
        $response->assertViewIs('admin.graphs.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $graph = Graph::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.graphs.show', $graph->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $graph = Graph::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.graphs.edit', $graph));

        $response->assertOk();
        $response->assertViewIs('admin.graphs.edit');
        $response->assertViewHas(['type_list', 'nodes', 'edges']);
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $graph = Graph::factory()->create();

        $response = $this->get(route('admin.graphs.edit', $graph));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update graph', function () {

        $name = fake()->word();
        $graph = Graph::factory()->create(['name' => $name, 'type' => fake()->word()]);

        $data = [
            'id' => $graph->id,
            'name' => 'Updated Name',
            'type' => $graph->type,
            'content' => '<GraphDataModel></GraphDataModel>',
        ];

        $response = $this->put(route('admin.graphs.update', $graph), $data);

        $response->assertRedirect(route('admin.graphs.index'));
        $this->assertDatabaseHas('graphs', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete graph', function () {
        $graph = Graph::factory()->create();

        $response = $this->delete(route('admin.graphs.destroy', $graph->id));
        $response->assertRedirect(route('admin.graphs.index'));

        $this->assertSoftDeleted('graphs', ['id' => $graph->id]);

        $graph->refresh();
        expect($graph->deleted_at)->not->toBeNull()
            ->and($graph->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $graph = Graph::factory()->create();

        $response = $this->delete(route('admin.graphs.destroy', $graph));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple graphs', function () {
        $graphs = Graph::factory()->count(3)->create();
        $ids = $graphs->pluck('id')->toArray();

        $response = $this->delete(route('admin.graphs.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('graphs', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $graph = Graph::factory()->create();

        $response = $this->delete(route('admin.graphs.massDestroy'), [
            'ids' => [$graph->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $graph = Graph::factory()->create();

        $response = $this->delete(route('admin.graphs.massDestroy'), [
            'ids' => [$graph->id],
        ]);

        $response->assertForbidden();
    });

});
