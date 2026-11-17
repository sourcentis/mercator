<?php

use App\Models\Activity;
use App\Models\ActivityImpact;
use App\Models\Actor;
use App\Models\Application;
use App\Models\Entity;
use App\Models\Information;
use App\Models\MacroProcessus;
use App\Models\Operation;
use App\Models\Process;
use App\Models\Task;
use App\Models\User;
use App\Services\Report\InformationSystemSection;
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
 * dedupes images sharing an identical source path (e.g. every Process with no custom icon
 * resolves to the same fallback icon file), so the media count reflects distinct sources actually
 * embedded, not one-per-object.
 *
 * @return array{0: string, 1: string, 2: array<int, int>} [document.xml, document.xml.rels, media PNG sizes]
 */
function renderInformationSystemSectionXml(array $selectedVues = ['2']): array
{
    $helper = new WordHelper;
    $phpWord = $helper->newDocument();
    $section = $phpWord->addSection();

    (new InformationSystemSection)->build($section, $helper, $selectedVues);

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

describe('InformationSystemSection content', function () {
    test('renders the full macroprocess-to-information chain with security_need and BPMN omitted', function () {
        $macroProcess = MacroProcessus::factory()->create(['name' => 'Order to Cash', 'owner' => 'Finance Dept']);
        $process = Process::factory()->create(['name' => 'Invoice Processing', 'macroprocess_id' => $macroProcess->id]);
        $activity = Activity::factory()->create(['name' => 'Validate Invoice']);
        $process->activities()->attach($activity->id);
        $operation = Operation::factory()->create(['name' => 'Check Amount', 'process_id' => $process->id]);
        $activity->operations()->attach($operation->id);
        $task = Task::factory()->create(['name' => 'Manual Review']);
        $operation->tasks()->attach($task->id);
        $actor = Actor::factory()->create(['name' => 'Accountant', 'contact' => 'accountant@example.com']);
        $operation->actors()->attach($actor->id);
        $information = Information::factory()->create(['name' => 'Invoice Amount']);
        $process->information()->attach($information->id);
        $application = Application::factory()->create(['name' => 'ERP System']);
        $process->applications()->attach($application->id);
        $entity = Entity::factory()->create(['name' => 'Accounting Entity']);
        $process->entities()->attach($entity->id);

        [$xml] = renderInformationSystemSectionXml(['1', '2', '3']);

        expect($xml)
            ->toContain('Order to Cash')
            ->toContain('Finance Dept')
            ->toContain('Invoice Processing')
            ->toContain('Validate Invoice')
            ->toContain('Check Amount')
            ->toContain('Manual Review')
            ->toContain('accountant@example.com')
            ->toContain('Invoice Amount')
            ->toContain('ERP System')
            ->toContain('Accounting Entity')
            ->toContain($macroProcess->getUID())
            ->toContain($process->getUID())
            ->toContain($activity->getUID())
            ->toContain($operation->getUID())
            ->toContain($task->getUID())
            ->toContain($actor->getUID())
            ->toContain($information->getUID())
            ->toContain($application->getUID())
            ->toContain($entity->getUID())
            ->not->toContain('BPMN');
    });

    test('renders Activity BIA durations, nested impacts table and a DRP hyperlink', function () {
        $activity = Activity::factory()->create([
            'name' => 'Restore Backup',
            'maximum_tolerable_downtime' => 1500, // 1 day, 1 hour
            'recovery_time_objective' => 90, // 1 hour, 30 minutes
            'drp' => '<p>Follow the runbook</p>',
            'drp_link' => 'https://runbook.example.com/restore',
        ]);
        ActivityImpact::factory()->create(['activity_id' => $activity->id, 'impact_type' => 'Financial', 'severity' => 3]);

        [$xml, $rels] = renderInformationSystemSectionXml();

        expect($xml)
            ->toContain('Restore Backup')
            ->toContain('Follow the runbook')
            ->toContain('Financial')
            ->toContain('<w:hyperlink');
        expect($rels)->toContain('https://runbook.example.com/restore');
    });

    test('renders Information hierarchy and GDPR constraints', function () {
        $parent = Information::factory()->create(['name' => 'Personal Data']);
        $child = Information::factory()->create(['name' => 'Email Address', 'sensitivity' => 'High']);
        $parent->children()->attach($child->id);
        $child->update(['constraints' => '<p>GDPR applies</p>']);

        [$xml] = renderInformationSystemSectionXml();

        expect($xml)
            ->toContain('Personal Data')
            ->toContain('Email Address')
            ->toContain('High')
            ->toContain('GDPR applies')
            ->toContain($parent->getUID())
            ->toContain($child->getUID());
    });

    test('renders type and attributes for a fully populated Activity, Task and Actor', function () {
        Activity::factory()->create(['name' => 'Onboard Employee', 'type' => 'RH', 'attributes' => 'critique urgent']);
        Task::factory()->create(['name' => 'Sign Contract', 'type' => 'Administratif', 'attributes' => 'papier signature']);
        Actor::factory()->create(['name' => 'HR Manager', 'type' => 'interne', 'attributes' => 'decideur valide']);

        [$xml] = renderInformationSystemSectionXml();

        expect($xml)
            ->toContain(trans('cruds.activity.fields.type'))
            ->toContain('RH')
            ->toContain('critique, urgent')
            ->toContain(trans('cruds.task.fields.type'))
            ->toContain('Administratif')
            ->toContain('papier, signature')
            ->toContain(trans('cruds.actor.fields.attributes'))
            ->toContain('decideur, valide');
    });

    test('renders one graph per MacroProcessus that has processes, skipping macro-processus with none', function () {
        $macroProcessWithProcess = MacroProcessus::factory()->create(['name' => 'Order to Cash']);
        Process::factory()->create(['name' => 'Invoice Processing', 'macroprocess_id' => $macroProcessWithProcess->id]);
        MacroProcessus::factory()->create(['name' => 'Empty Macro Process']);

        [, , $mediaSizes] = renderInformationSystemSectionXml();

        // Only "Order to Cash" has processes, so it's the only one that gets a graph (1 PNG).
        // The single Process also renders its own icon row, defaulting to the same fallback icon
        // path for every process — deduped by PhpWord into 1 more media entry. Total: 2.
        expect($mediaSizes)->toHaveCount(2);
    });
});
