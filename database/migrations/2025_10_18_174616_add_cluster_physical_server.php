<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the cluster_physical_server pivot table, migrate existing cluster associations into it,
     * and remove the legacy `cluster_id` column and its related constraints/indexes from physical_servers.
     *
     * The migration performs three main actions:
     * 1. Create the pivot table `cluster_physical_server` with a composite primary key (cluster_id, physical_server_id),
     *    an index on `physical_server_id`, and cascading foreign keys to `clusters.id` and `physical_servers.id`.
     * 2. Backfill the pivot from existing `physical_servers.cluster_id`, inserting (cluster_id, physical_server_id) pairs.
     * 3. If `physical_servers.cluster_id` exists, drop its foreign key(s) and related indexes (including defensive attempts
     *    for common/custom index names and a SQLite-specific purge of indexes containing "cluster_id"), then drop the column.
     */
    public function up(): void
    {
        // 1) Table pivot
        if (! Schema::hasTable('cluster_physical_server')) {
            Schema::create('cluster_physical_server', function (Blueprint $table) {
                $table->unsignedInteger('cluster_id');
                $table->unsignedInteger('physical_server_id');

                $table->primary(['cluster_id', 'physical_server_id']);
                $table->index('physical_server_id');

                $table->foreign('cluster_id')
                    ->references('id')->on('clusters')
                    ->onDelete('cascade');

                $table->foreign('physical_server_id')
                    ->references('id')->on('physical_servers')
                    ->onDelete('cascade');
            });

            // 2) Backfill depuis physical_servers.cluster_id
            DB::table('physical_servers')
                ->whereNotNull('cluster_id')
                ->orderBy('id')
                ->chunkById(1000, function ($rows) {
                    $inserts = [];
                    foreach ($rows as $row) {
                        $inserts[] = [
                            'cluster_id' => $row->cluster_id,
                            'physical_server_id' => $row->id,
                        ];
                    }
                    if ($inserts) {
                        DB::table('cluster_physical_server')->insertOrIgnore($inserts);
                    }
                });
        }
        // 3) Suppression propre de la FK, des index et de la colonne `cluster_id`
        if (Schema::hasColumn('physical_servers', 'cluster_id')) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE physical_servers DROP COLUMN IF EXISTS cluster_id CASCADE');
            } else {

                Schema::disableForeignKeyConstraints();

                // 3.1 Drop FK (essayer par colonnes + par noms possibles)
                try {
                    Schema::table('physical_servers', function (Blueprint $table) {
                        $table->dropForeign(['cluster_id']); // portable
                    });
                } catch (Throwable $e) {
                }
                try {
                    Schema::table('physical_servers', function (Blueprint $table) {
                        $table->dropForeign('cluster_id_fk_5438543'); // ancien nom custom éventuel
                    });
                } catch (Throwable $e) {
                }

                // 3.2 Drop index (IMPORTANT: par NOM d'index quand on le connaît)
                try {
                    Schema::table('physical_servers', function (Blueprint $table) {
                        $table->dropIndex('cluster_id_fk_5438543'); // l'index qui casse chez toi
                    });
                } catch (Throwable $e) {
                }

                // essayer aussi les conventions courantes
                try {
                    Schema::table('physical_servers', function (Blueprint $table) {
                        $table->dropIndex(['cluster_id']); // ex: physical_servers_cluster_id_index
                    });
                } catch (Throwable $e) {
                }
                try {
                    Schema::table('physical_servers', function (Blueprint $table) {
                        $table->dropIndex('physical_servers_cluster_id_index');
                    });
                } catch (Throwable $e) {
                }

                // 3.3 SQLite: purge défensive de tout index contenant "cluster_id"
                if (DB::getDriverName() === 'sqlite') {
                    $indexes = DB::select("PRAGMA index_list('physical_servers')");
                    foreach ($indexes as $idx) {
                        // $idx->name (string), $idx->origin (c, u, pk)
                        if (isset($idx->name) && stripos($idx->name, 'cluster_id') !== false) {
                            DB::statement('DROP INDEX IF EXISTS "'.$idx->name.'"');
                        }
                    }
                }

                // 3.4 Enfin, suppression de la colonne
                Schema::table('physical_servers', function (Blueprint $table) {
                    $table->dropColumn('cluster_id');
                });

                Schema::enableForeignKeyConstraints();
            }
        }
    }

    /**
     * Restores the physical_servers.cluster_id column, repopulates it from the pivot, re-adds the foreign key, and removes the pivot table.
     *
     * If missing, adds a nullable `cluster_id` column and index on `physical_servers`; sets `cluster_id` to the minimum associated cluster for each physical server based on `cluster_physical_server`; recreates the foreign key to `clusters(id)` with `ON DELETE CASCADE`; and drops the `cluster_physical_server` pivot table.
     */
    public function down(): void
    {
        // 1) Recréer la colonne (nullable) + index
        if (! Schema::hasColumn('physical_servers', 'cluster_id')) {
            Schema::table('physical_servers', function (Blueprint $table) {
                $table->unsignedInteger('cluster_id')->nullable();
                $table->index('cluster_id'); // portable
            });
        }

        // 2) Backfill inverse: MIN(cluster_id) si plusieurs
        $pairs = DB::table('cluster_physical_server')
            ->select('physical_server_id', DB::raw('MIN(cluster_id) AS cluster_id'))
            ->groupBy('physical_server_id')
            ->get();

        foreach ($pairs as $p) {
            DB::table('physical_servers')
                ->where('id', $p->physical_server_id)
                ->update(['cluster_id' => $p->cluster_id]);
        }

        // 3) Reposer la FK (nom laissé au SGBD)
        Schema::table('physical_servers', function (Blueprint $table) {
            $table->foreign('cluster_id')
                ->references('id')->on('clusters')
                ->onDelete('cascade');
        });

        // 4) Drop de la table pivot
        Schema::dropIfExists('cluster_physical_server');
    }
};
