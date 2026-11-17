<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garde-fou contre l'élévation de privilèges par la gestion des utilisateurs et des rôles
 * (GHSA-4x92-9hqx-5f58) : détenir user_create / user_edit / role_create / role_edit ne
 * permet pas de distribuer plus de droits que l'on n'en a soi-même.
 *
 * Pour un non-administrateur :
 *  - un rôle n'est attribuable (ou retirable) que si toutes ses permissions sont détenues par
 *    l'acteur dans le périmètre de ce rôle — le rôle Admin ne l'est donc jamais ;
 *  - un utilisateur n'est gérable (modification, suppression) que si tous ses rôles sont
 *    attribuables par l'acteur — un administrateur ou un utilisateur plus privilégié ne l'est
 *    donc jamais (pas de changement de mot de passe ou d'e-mail menant à une prise de compte) ;
 *  - un rôle n'est modifiable ou supprimable que s'il est attribuable par l'acteur, et un rôle
 *    créé ou modifié ne peut porter que des permissions détenues par l'acteur dans le
 *    périmètre de ce rôle (pas d'ajout de permission à son propre rôle, pas de déplacement
 *    d'un rôle vers un périmètre où l'acteur n'a pas ces droits, Admin intouchable).
 */
class RoleAssignment
{
    public const ADMIN_ROLE_ID = 1;

    /**
     * @return list<int>|null identifiants des rôles attribuables par l'acteur (null = tous)
     */
    public static function grantableRoleIds(User $actor): ?array
    {
        if ($actor->isAdmin()) {
            return null;
        }

        return Role::query()
            ->with('permissions')
            ->whereKeyNot(self::ADMIN_ROLE_ID)
            ->get()
            ->filter(fn (Role $role) => self::holdsAll(
                $actor,
                $role->permissions->pluck('title')->all(),
                (int) $role->perimeter_id
            ))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * L'acteur détient-il toutes ces permissions dans ce périmètre (ou, périmètres désactivés,
     * dans l'un quelconque de ses rôles) ? Toujours vrai pour un administrateur.
     *
     * @param  list<string>  $titles
     */
    public static function holdsAll(User $actor, array $titles, int $perimeterId): bool
    {
        if ($actor->isAdmin()) {
            return true;
        }

        $byPerimeter = $actor->permissionsByPerimeter();
        $held = PerimeterSettings::isEnabled()
            ? ($byPerimeter[$perimeterId] ?? [])
            : array_merge([], ...array_values($byPerimeter));

        return array_diff($titles, $held) === [];
    }

    /**
     * Identifiants des permissions que l'acteur détient, tous périmètres confondus (null = toutes).
     * Sert à griser les cases du formulaire de rôle ; le contrôle par périmètre reste fait à
     * l'enregistrement (authorizeRolePermissions).
     *
     * @return list<int>|null
     */
    public static function grantablePermissionIds(User $actor): ?array
    {
        if ($actor->isAdmin()) {
            return null;
        }

        return Permission::query()
            ->whereIn('title', array_merge([], ...array_values($actor->permissionsByPerimeter())))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * L'acteur peut-il modifier ou supprimer ce rôle ? Seulement s'il pourrait l'attribuer.
     */
    public static function canManageRole(User $actor, Role $role): bool
    {
        $grantable = self::grantableRoleIds($actor);

        return $grantable === null || in_array((int) $role->getKey(), $grantable, true);
    }

    /**
     * Interrompt la requête (403) si l'acteur ne peut pas gérer ce rôle.
     */
    public static function authorizeManageRole(User $actor, Role $role): void
    {
        abort_unless(self::canManageRole($actor, $role), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    /**
     * Interrompt la requête (403) si l'état final d'un rôle (créé ou modifié) porte une
     * permission que l'acteur ne détient pas dans le périmètre de ce rôle.
     *
     * @param  array<int, mixed>  $permissionIds
     */
    public static function authorizeRolePermissions(User $actor, array $permissionIds, int $perimeterId): void
    {
        $titles = Permission::query()
            ->whereKey(array_map('intval', $permissionIds))
            ->pluck('title')
            ->all();

        abort_unless(self::holdsAll($actor, $titles, $perimeterId), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    /**
     * L'acteur peut-il modifier ou supprimer cet utilisateur ?
     */
    public static function canManageUser(User $actor, User $target): bool
    {
        $grantable = self::grantableRoleIds($actor);
        if ($grantable === null) {
            return true;
        }

        return array_diff(self::currentRoleIds($target), $grantable) === [];
    }

    /**
     * Interrompt la requête (403) si l'acteur ne peut pas gérer l'utilisateur cible.
     */
    public static function authorizeManage(User $actor, User $target): void
    {
        abort_unless(self::canManageUser($actor, $target), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    /**
     * Interrompt la requête (403) si le passage des rôles actuels de la cible (aucun pour une
     * création) aux rôles demandés ajoute ou retire un rôle non attribuable par l'acteur.
     *
     * @param  array<int, mixed>  $requestedRoleIds
     */
    public static function authorizeRoles(User $actor, ?User $target, array $requestedRoleIds): void
    {
        $grantable = self::grantableRoleIds($actor);
        if ($grantable === null) {
            return;
        }

        $current = $target ? self::currentRoleIds($target) : [];
        $requested = array_values(array_unique(array_map('intval', $requestedRoleIds)));

        $changed = array_merge(array_diff($requested, $current), array_diff($current, $requested));

        abort_unless(array_diff($changed, $grantable) === [], Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    /**
     * @return list<int>
     */
    private static function currentRoleIds(User $user): array
    {
        return $user->roles()->pluck('roles.id')->map(fn ($id) => (int) $id)->all();
    }
}
