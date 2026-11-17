<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Actor;
use App\Models\AdminUser;
use App\Models\Annuaire;
use App\Models\Application;
use App\Models\ApplicationBlock;
use App\Models\ApplicationFlow;
use App\Models\ApplicationModule;
use App\Models\ApplicationService;
use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\Bay;
use App\Models\Building;
use App\Models\Certificate;
use App\Models\Cluster;
use App\Models\Container;
use App\Models\Database;
use App\Models\DataProcessing;
use App\Models\DhcpServer;
use App\Models\Dnsserver;
use App\Models\Document;
use App\Models\Domain;
use App\Models\Entity;
use App\Models\ExternalConnectedEntity;
use App\Models\ForestAd;
use App\Models\Gateway;
use App\Models\Graph;
use App\Models\Information;
use App\Models\Lan;
use App\Models\LogicalFlow;
use App\Models\LogicalServer;
use App\Models\MacroProcessus;
use App\Models\Man;
use App\Models\Network;
use App\Models\NetworkSwitch;
use App\Models\Operation;
use App\Models\Peripheral;
use App\Models\Phone;
use App\Models\PhysicalLink;
use App\Models\PhysicalRouter;
use App\Models\PhysicalSecurityDevice;
use App\Models\PhysicalServer;
use App\Models\PhysicalSwitch;
use App\Models\Process;
use App\Models\Relation;
use App\Models\Role;
use App\Models\Router;
use App\Models\SecurityControl;
use App\Models\SecurityDevice;
use App\Models\Site;
use App\Models\StorageDevice;
use App\Models\Subnetwork;
use App\Models\Task;
use App\Models\User;
use App\Models\Vlan;
use App\Models\Wan;
use App\Models\WifiTerminal;
use App\Models\Workstation;
use App\Models\Zone;
use App\Models\ZoneAdmin;
use Illuminate\Support\Str;
use ReflectionClass;

/**
 * Single source of truth for model metadata that used to be duplicated across
 * several hand-maintained maps (Cartographer, ShowLink, GlobalSearchController,
 * WordHelper, QueryEngineIntrospector): route names, title translation keys,
 * and the business whitelists/groupings built on top of them.
 *
 * slug()/routeName()/titleKey()/title() are pure derivations from the class
 * name — no per-model override table — except for the single documented
 * exception in ROUTE_SLUG_OVERRIDES (see its docblock). Everything else
 * (CARTOGRAPHY_MODELS, LINKABLE_MODELS) is explicit data because it encodes
 * a business decision, not something derivable from the class name.
 * Monarc-specific whitelists/groupings live on MonarcController and
 * MonarcExportService instead — they are the only consumers.
 */
final class ModelRegistry
{
    protected const string MODEL_NAMESPACE = 'App\\Models\\';

    /** Mirrors QueryEngineIntrospector's original exclusions. */
    private const array EXCLUDED_MODELS = ['User', 'PasswordReset'];

    /**
     * The sole exception to pure route-slug derivation: Str::plural(Str::snake('Man','-'))
     * derives 'men' (correct English pluralization), but the route actually registered
     * (and the one ShowLink's original hardcoded map already relied on) is 'admin.mans.show'.
     * Scoped to routeName() only — bare slug() stays a pure function, so callers that don't
     * go through admin.*.show (e.g. QueryEngineIntrospector::modelToApiName(), which already
     * produced 'men' for Man before this refactor) keep their exact prior output.
     */
    private const array ROUTE_SLUG_OVERRIDES = [
        'Man' => 'mans',
    ];

    /**
     * The ~52 models with a Cartographer-assignable route (ex-Cartographer::cartographiableRoutesMap()
     * keys). Drives AppServiceProvider's observer registration and the roles/users cartographer UI.
     */
    public const array CARTOGRAPHY_MODELS = [
        Activity::class,
        Actor::class,
        Annuaire::class,
        Application::class,
        ApplicationBlock::class,
        ApplicationFlow::class,
        ApplicationModule::class,
        ApplicationService::class,
        Backup::class,
        Bay::class,
        Building::class,
        Certificate::class,
        Cluster::class,
        Container::class,
        Database::class,
        DhcpServer::class,
        Dnsserver::class,
        Domain::class,
        Entity::class,
        ExternalConnectedEntity::class,
        ForestAd::class,
        Gateway::class,
        Information::class,
        Lan::class,
        LogicalFlow::class,
        LogicalServer::class,
        MacroProcessus::class,
        Man::class,
        Network::class,
        NetworkSwitch::class,
        Operation::class,
        Peripheral::class,
        Phone::class,
        PhysicalLink::class,
        PhysicalRouter::class,
        PhysicalSecurityDevice::class,
        PhysicalServer::class,
        PhysicalSwitch::class,
        Process::class,
        Relation::class,
        Router::class,
        SecurityDevice::class,
        Site::class,
        StorageDevice::class,
        Subnetwork::class,
        Task::class,
        Vlan::class,
        Wan::class,
        WifiTerminal::class,
        Workstation::class,
        Zone::class,
        ZoneAdmin::class,
    ];

    /**
     * The ~58 models ShowLink can render a show-link for (superset of CARTOGRAPHY_MODELS: adds
     * AdminUser, AuditLog, DataProcessing, Graph, Role, SecurityControl, User — models
     * with a show route but no cartographer assignment concept).
     */
    public const array LINKABLE_MODELS = [
        ...self::CARTOGRAPHY_MODELS,
        AdminUser::class,
        AuditLog::class,
        DataProcessing::class,
        Graph::class,
        Role::class,
        SecurityControl::class,
        User::class,
    ];

