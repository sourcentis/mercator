<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function dropForeignIfExists(string $table, string $fkName): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $exists = collect(Schema::getForeignKeys($table))
            ->pluck('name')
            ->contains($fkName);

        if ($exists) {
            Schema::table($table, fn (Blueprint $t) => $t->dropForeign($fkName));
        }
    }

    public function up(): void
    {
        if (Schema::hasTable('domaine_ads')) {
            // Support both legacy table names (domaine_ads or domaines)
            $sourceTable = 'domaine_ads';
            $sourcePivot = 'domaine_ad_forest_ad';
            $sourceFkCol = 'domaine_ad_id';
            $sourcePivotFk = 'domaine_ad_id_fk_1492084';

            // Drop FKs referencing the source table
            $this->dropForeignIfExists('admin_users', 'domain_id_fk_69385935');
            $this->dropForeignIfExists('logical_servers', 'domain_id_fk_493844');
            $this->dropForeignIfExists('workstations', 'workstations_domain_id_foreign');
            $this->dropForeignIfExists($sourcePivot, $sourcePivotFk);

            // Rename main table → domains
            Schema::rename($sourceTable, 'domains');

            // Rename pivot table + FK column → domain_forest_ad / domain_id
            Schema::rename($sourcePivot, 'domain_forest_ad');
            Schema::table('domain_forest_ad', function (Blueprint $t) use ($sourceFkCol): void {
                $t->renameColumn($sourceFkCol, 'domain_id');
            });

            // Restore FKs pointing to domains
            Schema::table('admin_users', function (Blueprint $t): void {
                $t->foreign('domain_id', 'domain_id_fk_69385935')
                    ->references('id')->on('domains')
                    ->onUpdate('NO ACTION')->onDelete('CASCADE');
            });
            Schema::table('logical_servers', function (Blueprint $t): void {
                $t->foreign('domain_id', 'domain_id_fk_493844')
                    ->references('id')->on('domains')
                    ->onUpdate('NO ACTION')->onDelete('SET NULL');
            });
            Schema::table('workstations', function (Blueprint $t): void {
                $t->foreign('domain_id', 'workstations_domain_id_foreign')
                    ->references('id')->on('domains');
            });
            Schema::table('domain_forest_ad', function (Blueprint $t): void {
                $t->foreign('domain_id', 'domain_id_fk_1492084')
                    ->references('id')->on('domains')
                    ->onUpdate('NO ACTION')->onDelete('CASCADE');
            });
        }
    }

    public function down(): void
    {
        // Drop FKs pointing to domains
        $this->dropForeignIfExists('admin_users', 'domain_id_fk_69385935');
        $this->dropForeignIfExists('logical_servers', 'domain_id_fk_493844');
        $this->dropForeignIfExists('workstations', 'workstations_domain_id_foreign');
        $this->dropForeignIfExists('domain_forest_ad', 'domain_id_fk_1492084');

        // Rename back to domaine_ads (canonical prior state)
        Schema::table('domain_forest_ad', function (Blueprint $t): void {
            $t->renameColumn('domain_id', 'domaine_ad_id');
        });
        Schema::rename('domain_forest_ad', 'domaine_ad_forest_ad');
        Schema::rename('domains', 'domaine_ads');

        // Restore FKs pointing to domaine_ads
        Schema::table('admin_users', function (Blueprint $t): void {
            $t->foreign('domain_id', 'domain_id_fk_69385935')
                ->references('id')->on('domaine_ads')
                ->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
        Schema::table('logical_servers', function (Blueprint $t): void {
            $t->foreign('domain_id', 'domain_id_fk_493844')
                ->references('id')->on('domaine_ads')
                ->onUpdate('NO ACTION')->onDelete('SET NULL');
        });
        Schema::table('workstations', function (Blueprint $t): void {
            $t->foreign('domain_id', 'workstations_domain_id_foreign')
                ->references('id')->on('domaine_ads');
        });
        Schema::table('domaine_ad_forest_ad', function (Blueprint $t): void {
            $t->foreign('domaine_ad_id', 'domaine_ad_id_fk_1492084')
                ->references('id')->on('domaine_ads')
                ->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }
};
