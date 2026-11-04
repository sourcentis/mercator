
<?php

use App\Models\Subnetwork;
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
    test('can display subnetworks index page', function () {
        Subnetwork::factory()->count(3)->create();

        $response = $this->get(route('admin.subnetworks.index'));

        $response->assertOk();
        $response->assertViewIs('admin.subnetworks.index');
        $response->assertViewHas('subnetworks');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.subnetworks.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.subnetworks.create'));

        $response->assertOk();
        $response->assertViewIs('admin.subnetworks.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.subnetworks.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $subnetwork = Subnetwork::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.subnetworks.show', $subnetwork->id));

        $response->assertOk();
        $response->assertViewIs('admin.subnetworks.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $subnetwork = Subnetwork::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.subnetworks.show', $subnetwork->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $subnetwork = Subnetwork::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.subnetworks.edit', $subnetwork));

        $response->assertOk();
        $response->assertViewIs('admin.subnetworks.edit');
        $response->assertViewHas('subnetwork');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $subnetwork = Subnetwork::factory()->create();

        $response = $this->get(route('admin.subnetworks.edit', $subnetwork));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Subnetwork', function () {
        $name = fake()->word();
        $subnetwork = Subnetwork::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.subnetworks.update', $subnetwork), $data);

        $response->assertRedirect(route('admin.subnetworks.index'));
        $this->assertDatabaseHas('subnetworks', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete Subnetwork', function () {
        $subnetwork = Subnetwork::factory()->create();

        $response = $this->delete(route('admin.subnetworks.destroy', $subnetwork->id));
        $response->assertRedirect(route('admin.subnetworks.index'));

        $this->assertSoftDeleted('subnetworks', ['id' => $subnetwork->id]);

        $subnetwork->refresh();
        expect($subnetwork->deleted_at)->not->toBeNull()
            ->and($subnetwork->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $subnetwork = Subnetwork::factory()->create();

        $response = $this->delete(route('admin.subnetworks.destroy', $subnetwork));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple subnetworks', function () {
        $subnetworks = Subnetwork::factory()->count(3)->create();
        $ids = $subnetworks->pluck('id')->toArray();

        $response = $this->delete(route('admin.subnetworks.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('subnetworks', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $subnetwork = Subnetwork::factory()->create();

        $response = $this->delete(route('admin.subnetworks.massDestroy'), [
            'ids' => [$subnetwork->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $subnetwork = Subnetwork::factory()->create();

        $response = $this->delete(route('admin.subnetworks.massDestroy'), [
            'ids' => [$subnetwork->id],
        ]);

        $response->assertForbidden();
    });

});
