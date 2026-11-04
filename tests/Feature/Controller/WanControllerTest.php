
<?php

use App\Models\User;
use App\Models\Wan;
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
    test('can display wans index page', function () {
        Wan::factory()->count(3)->create();

        $response = $this->get(route('admin.wans.index'));

        $response->assertOk();
        $response->assertViewIs('admin.wans.index');
        $response->assertViewHas('wans');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.wans.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.wans.create'));

        $response->assertOk();
        $response->assertViewIs('admin.wans.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.wans.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $Wan = Wan::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.wans.show', $Wan->id));

        $response->assertOk();
        $response->assertViewIs('admin.wans.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $Wan = Wan::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.wans.show', $Wan->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $Wan = Wan::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.wans.edit', $Wan));

        $response->assertOk();
        $response->assertViewIs('admin.wans.edit');
        $response->assertViewHas('wan');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Wan = Wan::factory()->create();

        $response = $this->get(route('admin.wans.edit', $Wan));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Wan', function () {
        $name = fake()->word();
        $Wan = Wan::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.wans.update', $Wan), $data);

        $response->assertRedirect(route('admin.wans.index'));
        $this->assertDatabaseHas('wans', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete Wan', function () {
        $Wan = Wan::factory()->create();

        $response = $this->delete(route('admin.wans.destroy', $Wan->id));
        $response->assertRedirect(route('admin.wans.index'));

        $this->assertSoftDeleted('wans', ['id' => $Wan->id]);

        $Wan->refresh();
        expect($Wan->deleted_at)->not->toBeNull()
            ->and($Wan->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Wan = Wan::factory()->create();

        $response = $this->delete(route('admin.wans.destroy', $Wan));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple wans', function () {
        $wans = Wan::factory()->count(3)->create();
        $ids = $wans->pluck('id')->toArray();

        $response = $this->delete(route('admin.wans.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('wans', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $Wan = Wan::factory()->create();

        $response = $this->delete(route('admin.wans.massDestroy'), [
            'ids' => [$Wan->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $Wan = Wan::factory()->create();

        $response = $this->delete(route('admin.wans.massDestroy'), [
            'ids' => [$Wan->id],
        ]);

        $response->assertForbidden();
    });

});
