<?php

namespace App\Http\Controllers;

use App\Http\Requests\MassDestroySavedQueryRequest;
use App\Http\Requests\StoreSavedQueryRequest;
use App\Http\Requests\UpdateSavedQueryRequest;
use App\Models\Cartographer;
use App\Models\Permission;
use App\Models\SavedQuery;
use App\Services\QueryEngine\GraphResult;
use App\Services\QueryEngine\QueryDslValidator;
use App\Services\QueryEngine\QueryEngineIntrospector;
use App\Services\QueryEngine\QueryResolver;
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class QueryController extends Controller
{
    public function __construct(protected QueryResolver $resolver) {}

    /**
     * Liste des requêtes visibles par l'utilisateur courant.
     */
    public function index(): View
    {
        $queries = SavedQuery::visibleBy(auth()->id())
            ->with('user:id,name')
            ->orderBy('name')
            ->get();

        return view('queries.index', compact('queries'));
    }

    /**
     * Formulaire de création.
     */
    public function create(Request $request): View
    {
        // Pré-remplissage depuis le query engine (DSL passé en session)
        $query = new SavedQuery([
            'query' => $request->old('query', session('qe_last_query', [
                'from' => '',
                'select' => [],
                'filters' => [],
                'traverse' => [],
                'output' => 'list',
                'limit' => 100,
            ])),
        ]);

        return view('queries.edit', compact('query'));
    }

    /**
     * Enregistrement d'une nouvelle requête.
     */
    public function store(StoreSavedQueryRequest $request)
    {
        abort_if(Gate::denies('query_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = SavedQuery::query()->create($request->validated());
        $query->user_id = auth()->id();
        $query->save();

        // return response()->json($query, Response::HTTP_CREATED);
        return redirect()
            ->route('admin.queries.index');
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(SavedQuery $query): View
    {
        $this->authorizeOwner($query);

        return view('queries.form', compact('query'));
    }

    /**
     * Mise à jour.
     */
    public function update(UpdateSavedQueryRequest $request, SavedQuery $query): RedirectResponse
    {
        $this->authorizeOwner($query);

        $query->update($request->validated());

        return redirect()
            ->route('admin.queries.index');
    }

    /**
     * Suppression.
     */
    public function destroy(SavedQuery $query): RedirectResponse
    {
        $this->authorizeOwner($query);

        $name = $query->name;
        $query->delete();

        return redirect()
            ->route('admin.queries.index');
    }

    /**
     * Duplication d'une requête.
     */
    public function duplicate(SavedQuery $query): RedirectResponse
    {
        abort_if(Gate::denies('query_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $copy = $query->duplicate();
        $copy->save();

        return redirect()
            ->route('admin.queries.edit', $copy);
    }

    public function show(SavedQuery $query): View
    {
        $exportUrl = URL::temporarySignedRoute(
            'queries.export.signed',
            now()->addDays(30),
            ['query' => $query->id],
        );

        return view('queries.show',
            compact('query', 'exportUrl'));
    }

    /**
     * Exécute un DSL et retourne le résultat JSON.
     */
    public function execute(Request $request): JsonResponse
    {
        // Exécuter est une lecture : query_show suffit, query_create reste accepté (#2760).
        // Les droits sur les objets interrogés sont vérifiés par collectDeniedModels().
        abort_if(Gate::none(['query_show', 'query_create']), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dsl = QueryDslValidator::validate($request->all());

        $denied = $this->collectDeniedModels($dsl);
        if (! empty($denied)) {
            return response()->json([
                'message' => 'Accès refusé. Vous n\'avez pas le droit de consulter : '.implode(', ', $denied).'.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $result = $this->resolver->execute($dsl);

        $response = $result->toArray();

        // Métadonnées pour l'UI
        $response['meta'] = [
            'output' => $dsl['output'] ?? 'list',
            'from' => $dsl['from'],
            'count' => $result instanceof GraphResult
                ? $result->nodeCount()
                : $result->rowCount(),
            'truncated' => $result instanceof GraphResult && $result->truncated,
        ];

        return response()->json($response);
    }

    /**
     * Liste tous les modèles disponibles.
     */
    public function schema(): JsonResponse
    {
        abort_if(Gate::denies('query_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response()->json([
            'models' => QueryEngineIntrospector::listModels(),
        ]);
    }

    /**
     * Décrit un modèle : colonnes + relations.
     */
    public function schemaModel(string $model): JsonResponse
    {
        abort_if(Gate::denies('query_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response()->json(
            QueryEngineIntrospector::describe($model)
        );
    }

    public function massDestroy(MassDestroySavedQueryRequest $request)
    {
        abort_if(Gate::denies('queries_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        SavedQuery::query()->whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    // ─── Private ───────────────────────────────────────────────

    /**
     * Retourne les noms des modèles du DSL pour lesquels l'utilisateur
     * n'a pas la permission _show.
     */
    private function collectDeniedModels(array $dsl): array
    {
        $denied = [];

        foreach ($this->resolveInvolvedModelClasses($dsl) as $modelClass => $displayName) {
            if (! $this->canShowModel($modelClass)) {
                $denied[] = $displayName;
            }
        }

        // Les modèles sans permission _show (Perimeter, ApplicationEvent…) n'ont pas de droit
        // propre : lus en relation, ils suivent leur objet parent.
        foreach ($this->resolveFieldAndFilterModelClasses($dsl) as $modelClass => $displayName) {
            if (! $this->canShowModel($modelClass)
                && Permission::query()->where('title', $this->showPermission($modelClass))->exists()) {
                $denied[] = $displayName;
            }
        }

        return array_values(array_unique($denied));
    }

    /**
     * Collecte les modèles atteints par les relations des clauses FIELDS et WHERE.
     *
     * @return array<class-string, string> FQCN → nom court affiché
     */
    private function resolveFieldAndFilterModelClasses(array $dsl): array
    {
        try {
            $fromClass = QueryEngineIntrospector::resolveModelClass($dsl['from']);
        } catch (\Throwable) {
            return [];
        }

        $models = [];

        foreach ($dsl['fields'] ?? [] as $field) {
            $this->walkRelationPath($fromClass, array_slice(explode('.', $field), 0, -1), $models);
        }

        $this->collectFilterModelClasses($fromClass, $dsl['filters'] ?? [], $models);

        return $models;
    }

    /**
     * @param  array<string, string>  $models
     */
    private function collectFilterModelClasses(string $class, array $filters, array &$models): void
    {
        foreach ($filters as $filter) {
            $relation = $filter['exists'] ?? $filter['not_exists'] ?? null;

            if ($relation !== null) {
                $relatedClass = $this->walkRelationPath($class, [$relation], $models);
                if ($relatedClass !== null) {
                    $this->collectFilterModelClasses($relatedClass, (array) ($filter['conditions'] ?? []), $models);
                }
            } elseif (isset($filter['group'])) {
                $this->collectFilterModelClasses($class, $filter['group'], $models);
            } elseif (isset($filter['field'])) {
                $this->walkRelationPath($class, array_slice(explode('.', $filter['field']), 0, -1), $models);
            }
        }
    }

    /**
     * Suit un chemin de relations et ajoute chaque modèle atteint à $models.
     * Une relation inconnue arrête le parcours : le résolveur la rejettera en 422.
     *
     * @param  list<string>  $segments
     * @param  array<string, string>  $models
     * @return class-string|null Classe du dernier modèle atteint
     */
    private function walkRelationPath(string $class, array $segments, array &$models): ?string
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
     * Collecte toutes les classes de modèles référencées par le DSL
     * (clause FROM + chaque segment des chemins traverse).
     *
     * @return array<class-string, string> FQCN → nom court affiché
     */
    private function resolveInvolvedModelClasses(array $dsl): array
    {
        $models = [];

        try {
            $fromClass = QueryEngineIntrospector::resolveModelClass($dsl['from']);
            $models[$fromClass] = class_basename($fromClass);
        } catch (\Throwable) {
            return $models;
        }

        foreach ($dsl['traverse'] ?? [] as $traverseItem) {
            $segments = QueryResolver::normalizeSegments($traverseItem);
            $currentClass = $fromClass;

            foreach ($segments as $segment) {
                try {
                    $relations = QueryEngineIntrospector::getRelations($currentClass);
                    $relDef = collect($relations)->firstWhere('name', Str::snake($segment['name']));

                    if (! $relDef) {
                        break;
                    }

                    $relatedClass = QueryEngineIntrospector::resolveModelClassFromAny($relDef['related']);
                    $models[$relatedClass] = class_basename($relatedClass);
                    $currentClass = $relatedClass;
                } catch (\Throwable) {
                    break;
                }
            }
        }

        return $models;
    }

    /**
     * Vérifie si l'utilisateur courant peut consulter ce type de modèle,
     * soit via une permission de rôle (_show), soit en tant que cartographe.
     */
    private function canShowModel(string $modelClass): bool
    {
        if (Gate::allows($this->showPermission($modelClass))) {
            return true;
        }

        return Cartographer::hasAnyFor(auth()->user(), $modelClass);
    }

    private function showPermission(string $modelClass): string
    {
        return Str::snake(class_basename($modelClass)).'_show';
    }

    private function authorizeOwner(SavedQuery $savedQuery): void
    {
        abort_if(

            $savedQuery->user_id !== auth()->id(),
            403,
            'Vous ne pouvez modifier que vos propres requêtes.'
        );
    }
}
