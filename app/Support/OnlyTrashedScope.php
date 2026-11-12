<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Variante de SoftDeletingScope qui ne retient que les objets supprimés.
 * Elle hérite des extensions du scope d'origine (withTrashed(), restore()…).
 */
class OnlyTrashedScope extends SoftDeletingScope
{
    /**
     * Même signature que SoftDeletingScope::apply() (dont les génériques ne sont pas
     * exprimables dans une sous-classe).
     *
     * @phpstan-ignore method.childParameterType
     */
    public function apply(Builder $builder, Model $model): void
    {
        /** @phpstan-ignore method.notFound */
        $builder->whereNotNull($model->getQualifiedDeletedAtColumn());
    }
}
