<?php

use App\Models\Perimeter;
use App\Models\Role;
use App\Models\User;
use App\Support\PerimeterSettings;
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

test('displays the info page', function () {
    $response = $this->get(route('admin.doc.info'));

    $response->assertOk();
    $response->assertViewIs('doc.info');
    $response->assertSee($this->user->login);
});

test('hides the perimeters row when the feature is disabled', function () {
    $response = $this->get(route('admin.doc.info'));

    $response->assertOk();
    $response->assertViewHas('perimetersEnabled', false);
    $response->assertDontSee('id="info-perimeters"', false);
});

test('shows the user perimeters when the feature is enabled', function () {
    PerimeterSettings::setEnabled(true);
    $perimeter = Perimeter::factory()->create(['name' => 'Site B']);
    $role = Role::factory()->create(['perimeter_id' => $perimeter->id]);
    $this->user->roles()->attach($role);

    $response = $this->get(route('admin.doc.info'));

    $response->assertOk();
    $response->assertViewHas('perimetersEnabled', true);
    $response->assertViewHas('perimeters', function (array $perimeters) {
        return in_array('Site B', $perimeters, true);
    });
    $response->assertSee('Site B');
});

test('shows mapped objects and per-perimeter rights', function () {
    PerimeterSettings::setEnabled(true);
    $perimeter = Perimeter::factory()->create(['name' => 'Site C']);
    $role = Role::factory()->create(['title' => 'Lecteur C', 'perimeter_id' => $perimeter->id]);
    $this->user->roles()->attach($role);

    $response = $this->get(route('admin.doc.info'));

    $response->assertOk();
    $response->assertViewHas('objectGroups', fn (array $groups) => collect($groups)->pluck('key')->contains('ecosystem'));
    $response->assertViewHas('perimeterSummary', function (array $summary) {
        $row = collect($summary)->firstWhere('name', 'Site C');

        return $row !== null && in_array('Lecteur C', $row['roles'], true);
    });
    $response->assertSee('id="info-perimeter-summary"', false);
    $response->assertSee('id="info-rights"', false);
});

test('renders for a non-admin user restricted to one perimeter', function () {
    PerimeterSettings::setEnabled(true);
    $perimeter = Perimeter::factory()->create(['name' => 'Site D']);
    $role = Role::factory()->create(['perimeter_id' => $perimeter->id]);
    $user = User::factory()->create();
    $user->roles()->attach($role);
    $this->actingAs($user);

    $response = $this->get(route('admin.doc.info'));

    $response->assertOk();
    $response->assertViewHas('isAdmin', false);
    $response->assertSee('Site D');
});

test('displays the CSP mode', function (string $mode, string $badge, string $labelKey) {
    config(['mercator.csp.mode' => $mode]);

    $response = $this->get(route('admin.doc.info'));

    $response->assertOk();
    $response->assertViewHas('cspMode', $mode);
    $response->assertSee('<span class="badge '.$badge.'">'.$mode.'</span>', false);
    $response->assertSee(trans('panel.info.'.$labelKey));
})->with([
    'enforce' => ['enforce', 'bg-success', 'csp_enforce'],
    'report-only' => ['report-only', 'bg-warning text-dark', 'csp_report_only'],
    'off' => ['off', 'bg-danger', 'csp_off'],
    // Valeur inconnue : SecurityHeaders n'émet aucune CSP — la page doit le refléter
    'unknown value' => ['enforced', 'bg-danger', 'csp_off'],
]);
