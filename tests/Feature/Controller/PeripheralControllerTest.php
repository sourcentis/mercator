
<?php

use App\Models\Peripheral;
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
    test('can display peripherals index page', function () {
        Peripheral::factory()->count(3)->create();

        $response = $this->get(route('admin.peripherals.index'));

        $response->assertOk();
        $response->assertViewIs('admin.peripherals.index');
        $response->assertViewHas('peripherals');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.peripherals.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.peripherals.create'));

        $response->assertOk();
        $response->assertViewIs('admin.peripherals.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.peripherals.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $Peripheral = Peripheral::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.peripherals.show', $Peripheral->id));

        $response->assertOk();
        $response->assertViewIs('admin.peripherals.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $Peripheral = Peripheral::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.peripherals.show', $Peripheral->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $Peripheral = Peripheral::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.peripherals.edit', $Peripheral));

        $response->assertOk();
        $response->assertViewIs('admin.peripherals.edit');
        $response->assertViewHas(['sites', 'buildings', 'bays']);
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Peripheral = Peripheral::factory()->create();

        $response = $this->get(route('admin.peripherals.edit', $Peripheral));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Peripheral', function () {
        $name = fake()->word();
        $Peripheral = Peripheral::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.peripherals.update', $Peripheral), $data);

        $response->assertRedirect(route('admin.peripherals.index'));
        $this->assertDatabaseHas('peripherals', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete Peripheral', function () {
        $Peripheral = Peripheral::factory()->create();

        $response = $this->delete(route('admin.peripherals.destroy', $Peripheral->id));
        $response->assertRedirect(route('admin.peripherals.index'));

        $this->assertSoftDeleted('peripherals', ['id' => $Peripheral->id]);

        $Peripheral->refresh();
        expect($Peripheral->deleted_at)->not->toBeNull()
            ->and($Peripheral->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Peripheral = Peripheral::factory()->create();

        $response = $this->delete(route('admin.peripherals.destroy', $Peripheral));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple peripherals', function () {
        $peripherals = Peripheral::factory()->count(3)->create();
        $ids = $peripherals->pluck('id')->toArray();

        $response = $this->delete(route('admin.peripherals.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('peripherals', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $Peripheral = Peripheral::factory()->create();

        $response = $this->delete(route('admin.peripherals.massDestroy'), [
            'ids' => [$Peripheral->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Peripheral = Peripheral::factory()->create();

        $response = $this->delete(route('admin.peripherals.massDestroy'), [
            'ids' => [$Peripheral->id],
        ]);

        $response->assertForbidden();
    });

});
