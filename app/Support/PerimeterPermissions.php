<?php

namespace App\Support;

use App\Models\Cartographer;
use App\Models\Perimeter;
use App\Models\Permission;
use App\Models\User;
use App\Traits\HasPerimeter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Autorisation par périmètre : une permission n'est valable que dans le périmètre du rôle qui
 * la porte. Un utilisateur « rédacteur dans P1 + lecteur dans P2 » peut donc modifier les
 * objets de P1 mais seulement consulter ceux de P2.
 *
 * Ne s'applique qu'aux non-administrateurs, quand la fonctionnalité périmètres est activée ;
 * dans tous les autres cas les appelants retombent sur le comportement historique (union à plat
 * des permissions de tous les rôles).
 */
class PerimeterPermissions
{
    private const OBJECT_VERBS = 'access|show|create|edit|delete';

    /**
     * L'utilisateur est-il soumis au contrôle par périmètre ?
     */
    public static function governs(User $user): bool
    {
        return PerimeterSettings::isEnabled() && ! $user->isAdmin();
    }

    /**
     * @return array<int, list<string>> permissions par périmètre (session, sinon base)
     */
    public static function for(User $user): array
    {
        $fromSession = session('auth_permissions_by_perimeter');

        return is_array($fromSession) ? $fromSession : $user->permissionsByPerimeter();
    }

    /**
     * @return list<int> périmètres dans lesquels l'utilisateur détient cette permission
     */
    public static function perimeterIdsWith(User $user, string $permission): array
    {
        $ids = [];
        foreach (self::for($user) as $perimeterId => $titles) {
            if (in_array($permission, $titles, true)) {
                $ids[] = (int) $perimeterId;
            }
        }

        return $ids;
    }

    /**
     * @param  int|null  $perimeterId  null = dans au moins un périmètre
     */
    public static function can(User $user, string $permission, ?int $perimeterId): bool
    {
        $ids = self::perimeterIdsWith($user, $permission);

        return $perimeterId === null ? $ids !== [] : in_array($perimeterId, $ids, true);
    }

    /**
     * Mémo (par requête/process) : la permission `<modèle>_access` existe-t-elle dans le
     * catalogue ? Si non (installation sans cette permission), on ne peut pas l'exiger sans
     * masquer tous les objets du type : seule l'appartenance au périmètre s'applique alors.
     *
     * @var array<string, bool>
     */
    private static array $accessPermissionExists = [];

    /**
     * Appelé à chaque boot de l'application (voir AppServiceProvider::boot()) pour que le mémo
     * ne survive pas à la requête ou au test suivant.
     */
    public static function resetCache(): void
    {
        self::$accessPermissionExists = [];
    }

    /**
     * Périmètres dans lesquels l'utilisateur peut VOIR les objets de ce modèle, compte tenu du
     * périmètre de travail actif : appartenance au périmètre (ou périmètre actif) ET permission
     * `<modèle>_access` détenue dans CE périmètre. Utilisé par les global scopes, donc par toutes
     * les listes, les liaisons de route et les listes déroulantes.
     *
     * @return list<int>|null null = aucune borne (administrateur avec « tous »)
     */
    public static function visiblePerimeterIds(User $user, Model $model): ?array
    {
        $active = $user->activePerimeterId();

        if ($user->isAdmin()) {
            return $active === Perimeter::ALL_ID ? null : [$active];
        }

        $ids = $active === Perimeter::ALL_ID ? $user->perimeterIds() : [$active];

        $permission = Str::snake(class_basename($model)).'_access';
        if (self::accessPermissionExists($permission)) {
            $ids = array_values(array_intersect($ids, self::perimeterIdsWith($user, $permission)));
        }

        return $ids;
    }

    private static function accessPermissionExists(string $permission): bool
    {
        return self::$accessPermissionExists[$permission]
            ??= Permission::query()->where('title', $permission)->exists();
    }

