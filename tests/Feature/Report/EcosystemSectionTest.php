<?php

use App\Models\Application;
use App\Models\Database;
use App\Models\Document;
use App\Models\Entity;
use App\Models\Process;
use App\Models\Relation;
use App\Models\RelationValue;
use App\Models\User;
use App\Services\Report\EcosystemSection;
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
 * Renders EcosystemSection into a standalone document and returns word/document.xml plus the
 * document relationships part (where external hyperlink targets actually live), so tests can
 * assert on its content without going through the full ReportBuilder/HTTP stack. Also returns the
 * size of every embedded word/media/*.png entry: PhpWord's Media registry dedupes images sharing
 * an identical source path (e.g. entities with no custom icon all resolve to the same fallback
 * icon file), so the media count reflects distinct sources actually embedded, not one-per-object.
 *
 * @return array{0: string, 1: string, 2: array<int, int>} [document.xml, document.xml.rels, media PNG sizes]
 */
function renderEcosystemSectionXml(array $selectedVues = ['1']): array
{
    $helper = new WordHelper;
    $phpWord = $helper->newDocument();
    $section = $phpWord->addSection();

    (new EcosystemSection)->build($section, $helper, $selectedVues);

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

describe('EcosystemSection content', function () {
    test('renders Entity fields and relations', function () {
        $parent = Entity::factory()->create(['name' => 'Parent Holding', 'type' => 'Group']);
        $child = Entity::factory()->create([
            'name' => 'Child Subsidiary',
            'parent_entity_id' => $parent->id,
            'description' => '<p>Some description</p>',
        ]);
        $process = Process::factory()->create(['name' => 'Order Processing']);
        $parent->processes()->attach($process->id);
        $application = Application::factory()->create(['name' => 'Billing App', 'entity_resp_id' => $parent->id]);
        $database = Database::factory()->create(['name' => 'Billing DB', 'entity_resp_id' => $parent->id]);
        $relation = Relation::factory()->create([
            'name' => 'Supply Contract',
            'source_id' => $parent->id,
            'destination_id' => $child->id,
        ]);

        // Vues 2 (Process) and 3 (Application/Database) selected alongside vue 1, so the
        // "processes"/"exploits" relations resolve to internal links rather than plain text.
        [$xml] = renderEcosystemSectionXml(['1', '2', '3']);

        expect($xml)
            ->toContain('Parent Holding')
            ->toContain('Child Subsidiary')
            ->toContain('Group')
            ->toContain('Order Processing')
            ->toContain('Billing App')
            ->toContain('Billing DB')
            ->toContain('Supply Contract')
            ->toContain($parent->getUID())
            ->toContain($child->getUID())
            ->toContain($process->getUID())
            ->toContain($application->getUID())
            ->toContain($database->getUID());
    });

    test('renders a Relation with contract terms, values history and document attachments', function () {
        $source = Entity::factory()->create(['name' => 'Source Co']);
        $destination = Entity::factory()->create(['name' => 'Destination Co']);
        $relation = Relation::factory()->create([
            'name' => 'Service Agreement',
            'source_id' => $source->id,
            'destination_id' => $destination->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'active' => true,
            'importance' => 3,
            'comments' => '<p>Renewed annually</p>',
        ]);
        RelationValue::factory()->create(['relation_id' => $relation->id, 'price' => 1234.5]);
        $document = Document::factory()->create(['filename' => 'contract.pdf']);
        $relation->documents()->attach($document->id);

        [$xml, $rels] = renderEcosystemSectionXml();

        expect($xml)
            ->toContain('Service Agreement')
            ->toContain('Source Co')
            ->toContain('Destination Co')
            ->toContain('Renewed annually')
            ->toContain('contract.pdf')
            ->toContain('<w:hyperlink');
        expect($rels)->toContain(route('admin.documents.show', $document->id));
    });

    test('omits internal links to objects outside the selected vues', function () {
        $entity = Entity::factory()->create(['name' => 'Solo Entity']);
        $process = Process::factory()->create(['name' => 'Unlinked Process']);
        $entity->processes()->attach($process->id);

        [$xmlWithVue2] = renderEcosystemSectionXml(['1', '2']);
        [$xmlWithoutVue2] = renderEcosystemSectionXml(['1']);

        expect($xmlWithVue2)->toContain($process->getUID());
        expect($xmlWithoutVue2)
            ->toContain('Unlinked Process')
            ->not->toContain($process->getUID());
    });

    test('renders one family graph per Entity that has a parent or children, skipping isolated entities', function () {
        $parent = Entity::factory()->create(['name' => 'Parent Holding']);
        $child = Entity::factory()->create(['name' => 'Child Subsidiary', 'parent_entity_id' => $parent->id]);
        Entity::factory()->create(['name' => 'Solo Entity']);

        [, , $mediaSizes] = renderEcosystemSectionXml();

        // Parent (has a child) and Child (has a parent) each get their own family graph — 2 unique
        // graph PNGs. Solo has neither, so it's skipped. All three entities share the same default
        // icon fallback path (icon_id is null), which PhpWord dedupes into a single media entry.
        expect($mediaSizes)->toHaveCount(3);
    });
});
