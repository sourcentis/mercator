<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
use App\Models\Information;
use App\Models\Lan;
use App\Models\LogicalFlow;
use App\Models\LogicalServer;
use App\Models\MacroProcessus;
use App\Models\Man;
use App\Models\Network;
use App\Models\NetworkSwitch;
use App\Models\Operation;
use App\Models\Perimeter;
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
use App\Models\User;
use App\Models\Vlan;
use App\Models\Wan;
use App\Models\WifiTerminal;
use App\Models\Workstation;
use App\Models\Zone;
use App\Models\ZoneAdmin;
use App\Support\ModelRegistry;
use App\Support\PerimeterPermissions;
use App\Support\PerimeterSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PDO;
use Throwable;

class InfoController extends Controller
{
    /** Droits affichés pour chaque type d'objet, dans l'ordre des colonnes. */
    private const VERBS = ['access', 'create', 'edit', 'delete'];

    /**
     * Regroupement des objets cartographiés par vue (méthode ANSSI). Clé = clé de traduction
     * `cruds.menu.<clé>.title`. Les modèles de PERIMETER_SCOPED_MODELS absents d'ici tombent
     * dans un groupe « autres » (voir objectGroups()).
     */
    private const VIEW_GROUPS = [
        'gdpr' => [DataProcessing::class, SecurityControl::class],
        'ecosystem' => [Entity::class, Relation::class],
        'metier' => [
            MacroProcessus::class, Process::class, Activity::class, Operation::class,
            Task::class, Actor::class, Information::class,
        ],
        'application' => [
            ApplicationBlock::class, Application::class, ApplicationService::class,
            ApplicationModule::class, Database::class, ApplicationFlow::class,
        ],
        'administration' => [ZoneAdmin::class, Annuaire::class, ForestAd::class, Domain::class, AdminUser::class],
        'logical_infrastructure' => [
            Network::class, Subnetwork::class, Gateway::class, ExternalConnectedEntity::class,
            NetworkSwitch::class, Router::class, SecurityDevice::class, DhcpServer::class,
            Dnsserver::class, Cluster::class, LogicalServer::class, Container::class,
            LogicalFlow::class, Certificate::class, Vlan::class, Zone::class, Backup::class,
        ],
        'physical_infrastructure' => [
            Site::class, Building::class, Bay::class, PhysicalServer::class, Workstation::class,
            StorageDevice::class, Peripheral::class, Phone::class, PhysicalSwitch::class,
            PhysicalRouter::class, WifiTerminal::class, PhysicalSecurityDevice::class,
            PhysicalLink::class, Wan::class, Man::class, Lan::class,
        ],
    ];

