
<?php

use App\Models\Phone;
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
    test('can display phones index page', function () {
        Phone::factory()->count(3)->create();

        $response = $this->get(route('admin.phones.index'));

        $response->assertOk();
        $response->assertViewIs('admin.phones.index');
        $response->assertViewHas('phones');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.phones.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.phones.create'));

        $response->assertOk();
        $response->assertViewIs('admin.phones.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.phones.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $Phone = Phone::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.phones.show', $Phone->id));

        $response->assertOk();
        $response->assertViewIs('admin.phones.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $Phone = Phone::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.phones.show', $Phone->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $Phone = Phone::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.phones.edit', $Phone));

        $response->assertOk();
        $response->assertViewIs('admin.phones.edit');
        $response->assertViewHas(['sites', 'buildings', 'type_list']);
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Phone = Phone::factory()->create();

        $response = $this->get(route('admin.phones.edit', $Phone));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Phone', function () {
        $name = fake()->word();
        $Phone = Phone::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.phones.update', $Phone), $data);

        $response->assertRedirect(route('admin.phones.index'));
        $this->assertDatabaseHas('phones', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete Phone', function () {
        $Phone = Phone::factory()->create();

        $response = $this->delete(route('admin.phones.destroy', $Phone->id));
        $response->assertRedirect(route('admin.phones.index'));

        $this->assertSoftDeleted('phones', ['id' => $Phone->id]);

        $Phone->refresh();
        expect($Phone->deleted_at)->not->toBeNull()
            ->and($Phone->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Phone = Phone::factory()->create();

        $response = $this->delete(route('admin.phones.destroy', $Phone));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple phones', function () {
        $phones = Phone::factory()->count(3)->create();
        $ids = $phones->pluck('id')->toArray();

        $response = $this->delete(route('admin.phones.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('phones', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $Phone = Phone::factory()->create();

        $response = $this->delete(route('admin.phones.massDestroy'), [
            'ids' => [$Phone->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Phone = Phone::factory()->create();

        $response = $this->delete(route('admin.phones.massDestroy'), [
            'ids' => [$Phone->id],
        ]);

        $response->assertForbidden();
    });

});
