<?php

namespace App\Scopes;

use App\Models\ApplicationFlow;
use App\Support\PerimeterPermissions;
use App\Support\PerimeterSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Filtrage périmètre pour ApplicationFlow. Contrairement à PerimeterScope
 * (une simple égalité sur la colonne perimeter_id du modèle), un flux relie
 * deux objets — source et destination — qui peuvent chacun appartenir à un
 * périmètre différent du sien : jusqu'à 3 périmètres distincts sont donc
 * pertinents pour un même flux (son perimeter_id explicite, celui de la
 * source, celui de la destination). Le flux reste visible dès que l'un
 * d'eux correspond au périmètre actif.
 */
class ApplicationFlowPerimeterScope implements PerimeterConstrainingScope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! $model instanceof ApplicationFlow || ! PerimeterSettings::isEnabled()) {
            return;
        }

        $user = auth()->user();
        if (! $user) {
            return;
        }

        // Périmètres où l'utilisateur voit ce type d'objet (rôle + `<modèle>_access`).
        $perimeterIds = PerimeterPermissions::visiblePerimeterIds($user, $model);
        if ($perimeterIds === null) {
            return;
        }

        $this->constrain($builder, $model, $perimeterIds);
    }

    /**
     * Visible dès que le périmètre propre, celui de la source ou celui de la destination
     * figure parmi $perimeterIds.
     *
     * @param  list<int>  $perimeterIds
     */
    public function constrain(Builder $builder, Model $model, array $perimeterIds): void
    {
        if (! $model instanceof ApplicationFlow) {
            return;
        }

        $table = $model->getTable();

        $builder->where(function (Builder $query) use ($model, $table, $perimeterIds) {
            $query->whereIn("{$table}.perimeter_id", $perimeterIds);

            foreach ($model::perimeterRelations() as $column => $relationName) {
                $relatedTable = $model->{$relationName}()->getRelated()->getTable();

                $query->orWhereExists(function (QueryBuilder $sub) use ($relatedTable, $column, $table, $perimeterIds) {
                    $sub->selectRaw('1')
                        ->from($relatedTable)
                        ->whereColumn("{$relatedTable}.id", "{$table}.{$column}")
                        ->whereIn("{$relatedTable}.perimeter_id", $perimeterIds);
                });
            }
        });
    }
}
