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

        Schema::create('saved_queries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('query');
            $table->boolean('is_public')->default(false);
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index(['user_id', 'is_public']);
        });

        // if not initial migration -> add permissions
        if (Permission::query()->count() > 0) {

            // create new permissions
            $permissions = [
                [
                    'id' => '320',
                    'title' => 'query_create',
                ],
                [
                    'id' => '321',
                    'title' => 'query_edit',
                ],
                [
                    'id' => '322',
                    'title' => 'query_show',
                ],
                [
                    'id' => '323',
                    'title' => 'query_delete',
                ],
                [
                    'id' => '324',
                    'title' => 'query_access',
                ],
            ];
            Permission::query()->insert($permissions);

            // Add permissions in roles :
            // Admin
            Role::query()->findOrFail(1)->permissions()->sync([320, 321, 322, 323, 324], false);
            // User
            Role::query()->findOrFail(2)->permissions()->sync([320, 321, 322, 323, 324], false);
            // Auditor
            Role::query()->findOrFail(3)->permissions()->sync([322, 324], false);
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('saved_queries');

        DB::delete('delete from permissions where id=320;');
        DB::delete('delete from permissions where id=321;');
        DB::delete('delete from permissions where id=322;');
        DB::delete('delete from permissions where id=323;');
        DB::delete('delete from permissions where id=324;');

    }
};
