<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crée la table pivot information_information permettant de regrouper
     * des informations sous une information "catégorie" (auto-relation n-m).
     *
     * Exemple d'usage :
     *   - "Informations pièce d'identité" (catégorie) contient :
     *     Nom, Prénom, Date de naissance, Numéro de pièce, Nationalité…
     */
    public function up(): void
    {
        Schema::create('information_information', function (Blueprint $table) {
            $table->unsignedInteger('information_id');
            $table->unsignedInteger('child_information_id');

            $table->primary(['information_id', 'child_information_id']);

            $table->foreign('information_id')
                ->references('id')
                ->on('information')
                ->onDelete('cascade');

            $table->foreign('child_information_id')
                ->references('id')
                ->on('information')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('information_information');
    }
};
