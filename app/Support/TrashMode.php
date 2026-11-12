<?php

namespace App\Support;

use App\Http\Controllers\BPMNController;
use App\Models\Activity;
use App\Models\Actor;
use App\Models\AdminUser;
use App\Models\Annuaire;
use App\Models\Application;
use App\Models\ApplicationBlock;
use App\Models\ApplicationFlow;
use App\Models\ApplicationModule;
use App\Models\ApplicationService;
use App\Models\Backup;
use App\Models\Bay;
use App\Models\Building;
use App\Models\Cartographer;
use App\Models\Certificate;
use App\Models\Cluster;
use App\Models\Container;
use App\Models\Database;
use App\Models\DataProcessing;
use App\Models\DhcpServer;
use App\Models\Dnsserver;
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
use App\Models\Router;
use App\Models\SecurityControl;
use App\Models\SecurityDevice;
use App\Models\Site;
use App\Models\StorageDevice;
use App\Models\Subnetwork;
use App\Models\Task;
use App\Models\Vlan;
use App\Models\Wan;
use App\Models\WifiTerminal;
use App\Models\Workstation;
use App\Models\Zone;
use App\Models\ZoneAdmin;
use App\Services\DatabaseCleanupService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Mode corbeille : exploration des objets supprimés (soft delete).
 *
 * Lorsque le mode est actif (drapeau en session), les listes des objets de la cartographie
 * n'affichent que les objets supprimés, et les fiches des objets supprimés sont consultables.
 */
class TrashMode
{
    public const SESSION_KEY = 'trash_mode';

    /** @var array<string, class-string<Model>>|null */
    private static ?array $models = null;

    /**
     * Vues du menu et types d'objets qu'elles contiennent, dans l'ordre et avec les libellés
     * de la barre latérale (resources/views/partials/sidebar.blade.php), puis les graphes du
     * menu Outils de la barre de navigation. À tenir à jour avec ces menus.
     *
     * @var array<string, array<class-string<Model>, string>> clé de traduction de la vue
     *                                                        => [modèle => clé de traduction du libellé]
     */
    public const MENU = [
        'cruds.menu.gdpr.title' => [
            DataProcessing::class => 'cruds.dataProcessing.title',
            SecurityControl::class => 'cruds.securityControl.title',
        ],
        'cruds.menu.ecosystem.title' => [
            Entity::class => 'cruds.entity.title',
            Relation::class => 'cruds.relation.title',
        ],
        'cruds.menu.metier.title' => [
            MacroProcessus::class => 'cruds.macroProcessus.title',
            Process::class => 'cruds.process.title',
            Activity::class => 'cruds.activity.title',
            Operation::class => 'cruds.operation.title',
            Task::class => 'cruds.task.title',
            Actor::class => 'cruds.actor.title',
            Information::class => 'cruds.information.title',
        ],
        'cruds.menu.application.title' => [
            ApplicationBlock::class => 'cruds.applicationBlock.title',
            Application::class => 'cruds.application.title',
            ApplicationService::class => 'cruds.applicationService.title',
            ApplicationModule::class => 'cruds.applicationModule.title',
            Database::class => 'cruds.database.title',
            ApplicationFlow::class => 'cruds.applicationFlow.title',
        ],
        'cruds.administration.title' => [
            ZoneAdmin::class => 'cruds.zoneAdmin.title',
            Annuaire::class => 'cruds.annuaire.title',
            ForestAd::class => 'cruds.forestAd.title',
            Domain::class => 'cruds.domain.title',
            AdminUser::class => 'cruds.adminUser.title',
        ],
        'cruds.menu.logical_infrastructure.title' => [
            Network::class => 'cruds.network.title',
            Subnetwork::class => 'cruds.subnetwork.title',
            Gateway::class => 'cruds.gateway.title_short',
            ExternalConnectedEntity::class => 'cruds.externalConnectedEntity.title',
            Router::class => 'cruds.router.title',
            NetworkSwitch::class => 'cruds.networkSwitch.title',
            SecurityDevice::class => 'cruds.securityDevice.title',
            DhcpServer::class => 'cruds.dhcpServer.title',
            Dnsserver::class => 'cruds.dnsserver.title',
            Cluster::class => 'cruds.cluster.title_short',
            LogicalServer::class => 'cruds.logicalServer.title',
            Backup::class => 'cruds.backup.title',
            Container::class => 'cruds.container.title',
            LogicalFlow::class => 'cruds.logicalFlow.title_short',
            Vlan::class => 'cruds.vlan.title_short',
            Certificate::class => 'cruds.certificate.title',
        ],
        'cruds.menu.physical_infrastructure.title' => [
            Site::class => 'cruds.site.title',
            Building::class => 'cruds.building.title',
            Bay::class => 'cruds.bay.title',
            Zone::class => 'cruds.zone.title',
            PhysicalServer::class => 'cruds.physicalServer.title',
            Workstation::class => 'cruds.workstation.title',
            StorageDevice::class => 'cruds.storageDevice.title',
            Peripheral::class => 'cruds.peripheral.title',
            Phone::class => 'cruds.phone.title',
            PhysicalSwitch::class => 'cruds.physicalSwitch.title',
            PhysicalRouter::class => 'cruds.physicalRouter.title',
            WifiTerminal::class => 'cruds.wifiTerminal.title',
            PhysicalSecurityDevice::class => 'cruds.physicalSecurityDevice.title',
            PhysicalLink::class => 'cruds.physicalLink.title',
            Wan::class => 'cruds.wan.title',
            Man::class => 'cruds.man.title',
            Lan::class => 'cruds.lan.title',
        ],
        'cruds.menu.configuration.title' => [
            Cartographer::class => 'cruds.cartographer.title',
        ],
        'panel.menu.tools' => [
            Graph::class => 'cruds.graph.title',
        ],
    ];

