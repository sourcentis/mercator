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
        Schema::table('logical_flows', function (Blueprint $table) {
            $table->string('chain')->after('description')->nullable();

            $table->unsignedInteger('subnetwork_source_id')
                ->nullable()
                ->after('physical_security_device_source_id');

            $table->index('subnetwork_source_id');
            $table->foreign('subnetwork_source_id')
                ->references('id')
                ->on('subnetworks')
                ->onDelete('set null');

            $table->unsignedInteger('subnetwork_dest_id')
                ->nullable()
                ->after('physical_security_device_dest_id');
            $table->index('subnetwork_dest_id');
            $table->foreign('subnetwork_dest_id')
                ->references('id')
                ->on('subnetworks')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logical_flows', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['subnetwork_source_id']);
                $table->dropIndex(['subnetwork_source_id']);
                $table->dropForeign(['subnetwork_dest_id']);
                $table->dropIndex(['subnetwork_dest_id']);

            }
            $table->dropColumn('subnetwork_source_id');
            $table->dropColumn('subnetwork_dest_id');

            $table->dropColumn('chain');
        });
    }
};
