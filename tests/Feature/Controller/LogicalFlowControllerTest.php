
<?php

use App\Models\LogicalFlow;
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
        LogicalFlow::factory()->count(3)->create();

        $response = $this->get(route('admin.logical-flows.index'));

        $response->assertOk();
        $response->assertViewIs('admin.logicalFlows.index');
        $response->assertViewHas('logicalFlows');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.logical-flows.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.logical-flows.create'));

        $response->assertOk();
        $response->assertViewIs('admin.logicalFlows.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.logical-flows.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $logicalFlow = LogicalFlow::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.logical-flows.show', $logicalFlow->id));

        $response->assertOk();
        $response->assertViewIs('admin.logicalFlows.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $logicalFlow = LogicalFlow::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.logical-flows.show', $logicalFlow->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $logicalFlow = LogicalFlow::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.logical-flows.edit', $logicalFlow));

        $response->assertOk();
        $response->assertViewIs('admin.logicalFlows.edit');
        $response->assertViewHas('logicalFlow');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $logicalFlow = LogicalFlow::factory()->create();

        $response = $this->get(route('admin.logical-flows.edit', $logicalFlow));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update LogicalFlow', function () {
        $name = fake()->word();
        $logicalFlow = LogicalFlow::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentences(3, true),
            'source_ip_range' => fake()->ipv4().'/32',
            'dest_ip_range' => fake()->ipv4().'/32',
        ];

        $response = $this->put(route('admin.logical-flows.update', $logicalFlow), $data);

        $response->assertRedirect(route('admin.logical-flows.index'));
        $this->assertDatabaseHas('logical_flows', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete LogicalFlow', function () {
        $logicalFlow = LogicalFlow::factory()->create();

        $response = $this->delete(route('admin.logical-flows.destroy', $logicalFlow->id));
        $response->assertRedirect(route('admin.logical-flows.index'));

        $this->assertSoftDeleted('logical_flows', ['id' => $logicalFlow->id]);

        $logicalFlow->refresh();
        expect($logicalFlow->deleted_at)->not->toBeNull()
            ->and($logicalFlow->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $logicalFlow = LogicalFlow::factory()->create();

        $response = $this->delete(route('admin.logical-flows.destroy', $logicalFlow));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple activities', function () {
        $activities = LogicalFlow::factory()->count(3)->create();
        $ids = $activities->pluck('id')->toArray();

        $response = $this->delete(route('admin.logical-flows.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('logical_flows', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $logicalFlow = LogicalFlow::factory()->create();

        $response = $this->delete(route('admin.logical-flows.massDestroy'), [
            'ids' => [$logicalFlow->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $logicalFlow = LogicalFlow::factory()->create();

        $response = $this->delete(route('admin.logical-flows.massDestroy'), [
            'ids' => [$logicalFlow->id],
        ]);

        $response->assertForbidden();
    });

});
