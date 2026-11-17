<?php

namespace App\Scopes;

use App\Models\Cartographer;
use App\Models\Perimeter;
use App\Support\PerimeterPermissions;
use App\Support\PerimeterSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Restreint toute requête sur un modèle HasPerimeter au périmètre de travail
 * actif (session('active_perimeter'), voir User::activePerimeterId()).
 * No-op tant que la fonctionnalité est désactivée. Quand « tous »
 * (Perimeter::ALL_ID) est sélectionné, restreint aux périmètres de l'utilisateur
 * (pas de filtre pour un administrateur). Pour un non-administrateur, ne garde que
 * les périmètres où son rôle donne la permission `<modèle>_access` : un objet dont
 * il n'a pas l'accès pour ce type et ce périmètre est caché de toutes les listes.
 * S'applique à tout le monde, admin
 * inclus, dès qu'un périmètre précis est actif — y compris aux requêtes de
 * liaison implicite de route (show/edit/update/destroy), pas seulement aux
 * listes : contrairement à Cartographer::scopedQuery(), qui n'est appelé que
 * par une minorité de contrôleurs, un global scope s'applique uniformément
 * partout où le modèle est interrogé.
 */
class PerimeterScope implements PerimeterConstrainingScope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! PerimeterSettings::isEnabled()) {
            return;
        }

        $user = auth()->user();
        if (! $user) {
            return;
        }

        // Périmètres où l'utilisateur voit ce TYPE d'objet : son périmètre actif (ou tous les
        // siens en « tous »), et seulement ceux où son rôle donne `<modèle>_access`. Seul
        // l'administrateur en « tous » n'est pas borné.
        $perimeterIds = PerimeterPermissions::visiblePerimeterIds($user, $model);
        if ($perimeterIds === null) {
            return;
        }

        // Les objets confiés à l'utilisateur comme cartographe restent visibles même sans
        // permission `_access` dans leur périmètre (dans le périmètre actif s'il en est un).
        $cartographed = $user->isAdmin() ? [] : Cartographer::allowedIdsFor($user, get_class($model));
        $active = $user->activePerimeterId();
        $table = $model->getTable();

        $builder->where(function (Builder $query) use ($model, $perimeterIds, $cartographed, $active, $table) {
            $this->constrain($query, $model, $perimeterIds);

            if ($cartographed === []) {
                return;
            }

            if ($active === Perimeter::ALL_ID) {
                $query->orWhereIn("{$table}.id", $cartographed);
            } else {
                $query->orWhere(fn (Builder $q) => $q->whereIn("{$table}.id", $cartographed)->where("{$table}.perimeter_id", $active));
            }
        });
    }

    /**
     * Restreint la requête aux objets appartenant à l'un de ces périmètres. Aussi utilisé par
     * Cartographer::scopedQuery() (via HasPerimeter::constrainToPerimeters()).
     *
     * @param  list<int>  $perimeterIds
     */
    public function constrain(Builder $builder, Model $model, array $perimeterIds): void
    {
        $builder->whereIn($model->getTable().'.perimeter_id', $perimeterIds);
    }
}
