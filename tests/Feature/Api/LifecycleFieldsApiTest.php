<?php

/*
 * Lifecycle fields (commissioning, warranty, end of life, hardware identity)
 * synchronised from GLPI Infocom (sourcentis/mercator-glpi#19).
 */

use App\Models\Database;
use App\Models\Peripheral;
use App\Models\Phone;
use App\Models\PhysicalRouter;
use App\Models\PhysicalSecurityDevice;
use App\Models\PhysicalServer;
use App\Models\PhysicalSwitch;
use App\Models\StorageDevice;
use App\Models\User;
use App\Models\WifiTerminal;
use App\Models\Workstation;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

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
    Passport::actingAs($this->user);
});

$dates = [
    'install_date' => '2021-09-30',
    'warranty_end_date' => '2026-09-30',
    'end_of_life_date' => '2029-12-31',
];
$identity = [
    'manufacturer' => 'Cisco',
    'model' => 'C9300-48P',
    'serial_number' => 'FOC2245X0AB',
];

dataset('lifecycle assets', [
    'workstations' => ['workstations', Workstation::class, [
        'install_date' => '2023-04-12',
        'end_of_life_date' => '2028-04-12',
        'purchase_date' => '2023-03-15',
        'fin_value' => '1299.00',
        'warranty' => 'ProSupport',
        'warranty_start_date' => '2023-03-20',
        'warranty_end_date' => '2026-03-20',
        'warranty_period' => '36 mois',
    ]],
    'peripherals' => ['peripherals', Peripheral::class, $dates + ['serial_number' => 'CN12345678']],
    'wifi-terminals' => ['wifi-terminals', WifiTerminal::class, $dates + ['serial_number' => 'WIFI0001']],
    'phones' => ['phones', Phone::class, $dates + $identity],
    'physical-switches' => ['physical-switches', PhysicalSwitch::class, $dates + $identity],
    'physical-routers' => ['physical-routers', PhysicalRouter::class, $dates + $identity],
    'physical-security-devices' => ['physical-security-devices', PhysicalSecurityDevice::class, $dates + $identity],
    'storage-devices' => ['storage-devices', StorageDevice::class, $dates + $identity],
    'physical-servers' => ['physical-servers', PhysicalServer::class, $dates + $identity],
    'databases' => ['databases', Database::class, ['install_date' => '2020-01-15']],
]);

it('stores lifecycle fields through the API', function (string $endpoint, string $modelClass, array $fields) {
    $response = $this->postJson("/api/{$endpoint}", ['name' => 'LIFECYCLE-01'] + $fields);
    $response->assertSuccessful();

    $model = $modelClass::query()->where('name', 'LIFECYCLE-01')->firstOrFail();
    foreach ($fields as $field => $value) {
        expect((string) $model->getRawOriginal($field))->toBe($value, $field);
    }
})->with('lifecycle assets');

it('updates lifecycle fields through mass-update', function (string $endpoint, string $modelClass, array $fields) {
    $model = $modelClass::factory()->create();

    $this->putJson("/api/{$endpoint}/mass-update", [
        'items' => [['id' => $model->id, 'name' => $model->name] + $fields],
    ])->assertSuccessful();

    $model->refresh();
    foreach ($fields as $field => $value) {
        expect((string) $model->getRawOriginal($field))->toBe($value, $field);
    }
})->with('lifecycle assets');

it('rejects an invalid lifecycle date', function (string $endpoint) {
    $this->postJson("/api/{$endpoint}", ['name' => 'LIFECYCLE-02', 'install_date' => 'not-a-date'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('install_date');
})->with(['phones', 'physical-switches', 'workstations', 'databases']);

it('filters assets reaching end of life', function () {
    PhysicalSwitch::factory()->create(['name' => 'SW-OLD', 'end_of_life_date' => '2025-06-30']);
    PhysicalSwitch::factory()->create(['name' => 'SW-NEW', 'end_of_life_date' => '2031-06-30']);

    $data = $this->getJson('/api/physical-switches?filter[end_of_life_date_before]=2026-01-01')
        ->assertOk()
        ->json();
    $data = $data['data'] ?? $data;

    expect(collect($data)->pluck('name')->all())->toBe(['SW-OLD']);
});

it('displays lifecycle fields on show and edit pages', function (string $route, string $modelClass, array $fields) {
    $this->actingAs($this->user);
    $model = $modelClass::factory()->create($fields);

    $html = $this->get(route("admin.{$route}.show", $model))
        ->assertOk()
        ->assertSee($fields['install_date'])
        ->assertSee($fields['end_of_life_date'])
        ->getContent();

    // Dedicated "Lifecycle" section, not rows appended to the object's own table.
    expect(substr_count($html, $fields['end_of_life_date']))->toBe(1);
    $details = view('admin.'.str($route)->camel().'._details', [str($modelClass)->classBasename()->camel()->toString() => $model])->render();
    expect($details)->not->toContain($fields['end_of_life_date']);

    $this->get(route("admin.{$route}.edit", $model))
        ->assertOk()
        ->assertSee('name="end_of_life_date"', false)
        ->assertSee('value="'.$fields['end_of_life_date'].'"', false);

    $this->get(route("admin.{$route}.create"))
        ->assertOk()
        ->assertSee('name="install_date"', false);
})->with([
    'workstations' => ['workstations', Workstation::class, ['install_date' => '2023-04-12', 'end_of_life_date' => '2028-04-12']],
    'physical-switches' => ['physical-switches', PhysicalSwitch::class, $dates],
    'storage-devices' => ['storage-devices', StorageDevice::class, $dates],
    'physical-servers' => ['physical-servers', PhysicalServer::class, $dates],
]);
