<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tentative de suppression sécurisée d'une contrainte unique.
     * Ignore l'erreur si la contrainte n'existe pas.
     */
    protected function dropUniqueIfExists(string $tableName, string $constraintName): void
    {
        try {
            Schema::table($tableName, function (Blueprint $table) use ($constraintName) {
                $table->dropUnique($constraintName);
            });
        } catch (QueryException $e) {
            // Ici on peut logger l'erreur si besoin ou simplement ignorer
            // Contrainte absente sans impact, on continue
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->dropUniqueIfExists('activities', 'activities_name_unique');
        $this->dropUniqueIfExists('actors', 'actors_name_unique');
        $this->dropUniqueIfExists('application_blocks', 'application_blocks_name_unique');
        $this->dropUniqueIfExists('application_modules', 'application_modules_name_unique');
        $this->dropUniqueIfExists('application_services', 'application_services_name_unique');
        $this->dropUniqueIfExists('dhcp_servers', 'dhcp_servers_name_unique');
        $this->dropUniqueIfExists('dnsservers', 'dnsservers_name_unique');
        $this->dropUniqueIfExists('domaine_ads', 'domaine_ads_name_unique');
        $this->dropUniqueIfExists('gateways', 'gateways_name_unique');
        $this->dropUniqueIfExists('information', 'information_name_unique');
        $this->dropUniqueIfExists('lans', 'lans_name_unique');
        $this->dropUniqueIfExists('macro_processuses', 'macro_processuses_name_unique');
        $this->dropUniqueIfExists('mans', 'men_name_unique');
        $this->dropUniqueIfExists('network_switches', 'network_switches_name_unique');
        $this->dropUniqueIfExists('networks', 'networks_name_unique');
        $this->dropUniqueIfExists('security_devices', 'security_devices_name_unique');
        $this->dropUniqueIfExists('tasks', 'tasks_nom_unique');
        $this->dropUniqueIfExists('vlans', 'vlans_name_unique');
        $this->dropUniqueIfExists('zone_admins', 'zone_admins_name_unique');
        $this->dropUniqueIfExists('bays', 'bays_name_unique');
        $this->dropUniqueIfExists('buildings', 'buildings_name_unique');
        $this->dropUniqueIfExists('databases', 'databases_name_unique');
        $this->dropUniqueIfExists('forest_ads', 'forest_ads_name_unique');
        $this->dropUniqueIfExists('phones', 'phones_name_unique');
        $this->dropUniqueIfExists('physical_security_devices', 'physical_security_devices_name_unique');
        $this->dropUniqueIfExists('physical_switches', 'physical_switches_name_unique');
        $this->dropUniqueIfExists('processes', 'processes_identifiant_unique');
        $this->dropUniqueIfExists('storage_devices', 'storage_devices_name_unique');
        $this->dropUniqueIfExists('wifi_terminals', 'wifi_terminals_name_unique');
        $this->dropUniqueIfExists('certificates', 'certificate_name_unique');
        $this->dropUniqueIfExists('subnetworks', 'subnetwords_name_unique');
        $this->dropUniqueIfExists('physical_routers', 'name');
        $this->dropUniqueIfExists('operations', 'operations_name_unique');
        $this->dropUniqueIfExists('external_connected_entities', 'external_connected_entities_name_unique');
        $this->dropUniqueIfExists('security_controls', 'security_controls_name_unique');
        $this->dropUniqueIfExists('clusters', 'cluster_name_unique');
        $this->dropUniqueIfExists('physical_servers', 'physical_servers_name_unique');
        $this->dropUniqueIfExists('routers', 'routers_name_unique');
        $this->dropUniqueIfExists('logical_servers', 'logical_servers_name_unique');
        $this->dropUniqueIfExists('m_applications', 'm_applications_name_unique');
        $this->dropUniqueIfExists('workstations', 'workstations_name_unique');
        $this->dropUniqueIfExists('peripherals', 'peripherals_name_unique');
        $this->dropUniqueIfExists('sites', 'sites_name_unique');
        $this->dropUniqueIfExists('entities', 'entities_name_unique');
    }

    /**
     * Reverse the migrations.
     * Ici en exemple on rajoute les contraintes supprimées,
     * adapter selon vos besoins réels.
     */
    public function down(): void
    {
        // Not Rollback
    }
};
