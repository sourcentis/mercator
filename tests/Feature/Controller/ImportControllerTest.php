
<?php

use App\Models\Network;
use App\Models\Perimeter;
use App\Models\Permission;
use App\Models\PhysicalLink;
use App\Models\Role;
use App\Models\User;
use App\Support\PerimeterSettings;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

describe('import', function () {

    test('can display import page', function () {

        $response = $this->get(route('admin.config.import'));

        $response->assertOk();
        $response->assertViewIs('admin.import');
    });

    test('can export entities', function () {

        $response = $this->post(
            route('admin.config.export'),
            ['object' => 'Entity']);

        $response->assertOk();
        $response->assertHeader('Content-Disposition');

        $disposition = $response->headers->get('Content-Disposition');
        expect($disposition)->toBeString()
            ->and(strtolower($disposition))->toContain('attachment;')
            ->and(strtolower($disposition))->toContain('filename=')
            ->and(strtolower($disposition))->toContain('.xlsx');

        $base = $response->baseResponse;
        expect($base)->toBeInstanceOf(BinaryFileResponse::class);

        $path = $base->getFile()->getPathname();
        expect(is_file($path))->toBeTrue();

        $content = file_get_contents($path);
        expect(strlen($content))->toBeGreaterThan(100);

        // DOCX/XLSX sont des ZIP → début "PK"
        expect(substr($content, 0, 2))->toBe('PK');
    });

    test('can reimport physical links exported with attributes', function () {

        $link = PhysicalLink::factory()->create([
            'type' => 'Ethernet',
            'attributes' => 'tag1 tag2',
        ]);

        $exportResponse = $this->post(
            route('admin.config.export'),
            ['object' => 'PhysicalLink']);

        $exportResponse->assertOk();

        $path = $exportResponse->baseResponse->getFile()->getPathname();
        $uploadedFile = new UploadedFile($path, 'PhysicalLink.xlsx', null, null, true);

        $importResponse = $this->post(
            route('admin.config.import'),
            ['object' => 'PhysicalLink', 'file' => $uploadedFile]);

        $importResponse->assertSessionDoesntHaveErrors();

        $link->refresh();
        expect($link->attributes)->toBe('tag1 tag2');
    });

});

describe('delete', function () {
    // Une ligne ne contenant que l'id supprime l'enregistrement
    function importNetworkDeletion(int $id): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([['id', 'name'], [$id, null]]);
        $path = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'Network.xlsx', null, null, true);
    }

    /**
     * @param  array<int, list<string>>  $permissionsByPerimeter
     */
    function actingAsNetworkUser(array $permissionsByPerimeter): void
    {
        $user = User::factory()->create();
        foreach ($permissionsByPerimeter as $perimeterId => $permissions) {
            $role = Role::query()->create(['title' => 'Network import '.$perimeterId, 'perimeter_id' => $perimeterId]);
            $role->permissions()->sync(Permission::query()->whereIn('title', $permissions)->pluck('id'));
            $user->roles()->attach($role->id);
        }
        test()->actingAs($user);
    }

    test('cannot delete through import without delete permission', function () {
        $network = Network::factory()->create();
        actingAsNetworkUser([Perimeter::DEFAULT_ID => ['network_access', 'network_edit']]);

        $this->post(route('admin.config.import'), ['object' => 'Network', 'file' => importNetworkDeletion($network->id)])
            ->assertSessionHasErrors();

        expect(Network::query()->find($network->id))->not->toBeNull();
    });

    test('can delete through import with delete permission', function () {
        $network = Network::factory()->create();
        actingAsNetworkUser([Perimeter::DEFAULT_ID => ['network_access', 'network_edit', 'network_delete']]);

        $this->post(route('admin.config.import'), ['object' => 'Network', 'file' => importNetworkDeletion($network->id)])
            ->assertSessionDoesntHaveErrors();

        expect(Network::query()->find($network->id))->toBeNull();
    });
});

describe('update', function () {
    function importNetworkRename(Network $network, string $name): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([['id', 'name'], [$network->id, $name]]);
        $path = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'Network.xlsx', null, null, true);
    }

    beforeEach(function () {
        PerimeterSettings::setEnabled(true);
        $this->perimeterB = Perimeter::factory()->create();
        // Lecteur dans le périmètre par défaut, rédacteur dans le périmètre B
        actingAsNetworkUser([
            Perimeter::DEFAULT_ID => ['network_access', 'network_show'],
            $this->perimeterB->id => ['network_access', 'network_show', 'network_edit'],
        ]);
    });

    afterEach(fn () => PerimeterSettings::setEnabled(false));

    test('cannot update through import a read-only object of another perimeter', function () {
        $network = Network::factory()->create(['name' => 'Original']);
        DB::table('networks')->where('id', $network->id)->update(['perimeter_id' => Perimeter::DEFAULT_ID]);

        $this->post(route('admin.config.import'), ['object' => 'Network', 'file' => importNetworkRename($network, 'Renamed')])
            ->assertSessionHasErrors();

        expect($network->refresh()->name)->toBe('Original');
    });

    test('can update through import an object inside the edit perimeter', function () {
        $network = Network::factory()->create(['name' => 'Original']);
        DB::table('networks')->where('id', $network->id)->update(['perimeter_id' => $this->perimeterB->id]);

        $this->post(route('admin.config.import'), ['object' => 'Network', 'file' => importNetworkRename($network, 'Renamed')])
            ->assertSessionDoesntHaveErrors();

        expect($network->refresh()->name)->toBe('Renamed');
    });
});
