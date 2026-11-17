<?php

namespace App\Traits;

use App\Models\Perimeter;
use App\Scopes\PerimeterConstrainingScope;
use App\Scopes\PerimeterScope;
use App\Support\PerimeterSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\HttpFoundation\Response;

trait HasPerimeter
{
    protected static function bootHasPerimeter(): void
    {
        static::addGlobalScope(new (static::perimeterScopeClass()));
    }

    /**
     * The Scope class enforcing perimeter filtering for this model. Override
     * in a model whose visibility depends on more than its own perimeter_id
     * column (see ApplicationFlow::perimeterScopeClass()).
     *
     * @return class-string<PerimeterConstrainingScope>
     */
    protected static function perimeterScopeClass(): string
    {
        return PerimeterScope::class;
    }

    /**
     * Restreint $query aux objets visibles depuis l'un de ces périmètres, avec la même règle que
     * le global scope du modèle (y compris les scopes à relations d'ApplicationFlow/PhysicalLink).
     *
     * @param  Builder<static>  $query
     * @param  list<int>  $perimeterIds
     */
    public static function constrainToPerimeters(Builder $query, array $perimeterIds): void
    {
        (new (static::perimeterScopeClass()))->constrain($query, $query->getModel(), $perimeterIds);
    }

    public function perimeter(): BelongsTo
    {
        return $this->belongsTo(Perimeter::class);
    }

    /**
     * Route-model binding normally lets PerimeterScope hide an out-of-perimeter
     * object behind a plain 404, indistinguishable from "doesn't exist". Here we
     * tell the two apart: if the object exists once PerimeterScope is lifted, the
     * user simply isn't allowed to see it from their active perimeter, so answer
     * 403 (access denied) rather than 404 (not found).
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $model = parent::resolveRouteBinding($value, $field);

        if ($model !== null || ! PerimeterSettings::isEnabled()) {
            return $model;
        }

        $existsOutsideActivePerimeter = static::withoutGlobalScope(static::perimeterScopeClass())
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->exists();

        abort_if($existsOutsideActivePerimeter, Response::HTTP_FORBIDDEN, '403 Forbidden');

        return null;
    }
}
