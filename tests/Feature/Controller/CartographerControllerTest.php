<?php

use App\Models\Application;
use App\Models\Cartographer;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::forget('permissions_roles_map');
    Cache::forget('cartographers_last_update');

    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    $this->admin = User::query()->where('login', 'admin@admin.com')->first();
    $this->actingAs($this->admin);
});

// ── associatedObjects ──────────────────────────────────────────────────────

describe('associatedObjects', function () {

    test('returns objects associated with a given user_id', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $app1 = Application::factory()->create(['name' => 'App A']);
        $app2 = Application::factory()->create(['name' => 'App B']);

        Cartographer::create(['cartographiable_type' => Application::class, 'cartographiable_id' => $app1->id, 'user_id' => $user->id]);
        Cartographer::create(['cartographiable_type' => Application::class, 'cartographiable_id' => $app2->id, 'user_id' => $other->id]);

        $response = $this->getJson(route('admin.cartographers.associated-objects', ['user_id' => $user->id]));

        $response->assertOk();
        $data = $response->json();
        expect(count($data))->toBe(1)
            ->and($data[0]['id'])->toBe($app1->id)
            ->and($data[0]['type'])->toBe(Application::class);
    });

    test('returns objects associated with a given role_id', function () {
        $role = Role::factory()->create(['title' => 'testers']);
        $app = Application::factory()->create(['name' => 'Shared App']);

        Cartographer::create(['cartographiable_type' => Application::class, 'cartographiable_id' => $app->id, 'role_id' => $role->id]);

        $response = $this->getJson(route('admin.cartographers.associated-objects', ['role_id' => $role->id]));

        $response->assertOk();
        $data = $response->json();
        expect(count($data))->toBe(1)
            ->and($data[0]['id'])->toBe($app->id);
    });

    test('returns empty array when no parameter provided', function () {
        $response = $this->getJson(route('admin.cartographers.associated-objects'));

        $response->assertOk()->assertExactJson([]);
    });

    test('returns 403 without cartographer_access permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson(route('admin.cartographers.associated-objects', ['user_id' => $user->id]));

        $response->assertForbidden();
    });

    test('label combines type label and object name', function () {
        $user = User::factory()->create();
        $app = Application::factory()->create(['name' => 'My App']);

        Cartographer::create(['cartographiable_type' => Application::class, 'cartographiable_id' => $app->id, 'user_id' => $user->id]);

        $response = $this->getJson(route('admin.cartographers.associated-objects', ['user_id' => $user->id]));

        $response->assertOk();
        $data = $response->json();
        expect($data[0]['name'])->toBe('My App')
            ->and($data[0]['label'])->toContain('My App');
    });
});

// ── create ─────────────────────────────────────────────────────────────────

describe('create', function () {

    test('displays the management page', function () {
        $response = $this->get(route('admin.cartographers.create'));

        $response->assertOk()->assertViewIs('admin.cartographers.create');
    });

    test('accepts user_id query parameter for pre-selection', function () {
        $user = User::factory()->create();

        $response = $this->get(route('admin.cartographers.create', ['user_id' => $user->id]));

        $response->assertOk()->assertViewHas('userId', (string) $user->id);
    });

    test('accepts role_id query parameter for pre-selection', function () {
        $role = Role::factory()->create(['title' => 'managers']);

        $response = $this->get(route('admin.cartographers.create', ['role_id' => $role->id]));

        $response->assertOk()->assertViewHas('roleId', (string) $role->id);
    });

    test('index edit link points to create page with user_id', function () {
        $user = User::factory()->create(['name' => 'Alice']);
        $app = Application::factory()->create();
        Cartographer::create(['cartographiable_type' => Application::class, 'cartographiable_id' => $app->id, 'user_id' => $user->id]);

        $response = $this->get(route('admin.cartographers.index'));

        $response->assertOk()
            ->assertSee(route('admin.cartographers.create', ['user_id' => $user->id]), false);
    });

    test('returns 403 without cartographer_create permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.cartographers.create'));

        $response->assertForbidden();
    });
});

// ── store ──────────────────────────────────────────────────────────────────

describe('store', function () {

    test('creates all associations for a user from objects array', function () {
        $user = User::factory()->create();
        $app1 = Application::factory()->create();
        $app2 = Application::factory()->create();

        $response = $this->post(route('admin.cartographers.store'), [
            'user_id' => $user->id,
            'objects' => [
                Application::class.'|'.$app1->id,
                Application::class.'|'.$app2->id,
            ],
        ]);

        $response->assertRedirect(route('admin.cartographers.index'));
        expect(Cartographer::where('user_id', $user->id)->count())->toBe(2);
    });

    test('does not delete existing associations absent from objects array', function () {
        $user = User::factory()->create();
        $app1 = Application::factory()->create();
        $app2 = Application::factory()->create();

        Cartographer::create(['cartographiable_type' => Application::class, 'cartographiable_id' => $app1->id, 'user_id' => $user->id]);

        $this->post(route('admin.cartographers.store'), [
            'user_id' => $user->id,
            'objects' => [Application::class.'|'.$app2->id],
        ]);

        expect(Cartographer::where('user_id', $user->id)->count())->toBe(2);
        $this->assertDatabaseHas('cartographers', ['cartographiable_id' => $app1->id, 'user_id' => $user->id]);
    });

    test('does not create a duplicate if association already exists', function () {
        $user = User::factory()->create();
        $app = Application::factory()->create();

        Cartographer::create(['cartographiable_type' => Application::class, 'cartographiable_id' => $app->id, 'user_id' => $user->id]);

        $this->post(route('admin.cartographers.store'), [
            'user_id' => $user->id,
            'objects' => [Application::class.'|'.$app->id],
        ]);

        expect(Cartographer::where('user_id', $user->id)->count())->toBe(1);
    });

    test('ignores entries with unauthorised type', function () {
        $user = User::factory()->create();

        $this->post(route('admin.cartographers.store'), [
            'user_id' => $user->id,
            'objects' => ['InvalidClass|1'],
        ]);

        expect(Cartographer::where('user_id', $user->id)->count())->toBe(0);
    });

    test('fails validation when objects array is empty', function () {
        $user = User::factory()->create();

        $response = $this->post(route('admin.cartographers.store'), [
            'user_id' => $user->id,
            'objects' => [],
        ]);

        $response->assertSessionHasErrors('objects');
    });

    test('fails validation when objects is absent', function () {
        $user = User::factory()->create();

        $response = $this->post(route('admin.cartographers.store'), [
            'user_id' => $user->id,
        ]);

        $response->assertSessionHasErrors('objects');
    });

    test('fails when both user and role are provided', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['title' => 'editors']);
        $app = Application::factory()->create();

        $response = $this->post(route('admin.cartographers.store'), [
            'user_id' => $user->id,
            'role_id' => $role->id,
            'objects' => [Application::class.'|'.$app->id],
        ]);

        $response->assertSessionHasErrors('user_id');
        expect(Cartographer::count())->toBe(0);
    });

    test('fails when neither user nor role is provided', function () {
        $app = Application::factory()->create();

        $response = $this->post(route('admin.cartographers.store'), [
            'objects' => [Application::class.'|'.$app->id],
        ]);

        $response->assertSessionHasErrors('user_id');
        expect(Cartographer::count())->toBe(0);
    });
});
