<?php

namespace App\Services\QueryEngine;

use App\Contracts\HasIconContract;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class QueryResolver
{
    protected const ALLOWED_OPERATORS = ['=', '!=', '<', '>', '<=', '>=', 'like', 'not like', 'in', 'not in'];

    // Plafond de sécurité : un graphe fortement connecté (many-to-many, cycles) peut
    // sinon être ré-exploré de façon combinatoire et dépasser le temps d'exécution max.
    protected const MAX_GRAPH_NODES = 500;

    protected array $visitedNodes = [];

    protected array $expandedPaths = [];

    protected array $nodes = [];

    protected array $edges = [];

    protected bool $truncated = false;

    // ─────────────────────────────────────────────────────────────
    // Point d'entrée
    // ─────────────────────────────────────────────────────────────

    public function execute(array $dsl): GraphResult|ListResult
    {
        $modelClass = QueryEngineIntrospector::resolveModelClass($dsl['from']);
        $builder = $modelClass::query();

        foreach ($dsl['filters'] ?? [] as $filter) {
            $this->applyFilter($builder, $filter);
        }

        $traverse = $dsl['traverse'] ?? [];
        $fields = $dsl['fields'] ?? [];
        $eagerLoad = $this->resolveEagerLoads($modelClass, $traverse, $fields);

        if (! empty($eagerLoad)) {
            $builder->with($eagerLoad);
        }

        // Sans LIMIT explicite, une liste renvoie tous les résultats ;
        // un graphe garde une limite par défaut (exploration coûteuse).
        $limit = $dsl['limit'] ?? (($dsl['output'] ?? 'list') === 'graph' ? 100 : null);
        if ($limit !== null) {
            $builder->limit($limit);
        }

        $items = $builder->get();

        return match ($dsl['output'] ?? 'list') {
            'graph' => $this->buildGraph($items, $dsl['from'], $traverse),
            default => $this->buildList($items, $dsl),
        };
    }

    /**
     * Exécute le DSL et retourne le résultat sérialisable, avec ses métadonnées
     * (réponse JSON du moteur, côté web comme côté API).
     */
    public function executeToArray(array $dsl): array
    {
        $result = $this->execute($dsl);

        $response = $result->toArray();
        $response['meta'] = [
            'output' => $dsl['output'] ?? 'list',
            'from' => $dsl['from'],
            'count' => $result instanceof GraphResult
                ? $result->nodeCount()
                : $result->rowCount(),
            'truncated' => $result instanceof GraphResult && $result->truncated,
        ];

        return $response;
    }

    // ─────────────────────────────────────────────────────────────
    // Normalisation du format traverse (string | {segments:[...]})
    // ─────────────────────────────────────────────────────────────

    /**
     * Normalise un item de traverse en tableau de segments typés.
     *
     * Entrées :
     *   "subnetworks.vlan"
     *   {"segments":[{"name":"subnetworks","hidden":true},{"name":"vlan","hidden":false}]}
     *
     * Sortie :
     *   [["name"=>"subnetworks","hidden"=>false],["name"=>"vlan","hidden"=>false]]
     *   [["name"=>"subnetworks","hidden"=>true], ["name"=>"vlan","hidden"=>false]]
     */
    public static function normalizeSegments(string|array $item): array
    {
        if (is_string($item)) {
            return array_map(
                fn (string $name) => ['name' => $name, 'hidden' => false],
                explode('.', $item)
            );
        }

        return array_map(
            fn (array $s) => ['name' => (string) $s['name'], 'hidden' => (bool) ($s['hidden'] ?? false)],
            $item['segments']
        );
    }

    /**
     * Extrait le chemin plat (sans parenthèses) pour le eager-loading Laravel.
     * Ex: {segments:[{name:"sub",hidden:true},{name:"vlan",hidden:false}]} → "sub.vlan"
     */
    public static function flattenTraversePath(string|array $item): string
    {
        return implode('.', array_column(self::normalizeSegments($item), 'name'));
    }

    // ─────────────────────────────────────────────────────────────
    // Eager loads
    // ─────────────────────────────────────────────────────────────

    protected function resolveEagerLoads(string $modelClass, array $traverse, array $fields): array
    {
        // Flatten les chemins traverse (ignore hidden/visible, on charge tout)
        $paths = collect($traverse)
            ->map(fn ($item) => self::flattenTraversePath($item));

        foreach ($fields as $field) {
            $parts = explode('.', $field);
            if (count($parts) > 1) {
                array_pop($parts);
                $paths->push(implode('.', $parts));
            }
        }

        return $paths->unique()
            ->map(fn ($path) => $this->resolveRelationPath($modelClass, $path))
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Résout un chemin de relation pointé en noms de méthodes Eloquent.
     * Ex : logical_servers.applications → logicalServers.applications
     * Chaque segment est résolu sur la classe liée du segment précédent.
     */
    protected function resolveRelationPath(string $modelClass, string $path): string
    {
        $parts = explode('.', $path);
        $resolved = [];
        $class = $modelClass;

        foreach ($parts as $segment) {
            $method = QueryEngineIntrospector::resolveRelationMethod($class, $segment);
            $resolved[] = $method;

            // Avancer vers la classe liée pour le segment suivant
            $relDef = collect(QueryEngineIntrospector::getRelations($class))
                ->firstWhere('method', $method);
            if ($relDef) {
                $class = QueryEngineIntrospector::resolveModelClassFromAny($relDef['related']);
            }
        }

        return implode('.', $resolved);
    }

    // ─────────────────────────────────────────────────────────────
    // Filtres — supporte a, a.b, a.b.c et groupes
    // ─────────────────────────────────────────────────────────────

    protected function applyFilter(Builder $builder, array $filter): void
    {
        // EXISTS : { exists: 'backups', ?conditions: [...] }
        if (array_key_exists('exists', $filter)) {
            $relation = QueryEngineIntrospector::resolveRelationMethod(get_class($builder->getModel()), $filter['exists']);
            $conditions = $filter['conditions'] ?? [];
            $boolean = strtolower($filter['boolean'] ?? 'and');
            $method = $boolean === 'or' ? 'orWhereHas' : 'whereHas';

            $builder->$method($relation, function (Builder $q) use ($conditions) {
                foreach ($conditions as $cond) {
                    $this->applyFilter($q, $cond);
                }
            });

            return;
        }

        // NOT EXISTS : { not_exists: 'backups', ?conditions: [...] }
        if (array_key_exists('not_exists', $filter)) {
            $relation = QueryEngineIntrospector::resolveRelationMethod(get_class($builder->getModel()), $filter['not_exists']);
            $conditions = $filter['conditions'] ?? [];
            $boolean = strtolower($filter['boolean'] ?? 'and');
            $method = $boolean === 'or' ? 'orWhereDoesntHave' : 'whereDoesntHave';

            if (empty($conditions)) {
                $builder->$method($relation);
            } else {
                $builder->$method($relation, function (Builder $q) use ($conditions) {
                    foreach ($conditions as $cond) {
                        $this->applyFilter($q, $cond);
                    }
                });
            }

            return;
        }

        // Groupe : { boolean, group: [...] }
        if (array_key_exists('group', $filter)) {
            $boolean = strtolower($filter['boolean'] ?? 'and');

            if ($boolean === 'not') {
                $builder->whereNot(function (Builder $q) use ($filter) {
                    foreach ($filter['group'] as $sub) {
                        $this->applyFilter($q, $sub);
                    }
                });

                return;
            }

            $method = $boolean === 'or' ? 'orWhere' : 'where';
            $builder->$method(function (Builder $q) use ($filter) {
                foreach ($filter['group'] as $sub) {
                    $this->applyFilter($q, $sub);
                }
            });

            return;
        }

        // Condition simple
        $operator = $filter['operator'] ?? '=';
        $value = $filter['value'] ?? null;
        $boolean = strtolower($filter['boolean'] ?? 'and');
        $isOr = $boolean === 'or';

        abort_if(
            ! in_array($operator, self::ALLOWED_OPERATORS, true),
            422,
            "Opérateur interdit : [{$operator}]"
        );

        $parts = array_map(
            fn ($p) => preg_replace('/[^a-zA-Z0-9_]/', '', $p),
            explode('.', $filter['field'])
        );

        if (count($parts) === 1) {
            $this->applyWhereOnBuilder($builder, $parts[0], $operator, $value, $isOr);

            return;
        }

        $field = array_pop($parts);
        $method = $isOr ? 'orWhereHas' : 'whereHas';
        $this->applyNestedWhereHas($builder, $parts, $field, $operator, $value, $method);
    }

    protected function applyNestedWhereHas(
        Builder $builder,
        array $relations,
        string $field,
        string $operator,
        mixed $value,
        string $method = 'whereHas'
    ): void {
        $relation = array_shift($relations);
        $modelClass = get_class($builder->getModel());
        $relMethod = QueryEngineIntrospector::resolveRelationMethod($modelClass, $relation);

        $builder->$method($relMethod, function (Builder $q) use ($relations, $field, $operator, $value) {
            if (empty($relations)) {
                $this->applyWhereOnBuilder($q, $field, $operator, $value);
            } else {
                $this->applyNestedWhereHas($q, $relations, $field, $operator, $value);
            }
        });
    }

    protected function applyWhereOnBuilder(
        Builder $builder,
        string $field,
        string $operator,
        mixed $value,
        bool $isOr = false
    ): void {
        $modelClass = get_class($builder->getModel());
        QueryEngineIntrospector::validateField($modelClass, $field);

        // A column that is $hidden in output must not be usable as a filter
        // predicate — prevents blind hash extraction via LIKE + meta.count oracle.
        abort_if(
            $this->isHidden(new $modelClass, $field),
            422,
            "Field [{$field}] cannot be used as a filter predicate."
        );

        if ($isOr) {
            match ($operator) {
                'in' => $builder->orWhereIn($field, (array) $value),
                'not in' => $builder->orWhereNotIn($field, (array) $value),
                default => $builder->orWhere($field, $operator, $value),
            };
        } else {
            match ($operator) {
                'in' => $builder->whereIn($field, (array) $value),
                'not in' => $builder->whereNotIn($field, (array) $value),
                default => $builder->where($field, $operator, $value),
            };
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Normalisation des valeurs
    // ─────────────────────────────────────────────────────────────

    protected function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof CarbonInterface) {
            return $value->toDateString();
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_array($value) && isset($value['date'])) {
            return substr($value['date'], 0, 10);
        }

        return $value;
    }

    protected function isHidden(Model $item, string $field): bool
    {
        return in_array($field, $item->getHidden(), true);
    }

    protected function getAttributes(Model $item): array
    {
        return collect(array_keys($item->getAttributes()))
            ->reject(fn ($key) => $this->isHidden($item, $key))
            ->mapWithKeys(fn ($key) => [$key => $this->normalizeValue($item->getAttribute($key))])
            ->toArray();
    }

    // ─────────────────────────────────────────────────────────────
    // Liste — Option A : une ligne par combinaison
    // ─────────────────────────────────────────────────────────────

    protected function buildList(Collection $items, array $dsl): ListResult
    {
        $fields = $dsl['fields'] ?? [];
        $traverse = $dsl['traverse'] ?? [];
        $select = $dsl['select'] ?? [];
        $groupBy = $dsl['group_by'] ?? [];
        $rows = [];

        if (! empty($fields) && ! empty($groupBy)) {
            $rows = $this->buildGroupedRows($items, $fields, $groupBy);

            return new ListResult(rows: collect($rows), columns: array_keys($rows[0] ?? []));
        }

        foreach ($items as $item) {
            if (! empty($fields)) {
                foreach ($this->expandRow($item, $fields) as $row) {
                    $rows[] = $row;
                }
            } else {
                $modelClass = get_class($item);
                $attrs = $this->getAttributes($item);

                if ($select) {
                    foreach ($select as $f) {
                        QueryEngineIntrospector::validateField($modelClass, $f);
                    }
                    $row = collect($attrs)->only($select)->toArray();
                } else {
                    $row = collect($attrs)->except(['created_at', 'updated_at', 'deleted_at'])->toArray();
                }

                foreach ($traverse as $rel) {
                    $firstSegment = explode('.', $rel)[0];
                    try {
                        $relMethod = QueryEngineIntrospector::resolveRelationMethod(get_class($item), $firstSegment);
                        $related = $item->$relMethod;
                        if (! $related) {
                            $row[$firstSegment] = '';

                            continue;
                        }
                        $related = $related instanceof Model ? collect([$related]) : $related;
                        $row[$firstSegment] = $related->pluck('name')->filter()->join(', ');
                    } catch (\Throwable) {
                        $row[$firstSegment] = '';
                    }
                }

                $rows[] = $row;
            }
        }

        return new ListResult(rows: collect($rows), columns: array_keys($rows[0] ?? []));
    }

    /**
     * Expansion récursive d'un objet en N lignes (produit cartésien).
     * L'ordre des colonnes respecte l'ordre original de $fields.
     */
    protected function expandRow(Model $item, array $fields): array
    {
        $rootFields = [];
        $relationFields = [];

        foreach ($fields as $field) {
            $parts = explode('.', $field, 2);
            if (count($parts) === 1) {
                $rootFields[] = $field;
            } else {
                $relationFields[$parts[0]][] = $parts[1];
            }
        }

        // Champs racine
        $rootRow = [];
        $modelClass = get_class($item);
        foreach ($rootFields as $f) {
            QueryEngineIntrospector::validateField($modelClass, $f);
            if ($this->isHidden($item, $f)) {
                continue;
            }
            $rootRow[$f] = $this->normalizeValue($item->getAttribute($f));
        }

        if (empty($relationFields)) {
            return [$rootRow];
        }

        $result = [$rootRow];

        foreach ($relationFields as $relation => $subFields) {
            try {
                $relMethod = QueryEngineIntrospector::resolveRelationMethod(get_class($item), $relation);
                $related = $item->$relMethod;
            } catch (\Throwable) {
                continue;
            }

            $related = $related
                ? ($related instanceof Model ? collect([$related]) : $related)
                : collect();

            if ($related->isEmpty()) {
                foreach ($result as &$row) {
                    foreach ($subFields as $sf) {
                        $row[$relation.'.'.$sf] = null;
                    }
                }
                unset($row);

                continue;
            }

            $newResult = [];
            foreach ($result as $row) {
                foreach ($related as $relatedItem) {
                    foreach ($this->expandRow($relatedItem, $subFields) as $subRow) {
                        $newRow = $row;
                        foreach ($subRow as $k => $v) {
                            $newRow[$relation.'.'.$k] = $v;
                        }
                        $newResult[] = $newRow;
                    }
                }
            }
            $result = $newResult;
        }

        // ── Réordonner chaque ligne selon l'ordre original de $fields ──
        return array_map(function (array $row) use ($fields): array {
            $ordered = [];
            foreach ($fields as $key) {
                if (array_key_exists($key, $row)) {
                    $ordered[$key] = $row[$key];
                }
            }
            // Clés non déclarées dans $fields ajoutées en fin
            foreach ($row as $k => $v) {
                if (! array_key_exists($k, $ordered)) {
                    $ordered[$k] = $v;
                }
            }

            return $ordered;
        }, $result);
    }

    // ─────────────────────────────────────────────────────────────
    // Liste groupée — GROUP BY
    // ─────────────────────────────────────────────────────────────

    /**
     * Construit les lignes regroupées : une ligne par valeur distincte des
     * champs de regroupement, les autres colonnes contenant la liste des
     * valeurs distinctes séparées par une virgule (à la GROUP_CONCAT).
     *
     * Quand tous les champs de regroupement sont des champs racine, les
     * relations sont parcourues directement, sans produit cartésien.
     */
    protected function buildGroupedRows(Collection $items, array $fields, array $groupBy): array
    {
        $rootOnly = collect($groupBy)->every(fn (string $f) => ! str_contains($f, '.'));

        /** @var array<string, array<string, list<string|int|float>>> $groups */
        $groups = [];

        foreach ($items as $item) {
            $partials = $rootOnly
                ? [$this->collectItemValues($item, $fields)]
                : array_map(
                    fn (array $row) => array_map(fn ($v) => $this->toCellValues($v), $row),
                    $this->expandRow($item, $fields)
                );

            foreach ($partials as $partial) {
                $key = json_encode(array_map(fn (string $f) => $partial[$f] ?? [], $groupBy));

                if (! isset($groups[$key])) {
                    $groups[$key] = $partial;

                    continue;
                }

                foreach ($partial as $column => $values) {
                    $groups[$key][$column] = array_values(array_unique(
                        array_merge($groups[$key][$column] ?? [], $values)
                    ));
                }
            }
        }

        return array_map(function (array $group) use ($groupBy): array {
            $row = [];
            foreach ($group as $column => $values) {
                $row[$column] = in_array($column, $groupBy, true)
                    ? ($values[0] ?? null)
                    : implode(', ', $values);
            }

            return $row;
        }, array_values($groups));
    }

    /**
     * Collecte, pour un objet racine, les valeurs distinctes de chaque champ
     * (y compris les champs de relations, à n'importe quelle profondeur).
     * Les champs masqués ($hidden) sont omis, comme dans expandRow().
     *
     * @return array<string, list<string|int|float>>
     */
    protected function collectItemValues(Model $item, array $fields): array
    {
        $row = [];

        foreach ($fields as $field) {
            $values = $this->collectPathValues($item, explode('.', $field));
            if ($values !== null) {
                $row[$field] = array_values(array_unique($values));
            }
        }

        return $row;
    }

    /**
     * Retourne les valeurs atteintes par un chemin pointé, ou null si le
     * champ final est masqué.
     *
     * @return list<string|int|float>|null
     */
    protected function collectPathValues(Model $item, array $parts): ?array
    {
        if (count($parts) === 1) {
            $field = $parts[0];
            QueryEngineIntrospector::validateField(get_class($item), $field);
            if ($this->isHidden($item, $field)) {
                return null;
            }

            return $this->toCellValues($this->normalizeValue($item->getAttribute($field)));
        }

        $relation = array_shift($parts);
        try {
            $relMethod = QueryEngineIntrospector::resolveRelationMethod(get_class($item), $relation);
            $related = $item->$relMethod;
        } catch (\Throwable) {
            return [];
        }

        $related = $related
            ? ($related instanceof Model ? collect([$related]) : $related)
            : collect();

        $values = [];
        foreach ($related as $relatedItem) {
            $sub = $this->collectPathValues($relatedItem, $parts);
            if ($sub === null) {
                return null;
            }
            array_push($values, ...$sub);
        }

        return $values;
    }

    /**
     * Normalise une valeur de cellule en liste de valeurs (vide si null ou '') :
     * les nombres sont conservés tels quels, le reste est converti en chaîne.
     *
     * @return list<string|int|float>
     */
    protected function toCellValues(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        if (is_bool($value)) {
            return [$value ? '1' : '0'];
        }
        if (is_int($value) || is_float($value)) {
            return [$value];
        }
        if (is_scalar($value)) {
            return [(string) $value];
        }

        return [json_encode($value)];
    }

    // ─────────────────────────────────────────────────────────────
    // Graphe
    // ─────────────────────────────────────────────────────────────

    protected function buildGraph(
        Collection $items,
        string $modelName,
        array $traverse,
    ): GraphResult {
        $this->visitedNodes = [];
        $this->expandedPaths = [];
        $this->nodes = [];
        $this->edges = [];
        $this->truncated = false;

        // Normaliser une seule fois : chaque item devient [[{name,hidden},...],...]
        $normalizedTraverse = array_map([self::class, 'normalizeSegments'], $traverse);

        foreach ($items as $item) {
            if ($this->truncated) {
                break;
            }
            $this->traverseNode($item, $modelName, $normalizedTraverse, null);
        }

        return new GraphResult(
            nodes: collect(array_values($this->nodes)),
            edges: collect(array_values($this->edges))
                ->unique(fn ($e) => $e['from'].'||'.$e['to'])
                ->values(),
            truncated: $this->truncated,
        );
    }

    protected function traverseNode(
        Model $item,
        string $modelName,
        array $normalizedTraverse,   // [[{name,hidden},...],...]
        ?string $parentNodeId
    ): void {
        $nodeId = $modelName.'_'.$item->getKey();

        if (! isset($this->visitedNodes[$nodeId]) && count($this->nodes) >= self::MAX_GRAPH_NODES) {
            $this->truncated = true;

            return;
        }

        if ($parentNodeId !== null && $parentNodeId !== $nodeId) {
            $this->edges[$parentNodeId.'||'.$nodeId] = ['from' => $parentNodeId, 'to' => $nodeId];
        }

        if (! isset($this->visitedNodes[$nodeId])) {
            $this->visitedNodes[$nodeId] = true;
            $this->nodes[$nodeId] = $this->buildNode($item, $nodeId, $modelName);
        }

        if (empty($normalizedTraverse)) {
            return;
        }

        // Un nœud déjà développé avec exactement le même sous-chemin de traversée ne l'est
        // pas une seconde fois : sur un graphe fortement connecté (many-to-many, cycles), le
        // re-développer à chaque chemin d'accès entraîne une explosion combinatoire.
        $expansionKey = $nodeId.'#'.md5(serialize($normalizedTraverse));
        if (isset($this->expandedPaths[$expansionKey])) {
            return;
        }
        $this->expandedPaths[$expansionKey] = true;

        // Regrouper par relation de premier niveau
        $relationMap = [];
        foreach ($normalizedTraverse as $segments) {
            if (empty($segments)) {
                continue;
            }
            $first = $segments[0];
            $rest = array_slice($segments, 1);
            $mapKey = $first['name'].($first['hidden'] ? ':h' : ':v');

            if (! isset($relationMap[$mapKey])) {
                $relationMap[$mapKey] = ['name' => $first['name'], 'hidden' => $first['hidden'], 'sub' => []];
            }
            if (! empty($rest)) {
                $relationMap[$mapKey]['sub'][] = $rest;
            }
        }

        foreach ($relationMap as $config) {
            try {
                $relMethod = QueryEngineIntrospector::resolveRelationMethod(get_class($item), $config['name']);
                $related = $item->$relMethod;
                if (! $related) {
                    continue;
                }

                $related = $related instanceof Model ? collect([$related]) : $related;
                if ($related->isEmpty()) {
                    continue;
                }

                $relatedModelName = class_basename(get_class($related->first()));

                foreach ($related as $relatedItem) {
                    if ($config['hidden']) {
                        // Segment masqué : pas de nœud/arête, on traverse avec $nodeId comme ancêtre visible
                        $this->traverseNodeSkip($relatedItem, $relatedModelName, $config['sub'], $nodeId);
                    } else {
                        $this->traverseNode($relatedItem, $relatedModelName, $config['sub'], $nodeId);
                    }
                }
            } catch (\Throwable) {
                continue;
            }
        }
    }

    /**
     * Traverse un nœud masqué : ni nœud ni arête créés.
     * Les enfants visibles sont liés directement à $visibleAncestorId.
     */
    protected function traverseNodeSkip(
        Model $item,
        string $modelName,
        array $normalizedTraverse,   // segments restants après le masqué
        string $visibleAncestorId
    ): void {
        if (empty($normalizedTraverse)) {
            return; // no-op : dernier segment masqué
        }

        if ($this->truncated) {
            return;
        }

        // Même garde-fou anti-réexploration que traverseNode(), sur (nœud masqué, chemin
        // restant, ancêtre visible) puisqu'un nœud masqué n'a pas d'identifiant de graphe propre.
        $skipKey = $modelName.'_'.$item->getKey().'#skip#'.$visibleAncestorId.'#'.md5(serialize($normalizedTraverse));
        if (isset($this->expandedPaths[$skipKey])) {
            return;
        }
        $this->expandedPaths[$skipKey] = true;

        $relationMap = [];
        foreach ($normalizedTraverse as $segments) {
            if (empty($segments)) {
                continue;
            }
            $first = $segments[0];
            $rest = array_slice($segments, 1);
            $mapKey = $first['name'].($first['hidden'] ? ':h' : ':v');

            if (! isset($relationMap[$mapKey])) {
                $relationMap[$mapKey] = ['name' => $first['name'], 'hidden' => $first['hidden'], 'sub' => []];
            }
            if (! empty($rest)) {
                $relationMap[$mapKey]['sub'][] = $rest;
            }
        }

        foreach ($relationMap as $config) {
            try {
                $relMethod = QueryEngineIntrospector::resolveRelationMethod(get_class($item), $config['name']);
                $related = $item->$relMethod;
                if (! $related) {
                    continue;
                }

                $related = $related instanceof Model ? collect([$related]) : $related;
                if ($related->isEmpty()) {
                    continue;
                }

                $relatedModelName = class_basename(get_class($related->first()));

                foreach ($related as $relatedItem) {
                    if ($config['hidden']) {
                        // Encore masqué : continuer à sauter
                        $this->traverseNodeSkip($relatedItem, $relatedModelName, $config['sub'], $visibleAncestorId);
                    } else {
                        // Premier visible : lier directement à l'ancêtre visible
                        $this->traverseNode($relatedItem, $relatedModelName, $config['sub'], $visibleAncestorId);
                    }
                }
            } catch (\Throwable) {
                continue;
            }
        }
    }

    protected function buildNode(Model $item, string $nodeId, string $modelName): array
    {
        return [
            'id' => $nodeId,
            'label' => $item->name ?? $item->getAttribute('label') ?? (string) $item->getKey(),
            'group' => $modelName,
            'icon' => $item instanceof HasIconContract ? $item->getIcon() : '/images/default.png',
            'data' => [
                'id' => $item->getKey(),
                'name' => $item->name ?? '',
                'model' => $modelName,
                'url' => $this->resolveUrl($modelName, $item->getKey()),
            ],
        ];
    }

    protected function resolveUrl(string $modelName, int|string $id): string
    {
        $slug = QueryEngineIntrospector::modelToApiName($modelName);

        return "/admin/{$slug}/{$id}";
    }
}
