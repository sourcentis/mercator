<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('information', function (Blueprint $table) {
            $table->unsignedInteger('icon_id')->after('attributes')->nullable()->index('document_id_fk_61928374');
            $table->foreign('icon_id', 'document_id_fk_61928374')->references('id')->on('documents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('information', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign('document_id_fk_61928374');
            }
        });

        Schema::table('information', function (Blueprint $table) {
            $table->dropColumn('icon_id');
        });
    }
};
