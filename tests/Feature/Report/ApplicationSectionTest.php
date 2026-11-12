<?php

use App\Models\AdminUser;
use App\Models\Application;
use App\Models\ApplicationBlock;
use App\Models\ApplicationEvent;
use App\Models\ApplicationFlow;
use App\Models\ApplicationModule;
use App\Models\ApplicationService;
use App\Models\Database;
use App\Models\User;
use App\Services\Report\ApplicationSection;
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
 * Also returns the size of every embedded word/media/*.png entry: PhpWord's Media registry
 * dedupes images sharing an identical source path (e.g. every Application with no custom icon
 * resolves to the same fallback icon file), so the media count reflects distinct sources actually
 * embedded, not one-per-object.
 *
 * @return array{0: string, 1: string, 2: array<int, int>} [document.xml, document.xml.rels, media PNG sizes]
 */
function renderApplicationSectionXml(array $selectedVues = ['3']): array
{
    $helper = new WordHelper;
    $phpWord = $helper->newDocument();
    $section = $phpWord->addSection();

    (new ApplicationSection)->build($section, $helper, $selectedVues);

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

describe('ApplicationSection content', function () {
    test('renders the full block-to-database chain with flows', function () {
        $block = ApplicationBlock::factory()->create(['name' => 'ERP Suite']);
        $application = Application::factory()->create(['name' => 'ERP Core', 'application_block_id' => $block->id]);
        $service = ApplicationService::factory()->create(['name' => 'Invoicing Service']);
        $module = ApplicationModule::factory()->create(['name' => 'Billing Module']);
        $database = Database::factory()->create(['name' => 'ERP Database']);

        $application->services()->attach($service->id);
        $service->modules()->attach($module->id);
        $application->databases()->attach($database->id);

        $flow = ApplicationFlow::factory()->create([
            'name' => 'ERP to Database Sync',
            'application_source_id' => $application->id,
            'database_dest_id' => $database->id,
        ]);

        [$xml] = renderApplicationSectionXml();

        expect($xml)
            ->toContain('ERP Suite')
            ->toContain('ERP Core')
            ->toContain('Invoicing Service')
            ->toContain('Billing Module')
            ->toContain('ERP Database')
            ->toContain('ERP to Database Sync')
            ->toContain($block->getUID())
            ->toContain($application->getUID())
            ->toContain($service->getUID())
            ->toContain($module->getUID())
            ->toContain($database->getUID())
            ->toContain($flow->getUID())
            ->not->toContain('BPMN');
    });

    test('renders Application administrators, RTO/RPO durations and event history as a server-side sub-table', function () {
        $application = Application::factory()->create([
            'name' => 'HR Platform',
            'rto' => 90, // 1 hour, 30 minutes
            'rpo' => 1500, // 1 day, 1 hour
            'documentation' => 'https://docs.example.com/hr-platform',
        ]);
        $admin = AdminUser::factory()->create(['user_id' => 'jdoe']);
        $application->administrators()->attach($admin->id);

        $eventUser = User::factory()->create(['name' => 'Alice Admin']);
        ApplicationEvent::factory()->create([
            'application_id' => $application->id,
            'user_id' => $eventUser->id,
            'message' => 'Upgraded to v2',
        ]);

        [$xml, $rels] = renderApplicationSectionXml();

        expect($xml)
            ->toContain('HR Platform')
            ->toContain('jdoe')
            ->toContain('Alice Admin')
            ->toContain('Upgraded to v2')
            ->toContain('<w:hyperlink');
        expect($rels)->toContain('https://docs.example.com/hr-platform');
    });

    test('renders a standalone ApplicationFlow with polymorphic source/destination type tags', function () {
        $application = Application::factory()->create(['name' => 'Billing App']);
        $service = ApplicationService::factory()->create(['name' => 'Payment Service']);

        $flow = ApplicationFlow::factory()->create([
            'name' => 'Billing to Payment',
            'application_source_id' => $application->id,
            'service_dest_id' => $service->id,
            'crypted' => true,
        ]);

        [$xml] = renderApplicationSectionXml();

        expect($xml)
            ->toContain('Billing to Payment')
            ->toContain('Billing App')
            ->toContain('[Application]')
            ->toContain('Payment Service')
            ->toContain('[Service]')
            ->toContain($flow->getUID());
    });

    test('renders one graph per ApplicationBlock plus one extra graph for unassigned applications', function () {
        $blockA = ApplicationBlock::factory()->create(['name' => 'Block A']);
        $appA = Application::factory()->create(['name' => 'App A', 'application_block_id' => $blockA->id]);
        $database = Database::factory()->create(['name' => 'Database D']);
        $appA->databases()->attach($database->id);
        ApplicationFlow::factory()->create([
            'application_source_id' => $appA->id,
            'database_dest_id' => $database->id,
        ]);

        $blockB = ApplicationBlock::factory()->create(['name' => 'Block B']);
        Application::factory()->create(['name' => 'App B', 'application_block_id' => $blockB->id]);

        Application::factory()->create(['name' => 'App C (unassigned)']);

        [, , $mediaSizes] = renderApplicationSectionXml();

        // Block A gets a structure graph + a flow graph (its App->Database flow has both endpoints
        // in scope): 2 PNGs. Block B gets a structure graph only: 1 PNG. The unassigned application
        // gets its own trailing structure graph: 1 PNG. Total graphs: 4. Applications share the same
        // default icon fallback (1 deduped entry) and Database has its own fallback (1 more): 2 icons.
        expect($mediaSizes)->toHaveCount(6);
    });
});
