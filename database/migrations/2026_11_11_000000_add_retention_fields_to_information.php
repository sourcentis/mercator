<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Legal retention of information: retention period (DUA), final
     * disposition once the period is over, and free-text notes.
     */
    public function up(): void
    {
        // Reuse the legacy (never exposed) "retention" column as retention_period
        if (Schema::hasColumn('information', 'retention')) {
            Schema::table('information', function (Blueprint $table) {
                $table->renameColumn('retention', 'retention_period');
            });
        } else {
            Schema::table('information', function (Blueprint $table) {
                $table->string('retention_period')->nullable()->after('constraints');
            });
        }

        Schema::table('information', function (Blueprint $table) {
            $table->string('final_disposition')->nullable()->after('retention_period');
            $table->longText('retention_notes')->nullable()->after('final_disposition');
        });
    }

    public function down(): void
    {
        Schema::table('information', function (Blueprint $table) {
            $table->dropColumn(['final_disposition', 'retention_notes']);
        });

        Schema::table('information', function (Blueprint $table) {
            $table->renameColumn('retention_period', 'retention');
        });
    }
};
