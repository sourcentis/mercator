<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ajoute query_export (génération des liens d'export CSV signés) et
     * l'attribue aux rôles qui peuvent déjà créer des requêtes.
     */
    public function up(): void
    {
        // Installation neuve : PermissionsTableSeeder s'en charge déjà.
        if (Permission::query()->count() === 0) {
            return;
        }

        if (Permission::query()->where('id', 336)->exists()) {
            return;
        }

        Permission::query()->insert([
            ['id' => 336, 'title' => 'query_export'],
        ]);

        $createId = DB::table('permissions')->where('title', 'query_create')->value('id');
        if (! $createId) {
            return;
        }

        $roleIds = DB::table('permission_role')
            ->where('permission_id', $createId)
            ->pluck('role_id');

        DB::table('permission_role')->insert(
            $roleIds->map(fn ($roleId) => ['role_id' => $roleId, 'permission_id' => 336])->all()
        );
    }

    public function down(): void
    {
        DB::table('permission_role')->where('permission_id', 336)->delete();
        DB::table('permissions')->where('id', 336)->delete();
    }
};
