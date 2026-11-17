<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. New scalar columns (name nullable first) ───────────────────────
        Schema::table('backups', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('type', 100)->nullable()->after('name');
            $table->text('attributes')->nullable()->after('type');
            $table->text('description')->nullable()->after('attributes');
        });

        // ── 2. Pivot tables ───────────────────────────────────────────────────
        Schema::create('backup_logical_server', function (Blueprint $table) {
            $table->unsignedInteger('backup_id');
            $table->unsignedInteger('logical_server_id');
            $table->primary(['backup_id', 'logical_server_id']);
            $table->foreign('backup_id')->references('id')->on('backups')->onDelete('cascade');
            $table->foreign('logical_server_id')->references('id')->on('logical_servers')->onDelete('cascade');
        });

        Schema::create('backup_storage_device', function (Blueprint $table) {
            $table->unsignedInteger('backup_id');
            $table->unsignedInteger('storage_device_id');
            $table->primary(['backup_id', 'storage_device_id']);
            $table->foreign('backup_id')->references('id')->on('backups')->onDelete('cascade');
            $table->foreign('storage_device_id')->references('id')->on('storage_devices')->onDelete('cascade');
        });

        // ── 3. Migrate existing FK data into pivot tables ─────────────────────
        DB::table('backups')->orderBy('id')->get()->each(function (object $row) {
            DB::table('backups')->where('id', $row->id)->update([
                'name' => 'Backup #'.$row->id,
            ]);

            if ($row->logical_server_id) {
                DB::table('backup_logical_server')->insertOrIgnore([
                    'backup_id' => $row->id,
                    'logical_server_id' => $row->logical_server_id,
                ]);
            }

            if ($row->storage_device_id) {
                DB::table('backup_storage_device')->insertOrIgnore([
                    'backup_id' => $row->id,
                    'storage_device_id' => $row->storage_device_id,
                ]);
            }
        });

        // ── 4. Make name NOT NULL and unique ──────────────────────────────────
        Schema::table('backups', function (Blueprint $table) {
            $table->string('name')->nullable(false)->unique()->change();
        });

        // ── 5. Drop old FK columns ────────────────────────────────────────────
        Schema::table('backups', function (Blueprint $table) {
            $table->dropForeign(['logical_server_id']);
            $table->dropForeign(['storage_device_id']);
            $table->dropColumn(['logical_server_id', 'storage_device_id']);
        });
    }

    public function down(): void
    {
        // Re-add FK columns
        Schema::table('backups', function (Blueprint $table) {
            $table->unsignedInteger('logical_server_id')->nullable()->after('id');
            $table->unsignedInteger('storage_device_id')->nullable()->after('logical_server_id');
        });

        // Restore first pivot entry into FK columns (best-effort)
        DB::table('backup_logical_server')->get()->each(function (object $row) {
            DB::table('backups')->where('id', $row->backup_id)->update([
                'logical_server_id' => $row->logical_server_id,
            ]);
        });

        DB::table('backup_storage_device')->get()->each(function (object $row) {
            DB::table('backups')->where('id', $row->backup_id)->update([
                'storage_device_id' => $row->storage_device_id,
            ]);
        });

        // Re-add FK constraints
        Schema::table('backups', function (Blueprint $table) {
            $table->foreign('logical_server_id')->references('id')->on('logical_servers')->onDelete('cascade');
            $table->foreign('storage_device_id')->references('id')->on('storage_devices')->onDelete('cascade');
        });

        // Drop pivot tables
        Schema::dropIfExists('backup_storage_device');
        Schema::dropIfExists('backup_logical_server');

        // Drop new columns
        Schema::table('backups', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropColumn(['name', 'type', 'attributes', 'description']);
        });
    }
};
