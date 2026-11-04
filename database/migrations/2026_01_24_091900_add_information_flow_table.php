<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flux_information', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('flux_id');
            $table->foreign('flux_id')
                ->references('id')
                ->on('fluxes')
                ->onDelete('cascade');

            $table->unsignedInteger('information_id');
            $table->foreign('information_id')
                ->references('id')
                ->on('information')
                ->onDelete('cascade');

            $table->timestamps();

            // Index unique pour éviter les doublons
            $table->unique(['flux_id', 'information_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flux_information');
    }
};
