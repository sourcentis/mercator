<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite ne supporte pas DROP/ADD FOREIGN KEY — rename suffit
            Schema::rename('fluxes', 'application_flows');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE flux_information DROP CONSTRAINT flux_information_flux_id_foreign');
            DB::statement('ALTER TABLE fluxes RENAME TO application_flows');
            DB::statement('ALTER TABLE flux_information ADD CONSTRAINT flux_information_flux_id_foreign FOREIGN KEY (flux_id) REFERENCES application_flows(id)');
        } else {
            DB::statement('ALTER TABLE flux_information DROP FOREIGN KEY flux_information_flux_id_foreign');
            DB::statement('RENAME TABLE fluxes TO application_flows');
            DB::statement('ALTER TABLE flux_information ADD CONSTRAINT flux_information_flux_id_foreign FOREIGN KEY (flux_id) REFERENCES application_flows(id)');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            Schema::rename('application_flows', 'fluxes');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE flux_information DROP CONSTRAINT flux_information_flux_id_foreign');
            DB::statement('ALTER TABLE application_flows RENAME TO fluxes');
            DB::statement('ALTER TABLE flux_information ADD CONSTRAINT flux_information_flux_id_foreign FOREIGN KEY (flux_id) REFERENCES fluxes(id)');
        } else {
            DB::statement('ALTER TABLE flux_information DROP FOREIGN KEY flux_information_flux_id_foreign');
            DB::statement('RENAME TABLE application_flows TO fluxes');
            DB::statement('ALTER TABLE flux_information ADD CONSTRAINT flux_information_flux_id_foreign FOREIGN KEY (flux_id) REFERENCES fluxes(id)');
        }
    }
};
