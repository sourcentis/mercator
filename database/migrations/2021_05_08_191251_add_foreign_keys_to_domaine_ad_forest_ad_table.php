<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('domaine_ad_forest_ad', function (Blueprint $table) {
            $table->foreign('domaine_ad_id', 'domaine_ad_id_fk_1492084')->references('id')->on('domaine_ads')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('forest_ad_id', 'forest_ad_id_fk_1492084')->references('id')->on('forest_ads')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    public function down()
    {
        Schema::table('domaine_ad_forest_ad', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign('domaine_ad_id_fk_1492084');
            }
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign('forest_ad_id_fk_1492084');
            }
        });
    }
};
