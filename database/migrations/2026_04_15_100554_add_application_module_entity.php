<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_module_entity', function (Blueprint $table) {
            $table->unsignedInteger('application_module_id');
            $table->unsignedInteger('entity_id');

            $table->primary(['application_module_id', 'entity_id']);

            $table->foreign('application_module_id')
                ->references('id')
                ->on('application_modules')
                ->onDelete('cascade');

            $table->foreign('entity_id')
                ->references('id')
                ->on('entities')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_module_entity');
    }
};
