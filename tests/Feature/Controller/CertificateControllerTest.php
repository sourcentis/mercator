<?php

use App\Models\Certificate;
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
    test('can display certificates index page', function () {
        Certificate::factory()->count(3)->create();

        $response = $this->get(route('admin.certificates.index'));

        $response->assertOk();
        $response->assertViewIs('admin.certificates.index');
        $response->assertViewHas('certificates');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.certificates.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.certificates.create'));

        $response->assertOk();
        $response->assertViewIs('admin.certificates.create');
        $response->assertViewHas(['applications']);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.certificates.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $certificate = Certificate::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.certificates.show', $certificate->id));

        $response->assertOk();
        $response->assertViewIs('admin.certificates.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $certificate = Certificate::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.certificates.show', $certificate->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $certificate = Certificate::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.certificates.edit', $certificate));

        $response->assertOk();
        $response->assertViewIs('admin.certificates.edit');
        $response->assertViewHas('applications');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $certificate = Certificate::factory()->create();

        $response = $this->get(route('admin.certificates.edit', $certificate));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update activity', function () {
        $name = fake()->word();
        $certificate = Certificate::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.certificates.update', $certificate), $data);

        $response->assertRedirect(route('admin.certificates.index'));
        $this->assertDatabaseHas('certificates', ['name' => 'Updated Name']);
    });
});

describe('destroy', function () {
    test('can delete activity', function () {
        $certificate = Certificate::factory()->create();

        $response = $this->delete(route('admin.certificates.destroy', $certificate->id));
        $response->assertRedirect(route('admin.certificates.index'));

        $this->assertSoftDeleted('certificates', ['id' => $certificate->id]);

        $certificate->refresh();
        expect($certificate->deleted_at)->not->toBeNull()
            ->and($certificate->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $certificate = Certificate::factory()->create();

        $response = $this->delete(route('admin.certificates.destroy', $certificate));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple certificates', function () {
        $certificates = Certificate::factory()->count(3)->create();
        $ids = $certificates->pluck('id')->toArray();

        $response = $this->delete(route('admin.certificates.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('certificates', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $certificate = Certificate::factory()->create();

        $response = $this->delete(route('admin.certificates.massDestroy'), [
            'ids' => [$certificate->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $certificate = Certificate::factory()->create();

        $response = $this->delete(route('admin.certificates.massDestroy'), [
            'ids' => [$certificate->id],
        ]);

        $response->assertForbidden();
    });

});
