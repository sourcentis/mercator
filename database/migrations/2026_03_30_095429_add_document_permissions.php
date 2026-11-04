<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // if not initial migration -> add permissions
        if (Permission::query()->count() > 0) {
            // create new permissions
            Permission::query()->insert(
                [
                    [
                        'id' => 310,
                        'title' => 'document_create',
                    ],
                    [
                        'id' => 311,
                        'title' => 'document_edit',
                    ],
                    [
                        'id' => 312,
                        'title' => 'document_show',
                    ],
                    [
                        'id' => 313,
                        'title' => 'document_delete',
                    ],
                    [
                        'id' => 314,
                        'title' => 'document_access',
                    ],
                ]);

            // Add permissions in roles :
            // Admin
            $roleId = DB::table('roles')
                ->where('title', 'Admin')
                ->value('id');
            if ($roleId) {
                Role::query()->findOrFail($roleId)->permissions()->sync([310, 311, 312, 313, 314], false);
            }

            // User
            $roleId = DB::table('roles')
                ->where('title', 'User')
                ->value('id');
            if ($roleId) {
                Role::query()->findOrFail($roleId)->permissions()->sync([310, 311, 312, 313, 314], false);
            }

            // Auditor
            $roleId = DB::table('roles')
                ->where('title', 'Auditor')
                ->value('id');
            if ($roleId) {
                Role::query()->findOrFail($roleId)->permissions()->sync([312, 314], false);
            }
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::delete('delete from permissions where id=310;');
        DB::delete('delete from permissions where id=311;');
        DB::delete('delete from permissions where id=312;');
        DB::delete('delete from permissions where id=313;');
        DB::delete('delete from permissions where id=314;');
    }
};