    public function show(): View
    {
        /** @var User $user */
        $user = auth()->user();
        $roles = $user->roles()->orderBy('title')->pluck('title')->toArray();
        $isAdmin = $user->isAdmin();

        $cartographerTypes = [];
        if (session('is_cartographer')) {
            $perms = session('cartographer_permissions', []);
            $cartographerTypes = array_map(
                fn ($fqcn) => class_basename($fqcn),
                array_keys(array_filter($perms, fn ($ids) => ! empty($ids)))
            );
            sort($cartographerTypes);
        }

        $perimetersEnabled = PerimeterSettings::isEnabled();
        $userPerimeters = $perimetersEnabled
            ? Perimeter::query()->whereIn('id', $user->perimeterIds())->orderBy('name')->pluck('name', 'id')->toArray()
            : [];
        $perimeters = array_values($userPerimeters);

        $activePerimeterId = $user->activePerimeterId();
        $activePerimeter = $activePerimeterId === Perimeter::ALL_ID
            ? trans('panel.info.all_perimeters')
            : (Perimeter::query()->whereKey($activePerimeterId)->value('name') ?? '—');

        // Droits : par périmètre quand la fonctionnalité est active, sinon une seule colonne « globale ».
        $rightColumns = $perimetersEnabled
            ? $this->rightsByPerimeter($user, $userPerimeters)
            : [0 => [
                'name' => trans('panel.info.global'),
                'permissions' => session('auth_permissions') ?? collect($user->permissionsByPerimeter())->flatten()->unique()->values()->all(),
            ]];

        $objectGroups = $this->objectGroups($rightColumns);

        $perimeterSummary = $perimetersEnabled
            ? $this->perimeterSummary($user, $userPerimeters, $rightColumns, $isAdmin)
            : [];

        $memLimitRaw = ini_get('memory_limit');
        $memUsedBytes = memory_get_usage(true);
        $memLimitBytes = $this->parseMemoryLimit($memLimitRaw);

        $memUsedMb = round($memUsedBytes / 1048576, 1);
        $memFreeMb = $memLimitBytes > 0 ? round(($memLimitBytes - $memUsedBytes) / 1048576, 1) : null;

        return view('doc.info', [
            'mercatorVersion' => app('mercator.version'),
            'appEnv' => config('app.env'),
            'appTimezone' => config('app.timezone'),
            'appLocale' => config('app.locale'),
            'dbDriver' => config('database.default'),
            'dbVersion' => $this->databaseVersion(),
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'authMethods' => $this->authMethods(),
            'cacheDriver' => config('cache.default'),
            'queueDriver' => config('queue.default'),
            'mailer' => config('mail.default'),
            'memLimit' => $memLimitRaw,
            'memUsedMb' => $memUsedMb,
            'memFreeMb' => $memFreeMb,
            'cspMode' => (string) config('mercator.csp.mode', 'enforce'),
            'userName' => $user->name,
            'userEmail' => $user->email,
            'userLogin' => $user->login,
            'userLanguage' => $user->language,
            'userGranularity' => $user->granularity,
            'isAdmin' => $isAdmin,
            'roles' => $roles,
            'isCartographer' => session('is_cartographer', false),
            'cartographerTypes' => $cartographerTypes,
            'perimetersEnabled' => $perimetersEnabled,
            'perimeters' => $perimeters,
            'activePerimeter' => $activePerimeter,
            'perimeterSummary' => $perimeterSummary,
            'rightColumns' => $rightColumns,
            'objectGroups' => $objectGroups,
            'objectTotal' => array_sum(array_column($objectGroups, 'count')),
            'verbs' => self::VERBS,
        ]);
    }

    /**
     * @param  array<int, string>  $userPerimeters  id => nom
     * @return array<int, array{name: string, permissions: list<string>}>
     */
    private function rightsByPerimeter(User $user, array $userPerimeters): array
    {
        $byPerimeter = PerimeterPermissions::for($user);

        $columns = [];
        foreach ($userPerimeters as $id => $name) {
            $columns[$id] = [
                'name' => $name,
                'permissions' => $byPerimeter[$id] ?? [],
            ];
        }

        return $columns;
    }

    /**
     * Objets cartographiés visibles par l'utilisateur (même règle que la page d'accueil),
     * regroupés par vue, avec ses droits sur chaque type dans chaque colonne de droits.
     *
     * @param  array<int, array{name: string, permissions: list<string>}>  $rightColumns
     * @return list<array{key: string, title: string, count: int, models: list<array<string, mixed>>}>
     */
    private function objectGroups(array $rightColumns): array
    {
        $groups = self::VIEW_GROUPS;
        $grouped = array_merge(...array_values($groups));
        $others = array_values(array_diff(ModelRegistry::PERIMETER_SCOPED_MODELS, $grouped));
        if ($others !== []) {
            $groups['others'] = $others;
        }

        $result = [];
        foreach ($groups as $key => $classes) {
            $models = [];
            foreach ($classes as $class) {
                $snake = Str::snake(class_basename($class));
                $rights = [];
                foreach ($rightColumns as $columnId => $column) {
                    foreach (self::VERBS as $verb) {
                        $rights[$columnId][$verb] = in_array($snake.'_'.$verb, $column['permissions'], true);
                    }
                }

                $indexRoute = ModelRegistry::routeName($class, 'index');
                $models[] = [
                    'title' => ModelRegistry::title($class),
                    'count' => Cartographer::scopedQueryByClass($class)->count(),
                    'url' => Route::has($indexRoute) ? route($indexRoute) : null,
                    'rights' => $rights,
                ];
            }

            $result[] = [
                'key' => $key,
                'title' => $key === 'others' ? trans('panel.info.others') : trans('cruds.menu.'.$key.'.title_short'),
                'count' => array_sum(array_column($models, 'count')),
                'models' => $models,
            ];
        }

        return $result;
    }

