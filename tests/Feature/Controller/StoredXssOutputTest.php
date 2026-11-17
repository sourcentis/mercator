<?php

use App\Models\DataProcessing;
use App\Models\Information;
use App\Models\Lan;
use App\Models\Network;
use App\Models\Relation;
use App\Models\User;
use App\Models\Workstation;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Valeurs stockées sans passer par les FormRequest (import, données antérieures
// à l'assainissement en entrée) : elles ne doivent pas s'exécuter à l'affichage.
uses(RefreshDatabase::class);

const STORED_XSS_PAYLOAD = '</textarea><script>alert(1)</script><img src=x onerror=alert(2)>';

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    $this->actingAs(User::query()->where('login', 'admin@admin.com')->first());
});

function assertNoStoredXss($response): void
{
    $response->assertOk();
    $response->assertDontSee('<script>alert(1)', false);
    $response->assertDontSee('<img src=x onerror', false);
    $response->assertDontSee('</textarea><script>', false);
}

test('data processing show page purifies rich fields', function () {
    $dataProcessing = DataProcessing::factory()->create();
    $dataProcessing->forceFill(['responsible' => STORED_XSS_PAYLOAD, 'retention' => STORED_XSS_PAYLOAD])->saveQuietly();

    assertNoStoredXss($this->get(route('admin.data-processings.show', $dataProcessing)));
});

test('data processing edit page escapes textarea content', function () {
    $dataProcessing = DataProcessing::factory()->create();
    $dataProcessing->forceFill(['responsible' => STORED_XSS_PAYLOAD])->saveQuietly();

    $response = $this->get(route('admin.data-processings.edit', $dataProcessing));

    assertNoStoredXss($response);
    $response->assertSee(e(STORED_XSS_PAYLOAD), false);
});

test('information index page escapes owner and purifies description', function () {
    $information = Information::factory()->create();
    $information->forceFill(['owner' => STORED_XSS_PAYLOAD, 'description' => STORED_XSS_PAYLOAD])->saveQuietly();

    assertNoStoredXss($this->get(route('admin.information.index')));
});

test('network index page escapes protocol type', function () {
    $network = Network::factory()->create();
    $network->forceFill(['protocol_type' => STORED_XSS_PAYLOAD])->saveQuietly();

    assertNoStoredXss($this->get(route('admin.networks.index')));
});

test('global search purifies result cells', function () {
    $network = Network::factory()->create(['name' => 'xssprobe']);
    $network->forceFill(['description' => 'xssprobe '.STORED_XSS_PAYLOAD])->saveQuietly();

    assertNoStoredXss($this->get(route('admin.globalSearch', ['search' => 'xssprobe'])));
});

test('import sanitizes attributes like the web forms', function () {
    $network = Network::factory()->create();

    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray([
        ['id', 'name', 'description', 'protocol_type'],
        [$network->id, $network->name, '<p>ok</p><script>alert(1)</script>', '<b>IPv4</b><script>alert(2)</script>'],
    ]);
    $path = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    $this->post(route('admin.config.import'), [
        'object' => 'Network',
        'file' => new UploadedFile($path, 'Network.xlsx', null, null, true),
    ])->assertSessionDoesntHaveErrors();

    $network->refresh();
    expect($network->description)->toBe('<p>ok</p>')
        ->and($network->protocol_type)->toBe('IPv4alert(2)');
});

test('lan index page escapes plain-text description', function () {
    $lan = Lan::factory()->create();
    $lan->forceFill(['description' => STORED_XSS_PAYLOAD])->saveQuietly();

    assertNoStoredXss($this->get(route('admin.lans.index')));
});

test('workstation pages escape plain-text fields', function () {
    $workstation = Workstation::factory()->create();
    $workstation->forceFill([
        'type' => STORED_XSS_PAYLOAD, 'status' => STORED_XSS_PAYLOAD, 'serial_number' => STORED_XSS_PAYLOAD,
        'mac_address' => STORED_XSS_PAYLOAD, 'network_port_type' => STORED_XSS_PAYLOAD,
    ])->saveQuietly();

    assertNoStoredXss($this->get(route('admin.workstations.index')));
    assertNoStoredXss($this->get(route('admin.workstations.show', $workstation)));
});

test('relation show page purifies comments', function () {
    $relation = Relation::factory()->create();
    $relation->forceFill(['comments' => STORED_XSS_PAYLOAD])->saveQuietly();

    assertNoStoredXss($this->get(route('admin.relations.show', $relation)));
});
