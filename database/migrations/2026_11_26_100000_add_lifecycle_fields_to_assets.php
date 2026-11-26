<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lifecycle fields (commissioning, warranty, end of life, hardware identity)
     * so that GLPI Infocom data can be synchronised on every asset type
     * (sourcentis/mercator-glpi#19).
     *
     * Columns already present on a table are skipped (physical_servers.install_date,
     * workstations.warranty_end_date / manufacturer / model / serial_number).
     */
    private const COLUMNS = [
        'workstations' => ['install_date', 'end_of_life_date'],
        'databases' => ['install_date'],
        'physical_servers' => ['warranty_end_date', 'end_of_life_date', 'manufacturer', 'model', 'serial_number'],
        // vendor / product already describe the hardware identity
        'peripherals' => ['install_date', 'warranty_end_date', 'end_of_life_date', 'serial_number'],
        'wifi_terminals' => ['install_date', 'warranty_end_date', 'end_of_life_date', 'serial_number'],
        'phones' => ['install_date', 'warranty_end_date', 'end_of_life_date', 'manufacturer', 'model', 'serial_number'],
        'physical_switches' => ['install_date', 'warranty_end_date', 'end_of_life_date', 'manufacturer', 'model', 'serial_number'],
        'physical_routers' => ['install_date', 'warranty_end_date', 'end_of_life_date', 'manufacturer', 'model', 'serial_number'],
        'physical_security_devices' => ['install_date', 'warranty_end_date', 'end_of_life_date', 'manufacturer', 'model', 'serial_number'],
        'storage_devices' => ['install_date', 'warranty_end_date', 'end_of_life_date', 'manufacturer', 'model', 'serial_number'],
    ];

    private const DATE_COLUMNS = ['install_date', 'warranty_end_date', 'end_of_life_date'];

    public function up(): void
    {
        // Physical servers kept datetime columns (2025_03_17_094654_datetime_to_date only
        // converted applications and logical servers): an <input type="date"> rejects
        // "Y-m-d H:i:s", so the edit form showed an empty field and erased the date on save.
        Schema::table('physical_servers', function (Blueprint $table) {
            $table->date('install_date')->nullable()->change();
            $table->date('update_date')->nullable()->change();
        });

        foreach (self::COLUMNS as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        continue;
                    }
                    if (in_array($column, self::DATE_COLUMNS, true)) {
                        $table->date($column)->nullable();
                    } else {
                        $table->string($column)->nullable();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('physical_servers', function (Blueprint $table) {
            $table->dateTime('install_date')->nullable()->change();
            $table->dateTime('update_date')->nullable()->change();
        });

        foreach (self::COLUMNS as $tableName => $columns) {
            $existing = array_values(array_filter(
                $columns,
                fn (string $column) => Schema::hasColumn($tableName, $column)
            ));
            if ($existing === []) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }
};
