
<?php

use App\Models\PhysicalRouter;
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
    test('can display physical-routers index page', function () {
        PhysicalRouter::factory()->count(3)->create();

        $response = $this->get(route('admin.physical-routers.index'));

        $response->assertOk();
        $response->assertViewIs('admin.physicalRouters.index');
        $response->assertViewHas('physicalRouters');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.physical-routers.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.physical-routers.create'));

        $response->assertOk();
        $response->assertViewIs('admin.physicalRouters.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.physical-routers.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $physicalRouter = PhysicalRouter::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.physical-routers.show', $physicalRouter->id));

        $response->assertOk();
        $response->assertViewIs('admin.physicalRouters.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $physicalRouter = PhysicalRouter::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.physical-routers.show', $physicalRouter->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $physicalRouter = PhysicalRouter::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.physical-routers.edit', $physicalRouter));

        $response->assertOk();
        $response->assertViewIs('admin.physicalRouters.edit');
        $response->assertViewHas(['sites', 'buildings', 'type_list']);
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $physicalRouter = PhysicalRouter::factory()->create();

        $response = $this->get(route('admin.physical-routers.edit', $physicalRouter));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update PhysicalRouter', function () {
        $name = fake()->word();
        $physicalRouter = PhysicalRouter::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.physical-routers.update', $physicalRouter), $data);

        $response->assertRedirect(route('admin.physical-routers.index'));
        $this->assertDatabaseHas('physical_routers', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete PhysicalRouter', function () {
        $physicalRouter = PhysicalRouter::factory()->create();

        $response = $this->delete(route('admin.physical-routers.destroy', $physicalRouter->id));
        $response->assertRedirect(route('admin.physical-routers.index'));

        $this->assertSoftDeleted('physical_routers', ['id' => $physicalRouter->id]);

        $physicalRouter->refresh();
        expect($physicalRouter->deleted_at)->not->toBeNull()
            ->and($physicalRouter->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $physicalRouter = PhysicalRouter::factory()->create();

        $response = $this->delete(route('admin.physical-routers.destroy', $physicalRouter));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple physical-routers', function () {
        $physicalRouters = PhysicalRouter::factory()->count(3)->create();
        $ids = $physicalRouters->pluck('id')->toArray();

        $response = $this->delete(route('admin.physical-routers.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('physical_routers', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $physicalRouter = PhysicalRouter::factory()->create();

        $response = $this->delete(route('admin.physical-routers.massDestroy'), [
            'ids' => [$physicalRouter->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $physicalRouter = PhysicalRouter::factory()->create();

        $response = $this->delete(route('admin.physical-routers.massDestroy'), [
            'ids' => [$physicalRouter->id],
        ]);

        $response->assertForbidden();
    });

});
