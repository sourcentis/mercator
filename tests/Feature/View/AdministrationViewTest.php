<?php

use App\Models\AdminUser;
use App\Models\Annuaire;
use App\Models\Domain;
use App\Models\ForestAd;
use App\Models\User;
use App\Models\ZoneAdmin;
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

describe('Administration View', function () {
    test('can display administration view page', function () {
        $response = $this->get(route('admin.report.view.administration'));

        $response->assertOk();
        $response->assertViewIs('admin.reports.administration');
        $response->assertViewHasAll([
            'zones',
            'annuaires',
            'forests',
            'domains',
            'adminUsers',
            'all_zones',
            'selectedZones',
        ]);
    });

    test('shows every zone without filter', function () {
        ZoneAdmin::factory()->count(2)->create();

        $response = $this->get(route('admin.report.view.administration'));

        $response->assertOk();
        $response->assertSee('name="zones[]"', false);
        expect($response->viewData('zones'))->toHaveCount(2);
        expect($response->viewData('selectedZones'))->toBe([]);
    });

    test('filters on the selected zones and their annuaires, forests, domains and users', function () {
        $zone = ZoneAdmin::factory()->create();
        $other = ZoneAdmin::factory()->create();

        $annuaire = Annuaire::factory()->create(['zone_admin_id' => $zone->id]);
        Annuaire::factory()->create(['zone_admin_id' => $other->id]);

        $forest = ForestAd::factory()->create(['zone_admin_id' => $zone->id]);
        $otherForest = ForestAd::factory()->create(['zone_admin_id' => $other->id]);

        $domain = Domain::factory()->create();
        $otherDomain = Domain::factory()->create();
        $forest->domains()->attach($domain);
        $otherForest->domains()->attach($otherDomain);

        $user = AdminUser::factory()->create(['domain_id' => $domain->id]);
        AdminUser::factory()->create(['domain_id' => $otherDomain->id]);

        $response = $this->get(route('admin.report.view.administration', ['zones' => [$zone->id]]));

        $response->assertOk();
        expect($response->viewData('selectedZones'))->toBe([$zone->id]);
        expect($response->viewData('zones')->pluck('id')->all())->toBe([$zone->id]);
        expect($response->viewData('annuaires')->pluck('id')->all())->toBe([$annuaire->id]);
        expect($response->viewData('forests')->pluck('id')->all())->toBe([$forest->id]);
        expect($response->viewData('domains')->pluck('id')->all())->toBe([$domain->id]);
        expect($response->viewData('adminUsers')->pluck('id')->all())->toBe([$user->id]);
        expect($response->viewData('dotSrc'))
            ->toContain('Z'.$zone->id.' ')
            ->not->toContain('Z'.$other->id.' ')
            ->not->toContain('F'.$otherForest->id.' ');
    });

    test('unknown zone ids are ignored', function () {
        ZoneAdmin::factory()->count(2)->create();

        $response = $this->get(route('admin.report.view.administration', ['zones' => [999999, 'abc']]));

        $response->assertOk();
        expect($response->viewData('selectedZones'))->toBe([]);
        expect($response->viewData('zones'))->toHaveCount(2);
    });

    test('denies access without permission', function () {
        // New user without the reports_access permission
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.report.view.administration'));

        $response->assertForbidden();
    });
});
