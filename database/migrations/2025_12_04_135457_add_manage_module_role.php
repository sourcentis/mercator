<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

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
            Permission::query()->insert([
                'id' => 309,
                'title' => 'module_manage',
            ]);

            // Add permissions in role admin
            Role::query()
                ->where('title', '=', 'Admin')
                ->firstOrFail()
                ->permissions()
                ->sync([309], false);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::delete('delete from permissions where id=309;');
    }
};
