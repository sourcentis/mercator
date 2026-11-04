<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Supprime la contrainte UNIQUE KEY `annuaires_name_unique` sur la colonne `name`
     * de la table `annuaires`.
     */
    public function up(): void
    {
        // SQLite ne supporte pas DROP INDEX via Blueprint sur certaines versions,
        // mais gère bien le DROP INDEX natif — on utilise une requête directe.
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS annuaires_name_unique');
        } else {
            Schema::table('annuaires', function (Blueprint $table) {
                $table->dropUnique('annuaires_name_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     * Recrée la contrainte UNIQUE KEY sur `name`.
     */
    public function down(): void
    {
        Schema::table('annuaires', function (Blueprint $table) {
            $table->unique('name', 'annuaires_name_unique');
        });
    }
};