    /**
     * The models that use the Blameable trait (created_by / updated_by / deleted_by).
     * Must stay equal to the set of models using the Auditable trait.
     */
    public const array BLAMEABLE_MODELS = [
        Activity::class,
        Actor::class,
        AdminUser::class,
        Annuaire::class,
        Application::class,
        ApplicationBlock::class,
        ApplicationFlow::class,
        ApplicationModule::class,
        ApplicationService::class,
        Backup::class,
        Bay::class,
        Building::class,
        Certificate::class,
        Cluster::class,
        Container::class,
        DataProcessing::class,
        Database::class,
        DhcpServer::class,
        Dnsserver::class,
        Document::class,
        Domain::class,
        Entity::class,
        ExternalConnectedEntity::class,
        ForestAd::class,
        Gateway::class,
        Graph::class,
        Information::class,
        Lan::class,
        LogicalFlow::class,
        LogicalServer::class,
        MacroProcessus::class,
        Man::class,
        Network::class,
        NetworkSwitch::class,
        Operation::class,
        Peripheral::class,
        Phone::class,
        PhysicalLink::class,
        PhysicalRouter::class,
        PhysicalSecurityDevice::class,
        PhysicalServer::class,
        PhysicalSwitch::class,
        Process::class,
        Relation::class,
        Router::class,
        SecurityControl::class,
        SecurityDevice::class,
        Site::class,
        StorageDevice::class,
        Subnetwork::class,
        Task::class,
        Vlan::class,
        Wan::class,
        WifiTerminal::class,
        Workstation::class,
        Zone::class,
        ZoneAdmin::class,
    ];

    /**
     * The models scoped by périmètre: CARTOGRAPHY_MODELS plus the GDPR
     * models (DataProcessing, SecurityControl) that HomeController's maturity
     * counts also cover, and AdminUser. Drives the perimeter_id migration, the
     * HasPerimeter trait rollout, and Cartographer::scopedQuery()'s périmètre filter.
     */
    public const array PERIMETER_SCOPED_MODELS = [
        ...self::CARTOGRAPHY_MODELS,
        AdminUser::class,
        DataProcessing::class,
        SecurityControl::class,
    ];

    // ─── Derivation engine (pure functions) ────────────────────────────────

    /**
     * Route-segment slug for a model, e.g. LogicalServer -> logical-servers. Pure
     * Str::plural(Str::snake()) derivation, no overrides — see ROUTE_SLUG_OVERRIDES for the one
     * place (routeName()) where a model's real admin route diverges from this.
     */
    public static function slug(string $class): string
    {
        return Str::plural(Str::snake(class_basename($class), '-'));
    }

    /** Admin route name for a model's action, e.g. routeName(LogicalServer::class) -> 'admin.logical-servers.show'. */
    public static function routeName(string $class, string $action = 'show'): string
    {
        $short = class_basename($class);
        $slug = self::ROUTE_SLUG_OVERRIDES[$short] ?? self::slug($class);

        return "admin.{$slug}.{$action}";
    }

    /** Translation key for a model's CRUD title, e.g. LogicalServer -> 'cruds.logicalServer.title'. */
    public static function titleKey(string $class): string
    {
        return 'cruds.'.Str::camel(class_basename($class)).'.title';
    }

    /** Resolved, translated title for a model. */
    public static function title(string $class): string
    {
        return trans(self::titleKey($class));
    }

    // ─── Convenience constructors ───────────────────────────────────────────

    /**
     * @param  array<int, class-string>|null  $only  Defaults to CARTOGRAPHY_MODELS.
     * @return array<class-string, string> Model class => 'admin.x.show'.
     */
    public static function routesMap(?array $only = null): array
    {
        $classes = $only ?? self::CARTOGRAPHY_MODELS;

        $map = [];
        foreach ($classes as $class) {
            $map[$class] = self::routeName($class);
        }

        return $map;
    }

    /**
     * @param  array<int, class-string>|null  $only  Defaults to CARTOGRAPHY_MODELS.
     * @return array<class-string, string> Model class => translated title.
     */
    public static function titlesMap(?array $only = null): array
    {
        $classes = $only ?? self::CARTOGRAPHY_MODELS;

        $map = [];
        foreach ($classes as $class) {
            $map[$class] = self::title($class);
        }

        return $map;
    }

    /**
     * Same as titlesMap() but keyed by short class name — for consumers (GlobalSearchController,
     * MonarcExportService::familyLabel()) that index their own whitelist by short name rather
     * than FQCN.
     *
     * @param  array<int, string>  $shortNames
     * @return array<string, string> Short name => translated title.
     */
    public static function titlesMapByShortName(array $shortNames): array
    {
        $map = [];
        foreach ($shortNames as $short) {
            $map[$short] = self::title(self::MODEL_NAMESPACE.$short);
        }

        return $map;
    }

    // ─── Dynamic model discovery ─────────────────────────────────────────────

    /**
     * Every concrete model class under app/Models, excluding abstract classes and EXCLUDED_MODELS.
     * Ex-QueryEngineIntrospector::listModelClasses(), moved here so both the query engine and any
     * future consumer share one discovery routine.
     *
     * @return array<int, class-string>
     */
    public static function allConcreteModels(): array
    {
        $path = base_path('app/Models');
        $classes = [];

        foreach (glob("{$path}/*.php") as $file) {
            $modelName = basename($file, '.php');
            $class = self::MODEL_NAMESPACE.$modelName;

            if (! class_exists($class)) {
                continue;
            }
            if ((new ReflectionClass($class))->isAbstract()) {
                continue;
            }
            if (in_array($modelName, self::EXCLUDED_MODELS, true)) {
                continue;
            }

            $classes[] = $class;
        }

        return $classes;
    }
}
