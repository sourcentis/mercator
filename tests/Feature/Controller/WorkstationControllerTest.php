
<?php

use App\Models\User;
use App\Models\Workstation;
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
    test('can display workstations index page', function () {
        Workstation::factory()->count(3)->create();

        $response = $this->get(route('admin.workstations.index'));

        $response->assertOk();
        $response->assertViewIs('admin.workstations.index');
        $response->assertViewHas('workstations');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.workstations.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.workstations.create'));

        $response->assertOk();
        $response->assertViewIs('admin.workstations.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.workstations.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $Workstation = Workstation::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.workstations.show', $Workstation->id));

        $response->assertOk();
        $response->assertViewIs('admin.workstations.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $Workstation = Workstation::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.workstations.show', $Workstation->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $Workstation = Workstation::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.workstations.edit', $Workstation));

        $response->assertOk();
        $response->assertViewIs('admin.workstations.edit');
        $response->assertViewHas('workstation');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Workstation = Workstation::factory()->create();

        $response = $this->get(route('admin.workstations.edit', $Workstation));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Workstation', function () {
        $name = fake()->word();
        $Workstation = Workstation::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.workstations.update', $Workstation), $data);

        $response->assertRedirect(route('admin.workstations.index'));
        $this->assertDatabaseHas('workstations', ['name' => 'Updated Name']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $workstation = Workstation::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.workstations.update', $workstation), $data);

        $response->assertForbidden();
    });
});

describe('destroy', function () {
    test('can delete Workstation', function () {
        $Workstation = Workstation::factory()->create();

        $response = $this->delete(route('admin.workstations.destroy', $Workstation->id));
        $response->assertRedirect(route('admin.workstations.index'));

        $this->assertSoftDeleted('workstations', ['id' => $Workstation->id]);

        $Workstation->refresh();
        expect($Workstation->deleted_at)->not->toBeNull()
            ->and($Workstation->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Workstation = Workstation::factory()->create();

        $response = $this->delete(route('admin.workstations.destroy', $Workstation));

        $response->assertForbidden();
    });

    test('returns no content status', function () {
        $Workstation = Workstation::factory()->create();

        $response = $this->delete(route('admin.workstations.massDestroy'), [
            'ids' => [$Workstation->id],
        ]);

        $response->assertStatus(204);
    });
});

describe('massDestroy', function () {
    test('can delete multiple workstations', function () {
        $workstations = Workstation::factory()->count(3)->create();
        $ids = $workstations->pluck('id')->toArray();

        $response = $this->delete(route('admin.workstations.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('workstations', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $Workstation = Workstation::factory()->create();

        $response = $this->delete(route('admin.workstations.massDestroy'), [
            'ids' => [$Workstation->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Workstation = Workstation::factory()->create();

        $response = $this->delete(route('admin.workstations.massDestroy'), [
            'ids' => [$Workstation->id],
        ]);

        $response->assertForbidden();
    });

});
