<?php

use App\Models\Entity;
use App\Models\Relation;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed base permissions/roles and users as in other feature tests
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    // Login as an admin (id=1 seeded by UsersTableSeeder)
    $this->user = User::query()->where('login', 'admin@admin.com')->first();
    $this->actingAs($this->user);
});

describe('Ecosystem View', function () {
    test('can display ecosystem view page', function () {
        $response = $this->get(route('admin.report.view.ecosystem'));

        $response->assertOk();
        $response->assertViewIs('admin.reports.ecosystem');
        $response->assertViewHasAll(['all_entities', 'selectedEntities', 'entities', 'relations']);
    });

    test('shows the whole ecosystem without starting entities', function () {
        $a = Entity::factory()->create();
        $b = Entity::factory()->create();
        $c = Entity::factory()->create();
        Relation::factory()->create(['source_id' => $a->id, 'destination_id' => $b->id]);
        Relation::factory()->create(['source_id' => $b->id, 'destination_id' => $c->id]);

        $response = $this->get(route('admin.report.view.ecosystem'));

        $response->assertOk();
        expect($response->viewData('entities')->pluck('id')->sort()->values()->all())
            ->toBe(collect([$a->id, $b->id, $c->id])->sort()->values()->all());
        expect($response->viewData('relations'))->toHaveCount(2);
        expect($response->viewData('selectedEntities'))->toBe([]);
    });

    test('shows starting entities, their neighbours and the relations between them', function () {
        $start = Entity::factory()->create();
        $out = Entity::factory()->create();
        $in = Entity::factory()->create();
        $far = Entity::factory()->create();
        $isolated = Entity::factory()->create();

        $r1 = Relation::factory()->create(['source_id' => $start->id, 'destination_id' => $out->id]);
        $r2 = Relation::factory()->create(['source_id' => $in->id, 'destination_id' => $start->id]);
        // Relations between neighbours / beyond the neighbours are not shown
        $r3 = Relation::factory()->create(['source_id' => $out->id, 'destination_id' => $in->id]);
        $r4 = Relation::factory()->create(['source_id' => $out->id, 'destination_id' => $far->id]);

        $response = $this->get(route('admin.report.view.ecosystem', ['entities' => [$start->id]]));

        $response->assertOk();
        expect($response->viewData('entities')->pluck('id')->sort()->values()->all())
            ->toBe(collect([$start->id, $out->id, $in->id])->sort()->values()->all());
        expect($response->viewData('relations')->pluck('id')->sort()->values()->all())
            ->toBe(collect([$r1->id, $r2->id])->sort()->values()->all());
        expect($response->viewData('selectedEntities'))->toBe([$start->id]);
        expect($response->viewData('dotSrc'))
            ->toContain('E'.$start->id.' -> E'.$out->id)
            ->not->toContain('E'.$far->id)
            ->not->toContain('E'.$isolated->id);
    });

    test('a starting entity without relations is shown alone', function () {
        $alone = Entity::factory()->create();
        $a = Entity::factory()->create();
        $b = Entity::factory()->create();
        Relation::factory()->create(['source_id' => $a->id, 'destination_id' => $b->id]);

        $response = $this->get(route('admin.report.view.ecosystem', ['entities' => [$alone->id]]));

        expect($response->viewData('entities')->pluck('id')->all())->toBe([$alone->id]);
        expect($response->viewData('relations'))->toBeEmpty();
    });

    test('unknown starting entity ids are ignored', function () {
        Entity::factory()->count(2)->create();

        $response = $this->get(route('admin.report.view.ecosystem', ['entities' => [999999, 'abc']]));

        $response->assertOk();
        expect($response->viewData('selectedEntities'))->toBe([]);
        expect($response->viewData('entities'))->toHaveCount(2);
    });

    test('perimeter and type filters are gone', function () {
        $response = $this->get(route('admin.report.view.ecosystem'));

        $response->assertOk();
        $response->assertSee('name="entities[]"', false);
        $response->assertDontSee('name="perimeter"', false);
        $response->assertDontSee('name="type"', false);
    });

    test('denies access without permission', function () {
        // New user without the reports_access permission
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.report.view.ecosystem'));

        $response->assertForbidden();
    });
});
