<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