    /**
     * Décision pour Gate::before. null = ne se prononce pas (utilisateur non soumis au contrôle,
     * ou ability qui n'est pas une permission de rôle de cet utilisateur) : le chemin historique
     * s'applique. true/false = décision ferme, y compris face aux Gates par permission définis
     * par AuthGates, qui eux ignorent le périmètre.
     *
     * @param  array<int, mixed>  $arguments
     */
    public static function decide(User $user, string $ability, array $arguments): ?bool
    {
        if (! self::governs($user) || ! self::can($user, $ability, null)) {
            return null;
        }

        foreach (self::requiredPerimeters($user, $ability, $arguments) as $perimeterId) {
            if (! self::can($user, $ability, $perimeterId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Garde-fou sur les écritures d'un modèle (create/edit/delete), appelé par l'observer de
     * périmètre. Couvre les chemins qui ne passent par aucun Gate portant l'objet : suppressions
     * et modifications de masse, API, code interne.
     *
     * @throws AuthorizationException
     */
    public static function authorizeModel(Model $model, string $verb): void
    {
        $user = auth()->user();
        if (! $user instanceof User || ! self::governs($user)) {
            return;
        }

        // Une délégation cartographe porte sur l'objet, pas sur son rattachement : changer de
        // périmètre exige le droit d'écriture de rôle dans le périmètre d'origine ET de destination.
        $moving = $verb === 'edit' && $model->exists && $model->isDirty('perimeter_id');

        $ability = Str::snake(class_basename($model)).'_'.$verb;
        if (! self::can($user, $ability, null)) {
            if ($moving) {
                throw new AuthorizationException('403 Forbidden');
            }

            return; // pas une permission de rôle : les Gates historiques ont déjà tranché
        }

        // Périmètre d'origine ET de destination (déplacement d'un objet).
        $perimeterIds = array_unique(array_filter([
            $model->getAttribute('perimeter_id'),
            $model->getOriginal('perimeter_id'),
        ], fn ($id) => $id !== null));

        foreach ($perimeterIds as $perimeterId) {
            if (self::can($user, $ability, (int) $perimeterId)) {
                continue;
            }
            // Un cartographe désigné sur l'objet peut le modifier hors de ses périmètres, sans le déplacer.
            if ($verb === 'edit' && $model->exists && ! $moving && Cartographer::isAllowed($user, $model)) {
                continue;
            }

            throw new AuthorizationException('403 Forbidden');
        }
    }

    /**
     * Périmètre d'un nouvel objet dont le périmètre n'a pas été saisi : le périmètre actif s'il
     * en est un, sinon le premier où l'utilisateur peut créer ce type d'objet.
     */
    public static function defaultPerimeterFor(User $user, Model $model): int
    {
        if (self::governs($user)) {
            $active = $user->activePerimeterId();
            if ($active !== Perimeter::ALL_ID && in_array($active, $user->perimeterIds(), true)) {
                return $active;
            }

            $creatable = self::perimeterIdsWith($user, Str::snake(class_basename($model)).'_create');
            if ($creatable !== []) {
                return $creatable[0];
            }
        }

        return $user->activeOrDefaultPerimeterId();
    }

    /**
     * Périmètres dans lesquels la permission doit être détenue pour cet appel. Vide = « dans au
     * moins un » (action de classe sans périmètre actif précis : menu, liste, etc.).
     *
     * @param  array<int, mixed>  $arguments
     * @return list<int>
     */
    private static function requiredPerimeters(User $user, string $ability, array $arguments): array
    {
        $object = self::contextObject($ability, $arguments);
        if ($object !== null) {
            $required = [(int) $object->getAttribute('perimeter_id')];

            // Déplacer un objet exige le droit d'écriture dans le périmètre cible aussi.
            if (str_ends_with($ability, '_edit') && ! request()->isMethodSafe()) {
                $target = request()->input('perimeter_id');
                if (is_numeric($target) && (int) $target > 0) {
                    $required[] = (int) $target;
                }
            }

            return $required;
        }

        // Suppression de masse (`ids[]`) : le droit doit être détenu dans le périmètre de CHAQUE
        // objet visé, quelle que soit la façon dont le contrôleur supprime ensuite (y compris un
        // delete() de requête qui ne déclenche aucun événement de modèle).
        if (str_ends_with($ability, '_delete')) {
            $perimeterIds = self::perimetersOfRequestedIds($ability);
            if ($perimeterIds !== []) {
                return $perimeterIds;
            }
        }

        if (str_ends_with($ability, '_create')) {
            $target = request()->input('perimeter_id');
            if (is_numeric($target) && (int) $target > 0) {
                return [(int) $target];
            }
        }

        $active = $user->activePerimeterId();

        return $active === Perimeter::ALL_ID ? [] : [$active];
    }

    /**
     * Périmètres des objets désignés par `ids` dans la requête, pour `<model>_delete`.
     *
     * @return list<int>
     */
    private static function perimetersOfRequestedIds(string $ability): array
    {
        $ids = request()->input('ids');
        if (! is_array($ids) || $ids === []) {
            return [];
        }

        /** @var class-string<Model> $class */
        $class = 'App\\Models\\'.Str::studly(substr($ability, 0, -strlen('_delete')));
        if (! class_exists($class) || ! in_array(HasPerimeter::class, class_uses_recursive($class), true)) {
            return [];
        }

        return $class::query()
            ->whereIn('id', array_filter($ids, 'is_scalar'))
            ->distinct()
            ->pluck('perimeter_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * L'objet visé par l'ability : passé explicitement au Gate, sinon lié à la route
     * (`destroy(Entity $entity)` appelle `Gate::denies('entity_delete')` sans l'objet).
     *
     * @param  array<int, mixed>  $arguments
     */
    private static function contextObject(string $ability, array $arguments): ?Model
    {
        foreach ($arguments as $argument) {
            if ($argument instanceof Model && $argument->getAttribute('perimeter_id') !== null) {
                return $argument;
            }
        }

        $route = request()->route();
        if ($route === null) {
            return null;
        }

        foreach ($route->parameters() as $parameter) {
            if (! $parameter instanceof Model || $parameter->getAttribute('perimeter_id') === null) {
                continue;
            }

            $prefix = preg_quote(Str::snake(class_basename($parameter)), '/');
            if (preg_match('/^'.$prefix.'_('.self::OBJECT_VERBS.')$/', $ability)) {
                return $parameter;
            }
        }

        return null;
    }
}
