<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_services', function (Blueprint $table) {
            $table->longText('comments')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('application_services', function (Blueprint $table) {
            $table->dropColumn('comments');
        });
    }
};
