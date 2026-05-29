<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoadTestSeeder extends Seeder
{
    private const ENTITY_COUNT = 2000;
    private const FLOW_COUNT = 8000;
    private const CHUNK_SIZE = 500;

    private const PROTOCOLS = ['TCP', 'UDP', 'ICMP', 'HTTPS', 'SSH', 'RDP', 'SNMP', 'FTP', 'SMTP', 'DNS'];
    private const PORTS = ['22', '25', '53', '80', '161', '389', '443', '3306', '3389', '5432', '8080', '8443'];

    private const SOURCE_FIELDS = [
        'workstation'    => 'workstation_source_id',
        'logical_server' => 'logical_server_source_id',
        'physical_server' => 'physical_server_source_id',
        'peripheral'     => 'peripheral_source_id',
    ];

    private const DEST_FIELDS = [
        'workstation'    => 'workstation_dest_id',
        'logical_server' => 'logical_server_dest_id',
        'physical_server' => 'physical_server_dest_id',
        'peripheral'     => 'peripheral_dest_id',
    ];

    private \Faker\Generator $faker;

    public function run(): void
    {
        $this->faker = $faker = \Faker\Factory::create();

        $now = now()->toDateTimeString();

        $workstationIds   = $this->seedWorkstations($now);
        $logicalServerIds = $this->seedLogicalServers($now);
        $physicalServerIds = $this->seedPhysicalServers($now);
        $peripheralIds    = $this->seedPeripherals($now);

        $this->seedLogicalFlows($now, $workstationIds, $logicalServerIds, $physicalServerIds, $peripheralIds);

        $this->command->info('LoadTestSeeder terminé : ' . self::ENTITY_COUNT . ' entités × 4 types + ' . self::FLOW_COUNT . ' flux logiques.');
    }

    /** @return int[] */
    private function seedWorkstations(string $now): array
    {
        $this->command->info('Création de ' . self::ENTITY_COUNT . ' workstations…');
        $rows = [];
        for ($i = 1; $i <= self::ENTITY_COUNT; $i++) {
            $rows[] = [
                'name'        => "LOAD-WS-{$i}",
                'description' => "Workstation de test de charge #{$i}",
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }
        DB::table('workstations')->insert($rows);

        return DB::table('workstations')->where('name', 'like', 'LOAD-WS-%')->pluck('id')->all();
    }

    /** @return int[] */
    private function seedLogicalServers(string $now): array
    {
        $this->command->info('Création de ' . self::ENTITY_COUNT . ' serveurs logiques…');
        $rows = [];
        for ($i = 1; $i <= self::ENTITY_COUNT; $i++) {
            $rows[] = [
                'name'        => "LOAD-LSRV-{$i}",
                'description' => "Serveur logique de test de charge #{$i}",
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }
        DB::table('logical_servers')->insert($rows);

        return DB::table('logical_servers')->where('name', 'like', 'LOAD-LSRV-%')->pluck('id')->all();
    }

    /** @return int[] */
    private function seedPhysicalServers(string $now): array
    {
        $this->command->info('Création de ' . self::ENTITY_COUNT . ' serveurs physiques…');
        $rows = [];
        for ($i = 1; $i <= self::ENTITY_COUNT; $i++) {
            $rows[] = [
                'name'        => "LOAD-PSRV-{$i}",
                'description' => "Serveur physique de test de charge #{$i}",
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }
        DB::table('physical_servers')->insert($rows);

        return DB::table('physical_servers')->where('name', 'like', 'LOAD-PSRV-%')->pluck('id')->all();
    }

    /** @return int[] */
    private function seedPeripherals(string $now): array
    {
        $this->command->info('Création de ' . self::ENTITY_COUNT . ' périphériques…');
        $rows = [];
        for ($i = 1; $i <= self::ENTITY_COUNT; $i++) {
            $rows[] = [
                'name'        => "LOAD-PER-{$i}",
                'description' => "Périphérique de test de charge #{$i}",
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }
        DB::table('peripherals')->insert($rows);

        return DB::table('peripherals')->where('name', 'like', 'LOAD-PER-%')->pluck('id')->all();
    }

    /**
     * @param int[] $workstationIds
     * @param int[] $logicalServerIds
     * @param int[] $physicalServerIds
     * @param int[] $peripheralIds
     */
    private function seedLogicalFlows(
        string $now,
        array $workstationIds,
        array $logicalServerIds,
        array $physicalServerIds,
        array $peripheralIds,
    ): void {
        $this->command->info('Création de ' . self::FLOW_COUNT . ' flux logiques…');

        $pools = [
            'workstation'    => $workstationIds,
            'logical_server' => $logicalServerIds,
            'physical_server' => $physicalServerIds,
            'peripheral'     => $peripheralIds,
        ];
        $types = array_keys($pools);

        $batch = [];
        for ($i = 1; $i <= self::FLOW_COUNT; $i++) {
            $srcType = $types[array_rand($types)];
            $dstType = $types[array_rand($types)];
            $type =
            $srcId   = $pools[$srcType][array_rand($pools[$srcType])];
            $dstId   = $pools[$dstType][array_rand($pools[$dstType])];

            $row = [
                'name'                     => "LOAD-FLOW-{$i}",
                'protocol'                 => self::PROTOCOLS[array_rand(self::PROTOCOLS)],
                'dest_port'                => self::PORTS[array_rand(self::PORTS)],
                'workstation_source_id'    => null,
                'logical_server_source_id' => null,
                'physical_server_source_id' => null,
                'peripheral_source_id'     => null,
                'workstation_dest_id'      => null,
                'logical_server_dest_id'   => null,
                'physical_server_dest_id'  => null,
                'peripheral_dest_id'       => null,
                'created_at'               => $now,
                'updated_at'               => $now,
            ];
            $row[self::SOURCE_FIELDS[$srcType]] = $srcId;
            $row[self::DEST_FIELDS[$dstType]]   = $dstId;

            $batch[] = $row;

            if (count($batch) >= self::CHUNK_SIZE) {
                DB::table('logical_flows')->insert($batch);
                $batch = [];
                $this->command->line("  {$i}/" . self::FLOW_COUNT . ' flux insérés…');
            }
        }

        if ($batch !== []) {
            DB::table('logical_flows')->insert($batch);
        }
    }
}
