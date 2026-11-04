<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $renames = [
            'flux_access' => 'application_flow_access',
            'flux_create' => 'application_flow_create',
            'flux_edit' => 'application_flow_edit',
            'flux_show' => 'application_flow_show',
            'flux_delete' => 'application_flow_delete',
            'domaine_ad_access' => 'domain_access',
            'domaine_ad_create' => 'domain_create',
            'domaine_ad_edit' => 'domain_edit',
            'domaine_ad_show' => 'domain_show',
            'domaine_ad_delete' => 'domain_delete',
        ];

        foreach ($renames as $old => $new) {
            DB::table('permissions')->where('title', $old)->update(['title' => $new]);
        }
    }

    public function down(): void
    {
        $renames = [
            'application_flow_access' => 'flux_access',
            'application_flow_create' => 'flux_create',
            'application_flow_edit' => 'flux_edit',
            'application_flow_show' => 'flux_show',
            'application_flow_delete' => 'flux_delete',
            'domain_access' => 'domaine_ad_access',
            'domain_create' => 'domaine_ad_create',
            'domain_edit' => 'domaine_ad_edit',
            'domain_show' => 'domaine_ad_show',
            'domain_delete' => 'domaine_ad_delete',
        ];

        foreach ($renames as $old => $new) {
            DB::table('permissions')->where('title', $old)->update(['title' => $new]);
        }
    }
};