    /**
     * Par périmètre de l'utilisateur : ses rôles, le nombre d'objets qu'il peut y consulter et
     * le nombre de types d'objets qu'il peut y lire / créer / modifier / supprimer.
     *
     * @param  array<int, string>  $userPerimeters
     * @param  array<int, array{name: string, permissions: list<string>}>  $rightColumns
     * @return list<array<string, mixed>>
     */
    private function perimeterSummary(User $user, array $userPerimeters, array $rightColumns, bool $isAdmin): array
    {
        $rolesByPerimeter = $user->roles()->orderBy('title')->get(['roles.id', 'title', 'perimeter_id'])
            ->groupBy('perimeter_id');

        // Une requête groupée par type d'objet, tous périmètres de l'utilisateur confondus.
        $countsByModel = [];
        foreach (ModelRegistry::PERIMETER_SCOPED_MODELS as $class) {
            $model = new $class;
            $countsByModel[$class] = $class::query()
                ->withoutGlobalScopes()
                ->whereNull($model->getQualifiedDeletedAtColumn())
                ->whereIn($model->qualifyColumn('perimeter_id'), array_keys($userPerimeters))
                ->groupBy($model->qualifyColumn('perimeter_id'))
                ->pluck(DB::raw('count(*)'), $model->qualifyColumn('perimeter_id'))
                ->all();
        }

        $activeId = $user->activePerimeterId();
        $summary = [];
        foreach ($userPerimeters as $id => $name) {
            $permissions = $rightColumns[$id]['permissions'] ?? [];

            $objects = 0;
            $typeCounts = array_fill_keys(self::VERBS, 0);
            foreach (ModelRegistry::PERIMETER_SCOPED_MODELS as $class) {
                $snake = Str::snake(class_basename($class));
                $canRead = $isAdmin || in_array($snake.'_access', $permissions, true);
                if ($canRead) {
                    $objects += (int) ($countsByModel[$class][$id] ?? 0);
                }
                foreach (self::VERBS as $verb) {
                    if ($isAdmin || in_array($snake.'_'.$verb, $permissions, true)) {
                        $typeCounts[$verb]++;
                    }
                }
            }

            $summary[] = [
                'name' => $name,
                'active' => $id === $activeId,
                'roles' => ($rolesByPerimeter[$id] ?? collect())->pluck('title')->all(),
                'objects' => $objects,
                'types' => $typeCounts,
            ];
        }

        return $summary;
    }

    /** @return list<string> */
    private function authMethods(): array
    {
        $methods = [];
        if (! config('app.ldap_enabled') || config('app.ldap_fallback_local')) {
            $methods[] = 'Local';
        }
        if (config('app.ldap_enabled')) {
            $methods[] = 'LDAP';
        }
        if (config('services.keycloak.enabled')) {
            $methods[] = 'Keycloak';
        }

        return $methods;
    }

    private function databaseVersion(): ?string
    {
        try {
            return (string) DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
        } catch (Throwable) {
            return null;
        }
    }

    private function parseMemoryLimit(string $val): int
    {
        $unit = strtoupper(substr(trim($val), -1));
        $num = (int) $val;

        return match ($unit) {
            'G' => $num * 1073741824,
            'M' => $num * 1048576,
            'K' => $num * 1024,
            default => $num,
        };
    }
}
