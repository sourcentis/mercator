
<?php

use App\Models\SecurityControl;
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
    test('can display security controls index page', function () {
        SecurityControl::factory()->count(3)->create();

        $response = $this->get(route('admin.security-controls.index'));

        $response->assertOk();
        $response->assertViewIs('admin.securityControls.index');
        $response->assertViewHas('controls');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.security-controls.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.security-controls.create'));

        $response->assertOk();
        $response->assertViewIs('admin.securityControls.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.security-controls.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $securityControl = SecurityControl::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.security-controls.show', $securityControl->id));

        $response->assertOk();
        $response->assertViewIs('admin.securityControls.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $securityControl = SecurityControl::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.security-controls.show', $securityControl->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $securityControl = SecurityControl::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.security-controls.edit', $securityControl));

        $response->assertOk();
        $response->assertViewIs('admin.securityControls.edit');
        $response->assertViewHas('securityControl');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $securityControl = SecurityControl::factory()->create();

        $response = $this->get(route('admin.security-controls.edit', $securityControl));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update SecurityControl', function () {
        $name = fake()->word();
        $securityControl = SecurityControl::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentences(3, true),
        ];

        $response = $this->put(route('admin.security-controls.update', $securityControl), $data);

        $response->assertRedirect(route('admin.security-controls.index'));
        $this->assertDatabaseHas('security_controls', ['name' => 'Updated Name']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $securityControl = SecurityControl::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentences(3, true),
        ];

        $response = $this->put(route('admin.security-controls.update', $securityControl), $data);

        $response->assertForbidden();
    });
});

describe('destroy', function () {
    test('can delete SecurityControl', function () {
        $securityControl = SecurityControl::factory()->create();

        $response = $this->delete(route('admin.security-controls.destroy', $securityControl->id));
        $response->assertRedirect(route('admin.security-controls.index'));

        $this->assertSoftDeleted('security_controls', ['id' => $securityControl->id]);

        $securityControl->refresh();
        expect($securityControl->deleted_at)->not->toBeNull()
            ->and($securityControl->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $securityControl = SecurityControl::factory()->create();

        $response = $this->delete(route('admin.security-controls.destroy', $securityControl));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple security controls', function () {
        $securityControl = SecurityControl::factory()->count(3)->create();
        $ids = $securityControl->pluck('id')->toArray();

        $response = $this->delete(route('admin.security-controls.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('security_controls', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $securityControl = SecurityControl::factory()->create();

        $response = $this->delete(route('admin.security-controls.massDestroy'), [
            'ids' => [$securityControl->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $securityControl = SecurityControl::factory()->create();

        $response = $this->delete(route('admin.security-controls.massDestroy'), [
            'ids' => [$securityControl->id],
        ]);

        $response->assertForbidden();
    });

});
