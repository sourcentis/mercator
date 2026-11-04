
<?php

use App\Models\MacroProcessus;
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
    test('can display activities index page', function () {
        MacroProcessus::factory()->count(3)->create();

        $response = $this->get(route('admin.macro-processuses.index'));

        $response->assertOk();
        $response->assertViewIs('admin.macroProcessuses.index');
        $response->assertViewHas('macroProcessuses');
        $response->assertSee(trans('cruds.macroProcessus.fields.type'));
        $response->assertSee(trans('cruds.macroProcessus.fields.attributes'));
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.macro-processuses.index'));

        $response->assertForbidden();
    });

});

describe('create', function () {
    test('can display create form', function () {
        $response = $this->get(route('admin.macro-processuses.create'));

        $response->assertOk();
        $response->assertViewIs('admin.macroProcessuses.create');
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.macro-processuses.create'));

        $response->assertForbidden();
    });

});

describe('show', function () {

    test('can display object', function () {
        $name = fake()->word();
        $macoProcessus = MacroProcessus::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.macro-processuses.show', $macoProcessus->id));

        $response->assertOk();
        $response->assertViewIs('admin.macroProcessuses.show');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $name = fake()->word();
        $macoProcessus = MacroProcessus::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.macro-processuses.show', $macoProcessus->id));

        $response->assertForbidden();
    });

});

describe('edit', function () {
    test('can display edit form', function () {
        $name = fake()->word();
        $macoProcessus = MacroProcessus::factory()->create(['name' => $name]);

        $response = $this->get(route('admin.macro-processuses.edit', $macoProcessus));

        $response->assertOk();
        $response->assertViewIs('admin.macroProcessuses.edit');
        $response->assertViewHas('macroProcessus');
        $response->assertSee($name);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $macoProcessus = MacroProcessus::factory()->create();

        $response = $this->get(route('admin.macro-processuses.edit', $macoProcessus));

        $response->assertForbidden();
    });
});

describe('store', function () {
    test('can create a MacroProcessus with type and attributes', function () {
        $data = [
            'name' => 'Order to Cash',
            'type' => 'Support',
            'attributes' => ['critique', 'transverse'],
        ];

        $response = $this->post(route('admin.macro-processuses.store'), $data);

        $response->assertRedirect(route('admin.macro-processuses.index'));
        $this->assertDatabaseHas('macro_processuses', [
            'name' => 'Order to Cash',
            'type' => 'Support',
            'attributes' => 'critique transverse',
        ]);
    });
});

describe('update', function () {
    test('can update MacroProcessus', function () {
        $name = fake()->word();
        $macoProcessus = MacroProcessus::factory()->create(['name' => $name]);

        $data = [
            'name' => 'Updated Name',
            'description' => fake()->sentences(3, true),
            'source_ip_range' => fake()->ipv4().'/32',
            'dest_ip_range' => fake()->ipv4().'/32',
        ];

        $response = $this->put(route('admin.macro-processuses.update', $macoProcessus), $data);

        $response->assertRedirect(route('admin.macro-processuses.index'));
        $this->assertDatabaseHas('macro_processuses', ['name' => 'Updated Name']);
    });

    test('can update MacroProcessus type and attributes', function () {
        $macoProcessus = MacroProcessus::factory()->create(['type' => 'Old', 'attributes' => 'old tag']);

        $data = [
            'name' => 'Order to Cash',
            'type' => 'Nouveau',
            'attributes' => ['nouveau', 'tag'],
        ];

        $response = $this->put(route('admin.macro-processuses.update', $macoProcessus), $data);

        $response->assertRedirect(route('admin.macro-processuses.index'));
        $this->assertDatabaseHas('macro_processuses', [
            'id' => $macoProcessus->id,
            'type' => 'Nouveau',
            'attributes' => 'nouveau tag',
        ]);
    });
});

describe('destroy', function () {
    test('can delete MacroProcessus', function () {
        $macoProcessus = MacroProcessus::factory()->create();

        $response = $this->delete(route('admin.macro-processuses.destroy', $macoProcessus->id));
        $response->assertRedirect(route('admin.macro-processuses.index'));

        $this->assertSoftDeleted('macro_processuses', ['id' => $macoProcessus->id]);

        $macoProcessus->refresh();
        expect($macoProcessus->deleted_at)->not->toBeNull()
            ->and($macoProcessus->trashed())->toBeTrue();

    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $macoProcessus = MacroProcessus::factory()->create();

        $response = $this->delete(route('admin.macro-processuses.destroy', $macoProcessus));

        $response->assertForbidden();
    });
});

describe('massDestroy', function () {
    test('can delete multiple MacroProcessus', function () {
        $activities = MacroProcessus::factory()->count(3)->create();
        $ids = $activities->pluck('id')->toArray();

        $response = $this->delete(route('admin.macro-processuses.massDestroy'), ['ids' => $ids]);
        $response->assertNoContent();

        foreach ($ids as $id) {
            $this->assertSoftDeleted('macro_processuses', ['id' => $id]);
        }
    });

    test('returns no content status', function () {
        $macoProcessus = MacroProcessus::factory()->create();

        $response = $this->delete(route('admin.macro-processuses.massDestroy'), [
            'ids' => [$macoProcessus->id],
        ]);

        $response->assertStatus(204);
    });

    test('denies access without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $macoProcessus = MacroProcessus::factory()->create();

        $response = $this->delete(route('admin.macro-processuses.massDestroy'), [
            'ids' => [$macoProcessus->id],
        ]);

        $response->assertForbidden();
    });

});
