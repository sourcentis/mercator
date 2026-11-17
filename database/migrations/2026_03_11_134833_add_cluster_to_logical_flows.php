<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logical_flows', function (Blueprint $table) {
            $table->unsignedInteger('cluster_source_id')
                ->nullable()
                ->after('subnetwork_source_id');

            $table->foreign('cluster_source_id')
                ->references('id')->on('clusters')
                ->nullOnDelete();

            $table->unsignedInteger('cluster_dest_id')
                ->nullable()
                ->after('subnetwork_dest_id');

            $table->foreign('cluster_dest_id')
                ->references('id')->on('clusters')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('logical_flows', function (Blueprint $table) {
            $table->dropForeign(['cluster_source_id']);
            $table->dropForeign(['cluster_dest_id']);
            $table->dropColumn(['cluster_source_id', 'cluster_dest_id']);
        });
    }
};
