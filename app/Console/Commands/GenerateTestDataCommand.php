<?php

namespace App\Console\Commands;

use App\Services\DataScenario\ScenarioBuilder;
use Faker\Factory;
use Illuminate\Console\Command;
use InvalidArgumentException;

class GenerateTestDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mercator:generate-test-data
        {--scenario= : Profil prédéfini (voir config/data-scenarios.php)}
        {--perimeters= : Nombre total de périmètres (périmètre par défaut inclus)}
        {--applications= : Nombre d\'applications à générer}
        {--databases= : Nombre de bases de données à générer}
        {--servers= : Nombre de serveurs logiques à générer}
        {--flows= : Nombre de flux applicatifs à générer}
        {--sites= : Nombre de sites à générer}
        {--buildings= : Nombre de bâtiments à générer}
        {--bays= : Nombre de baies à générer}
        {--physical-servers= : Nombre de serveurs physiques à générer}
        {--peripherals= : Nombre de périphériques à générer}
        {--workstations= : Nombre de postes de travail à générer}
        {--security-zones= : Nombre de zones de sécurité à générer}
        {--chunk= : Taille des lots d\'insertion}
        {--seed= : Graine aléatoire pour des données reproductibles}
        {--dry-run : Calcule et affiche le plan de génération sans écrire en base}
        {--force : Ignore la confirmation interactive}';

    /**
     * The console command description.
     */
    protected $description = 'Génère un jeu de données de test réaliste (périmètres, applications, bases de données, serveurs, flux, infrastructure physique) pour la démonstration ou les tests de charge';

    /**
     * Options CLI dont le nom (avec tirets) diffère de la clé de compteur
     * utilisée par ScenarioBuilder/config (avec underscores).
     */
    private const COUNT_OPTIONS = [
        'perimeters' => 'perimeters',
        'applications' => 'applications',
        'databases' => 'databases',
        'servers' => 'servers',
        'flows' => 'flows',
        'sites' => 'sites',
        'buildings' => 'buildings',
        'bays' => 'bays',
        'physical-servers' => 'physical_servers',
        'peripherals' => 'peripherals',
        'workstations' => 'workstations',
        'security-zones' => 'security_zones',
    ];

    public function handle(): int
    {
        // Laravel only defines the fake() helper when Faker is installed
        if (! class_exists(Factory::class)) {
            $this->error('La librairie fakerphp/faker est introuvable. Installez-la avec "composer require fakerphp/faker" ou lancez "composer install" sans --no-dev.');

            return self::FAILURE;
        }

        $scenarios = (array) config('data-scenarios.scenarios', []);
        $scenarioName = $this->option('scenario');

        $hasManualCounts = collect(array_keys(self::COUNT_OPTIONS))->contains(fn (string $option) => $this->option($option) !== null);

        if ($scenarioName === null && ! $hasManualCounts) {
            $scenarioName = (string) config('data-scenarios.default_scenario', 'pme');
        }

        $base = [];

        if ($scenarioName !== null) {
            if (! isset($scenarios[$scenarioName])) {
                $this->error("Scénario inconnu : \"{$scenarioName}\". Scénarios disponibles : ".implode(', ', array_keys($scenarios)));

                return self::FAILURE;
            }

            $base = $scenarios[$scenarioName];
        }

        $counts = [];

        foreach (self::COUNT_OPTIONS as $option => $key) {
            $default = $key === 'perimeters' ? 1 : 0;
            $counts[$key] = (int) ($this->option($option) ?? $base[$key] ?? $default);
        }

        $chunkSize = (int) ($this->option('chunk') ?? config('data-scenarios.chunk_size', 500));
        $seedOption = $this->option('seed');
        $seed = $seedOption !== null ? (int) $seedOption : null;
        $dryRun = (bool) $this->option('dry-run');
        $wifiTerminalProbability = (float) config('data-scenarios.wifi_terminal_probability', 0.8);
        $wifiTerminalPercent = (int) round($wifiTerminalProbability * 100);
        $floorsPerSite = config('data-scenarios.floors_per_site', ['min' => 1, 'max' => 8]);
        $localsPerFloor = config('data-scenarios.locals_per_floor', ['min' => 5, 'max' => 10]);
        $subnetworksPerNetwork = config('data-scenarios.subnetworks_per_network', ['min' => 4, 'max' => 16]);
        $applicationBlocksPerPerimeter = config('data-scenarios.application_blocks_per_perimeter', ['min' => 5, 'max' => 7]);
        $zoneAdminsPerSite = config('data-scenarios.zone_admins_per_site', ['min' => 1, 'max' => 3]);
        $domainsPerSite = config('data-scenarios.domains_per_site', ['min' => 3, 'max' => 5]);
        $applicationServicesPerApplication = config('data-scenarios.application_services_per_application', ['min' => 0, 'max' => 5]);
        $applicationModulesPerService = config('data-scenarios.application_modules_per_service', ['min' => 0, 'max' => 3]);
        $macroProcessesPerPerimeter = config('data-scenarios.macro_processes_per_perimeter', ['min' => 3, 'max' => 5]);
        $processesPerMacroProcess = config('data-scenarios.processes_per_macro_process', ['min' => 0, 'max' => 10]);
        $activitiesPerProcess = config('data-scenarios.activities_per_process', ['min' => 0, 'max' => 10]);
        $operationsPerActivity = config('data-scenarios.operations_per_activity', ['min' => 0, 'max' => 3]);
        $tasksPerOperation = config('data-scenarios.tasks_per_operation', ['min' => 0, 'max' => 3]);
        $actorsPerPerimeter = config('data-scenarios.actors_per_perimeter', ['min' => 1, 'max' => 10]);
        $actorOperationsPerActor = config('data-scenarios.actor_operations_per_actor', ['min' => 1, 'max' => 5]);
        $informationsPerPerimeter = config('data-scenarios.informations_per_perimeter', ['min' => 1, 'max' => 20]);
        $entitiesPerPerimeter = (int) config('data-scenarios.entities_per_perimeter', 100);
        $relationsPerEntity = config('data-scenarios.relations_per_entity', ['min' => 0, 'max' => 3]);
        $informationsPerDatabase = config('data-scenarios.informations_per_database', ['min' => 1, 'max' => 5]);
        $informationsPerFlow = config('data-scenarios.informations_per_flow', ['min' => 0, 'max' => 2]);
        $dataProcessingPerPerimeter = config('data-scenarios.data_processing_per_perimeter', ['min' => 20, 'max' => 50]);
        $dataProcessingLinksPerType = config('data-scenarios.data_processing_links_per_type', ['min' => 1, 'max' => 3]);
        $floorsEstimate = (int) round($counts['sites'] * (($floorsPerSite['min'] + $floorsPerSite['max']) / 2));
        $localsEstimate = (int) round($floorsEstimate * (($localsPerFloor['min'] + $localsPerFloor['max']) / 2));
        $subnetworksEstimate = (int) round($counts['sites'] * (($subnetworksPerNetwork['min'] + $subnetworksPerNetwork['max']) / 2));
        $applicationBlocksEstimate = (int) round(max($counts['perimeters'], 1) * (($applicationBlocksPerPerimeter['min'] + $applicationBlocksPerPerimeter['max']) / 2));
        $zoneAdminsEstimate = (int) round($counts['sites'] * (($zoneAdminsPerSite['min'] + $zoneAdminsPerSite['max']) / 2));
        $domainsEstimate = (int) round($counts['sites'] * (($domainsPerSite['min'] + $domainsPerSite['max']) / 2));
        $applicationServicesEstimate = (int) round($counts['applications'] * (($applicationServicesPerApplication['min'] + $applicationServicesPerApplication['max']) / 2));
        $applicationModulesEstimate = (int) round($applicationServicesEstimate * (($applicationModulesPerService['min'] + $applicationModulesPerService['max']) / 2));
        $macroProcessesEstimate = (int) round(max($counts['perimeters'], 1) * (($macroProcessesPerPerimeter['min'] + $macroProcessesPerPerimeter['max']) / 2));
        $processesEstimate = (int) round($macroProcessesEstimate * (($processesPerMacroProcess['min'] + $processesPerMacroProcess['max']) / 2));
        $activitiesEstimate = (int) round($processesEstimate * (($activitiesPerProcess['min'] + $activitiesPerProcess['max']) / 2));
        $operationsEstimate = (int) round($activitiesEstimate * (($operationsPerActivity['min'] + $operationsPerActivity['max']) / 2));
        $tasksEstimate = (int) round($operationsEstimate * (($tasksPerOperation['min'] + $tasksPerOperation['max']) / 2));
        $actorsEstimate = (int) round(max($counts['perimeters'], 1) * (($actorsPerPerimeter['min'] + $actorsPerPerimeter['max']) / 2));
        $informationsEstimate = (int) round(max($counts['perimeters'], 1) * (($informationsPerPerimeter['min'] + $informationsPerPerimeter['max']) / 2));
        $entitiesEstimate = max($counts['perimeters'], 1) * $entitiesPerPerimeter;
        $relationsEstimate = (int) round($entitiesEstimate * (($relationsPerEntity['min'] + $relationsPerEntity['max']) / 2));
        $dataProcessingEstimate = (int) round(max($counts['perimeters'], 1) * (($dataProcessingPerPerimeter['min'] + $dataProcessingPerPerimeter['max']) / 2));

        $this->table(['Objet', 'Quantité demandée'], [
            ['Périmètres (total)', $counts['perimeters']],
            ['Applications', $counts['applications']],
            ["Groupes applicatifs par périmètre (~{$applicationBlocksEstimate} au total)", "{$applicationBlocksPerPerimeter['min']}-{$applicationBlocksPerPerimeter['max']}"],
            ["Services applicatifs par application (~{$applicationServicesEstimate} au total)", "{$applicationServicesPerApplication['min']}-{$applicationServicesPerApplication['max']}"],
            ["Modules applicatifs par service (~{$applicationModulesEstimate} au total)", "{$applicationModulesPerService['min']}-{$applicationModulesPerService['max']}"],
            ['Bases de données', $counts['databases']],
            ['Serveurs logiques', $counts['servers']],
            ['Flux applicatifs (application/service/module/BDD)', $counts['flows']],
            ['Sites', $counts['sites']],
            ['Réseaux (= sites)', $counts['sites']],
            ["Sous-réseaux par réseau (~{$subnetworksEstimate} au total)", "{$subnetworksPerNetwork['min']}-{$subnetworksPerNetwork['max']}"],
            ['VLANs (= sous-réseaux)', $subnetworksEstimate],
            ['Bâtiments', $counts['buildings']],
            ['Baies', $counts['bays']],
            ["Étages par site (~{$floorsEstimate} au total)", "{$floorsPerSite['min']}-{$floorsPerSite['max']}"],
            ["Locaux par étage (~{$localsEstimate} au total)", "{$localsPerFloor['min']}-{$localsPerFloor['max']}"],
            ['Switchs physiques (= baies)', $counts['bays']],
            ['Switchs physiques (= étages)', $floorsEstimate],
            ['Routeurs physiques (<= sites)', $counts['sites']],
            ['Serveurs physiques', $counts['physical_servers']],
            ['Périphériques', $counts['peripherals']],
            ['Postes de travail (dans un local)', $counts['workstations']],
            ['Téléphones (= postes de travail, dans un local)', $counts['workstations']],
            ["Bornes WiFi (~{$wifiTerminalPercent}% des étages/locaux)", (int) round(($floorsEstimate + $localsEstimate) * $wifiTerminalProbability)],
            ['Zones de sécurité', $counts['security_zones']],
            ["Zones d'administration par site (~{$zoneAdminsEstimate} au total)", "{$zoneAdminsPerSite['min']}-{$zoneAdminsPerSite['max']}"],
            ['Annuaires (= sites)', $counts['sites']],
            ['Forêts Active Directory / LDAP (= sites)', $counts['sites']],
            ["Domaines par site (~{$domainsEstimate} au total)", "{$domainsPerSite['min']}-{$domainsPerSite['max']}"],
            ['Utilisateurs admin_users (= postes de travail)', $counts['workstations']],
            ["Macro-processus par périmètre (~{$macroProcessesEstimate} au total)", "{$macroProcessesPerPerimeter['min']}-{$macroProcessesPerPerimeter['max']}"],
            ["Processus par macro-processus (~{$processesEstimate} au total)", "{$processesPerMacroProcess['min']}-{$processesPerMacroProcess['max']}"],
            ["Activités par processus (~{$activitiesEstimate} au total)", "{$activitiesPerProcess['min']}-{$activitiesPerProcess['max']}"],
            ["Opérations par activité (~{$operationsEstimate} au total)", "{$operationsPerActivity['min']}-{$operationsPerActivity['max']}"],
            ["Tâches par opération (~{$tasksEstimate} au total)", "{$tasksPerOperation['min']}-{$tasksPerOperation['max']}"],
            ["Acteurs par périmètre (~{$actorsEstimate} au total)", "{$actorsPerPerimeter['min']}-{$actorsPerPerimeter['max']}"],
            ["Informations par périmètre (~{$informationsEstimate} au total)", "{$informationsPerPerimeter['min']}-{$informationsPerPerimeter['max']}"],
            ["Entités par périmètre (~{$entitiesEstimate} au total)", (string) $entitiesPerPerimeter],
            ["Relations par entité (~{$relationsEstimate} au total)", "{$relationsPerEntity['min']}-{$relationsPerEntity['max']}"],
            ["Traitements de données par périmètre (~{$dataProcessingEstimate} au total)", "{$dataProcessingPerPerimeter['min']}-{$dataProcessingPerPerimeter['max']}"],
        ]);

        if ($counts['perimeters'] > 1) {
            $this->info('Plus d\'un périmètre : la fonctionnalité périmètres sera activée, les rôles admin.perimeter.<nom>, user.perimeter.<nom> et auditor.perimeter.<nom> seront créés pour chacun, et le compte admin@admin.com (s\'il existe) sera rattaché aux rôles admin.');
        }

        if (! $dryRun && ! $this->option('force') && ! $this->confirm('Confirmer la génération de ces données de test ?', true)) {
            $this->info('Annulé.');

            return self::SUCCESS;
        }

        $bar = null;

        $onPhaseStart = function (string $label, int $total) use (&$bar) {
            $this->info("{$label}...");

            if ($total > 0) {
                $bar = $this->output->createProgressBar($total);
                $bar->start();
            }
        };

        $onTick = function (int $advance = 1) use (&$bar) {
            $bar?->advance($advance);
        };

        $onPhaseEnd = function () use (&$bar) {
            if ($bar) {
                $bar->finish();
                $this->newLine();
                $bar = null;
            }
        };

        $builder = new ScenarioBuilder(
            tuning: [
                'databases_per_application' => config('data-scenarios.databases_per_application', ['min' => 0, 'max' => 2]),
                'buildings_per_zone' => config('data-scenarios.buildings_per_zone', ['min' => 1, 'max' => 3]),
                'floors_per_site' => $floorsPerSite,
                'locals_per_floor' => $localsPerFloor,
                'subnetworks_per_network' => $subnetworksPerNetwork,
                'application_blocks_per_perimeter' => $applicationBlocksPerPerimeter,
                'zone_admins_per_site' => $zoneAdminsPerSite,
                'domains_per_site' => $domainsPerSite,
                'application_services_per_application' => $applicationServicesPerApplication,
                'application_modules_per_service' => $applicationModulesPerService,
                'macro_processes_per_perimeter' => $macroProcessesPerPerimeter,
                'processes_per_macro_process' => $processesPerMacroProcess,
                'activities_per_process' => $activitiesPerProcess,
                'operations_per_activity' => $operationsPerActivity,
                'tasks_per_operation' => $tasksPerOperation,
                'actors_per_perimeter' => $actorsPerPerimeter,
                'actor_operations_per_actor' => $actorOperationsPerActor,
                'informations_per_perimeter' => $informationsPerPerimeter,
                'informations_per_database' => $informationsPerDatabase,
                'informations_per_flow' => $informationsPerFlow,
                'entities_per_perimeter' => $entitiesPerPerimeter,
                'relations_per_entity' => $relationsPerEntity,
                'data_processing_per_perimeter' => $dataProcessingPerPerimeter,
                'data_processing_links_per_type' => $dataProcessingLinksPerType,
                'cross_perimeter_flow_probability' => (float) config('data-scenarios.cross_perimeter_flow_probability', 0.1),
                'wifi_terminal_probability' => $wifiTerminalProbability,
            ],
            chunkSize: $chunkSize,
            onPhaseStart: $onPhaseStart,
            onTick: $onTick,
            onPhaseEnd: $onPhaseEnd,
        );

        try {
            $result = $builder->build($counts, dryRun: $dryRun, seed: $seed);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('Dry-run : aucune donnée n\'a été écrite en base.');
        }

        $this->table(['Résultat', 'Valeur'], [
            ['Périmètres (total / créés)', "{$result['perimeters_total']} / {$result['perimeters_created']}"],
            ['Fonctionnalité périmètres activée', $result['perimeters_feature_enabled'] ? 'Oui' : 'Non'],
            ['Rôles admin.perimeter.* créés', $result['admin_roles_created']],
            ['Rôles user.perimeter.* créés', $result['user_roles_created']],
            ['Rôles auditor.perimeter.* créés', $result['auditor_roles_created']],
            ['Compte admin@admin.com rattaché aux rôles', $result['admin_user_linked'] ? 'Oui' : 'Non'],
            ['Applications créées', $result['applications']],
            ['Groupes applicatifs créés', $result['application_blocks']],
            ['Services applicatifs créés', $result['application_services']],
            ['Liens applications <-> services applicatifs', $result['application_service_links'] ?? 'n/a (dry-run)'],
            ['Modules applicatifs créés', $result['application_modules']],
            ['Liens services applicatifs <-> modules applicatifs', $result['service_module_links'] ?? 'n/a (dry-run)'],
            ['Bases de données créées', $result['databases']],
            ['Serveurs logiques créés', $result['servers']],
            ['Liens serveurs logiques <-> serveurs physiques', $result['logical_physical_server_links'] ?? 'n/a (dry-run)'],
            ['Liens applications <-> serveurs', $result['server_links'] ?? 'n/a (dry-run)'],
            ['Liens applications <-> bases de données', $result['database_links'] ?? 'n/a (dry-run)'],
            ['Flux applicatifs créés', $result['flows']],
            ['Sites créés', $result['sites']],
            ['Réseaux créés', $result['networks']],
            ['Sous-réseaux créés', $result['subnetworks']],
            ['VLANs créés', $result['vlans']],
            ['Bâtiments créés', $result['buildings']],
            ['Baies créées', $result['bays']],
            ['Étages créés', $result['floors']],
            ['Locaux créés', $result['locals']],
            ['Switchs physiques créés (baies)', $result['physical_switches']],
            ['Switchs physiques créés (étages)', $result['floor_switches']],
            ['Routeurs physiques créés', $result['physical_routers']],
            ['Serveurs physiques créés', $result['physical_servers']],
            ['Liens serveurs <-> switchs', $result['server_switch_links'] ?? 'n/a (dry-run)'],
            ['Liens switchs de baie <-> routeurs', $result['switch_router_links'] ?? 'n/a (dry-run)'],
            ['Liens switchs d\'étage <-> routeurs', $result['floor_switch_router_links'] ?? 'n/a (dry-run)'],
            ['Périphériques créés', $result['peripherals']],
            ['Postes de travail créés', $result['workstations']],
            ['Liens postes de travail <-> switchs', $result['workstation_switch_links'] ?? 'n/a (dry-run)'],
            ['Téléphones créés', $result['phones']],
            ['Bornes WiFi créées', $result['wifi_terminals']],
            ['Liens bornes WiFi <-> switchs', $result['wifi_switch_links'] ?? 'n/a (dry-run)'],
            ['Zones de sécurité créées', $result['security_zones']],
            ['Liens zones <-> bâtiments', $result['zone_building_links'] ?? 'n/a (dry-run)'],
            ["Zones d'administration créées", $result['zone_admins']],
            ['Annuaires créés', $result['annuaires']],
            ['Forêts Active Directory / LDAP créées', $result['forest_ads']],
            ['Domaines créés', $result['domains']],
            ['Liens forêts AD <-> domaines', $result['domain_forest_ad_links'] ?? 'n/a (dry-run)'],
            ['Utilisateurs admin_users créés', $result['admin_users']],
            ['Macro-processus créés', $result['macro_processes']],
            ['Processus créés', $result['processes']],
            ['Liens processus <-> activités', $result['process_activity_links'] ?? 'n/a (dry-run)'],
            ['Activités créées', $result['activities']],
            ['Liens activités <-> opérations', $result['activity_operation_links'] ?? 'n/a (dry-run)'],
            ['Opérations créées', $result['operations']],
            ['Liens opérations <-> tâches', $result['operation_task_links'] ?? 'n/a (dry-run)'],
            ['Tâches créées', $result['tasks']],
            ['Acteurs créés', $result['actors']],
            ['Liens acteurs <-> opérations', $result['actor_operation_links'] ?? 'n/a (dry-run)'],
            ['Informations créées', $result['informations']],
            ['Liens bases de données <-> informations', $result['database_information_links'] ?? 'n/a (dry-run)'],
            ['Liens flux applicatifs <-> informations', $result['flow_information_links'] ?? 'n/a (dry-run)'],
            ['Entités créées', $result['entities']],
            ['Relations créées', $result['relations']],
            ['Traitements de données créés', $result['data_processing']],
            ['Liens traitements de données <-> applications', $result['data_processing_application_links'] ?? 'n/a (dry-run)'],
            ['Liens traitements de données <-> processus', $result['data_processing_process_links'] ?? 'n/a (dry-run)'],
            ['Liens traitements de données <-> informations', $result['data_processing_information_links'] ?? 'n/a (dry-run)'],
        ]);

        return self::SUCCESS;
    }
}
