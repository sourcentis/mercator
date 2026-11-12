<?php

use App\Models\Application;
use App\Models\DataProcessing;
use App\Models\Document;
use App\Models\SecurityControl;
use App\Models\User;
use App\Services\Report\GdprSection;
use App\Services\Report\WordHelper;
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

    $this->actingAs(User::query()->where('login', 'admin@admin.com')->first());
});

/**
 * @return array{0: string, 1: string, 2: array<int, int>} [document.xml, document.xml.rels, media PNG sizes]
 */
function renderGdprSectionXml(array $selectedVues = ['7']): array
{
    $helper = new WordHelper;
    $phpWord = $helper->newDocument();
    $section = $phpWord->addSection();

    (new GdprSection)->build($section, $helper, $selectedVues);

    $path = tempnam(sys_get_temp_dir(), 'docx');
    WordHelper::saveDocx($phpWord, $path);
    $helper->cleanupTempFiles();

    $zip = new ZipArchive;
    $zip->open($path);
    $xml = $zip->getFromName('word/document.xml');
    $rels = $zip->getFromName('word/_rels/document.xml.rels');
    $mediaSizes = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if ($name !== false && str_starts_with($name, 'word/media/') && str_ends_with($name, '.png')) {
            $mediaSizes[] = $zip->statIndex($i)['size'];
        }
    }
    $zip->close();
    unlink($path);

    return [$xml, $rels, $mediaSizes];
}

describe('GdprSection content', function () {
    test('renders DataProcessing lawfulness bases, update_date, and linked applications/documents', function () {
        $application = Application::factory()->create(['name' => 'Payroll App']);
        $document = Document::factory()->create(['filename' => 'privacy-notice.pdf']);
        $dataProcessing = DataProcessing::factory()->create([
            'name' => 'Payroll Processing',
            'lawfulness_consent' => true,
            'lawfulness_contract' => true,
            'lawfulness_vital_interest' => false,
            'update_date' => '2026-03-15',
        ]);
        $dataProcessing->applications()->attach($application->id);
        $dataProcessing->documents()->attach($document->id);

        [$xml, $rels] = renderGdprSectionXml(['7', '3']);

        expect($xml)
            ->toContain('Payroll Processing')
            ->toContain('Payroll App')
            ->toContain('privacy-notice.pdf')
            ->toContain('15-03-2026')
            ->toContain($dataProcessing->getUID())
            ->toContain($application->getUID())
            ->not->toContain('BPMN');

        $consentLabel = trans('cruds.dataProcessing.fields.lawfulness_consent');
        $vitalInterestLabel = trans('cruds.dataProcessing.fields.lawfulness_vital_interest');
        expect($xml)->toContain($consentLabel);
        expect($xml)->not->toContain($vitalInterestLabel);
    });

    test('renders SecurityControl with only name/description and an ad hoc bookmark (no getUID)', function () {
        $securityControl = SecurityControl::factory()->create(['name' => 'Access Control Policy']);

        [$xml] = renderGdprSectionXml();

        expect($xml)
            ->toContain('Access Control Policy')
            ->toContain('SECURITY_CONTROL_'.$securityControl->id);
    });

    test('renders one graph per DataProcessing (registre), skipping registres with neither processes nor applications', function () {
        $appA = Application::factory()->create(['name' => 'App A']);
        $dpWithApp = DataProcessing::factory()->create(['name' => 'Registre with an application']);
        $dpWithApp->applications()->attach($appA->id);

        $appB = Application::factory()->create(['name' => 'App B']);
        $dpWithOtherApp = DataProcessing::factory()->create(['name' => 'Registre with another application']);
        $dpWithOtherApp->applications()->attach($appB->id);

        DataProcessing::factory()->create(['name' => 'Registre with no links']);

        [, , $mediaSizes] = renderGdprSectionXml();

        // GdprSection has no icon rows at all, so the media count is purely the graph count: one
        // per DataProcessing that has processes or applications (2), skipping the one with neither.
        expect($mediaSizes)->toHaveCount(2);
    });
});
