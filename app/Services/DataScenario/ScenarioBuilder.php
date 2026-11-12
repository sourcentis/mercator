<?php

namespace App\Services\DataScenario;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\FakerPatterns;
use App\Support\PerimeterSettings;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Génère un jeu de données de test réaliste et cohérent avec périmètres :
 * périmètres, applications, bases de données, serveurs logiques, flux
 * applicatifs, et les liens entre eux. Utilisé par la commande
 * `mercator:generate-test-data`.
 *
 * Chaque objet de cartographie généré reçoit aussi 3 à 5 étiquettes libres
 * dans son champ `attributes` (voir ATTRIBUTE_TAGS / randomAttributes()) —
 * sauf `data_processing`, dont la table n'a pas cette colonne.
 *
 * Chaque objet généré (hors flux applicatifs) reçoit un `perimeter_id`
 * explicite parmi les périmètres disponibles : contrairement à ce que
 * suggère un modèle générique de cartographie, `perimeter_id` n'est ici ni
 * nullable ni multi-valué (voir la migration add_perimeter_id_to_mapped_objects) —
 * chaque objet appartient à exactement un périmètre. Les flux applicatifs
 * peuvent en revanche relier deux applications de périmètres différents
 * (voir ApplicationFlowPerimeterScope), ce que cette classe exerce
 * volontairement via `cross_perimeter_flow_probability`.
 *
 * Dès que plus d'un périmètre est en jeu, la fonctionnalité périmètres est
 * activée (voir PerimeterSettings) et trois rôles dédiés sont créés pour
 * chaque périmètre, afin de pouvoir immédiatement s'y connecter et le
 * distinguer des autres : `admin.perimeter.<slug>` (toutes les permissions),
 * `user.perimeter.<slug>` (toutes sauf l'administration) et
 * `auditor.perimeter.<slug>` (consultation seule, sans l'administration) ;
 * le compte admin@admin.com (s'il existe) est rattaché aux rôles admin. L'infrastructure physique (sites,
 * bâtiments, baies, serveurs physiques, périphériques) est chaînée de façon
 * cohérente : un bâtiment référence un site de son propre périmètre, une
 * baie référence le même site que son bâtiment, un serveur physique/
 * périphérique référence la même baie/bâtiment/site — jamais un mélange
 * d'objets de périmètres différents. Une zone de sécurité est rattachée à
 * des bâtiments de son propre périmètre.
 *
 * Chaque site reçoit aussi des étages (ET1, ET2, ...) et, dans chaque étage,
 * des locaux (LOCAL042, ...) — une seconde hiérarchie d'entrées `buildings`,
 * en parallèle des bâtiments "classiques", pour le câblage bureautique
 * plutôt que technique. Les postes de travail et téléphones se trouvent
 * toujours dans un local (jamais directement dans un étage ou un bâtiment) ;
 * une borne WiFi se trouve dans un étage OU un local. Chaque étage reçoit
 * son propre switch physique (distinct du switch de baie), relié au routeur
 * du site. Les postes de travail et les bornes WiFi sont eux-mêmes reliés
 * par un lien physique au switch le plus proche : celui de leur propre
 * étage, ou celui de l'étage qui contient leur local.
 *
 * Chaque site reçoit aussi un réseau logique portant son propre nom (voir
 * Network — un objet purement logique, sans colonne site_id, d'où
 * l'association par le nom plutôt que par une clé étrangère), et ce réseau
 * reçoit entre 4 et 16 sous-réseaux (`subnetworks_per_network`), chacun
 * associé à son propre VLAN (1:1, `subnetworks.vlan_id`).
 *
 * Chaque serveur logique est apparié à un serveur physique de son propre
 * périmètre (`logical_server_physical_server`), et reçoit une adresse IP
 * piochée dans un sous-réseau du même site que ce serveur physique plutôt
 * qu'une IP générique — sauf s'il n'existe aucun serveur physique ou aucun
 * sous-réseau disponible, auquel cas une IP générique reste utilisée.
 *
 * Plusieurs objets ne suivent pas le schéma "compteur réparti sur les
 * périmètres" des autres : le nombre de téléphones est toujours aligné sur
 * celui des postes de travail effectivement créés ; chaque étage/local a
 * indépendamment une probabilité donnée (`wifi_terminal_probability`) de
 * recevoir sa propre borne WiFi ; et le nombre d'étages par site / de locaux
 * par étage est un tirage direct (`floors_per_site` / `locals_per_floor`),
 * pas un compteur global à atteindre.
 *
 * Chaque périmètre reçoit aussi entre 5 et 7 groupes applicatifs
 * (`application_blocks_per_perimeter`, tirage direct), et chaque application
 * est rattachée à un groupe de son propre périmètre (`application_block_id`).
 * Chaque application est par ailleurs reliée à 1, 2 ou 3 serveurs logiques de
 * son propre périmètre — jamais 0 dès qu'un serveur logique existe dans ce
 * périmètre —, 90% des applications n'en ayant qu'un seul (voir
 * APPLICATION_SERVER_COUNTS), au lieu d'une simple plage min/max.
 *
 * Chaque site reçoit aussi son infrastructure d'administration/annuaire :
 * 1 à 3 zones d'administration (`zone_admins_per_site`), un service
 * d'annuaire (avec une application de son propre périmètre), une forêt
 * Active Directory/LDAP, et 3 à 5 domaines (`domains_per_site`) rattachés à
 * cette forêt. Chaque site reçoit également autant d'utilisateurs
 * (`admin_users`) que de postes de travail effectivement créés sur ce site,
 * répartis aussi également que possible entre ses domaines (voir
 * insertDirectoryInfrastructure()) — aucune de ces quatre tables n'a de
 * colonne site_id, ce découpage ne structure donc que la génération.
 *
 * Chaque application reçoit aussi 0 à 5 services applicatifs
 * (`application_services_per_application`), chacun dédié à cette seule
 * application, et chaque service reçoit 0 à 3 modules applicatifs
 * (`application_modules_per_service`), chacun dédié à ce seul service — ni
 * les services ni les modules ne sont partagés entre plusieurs parents. Les
 * flux applicatifs peuvent désormais relier deux extrémités de n'importe
 * lequel de ces quatre types (application, service, module, base de
 * données), dans n'importe quelle combinaison, et pas seulement deux
 * applications (voir buildFlowEndpoints() / FLOW_ENDPOINT_COLUMNS).
 *
 * Chaque périmètre reçoit aussi sa cartographie métier, chaînée de proche en
 * proche et jamais partagée d'un parent à l'autre : 3 à 5 macro-processus
 * (`macro_processes_per_perimeter`), 3 à 10 processus par macro-processus
 * (`macroprocess_id`), 5 à 10 activités par processus (`activity_process`),
 * 1 à 3 opérations par activité (`activity_operation`, chaque opération
 * héritant du même `process_id` que son activité parente), et 1 à 3 tâches
 * par opération (`operation_task`). Chaque périmètre reçoit également 5 à 20
 * acteurs (`actors_per_perimeter`), chacun affecté à 1 à 5 opérations de ce
 * même périmètre (`actor_operation`), et 5 à 20 informations
 * (`informations_per_perimeter`). Chaque base de données reçoit 1 à 5 de ces
 * informations (`informations_per_database`, `database_information`), et
 * chaque flux applicatif en reçoit 0 à 2 (`informations_per_flow`,
 * `application_flow_information`) — dans les deux cas prises dans le même
 * périmètre que la base de données/le flux.
 *
 * Chaque périmètre reçoit enfin une centaine d'entités
 * (`entities_per_perimeter`, un compte fixe plutôt qu'une plage), chacune
 * reliée à 0 à 3 autres entités de ce même périmètre (`relations_per_entity`,
 * jamais à elle-même) — `Relation`, contrairement à `ApplicationFlow`,
 * n'autorise pas de source/destination dans un périmètre différent.
 *
 * Chaque périmètre reçoit également 20 à 50 traitements de données
 * (`data_processing_per_perimeter`, un registre RGPD), chacun relié à 1 à 3
 * applications, 1 à 3 processus et 1 à 3 informations
 * (`data_processing_links_per_type`), toutes prises dans son propre
 * périmètre. Une seule base légale (RGPD art. 6) est tirée par traitement,
 * reflétée à la fois dans `legal_basis` et dans le `lawfulness_*`
 * correspondant.
 */
class ScenarioBuilder
{
    /** @var list<string> */
    private const PERIMETER_NAMES = [
        'Paris', 'Lyon', 'Nice', 'Bordeaux', 'Marseille', 'Bruxelles', 'New-York', 'Roubaix',
        'Filiale Nord', 'Filiale Sud', 'Filiale est', 'International', 'Luxembourg', 'Toulouse',
        'Strasbourg', 'Montpellier', 'Datacenter',
    ];

    /** @var array<string,int> */
    private const APP_TYPES = ['web' => 40, 'batch' => 15, 'middleware' => 15, 'api' => 20, 'mobile-backend' => 10];

    /** @var array<string,int> */
    private const SERVER_TYPES = ['vm' => 70, 'bare-metal' => 20, 'container' => 10];

    /** @var array<string,int> */
    private const DATABASE_TYPES = ['mysql' => 40, 'mariadb' => 20, 'postgresql' => 30, 'mongodb' => 10];

    /** @var array<string,int> */
    private const FLOW_TYPES = [
        'http' => 10, 'https' => 10, 'vpn' => 10, 'ssh' => 5,  // 3.5
        'rdp' => 10, 'ftp' => 5,  'telnet' => 5, 'smtp' => 10, 'pop3' => 5, 'imap' => 5, // 4
        'ldap' => 5, 'api' => 10, 'xml' => 10]; // 2.5

    /** @var list<string> */
    private const ENVIRONMENTS = ['prod', 'integration', 'staging', 'dev', 'preprod'];

    /** @var list<string> */
    private const DOMAIN_WORDS = [
        'platform', 'core', 'portal', 'crm', 'erp', 'billing', 'auth', 'gateway',
        'reporting', 'payments', 'hr', 'logistics', 'scheduler', 'inventory', 'catalog',
    ];

    /** @var list<string> */
    private const COMPANY_WORDS = ['acme', 'globex', 'initech', 'umbrella', 'stark', 'wayne', 'soylent', 'wonka', 'hooli', 'vandelay'];

    /** @var list<string> */
    private const SERVER_PREFIXES = ['web', 'app', 'db', 'winsrv', 'linux', 'cache', 'lb', 'batch', 'file'];

    /** @var list<string> */
    private const SITE_TYPES = ['Siege social', 'Agence', 'Datacenter', 'Site distant', 'Entrepot'];

    /** @var list<string> */
    private const BUILDING_TYPES = ['Batiment principal', 'Annexe', 'Datacenter', 'Local technique'];

    /** @var list<string> */
    private const BAY_TYPES = ['Baie serveurs', 'Baie reseau', 'Baie stockage', 'Baie telecom'];

    /** @var list<string> */
    private const PHYSICAL_SERVER_TYPES = ['Rack', 'Lame', 'Tour'];

    /** @var list<string> */
    private const OPERATING_SYSTEMS = ['Ubuntu 22.04', 'Debian 12', 'RHEL 9', 'Windows Server 2022'];

    /** @var list<string> */
    private const CPU_MODELS = ['Intel Xeon E5-2670', 'Intel Xeon Silver 4210', 'Intel Xeon Gold 6248', 'AMD EPYC 7302'];

    /** @var list<string> */
    private const MEMORY_SIZES = ['16 Go', '32 Go', '64 Go', '128 Go', '256 Go'];

    /** @var list<string> */
    private const DISK_SIZES = ['500 Go', '1 To', '2 To', '4 To'];

    /** @var list<string> */
    private const PERIPHERAL_TYPES = ['Imprimante', 'Scanner', 'Photocopieur', 'Onduleur', 'Badgeuse', 'Camera'];

    /** @var list<string> */
    private const WORKSTATION_TYPES = ['Desktop', 'Laptop', 'Tablette'];

    /** @var list<string> */
    private const WORKSTATION_CPU_MODELS = ['Intel Core i5-1240P', 'Intel Core i7-1265U', 'AMD Ryzen 5 5600U', 'AMD Ryzen 7 5800U', 'Apple M2'];

    /** @var list<string> */
    private const WORKSTATION_OPERATING_SYSTEMS = ['Windows 11', 'Windows 10', 'Ubuntu 22.04', 'macOS Sonoma'];

    /** @var list<string> */
    private const PHONE_TYPES = ['Fixe', 'IP', 'DECT'];

    /** @var list<string> */
    private const WIFI_TERMINAL_TYPES = ['Interieur', 'Exterieur'];

    /** @var list<string> */
    private const NETWORK_TYPES = ['LAN', 'WAN', 'MAN'];

    /** @var list<string> */
    private const NETWORK_PROTOCOL_TYPES = ['Ethernet', 'TCP/IP', 'Fibre Channel', 'Wi-Fi', 'MPLS'];

    /** @var list<string> */
    private const SUBNETWORK_TYPES = ['LAN', 'VLAN', 'DMZ'];

    /** @var list<string> */
    private const APPLICATION_BLOCK_TYPES = ['Metier', 'Support', 'Technique'];

    /**
     * Nombre de serveurs logiques attachés à chaque application : 90% des
     * applications n'en ont qu'un, le reste se partage entre deux et trois.
     *
     * @var array<int,int>
     */
    private const APPLICATION_SERVER_COUNTS = [1 => 90, 2 => 5, 3 => 5];

    /** @var list<string> */
    private const ZONE_ADMIN_TYPES = ['Corporate', 'Filiale', 'DMZ', 'Test'];

    /** @var list<string> */
    private const ANNUAIRE_TYPES = ['Active Directory', 'LDAP', 'RH', 'Technique'];

    /** @var list<string> */
    private const ANNUAIRE_SOLUTIONS = ['Active Directory', 'OpenLDAP', 'FreeIPA', 'Azure AD', '389 Directory Server'];

    /** @var list<string> */
    private const FOREST_AD_TYPES = ['Interne', 'Externe', 'Partenaire'];

    /** @var list<string> */
    private const DOMAIN_TYPES = ['Production', 'Test', 'Recette'];

    /** @var list<string> */
    private const DOMAIN_INTER_DOMAIN_RELATIONS = ['Aucune', 'Approbation unidirectionnelle', 'Approbation bidirectionnelle'];

    /**
     * Exemples suggérés par le champ `admin_users.type` de l'interface
     * (voir resources/lang/fr/cruds.php, adminUser.fields.type_helper).
     *
     * @var list<string>
     */
    private const ADMIN_USER_TYPES = ['support', 'direction', 'compta', 'external', 'domain-admin'];

    /** @var list<string> */
    private const APPLICATION_SERVICE_TYPES = ['REST API', 'SOAP', 'Batch', 'Messagerie', 'Fichier'];

    /** @var list<string> */
    private const APPLICATION_SERVICE_EXPOSITIONS = ['Interne', 'Cloud', 'Partenaire', 'Public'];

    /** @var list<string> */
    private const APPLICATION_MODULE_TYPES = ['Frontend', 'Backend', 'Middleware', 'Reporting', 'Integration'];

    /**
     * Colonnes source/destination d'un flux applicatif pour chaque type
     * d'objet pouvant servir d'extrémité (voir insertFlows()) — un flux ne
     * renseigne jamais qu'une seule paire, les trois autres restant NULL.
     *
     * @var array<string,array{source:string,dest:string}>
     */
    private const FLOW_ENDPOINT_COLUMNS = [
        'application' => ['source' => 'application_source_id', 'dest' => 'application_dest_id'],
        'service' => ['source' => 'service_source_id', 'dest' => 'service_dest_id'],
        'module' => ['source' => 'module_source_id', 'dest' => 'module_dest_id'],
        'database' => ['source' => 'database_source_id', 'dest' => 'database_dest_id'],
    ];

    /** @var list<string> */
    private const MACRO_PROCESS_TYPES = ['Metier', 'Support', 'Pilotage'];

    /** @var list<string> */
    private const PROCESS_TYPES = ['Metier', 'Support', 'Management'];

    /** @var list<string> */
    private const ACTIVITY_TYPES = ['Manuelle', 'Automatisee', 'Mixte'];

    /** @var list<string> */
    private const OPERATION_TYPES = ['Manuelle', 'Automatisee', 'Controle'];

    /** @var list<string> */
    private const TASK_TYPES = ['Manuelle', 'Automatisee'];

    /** @var list<string> */
    private const ACTOR_NATURES = ['Personne', 'Groupe', 'Entite'];

    /**
     * Voir resources/lang/fr/cruds.php, actor.fields.type_helper ("interne
     * ou externe à l'organisme").
     *
     * @var list<string>
     */
    private const ACTOR_TYPES = ['interne', 'externe'];

    /** @var list<string> */
    private const INFORMATION_TYPES = ['Donnee personnelle', 'Donnee medicale', 'Donnee financiere', 'Donnee technique'];

    /** @var list<string> */
    private const INFORMATION_SENSITIVITIES = ['Publique', 'Interne', 'Confidentielle', 'Donnee a caractere personnel', 'Donnee medicale'];

    /**
     * Voir resources/lang/fr/cruds.php, entity.fields.type_helper
     * ("fournisseur, prestataire, auditeur...").
     *
     * @var list<string>
     */
    private const ENTITY_TYPES = ['Fournisseur', 'Prestataire', 'Auditeur', 'Partenaire', 'Filiale', 'Client'];

    /** @var list<string> */
    private const ENTITY_SECURITY_LEVELS = ['Faible', 'Moyen', 'Eleve', 'Critique'];

    /**
     * Voir resources/lang/fr/cruds.php, relation.fields.type_helper
     * ("fourniture de biens, de services, partenariat commercial...").
     *
     * @var list<string>
     */
    private const RELATION_TYPES = ['Fourniture de biens', 'Fourniture de services', 'Partenariat commercial', 'Sous-traitance', 'Cooperation'];

    /**
     * Bases légales du RGPD (article 6) — une seule est tirée par traitement
     * et reflétée à la fois dans `legal_basis` et dans le `lawfulness_*`
     * correspondant (voir insertDataProcessing()).
     *
     * @var array<string,string> legal_basis => colonne lawfulness_*
     */
    private const DATA_PROCESSING_LEGAL_BASES = [
        'Consentement' => 'lawfulness_consent',
        'Contrat' => 'lawfulness_contract',
        'Obligation legale' => 'lawfulness_legal_obligation',
        'Interet vital' => 'lawfulness_vital_interest',
        'Mission d\'interet public' => 'lawfulness_public_interest',
        'Interet legitime' => 'lawfulness_legitimate_interest',
    ];

    /**
     * Étiquettes libres piochées pour peupler le champ `attributes` de
     * chaque objet de cartographie généré (voir randomAttributes()).
     *
     * @var list<string>
     */
    private const ATTRIBUTE_TAGS = [
        'OK', 'Checked', 'Dev', 'Test', 'T1', 'T2', 'T3', 'O1',
        'Saved', 'A1', 'A2', 'A3', 'A4', 'A5', 'Sec', 'Lab',
    ];

    /**
     * Le login canonique du compte administrateur (voir
     * database/seeders/RoleUserTableSeeder.php), utilisé pour rattacher
     * automatiquement les rôles admin.perimeter.* créés par cette classe.
     */
    private const ADMIN_LOGIN = 'admin@admin.com';

    /**
     * Permissions d'administration, exclues des rôles user.perimeter.* et
     * auditor.perimeter.* : gestion des utilisateurs, rôles et permissions,
     * configuration, modules et cartographes.
     */
    private const ADMIN_PERMISSION_PATTERN = '/^(user_management_access|configuration_access|module_manage|(permission|role|user|cartographer)_(access|create|edit|show|delete))$/';

    /**
     * @param  array{databases_per_application: array{min:int,max:int}, buildings_per_zone: array{min:int,max:int}, floors_per_site: array{min:int,max:int}, locals_per_floor: array{min:int,max:int}, subnetworks_per_network: array{min:int,max:int}, application_blocks_per_perimeter: array{min:int,max:int}, zone_admins_per_site: array{min:int,max:int}, domains_per_site: array{min:int,max:int}, application_services_per_application: array{min:int,max:int}, application_modules_per_service: array{min:int,max:int}, macro_processes_per_perimeter: array{min:int,max:int}, processes_per_macro_process: array{min:int,max:int}, activities_per_process: array{min:int,max:int}, operations_per_activity: array{min:int,max:int}, tasks_per_operation: array{min:int,max:int}, actors_per_perimeter: array{min:int,max:int}, actor_operations_per_actor: array{min:int,max:int}, informations_per_perimeter: array{min:int,max:int}, informations_per_database: array{min:int,max:int}, informations_per_flow: array{min:int,max:int}, entities_per_perimeter: int, relations_per_entity: array{min:int,max:int}, data_processing_per_perimeter: array{min:int,max:int}, data_processing_links_per_type: array{min:int,max:int}, cross_perimeter_flow_probability: float, wifi_terminal_probability: float}  $tuning
     */
    public function __construct(
        private readonly array $tuning,
        private readonly int $chunkSize = 500,
        private readonly ?Closure $onPhaseStart = null,
        private readonly ?Closure $onTick = null,
        private readonly ?Closure $onPhaseEnd = null,
    ) {}

    /**
     * @param  array<string,int>  $counts  perimeters, applications, databases, servers, flows, sites, buildings, bays, physical_servers, security_zones
     * @return array<string, int|bool|null>
     */
    public function build(array $counts, bool $dryRun = false, ?int $seed = null): array
    {
        $this->validate($counts);

        if ($seed !== null) {
            mt_srand($seed);
            fake()->seed($seed);
        }

        if ($dryRun) {
            return $this->plan($counts);
        }

        return DB::transaction(fn () => $this->generate($counts));
    }

    /**
     * @param  array<string,int>  $counts
     */
    private function validate(array $counts): void
    {
        if ($counts['perimeters'] < 1) {
            throw new InvalidArgumentException('Le nombre de périmètres doit être au moins 1.');
        }

        foreach (['applications', 'databases', 'servers', 'flows', 'sites', 'buildings', 'bays', 'physical_servers', 'peripherals', 'workstations', 'security_zones'] as $key) {
            if (($counts[$key] ?? 0) < 0) {
                throw new InvalidArgumentException("Le compteur \"{$key}\" ne peut pas être négatif.");
            }
        }

        if ($counts['flows'] > 0 && $counts['applications'] < 2) {
            throw new InvalidArgumentException('Au moins 2 applications sont nécessaires pour générer des flux applicatifs.');
        }
    }

    /**
     * @param  array<string,int>  $counts
     * @return array<string, int|bool|null>
     */
    private function plan(array $counts): array
    {
        $existing = DB::table('perimeters')->count();
        $perimetersTotal = max($counts['perimeters'], $existing);
        $multiPerimeter = $perimetersTotal > 1;

        $sites = $counts['sites'] ?? 0;
        $floorsEstimate = (int) round($sites * $this->averageOf('floors_per_site'));
        $localsEstimate = (int) round($floorsEstimate * $this->averageOf('locals_per_floor'));
        $subnetworksEstimate = (int) round($sites * $this->averageOf('subnetworks_per_network'));
        $applicationBlocksEstimate = (int) round($perimetersTotal * $this->averageOf('application_blocks_per_perimeter'));
        $zoneAdminsEstimate = (int) round($sites * $this->averageOf('zone_admins_per_site'));
        $domainsEstimate = (int) round($sites * $this->averageOf('domains_per_site'));
        $applicationServicesEstimate = (int) round($counts['applications'] * $this->averageOf('application_services_per_application'));
        $applicationModulesEstimate = (int) round($applicationServicesEstimate * $this->averageOf('application_modules_per_service'));
        $macroProcessesEstimate = (int) round($perimetersTotal * $this->averageOf('macro_processes_per_perimeter'));
        $processesEstimate = (int) round($macroProcessesEstimate * $this->averageOf('processes_per_macro_process'));
        $activitiesEstimate = (int) round($processesEstimate * $this->averageOf('activities_per_process'));
        $operationsEstimate = (int) round($activitiesEstimate * $this->averageOf('operations_per_activity'));
        $tasksEstimate = (int) round($operationsEstimate * $this->averageOf('tasks_per_operation'));
        $actorsEstimate = (int) round($perimetersTotal * $this->averageOf('actors_per_perimeter'));
        $informationsEstimate = (int) round($perimetersTotal * $this->averageOf('informations_per_perimeter'));
        $entitiesEstimate = $perimetersTotal * $this->tuning['entities_per_perimeter'];
        $relationsEstimate = (int) round($entitiesEstimate * $this->averageOf('relations_per_entity'));
        $dataProcessingEstimate = (int) round($perimetersTotal * $this->averageOf('data_processing_per_perimeter'));

        return [
            'dry_run' => true,
            'perimeters_total' => $perimetersTotal,
            'perimeters_created' => max(0, $counts['perimeters'] - $existing),
            'perimeters_feature_enabled' => $multiPerimeter,
            'admin_roles_created' => $multiPerimeter ? $perimetersTotal : 0,
            'user_roles_created' => $multiPerimeter ? $perimetersTotal : 0,
            'auditor_roles_created' => $multiPerimeter ? $perimetersTotal : 0,
            'admin_user_linked' => $multiPerimeter && User::query()->where('login', self::ADMIN_LOGIN)->exists(),
            'applications' => $counts['applications'],
            'application_blocks' => $applicationBlocksEstimate,
            'application_services' => $applicationServicesEstimate,
            'application_service_links' => null,
            'application_modules' => $applicationModulesEstimate,
            'service_module_links' => null,
            'databases' => $counts['databases'],
            'servers' => $counts['servers'],
            'server_links' => null,
            'logical_physical_server_links' => null,
            'database_links' => null,
            'flows' => $counts['flows'],
            'sites' => $sites,
            'networks' => $sites,
            'subnetworks' => $subnetworksEstimate,
            'vlans' => $subnetworksEstimate,
            'buildings' => $counts['buildings'] ?? 0,
            'bays' => $counts['bays'] ?? 0,
            'floors' => $floorsEstimate,
            'locals' => $localsEstimate,
            'physical_switches' => $counts['bays'] ?? 0,
            'floor_switches' => $floorsEstimate,
            'physical_routers' => $counts['sites'] ?? 0,
            'physical_servers' => $counts['physical_servers'] ?? 0,
            'server_switch_links' => null,
            'switch_router_links' => null,
            'floor_switch_router_links' => null,
            'peripherals' => $counts['peripherals'] ?? 0,
            'workstations' => $counts['workstations'] ?? 0,
            'workstation_switch_links' => null,
            'phones' => $counts['workstations'] ?? 0,
            'wifi_terminals' => (int) round(($floorsEstimate + $localsEstimate) * $this->tuning['wifi_terminal_probability']),
            'wifi_switch_links' => null,
            'security_zones' => $counts['security_zones'] ?? 0,
            'zone_building_links' => null,
            'zone_admins' => $zoneAdminsEstimate,
            'annuaires' => $sites,
            'forest_ads' => $sites,
            'domains' => $domainsEstimate,
            'domain_forest_ad_links' => null,
            'admin_users' => $counts['workstations'] ?? 0,
            'macro_processes' => $macroProcessesEstimate,
            'processes' => $processesEstimate,
            'process_activity_links' => null,
            'activities' => $activitiesEstimate,
            'activity_operation_links' => null,
            'operations' => $operationsEstimate,
            'operation_task_links' => null,
            'tasks' => $tasksEstimate,
            'actors' => $actorsEstimate,
            'actor_operation_links' => null,
            'informations' => $informationsEstimate,
            'database_information_links' => null,
            'flow_information_links' => null,
            'entities' => $entitiesEstimate,
            'relations' => $relationsEstimate,
            'data_processing' => $dataProcessingEstimate,
            'data_processing_application_links' => null,
            'data_processing_process_links' => null,
            'data_processing_information_links' => null,
        ];
    }

    /**
     * Moyenne d'une plage min/max de $this->tuning, pour les estimations de
     * dry-run des tirages directs (étages par site, locaux par étage).
     */
    private function averageOf(string $tuningKey): float
    {
        return ($this->tuning[$tuningKey]['min'] + $this->tuning[$tuningKey]['max']) / 2;
    }

    /**
     * @param  array<string,int>  $counts
     * @return array<string, int|bool|null>
     */
    private function generate(array $counts): array
    {
        $existingPerimeterCount = DB::table('perimeters')->count();

        $perimeterIds = $this->ensurePerimeters($counts['perimeters']);
        $access = $this->enableMultiPerimeterAccess($perimeterIds);

        // 5 à 7 groupes applicatifs par périmètre (tirage direct), chaque
        // application étant rattachée à un groupe de son propre périmètre.
        [$applicationBlockIds, $applicationBlockPerimeterById] = $this->insertApplicationBlocks($perimeterIds);
        $applicationBlocksByPerimeter = $this->groupBy($applicationBlockIds, $applicationBlockPerimeterById);

        [$appIds, $appPerimeterById] = $this->insertApplications($counts['applications'], $perimeterIds, $applicationBlocksByPerimeter);

        // 0 à 5 services applicatifs par application, 0 à 3 modules par
        // service — chacun dédié à son seul parent (jamais partagé).
        [$serviceIds, $servicePerimeterById, $applicationServiceLinks] = $this->insertApplicationServices($appIds, $appPerimeterById);
        [$moduleIds, $modulePerimeterById, $serviceModuleLinks] = $this->insertApplicationModules($serviceIds, $servicePerimeterById);

        [$dbIds, $dbPerimeterById] = $this->insertDatabases($counts['databases'], $perimeterIds);

        $databaseLinks = $this->attachPivot(
            'application_database', 'application_id', 'database_id',
            $appIds, $appPerimeterById, $this->groupBy($dbIds, $dbPerimeterById),
            fn () => random_int($this->tuning['databases_per_application']['min'], $this->tuning['databases_per_application']['max']),
            'Liens applications <-> bases de données',
        );

        // Un flux applicatif peut relier deux applications, services,
        // modules ou bases de données (dans n'importe quelle combinaison),
        // pas seulement deux applications.
        [$flowEndpointKeys, $flowEndpointPerimeterByKey] = $this->buildFlowEndpoints(
            $appIds, $appPerimeterById, $serviceIds, $servicePerimeterById, $moduleIds, $modulePerimeterById, $dbIds, $dbPerimeterById,
        );
        [$flowIds, $flowPerimeterById] = $this->insertFlows($counts['flows'], $flowEndpointKeys, $flowEndpointPerimeterByKey);

        [$siteIds, $sitePerimeterById, $siteNameById] = $this->insertSites($counts['sites'] ?? 0, $perimeterIds);
        $sitesByPerimeter = $this->groupBy($siteIds, $sitePerimeterById);

        // Un réseau par site (nommé comme le site), 4 à 16 sous-réseaux par
        // réseau : des conséquences directes du nombre de sites, pas des
        // compteurs configurables.
        [$networkIds, $networkPerimeterById, $networkSiteById] = $this->insertNetworks($siteIds, $sitePerimeterById, $siteNameById);
        [$subnetworkIds, , $vlanIds, $subnetworkSiteById, $subnetworkAddressById] = $this->insertSubnetworks($networkIds, $networkPerimeterById, $networkSiteById);
        $subnetworksBySite = $this->groupBy($subnetworkIds, $subnetworkSiteById);

        [$buildingIds, $buildingPerimeterById, $buildingSiteById] = $this->insertBuildings($counts['buildings'] ?? 0, $perimeterIds, $sitesByPerimeter);
        $buildingsByPerimeter = $this->groupBy($buildingIds, $buildingPerimeterById);

        [$bayIds, $bayPerimeterById, $bayBuildingById, $baySiteById] = $this->insertBays($counts['bays'] ?? 0, $perimeterIds, $buildingsByPerimeter, $buildingSiteById);
        $baysByPerimeter = $this->groupBy($bayIds, $bayPerimeterById);
        $baysBySite = $this->groupBy($bayIds, $baySiteById);

        // 1 à 8 étages par site, 5 à 10 locaux par étage : des tirages
        // directs par site/étage (voir insertFloors/insertLocals), pas des
        // compteurs configurables répartis sur les périmètres.
        [$floorIds, $floorPerimeterById, $floorSiteById] = $this->insertFloors($siteIds, $sitePerimeterById);
        [$localIds, $localPerimeterById, $localSiteById, $localFloorById] = $this->insertLocals($floorIds, $floorPerimeterById, $floorSiteById);
        $localsByPerimeter = $this->groupBy($localIds, $localPerimeterById);

        // Un switch par baie ET un switch par étage (deux réseaux distincts :
        // câblage technique vs câblage bureautique), un routeur par site
        // (dans l'une de ses baies) : pas des compteurs configurables, une
        // conséquence directe du nombre de baies/étages/sites déjà générés.
        [$physicalSwitchIds, $switchPerimeterById, $switchIdByBayId, $switchSiteById] = $this->insertPhysicalSwitches($bayIds, $bayPerimeterById, $bayBuildingById, $baySiteById);
        [$floorSwitchIds, $floorSwitchPerimeterById, $floorSwitchSiteById, $switchIdByFloorId] = $this->insertFloorSwitches($floorIds, $floorPerimeterById, $floorSiteById);
        [$physicalRouterIds, , $routerIdBySite] = $this->insertPhysicalRouters($siteIds, $sitePerimeterById, $baysBySite, $bayBuildingById);

        [$physicalServerIds, $physicalServerPerimeterById, $physicalServerBayById, $physicalServerSiteById] = $this->insertPhysicalServers($counts['physical_servers'] ?? 0, $perimeterIds, $baysByPerimeter, $bayBuildingById, $baySiteById);
        [$peripheralIds] = $this->insertPeripherals($counts['peripherals'] ?? 0, $perimeterIds, $baysByPerimeter, $bayBuildingById, $baySiteById);

        // Chaque serveur logique est assigné à un serveur physique de son
        // propre périmètre, et reçoit une IP prise dans un sous-réseau du
        // site de ce serveur physique (voir insertServers()).
        $physicalServersByPerimeter = $this->groupBy($physicalServerIds, $physicalServerPerimeterById);
        [$srvIds, $srvPerimeterById, $srvPhysicalServerById] = $this->insertServers(
            $counts['servers'], $perimeterIds, $physicalServersByPerimeter, $physicalServerSiteById, $subnetworksBySite, $subnetworkAddressById,
        );
        $logicalPhysicalServerLinks = $this->insertDirectPivot(
            'logical_server_physical_server', 'logical_server_id', 'physical_server_id', $srvPhysicalServerById,
            'Liens serveurs logiques <-> serveurs physiques',
        );

        // Chaque application reçoit 1, 2 ou 3 serveurs logiques (jamais 0) :
        // 90% n'en ont qu'un (voir APPLICATION_SERVER_COUNTS), pas une plage
        // min/max configurable.
        $serverLinks = $this->attachPivot(
            'application_logical_server', 'application_id', 'logical_server_id',
            $appIds, $appPerimeterById, $this->groupBy($srvIds, $srvPerimeterById),
            fn () => (int) $this->pickWeighted(self::APPLICATION_SERVER_COUNTS),
            'Liens applications <-> serveurs',
        );

        $serverSwitchLinks = $this->insertServerSwitchLinks($physicalServerIds, $physicalServerPerimeterById, $physicalServerBayById, $switchIdByBayId);
        $switchRouterLinks = $this->insertSwitchRouterLinks($physicalSwitchIds, $switchSiteById, $switchPerimeterById, $routerIdBySite);
        $floorSwitchRouterLinks = $this->insertSwitchRouterLinks($floorSwitchIds, $floorSwitchSiteById, $floorSwitchPerimeterById, $routerIdBySite);

        // Switch le plus proche de chaque local : celui de l'étage qui le
        // contient. Combiné à switchIdByFloorId (étage -> switch), cette
        // seule table sert à résoudre le switch le plus proche pour tout
        // équipement placé dans un étage OU un local (postes de travail,
        // bornes WiFi).
        $switchIdByLocalId = [];
        foreach ($localFloorById as $localId => $floorId) {
            if (isset($switchIdByFloorId[$floorId])) {
                $switchIdByLocalId[$localId] = $switchIdByFloorId[$floorId];
            }
        }
        $nearestSwitchByBuildingId = $switchIdByFloorId + $switchIdByLocalId;

        // Postes de travail et téléphones se trouvent dans un local, jamais
        // directement dans un bâtiment/étage.
        [$workstationIds, $workstationPerimeterById, $workstationBuildingById] = $this->insertWorkstations($counts['workstations'] ?? 0, $perimeterIds, $localsByPerimeter, $localSiteById);

        // Autant de téléphones que de postes de travail effectivement créés.
        [$phoneIds] = $this->insertPhones(count($workstationIds), $perimeterIds, $localsByPerimeter, $localSiteById);

        // Une borne WiFi peut se trouver dans un étage ou un local (jamais
        // un bâtiment "classique" ni une baie).
        [$wifiTerminalIds, $wifiTerminalPerimeterById, $wifiTerminalBuildingById] = $this->insertWifiTerminals(
            array_merge($floorIds, $localIds),
            $floorPerimeterById + $localPerimeterById,
            $floorSiteById + $localSiteById,
            $this->tuning['wifi_terminal_probability'],
        );

        // Postes de travail et bornes WiFi sont reliés au switch le plus
        // proche (celui de leur étage, ou de l'étage contenant leur local).
        $workstationSwitchLinks = $this->insertNearestSwitchLinks(
            $workstationIds, $workstationPerimeterById, $workstationBuildingById, $nearestSwitchByBuildingId,
            'workstation_src_id', 'Liens postes de travail <-> switchs',
        );
        $wifiSwitchLinks = $this->insertNearestSwitchLinks(
            $wifiTerminalIds, $wifiTerminalPerimeterById, $wifiTerminalBuildingById, $nearestSwitchByBuildingId,
            'wifi_terminal_src_id', 'Liens bornes WiFi <-> switchs',
        );

        // Site du poste de travail : celui du local qui le contient (aucune
        // colonne workstations.site_id n'est utilisée ici, on réutilise
        // simplement localSiteById comme pour le switch le plus proche).
        $workstationSiteById = [];
        foreach ($workstationBuildingById as $workstationId => $buildingId) {
            if ($buildingId !== null && isset($localSiteById[$buildingId])) {
                $workstationSiteById[$workstationId] = $localSiteById[$buildingId];
            }
        }
        $workstationsBySite = $this->groupBy(array_keys($workstationSiteById), $workstationSiteById);
        $applicationsByPerimeter = $this->groupBy($appIds, $appPerimeterById);

        // Par site : 1 à 3 zones d'administration, un annuaire (avec une
        // application de son propre périmètre), une forêt Active
        // Directory/LDAP, 3 à 5 domaines rattachés à cette forêt, et autant
        // d'utilisateurs (admin_users) que de postes de travail effectivement
        // créés sur ce site, chacun rattaché à l'un de ces domaines.
        [$zoneAdminIds, $annuaireIds, $forestAdIds, $domainIds, $adminUserIds, $domainForestAdLinks] = $this->insertDirectoryInfrastructure(
            $siteIds, $sitePerimeterById, $applicationsByPerimeter, $workstationsBySite,
        );

        [$zoneIds, $zonePerimeterById] = $this->insertSecurityZones($counts['security_zones'] ?? 0, $perimeterIds);

        $zoneLinks = $this->attachPivot(
            'building_zone', 'zone_id', 'building_id',
            $zoneIds, $zonePerimeterById, $buildingsByPerimeter,
            fn () => random_int($this->tuning['buildings_per_zone']['min'], $this->tuning['buildings_per_zone']['max']),
            'Liens zones <-> bâtiments',
        );

        // Cartographie métier : périmètre -> macro-processus -> processus ->
        // activités -> opérations -> tâches (chaque niveau dédié à son seul
        // parent, jamais partagé), acteurs par périmètre affectés à des
        // opérations de ce même périmètre, et informations par périmètre.
        [$macroProcessIds, $macroProcessPerimeterById] = $this->insertMacroProcesses($perimeterIds);
        [$processIds, $processPerimeterById] = $this->insertProcesses($macroProcessIds, $macroProcessPerimeterById);
        [$activityIds, $activityPerimeterById, $activityProcessById, $processActivityLinks] = $this->insertActivities($processIds, $processPerimeterById);
        [$operationIds, $operationPerimeterById, $activityOperationLinks] = $this->insertOperations($activityIds, $activityPerimeterById, $activityProcessById);
        [$taskIds, , $operationTaskLinks] = $this->insertTasks($operationIds, $operationPerimeterById);

        [$actorIds, $actorPerimeterById] = $this->insertActors($perimeterIds);
        $actorOperationLinks = $this->attachPivot(
            'actor_operation', 'actor_id', 'operation_id',
            $actorIds, $actorPerimeterById, $this->groupBy($operationIds, $operationPerimeterById),
            fn () => random_int($this->tuning['actor_operations_per_actor']['min'], $this->tuning['actor_operations_per_actor']['max']),
            'Liens acteurs <-> opérations',
        );

        [$informationIds, $informationPerimeterById] = $this->insertInformations($perimeterIds);

        // 1 à 5 informations par base de données, et 0 à 2 informations par
        // flux applicatif — toutes deux prises dans le même périmètre que
        // la base de données/le flux.
        $informationsByPerimeter = $this->groupBy($informationIds, $informationPerimeterById);
        $databaseInformationLinks = $this->attachPivot(
            'database_information', 'database_id', 'information_id',
            $dbIds, $dbPerimeterById, $informationsByPerimeter,
            fn () => random_int($this->tuning['informations_per_database']['min'], $this->tuning['informations_per_database']['max']),
            'Liens bases de données <-> informations',
        );
        $flowInformationLinks = $this->attachPivot(
            'application_flow_information', 'flux_id', 'information_id',
            $flowIds, $flowPerimeterById, $informationsByPerimeter,
            fn () => random_int($this->tuning['informations_per_flow']['min'], $this->tuning['informations_per_flow']['max']),
            'Liens flux applicatifs <-> informations',
        );

        // Une centaine d'entités par périmètre, et 0 à 3 relations par
        // entité vers une autre entité du même périmètre (jamais elle-même).
        [$entityIds, $entityPerimeterById] = $this->insertEntities($perimeterIds);
        $relationCount = $this->insertRelations($entityIds, $entityPerimeterById);

        // 20 à 50 traitements de données par périmètre, chacun relié à 1 à 3
        // applications, 1 à 3 processus et 1 à 3 informations, toutes prises
        // dans son propre périmètre.
        [$dataProcessingIds, $dataProcessingPerimeterById] = $this->insertDataProcessing($perimeterIds);
        $dataProcessingLinksPicker = fn () => random_int($this->tuning['data_processing_links_per_type']['min'], $this->tuning['data_processing_links_per_type']['max']);
        $dataProcessingApplicationLinks = $this->attachPivot(
            'application_data_processing', 'data_processing_id', 'application_id',
            $dataProcessingIds, $dataProcessingPerimeterById, $this->groupBy($appIds, $appPerimeterById),
            $dataProcessingLinksPicker,
            'Liens traitements de données <-> applications',
        );
        $dataProcessingProcessLinks = $this->attachPivot(
            'data_processing_process', 'data_processing_id', 'process_id',
            $dataProcessingIds, $dataProcessingPerimeterById, $this->groupBy($processIds, $processPerimeterById),
            $dataProcessingLinksPicker,
            'Liens traitements de données <-> processus',
        );
        $dataProcessingInformationLinks = $this->attachPivot(
            'data_processing_information', 'data_processing_id', 'information_id',
            $dataProcessingIds, $dataProcessingPerimeterById, $informationsByPerimeter,
            $dataProcessingLinksPicker,
            'Liens traitements de données <-> informations',
        );

        return [
            'perimeters_total' => count($perimeterIds),
            'perimeters_created' => count($perimeterIds) - $existingPerimeterCount,
            'perimeters_feature_enabled' => $access['enabled'],
            'admin_roles_created' => $access['roles_created'],
            'user_roles_created' => $access['user_roles_created'],
            'auditor_roles_created' => $access['auditor_roles_created'],
            'admin_user_linked' => $access['admin_user_linked'],
            'applications' => count($appIds),
            'application_blocks' => count($applicationBlockIds),
            'application_services' => count($serviceIds),
            'application_service_links' => $applicationServiceLinks,
            'application_modules' => count($moduleIds),
            'service_module_links' => $serviceModuleLinks,
            'databases' => count($dbIds),
            'servers' => count($srvIds),
            'server_links' => $serverLinks,
            'logical_physical_server_links' => $logicalPhysicalServerLinks,
            'database_links' => $databaseLinks,
            'flows' => count($flowIds),
            'sites' => count($siteIds),
            'networks' => count($networkIds),
            'subnetworks' => count($subnetworkIds),
            'vlans' => count($vlanIds),
            'buildings' => count($buildingIds),
            'bays' => count($bayIds),
            'floors' => count($floorIds),
            'locals' => count($localIds),
            'physical_switches' => count($physicalSwitchIds),
            'floor_switches' => count($floorSwitchIds),
            'physical_routers' => count($physicalRouterIds),
            'physical_servers' => count($physicalServerIds),
            'server_switch_links' => $serverSwitchLinks,
            'switch_router_links' => $switchRouterLinks,
            'floor_switch_router_links' => $floorSwitchRouterLinks,
            'peripherals' => count($peripheralIds),
            'workstations' => count($workstationIds),
            'workstation_switch_links' => $workstationSwitchLinks,
            'phones' => count($phoneIds),
            'wifi_terminals' => count($wifiTerminalIds),
            'wifi_switch_links' => $wifiSwitchLinks,
            'security_zones' => count($zoneIds),
            'zone_building_links' => $zoneLinks,
            'zone_admins' => count($zoneAdminIds),
            'annuaires' => count($annuaireIds),
            'forest_ads' => count($forestAdIds),
            'domains' => count($domainIds),
            'domain_forest_ad_links' => $domainForestAdLinks,
            'admin_users' => count($adminUserIds),
            'macro_processes' => count($macroProcessIds),
            'processes' => count($processIds),
            'process_activity_links' => $processActivityLinks,
            'activities' => count($activityIds),
            'activity_operation_links' => $activityOperationLinks,
            'operations' => count($operationIds),
            'operation_task_links' => $operationTaskLinks,
            'tasks' => count($taskIds),
            'actors' => count($actorIds),
            'actor_operation_links' => $actorOperationLinks,
            'informations' => count($informationIds),
            'database_information_links' => $databaseInformationLinks,
            'flow_information_links' => $flowInformationLinks,
            'entities' => count($entityIds),
            'relations' => $relationCount,
            'data_processing' => count($dataProcessingIds),
            'data_processing_application_links' => $dataProcessingApplicationLinks,
            'data_processing_process_links' => $dataProcessingProcessLinks,
            'data_processing_information_links' => $dataProcessingInformationLinks,
        ];
    }

    /**
     * Active la fonctionnalité périmètres et crée, pour chaque périmètre, trois
     * rôles dédiés — uniquement lorsque plus d'un périmètre est en jeu (sinon
     * la notion même de "changer de périmètre" n'a pas de sens) :
     *
     *  - `admin.perimeter.<slug>` : toutes les permissions ;
     *  - `user.perimeter.<slug>` : toutes les permissions sauf l'administration
     *    (voir ADMIN_PERMISSION_PATTERN) ;
     *  - `auditor.perimeter.<slug>` : tout voir (`*_access` et `*_show`), rien
     *    modifier, sans l'administration.
     *
     * Chacune de ces permissions n'est valable que dans le périmètre du rôle
     * (voir PerimeterPermissions). Idempotent : réexécuter la commande sur les
     * mêmes périmètres retrouve les rôles déjà créés (et rafraîchit leurs
     * permissions) au lieu d'en dupliquer. Si le compte administrateur
     * canonique (voir ADMIN_LOGIN) existe, il est rattaché aux rôles admin (sans
     * jamais lui retirer ses rôles existants) afin de pouvoir immédiatement se
     * connecter à n'importe quel périmètre généré.
     *
     * @param  list<int>  $perimeterIds
     * @return array{enabled: bool, roles_created: int, user_roles_created: int, auditor_roles_created: int, admin_user_linked: bool}
     */
    private function enableMultiPerimeterAccess(array $perimeterIds): array
    {
        if (count($perimeterIds) <= 1) {
            return ['enabled' => false, 'roles_created' => 0, 'user_roles_created' => 0, 'auditor_roles_created' => 0, 'admin_user_linked' => false];
        }

        PerimeterSettings::setEnabled(true);

        $permissions = Permission::query()->get(['id', 'title']);
        $nonAdmin = $permissions->reject(fn (Permission $p) => preg_match(self::ADMIN_PERMISSION_PATTERN, $p->title) === 1);

        $permissionIdsByPrefix = [
            'admin' => $permissions->pluck('id'),
            'user' => $nonAdmin->pluck('id'),
            'auditor' => $nonAdmin->filter(fn (Permission $p) => preg_match('/_(access|show)$/', $p->title) === 1)->pluck('id'),
        ];

        $roleIds = ['admin' => [], 'user' => [], 'auditor' => []];

        $this->phaseStart('Rôles par périmètre (admin, user, auditor)', count($perimeterIds));

        foreach (DB::table('perimeters')->whereIn('id', $perimeterIds)->get(['id', 'name']) as $perimeter) {
            foreach ($permissionIdsByPrefix as $prefix => $ids) {
                $role = Role::query()->firstOrCreate([
                    'title' => $prefix.'.perimeter.'.Str::slug($perimeter->name),
                    'perimeter_id' => $perimeter->id,
                ]);

                $role->permissions()->sync($ids);
                $roleIds[$prefix][] = $role->id;
            }

            $this->tick();
        }

        $this->phaseEnd();

        $adminUser = User::query()->where('login', self::ADMIN_LOGIN)->first();
        $adminUser?->roles()->syncWithoutDetaching($roleIds['admin']);

        return [
            'enabled' => true,
            'roles_created' => count($roleIds['admin']),
            'user_roles_created' => count($roleIds['user']),
            'auditor_roles_created' => count($roleIds['auditor']),
            'admin_user_linked' => $adminUser !== null,
        ];
    }

    /**
     * @return list<int>
     */
    private function ensurePerimeters(int $total): array
    {
        $existingIds = DB::table('perimeters')->orderBy('id')->pluck('id')->all();
        $existingNames = DB::table('perimeters')->pluck('name')->all();

        $missing = max(0, $total - count($existingIds));

        if ($missing === 0) {
            return $existingIds;
        }

        // array_unique en plus d'array_diff : `perimeters.name` est unique en
        // base, un doublon accidentel dans PERIMETER_NAMES ne doit jamais
        // pouvoir faire échouer l'insertion en lot.
        $pool = array_values(array_unique(array_diff(self::PERIMETER_NAMES, $existingNames)));
        shuffle($pool);

        $now = now();
        $rows = [];
        $usedNames = $existingNames;

        for ($i = 0; $i < $missing; $i++) {
            $name = array_shift($pool) ?? $this->uniquePerimeterName($usedNames);
            $usedNames[] = $name;
            $rows[] = ['name' => $name, 'created_at' => $now, 'updated_at' => $now];
        }

        $this->phaseStart('Périmètres', count($rows));
        $newIds = $this->insertRows('perimeters', $rows);
        $this->phaseEnd();

        return array_merge($existingIds, $newIds);
    }

    /**
     * @param  list<string>  $used
     */
    private function uniquePerimeterName(array $used): string
    {
        do {
            $name = 'Perimetre-'.strtoupper(bin2hex(random_bytes(3)));
        } while (in_array($name, $used, true));

        return $name;
    }

    /**
     * Entre 5 et 7 groupes applicatifs par périmètre (`application_blocks`)
     * — tirage direct, pas un compteur global réparti sur les périmètres.
     *
     * @param  list<int>  $perimeterIds
     * @return array{0: list<int>, 1: array<int,int>} ids, perimeter par id
     */
    private function insertApplicationBlocks(array $perimeterIds): array
    {
        if ($perimeterIds === []) {
            return [[], []];
        }

        [$min, $max] = [$this->tuning['application_blocks_per_perimeter']['min'], $this->tuning['application_blocks_per_perimeter']['max']];
        $now = now();
        $rows = [];
        $assignments = [];

        foreach ($perimeterIds as $perimeterId) {
            $blockCount = random_int($min, $max);

            for ($i = 0; $i < $blockCount; $i++) {
                $assignments[] = $perimeterId;

                $rows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                    'type' => fake()->randomElement(self::APPLICATION_BLOCK_TYPES),
                    'description' => fake()->sentence(),
                    'responsible' => fake()->name(),
                    'attributes' => $this->randomAttributes(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Groupes applicatifs', count($rows));
        $ids = $this->insertRows('application_blocks', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments)];
    }

    /**
     * Chaque application est rattachée à un groupe applicatif de son propre
     * périmètre (voir insertApplicationBlocks()), quand ce périmètre en a
     * au moins un.
     *
     * @param  list<int>  $perimeterIds
     * @param  array<int,list<int>>  $applicationBlocksByPerimeter
     * @return array{0: list<int>, 1: array<int,int>}
     */
    private function insertApplications(int $count, array $perimeterIds, array $applicationBlocksByPerimeter): array
    {
        if ($count === 0) {
            return [[], []];
        }

        $assignments = $this->distributePerimeters($count, $perimeterIds);
        $now = now();
        $rows = [];

        foreach ($assignments as $perimeterId) {
            $env = $this->randomEnvironment();
            [$min, $max] = $this->securityNeedRange();

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => $this->applicationName($env),
                'description' => fake()->sentence(),
                'vendor' => fake()->company(),
                'product' => fake()->word(),
                'type' => $this->pickWeighted(self::APP_TYPES),
                'security_need_c' => random_int($min, $max),
                'security_need_i' => random_int($min, $max),
                'security_need_a' => random_int($min, $max),
                'security_need_t' => random_int($min, $max),
                'security_need_auth' => random_int(0, 4),
                'responsible' => fake()->name(),
                'functional_referent' => fake()->name(),
                'editor' => fake()->company(),
                'technology' => fake()->randomElement(['PHP', 'Java', 'Python', '.NET', 'Node.js']),
                'documentation' => null,
                'users' => (string) random_int(5, 5000),
                'version' => fake()->numerify('#.#.#'),
                'rto' => fake()->randomElement([0, 4, 24, 48, 72]),
                'rpo' => fake()->randomElement([0, 1, 4, 24]),
                'external' => fake()->boolean(20) ? 'oui' : 'non',
                'entity_resp_id' => null,
                'application_block_id' => $this->pickParent($applicationBlocksByPerimeter, $perimeterId),
                'attributes' => $this->randomAttributes(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Applications', count($rows));
        $ids = $this->insertRows('applications', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments)];
    }

    /**
     * Entre 0 et 5 services applicatifs par application (`application_services_per_application`),
     * chacun dédié à cette seule application (pas partagé), reliés via la
     * table pivot brute `application_application_service`.
     *
     * @param  list<int>  $appIds
     * @param  array<int,int>  $appPerimeterById
     * @return array{0: list<int>, 1: array<int,int>, 2: int} ids, perimeter par id, liens application<->service créés
     */
    private function insertApplicationServices(array $appIds, array $appPerimeterById): array
    {
        if ($appIds === []) {
            return [[], [], 0];
        }

        [$min, $max] = [$this->tuning['application_services_per_application']['min'], $this->tuning['application_services_per_application']['max']];
        $now = now();
        $rows = [];
        $assignments = [];
        $rowApplicationIds = [];

        foreach ($appIds as $applicationId) {
            $perimeterId = $appPerimeterById[$applicationId];
            $serviceCount = random_int($min, $max);

            for ($i = 0; $i < $serviceCount; $i++) {
                $assignments[] = $perimeterId;
                $rowApplicationIds[] = $applicationId;

                $rows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                    'type' => fake()->randomElement(self::APPLICATION_SERVICE_TYPES),
                    'exposition' => fake()->randomElement(self::APPLICATION_SERVICE_EXPOSITIONS),
                    'description' => fake()->sentence(),
                    'attributes' => $this->randomAttributes(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Services applicatifs', count($rows));
        $ids = $this->insertRows('application_services', $rows);
        $this->phaseEnd();

        $pivotRows = [];

        foreach ($ids as $index => $serviceId) {
            $pivotRows[] = ['application_id' => $rowApplicationIds[$index], 'application_service_id' => $serviceId];
        }

        $this->phaseStart('Liens applications <-> services applicatifs', count($pivotRows));
        $links = 0;

        foreach (array_chunk($pivotRows, $this->chunkSize) as $chunk) {
            DB::table('application_application_service')->insert($chunk);
            $links += count($chunk);
            $this->tick(count($chunk));
        }

        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), $links];
    }

    /**
     * Entre 0 et 3 modules applicatifs par service (`application_modules_per_service`),
     * chacun dédié à ce seul service (pas partagé), reliés via la table pivot
     * brute `application_module_application_service`.
     *
     * @param  list<int>  $serviceIds
     * @param  array<int,int>  $servicePerimeterById
     * @return array{0: list<int>, 1: array<int,int>, 2: int} ids, perimeter par id, liens service<->module créés
     */
    private function insertApplicationModules(array $serviceIds, array $servicePerimeterById): array
    {
        if ($serviceIds === []) {
            return [[], [], 0];
        }

        [$min, $max] = [$this->tuning['application_modules_per_service']['min'], $this->tuning['application_modules_per_service']['max']];
        $now = now();
        $rows = [];
        $assignments = [];
        $rowServiceIds = [];

        foreach ($serviceIds as $serviceId) {
            $perimeterId = $servicePerimeterById[$serviceId];
            $moduleCount = random_int($min, $max);

            for ($i = 0; $i < $moduleCount; $i++) {
                $assignments[] = $perimeterId;
                $rowServiceIds[] = $serviceId;

                $rows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                    'type' => fake()->randomElement(self::APPLICATION_MODULE_TYPES),
                    'description' => fake()->sentence(),
                    'vendor' => fake()->company(),
                    'product' => fake()->word(),
                    'version' => fake()->regexify(FakerPatterns::SEMVER),
                    'attributes' => $this->randomAttributes(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Modules applicatifs', count($rows));
        $ids = $this->insertRows('application_modules', $rows);
        $this->phaseEnd();

        $pivotRows = [];

        foreach ($ids as $index => $moduleId) {
            $pivotRows[] = ['application_service_id' => $rowServiceIds[$index], 'application_module_id' => $moduleId];
        }

        $this->phaseStart('Liens services applicatifs <-> modules applicatifs', count($pivotRows));
        $links = 0;

        foreach (array_chunk($pivotRows, $this->chunkSize) as $chunk) {
            DB::table('application_module_application_service')->insert($chunk);
            $links += count($chunk);
            $this->tick(count($chunk));
        }

        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), $links];
    }

    /**
     * @param  list<int>  $perimeterIds
     * @return array{0: list<int>, 1: array<int,int>}
     */
    private function insertDatabases(int $count, array $perimeterIds): array
    {
        if ($count === 0) {
            return [[], []];
        }

        $assignments = $this->distributePerimeters($count, $perimeterIds);
        $now = now();
        $rows = [];

        foreach ($assignments as $perimeterId) {
            $env = $this->randomEnvironment();
            [$min, $max] = $this->securityNeedRange();

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => $this->databaseName($env),
                'type' => $this->pickWeighted(self::DATABASE_TYPES),
                'description' => fake()->sentence(),
                'entity_resp_id' => null,
                'responsible' => fake()->name(),
                'security_need_c' => random_int($min, $max),
                'security_need_i' => random_int($min, $max),
                'security_need_a' => random_int($min, $max),
                'security_need_t' => random_int($min, $max),
                'security_need_auth' => random_int(0, 4),
                'external' => fake()->boolean(10) ? 'oui' : 'non',
                'attributes' => $this->randomAttributes(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Bases de données', count($rows));
        $ids = $this->insertRows('databases', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments)];
    }

    /**
     * Chaque serveur logique se voit assigner un serveur physique de son
     * propre périmètre (`logical_server_physical_server`), et une adresse
     * IP piochée dans un sous-réseau du même site que ce serveur physique
     * (voir randomIpInCidr()). Sans serveur physique disponible dans le
     * périmètre, ou sans sous-réseau sur son site, une adresse IP générique
     * est utilisée à la place plutôt que d'échouer.
     *
     * @param  list<int>  $perimeterIds
     * @param  array<int,list<int>>  $physicalServersByPerimeter
     * @param  array<int,?int>  $physicalServerSiteById
     * @param  array<int,list<int>>  $subnetworksBySite
     * @param  array<int,string>  $subnetworkAddressById
     * @return array{0: list<int>, 1: array<int,int>, 2: array<int,?int>} ids, perimeter par id, physical_server_id par id
     */
    private function insertServers(
        int $count,
        array $perimeterIds,
        array $physicalServersByPerimeter,
        array $physicalServerSiteById,
        array $subnetworksBySite,
        array $subnetworkAddressById,
    ): array {
        if ($count === 0) {
            return [[], [], []];
        }

        $assignments = $this->distributePerimeters($count, $perimeterIds);
        $now = now();
        $rows = [];
        $rowPhysicalServerIds = [];

        foreach ($assignments as $perimeterId) {
            $env = $this->randomEnvironment();

            $physicalServerId = $this->pickParent($physicalServersByPerimeter, $perimeterId);
            $rowPhysicalServerIds[] = $physicalServerId;

            $addressIp = fake()->unique()->ipv4();

            if ($physicalServerId !== null) {
                $siteId = $physicalServerSiteById[$physicalServerId] ?? null;
                $subnetworkId = $siteId !== null ? $this->pickParent($subnetworksBySite, $siteId) : null;

                if ($subnetworkId !== null) {
                    $addressIp = $this->randomIpInCidr($subnetworkAddressById[$subnetworkId]);
                }
            }

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => $this->serverHostname($env),
                'type' => $this->pickWeighted(self::SERVER_TYPES),
                'description' => fake()->sentence(),
                'active' => true,
                'operating_system' => fake()->randomElement(['Ubuntu 22.04', 'Debian 12', 'RHEL 9', 'Windows Server 2022']),
                'environment' => $env,
                'attributes' => $this->randomAttributes(),
                'address_ip' => $addressIp,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Serveurs logiques', count($rows));
        $ids = $this->insertRows('logical_servers', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), array_combine($ids, $rowPhysicalServerIds)];
    }

    /**
     * @param  list<int>  $perimeterIds
     * @return array{0: list<int>, 1: array<int,int>}
     */
    /**
     * @param  list<int>  $perimeterIds
     * @return array{0: list<int>, 1: array<int,int>, 2: array<int,string>} ids, perimeter par id, nom par id
     */
    private function insertSites(int $count, array $perimeterIds): array
    {
        if ($count === 0) {
            return [[], [], []];
        }

        $assignments = $this->distributePerimeters($count, $perimeterIds);
        $now = now();
        $rows = [];
        $names = [];

        foreach ($assignments as $perimeterId) {
            $name = fake()->city();
            $names[] = $name;

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => $name,
                'type' => fake()->randomElement(self::SITE_TYPES),
                'description' => fake()->sentence(),
                'attributes' => $this->randomAttributes(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Sites', count($rows));
        $ids = $this->insertRows('sites', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), array_combine($ids, $names)];
    }

    /**
     * Un réseau logique par site, nommé comme le site (pas un compteur
     * configurable — une conséquence directe du nombre de sites).
     *
     * @param  list<int>  $siteIds
     * @param  array<int,int>  $sitePerimeterById
     * @param  array<int,string>  $siteNameById
     * @return array{0: list<int>, 1: array<int,int>, 2: array<int,int>} ids, perimeter par id, site_id par id
     */
    private function insertNetworks(array $siteIds, array $sitePerimeterById, array $siteNameById): array
    {
        if ($siteIds === []) {
            return [[], [], []];
        }

        $now = now();
        $rows = [];
        $assignments = [];

        foreach ($siteIds as $siteId) {
            $perimeterId = $sitePerimeterById[$siteId];
            $assignments[] = $perimeterId;

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => $siteNameById[$siteId],
                'type' => fake()->randomElement(self::NETWORK_TYPES),
                'description' => fake()->sentence(),
                'protocol_type' => fake()->randomElement(self::NETWORK_PROTOCOL_TYPES),
                'responsible' => fake()->name(),
                'responsible_sec' => fake()->name(),
                'security_need_c' => random_int(0, 4),
                'security_need_i' => random_int(0, 4),
                'security_need_a' => random_int(0, 4),
                'security_need_t' => random_int(0, 4),
                'security_need_auth' => random_int(0, 4),
                'attributes' => $this->randomAttributes(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Réseaux', count($rows));
        $ids = $this->insertRows('networks', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), array_combine($ids, $siteIds)];
    }

    /**
     * Entre 4 et 16 sous-réseaux par réseau — tirage direct, pas un
     * compteur global réparti sur les périmètres. Chaque sous-réseau est
     * associé à son propre VLAN (voir insertVlans()).
     *
     * @param  list<int>  $networkIds
     * @param  array<int,int>  $networkPerimeterById
     * @param  array<int,int>  $networkSiteById
     * @return array{0: list<int>, 1: array<int,int>, 2: list<int>, 3: array<int,int>, 4: array<int,string>} ids, perimeter par id, vlan_ids créés, site_id par id, adresse CIDR par id
     */
    private function insertSubnetworks(array $networkIds, array $networkPerimeterById, array $networkSiteById): array
    {
        if ($networkIds === []) {
            return [[], [], [], [], []];
        }

        [$min, $max] = [$this->tuning['subnetworks_per_network']['min'], $this->tuning['subnetworks_per_network']['max']];

        // Détermine d'abord le plan (réseau + périmètre + site par
        // sous-réseau à créer), pour pouvoir créer un VLAN par sous-réseau
        // *avant* et renseigner subnetworks.vlan_id dès l'insertion plutôt
        // que par une passe UPDATE séparée.
        $planNetworkIds = [];
        $planPerimeterIds = [];
        $planSiteIds = [];

        foreach ($networkIds as $networkId) {
            $perimeterId = $networkPerimeterById[$networkId];
            $siteId = $networkSiteById[$networkId];
            $subnetCount = random_int($min, $max);

            for ($i = 0; $i < $subnetCount; $i++) {
                $planNetworkIds[] = $networkId;
                $planPerimeterIds[] = $perimeterId;
                $planSiteIds[] = $siteId;
            }
        }

        $vlanIds = $this->insertVlans($planPerimeterIds);

        $now = now();
        $rows = [];
        $planAddresses = [];

        foreach ($planPerimeterIds as $i => $perimeterId) {
            $address = fake()->regexify(FakerPatterns::SUBNET_CIDR);
            $planAddresses[] = $address;

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => fake()->regexify(FakerPatterns::NETWORK_SEGMENT),
                'type' => fake()->randomElement(self::SUBNETWORK_TYPES),
                'description' => fake()->sentence(),
                'address' => $address,
                'ip_allocation_type' => fake()->regexify(FakerPatterns::IP_ALLOCATION_TYPE),
                'responsible_exp' => fake()->name(),
                'dmz' => fake()->regexify(FakerPatterns::YES_NO_FR),
                'wifi' => fake()->regexify(FakerPatterns::YES_NO_FR),
                'connected_subnets_id' => null,
                'gateway_id' => null,
                'zone' => fake()->regexify(FakerPatterns::SECURITY_ZONE),
                'vlan_id' => $vlanIds[$i],
                'network_id' => $planNetworkIds[$i],
                'subnetwork_id' => null,
                'default_gateway' => fake()->ipv4(),
                'attributes' => $this->randomAttributes(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Sous-réseaux', count($rows));
        $ids = $this->insertRows('subnetworks', $rows);
        $this->phaseEnd();

        return [
            $ids,
            array_combine($ids, $planPerimeterIds),
            $vlanIds,
            array_combine($ids, $planSiteIds),
            array_combine($ids, $planAddresses),
        ];
    }

    /**
     * Un VLAN par sous-réseau (1:1, même ordre que $rowPerimeterIds), créé
     * avant les sous-réseaux pour renseigner subnetworks.vlan_id dès
     * l'insertion.
     *
     * @param  list<int>  $rowPerimeterIds  un élément par VLAN à créer (pas la liste des périmètres disponibles)
     * @return list<int>
     */
    private function insertVlans(array $rowPerimeterIds): array
    {
        if ($rowPerimeterIds === []) {
            return [];
        }

        $now = now();
        $rows = [];

        foreach ($rowPerimeterIds as $perimeterId) {
            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => fake()->regexify(FakerPatterns::NETWORK_SEGMENT),
                'type' => 'VLAN',
                'description' => fake()->sentence(),
                'vlan_id' => (int) fake()->unique()->regexify(FakerPatterns::VLAN_ID),
                'attributes' => $this->randomAttributes(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('VLANs', count($rows));
        $ids = $this->insertRows('vlans', $rows);
        $this->phaseEnd();

        return $ids;
    }

    /**
     * Chaque bâtiment référence un site pris dans son propre périmètre
     * (ou aucun site si ce périmètre n'en a aucun).
     *
     * @param  list<int>  $perimeterIds
     * @param  array<int,list<int>>  $sitesByPerimeter
     * @return array{0: list<int>, 1: array<int,int>, 2: array<int,?int>} ids, perimeter par id, site_id par id
     */
    private function insertBuildings(int $count, array $perimeterIds, array $sitesByPerimeter): array
    {
        if ($count === 0) {
            return [[], [], []];
        }

        $assignments = $this->distributePerimeters($count, $perimeterIds);
        $now = now();
        $rows = [];
        $siteIds = [];

        foreach ($assignments as $perimeterId) {
            $siteId = $this->pickParent($sitesByPerimeter, $perimeterId);
            $siteIds[] = $siteId;

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => fake()->regexify(FakerPatterns::BUILDING_NAME),
                'type' => fake()->randomElement(self::BUILDING_TYPES),
                'description' => fake()->sentence(),
                'attributes' => $this->randomAttributes(),
                'site_id' => $siteId,
                'building_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Bâtiments', count($rows));
        $ids = $this->insertRows('buildings', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), array_combine($ids, $siteIds)];
    }

    /**
     * Entre 1 et 8 étages (ET1, ET2, ...) par site — un tirage direct par
     * site, pas un compteur global réparti sur les périmètres comme les
     * autres méthodes insert*. Un étage est une entrée `buildings`
     * supplémentaire, rattachée directement au site (building_id null),
     * en parallèle des bâtiments "classiques" générés par insertBuildings().
     *
     * @param  list<int>  $siteIds
     * @param  array<int,int>  $sitePerimeterById
     * @return array{0: list<int>, 1: array<int,int>, 2: array<int,int>} ids, perimeter par id, site par id
     */
    private function insertFloors(array $siteIds, array $sitePerimeterById): array
    {
        if ($siteIds === []) {
            return [[], [], []];
        }

        [$min, $max] = [$this->tuning['floors_per_site']['min'], $this->tuning['floors_per_site']['max']];
        $now = now();
        $rows = [];
        $assignments = [];
        $rowSiteIds = [];

        foreach ($siteIds as $siteId) {
            $perimeterId = $sitePerimeterById[$siteId];
            $floorCount = random_int($min, $max);

            for ($i = 1; $i <= $floorCount; $i++) {
                $assignments[] = $perimeterId;
                $rowSiteIds[] = $siteId;

                $rows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => "ET{$i}",
                    'type' => 'Etage',
                    'description' => fake()->sentence(),
                    'attributes' => $this->randomAttributes(),
                    'site_id' => $siteId,
                    'building_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Étages', count($rows));
        $ids = $this->insertRows('buildings', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), array_combine($ids, $rowSiteIds)];
    }

    /**
     * Entre 5 et 10 locaux (LOCAL042, ...) par étage — même logique de
     * tirage direct qu'insertFloors(), pas un compteur global. Un local est
     * lui aussi une entrée `buildings`, rattachée à son étage (building_id)
     * et au site de cet étage.
     *
     * @param  list<int>  $floorIds
     * @param  array<int,int>  $floorPerimeterById
     * @param  array<int,int>  $floorSiteById
     * @return array{0: list<int>, 1: array<int,int>, 2: array<int,int>, 3: array<int,int>} ids, perimeter par id, site par id, étage par id
     */
    private function insertLocals(array $floorIds, array $floorPerimeterById, array $floorSiteById): array
    {
        if ($floorIds === []) {
            return [[], [], [], []];
        }

        [$min, $max] = [$this->tuning['locals_per_floor']['min'], $this->tuning['locals_per_floor']['max']];
        $now = now();
        $rows = [];
        $assignments = [];
        $rowSiteIds = [];
        $rowFloorIds = [];

        foreach ($floorIds as $floorId) {
            $perimeterId = $floorPerimeterById[$floorId];
            $siteId = $floorSiteById[$floorId];
            $localCount = random_int($min, $max);

            for ($i = 0; $i < $localCount; $i++) {
                $assignments[] = $perimeterId;
                $rowSiteIds[] = $siteId;
                $rowFloorIds[] = $floorId;

                $rows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::LOCAL_NAME),
                    'type' => 'Local',
                    'description' => fake()->sentence(),
                    'attributes' => $this->randomAttributes(),
                    'site_id' => $siteId,
                    'building_id' => $floorId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Locaux', count($rows));
        $ids = $this->insertRows('buildings', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), array_combine($ids, $rowSiteIds), array_combine($ids, $rowFloorIds)];
    }

    /**
     * Chaque baie référence un bâtiment de son propre périmètre, et hérite
     * du même site que ce bâtiment (jamais un site indépendant).
     *
     * @param  list<int>  $perimeterIds
     * @param  array<int,list<int>>  $buildingsByPerimeter
     * @param  array<int,?int>  $buildingSiteById
     * @return array{0: list<int>, 1: array<int,int>, 2: array<int,?int>, 3: array<int,?int>} ids, perimeter, building_id, site_id
     */
    private function insertBays(int $count, array $perimeterIds, array $buildingsByPerimeter, array $buildingSiteById): array
    {
        if ($count === 0) {
            return [[], [], [], []];
        }

        $assignments = $this->distributePerimeters($count, $perimeterIds);
        $now = now();
        $rows = [];
        $buildingIds = [];
        $siteIds = [];

        foreach ($assignments as $perimeterId) {
            $buildingId = $this->pickParent($buildingsByPerimeter, $perimeterId);
            $siteId = $buildingId !== null ? ($buildingSiteById[$buildingId] ?? null) : null;

            $buildingIds[] = $buildingId;
            $siteIds[] = $siteId;

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => fake()->regexify(FakerPatterns::BAY_NAME),
                'type' => fake()->randomElement(self::BAY_TYPES),
                'description' => fake()->sentence(),
                'attributes' => $this->randomAttributes(),
                'building_id' => $buildingId,
                'site_id' => $siteId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Baies', count($rows));
        $ids = $this->insertRows('bays', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), array_combine($ids, $buildingIds), array_combine($ids, $siteIds)];
    }

    /**
     * Un switch physique par baie : pas un compteur à atteindre, une baie
     * sans switch n'aurait pas de sens dans une infrastructure cohérente.
     *
     * @param  list<int>  $bayIds
     * @param  array<int,int>  $bayPerimeterById
     * @param  array<int,?int>  $bayBuildingById
     * @param  array<int,?int>  $baySiteById
     * @return array{0: list<int>, 1: array<int,int>, 2: array<int,int>, 3: array<int,?int>} ids, perimeter par id, switch_id par bay_id, site par switch_id
     */
    private function insertPhysicalSwitches(array $bayIds, array $bayPerimeterById, array $bayBuildingById, array $baySiteById): array
    {
        if ($bayIds === []) {
            return [[], [], [], []];
        }

        $now = now();
        $rows = [];
        $assignments = [];
        $siteIds = [];

        foreach ($bayIds as $bayId) {
            $perimeterId = $bayPerimeterById[$bayId];
            $assignments[] = $perimeterId;
            $siteIds[] = $baySiteById[$bayId] ?? null;

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => fake()->regexify(FakerPatterns::HOSTNAME),
                'type' => 'Switch',
                'description' => fake()->sentence(),
                'vendor' => fake()->company(),
                'product' => fake()->word(),
                'version' => fake()->regexify(FakerPatterns::SEMVER),
                'attributes' => $this->randomAttributes(),
                'site_id' => $baySiteById[$bayId] ?? null,
                'building_id' => $bayBuildingById[$bayId] ?? null,
                'bay_id' => $bayId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Switchs physiques', count($rows));
        $ids = $this->insertRows('physical_switches', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), array_combine($bayIds, $ids), array_combine($ids, $siteIds)];
    }

    /**
     * Un switch physique par étage (câblage bureautique), distinct du
     * switch de baie (câblage technique côté datacenter/local serveur).
     * Contrairement à une baie, un étage EST déjà une entrée de la table
     * `buildings` : building_id porte directement l'étage, il n'y a pas de
     * baie associée.
     *
     * @param  list<int>  $floorIds
     * @param  array<int,int>  $floorPerimeterById
     * @param  array<int,int>  $floorSiteById
     * @return array{0: list<int>, 1: array<int,int>, 2: array<int,int>, 3: array<int,int>} ids, perimeter par id, site par id, switch_id par étage
     */
    private function insertFloorSwitches(array $floorIds, array $floorPerimeterById, array $floorSiteById): array
    {
        if ($floorIds === []) {
            return [[], [], [], []];
        }

        $now = now();
        $rows = [];
        $assignments = [];
        $siteIds = [];

        foreach ($floorIds as $floorId) {
            $perimeterId = $floorPerimeterById[$floorId];
            $assignments[] = $perimeterId;
            $siteIds[] = $floorSiteById[$floorId];

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => fake()->regexify(FakerPatterns::HOSTNAME),
                'type' => 'Switch',
                'description' => fake()->sentence(),
                'vendor' => fake()->company(),
                'product' => fake()->word(),
                'version' => fake()->regexify(FakerPatterns::SEMVER),
                'attributes' => $this->randomAttributes(),
                'site_id' => $floorSiteById[$floorId],
                'building_id' => $floorId,
                'bay_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Switchs physiques (étages)', count($rows));
        $ids = $this->insertRows('physical_switches', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), array_combine($ids, $siteIds), array_combine($floorIds, $ids)];
    }

    /**
     * Un routeur physique par site, placé dans l'une des baies de ce site
     * (jamais un routeur "hors baie" ou dans la baie d'un autre site). Les
     * sites sans aucune baie n'ont pas de routeur.
     *
     * @param  list<int>  $siteIds
     * @param  array<int,int>  $sitePerimeterById
     * @param  array<int,list<int>>  $baysBySite
     * @param  array<int,?int>  $bayBuildingById
     * @return array{0: list<int>, 1: array<int,int>, 2: array<int,int>} ids, perimeter par id, router_id par site_id
     */
    private function insertPhysicalRouters(array $siteIds, array $sitePerimeterById, array $baysBySite, array $bayBuildingById): array
    {
        if ($siteIds === []) {
            return [[], [], []];
        }

        $now = now();
        $rows = [];
        $assignments = [];
        $rowSiteIds = [];

        foreach ($siteIds as $siteId) {
            $bayPool = $baysBySite[$siteId] ?? [];

            if ($bayPool === []) {
                continue;
            }

            $bayId = $bayPool[array_rand($bayPool)];
            $perimeterId = $sitePerimeterById[$siteId];
            $assignments[] = $perimeterId;
            $rowSiteIds[] = $siteId;

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => fake()->regexify(FakerPatterns::HOSTNAME),
                'type' => 'Routeur',
                'description' => fake()->sentence(),
                'vendor' => fake()->company(),
                'product' => fake()->word(),
                'version' => fake()->regexify(FakerPatterns::SEMVER),
                'attributes' => $this->randomAttributes(),
                'site_id' => $siteId,
                'building_id' => $bayBuildingById[$bayId] ?? null,
                'bay_id' => $bayId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Routeurs physiques', count($rows));
        $ids = $this->insertRows('physical_routers', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), array_combine($rowSiteIds, $ids)];
    }

    /**
     * Chaque serveur physique référence une baie de son propre périmètre, et
     * hérite du bâtiment/site de cette baie (jamais une baie, un bâtiment et
     * un site pris indépendamment les uns des autres).
     *
     * @param  list<int>  $perimeterIds
     * @param  array<int,list<int>>  $baysByPerimeter
     * @param  array<int,?int>  $bayBuildingById
     * @param  array<int,?int>  $baySiteById
     * @return array{0: list<int>, 1: array<int,int>, 2: array<int,?int>, 3: array<int,?int>} ids, perimeter, bay_id par id, site_id par id
     */
    private function insertPhysicalServers(int $count, array $perimeterIds, array $baysByPerimeter, array $bayBuildingById, array $baySiteById): array
    {
        if ($count === 0) {
            return [[], [], [], []];
        }

        $assignments = $this->distributePerimeters($count, $perimeterIds);
        $now = now();
        $rows = [];
        $rowBayIds = [];
        $rowSiteIds = [];

        foreach ($assignments as $perimeterId) {
            $bayId = $this->pickParent($baysByPerimeter, $perimeterId);
            $buildingId = $bayId !== null ? ($bayBuildingById[$bayId] ?? null) : null;
            $siteId = $bayId !== null ? ($baySiteById[$bayId] ?? null) : null;
            $rowBayIds[] = $bayId;
            $rowSiteIds[] = $siteId;

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => fake()->regexify(FakerPatterns::HOSTNAME),
                'type' => fake()->randomElement(self::PHYSICAL_SERVER_TYPES),
                'description' => fake()->sentence(),
                'configuration' => null,
                'address_ip' => fake()->unique()->ipv4(),
                'cpu' => fake()->randomElement(self::CPU_MODELS),
                'memory' => fake()->randomElement(self::MEMORY_SIZES),
                'disk' => fake()->randomElement(self::DISK_SIZES),
                'disk_used' => random_int(10, 90).' %',
                'operating_system' => fake()->randomElement(self::OPERATING_SYSTEMS),
                'responsible' => fake()->name(),
                'attributes' => $this->randomAttributes(),
                'site_id' => $siteId,
                'building_id' => $buildingId,
                'bay_id' => $bayId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Serveurs physiques', count($rows));
        $ids = $this->insertRows('physical_servers', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), array_combine($ids, $rowBayIds), array_combine($ids, $rowSiteIds)];
    }

    /**
     * Relie chaque serveur physique au switch de sa propre baie (un lien
     * physique par serveur ayant une baie).
     *
     * @param  list<int>  $physicalServerIds
     * @param  array<int,int>  $serverPerimeterById
     * @param  array<int,?int>  $serverBayById
     * @param  array<int,int>  $switchIdByBayId
     */
    private function insertServerSwitchLinks(array $physicalServerIds, array $serverPerimeterById, array $serverBayById, array $switchIdByBayId): int
    {
        if ($physicalServerIds === [] || $switchIdByBayId === []) {
            return 0;
        }

        $this->phaseStart('Liens serveurs physiques <-> switchs', count($physicalServerIds));

        $now = now();
        $rows = [];
        $total = 0;

        foreach ($physicalServerIds as $serverId) {
            $bayId = $serverBayById[$serverId] ?? null;
            $switchId = $bayId !== null ? ($switchIdByBayId[$bayId] ?? null) : null;

            if ($switchId !== null) {
                $rows[] = [
                    'perimeter_id' => $serverPerimeterById[$serverId],
                    'type' => 'LAN',
                    'color' => '#0000FF',
                    'description' => null,
                    'attributes' => $this->randomAttributes(),
                    'src_port' => fake()->regexify(FakerPatterns::PORT),
                    'dest_port' => fake()->regexify(FakerPatterns::PORT),
                    'physical_server_src_id' => $serverId,
                    'physical_switch_dest_id' => $switchId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $total++;
            }

            if (count($rows) >= $this->chunkSize) {
                DB::table('physical_links')->insert($rows);
                $rows = [];
            }

            $this->tick();
        }

        if ($rows !== []) {
            DB::table('physical_links')->insert($rows);
        }

        $this->phaseEnd();

        return $total;
    }

    /**
     * Relie chaque switch fourni (de baie ou d'étage, peu importe son
     * origine) au routeur du même site. Un switch dont le site n'a pas de
     * routeur (site sans aucune baie) n'a pas de lien.
     *
     * @param  list<int>  $switchIds
     * @param  array<int,?int>  $switchSiteById
     * @param  array<int,int>  $switchPerimeterById
     * @param  array<int,int>  $routerIdBySite
     */
    private function insertSwitchRouterLinks(array $switchIds, array $switchSiteById, array $switchPerimeterById, array $routerIdBySite): int
    {
        if ($switchIds === [] || $routerIdBySite === []) {
            return 0;
        }

        $this->phaseStart('Liens switchs <-> routeurs', count($switchIds));

        $now = now();
        $rows = [];
        $total = 0;

        foreach ($switchIds as $switchId) {
            $siteId = $switchSiteById[$switchId] ?? null;
            $routerId = $siteId !== null ? ($routerIdBySite[$siteId] ?? null) : null;

            if ($routerId !== null) {
                $rows[] = [
                    'perimeter_id' => $switchPerimeterById[$switchId],
                    'type' => 'LAN',
                    'color' => '#0000FF',
                    'description' => null,
                    'attributes' => $this->randomAttributes(),
                    'src_port' => fake()->regexify(FakerPatterns::PORT),
                    'dest_port' => fake()->regexify(FakerPatterns::PORT),
                    'physical_switch_src_id' => $switchId,
                    'physical_router_dest_id' => $routerId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $total++;
            }

            if (count($rows) >= $this->chunkSize) {
                DB::table('physical_links')->insert($rows);
                $rows = [];
            }

            $this->tick();
        }

        if ($rows !== []) {
            DB::table('physical_links')->insert($rows);
        }

        $this->phaseEnd();

        return $total;
    }

    /**
     * Relie chaque équipement fourni (poste de travail, borne WiFi, ...) au
     * switch le plus proche : celui de son propre étage, ou celui de
     * l'étage qui contient son local — jamais un switch de baie. Un
     * équipement dont l'étage/local n'a pas de switch résolu (ne devrait
     * arriver que si le switch de cet étage a lui-même échoué à se créer)
     * n'a pas de lien.
     *
     * @param  list<int>  $deviceIds
     * @param  array<int,int>  $devicePerimeterById
     * @param  array<int,?int>  $deviceBuildingById  étage ou local par device_id
     * @param  array<int,int>  $switchIdByBuildingId  switch le plus proche, par étage ET par local (voir generate())
     */
    private function insertNearestSwitchLinks(array $deviceIds, array $devicePerimeterById, array $deviceBuildingById, array $switchIdByBuildingId, string $srcColumn, string $label): int
    {
        if ($deviceIds === [] || $switchIdByBuildingId === []) {
            return 0;
        }

        $this->phaseStart($label, count($deviceIds));

        $now = now();
        $rows = [];
        $total = 0;

        foreach ($deviceIds as $deviceId) {
            $buildingId = $deviceBuildingById[$deviceId] ?? null;
            $switchId = $buildingId !== null ? ($switchIdByBuildingId[$buildingId] ?? null) : null;

            if ($switchId !== null) {
                $rows[] = [
                    'perimeter_id' => $devicePerimeterById[$deviceId],
                    'type' => 'LAN',
                    'color' => '#0000FF',
                    'description' => null,
                    'attributes' => $this->randomAttributes(),
                    'src_port' => fake()->regexify(FakerPatterns::PORT),
                    'dest_port' => fake()->regexify(FakerPatterns::PORT),
                    $srcColumn => $deviceId,
                    'physical_switch_dest_id' => $switchId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $total++;
            }

            if (count($rows) >= $this->chunkSize) {
                DB::table('physical_links')->insert($rows);
                $rows = [];
            }

            $this->tick();
        }

        if ($rows !== []) {
            DB::table('physical_links')->insert($rows);
        }

        $this->phaseEnd();

        return $total;
    }

    /**
     * Même chaînage que les serveurs physiques : un périphérique référence
     * une baie de son propre périmètre, et hérite du bâtiment/site de cette
     * baie.
     *
     * @param  list<int>  $perimeterIds
     * @param  array<int,list<int>>  $baysByPerimeter
     * @param  array<int,?int>  $bayBuildingById
     * @param  array<int,?int>  $baySiteById
     * @return array{0: list<int>, 1: array<int,int>}
     */
    private function insertPeripherals(int $count, array $perimeterIds, array $baysByPerimeter, array $bayBuildingById, array $baySiteById): array
    {
        if ($count === 0) {
            return [[], []];
        }

        $assignments = $this->distributePerimeters($count, $perimeterIds);
        $now = now();
        $rows = [];

        foreach ($assignments as $perimeterId) {
            $bayId = $this->pickParent($baysByPerimeter, $perimeterId);
            $buildingId = $bayId !== null ? ($bayBuildingById[$bayId] ?? null) : null;
            $siteId = $bayId !== null ? ($baySiteById[$bayId] ?? null) : null;

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => fake()->regexify(FakerPatterns::HOSTNAME),
                'type' => fake()->randomElement(self::PERIPHERAL_TYPES),
                'description' => fake()->sentence(),
                'vendor' => fake()->company(),
                'product' => fake()->word(),
                'version' => fake()->regexify(FakerPatterns::SEMVER),
                'responsible' => fake()->name(),
                'address_ip' => fake()->unique()->ipv4(),
                'attributes' => $this->randomAttributes(),
                'domain_id' => null,
                'provider_id' => null,
                'site_id' => $siteId,
                'building_id' => $buildingId,
                'bay_id' => $bayId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Périphériques', count($rows));
        $ids = $this->insertRows('peripherals', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments)];
    }

    /**
     * Un poste de travail référence un bâtiment de son propre périmètre, et
     * hérite du site de ce bâtiment (les postes de travail ne sont pas
     * rattachés à une baie, contrairement aux serveurs/périphériques).
     *
     * @param  list<int>  $perimeterIds
     * @param  array<int,list<int>>  $buildingsByPerimeter
     * @param  array<int,?int>  $buildingSiteById
     * @return array{0: list<int>, 1: array<int,int>, 2: array<int,?int>} ids, perimeter, building_id (local) par id
     */
    private function insertWorkstations(int $count, array $perimeterIds, array $buildingsByPerimeter, array $buildingSiteById): array
    {
        if ($count === 0) {
            return [[], [], []];
        }

        $assignments = $this->distributePerimeters($count, $perimeterIds);
        $now = now();
        $rows = [];
        $rowBuildingIds = [];

        foreach ($assignments as $perimeterId) {
            $buildingId = $this->pickParent($buildingsByPerimeter, $perimeterId);
            $siteId = $buildingId !== null ? ($buildingSiteById[$buildingId] ?? null) : null;
            $rowBuildingIds[] = $buildingId;

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => fake()->regexify(FakerPatterns::WORKSTATION_NAME),
                'type' => fake()->randomElement(self::WORKSTATION_TYPES),
                'description' => fake()->sentence(),
                'manufacturer' => fake()->company(),
                'model' => fake()->word(),
                'serial_number' => fake()->regexify(FakerPatterns::SERIAL_NUMBER),
                'cpu' => fake()->randomElement(self::WORKSTATION_CPU_MODELS),
                'memory' => fake()->randomElement(self::MEMORY_SIZES),
                // Contrairement à physical_servers.disk (varchar), workstations.disk
                // est un entier (Go) — pas de libellé "500 Go"/"1 To" ici.
                'disk' => fake()->randomElement([128, 256, 512, 1024, 2048]),
                'operating_system' => fake()->randomElement(self::WORKSTATION_OPERATING_SYSTEMS),
                'entity_id' => null,
                'domain_id' => null,
                'user_id' => null,
                'other_user' => null,
                'network_id' => null,
                'network_port_type' => fake()->randomElement(['RJ45', 'WiFi']),
                'address_ip' => fake()->unique()->ipv4(),
                'mac_address' => fake()->regexify(FakerPatterns::MAC_ADDRESS),
                'attributes' => $this->randomAttributes(),
                'site_id' => $siteId,
                'building_id' => $buildingId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Postes de travail', count($rows));
        $ids = $this->insertRows('workstations', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), array_combine($ids, $rowBuildingIds)];
    }

    /**
     * Même chaînage site/bâtiment que les postes de travail (pas de baie).
     *
     * @param  list<int>  $perimeterIds
     * @param  array<int,list<int>>  $buildingsByPerimeter
     * @param  array<int,?int>  $buildingSiteById
     * @return array{0: list<int>, 1: array<int,int>}
     */
    private function insertPhones(int $count, array $perimeterIds, array $buildingsByPerimeter, array $buildingSiteById): array
    {
        if ($count === 0) {
            return [[], []];
        }

        $assignments = $this->distributePerimeters($count, $perimeterIds);
        $now = now();
        $rows = [];

        foreach ($assignments as $perimeterId) {
            $buildingId = $this->pickParent($buildingsByPerimeter, $perimeterId);
            $siteId = $buildingId !== null ? ($buildingSiteById[$buildingId] ?? null) : null;

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => fake()->regexify(FakerPatterns::PHONE_NAME),
                'type' => fake()->randomElement(self::PHONE_TYPES),
                'description' => fake()->sentence(),
                'attributes' => $this->randomAttributes(),
                'address_ip' => fake()->unique()->ipv4(),
                'site_id' => $siteId,
                'building_id' => $buildingId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Téléphones', count($rows));
        $ids = $this->insertRows('phones', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments)];
    }

    /**
     * Contrairement aux autres objets (un compteur cible réparti sur les
     * périmètres), une borne WiFi est un tirage indépendant par bâtiment :
     * chaque bâtiment a une probabilité `$probability` de recevoir sa
     * propre borne, dans son propre périmètre et rattachée à son propre
     * site — pas de compteur global à atteindre.
     *
     * @param  list<int>  $buildingIds
     * @param  array<int,int>  $buildingPerimeterById
     * @param  array<int,?int>  $buildingSiteById
     * @return array{0: list<int>, 1: array<int,int>, 2: array<int,int>} ids, perimeter, building_id (étage ou local) par id
     */
    private function insertWifiTerminals(array $buildingIds, array $buildingPerimeterById, array $buildingSiteById, float $probability): array
    {
        if ($buildingIds === [] || $probability <= 0) {
            return [[], [], []];
        }

        $now = now();
        $rows = [];
        $assignments = [];
        $rowBuildingIds = [];

        foreach ($buildingIds as $buildingId) {
            if ((mt_rand() / mt_getrandmax()) >= $probability) {
                continue;
            }

            $perimeterId = $buildingPerimeterById[$buildingId];
            $assignments[] = $perimeterId;
            $rowBuildingIds[] = $buildingId;

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => fake()->regexify(FakerPatterns::HOSTNAME),
                'type' => fake()->randomElement(self::WIFI_TERMINAL_TYPES),
                'description' => fake()->sentence(),
                'attributes' => $this->randomAttributes(),
                'address_ip' => fake()->unique()->ipv4(),
                'vendor' => fake()->company(),
                'product' => fake()->word(),
                'version' => fake()->regexify(FakerPatterns::SEMVER),
                'site_id' => $buildingSiteById[$buildingId] ?? null,
                'building_id' => $buildingId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Bornes WiFi', count($rows));
        $ids = $this->insertRows('wifi_terminals', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), array_combine($ids, $rowBuildingIds)];
    }

    /**
     * Répartit $total en $buckets parts aussi égales que possible (le reste
     * de la division est distribué aux premières parts) — jamais un tirage
     * aléatoire indépendant par part, qui pourrait s'écarter du total exact.
     *
     * @return list<int>
     */
    private function splitEvenly(int $total, int $buckets): array
    {
        if ($buckets <= 0) {
            return [];
        }

        $base = intdiv($total, $buckets);
        $remainder = $total % $buckets;

        return array_map(
            fn (int $i) => $base + ($i < $remainder ? 1 : 0),
            range(0, $buckets - 1),
        );
    }

    /**
     * Pour chaque site : 1 à 3 zones d'administration (`zone_admins_per_site`),
     * un service d'annuaire (rattaché à l'une de ces zones et, si possible, à
     * une application de son propre périmètre), une forêt Active
     * Directory/LDAP (rattachée à l'une de ces zones), 3 à 5 domaines
     * (`domains_per_site`) rattachés à cette forêt, et autant d'utilisateurs
     * (`admin_users`) que de postes de travail effectivement créés sur ce
     * site — répartis aussi également que possible entre les domaines du
     * site (voir splitEvenly()), chaque domaine recevant exactement
     * `domains.user_count` utilisateurs, sans mise à jour a posteriori.
     * Aucune de ces quatre tables n'a de colonne site_id (contrairement aux
     * workstations) : le découpage "par site" ne structure donc que la
     * génération, pas une association interrogeable en base au-delà du
     * périmètre.
     *
     * @param  list<int>  $siteIds
     * @param  array<int,int>  $sitePerimeterById
     * @param  array<int,list<int>>  $applicationsByPerimeter
     * @param  array<int,list<int>>  $workstationsBySite
     * @return array{0: list<int>, 1: list<int>, 2: list<int>, 3: list<int>, 4: list<int>, 5: int}
     */
    private function insertDirectoryInfrastructure(
        array $siteIds,
        array $sitePerimeterById,
        array $applicationsByPerimeter,
        array $workstationsBySite,
    ): array {
        if ($siteIds === []) {
            return [[], [], [], [], [], 0];
        }

        [$zaMin, $zaMax] = [$this->tuning['zone_admins_per_site']['min'], $this->tuning['zone_admins_per_site']['max']];
        [$domMin, $domMax] = [$this->tuning['domains_per_site']['min'], $this->tuning['domains_per_site']['max']];
        $now = now();

        // Étape 1 : zones d'administration.
        $zoneAdminRows = [];
        $siteZoneAdminCount = [];

        foreach ($siteIds as $siteId) {
            $perimeterId = $sitePerimeterById[$siteId];
            $count = random_int($zaMin, $zaMax);
            $siteZoneAdminCount[$siteId] = $count;

            for ($i = 0; $i < $count; $i++) {
                $zoneAdminRows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                    'type' => fake()->randomElement(self::ZONE_ADMIN_TYPES),
                    'description' => fake()->sentence(),
                    'attributes' => $this->randomAttributes(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Zones d\'administration', count($zoneAdminRows));
        $zoneAdminIds = $this->insertRows('zone_admins', $zoneAdminRows);
        $this->phaseEnd();

        // $zoneAdminIds est un simple intervalle inséré dans l'ordre de
        // $zoneAdminRows : on peut donc le retrancher par site en réutilisant
        // les mêmes compteurs qu'à l'étape 1.
        $zoneAdminIdsBySite = [];
        $cursor = 0;

        foreach ($siteIds as $siteId) {
            $count = $siteZoneAdminCount[$siteId];
            $zoneAdminIdsBySite[$siteId] = array_slice($zoneAdminIds, $cursor, $count);
            $cursor += $count;
        }

        // Étape 2 : un annuaire ET une forêt AD par site (un enregistrement
        // chacun, dans le même ordre que $siteIds), tous deux rattachés à
        // une zone d'administration de ce site.
        $annuaireRows = [];
        $forestAdRows = [];

        foreach ($siteIds as $siteId) {
            $perimeterId = $sitePerimeterById[$siteId];
            $zoneAdminId = $zoneAdminIdsBySite[$siteId][array_rand($zoneAdminIdsBySite[$siteId])];

            $annuaireRows[] = [
                'perimeter_id' => $perimeterId,
                'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                'type' => fake()->randomElement(self::ANNUAIRE_TYPES),
                'description' => fake()->sentence(),
                'solution' => fake()->randomElement(self::ANNUAIRE_SOLUTIONS),
                'zone_admin_id' => $zoneAdminId,
                'application_id' => $this->pickParent($applicationsByPerimeter, $perimeterId),
                'attributes' => $this->randomAttributes(),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $forestAdRows[] = [
                'perimeter_id' => $perimeterId,
                'name' => fake()->regexify(FakerPatterns::DOMAIN_FQDN),
                'type' => fake()->randomElement(self::FOREST_AD_TYPES),
                'description' => fake()->sentence(),
                'zone_admin_id' => $zoneAdminId,
                'attributes' => $this->randomAttributes(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Annuaires', count($annuaireRows));
        $annuaireIds = $this->insertRows('annuaires', $annuaireRows);
        $this->phaseEnd();

        $this->phaseStart('Forêts Active Directory / LDAP', count($forestAdRows));
        $forestAdIds = $this->insertRows('forest_ads', $forestAdRows);
        $this->phaseEnd();

        // Étape 3 : 3 à 5 domaines par site. Le nombre d'utilisateurs de
        // chaque domaine est décidé ici (répartition égale du nombre de
        // postes de travail du site), pour que domains.user_count corresponde
        // exactement au nombre d'admin_users créés à l'étape 5.
        $domainRows = [];
        $siteDomainCount = [];
        $siteDomainUserTally = [];

        foreach ($siteIds as $siteId) {
            $perimeterId = $sitePerimeterById[$siteId];
            $count = random_int($domMin, $domMax);
            $siteDomainCount[$siteId] = $count;

            $workstationCount = count($workstationsBySite[$siteId] ?? []);
            $tally = $this->splitEvenly($workstationCount, $count);
            $siteDomainUserTally[$siteId] = $tally;

            foreach ($tally as $userCount) {
                $domainRows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::DOMAIN_FQDN),
                    'type' => fake()->randomElement(self::DOMAIN_TYPES),
                    'description' => fake()->sentence(),
                    'domain_ctrl_cnt' => random_int(1, 4),
                    'user_count' => $userCount,
                    'machine_count' => random_int(10, 500),
                    'relation_inter_domaine' => fake()->randomElement(self::DOMAIN_INTER_DOMAIN_RELATIONS),
                    'attributes' => $this->randomAttributes(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Domaines Active Directory / LDAP', count($domainRows));
        $domainIds = $this->insertRows('domains', $domainRows);
        $this->phaseEnd();

        $domainIdsBySite = [];
        $cursor = 0;

        foreach ($siteIds as $siteId) {
            $count = $siteDomainCount[$siteId];
            $domainIdsBySite[$siteId] = array_slice($domainIds, $cursor, $count);
            $cursor += $count;
        }

        // Étape 4 : chaque domaine d'un site appartient à la forêt AD de ce
        // même site (domain_forest_ad, table pivot brute).
        $domainForestAdRows = [];

        foreach ($siteIds as $index => $siteId) {
            $forestAdId = $forestAdIds[$index];

            foreach ($domainIdsBySite[$siteId] as $domainId) {
                $domainForestAdRows[] = ['domain_id' => $domainId, 'forest_ad_id' => $forestAdId];
            }
        }

        $this->phaseStart('Liens forêts AD <-> domaines', count($domainForestAdRows));
        $domainForestAdLinks = 0;

        foreach (array_chunk($domainForestAdRows, $this->chunkSize) as $chunk) {
            DB::table('domain_forest_ad')->insert($chunk);
            $domainForestAdLinks += count($chunk);
            $this->tick(count($chunk));
        }

        $this->phaseEnd();

        // Étape 5 : les utilisateurs (admin_users) de chaque site, répartis
        // entre ses domaines selon $siteDomainUserTally (décidé à l'étape 3).
        $adminUserRows = [];

        foreach ($siteIds as $siteId) {
            $perimeterId = $sitePerimeterById[$siteId];

            foreach ($domainIdsBySite[$siteId] as $index => $domainId) {
                $userCount = $siteDomainUserTally[$siteId][$index];

                for ($i = 0; $i < $userCount; $i++) {
                    $adminUserRows[] = [
                        'perimeter_id' => $perimeterId,
                        'user_id' => fake()->unique()->userName(),
                        'firstname' => fake()->firstName(),
                        'lastname' => fake()->lastName(),
                        'type' => fake()->randomElement(self::ADMIN_USER_TYPES),
                        'attributes' => $this->randomAttributes(),
                        'icon_id' => null,
                        'description' => fake()->sentence(),
                        'domain_id' => $domainId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        $this->phaseStart('Utilisateurs (admin_users)', count($adminUserRows));
        $adminUserIds = $this->insertRows('admin_users', $adminUserRows);
        $this->phaseEnd();

        return [$zoneAdminIds, $annuaireIds, $forestAdIds, $domainIds, $adminUserIds, $domainForestAdLinks];
    }

    /**
     * Entre 3 et 5 macro-processus par périmètre (`macro_processes_per_perimeter`)
     * — tirage direct, pas un compteur global réparti sur les périmètres.
     *
     * @param  list<int>  $perimeterIds
     * @return array{0: list<int>, 1: array<int,int>}
     */
    private function insertMacroProcesses(array $perimeterIds): array
    {
        if ($perimeterIds === []) {
            return [[], []];
        }

        [$min, $max] = [$this->tuning['macro_processes_per_perimeter']['min'], $this->tuning['macro_processes_per_perimeter']['max']];
        $now = now();
        $rows = [];
        $assignments = [];

        foreach ($perimeterIds as $perimeterId) {
            $count = random_int($min, $max);

            for ($i = 0; $i < $count; $i++) {
                $assignments[] = $perimeterId;

                [$min2, $max2] = $this->securityNeedRange();

                $rows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                    'type' => fake()->randomElement(self::MACRO_PROCESS_TYPES),
                    'description' => fake()->sentence(),
                    'io_elements' => fake()->sentence(),
                    'security_need_c' => random_int($min2, $max2),
                    'security_need_i' => random_int($min2, $max2),
                    'security_need_a' => random_int($min2, $max2),
                    'security_need_t' => random_int($min2, $max2),
                    'security_need_auth' => random_int(0, 4),
                    'owner' => fake()->name(),
                    'attributes' => $this->randomAttributes(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Macro-processus', count($rows));
        $ids = $this->insertRows('macro_processuses', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments)];
    }

    /**
     * Entre 3 et 10 processus par macro-processus (`processes_per_macro_process`),
     * chacun rattaché à son macro-processus via `macroprocess_id`.
     *
     * @param  list<int>  $macroProcessIds
     * @param  array<int,int>  $macroProcessPerimeterById
     * @return array{0: list<int>, 1: array<int,int>}
     */
    private function insertProcesses(array $macroProcessIds, array $macroProcessPerimeterById): array
    {
        if ($macroProcessIds === []) {
            return [[], []];
        }

        [$min, $max] = [$this->tuning['processes_per_macro_process']['min'], $this->tuning['processes_per_macro_process']['max']];
        $now = now();
        $rows = [];
        $assignments = [];

        foreach ($macroProcessIds as $macroProcessId) {
            $perimeterId = $macroProcessPerimeterById[$macroProcessId];
            $count = random_int($min, $max);

            for ($i = 0; $i < $count; $i++) {
                $assignments[] = $perimeterId;

                [$min2, $max2] = $this->securityNeedRange();

                $rows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                    'type' => fake()->randomElement(self::PROCESS_TYPES),
                    'description' => fake()->sentence(),
                    'in_out' => fake()->sentence(),
                    'security_need_c' => random_int($min2, $max2),
                    'security_need_i' => random_int($min2, $max2),
                    'security_need_a' => random_int($min2, $max2),
                    'security_need_t' => random_int($min2, $max2),
                    'security_need_auth' => random_int(0, 4),
                    'owner' => fake()->name(),
                    'macroprocess_id' => $macroProcessId,
                    'attributes' => $this->randomAttributes(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Processus', count($rows));
        $ids = $this->insertRows('processes', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments)];
    }

    /**
     * Entre 5 et 10 activités par processus (`activities_per_process`),
     * chacune dédiée à ce seul processus (pas partagée), reliées via la
     * table pivot brute `activity_process`.
     *
     * @param  list<int>  $processIds
     * @param  array<int,int>  $processPerimeterById
     * @return array{0: list<int>, 1: array<int,int>, 2: array<int,int>, 3: int} ids, perimeter par id, processus par id, liens créés
     */
    private function insertActivities(array $processIds, array $processPerimeterById): array
    {
        if ($processIds === []) {
            return [[], [], [], 0];
        }

        [$min, $max] = [$this->tuning['activities_per_process']['min'], $this->tuning['activities_per_process']['max']];
        $now = now();
        $rows = [];
        $assignments = [];
        $rowProcessIds = [];

        foreach ($processIds as $processId) {
            $perimeterId = $processPerimeterById[$processId];
            $count = random_int($min, $max);

            for ($i = 0; $i < $count; $i++) {
                $assignments[] = $perimeterId;
                $rowProcessIds[] = $processId;

                $rows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                    'type' => fake()->randomElement(self::ACTIVITY_TYPES),
                    'description' => fake()->sentence(),
                    'recovery_time_objective' => random_int(0, 10080),
                    'maximum_tolerable_downtime' => random_int(0, 20160),
                    'recovery_point_objective' => random_int(0, 10080),
                    'maximum_tolerable_data_loss' => random_int(0, 20160),
                    'drp' => fake()->sentence(),
                    'drp_link' => null,
                    'attributes' => $this->randomAttributes(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Activités', count($rows));
        $ids = $this->insertRows('activities', $rows);
        $this->phaseEnd();

        $pivotRows = [];

        foreach ($ids as $index => $activityId) {
            $pivotRows[] = ['process_id' => $rowProcessIds[$index], 'activity_id' => $activityId];
        }

        $this->phaseStart('Liens processus <-> activités', count($pivotRows));
        $links = 0;

        foreach (array_chunk($pivotRows, $this->chunkSize) as $chunk) {
            DB::table('activity_process')->insert($chunk);
            $links += count($chunk);
            $this->tick(count($chunk));
        }

        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), array_combine($ids, $rowProcessIds), $links];
    }

    /**
     * Entre 1 et 3 opérations par activité (`operations_per_activity`),
     * chacune dédiée à cette seule activité (pas partagée, lien
     * `activity_operation`) et rattachée au même processus que cette
     * activité (`operations.process_id`).
     *
     * @param  list<int>  $activityIds
     * @param  array<int,int>  $activityPerimeterById
     * @param  array<int,int>  $activityProcessById
     * @return array{0: list<int>, 1: array<int,int>, 2: int} ids, perimeter par id, liens créés
     */
    private function insertOperations(array $activityIds, array $activityPerimeterById, array $activityProcessById): array
    {
        if ($activityIds === []) {
            return [[], [], 0];
        }

        [$min, $max] = [$this->tuning['operations_per_activity']['min'], $this->tuning['operations_per_activity']['max']];
        $now = now();
        $rows = [];
        $assignments = [];
        $rowActivityIds = [];

        foreach ($activityIds as $activityId) {
            $perimeterId = $activityPerimeterById[$activityId];
            $count = random_int($min, $max);

            for ($i = 0; $i < $count; $i++) {
                $assignments[] = $perimeterId;
                $rowActivityIds[] = $activityId;

                $rows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                    'type' => fake()->randomElement(self::OPERATION_TYPES),
                    'description' => fake()->sentence(),
                    'process_id' => $activityProcessById[$activityId],
                    'attributes' => $this->randomAttributes(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Opérations', count($rows));
        $ids = $this->insertRows('operations', $rows);
        $this->phaseEnd();

        $pivotRows = [];

        foreach ($ids as $index => $operationId) {
            $pivotRows[] = ['activity_id' => $rowActivityIds[$index], 'operation_id' => $operationId];
        }

        $this->phaseStart('Liens activités <-> opérations', count($pivotRows));
        $links = 0;

        foreach (array_chunk($pivotRows, $this->chunkSize) as $chunk) {
            DB::table('activity_operation')->insert($chunk);
            $links += count($chunk);
            $this->tick(count($chunk));
        }

        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), $links];
    }

    /**
     * Entre 1 et 3 tâches par opération (`tasks_per_operation`), chacune
     * dédiée à cette seule opération (pas partagée), reliées via la table
     * pivot brute `operation_task`.
     *
     * @param  list<int>  $operationIds
     * @param  array<int,int>  $operationPerimeterById
     * @return array{0: list<int>, 1: array<int,int>, 2: int} ids, perimeter par id, liens créés
     */
    private function insertTasks(array $operationIds, array $operationPerimeterById): array
    {
        if ($operationIds === []) {
            return [[], [], 0];
        }

        [$min, $max] = [$this->tuning['tasks_per_operation']['min'], $this->tuning['tasks_per_operation']['max']];
        $now = now();
        $rows = [];
        $assignments = [];
        $rowOperationIds = [];

        foreach ($operationIds as $operationId) {
            $perimeterId = $operationPerimeterById[$operationId];
            $count = random_int($min, $max);

            for ($i = 0; $i < $count; $i++) {
                $assignments[] = $perimeterId;
                $rowOperationIds[] = $operationId;

                $rows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                    'type' => fake()->randomElement(self::TASK_TYPES),
                    'description' => fake()->sentence(),
                    'attributes' => $this->randomAttributes(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Tâches', count($rows));
        $ids = $this->insertRows('tasks', $rows);
        $this->phaseEnd();

        $pivotRows = [];

        foreach ($ids as $index => $taskId) {
            $pivotRows[] = ['operation_id' => $rowOperationIds[$index], 'task_id' => $taskId];
        }

        $this->phaseStart('Liens opérations <-> tâches', count($pivotRows));
        $links = 0;

        foreach (array_chunk($pivotRows, $this->chunkSize) as $chunk) {
            DB::table('operation_task')->insert($chunk);
            $links += count($chunk);
            $this->tick(count($chunk));
        }

        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments), $links];
    }

    /**
     * Entre 5 et 20 acteurs par périmètre (`actors_per_perimeter`) — tirage
     * direct, pas un compteur global réparti sur les périmètres.
     *
     * @param  list<int>  $perimeterIds
     * @return array{0: list<int>, 1: array<int,int>}
     */
    private function insertActors(array $perimeterIds): array
    {
        if ($perimeterIds === []) {
            return [[], []];
        }

        [$min, $max] = [$this->tuning['actors_per_perimeter']['min'], $this->tuning['actors_per_perimeter']['max']];
        $now = now();
        $rows = [];
        $assignments = [];

        foreach ($perimeterIds as $perimeterId) {
            $count = random_int($min, $max);

            for ($i = 0; $i < $count; $i++) {
                $assignments[] = $perimeterId;

                $rows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                    'nature' => fake()->randomElement(self::ACTOR_NATURES),
                    'type' => fake()->randomElement(self::ACTOR_TYPES),
                    'contact' => fake()->safeEmail(),
                    'attributes' => $this->randomAttributes(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Acteurs', count($rows));
        $ids = $this->insertRows('actors', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments)];
    }

    /**
     * Entre 5 et 20 informations par périmètre (`informations_per_perimeter`)
     * — tirage direct, pas un compteur global réparti sur les périmètres.
     * Aucune relation n'est créée avec les processus/bases de données.
     *
     * @param  list<int>  $perimeterIds
     * @return array{0: list<int>, 1: array<int,int>}
     */
    private function insertInformations(array $perimeterIds): array
    {
        if ($perimeterIds === []) {
            return [[], []];
        }

        [$min, $max] = [$this->tuning['informations_per_perimeter']['min'], $this->tuning['informations_per_perimeter']['max']];
        $now = now();
        $rows = [];
        $assignments = [];

        foreach ($perimeterIds as $perimeterId) {
            $count = random_int($min, $max);

            for ($i = 0; $i < $count; $i++) {
                $assignments[] = $perimeterId;

                [$min2, $max2] = $this->securityNeedRange();

                $rows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                    'type' => fake()->randomElement(self::INFORMATION_TYPES),
                    'description' => fake()->sentence(),
                    'owner' => fake()->name(),
                    'administrator' => fake()->name(),
                    'storage' => fake()->randomElement(['Local', 'Disque', 'Papier', 'Cloud']),
                    'security_need_c' => random_int($min2, $max2),
                    'security_need_i' => random_int($min2, $max2),
                    'security_need_a' => random_int($min2, $max2),
                    'security_need_t' => random_int($min2, $max2),
                    'security_need_auth' => random_int(0, 4),
                    'sensitivity' => fake()->randomElement(self::INFORMATION_SENSITIVITIES),
                    'constraints' => fake()->sentence(),
                    'retention_period' => fake()->randomElement(['6 mois', '1 an', '5 ans', '10 ans', 'Durée du contrat + 5 ans']),
                    'final_disposition' => fake()->randomElement(['Destruction', 'Tri', 'Versement aux archives']),
                    'retention_notes' => fake()->sentence(),
                    'attributes' => $this->randomAttributes(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Informations', count($rows));
        $ids = $this->insertRows('information', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments)];
    }

    /**
     * Une centaine d'entités par périmètre (`entities_per_perimeter`, un
     * compte fixe plutôt qu'une plage min/max — l'utilisateur a demandé "une
     * centaine", pas une fourchette) — tirage direct, pas un compteur global
     * réparti sur les périmètres.
     *
     * @param  list<int>  $perimeterIds
     * @return array{0: list<int>, 1: array<int,int>}
     */
    private function insertEntities(array $perimeterIds): array
    {
        if ($perimeterIds === []) {
            return [[], []];
        }

        $countPerPerimeter = $this->tuning['entities_per_perimeter'];
        $now = now();
        $rows = [];
        $assignments = [];

        foreach ($perimeterIds as $perimeterId) {
            for ($i = 0; $i < $countPerPerimeter; $i++) {
                $assignments[] = $perimeterId;

                $rows[] = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                    'type' => fake()->randomElement(self::ENTITY_TYPES),
                    'description' => fake()->sentence(),
                    'security_level' => fake()->randomElement(self::ENTITY_SECURITY_LEVELS),
                    'contact_point' => fake()->safeEmail(),
                    'reference' => fake()->regexify(FakerPatterns::REFERENCE_CODE),
                    'parent_entity_id' => null,
                    'attributes' => $this->randomAttributes(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->phaseStart('Entités', count($rows));
        $ids = $this->insertRows('entities', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments)];
    }

    /**
     * Entre 0 et 3 relations par entité (`relations_per_entity`), chacune
     * reliant deux entités du même périmètre (jamais la même entité des deux
     * côtés) — contrairement aux flux applicatifs, `Relation` n'a pas de
     * scope multi-périmètre : source et destination restent toujours dans le
     * périmètre de la relation. Une entité seule dans son périmètre (aucune
     * autre candidate) n'obtient aucune relation plutôt qu'une boucle sur
     * elle-même.
     *
     * @param  list<int>  $entityIds
     * @param  array<int,int>  $entityPerimeterById
     */
    private function insertRelations(array $entityIds, array $entityPerimeterById): int
    {
        if ($entityIds === []) {
            return 0;
        }

        [$min, $max] = [$this->tuning['relations_per_entity']['min'], $this->tuning['relations_per_entity']['max']];
        $entitiesByPerimeter = $this->groupBy($entityIds, $entityPerimeterById);
        $now = now();

        $this->phaseStart('Relations entre entités', count($entityIds));

        $rows = [];
        $total = 0;

        foreach ($entityIds as $sourceId) {
            $perimeterId = $entityPerimeterById[$sourceId];
            $pool = $entitiesByPerimeter[$perimeterId];

            if (count($pool) > 1) {
                $howMany = min(count($pool) - 1, random_int($min, $max));

                for ($i = 0; $i < $howMany; $i++) {
                    do {
                        $destinationId = $pool[array_rand($pool)];
                    } while ($destinationId === $sourceId);

                    [$min2, $max2] = $this->securityNeedRange();

                    $rows[] = [
                        'perimeter_id' => $perimeterId,
                        'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                        'type' => fake()->randomElement(self::RELATION_TYPES),
                        'description' => fake()->sentence(),
                        'reference' => fake()->regexify(FakerPatterns::REFERENCE_CODE),
                        'responsible' => fake()->name(),
                        'order_number' => fake()->regexify(FakerPatterns::REFERENCE_CODE),
                        'active' => fake()->boolean(80),
                        'start_date' => $now->copy()->subDays(random_int(30, 1800)),
                        'end_date' => null,
                        'comments' => fake()->sentence(),
                        'importance' => random_int(1, 4),
                        'security_need_c' => random_int($min2, $max2),
                        'security_need_i' => random_int($min2, $max2),
                        'security_need_a' => random_int($min2, $max2),
                        'security_need_t' => random_int($min2, $max2),
                        'security_need_auth' => random_int(0, 4),
                        'source_id' => $sourceId,
                        'destination_id' => $destinationId,
                        'attributes' => $this->randomAttributes(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if (count($rows) >= $this->chunkSize) {
                        DB::table('relations')->insert($rows);
                        $total += count($rows);
                        $rows = [];
                    }
                }
            }

            $this->tick();
        }

        if ($rows !== []) {
            DB::table('relations')->insert($rows);
            $total += count($rows);
        }

        $this->phaseEnd();

        return $total;
    }

    /**
     * Entre 20 et 50 traitements de données (`data_processing_per_perimeter`)
     * par périmètre — tirage direct, pas un compteur global réparti sur les
     * périmètres. Une seule base légale (RGPD art. 6) est tirée par
     * traitement, reflétée à la fois dans `legal_basis` et dans le
     * `lawfulness_*` correspondant (les cinq autres restant à `false`).
     *
     * @param  list<int>  $perimeterIds
     * @return array{0: list<int>, 1: array<int,int>}
     */
    private function insertDataProcessing(array $perimeterIds): array
    {
        if ($perimeterIds === []) {
            return [[], []];
        }

        [$min, $max] = [$this->tuning['data_processing_per_perimeter']['min'], $this->tuning['data_processing_per_perimeter']['max']];
        $now = now();
        $rows = [];
        $assignments = [];

        foreach ($perimeterIds as $perimeterId) {
            $count = random_int($min, $max);

            for ($i = 0; $i < $count; $i++) {
                $assignments[] = $perimeterId;

                $legalBasis = fake()->randomElement(array_keys(self::DATA_PROCESSING_LEGAL_BASES));
                $lawfulnessColumn = self::DATA_PROCESSING_LEGAL_BASES[$legalBasis];

                $row = [
                    'perimeter_id' => $perimeterId,
                    'name' => fake()->regexify(FakerPatterns::ORG_NAME),
                    'legal_basis' => $legalBasis,
                    'description' => fake()->sentence(),
                    'responsible' => fake()->name(),
                    'purpose' => fake()->sentence(),
                    'lawfulness' => $legalBasis,
                    'lawfulness_consent' => false,
                    'lawfulness_contract' => false,
                    'lawfulness_legal_obligation' => false,
                    'lawfulness_vital_interest' => false,
                    'lawfulness_public_interest' => false,
                    'lawfulness_legitimate_interest' => false,
                    'categories' => fake()->sentence(),
                    'data_source' => fake()->randomElement(['Collecte directe aupres de la personne concernee', 'Tiers ou partenaire', 'Source publique']),
                    'data_collection_obligation' => fake()->randomElement(['Obligatoire', 'Facultative']),
                    'recipients' => fake()->sentence(),
                    'transfert' => fake()->sentence(),
                    'automated_decision_making' => fake()->regexify(FakerPatterns::YES_NO_FR),
                    'retention' => fake()->sentence(),
                    'data_subject_rights' => fake()->sentence(),
                    'update_date' => $now->copy()->subDays(random_int(0, 365)),
                    'controls' => fake()->sentence(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $row[$lawfulnessColumn] = true;

                $rows[] = $row;
            }
        }

        $this->phaseStart('Traitements de données', count($rows));
        $ids = $this->insertRows('data_processing', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments)];
    }

    /**
     * @param  list<int>  $perimeterIds
     * @return array{0: list<int>, 1: array<int,int>}
     */
    private function insertSecurityZones(int $count, array $perimeterIds): array
    {
        if ($count === 0) {
            return [[], []];
        }

        $assignments = $this->distributePerimeters($count, $perimeterIds);
        $now = now();
        $rows = [];

        foreach ($assignments as $perimeterId) {
            $type = fake()->regexify(FakerPatterns::SECURITY_ZONE);

            $rows[] = [
                'perimeter_id' => $perimeterId,
                'name' => $type.'-'.random_int(1, 99),
                'type' => $type,
                'description' => fake()->sentence(),
                'attributes' => $this->randomAttributes(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->phaseStart('Zones de sécurité', count($rows));
        $ids = $this->insertRows('zones', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments)];
    }

    /**
     * Pioche un id au hasard dans le pool du périmètre donné, ou null si ce
     * périmètre n'a aucun candidat (l'objet enfant est alors créé sans
     * parent plutôt que d'échouer ou d'en emprunter un d'un autre périmètre).
     *
     * @param  array<int,list<int>>  $poolByPerimeter
     */
    private function pickParent(array $poolByPerimeter, int $perimeterId): ?int
    {
        $pool = $poolByPerimeter[$perimeterId] ?? [];

        return $pool === [] ? null : $pool[array_rand($pool)];
    }

    /**
     * Insère une ligne de pivot pour chaque paire déjà résolue (contrairement
     * à attachPivot(), qui pioche elle-même parmi plusieurs candidats) —
     * utilisé par exemple pour logical_server_physical_server, où chaque
     * serveur logique a déjà été apparié à au plus un serveur physique lors
     * de sa création. Une valeur null est ignorée (pas de ligne).
     *
     * @param  array<int,?int>  $rightIdByLeftId
     */
    private function insertDirectPivot(string $table, string $leftColumn, string $rightColumn, array $rightIdByLeftId, string $label): int
    {
        if ($rightIdByLeftId === []) {
            return 0;
        }

        $this->phaseStart($label, count($rightIdByLeftId));

        $rows = [];
        $total = 0;

        foreach ($rightIdByLeftId as $leftId => $rightId) {
            if ($rightId !== null) {
                $rows[] = [$leftColumn => $leftId, $rightColumn => $rightId];
                $total++;
            }

            if (count($rows) >= $this->chunkSize) {
                DB::table($table)->insert($rows);
                $rows = [];
            }

            $this->tick();
        }

        if ($rows !== []) {
            DB::table($table)->insert($rows);
        }

        $this->phaseEnd();

        return $total;
    }

    /**
     * Relie chaque application à un nombre aléatoire d'objets (serveurs ou bases
     * de données) pris dans le même périmètre qu'elle, via une table pivot brute.
     * $howManyPicker est appelé une fois par application pour déterminer ce
     * nombre (avant plafonnement par la taille du pool disponible) — une
     * simple plage min/max ou une distribution pondérée (voir
     * APPLICATION_SERVER_COUNTS) selon l'appelant.
     *
     * @param  list<int>  $appIds
     * @param  array<int,int>  $appPerimeterById
     * @param  array<int,list<int>>  $relatedByPerimeter
     * @param  Closure(): int  $howManyPicker
     */
    private function attachPivot(
        string $table,
        string $appColumn,
        string $relatedColumn,
        array $appIds,
        array $appPerimeterById,
        array $relatedByPerimeter,
        Closure $howManyPicker,
        string $label,
    ): int {
        if (empty($appIds) || empty($relatedByPerimeter)) {
            return 0;
        }

        $this->phaseStart($label, count($appIds));

        $rows = [];
        $total = 0;

        foreach ($appIds as $appId) {
            $pool = $relatedByPerimeter[$appPerimeterById[$appId]] ?? [];

            if ($pool !== []) {
                $howMany = min(count($pool), $howManyPicker());

                if ($howMany > 0) {
                    foreach ((array) array_rand(array_flip($pool), $howMany) as $relatedId) {
                        $rows[] = [$appColumn => $appId, $relatedColumn => $relatedId];
                    }
                }
            }

            if (count($rows) >= $this->chunkSize) {
                DB::table($table)->insert($rows);
                $total += count($rows);
                $rows = [];
            }

            $this->tick();
        }

        if ($rows !== []) {
            DB::table($table)->insert($rows);
            $total += count($rows);
        }

        $this->phaseEnd();

        return $total;
    }

    /**
     * Construit, pour chacun des quatre types d'objets pouvant servir
     * d'extrémité à un flux applicatif (application, service applicatif,
     * module applicatif, base de données), la liste plate de ses clés
     * "type:id" ainsi que le périmètre de chacune — utilisé par
     * insertFlows() pour piocher une extrémité indépendamment de son type.
     *
     * @param  list<int>  $appIds
     * @param  array<int,int>  $appPerimeterById
     * @param  list<int>  $serviceIds
     * @param  array<int,int>  $servicePerimeterById
     * @param  list<int>  $moduleIds
     * @param  array<int,int>  $modulePerimeterById
     * @param  list<int>  $dbIds
     * @param  array<int,int>  $dbPerimeterById
     * @return array{0: list<string>, 1: array<string,int>} clés plates, périmètre par clé
     */
    private function buildFlowEndpoints(
        array $appIds,
        array $appPerimeterById,
        array $serviceIds,
        array $servicePerimeterById,
        array $moduleIds,
        array $modulePerimeterById,
        array $dbIds,
        array $dbPerimeterById,
    ): array {
        $keys = [];
        $perimeterByKey = [];

        foreach ([
            ['application', $appIds, $appPerimeterById],
            ['service', $serviceIds, $servicePerimeterById],
            ['module', $moduleIds, $modulePerimeterById],
            ['database', $dbIds, $dbPerimeterById],
        ] as [$type, $ids, $perimeterById]) {
            foreach ($ids as $id) {
                $key = "{$type}:{$id}";
                $keys[] = $key;
                $perimeterByKey[$key] = $perimeterById[$id];
            }
        }

        return [$keys, $perimeterByKey];
    }

    /**
     * @param  list<string>  $endpointKeys  clés "type:id" (voir buildFlowEndpoints())
     * @param  array<string,int>  $endpointPerimeterByKey
     * @return array{0: list<int>, 1: array<int,int>}
     */
    private function insertFlows(int $count, array $endpointKeys, array $endpointPerimeterByKey): array
    {
        if ($count === 0 || $endpointKeys === []) {
            return [[], []];
        }

        $byPerimeter = $this->groupBy($endpointKeys, $endpointPerimeterByKey);
        $perimeters = array_keys($byPerimeter);
        $crossProbability = $this->tuning['cross_perimeter_flow_probability'];

        $now = now();
        $rows = [];
        $assignments = [];

        for ($i = 0; $i < $count; $i++) {
            $sourceKey = $endpointKeys[array_rand($endpointKeys)];
            $sourcePerimeter = $endpointPerimeterByKey[$sourceKey];
            $destKey = $this->pickFlowDestination($sourceKey, $sourcePerimeter, $endpointKeys, $byPerimeter, $perimeters, $crossProbability);
            $assignments[] = $sourcePerimeter;

            $row = [
                'perimeter_id' => $sourcePerimeter,
                'name' => fake()->words(2, true),
                'description' => fake()->sentence(),
                'type' => $this->pickWeighted(self::FLOW_TYPES),
                'attributes' => $this->randomAttributes(),
                'application_source_id' => null,
                'service_source_id' => null,
                'module_source_id' => null,
                'database_source_id' => null,
                'application_dest_id' => null,
                'service_dest_id' => null,
                'module_dest_id' => null,
                'database_dest_id' => null,
                'crypted' => fake()->boolean(70),
                'bidirectional' => fake()->boolean(20),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $this->setFlowEndpoint($row, $sourceKey, 'source');
            $this->setFlowEndpoint($row, $destKey, 'dest');

            $rows[] = $row;
        }

        $this->phaseStart('Flux applicatifs', count($rows));
        $ids = $this->insertRows('application_flows', $rows);
        $this->phaseEnd();

        return [$ids, array_combine($ids, $assignments)];
    }

    /**
     * @param  array<string,mixed>  $row
     */
    private function setFlowEndpoint(array &$row, string $endpointKey, string $direction): void
    {
        [$type, $id] = explode(':', $endpointKey, 2);
        $row[self::FLOW_ENDPOINT_COLUMNS[$type][$direction]] = (int) $id;
    }

    /**
     * @param  list<string>  $endpointKeys
     * @param  array<int,list<string>>  $byPerimeter
     * @param  list<int>  $perimeters
     */
    private function pickFlowDestination(string $sourceKey, int $sourcePerimeter, array $endpointKeys, array $byPerimeter, array $perimeters, float $crossProbability): string
    {
        $wantsCross = count($perimeters) > 1 && (mt_rand() / mt_getrandmax()) < $crossProbability;

        if ($wantsCross) {
            $otherPerimeters = array_values(array_diff($perimeters, [$sourcePerimeter]));
            $targetPool = $byPerimeter[$otherPerimeters[array_rand($otherPerimeters)]];

            return $targetPool[array_rand($targetPool)];
        }

        $pool = $byPerimeter[$sourcePerimeter];

        if (count($pool) > 1) {
            do {
                $destKey = $pool[array_rand($pool)];
            } while ($destKey === $sourceKey);

            return $destKey;
        }

        // Un seul candidat dans ce périmètre : on relie vers un autre périmètre
        // plutôt que de créer une boucle du flux sur sa propre extrémité.
        $others = array_values(array_diff($endpointKeys, [$sourceKey]));

        return $others[array_rand($others)];
    }

    /**
     * Regroupe une liste d'ids par une clé arbitraire (périmètre, site, ...)
     * lue dans $keyById. Générique sur le type des ids (`int` partout sauf
     * les clés "type:id" des extrémités de flux, voir buildFlowEndpoints()).
     *
     * @template TId of array-key
     *
     * @param  list<TId>  $ids
     * @param  array<TId,int>  $keyById
     * @return array<int,list<TId>>
     */
    private function groupBy(array $ids, array $keyById): array
    {
        $groups = [];

        foreach ($ids as $id) {
            $groups[$keyById[$id]][] = $id;
        }

        return $groups;
    }

    /**
     * Répartit $count objets sur les périmètres disponibles aussi
     * uniformément que possible.
     *
     * @param  list<int>  $perimeterIds
     * @return list<int>
     */
    private function distributePerimeters(int $count, array $perimeterIds): array
    {
        $n = count($perimeterIds);
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $result[] = $perimeterIds[$i % $n];
        }

        shuffle($result);

        return $result;
    }

    /**
     * Insère $rows par lots de $this->chunkSize et retourne la plage d'ids
     * générée, en supposant un auto-increment séquentiel sans écriture
     * concurrente (valable pour une commande artisan interactive).
     *
     * Le premier enregistrement est inséré via insertGetId() pour ancrer
     * $firstId sur l'id réellement attribué : max(id)+1 ne suffit pas, une
     * transaction précédente ayant échoué et été annulée (rollback) peut
     * avoir fait avancer le compteur AUTO_INCREMENT sans laisser de ligne
     * derrière elle (comportement InnoDB — les valeurs consommées ne sont
     * jamais réutilisées).
     *
     * @param  list<array<string,mixed>>  $rows
     * @return list<int>
     */
    private function insertRows(string $table, array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $chunks = array_chunk($rows, $this->chunkSize);
        $firstChunk = array_shift($chunks);
        $firstRow = array_shift($firstChunk);

        $firstId = (int) DB::table($table)->insertGetId($firstRow);
        $this->tick();

        if ($firstChunk !== []) {
            DB::table($table)->insert($firstChunk);
            $this->tick(count($firstChunk));
        }

        foreach ($chunks as $chunk) {
            DB::table($table)->insert($chunk);
            $this->tick(count($chunk));
        }

        return range($firstId, $firstId + count($rows) - 1);
    }

    /**
     * @param  array<array-key,int>  $weights
     */
    private function pickWeighted(array $weights): string
    {
        $roll = random_int(1, array_sum($weights));
        $cumulative = 0;

        foreach ($weights as $value => $weight) {
            $cumulative += $weight;

            if ($roll <= $cumulative) {
                return (string) $value;
            }
        }

        return (string) array_key_first($weights);
    }

    /**
     * @return array{0:int,1:int}
     */
    private function securityNeedRange(): array
    {
        return match ($this->pickWeighted(['C1' => 10, 'C2' => 30, 'C3' => 60])) {
            'C1' => [3, 4],
            'C2' => [2, 3],
            default => [1, 1],
        };
    }

    private function randomEnvironment(): string
    {
        return self::ENVIRONMENTS[array_rand(self::ENVIRONMENTS)];
    }

    /**
     * 3 à 5 étiquettes libres parmi ATTRIBUTE_TAGS, séparées par un espace
     * (même convention que le reste de l'application pour ce champ — voir
     * la migration move_is_external_to_attributes_on_entities, qui stocke
     * déjà ses tags via `implode(' ', $tags)`).
     */
    private function randomAttributes(): string
    {
        $tags = self::ATTRIBUTE_TAGS;
        shuffle($tags);

        return implode(' ', array_slice($tags, 0, random_int(3, 5)));
    }

    /**
     * Pioche une adresse IP d'hôte plausible à l'intérieur d'un sous-réseau
     * CIDR (ex. "172.16.42.0/24" -> "172.16.42.187") : ni l'adresse réseau
     * ni l'adresse de broadcast. FakerPatterns::SUBNET_CIDR ne génère que
     * des /24, mais cette méthode reste correcte pour tout préfixe.
     */
    private function randomIpInCidr(string $cidr): string
    {
        [$network, $prefix] = explode('/', $cidr);
        $prefix = (int) $prefix;

        $networkLong = ip2long($network);
        $hostBits = 32 - $prefix;
        $maxHostOffset = (1 << $hostBits) - 2;

        if ($networkLong === false || $maxHostOffset < 1) {
            return $network;
        }

        return long2ip($networkLong + random_int(1, $maxHostOffset));
    }

    private function applicationName(string $env): string
    {
        $company = self::COMPANY_WORDS[array_rand(self::COMPANY_WORDS)];
        $domain = self::DOMAIN_WORDS[array_rand(self::DOMAIN_WORDS)];

        return "{$company}-{$domain}-{$env}";
    }

    private function databaseName(string $env): string
    {
        $domain = self::DOMAIN_WORDS[array_rand(self::DOMAIN_WORDS)];

        return "db-{$domain}-{$env}";
    }

    private function serverHostname(string $env): string
    {
        $prefix = self::SERVER_PREFIXES[array_rand(self::SERVER_PREFIXES)];
        $number = str_pad((string) random_int(1, 99), 2, '0', STR_PAD_LEFT);

        return "{$prefix}{$number}-{$env}";
    }

    private function phaseStart(string $label, int $total): void
    {
        if ($this->onPhaseStart) {
            ($this->onPhaseStart)($label, $total);
        }
    }

    private function tick(int $advance = 1): void
    {
        if ($this->onTick) {
            ($this->onTick)($advance);
        }
    }

    private function phaseEnd(): void
    {
        if ($this->onPhaseEnd) {
            ($this->onPhaseEnd)();
        }
    }
}
