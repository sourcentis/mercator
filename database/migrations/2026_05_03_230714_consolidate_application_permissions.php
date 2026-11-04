<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Consolidate duplicate application_* permissions.
 *
 * IDs 64-68 (application_create/edit/show/delete/access) already existed but had no role assignments.
 * IDs 70-74 were m_application_* renamed to application_*, now duplicates.
 * ID 69 ('papplication_access') is a typo artifact.
 *
 * This migration transfers role assignments from 70-74 to 64-68, then removes the duplicates.
 */
return new class extends Migration
{
    /** @var array<int, int> old_id => canonical_id */
    private array $map = [
        70 => 64, // application_create
        71 => 65, // application_edit
        72 => 66, // application_show
        73 => 67, // application_delete
        74 => 68, // application_access
    ];

    public function up(): void
    {
        foreach ($this->map as $oldId => $canonicalId) {
            // Get roles assigned to old permission that aren't yet on canonical
            $existingRoles = DB::table('permission_role')
                ->where('permission_id', $canonicalId)
                ->pluck('role_id')
                ->toArray();

            $toInsert = DB::table('permission_role')
                ->where('permission_id', $oldId)
                ->whereNotIn('role_id', $existingRoles)
                ->get()
                ->map(fn ($r) => ['role_id' => $r->role_id, 'permission_id' => $canonicalId])
                ->toArray();

            if (! empty($toInsert)) {
                DB::table('permission_role')->insert($toInsert);
            }

            // Remove old permission_role entries and the old permission itself
            DB::table('permission_role')->where('permission_id', $oldId)->delete();
        }

        DB::table('permissions')->whereIn('id', array_keys($this->map))->delete();

        // Remove 'papplication_access' typo (ID 69)
        DB::table('permission_role')->where('permission_id', 69)->delete();
        DB::table('permissions')->where('id', 69)->delete();
    }

    public function down(): void
    {
        // Restore permissions 69-74
        DB::table('permissions')->insert([
            ['id' => 69, 'title' => 'papplication_access'],
            ['id' => 70, 'title' => 'application_create'],
            ['id' => 71, 'title' => 'application_edit'],
            ['id' => 72, 'title' => 'application_show'],
            ['id' => 73, 'title' => 'application_delete'],
            ['id' => 74, 'title' => 'application_access'],
        ]);

        // Move role assignments back from 64-68 to 70-74
        foreach ($this->map as $oldId => $canonicalId) {
            $roles = DB::table('permission_role')
                ->where('permission_id', $canonicalId)
                ->pluck('role_id');

            $roles->each(function ($roleId) use ($oldId): void {
                DB::table('permission_role')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $oldId]
                );
            });
        }

        // Remove canonical entries (64-67; 68 stays as it was)
        DB::table('permission_role')->whereIn('permission_id', [64, 65, 66, 67])->delete();
    }
};