    /**
     * Contrôleurs hors du namespace Admin dont la liste et les fiches relèvent aussi du mode
     * corbeille. Leur liste n'est pas la liste « canonique » du modèle (voir indexRoutes()).
     *
     * @var array<class-string, class-string<Model>>
     */
    public const EXTRA_CONTROLLERS = [
        BPMNController::class => Graph::class,
    ];

    /**
     * Modèles affichés dans plusieurs listes : une ligne par liste dans la vue d'ensemble de la
     * corbeille, chacune avec son filtre. Les graphes sont des cartes (class 1, liste des cartes)
     * ou des BPMN (class 2, liste des BPMN).
     *
     * @var array<class-string<Model>, list<array{label: string, route: string, where: array<string, mixed>}>>
     */
    public const SPLIT_LISTS = [
        Graph::class => [
            ['label' => 'cruds.graph.title', 'route' => 'admin.graphs.index', 'where' => ['class' => 1]],
            ['label' => 'cruds.bpmn.title', 'route' => 'admin.bpmn.index', 'where' => ['class' => 2]],
        ],
    ];

    /**
     * Modèles dont le nom n'est pas unique (ni en base ni dans les formulaires) : la restauration
     * n'a pas à refuser un objet dont le nom est déjà porté par un objet actif.
     *
     * @var list<class-string<Model>>
     */
    public const NON_UNIQUE_NAMES = [
        Graph::class,
    ];

    public static function active(): bool
    {
        return (bool) session(self::SESSION_KEY, false);
    }

    /**
     * Modèles explorables, indexés par leur clé d'URL (ex. `application-flow`).
     * Mêmes modèles que la purge des objets supprimés (utilisateurs et rôles exclus).
     *
     * @return array<string, class-string<Model>>
     */
    public static function models(): array
    {
        if (self::$models === null) {
            self::$models = [];
            foreach (app(DatabaseCleanupService::class)->softDeletableModels() as $model) {
                self::$models[self::key($model::class)] = $model::class;
            }
            ksort(self::$models);
        }

        return self::$models;
    }

