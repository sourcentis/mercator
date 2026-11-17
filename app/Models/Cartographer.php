<?php

namespace App\Models;

use App\Support\ModelRegistry;
use App\Support\PerimeterPermissions;
use App\Support\PerimeterSettings;
use App\Traits\HasPerimeter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class Cartographer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cartographiable_type',
        'cartographiable_id',
        'user_id',
        'role_id',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::put('cartographers_last_update', now()->timestamp));
        static::deleted(fn () => Cache::put('cartographers_last_update', now()->timestamp));
    }

    public static function canAccess(string $class): bool
    {
        $permission = Str::snake(class_basename($class)).'_access';
        if (Gate::allows($permission)) {
            return true;
        }

        return array_key_exists($class, session('cartographer_permissions', []));
    }

    public static function canAccessAny(array $classes): bool
    {
        foreach ($classes as $class) {
            if (static::canAccess($class)) {
                return true;
            }
        }

        return false;
    }

    public static function canAccessAll(array $classes): bool
    {
        foreach ($classes as $class) {
            if (! static::canAccess($class)) {
                return false;
            }
        }

        return true;
    }

    public function cartographiable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Role, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** @return array<class-string<Model>, string> */
    public static function cartographiableRoutesMap(): array
    {
        return ModelRegistry::routesMap(ModelRegistry::CARTOGRAPHY_MODELS);
    }

    /** @return array<class-string<Model>, string> */
    public static function cartographiableModelsList(): array
    {
        return ModelRegistry::titlesMap(ModelRegistry::CARTOGRAPHY_MODELS);
    }

    // ─── Détection contexte API (pas de session web initialisée) ─────────────

    /** @var array<int, list<int>> */
    private static array $roleIdsCache = [];

    /** @var array<int, list<string>> */
    private static array $dbPermissionsCache = [];

    /** @var array<int, array<string, list<int>>> */
    private static array $cartographerCache = [];

    private static function hasWebSession(): bool
    {
        return session()->has('cartographer_permissions_at');
    }

    // ─── Cache par requête sur le User (évite les N+1 en contexte API) ───────

    private static function getRoleIds(User $user): array
    {
        $key = (int) $user->getKey();
        if (! array_key_exists($key, self::$roleIdsCache)) {
            self::$roleIdsCache[$key] = $user->roles()->pluck('id')->toArray();
        }

        return self::$roleIdsCache[$key];
    }

    /**
     * Vérifie si l'utilisateur a la permission via ses rôles, sans passer par Gate.
     * Gate::allows() retournerait true pour les cartographes (règle 2b de AuthServiceProvider),
     * ce qui ferait croire à une permission complète alors qu'il faut filtrer par IDs.
     */
    private static function userHasRolePermission(User $user, string $permission): bool
    {
        // Ne se fier à la session que si elle porte réellement les permissions de rôle :
        // `cartographer_permissions_at` seul (session ouverte sans passer par le login web)
        // ne suffit pas, sinon toutes les listes reviendraient vides (#2754).
        if (session()->has('auth_permissions')) {
            return in_array($permission, session('auth_permissions', []), true);
        }
        // Contexte API : requête DB (cachée par requête)
        $key = (int) $user->getKey();
        if (! array_key_exists($key, self::$dbPermissionsCache)) {
            $roleIds = self::getRoleIds($user);
            self::$dbPermissionsCache[$key] = empty($roleIds) ? [] :
                Role::whereIn('id', $roleIds)
                    ->with('permissions')
                    ->get()
                    ->flatMap->permissions
                    ->pluck('title')
                    ->unique()
                    ->toArray();
        }

        return in_array($permission, self::$dbPermissionsCache[$key], true);
    }

    /**
     * Charge (et met en cache) toutes les entrées cartographe depuis la base,
     * groupées par type de modèle. Utilisé uniquement en contexte API (pas de session).
     */
    private static function loadCartographerCache(User $user): array
    {
        $key = (int) $user->getKey();
        if (! array_key_exists($key, self::$cartographerCache)) {
            $roleIds = self::getRoleIds($user);
            self::$cartographerCache[$key] = static::where(function ($q) use ($user, $roleIds) {
                $q->where('user_id', $user->id);
                if (! empty($roleIds)) {
                    $q->orWhereIn('role_id', $roleIds);
                }
            })
                ->get(['cartographiable_type', 'cartographiable_id'])
                ->groupBy('cartographiable_type')
                ->map(fn ($rows) => $rows->pluck('cartographiable_id')->unique()->values()->toArray())
                ->toArray();
        }

        return self::$cartographerCache[$key];
    }

    // ─── API publique ──────────────────────────────────────────────────────────

    public static function isAllowed(User $user, Model $object): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (self::hasWebSession()) {
            $ids = session('cartographer_permissions.'.get_class($object), []);

            return in_array($object->getKey(), $ids);
        }

        // Contexte API : requête DB (cachée sur $user)
        $cache = self::loadCartographerCache($user);
        $ids = $cache[get_class($object)] ?? [];

        return in_array($object->getKey(), $ids);
    }

    public static function allowedIdsFor(User $user, string $modelClass): array
    {
        if ($user->isAdmin()) {
            return [];
        }

        if (self::hasWebSession()) {
            return session('cartographer_permissions.'.$modelClass, []);
        }

        // Contexte API : requête DB (cachée sur $user)
        $cache = self::loadCartographerCache($user);

        return $cache[$modelClass] ?? [];
    }

    /**
     * Applique la restriction cartographe sur un builder déjà typé.
     *
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<T>  $query
     * @return Builder<T>
     */
    public static function scopedQuery(Builder $query): Builder
    {
        $class = get_class($query->getModel());
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('0 = 1');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        // Périmètres activés : chaque rôle n'ouvre l'accès que dans son propre périmètre.
        if (PerimeterSettings::isEnabled() && in_array(HasPerimeter::class, class_uses_recursive($class), true)) {
            return self::perimeterScopedQuery($query, $user, $class, Str::snake(class_basename($class)).'_access');
        }

        // Accès complet si l'utilisateur a la permission via son rôle.
        // On passe par userHasRolePermission() et non Gate::allows() car AuthServiceProvider
        // accorde _access via Gate aux cartographes (règle 2b), ce qui fausserait le filtre.
        $permission = Str::snake(class_basename($class)).'_access';
        if (self::userHasRolePermission($user, $permission)) {
            return $query;
        }

        // Cartographer assignments take precedence: if any exist, restrict to them.
        $ids = self::allowedIdsFor($user, $class);
        if (! empty($ids)) {
            $table = $query->getModel()->getTable();

            return $query->whereIn("{$table}.id", $ids);
        }

        return $query->whereRaw('0 = 1');
    }

    /**
     * Objets des périmètres où l'utilisateur a la permission `_access` (par le rôle de CE
     * périmètre), plus ceux dont il est cartographe, quel que soit leur périmètre.
     *
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<T>  $query
     * @param  class-string<T>  $class
     * @return Builder<T>
     */
    private static function perimeterScopedQuery(Builder $query, User $user, string $class, string $permission): Builder
    {
        $perimeterIds = PerimeterPermissions::perimeterIdsWith($user, $permission);
        $ids = self::allowedIdsFor($user, $class);

        if ($perimeterIds === [] && $ids === []) {
            return $query->whereRaw('0 = 1');
        }

        $table = $query->getModel()->getTable();

        return $query->where(function (Builder $q) use ($class, $perimeterIds, $ids, $table) {
            if ($perimeterIds !== [] && method_exists($class, 'constrainToPerimeters')) {
                $class::constrainToPerimeters($q, $perimeterIds);
            }
            if ($ids !== []) {
                $q->orWhereIn("{$table}.id", $ids);
            }
        });
    }

    /**
     * Variante pour les appelants avec un nom de classe dynamique (chaîne).
     * Ne promet pas de type générique — utiliser scopedQuery(X::query()) si possible.
     */
    public static function scopedQueryByClass(string $class): Builder
    {
        return self::scopedQuery((new $class)->newQuery());
    }

    public static function hasAnyFor(User $user, string $modelClass): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (self::hasWebSession()) {
            return ! empty(session('cartographer_permissions.'.$modelClass, []));
        }

        // Contexte API
        $cache = self::loadCartographerCache($user);

        return ! empty($cache[$modelClass] ?? []);
    }

    public static function loadSessionFor(User $user): void
    {
        $roleIds = $user->roles()->pluck('id')->toArray();

        $hasAssignments = static::where(function ($q) use ($user, $roleIds) {
            $q->where('user_id', $user->id);
            if (! empty($roleIds)) {
                $q->orWhereIn('role_id', $roleIds);
            }
        })->exists();

        if ($user->isAdmin()) {
            session([
                'cartographer_permissions' => [],
                'cartographer_permissions_at' => now()->timestamp,
                'is_cartographer' => $hasAssignments,
            ]);

            return;
        }

        $permissions = static::where(function ($q) use ($user, $roleIds) {
            $q->where('user_id', $user->id);
            if (! empty($roleIds)) {
                $q->orWhereIn('role_id', $roleIds);
            }
        })
            ->get(['cartographiable_type', 'cartographiable_id'])
            ->groupBy('cartographiable_type')
            ->map(fn ($rows) => $rows->pluck('cartographiable_id')->unique()->values()->toArray())
            ->toArray();

        session([
            'cartographer_permissions' => $permissions,
            'cartographer_permissions_at' => now()->timestamp,
            'is_cartographer' => $hasAssignments,
        ]);
    }
}
