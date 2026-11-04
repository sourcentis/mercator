<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('logical_server_id');
            $table->unsignedInteger('storage_device_id');

            $table->unsignedTinyInteger('backup_frequency')->nullable()->comment('1=daily,2=weekly,3=monthly');
            $table->unsignedTinyInteger('backup_cycle')->nullable()->comment('Ex: 1 full/day + 1 weekly/month');
            $table->unsignedSmallInteger('backup_retention')->nullable()->comment('Retention in days');

            $table->foreign('logical_server_id')
                ->references('id')->on('logical_servers')
                ->onDelete('cascade');

            $table->foreign('storage_device_id')
                ->references('id')->on('storage_devices')
                ->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        // if not initial migration -> add permissions
        if (Permission::query()->count() > 0) {

            // create new permissions
            $permissions = [
                [
                    'id' => '315',
                    'title' => 'backup_create',
                ],
                [
                    'id' => '316',
                    'title' => 'backup_edit',
                ],
                [
                    'id' => '317',
                    'title' => 'backup_show',
                ],
                [
                    'id' => '318',
                    'title' => 'backup_delete',
                ],
                [
                    'id' => '319',
                    'title' => 'backup_access',
                ],
            ];
            Permission::query()->insert($permissions);

            // Add permissions in roles :
            // Admin
            Role::query()->findOrFail(1)->permissions()->sync([315, 316, 317, 318, 319], false);
            // User
            Role::query()->findOrFail(2)->permissions()->sync([315, 316, 317, 318, 319], false);
            // Auditor
            Role::query()->findOrFail(3)->permissions()->sync([317, 319], false);
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('backups');

        DB::delete('delete from permissions where id=315;');
        DB::delete('delete from permissions where id=316;');
        DB::delete('delete from permissions where id=317;');
        DB::delete('delete from permissions where id=318;');
        DB::delete('delete from permissions where id=319;');

    }
};
