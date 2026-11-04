<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop whatever foreign key constraint(s) currently exist on $table.$column,
     * regardless of what they happen to be named. Historical migrations hardcoded
     * FK names that don't always match what's actually in the database (some were
     * created via ->index() and never became the real constraint name, others were
     * renamed outside of migrations), so we look the real name up instead of
     * assuming it.
     */
    private function dropForeignKeysOnColumn(string $table, string $column): void
    {
        $constraints = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $column]
        );

        foreach ($constraints as $constraint) {
            Schema::table($table, fn (Blueprint $t) => $t->dropForeign($constraint->CONSTRAINT_NAME));
        }
    }

    public function up(): void
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        // --- Drop FK constraints ---
        if (! $isSqlite) {
            $this->dropForeignKeysOnColumn('activity_m_application', 'm_application_id');
            $this->dropForeignKeysOnColumn('admin_user_m_application', 'm_application_id');
            $this->dropForeignKeysOnColumn('application_service_m_application', 'm_application_id');
            $this->dropForeignKeysOnColumn('cartographer_m_application', 'm_application_id');
            $this->dropForeignKeysOnColumn('certificate_m_application', 'm_application_id');
            $this->dropForeignKeysOnColumn('container_m_application', 'm_application_id');
            $this->dropForeignKeysOnColumn('database_m_application', 'm_application_id');
            $this->dropForeignKeysOnColumn('data_processing_m_application', 'm_application_id');
            $this->dropForeignKeysOnColumn('entity_m_application', 'm_application_id');
            $this->dropForeignKeysOnColumn('logical_server_m_application', 'm_application_id');
            $this->dropForeignKeysOnColumn('m_application_peripheral', 'm_application_id');
            $this->dropForeignKeysOnColumn('m_application_physical_server', 'm_application_id');
            $this->dropForeignKeysOnColumn('m_application_process', 'm_application_id');
            $this->dropForeignKeysOnColumn('m_application_security_device', 'm_application_id');
            $this->dropForeignKeysOnColumn('m_application_workstation', 'm_application_id');
            $this->dropForeignKeysOnColumn('security_control_m_application', 'm_application_id');
            $this->dropForeignKeysOnColumn('fluxes', 'application_source_id');
            $this->dropForeignKeysOnColumn('fluxes', 'application_dest_id');
            $this->dropForeignKeysOnColumn('m_application_events', 'm_application_id');
        }

        // --- Rename main table ---
        Schema::rename('m_applications', 'applications');

        // --- Rename pivot tables (column + table) ---
        Schema::table('activity_m_application', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('activity_m_application', 'activity_application');

        Schema::table('admin_user_m_application', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('admin_user_m_application', 'admin_user_application');

        Schema::table('application_service_m_application', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('application_service_m_application', 'application_application_service');

        Schema::table('cartographer_m_application', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('cartographer_m_application', 'application_cartographer');

        Schema::table('certificate_m_application', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('certificate_m_application', 'application_certificate');

        Schema::table('container_m_application', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('container_m_application', 'application_container');

        Schema::table('database_m_application', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('database_m_application', 'application_database');

        Schema::table('data_processing_m_application', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('data_processing_m_application', 'application_data_processing');

        Schema::table('entity_m_application', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('entity_m_application', 'application_entity');

        Schema::table('logical_server_m_application', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('logical_server_m_application', 'application_logical_server');

        Schema::table('m_application_peripheral', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('m_application_peripheral', 'application_peripheral');

        Schema::table('m_application_physical_server', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('m_application_physical_server', 'application_physical_server');

        Schema::table('m_application_process', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('m_application_process', 'application_process');

        Schema::table('m_application_security_device', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('m_application_security_device', 'application_security_device');

        Schema::table('m_application_workstation', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('m_application_workstation', 'application_workstation');

        Schema::table('security_control_m_application', fn (Blueprint $t) => $t->renameColumn('m_application_id', 'application_id'));
        Schema::rename('security_control_m_application', 'application_security_control');

        // --- Restore FK constraints → applications ---
        if (! $isSqlite) {
            Schema::table('activity_application', fn (Blueprint $t) => $t->foreign('application_id', 'activity_application_application_id_foreign')->references('id')->on('applications'));
            Schema::table('admin_user_application', fn (Blueprint $t) => $t->foreign('application_id', 'admin_user_application_application_id_foreign')->references('id')->on('applications'));
            Schema::table('application_application_service', fn (Blueprint $t) => $t->foreign('application_id', 'application_application_service_application_id_foreign')->references('id')->on('applications'));
            Schema::table('application_cartographer', fn (Blueprint $t) => $t->foreign('application_id', 'application_cartographer_application_id_foreign')->references('id')->on('applications'));
            Schema::table('application_certificate', fn (Blueprint $t) => $t->foreign('application_id', 'application_certificate_application_id_foreign')->references('id')->on('applications'));
            Schema::table('application_container', fn (Blueprint $t) => $t->foreign('application_id', 'application_container_application_id_foreign')->references('id')->on('applications'));
            Schema::table('application_database', fn (Blueprint $t) => $t->foreign('application_id', 'application_database_application_id_foreign')->references('id')->on('applications'));
            Schema::table('application_data_processing', fn (Blueprint $t) => $t->foreign('application_id', 'application_data_processing_application_id_foreign')->references('id')->on('applications'));
            Schema::table('application_entity', fn (Blueprint $t) => $t->foreign('application_id', 'application_entity_application_id_foreign')->references('id')->on('applications'));
            Schema::table('application_logical_server', fn (Blueprint $t) => $t->foreign('application_id', 'application_logical_server_application_id_foreign')->references('id')->on('applications'));
            Schema::table('application_peripheral', fn (Blueprint $t) => $t->foreign('application_id', 'application_peripheral_application_id_foreign')->references('id')->on('applications'));
            Schema::table('application_physical_server', fn (Blueprint $t) => $t->foreign('application_id', 'application_physical_server_application_id_foreign')->references('id')->on('applications'));
            Schema::table('application_process', fn (Blueprint $t) => $t->foreign('application_id', 'application_process_application_id_foreign')->references('id')->on('applications'));
            Schema::table('application_security_device', fn (Blueprint $t) => $t->foreign('application_id', 'application_security_device_application_id_foreign')->references('id')->on('applications'));
            Schema::table('application_workstation', fn (Blueprint $t) => $t->foreign('application_id', 'application_workstation_application_id_foreign')->references('id')->on('applications'));
            Schema::table('application_security_control', fn (Blueprint $t) => $t->foreign('application_id', 'application_security_control_application_id_foreign')->references('id')->on('applications'));
            Schema::table('fluxes', function (Blueprint $t): void {
                $t->foreign('application_source_id', 'application_source_fk_1485545')->references('id')->on('applications');
                $t->foreign('application_dest_id', 'application_dest_fk_1485549')->references('id')->on('applications');
            });
            Schema::table('m_application_events', fn (Blueprint $t) => $t->foreign('m_application_id', 'm_application_events_m_application_id_foreign')->references('id')->on('applications'));
        }
    }

    public function down(): void
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        // --- Drop FK constraints added in up() ---
        if (! $isSqlite) {
            Schema::table('activity_application', fn (Blueprint $t) => $t->dropForeign('activity_application_application_id_foreign'));
            Schema::table('admin_user_application', fn (Blueprint $t) => $t->dropForeign('admin_user_application_application_id_foreign'));
            Schema::table('application_application_service', fn (Blueprint $t) => $t->dropForeign('application_application_service_application_id_foreign'));
            Schema::table('application_cartographer', fn (Blueprint $t) => $t->dropForeign('application_cartographer_application_id_foreign'));
            Schema::table('application_certificate', fn (Blueprint $t) => $t->dropForeign('application_certificate_application_id_foreign'));
            Schema::table('application_container', fn (Blueprint $t) => $t->dropForeign('application_container_application_id_foreign'));
            Schema::table('application_database', fn (Blueprint $t) => $t->dropForeign('application_database_application_id_foreign'));
            Schema::table('application_data_processing', fn (Blueprint $t) => $t->dropForeign('application_data_processing_application_id_foreign'));
            Schema::table('application_entity', fn (Blueprint $t) => $t->dropForeign('application_entity_application_id_foreign'));
            Schema::table('application_logical_server', fn (Blueprint $t) => $t->dropForeign('application_logical_server_application_id_foreign'));
            Schema::table('application_peripheral', fn (Blueprint $t) => $t->dropForeign('application_peripheral_application_id_foreign'));
            Schema::table('application_physical_server', fn (Blueprint $t) => $t->dropForeign('application_physical_server_application_id_foreign'));
            Schema::table('application_process', fn (Blueprint $t) => $t->dropForeign('application_process_application_id_foreign'));
            Schema::table('application_security_device', fn (Blueprint $t) => $t->dropForeign('application_security_device_application_id_foreign'));
            Schema::table('application_workstation', fn (Blueprint $t) => $t->dropForeign('application_workstation_application_id_foreign'));
            Schema::table('application_security_control', fn (Blueprint $t) => $t->dropForeign('application_security_control_application_id_foreign'));
            Schema::table('fluxes', function (Blueprint $t): void {
                $t->dropForeign('application_source_fk_1485545');
                $t->dropForeign('application_dest_fk_1485549');
            });
            Schema::table('m_application_events', fn (Blueprint $t) => $t->dropForeign('m_application_events_m_application_id_foreign'));
        }

        // --- Restore main table name ---
        Schema::rename('applications', 'm_applications');

        // --- Restore pivot tables (table + column) ---
        Schema::rename('activity_application', 'activity_m_application');
        Schema::table('activity_m_application', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('admin_user_application', 'admin_user_m_application');
        Schema::table('admin_user_m_application', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('application_application_service', 'application_service_m_application');
        Schema::table('application_service_m_application', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('application_cartographer', 'cartographer_m_application');
        Schema::table('cartographer_m_application', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('application_certificate', 'certificate_m_application');
        Schema::table('certificate_m_application', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('application_container', 'container_m_application');
        Schema::table('container_m_application', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('application_database', 'database_m_application');
        Schema::table('database_m_application', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('application_data_processing', 'data_processing_m_application');
        Schema::table('data_processing_m_application', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('application_entity', 'entity_m_application');
        Schema::table('entity_m_application', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('application_logical_server', 'logical_server_m_application');
        Schema::table('logical_server_m_application', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('application_peripheral', 'm_application_peripheral');
        Schema::table('m_application_peripheral', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('application_physical_server', 'm_application_physical_server');
        Schema::table('m_application_physical_server', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('application_process', 'm_application_process');
        Schema::table('m_application_process', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('application_security_device', 'm_application_security_device');
        Schema::table('m_application_security_device', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('application_workstation', 'm_application_workstation');
        Schema::table('m_application_workstation', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        Schema::rename('application_security_control', 'security_control_m_application');
        Schema::table('security_control_m_application', fn (Blueprint $t) => $t->renameColumn('application_id', 'm_application_id'));

        // --- Restore original FK constraints → m_applications ---
        if (! $isSqlite) {
            Schema::table('activity_m_application', fn (Blueprint $t) => $t->foreign('m_application_id', 'activity_m_application_m_application_id_foreign')->references('id')->on('m_applications'));
            Schema::table('admin_user_m_application', fn (Blueprint $t) => $t->foreign('m_application_id', 'admin_user_m_application_m_application_id_foreign')->references('id')->on('m_applications'));
            Schema::table('application_service_m_application', fn (Blueprint $t) => $t->foreign('m_application_id', 'm_application_id_fk_1482585')->references('id')->on('m_applications'));
            Schema::table('cartographer_m_application', fn (Blueprint $t) => $t->foreign('m_application_id', 'cartographer_m_application_m_application_id_foreign')->references('id')->on('m_applications'));
            Schema::table('certificate_m_application', fn (Blueprint $t) => $t->foreign('m_application_id', 'certificate_m_application_m_application_id_foreign')->references('id')->on('m_applications'));
            Schema::table('container_m_application', fn (Blueprint $t) => $t->foreign('m_application_id', 'container_m_application_m_application_id_foreign')->references('id')->on('m_applications'));
            Schema::table('database_m_application', fn (Blueprint $t) => $t->foreign('m_application_id', 'm_application_id_fk_1482586')->references('id')->on('m_applications'));
            Schema::table('data_processing_m_application', fn (Blueprint $t) => $t->foreign('m_application_id', 'applications_id_fk_0483434')->references('id')->on('m_applications'));
            Schema::table('entity_m_application', fn (Blueprint $t) => $t->foreign('m_application_id', 'm_application_id_fk_1488611')->references('id')->on('m_applications'));
            Schema::table('logical_server_m_application', fn (Blueprint $t) => $t->foreign('m_application_id', 'm_application_id_fk_1488616')->references('id')->on('m_applications'));
            Schema::table('m_application_peripheral', fn (Blueprint $t) => $t->foreign('m_application_id', 'm_application_id_fk_9878654')->references('id')->on('m_applications'));
            Schema::table('m_application_physical_server', fn (Blueprint $t) => $t->foreign('m_application_id', 'm_application_id_fk_5483543')->references('id')->on('m_applications'));
            Schema::table('m_application_process', fn (Blueprint $t) => $t->foreign('m_application_id', 'm_application_id_fk_1482573')->references('id')->on('m_applications'));
            Schema::table('m_application_security_device', fn (Blueprint $t) => $t->foreign('m_application_id', 'm_application_id_fk_41923483')->references('id')->on('m_applications'));
            Schema::table('m_application_workstation', fn (Blueprint $t) => $t->foreign('m_application_id', 'm_application_id_fk_1486547')->references('id')->on('m_applications'));
            Schema::table('security_control_m_application', fn (Blueprint $t) => $t->foreign('m_application_id', 'm_application_id_fk_304958543')->references('id')->on('m_applications'));
            Schema::table('fluxes', function (Blueprint $t): void {
                $t->foreign('application_source_id', 'application_source_fk_1485545')->references('id')->on('m_applications');
                $t->foreign('application_dest_id', 'application_dest_fk_1485549')->references('id')->on('m_applications');
            });
            Schema::table('m_application_events', fn (Blueprint $t) => $t->foreign('m_application_id', 'm_application_events_m_application_id_foreign')->references('id')->on('m_applications'));
        }
    }
};
