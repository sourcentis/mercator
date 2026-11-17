<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Factories\UserFactory;
use App\Traits\HasIcon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements HasIconContract, OAuthenticatable
{
    use HasApiTokens, HasFactory, HasIcon, Notifiable, SoftDeletes;

    protected $table = 'users';

    public static string $icon = '/images/actor.png';

    protected $hidden = [
        'remember_token',
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'login',
        'name',
        'email',
        'password',
        'granularity',
        'language',
        'flow_label',
    ];

    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }

    /**
     * Mutator mot de passe : hash seulement si nécessaire.
     */
    protected function password(): Attribute
    {
        return Attribute::set(function (?string $value) {
            if ($value === null || $value === '') {
                return null;
            }

            return Hash::needsRehash($value) ? Hash::make($value) : $value;
        });
    }

    /**
     * Relation rôles
     *
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @var array<int, int>|null
     */
    private ?array $perimeterIdsCache = null;

    /**
     * Ensemble distinct des `perimeter_id` de ses rôles.
     *
     * Mémorisé sur l'instance : `hasMultiplePerimeters()` est appelé (souvent deux fois) par
     * quasiment chaque partial `_details.blade.php` de l'appli — sans ce cache, une vue listant
     * N objets ré-exécute la requête `roles()->pluck('perimeter_id')` N fois pour le même
     * utilisateur (auth()->user() renvoie la même instance tout au long de la requête).
     * `EnsureActivePerimeter` invalide ce cache en tout début de requête via
     * resetPerimeterCache(), donc la mémorisation ne survit jamais à un changement de rôles
     * entre deux requêtes (y compris dans les tests qui réutilisent le même User via actingAs).
     *
     * @return array<int, int>
     */
    public function perimeterIds(): array
    {
        if ($this->perimeterIdsCache === null) {
            $this->perimeterIdsCache = $this->roles()->pluck('perimeter_id')->unique()->values()->all();
        }

        return $this->perimeterIdsCache;
    }

    /**
     * Invalide le cache de perimeterIds(). Appelé par EnsureActivePerimeter en tout début de
     * requête pour borner la mémorisation à la durée d'une seule requête.
     */
    public function resetPerimeterCache(): void
    {
        $this->perimeterIdsCache = null;
        $this->permissionsByPerimeterCache = null;
    }

    /**
     * @var array<int, list<string>>|null
     */
    private ?array $permissionsByPerimeterCache = null;

    /**
     * Permissions de l'utilisateur regroupées par périmètre : chaque rôle ne porte ses
     * permissions que dans SON périmètre. Deux rôles dans deux périmètres ne se cumulent donc
     * jamais (lecteur dans P2 + rédacteur dans P1 = lecture seule dans P2).
     * Mémorisé sur l'instance, invalidé par resetPerimeterCache().
     *
     * @return array<int, list<string>>
     */
    public function permissionsByPerimeter(): array
    {
        return $this->permissionsByPerimeterCache
            ??= self::groupPermissionsByPerimeter($this->roles()->with('permissions')->get());
    }

    /**
     * Données de session relatives aux rôles/permissions, partagées par le login local, le SSO
     * et RefreshRolePermissions. `auth_permissions` (union à plat) reste exposé pour les
     * consommateurs qui ne raisonnent pas par périmètre (fonctionnalité désactivée).
     *
     * @return array<string, mixed>
     */
    public function sessionPermissionData(): array
    {
        $this->load('roles.permissions');
        $byPerimeter = $this->permissionsByPerimeterCache = self::groupPermissionsByPerimeter($this->roles);

        return [
            'auth_role_ids' => $this->roles->pluck('id')->all(),
            'auth_permissions' => collect($byPerimeter)->flatten()->unique()->values()->all(),
            'auth_permissions_by_perimeter' => $byPerimeter,
            'auth_permissions_at' => now()->timestamp,
        ];
    }

    /**
     * @param  Collection<int, Role>  $roles  rôles avec `permissions` chargées
     * @return array<int, list<string>>
     */
    private static function groupPermissionsByPerimeter(Collection $roles): array
    {
        $byPerimeter = [];
        foreach ($roles as $role) {
            $titles = $role->permissions->pluck('title')->all();
            $byPerimeter[(int) $role->perimeter_id] = array_values(array_unique(
                array_merge($byPerimeter[(int) $role->perimeter_id] ?? [], $titles)
            ));
        }

        return $byPerimeter;
    }

    /**
     * Vrai si l'utilisateur est responsable d'au moins 2 périmètres distincts.
     * Utilisé par le sélecteur de périmètre actif et les colonnes de liste
     * (incréments suivants).
     */
    public function hasMultiplePerimeters(): bool
    {
        return count($this->perimeterIds()) >= 2;
    }

    /**
     * Vrai si le formulaire d'édition de $object doit proposer le choix du périmètre :
     * plusieurs périmètres, et l'objet appartient à l'un d'eux. Un cartographe désigné sur
     * un objet hors de ses périmètres ne voit pas le sélecteur, qui ne pourrait proposer que
     * ses propres périmètres et déplacerait l'objet à l'enregistrement.
     */
    public function canChoosePerimeterOf(Model $object): bool
    {
        return $this->hasMultiplePerimeters()
            && in_array((int) $object->getAttribute('perimeter_id'), $this->perimeterIds(), true);
    }

    /**
     * Périmètre de travail courant, mémorisé en session par
     * EnsureActivePerimeter/PerimeterActiveController. `Perimeter::ALL_ID`
     * (0) signifie « tous mes périmètres » (aucun filtre).
     */
    public function activePerimeterId(): int
    {
        return (int) session('active_perimeter', Perimeter::ALL_ID);
    }

    /**
     * Périmètre par défaut pour un nouvel objet : le périmètre actif s'il en
     * est un réel (pas « tous »), sinon le premier périmètre de l'utilisateur,
     * sinon le périmètre par défaut. Utilisé par le sélecteur de création et
     * PerimeterAssignmentObserver.
     */
    public function activeOrDefaultPerimeterId(): int
    {
        $active = $this->activePerimeterId();
        if ($active !== Perimeter::ALL_ID && in_array($active, $this->perimeterIds(), true)) {
            return $active;
        }

        return $this->perimeterIds()[0] ?? Perimeter::DEFAULT_ID;
    }

    private ?bool $isAdminCache = null;

    /**
     * L'utilisateur es-til administrateur ?
     */
    public function isAdmin(): bool
    {
        if ($this->isAdminCache === null) {
            $this->isAdminCache = $this->relationLoaded('roles')
                ? $this->roles->contains('id', 1)
                : $this->roles()->whereKey(1)->exists();
        }

        return $this->isAdminCache;
    }

    /**
     * Ajouter un rôle (utilise attach sur pivot).
     */
    public function addRole(Role $role): void
    {
        if (! $this->hasRole($role)) {
            $this->roles()->attach($role->getKey());
            // Optionnel : tenir le cache en mémoire si déjà chargé :
            if ($this->relationLoaded('roles')) {
                $this->setRelation('roles', $this->getRelation('roles')->push($role));
            }
        }
    }

    /**
     * Vérifie si l'utilisateur possède un rôle.
     *
     * @param  string|Role  $role  Titre/slug OU instance Role.
     */
    public function hasRole(string|Role $role): bool
    {
        // Zéro requête si déjà eager-loaded
        if ($this->relationLoaded('roles')) {
            $roles = $this->getRelation('roles'); // Collection<Role>

            return $role instanceof Role
                ? $roles->contains('id', $role->getKey())
                : ($roles->contains('slug', $role) || $roles->contains('title', $role));
        }

        // Requête minimale
        if ($role instanceof Role) {
            return $this->roles()->whereKey($role->getKey())->exists();
        }

        // Privilégie 'slug' si disponible
        return $this->roles()
            ->where(fn ($q) => $q->where('slug', $role)->orWhere('title', $role))
            ->exists();
    }

    public function cartographerEntries(): HasMany
    {
        return $this->hasMany(Cartographer::class, 'user_id');
    }

    public function isCartographerOf(Model $object): bool
    {
        return Cartographer::isAllowed($this, $object);
    }
}
