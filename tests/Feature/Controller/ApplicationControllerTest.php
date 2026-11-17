<?php

use App\Models\Application;
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
    test('can display applications index page', function () {
        Application::factory()->count(3)->create();

        $response = $this->get(route('admin.applications.index'));

        $response->assertOk();
        $response->assertViewIs('admin.applications.index');
        $response->assertViewHas('applications');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.applications.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.applications.create'));

        $response->assertOk();
        $response->assertViewIs('admin.applications.create');
        $response->assertViewHas([
            'activities',
            'services',
            'databases',
        ]);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.applications.create'));

        $response->assertForbidden();
    });

});

describe('clone', function () {
    test('can display clone form with multi-valued string fields', function () {
        $application = Application::factory()->create([
            'attributes' => 'Critical Internal',
            'responsible' => 'Alice, Bob',
            'technology' => 'PHP, MySQL',
            'external' => 'SaaS',
        ]);

        $response = $this->get(route('admin.applications.clone', $application->id));

        $response->assertOk();
        $response->assertViewIs('admin.applications.create');
        $response->assertSee($application->name.' (copy)', false);
    });
});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $container = Application::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.applications.show', $container->id));

        $response->assertOk();
        $response->assertViewIs('admin.applications.show');
        $response->assertSee($name);
    });

    test('renders web URLs as links and network paths with a copy button', function () {
        $application = Application::factory()->create([
            'urls' => 'https://app.example.com',
            'documentation' => '//srv/Mon Partage/doc.pdf',
        ]);

        $response = $this->get(route('admin.applications.show', $application->id));

        $response->assertOk();
        $response->assertSee('<a href="https://app.example.com"', false);
        $response->assertSee('\\\\srv\\Mon Partage\\doc.pdf');
        $response->assertSee('copy-path-button', false);
        $response->assertDontSee('href="//srv', false);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $container = Application::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.applications.show', $container->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $container = Application::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.applications.edit', $container));

        $response->assertOk();
        $response->assertViewIs('admin.applications.edit');
        $response->assertViewHas('application');
        $response->assertSee($name);
    });

    test('does not select technologies whose name is a prefix of a selected one', function () {
        Application::factory()->create(['technology' => 'Angular']);
        $container = Application::factory()->create(['technology' => 'AngularJS']);

        $response = $this->get(route('admin.applications.edit', $container));

        $response->assertOk();
        $response->assertSee('<option selected>AngularJS</option>', false);
        $response->assertDontSee('<option selected>Angular</option>', false);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $container = Application::factory()->create();

        $response = $this->get(route('admin.applications.edit', $container));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Application', function () {
        $name = fake()->word();
        $container = Application::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(),
        ];

        $response = $this->put(route('admin.applications.update', $container), $data);

        $response->assertRedirect(route('admin.applications.index'));
        $this->assertDatabaseHas('applications', ['name' => 'Updated Name']);
    });

    test('stores network paths in UNC form', function () {
        $application = Application::factory()->create();

        $response = $this->put(route('admin.applications.update', $application), [
            'name' => $application->name,
            'urls' => 'https://app.example.com',
            'documentation' => '//srv/Mon Partage/Procédures/doc.pdf, \\\\srv\\partage$\\guide v2.pdf',
        ]);

        $response->assertRedirect(route('admin.applications.index'));
        expect($application->fresh()->documentation)
            ->toBe('\\\\srv\\Mon Partage\\Procédures\\doc.pdf,\\\\srv\\partage$\\guide v2.pdf');
    });

    test('rejects invalid documentation paths', function () {
        $application = Application::factory()->create();

        $response = $this->put(route('admin.applications.update', $application), [
            'name' => $application->name,
            'documentation' => 'not a path',
        ]);

        $response->assertSessionHasErrors('documentation');
    });
});

describe('destroy', function () {
    test('can delete Application', function () {
        $container = Application::factory()->create();

        $response = $this->delete(route('admin.applications.destroy', $container->id));
        $response->assertRedirect(route('admin.applications.index'));

        $this->assertSoftDeleted('applications', ['id' => $container->id]);

        $container->refresh();
        expect($container->deleted_at)->not->toBeNull()
            ->and($container->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $container = Application::factory()->create();

        $response = $this->delete(route('admin.applications.destroy', $container));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple applications', function () {
        $application = Application::factory()->count(3)->create();
        $ids = $application->pluck('id')->toArray();

        $response = $this->delete(route('admin.applications.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('applications', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $application = Application::factory()->create();

        $response = $this->delete(route('admin.applications.massDestroy'), [
            'ids' => [$application->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $application = Application::factory()->create();

        $response = $this->delete(route('admin.applications.massDestroy'), [
            'ids' => [$application->id],
        ]);

        $response->assertForbidden();
    });

});
