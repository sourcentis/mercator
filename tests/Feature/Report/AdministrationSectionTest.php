<?php

use App\Models\AdminUser;
use App\Models\Annuaire;
use App\Models\Domain;
use App\Models\ForestAd;
use App\Models\LogicalServer;
use App\Models\User;
use App\Models\ZoneAdmin;
use App\Services\Report\AdministrationSection;
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
function renderAdministrationSectionXml(array $selectedVues = ['4']): array
{
    $helper = new WordHelper;
    $phpWord = $helper->newDocument();
    $section = $phpWord->addSection();

    (new AdministrationSection)->build($section, $helper, $selectedVues);

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

describe('AdministrationSection content', function () {
    test('renders the full zone-admin to forest-to-domain chain', function () {
        $zoneAdmin = ZoneAdmin::factory()->create(['name' => 'EMEA Zone']);
        $annuaire = Annuaire::factory()->create(['name' => 'Central LDAP', 'zone_admin_id' => $zoneAdmin->id]);
        $forestAd = ForestAd::factory()->create(['name' => 'Corp Forest', 'zone_admin_id' => $zoneAdmin->id]);
        $domain = Domain::factory()->create(['name' => 'corp.example.com']);
        $forestAd->domains()->attach($domain->id);
        $logicalServer = LogicalServer::factory()->create(['name' => 'DC01', 'domain_id' => $domain->id]);

        [$xml] = renderAdministrationSectionXml(['4', '5']);

        expect($xml)
            ->toContain('EMEA Zone')
            ->toContain('Central LDAP')
            ->toContain('Corp Forest')
            ->toContain('corp.example.com')
            ->toContain('DC01')
            ->toContain($zoneAdmin->getUID())
            ->toContain($annuaire->getUID())
            ->toContain($forestAd->getUID())
            ->toContain($domain->getUID())
            ->toContain($logicalServer->getUID())
            ->not->toContain('BPMN');
    });

    test('renders AdminUser using user_id as the page title, with attributes as a comma list and a domain link', function () {
        $domain = Domain::factory()->create(['name' => 'corp.example.com']);
        $adminUser = AdminUser::factory()->create([
            'user_id' => 'jdupont',
            'firstname' => 'Jean',
            'lastname' => 'Dupont',
            'attributes' => 'ldap sudo',
            'domain_id' => $domain->id,
        ]);

        [$xml] = renderAdministrationSectionXml(['4', '5']);

        expect($xml)
            ->toContain('jdupont')
            ->toContain('Jean')
            ->toContain('Dupont')
            ->toContain('ldap, sudo')
            ->toContain('corp.example.com')
            ->toContain($adminUser->getUID())
            ->toContain($domain->getUID());
    });

    test('renders a single, unsplit graph regardless of how many ZoneAdmin/ForestAd/Domain records exist', function () {
        $zoneAdminA = ZoneAdmin::factory()->create(['name' => 'EMEA Zone']);
        $forestA = ForestAd::factory()->create(['name' => 'Forest A', 'zone_admin_id' => $zoneAdminA->id]);
        ForestAd::factory()->create(['name' => 'Forest B', 'zone_admin_id' => $zoneAdminA->id]);
        $zoneAdminB = ZoneAdmin::factory()->create(['name' => 'APAC Zone']);
        ForestAd::factory()->create(['name' => 'Forest C', 'zone_admin_id' => $zoneAdminB->id]);
        $domain = Domain::factory()->create(['name' => 'corp.example.com']);
        $forestA->domains()->attach($domain->id);

        [, , $mediaSizes] = renderAdministrationSectionXml();

        // AdministrationSection deliberately does NOT split by grouping element (unlike every
        // other vue) — always exactly one global graph, regardless of how many zone admins/
        // forests/domains exist.
        expect($mediaSizes)->toHaveCount(1);
    });
});
