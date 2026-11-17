
<?php

use App\Models\Information;
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
    test('can display information index page', function () {
        Information::factory()->count(3)->create();

        $response = $this->get(route('admin.information.index'));

        $response->assertOk();
        $response->assertViewIs('admin.information.index');
        $response->assertViewHas('information');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.information.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.information.create'));

        $response->assertOk();
        $response->assertViewIs('admin.information.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.information.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $information = Information::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.information.show', $information->id));

        $response->assertOk();
        $response->assertViewIs('admin.information.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $information = Information::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.information.show', $information->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $information = Information::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.information.edit', $information));

        $response->assertOk();
        $response->assertViewIs('admin.information.edit');
        $response->assertViewHas('information');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $information = Information::factory()->create();

        $response = $this->get(route('admin.information.edit', $information));

        $response->assertForbidden();
    });
});

describe('update', function () {
    test('can update Information', function () {
        $name = fake()->word();
        $information = Information::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentence(5),
        ];

        $response = $this->put(route('admin.information.update', $information), $data);

        $response->assertRedirect(route('admin.information.index'));
        $this->assertDatabaseHas('information', ['name' => 'Updated Name']);
    });
});

describe('retention', function () {
    test('can store retention fields', function () {
        $data = [
            'name' => 'Candidatures spontanées',
            'retention_period' => '6 mois',
            'final_disposition' => 'Destruction',
            'retention_notes' => '<p>Après visa du service des archives</p>',
        ];

        $response = $this->post(route('admin.information.store'), $data);

        $response->assertRedirect(route('admin.information.index'));
        $this->assertDatabaseHas('information', [
            'name' => 'Candidatures spontanées',
            'retention_period' => '6 mois',
            'final_disposition' => 'Destruction',
        ]);
        expect(Information::query()->where('name', 'Candidatures spontanées')->first()->retention_notes)
            ->toContain('Après visa du service des archives');
    });

    test('can update retention fields', function () {
        $information = Information::factory()->create(['name' => 'Convention partenaires']);

        $response = $this->put(route('admin.information.update', $information), [
            'name' => 'Convention partenaires',
            'retention_period' => 'Durée de la convention + 5 ans',
            'final_disposition' => 'Versement aux archives',
        ]);

        $response->assertRedirect(route('admin.information.index'));
        $information->refresh();
        expect($information->retention_period)->toBe('Durée de la convention + 5 ans')
            ->and($information->final_disposition)->toBe('Versement aux archives');
    });

    test('edit form proposes existing retention values', function () {
        Information::factory()->create(['retention_period' => '10 ans', 'final_disposition' => 'Tri']);
        $information = Information::factory()->create();

        $response = $this->get(route('admin.information.edit', $information));

        $response->assertOk();
        $response->assertViewHas('retention_period_list', fn ($list) => $list->contains('10 ans'));
        $response->assertViewHas('final_disposition_list', fn ($list) => $list->contains('Tri'));
    });

    test('show displays retention fields', function () {
        $information = Information::factory()->create([
            'retention_period' => '6 mois',
            'final_disposition' => 'Destruction',
            'retention_notes' => 'Critère de tri spécifique',
        ]);

        $response = $this->get(route('admin.information.show', $information));

        $response->assertOk();
        $response->assertSee('6 mois');
        $response->assertSee('Destruction');
        $response->assertSee('Critère de tri spécifique');
    });
});

describe('destroy', function () {
    test('can delete Information', function () {
        $information = Information::factory()->create();

        $response = $this->delete(route('admin.information.destroy', $information->id));
        $response->assertRedirect(route('admin.information.index'));

        $this->assertSoftDeleted('information', ['id' => $information->id]);

        $information->refresh();
        expect($information->deleted_at)->not->toBeNull()
            ->and($information->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $information = Information::factory()->create();

        $response = $this->delete(route('admin.information.destroy', $information));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple information', function () {
        $information = Information::factory()->count(3)->create();
        $ids = $information->pluck('id')->toArray();

        $response = $this->delete(route('admin.information.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('information', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $information = Information::factory()->create();

        $response = $this->delete(route('admin.information.massDestroy'), [
            'ids' => [$information->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $information = Information::factory()->create();

        $response = $this->delete(route('admin.information.massDestroy'), [
            'ids' => [$information->id],
        ]);

        $response->assertForbidden();
    });

});
