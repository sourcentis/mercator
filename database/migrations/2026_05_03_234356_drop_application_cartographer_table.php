<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        if (! $isSqlite) {
            Schema::table('application_cartographer', function (Blueprint $table): void {
                $table->dropForeign('application_cartographer_application_id_foreign');
                $table->dropForeign('cartographer_m_application_user_id_foreign');
            });
        }

        Schema::dropIfExists('application_cartographer');
    }

    public function down(): void
    {
        Schema::create('application_cartographer', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('application_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('application_id', 'application_cartographer_application_id_foreign')
                ->references('id')->on('applications');
            $table->foreign('user_id', 'cartographer_m_application_user_id_foreign')
                ->references('id')->on('users');
        });
    }
};
