<?php

namespace App\Services\QueryEngine;

use App\Support\ModelRegistry;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

class QueryEngineIntrospector
{
    private const EXCLUDED_MODELS = ['User', 'PasswordReset'];

    protected const MODEL_NAMESPACE = 'App\\Models\\';

    /**
     * Résout la classe depuis un nom court OU une FQCN — utile
     * lors de la traversée récursive où on a déjà la classe complète.
     */
    public static function resolveModelClassFromAny(string $classOrShortName): string
    {
        // FQCN direct (ex: App\Models\LogicalServer)
        if (class_exists($classOrShortName)) {
            return $classOrShortName;
        }

        // Nom court PascalCase — usage interne depuis getRelations() (class_basename)
        $fqcn = self::MODEL_NAMESPACE.$classOrShortName;
        if (class_exists($fqcn)) {
            return $fqcn;
        }

        // Slug API en dernier recours
        return self::resolveModelClass($classOrShortName);
    }

    /**
     * Décrit un modèle : colonnes + relations.
     * Utilisé par le endpoint /query-engine/schema/{model}.
     */
    public static function describe(string $modelName): array
    {
        $class = self::resolveModelClass($modelName);
        $instance = new $class;

        return [
            'model' => $modelName,
            'table' => $instance->getTable(),
            'fields' => self::getFillable($class),
            'relations' => self::getRelations($class),
        ];
    }

    /** @var array<class-string, array> */
    private static array $relationsCache = [];

    /** @var array<class-string, array> */
    private static array $fillableCache = [];

    /**
     * Vide les caches d'introspection (relations, champs autorisés).
     */
    public static function flushCache(): void
    {
        self::$relationsCache = [];
        self::$fillableCache = [];
    }

    /**
     * Découverte des relations Eloquent par Reflection.
     * Le nom exposé est en snake_case (ex: logicalServers → logical_servers).
     *
     * Seules les méthodes dont le type de retour déclaré est une Relation sont
     * invoquées : appeler aveuglément toutes les méthodes publiques exécutait
     * aussi restore(), forceDelete()… (méthodes de traits) avec des écritures
     * en base et des entrées d'audit parasites.
     */
    public static function getRelations(string $class): array
    {
        if (isset(self::$relationsCache[$class])) {
            return self::$relationsCache[$class];
        }

        $instance = new $class;
        $relations = [];

        foreach ((new ReflectionClass($instance))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class !== $class) {
                continue;
            }
            if ($method->isStatic() || $method->getNumberOfParameters() !== 0) {
                continue;
            }

            $returnType = $method->getReturnType();
            if (! $returnType instanceof ReflectionNamedType
                || $returnType->isBuiltin()
                || ! is_a($returnType->getName(), Relation::class, true)) {
                continue;
            }

            try {
                $result = $method->invoke($instance);

                if ($result instanceof Relation) {
                    $related = class_basename($result->getRelated());

                    // Une relation pointant vers un modèle exclu ne doit être ni listée
                    // ni traversable, sinon EXCLUDED_MODELS est contournable via
                    // from:roles + fields:users.* (pivot Role::users()).
                    if (in_array($related, self::EXCLUDED_MODELS, true)) {
                        continue;
                    }

                    $relations[] = [
                        'name' => Str::snake($method->getName()),   // logical_servers
                        'method' => $method->getName(),               // logicalServers (usage interne)
                        'type' => class_basename($result),
                        'related' => $related,
                    ];
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return self::$relationsCache[$class] = $relations;
    }

    /**
     * Résout le nom de méthode Eloquent depuis un nom snake_case ou camelCase.
     * Ex : logical_servers → logicalServers
     * Lance une HttpException 422 si la relation est introuvable.
     */
    public static function resolveRelationMethod(string $class, string $relationName): string
    {
        $snake = Str::snake($relationName); // normalise l'entrée dans tous les cas

        foreach (self::getRelations($class) as $relation) {
            if ($relation['name'] === $snake) {
                return $relation['method'];
            }
        }

        abort(422, "Relation [{$relationName}] introuvable sur [".class_basename($class).'].');
    }

    /**
     * Retourne les champs autorisés d'un modèle :
     * fillable + id + clés étrangères déclarées dans la table.
     */
    public static function getFillable(string $class): array
    {
        // Toujours autoriser id et les clés primaires
        return self::$fillableCache[$class]
            ??= array_unique(array_merge(['id'], (new $class)->getFillable()));
    }

    /**
     * Vérifie qu'un champ existe dans le fillable du modèle.
     * Lance une HttpException 422 si invalide.
     */
    public static function validateField(string $class, string $field): void
    {
        if (in_array($field, self::getFillable($class), true)) {
            return;
        }

        $table = (new $class)->getTable();
        abort(422, "Le champ [{$field}] n'existe pas dans la table [{$table}].");
    }

    /**
     * Convertit un nom de classe modèle en nom d'API (slug pluriel).
     * Miroir de la logique d'ImportController::export().
     */
    public static function modelToApiName(string $modelName): string
    {
        return ModelRegistry::slug($modelName);
    }

    /**
     * Résout le nom de classe court depuis un nom d'API (slug).
     * Inverse de modelToApiName().
     */
    public static function apiNameToModelName(string $apiName): string
    {
        foreach (self::listModelClasses() as $modelName) {
            if (self::modelToApiName($modelName) === $apiName) {
                return $modelName;
            }
        }

        abort(404, "Modèle API [{$apiName}] introuvable.");
    }

    /**
     * Résout la classe depuis un nom court OU un slug d'API.
     */
    public static function resolveModelClass(string $modelName): string
    {
        // FQCN interne uniquement (traversée récursive)
        if (str_contains($modelName, '\\')) {
            abort_if(! class_exists($modelName), 404, "Classe [{$modelName}] introuvable.");

            return $modelName;
        }

        // Slug API uniquement — le nom de classe court est interdit
        $short = self::apiNameToModelName($modelName); // abort 404 si inconnu

        return self::MODEL_NAMESPACE.$short;
    }

    /**
     * Liste interne des noms de classes (usage : apiNameToModelName, listModels).
     */
    protected static function listModelClasses(): array
    {
        return array_map(
            fn (string $class) => class_basename($class),
            ModelRegistry::allConcreteModels()
        );
    }

    /**
     * Retourne les noms d'API (slugs) pour tous les modèles concrets.
     * Ex : Application → applications, LogicalServer → logical-servers
     */
    public static function listModels(): array
    {
        $models = array_map(
            fn (string $modelName) => self::modelToApiName($modelName),
            self::listModelClasses()
        );

        sort($models);

        return $models;
    }
}
