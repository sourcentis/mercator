
<?php

use App\Models\PhysicalSwitch;
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
    test('can display physical-switches index page', function () {
        PhysicalSwitch::factory()->count(3)->create();

        $response = $this->get(route('admin.physical-switches.index'));

        $response->assertOk();
        $response->assertViewIs('admin.physicalSwitches.index');
        $response->assertViewHas('physicalSwitches');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.physical-switches.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.physical-switches.create'));

        $response->assertOk();
        $response->assertViewIs('admin.physicalSwitches.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.physical-switches.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $physicalSwitch = PhysicalSwitch::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.physical-switches.show', $physicalSwitch->id));

        $response->assertOk();
        $response->assertViewIs('admin.physicalSwitches.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $physicalSwitch = PhysicalSwitch::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.physical-switches.show', $physicalSwitch->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $physicalSwitch = PhysicalSwitch::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.physical-switches.edit', $physicalSwitch));

        $response->assertOk();
        $response->assertViewIs('admin.physicalSwitches.edit');
        $response->assertViewHas(['sites', 'buildings', 'bays']);
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $physicalSwitch = PhysicalSwitch::factory()->create();

        $response = $this->get(route('admin.physical-switches.edit', $physicalSwitch));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update PhysicalSwitch', function () {
        $name = fake()->word();
        $physicalSwitch = PhysicalSwitch::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.physical-switches.update', $physicalSwitch), $data);

        $response->assertRedirect(route('admin.physical-switches.index'));
        $this->assertDatabaseHas('physical_switches', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete PhysicalSwitch', function () {
        $physicalSwitch = PhysicalSwitch::factory()->create();

        $response = $this->delete(route('admin.physical-switches.destroy', $physicalSwitch->id));
        $response->assertRedirect(route('admin.physical-switches.index'));

        $this->assertSoftDeleted('physical_switches', ['id' => $physicalSwitch->id]);

        $physicalSwitch->refresh();
        expect($physicalSwitch->deleted_at)->not->toBeNull()
            ->and($physicalSwitch->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $physicalSwitch = PhysicalSwitch::factory()->create();

        $response = $this->delete(route('admin.physical-switches.destroy', $physicalSwitch));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple physical-switches', function () {
        $physicalSwitches = PhysicalSwitch::factory()->count(3)->create();
        $ids = $physicalSwitches->pluck('id')->toArray();

        $response = $this->delete(route('admin.physical-switches.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('physical_switches', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $physicalSwitch = PhysicalSwitch::factory()->create();

        $response = $this->delete(route('admin.physical-switches.massDestroy'), [
            'ids' => [$physicalSwitch->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $physicalSwitch = PhysicalSwitch::factory()->create();

        $response = $this->delete(route('admin.physical-switches.massDestroy'), [
            'ids' => [$physicalSwitch->id],
        ]);

        $response->assertForbidden();
    });

});
