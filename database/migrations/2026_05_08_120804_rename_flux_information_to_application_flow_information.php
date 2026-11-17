<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        if (! $isSqlite) {
            Schema::table('flux_information', function (Blueprint $t): void {
                $t->dropForeign('flux_information_flux_id_foreign');
                $t->dropForeign('flux_information_information_id_foreign');
            });
        }

        Schema::rename('flux_information', 'application_flow_information');

        if (! $isSqlite) {
            Schema::table('application_flow_information', function (Blueprint $t): void {
                $t->foreign('flux_id', 'application_flow_information_flux_id_foreign')->references('id')->on('application_flows')->cascadeOnDelete();
                $t->foreign('information_id', 'application_flow_information_information_id_foreign')->references('id')->on('information')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        if (! $isSqlite) {
            Schema::table('application_flow_information', function (Blueprint $t): void {
                $t->dropForeign('application_flow_information_flux_id_foreign');
                $t->dropForeign('application_flow_information_information_id_foreign');
            });
        }

        Schema::rename('application_flow_information', 'flux_information');

        if (! $isSqlite) {
            Schema::table('flux_information', function (Blueprint $t): void {
                $t->foreign('flux_id', 'flux_information_flux_id_foreign')->references('id')->on('application_flows')->cascadeOnDelete();
                $t->foreign('information_id', 'flux_information_information_id_foreign')->references('id')->on('information')->cascadeOnDelete();
            });
        }
    }
};
