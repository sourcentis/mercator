<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope de périmètre capable aussi de restreindre une requête à une liste
 * explicite de périmètres (utilisé par HasPerimeter::constrainToPerimeters()).
 */
interface PerimeterConstrainingScope extends Scope
{
    /**
     * @param  list<int>  $perimeterIds
     */
    public function constrain(Builder $builder, Model $model, array $perimeterIds): void;
}
