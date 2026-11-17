<?php

use App\Models\ApplicationFlow;
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
    test('can display fluxes index page', function () {
        ApplicationFlow::factory()->count(3)->create();

        $response = $this->get(route('admin.application-flows.index'));

        $response->assertOk();
        $response->assertViewIs('admin.application-flows.index');
        $response->assertViewHas('flows');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.application-flows.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.application-flows.create'));

        $response->assertOk();
        $response->assertViewIs('admin.application-flows.create');
        $response->assertViewHas(['items', 'type_list', 'attributes_list']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.application-flows.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $flow = ApplicationFlow::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.application-flows.show', $flow->id));

        $response->assertOk();
        $response->assertViewIs('admin.application-flows.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $flow = ApplicationFlow::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.application-flows.show', $flow->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $flow = ApplicationFlow::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.application-flows.edit', $flow));

        $response->assertOk();
        $response->assertViewIs('admin.application-flows.edit');
        $response->assertViewHas(['items', 'type_list', 'attributes_list', 'flow']);
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $flow = ApplicationFlow::factory()->create();

        $response = $this->get(route('admin.application-flows.edit', $flow));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update flux', function () {
        $name = fake()->word();
        $flow = ApplicationFlow::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.application-flows.update', $flow), $data);

        $response->assertRedirect(route('admin.application-flows.index'));
        $this->assertDatabaseHas('application_flows', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete flux', function () {
        $flow = ApplicationFlow::factory()->create();

        $response = $this->delete(route('admin.application-flows.destroy', $flow->id));
        $response->assertRedirect(route('admin.application-flows.index'));

        $this->assertSoftDeleted('application_flows', ['id' => $flow->id]);

        $flow->refresh();
        expect($flow->deleted_at)->not->toBeNull()
            ->and($flow->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $flow = ApplicationFlow::factory()->create();

        $response = $this->delete(route('admin.application-flows.destroy', $flow));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple fluxes', function () {
        $flows = ApplicationFlow::factory()->count(3)->create();
        $ids = $flows->pluck('id')->toArray();

        $response = $this->delete(route('admin.application-flows.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('application_flows', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $flow = ApplicationFlow::factory()->create();

        $response = $this->delete(route('admin.application-flows.massDestroy'), [
            'ids' => [$flow->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $flow = ApplicationFlow::factory()->create();

        $response = $this->delete(route('admin.application-flows.massDestroy'), [
            'ids' => [$flow->id],
        ]);

        $response->assertForbidden();
    });

});
