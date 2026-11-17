<?php

use App\Models\Cluster;
use App\Models\Gateway;
use App\Models\LogicalFlow;
use App\Models\LogicalServer;
use App\Models\Network;
use App\Models\Subnetwork;
use App\Models\User;
use App\Models\Vlan;
use App\Services\Report\LogicalInfrastructureSection;
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
function renderLogicalInfrastructureSectionXml(array $selectedVues = ['5']): array
{
    $helper = new WordHelper;
    $phpWord = $helper->newDocument();
    $section = $phpWord->addSection();

    (new LogicalInfrastructureSection)->build($section, $helper, $selectedVues);

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

describe('LogicalInfrastructureSection content', function () {
    test('renders the network-to-subnetwork chain with gateway, vlan and addressing details', function () {
        $network = Network::factory()->create(['name' => 'Core Network']);
        $gateway = Gateway::factory()->create(['name' => 'Edge Gateway']);
        $vlan = Vlan::factory()->create(['name' => 'VLAN 100', 'vlan_id' => 100]);
        $subnetwork = Subnetwork::factory()->create([
            'name' => 'Core Subnet',
            'address' => '10.0.0.0/24',
            'network_id' => $network->id,
            'gateway_id' => $gateway->id,
            'vlan_id' => $vlan->id,
        ]);

        [$xml] = renderLogicalInfrastructureSectionXml();

        expect($xml)
            ->toContain('Core Network')
            ->toContain('Edge Gateway')
            ->toContain('VLAN 100')
            ->toContain('Core Subnet')
            ->toContain('10.0.0.0/24')
            ->toContain($network->getUID())
            ->toContain($gateway->getUID())
            ->toContain($vlan->getUID())
            ->toContain($subnetwork->getUID())
            ->not->toContain('BPMN');
    });

    test('renders LogicalServer disk usage percentage and the backup_show-gated backup history sub-table', function () {
        $logicalServer = LogicalServer::factory()->create([
            'name' => 'DB Host',
            'disk' => 100,
            'disk_used' => 42,
        ]);
        $logicalServer->backups()->create([
            'name' => 'Nightly Backup',
            'backup_frequency' => 1,
            'backup_cycle' => 2,
            'backup_retention' => 30,
        ]);

        [$xml] = renderLogicalInfrastructureSectionXml();

        expect($xml)
            ->toContain('DB Host')
            ->toContain('42 / 100')
            ->toContain('42.00 %')
            ->toContain('Nightly Backup')
            ->toContain($logicalServer->getUID());
    });

    test('renders a standalone LogicalFlow endpoint resolved via the subnetwork polymorphic relation', function () {
        $subnetworkEndpoint = Subnetwork::factory()->create(['name' => 'Edge Subnet', 'address' => '10.1.0.0/24']);
        $cluster = Cluster::factory()->create(['name' => 'Web Cluster', 'address_ip' => '10.1.0.5']);

        $flow = LogicalFlow::factory()->create([
            'name' => 'Edge Flow',
            'source_ip_range' => null,
            'logical_server_source_id' => null,
            'peripheral_source_id' => null,
            'physical_server_source_id' => null,
            'storage_device_source_id' => null,
            'workstation_source_id' => null,
            'physical_security_device_source_id' => null,
            'subnetwork_source_id' => $subnetworkEndpoint->id,
            'dest_ip_range' => null,
            'logical_server_dest_id' => null,
            'peripheral_dest_id' => null,
            'physical_server_dest_id' => null,
            'storage_device_dest_id' => null,
            'workstation_dest_id' => null,
            'physical_security_device_dest_id' => null,
            'cluster_dest_id' => $cluster->id,
        ]);

        [$xml] = renderLogicalInfrastructureSectionXml();

        expect($xml)
            ->toContain('Edge Flow')
            ->toContain('Edge Subnet')
            ->toContain('10.1.0.0/24')
            ->toContain('Web Cluster')
            ->toContain('10.1.0.5')
            ->toContain($flow->getUID())
            ->toContain($subnetworkEndpoint->getUID())
            ->toContain($cluster->getUID());
    });

    test('renders one graph per Network when several networks are visible', function () {
        $networkA = Network::factory()->create(['name' => 'Network A']);
        Subnetwork::factory()->create(['name' => 'Subnet A', 'network_id' => $networkA->id]);
        $networkB = Network::factory()->create(['name' => 'Network B']);
        Subnetwork::factory()->create(['name' => 'Subnet B', 'network_id' => $networkB->id]);

        [, , $mediaSizes] = renderLogicalInfrastructureSectionXml();

        // 2 networks visible -> splits per Network, not per Subnetwork: 1 graph each, no icons
        // triggered by this scenario (no LogicalServer/Cluster/etc.).
        expect($mediaSizes)->toHaveCount(2);
    });

    test('renders one graph per Subnetwork when exactly one Network is visible', function () {
        $network = Network::factory()->create(['name' => 'Only Network']);
        Subnetwork::factory()->create(['name' => 'Subnet A', 'network_id' => $network->id]);
        Subnetwork::factory()->create(['name' => 'Subnet B', 'network_id' => $network->id]);

        [, , $mediaSizes] = renderLogicalInfrastructureSectionXml();

        // Exactly 1 network visible -> falls back to splitting per Subnetwork instead: 1 graph
        // per subnetwork (2), no icons triggered by this scenario.
        expect($mediaSizes)->toHaveCount(2);
    });
});
