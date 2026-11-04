<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cartographers', function (Blueprint $table) {
            $table->id();
            $table->string('cartographiable_type');
            $table->unsignedBigInteger('cartographiable_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('role_id')->nullable();
            $table->timestamps();

            $table->index(['cartographiable_type', 'cartographiable_id']);
            $table->index('user_id');
            $table->index('role_id');
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('cartographers', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            });
        }

        // Add permissions
        if (Permission::query()->count() === 0) {
            return;
        }

        Permission::query()->insert([
            ['id' => 330, 'title' => 'cartographer_create'],
            ['id' => 331, 'title' => 'cartographer_edit'],
            ['id' => 332, 'title' => 'cartographer_show'],
            ['id' => 333, 'title' => 'cartographer_delete'],
            ['id' => 334, 'title' => 'cartographer_access'],
        ]);

        $adminId = DB::table('roles')->where('title', 'Admin')->value('id');
        if ($adminId) {
            Role::query()->findOrFail($adminId)->permissions()->syncWithoutDetaching([330, 331, 332, 333, 334]);
        }

    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('cartographers', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['role_id']);
            });
        }

        Schema::dropIfExists('cartographers');
    }
};
