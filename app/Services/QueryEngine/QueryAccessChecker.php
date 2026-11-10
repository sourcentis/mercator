<?php

namespace App\Services\QueryEngine;

use App\Models\Cartographer;
use App\Models\Permission;
use Gate;
use Illuminate\Support\Str;

/**
 * Vérifie que l'utilisateur courant peut consulter tous les modèles lus par un DSL.
 */
final class QueryAccessChecker
{
    /**
     * Retourne les noms des modèles du DSL pour lesquels l'utilisateur
     * n'a pas la permission _show.
     *
     * @return list<string>
     */
    public static function deniedModels(array $dsl): array
    {
        $denied = [];

        foreach (self::resolveInvolvedModelClasses($dsl) as $modelClass => $displayName) {
            if (! self::canShowModel($modelClass)) {
                $denied[] = $displayName;
            }
        }

        // Les modèles sans permission _show (Perimeter, ApplicationEvent…) n'ont pas de droit
        // propre : lus en relation, ils suivent leur objet parent.
        foreach (self::resolveFieldAndFilterModelClasses($dsl) as $modelClass => $displayName) {
            if (! self::canShowModel($modelClass)
                && Permission::query()->where('title', self::showPermission($modelClass))->exists()) {
                $denied[] = $displayName;
            }
        }

        return array_values(array_unique($denied));
    }

    public static function deniedMessage(array $denied): string
    {
        return 'Accès refusé. Vous n\'avez pas le droit de consulter : '.implode(', ', $denied).'.';
    }

    /**
     * Collecte les classes de modèles référencées par la clause FROM
     * et par chaque segment des chemins traverse.
     *
     * @return array<string, string> FQCN → nom court affiché
     */
    private static function resolveInvolvedModelClasses(array $dsl): array
    {
        $models = [];

        try {
            $fromClass = QueryEngineIntrospector::resolveModelClass($dsl['from']);
            $models[$fromClass] = class_basename($fromClass);
        } catch (\Throwable) {
            return $models;
        }

        foreach ($dsl['traverse'] ?? [] as $traverseItem) {
            $segments = array_column(QueryResolver::normalizeSegments($traverseItem), 'name');

            try {
                self::walkRelationPath($fromClass, $segments, $models);
            } catch (\Throwable) {
                continue;
            }
        }

        return $models;
    }

    /**
     * Collecte les modèles atteints par les relations des clauses FIELDS et WHERE.
     *
     * @return array<string, string> FQCN → nom court affiché
     */
    private static function resolveFieldAndFilterModelClasses(array $dsl): array
    {
        try {
            $fromClass = QueryEngineIntrospector::resolveModelClass($dsl['from']);
        } catch (\Throwable) {
            return [];
        }

        $models = [];

        foreach ($dsl['fields'] ?? [] as $field) {
            self::walkRelationPath($fromClass, array_slice(explode('.', $field), 0, -1), $models);
        }

        self::collectFilterModelClasses($fromClass, $dsl['filters'] ?? [], $models);

        return $models;
    }

    /**
     * @param  array<string, string>  $models
     */
    private static function collectFilterModelClasses(string $class, array $filters, array &$models): void
    {
        foreach ($filters as $filter) {
            $relation = $filter['exists'] ?? $filter['not_exists'] ?? null;

            if ($relation !== null) {
                $relatedClass = self::walkRelationPath($class, [$relation], $models);
                if ($relatedClass !== null) {
                    self::collectFilterModelClasses($relatedClass, (array) ($filter['conditions'] ?? []), $models);
                }
            } elseif (isset($filter['group'])) {
                self::collectFilterModelClasses($class, $filter['group'], $models);
            } elseif (isset($filter['field'])) {
                self::walkRelationPath($class, array_slice(explode('.', $filter['field']), 0, -1), $models);
            }
        }
    }

    /**
     * Suit un chemin de relations et ajoute chaque modèle atteint à $models.
     * Une relation inconnue arrête le parcours : le résolveur la rejettera en 422.
     *
     * @param  list<string>  $segments
     * @param  array<string, string>  $models
     * @return string|null Classe du dernier modèle atteint
     */
    private static function walkRelationPath(string $class, array $segments, array &$models): ?string
    {
        foreach ($segments as $segment) {
            $relDef = collect(QueryEngineIntrospector::getRelations($class))
                ->firstWhere('name', Str::snake($segment));

            if (! $relDef) {
                return null;
            }

            $class = QueryEngineIntrospector::resolveModelClassFromAny($relDef['related']);
            $models[$class] = class_basename($class);
        }

        return $class;
    }

    /**
     * Vérifie si l'utilisateur courant peut consulter ce type de modèle,
     * soit via une permission de rôle (_show), soit en tant que cartographe.
     */
    private static function canShowModel(string $modelClass): bool
    {
        if (Gate::allows(self::showPermission($modelClass))) {
            return true;
        }

        return Cartographer::hasAnyFor(auth()->user(), $modelClass);
    }

    private static function showPermission(string $modelClass): string
    {
        return Str::snake(class_basename($modelClass)).'_show';
    }
}
