<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('external_connected_entities', 'security')) {
            Schema::table('external_connected_entities', function (Blueprint $table) {
                $table->text('security')->nullable()->after('description');
            });
        }

        if (! Schema::hasTable('document_external_connected_entity')) {
            Schema::create('document_external_connected_entity', function (Blueprint $table) {
                $table->unsignedInteger('external_connected_entity_id');
                $table->unsignedInteger('document_id');

                // index pour jointures
                $table->index('external_connected_entity_id', 'external_connected_entity_idx_2143243');
                $table->index('document_id', 'document_idx_434934839');

                // contraintes référentielles
                $table->foreign('external_connected_entity_id', 'external_connected_entity_id_fk_434854')
                    ->references('id')
                    ->on('external_connected_entities')
                    ->onDelete('cascade');

                $table->foreign('document_id', 'document_id_fk_4394384')
                    ->references('id')
                    ->on('documents')
                    ->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('external_connected_entity_subnetwork')) {
            Schema::create('external_connected_entity_subnetwork', function (Blueprint $table) {
                $table->unsignedInteger('external_connected_entity_id');
                $table->unsignedInteger('subnetwork_id');

                $table->index('external_connected_entity_id', 'external_connected_entity_idx_59458458');
                $table->index('subnetwork_id', 'subnetwork_idx_4343848');

                $table->foreign('external_connected_entity_id', 'external_connected_entity_id_fk_4302049')
                    ->references('id')
                    ->on('external_connected_entities')
                    ->onDelete('cascade');

                $table->foreign('subnetwork_id', 'subnetwork_id_fk_09848239')
                    ->references('id')
                    ->on('subnetworks')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('external_connected_entity_subnetwork');
        Schema::dropIfExists('document_external_connected_entity');

        Schema::table('external_connected_entities', function (Blueprint $table) {
            if (Schema::hasColumn('external_connected_entities', 'security')) {
                $table->dropColumn('security');
            }
        });
    }
};