    /** @param class-string<Model> $class */
    public static function key(string $class): string
    {
        return Str::kebab(class_basename($class));
    }

    /** @return class-string<Model>|null */
    public static function modelForKey(string $key): ?string
    {
        return self::models()[$key] ?? null;
    }

    /**
     * Modèle géré par un contrôleur Admin (ex. ApplicationController → Application).
     *
     * @return class-string<Model>|null
     */
    public static function modelForController(?string $controller): ?string
    {
        if ($controller !== null && isset(self::EXTRA_CONTROLLERS[$controller])) {
            return self::EXTRA_CONTROLLERS[$controller];
        }

        if ($controller === null || ! str_starts_with($controller, 'App\\Http\\Controllers\\Admin\\')) {
            return null;
        }

        $basename = Str::beforeLast(class_basename($controller), 'Controller');

        return self::modelForKey(Str::kebab($basename));
    }

    /**
     * Route de la liste de chaque modèle explorable.
     *
     * @return array<class-string<Model>, string>
     */
    public static function indexRoutes(): array
    {
        $routes = [];
        foreach (Route::getRoutes()->getRoutes() as $route) {
            $name = (string) $route->getName();
            if (! str_starts_with($name, 'admin.') || ! str_ends_with($name, '.index')) {
                continue;
            }
            // Les listes secondaires (EXTRA_CONTROLLERS) ne remplacent pas la liste du modèle.
            if (isset(self::EXTRA_CONTROLLERS[$route->getControllerClass()])) {
                continue;
            }
            $model = self::modelForController($route->getControllerClass());
            if ($model !== null) {
                $routes[$model] = $name;
            }
        }

        return $routes;
    }

    /**
     * Listes d'un modèle pour la vue d'ensemble de la corbeille : libellé, route et filtre.
     *
     * @param  class-string<Model>  $class
     * @param  array<class-string<Model>, string>  $indexRoutes  résultat de indexRoutes()
     * @return list<array{label: string, route: string, where: array<string, mixed>}>
     */
    public static function lists(string $class, string $labelKey, array $indexRoutes): array
    {
        if (isset(self::SPLIT_LISTS[$class])) {
            return array_values(array_filter(self::SPLIT_LISTS[$class], fn (array $list) => Route::has($list['route'])));
        }

        if (! isset($indexRoutes[$class])) {
            return [];
        }

        return [['label' => $labelKey, 'route' => $indexRoutes[$class], 'where' => []]];
    }

    /** Préfixe des permissions du modèle (ex. `application_flow`). */
    public static function permissionPrefix(string $class): string
    {
        return Str::snake(class_basename($class));
    }

    /**
     * Remplace, pour la durée d'une requête, le scope SoftDeletes du modèle par un scope
     * qui ne retient que les objets supprimés. Le scope ne concerne que cette classe :
     * les relations vers d'autres modèles restent filtrées normalement.
     *
     * @param  class-string<Model>  $class
     * @return \Closure Restaure le scope d'origine.
     */
    public static function scopeToTrashed(string $class): \Closure
    {
        $swap = function (string $class, ?SoftDeletingScope $scope): ?SoftDeletingScope {
            // Closure liée à Model : accès à la propriété protégée Model::$globalScopes.
            /** @phpstan-ignore staticProperty.notFound */
            $previous = static::$globalScopes[$class][SoftDeletingScope::class] ?? null;
            /** @phpstan-ignore staticProperty.notFound */
            static::$globalScopes[$class][SoftDeletingScope::class] = $scope;

            return $previous;
        };
        $swap = \Closure::bind($swap, null, Model::class);

        // Le modèle doit être booté AVANT l'échange : son premier boot (première instanciation dans
        // la requête) réinstalle le SoftDeletingScope et écraserait le scope de la corbeille.
        // Les modèles des périmètres sont bootés dès le démarrage (observers), mais pas tous (Graph).
        new $class;

        $previous = $swap($class, new OnlyTrashedScope);

        return fn () => $swap($class, $previous ?? new SoftDeletingScope);
    }
}
