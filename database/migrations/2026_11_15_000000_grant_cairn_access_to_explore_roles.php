<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Donne cairn_access à tous les rôles qui ont déjà explore_access.
     */
    public function up(): void
    {
        $exploreId = DB::table('permissions')->where('title', 'explore_access')->value('id');
        $cairnId = DB::table('permissions')->where('title', 'cairn_access')->value('id');

        if (! $exploreId || ! $cairnId) {
            return;
        }

        $roleIds = DB::table('permission_role')
            ->where('permission_id', $exploreId)
            ->whereNotIn('role_id', function ($query) use ($cairnId) {
                $query->select('role_id')
                    ->from('permission_role')
                    ->where('permission_id', $cairnId);
            })
            ->pluck('role_id');

        DB::table('permission_role')->insert(
            $roleIds->map(fn ($roleId) => ['role_id' => $roleId, 'permission_id' => $cairnId])->all()
        );
    }

    public function down(): void
    {
        // Irréversible : impossible de distinguer les attributions faites par
        // cette migration de celles faites manuellement.
    }
};
