<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scénario par défaut
    |--------------------------------------------------------------------------
    |
    | Utilisé par `mercator:generate-test-data` quand ni --scenario ni aucun
    | des compteurs manuels (--applications, --servers, ...) n'est fourni.
    */
    'default_scenario' => 'pme',

    /*
    |--------------------------------------------------------------------------
    | Profils de génération
    |--------------------------------------------------------------------------
    |
    | `perimeters` est un total (le périmètre par défaut, id=1, compte pour 1) :
    | seuls les périmètres manquants sont créés. Les compteurs manuels de la
    | commande (--applications=..., etc.) surchargent individuellement les
    | valeurs du scénario choisi.
    */
    'scenarios' => [

        'pme' => [
            'perimeters' => 1,
            'applications' => 15,
            'databases' => 5,
            'servers' => 8,
            'flows' => 7,
            'sites' => 1,
            'buildings' => 2,
            'bays' => 4,
            'physical_servers' => 6,
            'peripherals' => 4,
            'workstations' => 10,
            'security_zones' => 2,
        ],

        'mid_market' => [
            'perimeters' => 2,
            'applications' => 80,
            'databases' => 25,
            'servers' => 40,
            'flows' => 50,
            'sites' => 3,
            'buildings' => 6,
            'bays' => 12,
            'physical_servers' => 20,
            'peripherals' => 15,
            'workstations' => 100,
            'security_zones' => 14,
        ],

        'large_enterprise' => [
            'perimeters' => 4,
            'applications' => 400,
            'databases' => 100,
            'servers' => 200,
            'flows' => 400,
            'sites' => 6,
            'buildings' => 15,
            'bays' => 40,
            'physical_servers' => 80,
            'peripherals' => 40,
            'workstations' => 800,
            'security_zones' => 20,
        ],

        'load_test' => [
            'perimeters' => 2,
            'applications' => 1000,
            'databases' => 200,
            'servers' => 2000,
            'flows' => 1667,
            'sites' => 4,
            'buildings' => 100,
            'bays' => 30,
            'physical_servers' => 100,
            'peripherals' => 300,
            'workstations' => 1500,
            'security_zones' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Réglages de réalisme / cohérence
    |--------------------------------------------------------------------------
    */

    // Nombre de bases de données rattachées à chaque application. Le nombre
    // de serveurs logiques rattachés à chaque application (1, 2 ou 3, 90% des
    // applications n'en ayant qu'un) n'est pas configurable ici — voir
    // APPLICATION_SERVER_COUNTS dans ScenarioBuilder.
    'databases_per_application' => ['min' => 0, 'max' => 2],

    // Nombre de bâtiments rattachés à chaque zone de sécurité.
    'buildings_per_zone' => ['min' => 1, 'max' => 3],

    // Nombre d'étages (ET1, ET2, ...) générés pour chaque site, et de locaux
    // (LOCAL042, ...) générés pour chaque étage. Contrairement aux compteurs
    // globaux (--sites, --buildings, ...), ce sont des tirages directs par
    // site/étage, pas un total réparti sur les périmètres.
    'floors_per_site' => ['min' => 1, 'max' => 8],
    'locals_per_floor' => ['min' => 5, 'max' => 10],

    // Nombre de sous-réseaux générés pour le réseau logique de chaque site
    // (un seul réseau par site, nommé comme le site) — tirage direct, pas un
    // compteur global réparti sur les périmètres.
    'subnetworks_per_network' => ['min' => 4, 'max' => 16],

    // Nombre de groupes applicatifs (application_blocks) générés pour chaque
    // périmètre — tirage direct, pas un compteur global réparti sur les
    // périmètres.
    'application_blocks_per_perimeter' => ['min' => 5, 'max' => 7],

    // Nombre de zones d'administration et de domaines Active Directory/LDAP
    // générés pour chaque site — tirages directs, pas des compteurs globaux
    // répartis sur les périmètres. Un seul annuaire et une seule forêt AD
    // sont générés par site (pas configurable).
    'zone_admins_per_site' => ['min' => 1, 'max' => 3],
    'domains_per_site' => ['min' => 3, 'max' => 5],

    // Nombre de services applicatifs générés pour chaque application, et de
    // modules applicatifs générés pour chaque service — chacun dédié à son
    // seul parent (pas partagé), tirages directs comme les groupes
    // applicatifs ou les étages par site.
    'application_services_per_application' => ['min' => 0, 'max' => 5],
    'application_modules_per_service' => ['min' => 0, 'max' => 3],

    // Cartographie métier, chaînée de proche en proche et jamais partagée
    // d'un parent à l'autre — tirages directs par périmètre/macro-processus/
    // processus/activité/opération, pas des compteurs globaux répartis sur
    // les périmètres.
    'macro_processes_per_perimeter' => ['min' => 3, 'max' => 5],
    'processes_per_macro_process' => ['min' => 0, 'max' => 10],
    'activities_per_process' => ['min' => 0, 'max' => 10],
    'operations_per_activity' => ['min' => 0, 'max' => 3],
    'tasks_per_operation' => ['min' => 0, 'max' => 3],

    // Nombre d'acteurs générés pour chaque périmètre (tirage direct), et
    // nombre d'opérations de ce même périmètre auxquelles chaque acteur est
    // affecté (actor_operation).
    'actors_per_perimeter' => ['min' => 1, 'max' => 10],
    'actor_operations_per_actor' => ['min' => 1, 'max' => 5],

    // Nombre d'informations générées pour chaque périmètre (tirage direct) —
    // aucune relation n'est créée avec les processus.
    'informations_per_perimeter' => ['min' => 1, 'max' => 20],

    // Nombre d'informations rattachées à chaque base de données, et à chaque
    // flux applicatif (toutes deux prises dans le même périmètre).
    'informations_per_database' => ['min' => 1, 'max' => 5],
    'informations_per_flow' => ['min' => 0, 'max' => 2],

    // Nombre d'entités générées pour chaque périmètre (compte fixe, "une
    // centaine" plutôt qu'une plage), et nombre de relations générées pour
    // chaque entité vers une autre entité du même périmètre (jamais
    // elle-même) — contrairement aux flux applicatifs, une relation ne peut
    // pas relier deux périmètres différents.
    'entities_per_perimeter' => 100,
    'relations_per_entity' => ['min' => 0, 'max' => 3],

    // Nombre de traitements de données (registre RGPD) générés pour chaque
    // périmètre — tirage direct —, et nombre d'applications/processus/
    // informations de ce même périmètre auxquels chaque traitement est relié
    // (la même plage s'applique aux trois types de lien).
    'data_processing_per_perimeter' => ['min' => 20, 'max' => 50],
    'data_processing_links_per_type' => ['min' => 1, 'max' => 3],

    // Probabilité qu'un bâtiment/étage/local donné reçoive sa propre borne
    // WiFi (tirage indépendant, pas un compteur global à atteindre).
    'wifi_terminal_probability' => 0.80,

    // Probabilité qu'un flux applicatif relie deux extrémités (application,
    // service, module ou base de données) de périmètres différents (exerce
    // la visibilité "croisée" d'ApplicationFlowPerimeterScope). Le reste des
    // flux relie deux extrémités du même périmètre.
    'cross_perimeter_flow_probability' => 0.10,

    // Taille des lots utilisés pour les insertions en masse.
    'chunk_size' => 500,
];
