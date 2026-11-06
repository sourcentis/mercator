<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables of the audited models (models using the Auditable trait).
     * Hard-coded on purpose: a migration must not depend on application code.
     */
    private const array TABLES = [
        'activities',
        'actors',
        'admin_users',
        'annuaires',
        'application_blocks',
        'application_flows',
        'application_modules',
        'application_services',
        'applications',
        'backups',
        'bays',
        'buildings',
        'certificates',
        'clusters',
        'containers',
        'data_processing',
        'databases',
        'dhcp_servers',
        'dnsservers',
        'documents',
        'domains',
        'entities',
        'external_connected_entities',
        'forest_ads',
        'gateways',
        'graphs',
        'information',
        'lans',
        'logical_flows',
        'logical_servers',
        'macro_processuses',
        'mans',
        'network_switches',
        'networks',
        'operations',
        'peripherals',
        'phones',
        'physical_links',
        'physical_routers',
        'physical_security_devices',
        'physical_servers',
        'physical_switches',
        'processes',
        'relations',
        'routers',
        'security_controls',
        'security_devices',
        'sites',
        'storage_devices',
        'subnetworks',
        'tasks',
        'vlans',
        'wans',
        'wifi_terminals',
        'workstations',
        'zone_admins',
        'zones',
    ];

    private const array COLUMNS = ['created_by', 'updated_by', 'deleted_by'];

    /**
     * audit_logs.subject_type => table, for the backfill. Includes the historical
     * App\Models\MApplication alias.
     */
    private const array SUBJECT_TYPES = [
        'App\\Models\\Activity' => 'activities',
        'App\\Models\\Actor' => 'actors',
        'App\\Models\\AdminUser' => 'admin_users',
        'App\\Models\\Annuaire' => 'annuaires',
        'App\\Models\\Application' => 'applications',
        'App\\Models\\ApplicationBlock' => 'application_blocks',
        'App\\Models\\ApplicationFlow' => 'application_flows',
        'App\\Models\\ApplicationModule' => 'application_modules',
        'App\\Models\\ApplicationService' => 'application_services',
        'App\\Models\\Backup' => 'backups',
        'App\\Models\\Bay' => 'bays',
        'App\\Models\\Building' => 'buildings',
        'App\\Models\\Certificate' => 'certificates',
        'App\\Models\\Cluster' => 'clusters',
        'App\\Models\\Container' => 'containers',
        'App\\Models\\DataProcessing' => 'data_processing',
        'App\\Models\\Database' => 'databases',
        'App\\Models\\DhcpServer' => 'dhcp_servers',
        'App\\Models\\Dnsserver' => 'dnsservers',
        'App\\Models\\Document' => 'documents',
        'App\\Models\\Domain' => 'domains',
        'App\\Models\\Entity' => 'entities',
        'App\\Models\\ExternalConnectedEntity' => 'external_connected_entities',
        'App\\Models\\ForestAd' => 'forest_ads',
        'App\\Models\\Gateway' => 'gateways',
        'App\\Models\\Graph' => 'graphs',
        'App\\Models\\Information' => 'information',
        'App\\Models\\Lan' => 'lans',
        'App\\Models\\LogicalFlow' => 'logical_flows',
        'App\\Models\\LogicalServer' => 'logical_servers',
        'App\\Models\\MacroProcessus' => 'macro_processuses',
        'App\\Models\\Man' => 'mans',
        'App\\Models\\Network' => 'networks',
        'App\\Models\\NetworkSwitch' => 'network_switches',
        'App\\Models\\Operation' => 'operations',
        'App\\Models\\Peripheral' => 'peripherals',
        'App\\Models\\Phone' => 'phones',
        'App\\Models\\PhysicalLink' => 'physical_links',
        'App\\Models\\PhysicalRouter' => 'physical_routers',
        'App\\Models\\PhysicalSecurityDevice' => 'physical_security_devices',
        'App\\Models\\PhysicalServer' => 'physical_servers',
        'App\\Models\\PhysicalSwitch' => 'physical_switches',
        'App\\Models\\Process' => 'processes',
        'App\\Models\\Relation' => 'relations',
        'App\\Models\\Router' => 'routers',
        'App\\Models\\SecurityControl' => 'security_controls',
        'App\\Models\\SecurityDevice' => 'security_devices',
        'App\\Models\\Site' => 'sites',
        'App\\Models\\StorageDevice' => 'storage_devices',
        'App\\Models\\Subnetwork' => 'subnetworks',
        'App\\Models\\Task' => 'tasks',
        'App\\Models\\Vlan' => 'vlans',
        'App\\Models\\Wan' => 'wans',
        'App\\Models\\WifiTerminal' => 'wifi_terminals',
        'App\\Models\\Workstation' => 'workstations',
        'App\\Models\\Zone' => 'zones',
        'App\\Models\\ZoneAdmin' => 'zone_admins',
        'App\\Models\\MApplication' => 'applications',
    ];

    private const int READ_CHUNK = 5000;

    private const int WRITE_BATCH = 500;

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            $missing = array_values(array_filter(
                self::COLUMNS,
                fn (string $column) => ! Schema::hasColumn($tableName, $column)
            ));

            if ($missing === []) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($missing) {
                foreach ($missing as $column) {
                    $table->unsignedInteger($column)->nullable();
                }
            });

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $missing) {
                foreach ($missing as $column) {
                    $table->foreign($column, "{$tableName}_{$column}_foreign")
                        ->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        $this->backfill();
    }

    /**
     * Fills the new columns from the audit history, only where they are still NULL:
     * - created_by: user_id of the first "created" entry;
     * - updated_by: user_id of the last "created" or "updated" entry;
     * - deleted_by: user_id of the last "deleted" entry (soft-deleted rows only).
     *
     * Writes go through the query builder: no event, no audit, updated_at untouched.
     * A user_id pointing to a purged user is treated as null. No-op on a fresh install.
     */
    private function backfill(): void
    {
        /** @var array<int, int> $existingUsers */
        $existingUsers = DB::table('users')->pluck('id')->flip()->all();

        // [column][table][id] => ?int user id
        $values = ['created_by' => [], 'updated_by' => [], 'deleted_by' => []];
        $ignored = 0;

        DB::table('audit_logs')
            ->select('id', 'subject_type', 'subject_id', 'user_id', 'description')
            ->whereIn('description', ['created', 'updated', 'deleted'])
            ->whereIn('subject_type', array_keys(self::SUBJECT_TYPES))
            ->whereNotNull('subject_id')
            ->chunkById(self::READ_CHUNK, function ($rows) use ($existingUsers, &$values, &$ignored): void {
                foreach ($rows as $row) {
                    $table = self::SUBJECT_TYPES[$row->subject_type];
                    $id = (int) $row->subject_id;

                    $userId = $row->user_id === null ? null : (int) $row->user_id;
                    if ($userId !== null && ! isset($existingUsers[$userId])) {
                        $ignored++;
                        $userId = null;
                    }

                    switch ($row->description) {
                        case 'created':
                            if (! array_key_exists($id, $values['created_by'][$table] ?? [])) {
                                $values['created_by'][$table][$id] = $userId;
                            }
                            $values['updated_by'][$table][$id] = $userId;
                            break;
                        case 'updated':
                            $values['updated_by'][$table][$id] = $userId;
                            break;
                        case 'deleted':
                            $values['deleted_by'][$table][$id] = $userId;
                            break;
                    }
                }
            });

        $written = 0;
        foreach ($values as $column => $byTable) {
            foreach ($byTable as $table => $valuesById) {
                $written += $this->write($table, $column, $valuesById);
            }
        }

        Log::info("Blame backfill: {$written} value(s) written, {$ignored} audit entry(ies) of purged users ignored");
    }

    /**
     * @param  array<int, ?int>  $valuesById
     */
    private function write(string $table, string $column, array $valuesById): int
    {
        // Group ids by user; null (system / purged user) is never written
        $idsByUser = [];
        foreach ($valuesById as $id => $userId) {
            if ($userId !== null) {
                $idsByUser[$userId][] = $id;
            }
        }

        $count = 0;
        foreach ($idsByUser as $userId => $ids) {
            foreach (array_chunk($ids, self::WRITE_BATCH) as $batch) {
                $query = DB::table($table)->whereIn('id', $batch)->whereNull($column);
                if ($column === 'deleted_by') {
                    $query->whereNotNull('deleted_at');
                }
                $count += $query->update([$column => $userId]);
            }
        }

        return $count;
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            // Foreign keys are looked up by column: SQLite does not report constraint names.
            $foreignColumns = array_merge(...array_map(
                fn (array $foreignKey) => count($foreignKey['columns']) === 1 ? $foreignKey['columns'] : [],
                Schema::getForeignKeys($tableName)
            ) ?: [[]]);

            $toDrop = array_values(array_intersect(self::COLUMNS, $foreignColumns));

            if ($toDrop !== []) {
                Schema::table($tableName, function (Blueprint $table) use ($toDrop) {
                    foreach ($toDrop as $column) {
                        // dropForeign([column]) resolves to "{table}_{column}_foreign",
                        // the explicit name used in up(), and is the only form SQLite accepts.
                        $table->dropForeign([$column]);
                    }
                });
            }

            $existing = array_values(array_filter(
                self::COLUMNS,
                fn (string $column) => Schema::hasColumn($tableName, $column)
            ));

            if ($existing !== []) {
                Schema::table($tableName, function (Blueprint $table) use ($existing) {
                    $table->dropColumn($existing);
                });
            }
        }
    }
};
