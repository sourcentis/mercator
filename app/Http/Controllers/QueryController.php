<?php

namespace App\Http\Controllers;

use App\Http\Requests\MassDestroySavedQueryRequest;
use App\Http\Requests\StoreSavedQueryRequest;
use App\Http\Requests\UpdateSavedQueryRequest;
use App\Models\AuditLog;
use App\Models\SavedQuery;
use App\Services\QueryEngine\QueryAccessChecker;
use App\Services\QueryEngine\QueryDslValidator;
use App\Services\QueryEngine\QueryEngineIntrospector;
use App\Services\QueryEngine\QueryResolver;
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class QueryController extends Controller
{
    private const READ_ABILITIES = ['query_show', 'query_create', 'query_edit'];

    private const EDITOR_ABILITIES = ['query_create', 'query_edit'];

    public function __construct(protected QueryResolver $resolver) {}

    /**
     * Liste des requêtes visibles par l'utilisateur courant.
     */
    public function index(): View
    {
        abort_if(Gate::denies('query_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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
        abort_if(Gate::denies('query_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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
        abort_if(Gate::denies('query_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorizeOwner($query);

        return view('queries.form', compact('query'));
    }

    /**
     * Mise à jour.
     */
    public function update(UpdateSavedQueryRequest $request, SavedQuery $query): RedirectResponse
    {
        abort_if(Gate::denies('query_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
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
        abort_if(Gate::denies('query_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
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
        abort_unless($query->isVisibleBy(auth()->id()), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $copy = $query->duplicate();
        $copy->save();

        // Sans query_edit, edit() refuserait l'accès : on ouvre la copie à la place.
        return redirect()->route(
            Gate::allows('query_edit') ? 'admin.queries.edit' : 'admin.queries.show',
            $copy
        );
    }

    public function show(SavedQuery $query): View
    {
        abort_if(Gate::none(self::READ_ABILITIES), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_unless($query->isVisibleBy(auth()->id()), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $canUseEditor = Gate::any(self::EDITOR_ABILITIES);
        $canExport = $this->canExport($query);

        return view('queries.show',
            compact('query', 'canExport', 'canUseEditor'));
    }

    /**
     * Génère un lien d'export CSV signé et trace sa création.
     *
     * Le lien s'utilise sans authentification : il n'est remis qu'à un
     * utilisateur ayant query_export et le droit de lire tout le résultat.
     */
    public function exportLink(Request $request, SavedQuery $query): JsonResponse
    {
        abort_if(Gate::denies('query_export'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_unless($query->isVisibleBy(auth()->id()), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_unless($this->canReadResult($query), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $expiresAt = now()->addDays(max(1, (int) config('mercator.query.export_link_days', 90)));

        // « by » est couvert par la signature : il permet d'attribuer chaque
        // téléchargement anonyme au créateur du lien.
        $url = URL::temporarySignedRoute(
            'queries.export.signed',
            $expiresAt,
            ['query' => $query->id, 'by' => auth()->id()],
        );

        AuditLog::query()->create([
            'description' => 'export_link_created',
            'subject_id' => $query->id,
            'subject_type' => SavedQuery::class,
            'user_id' => auth()->id(),
            'properties' => [
                'name' => $query->name,
                'expires_at' => $expiresAt->toIso8601String(),
            ],
            'host' => $request->ip(),
        ]);

        return response()->json([
            'url' => $url,
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_at_label' => $expiresAt->format(config('panel.date_format').' '.config('panel.time_format')),
        ]);
    }

    /**
     * Exécute une requête enregistrée : seul son DSL stocké est pris en compte,
     * ce qui suffit aux comptes en lecture seule (#2760).
     */
    public function run(SavedQuery $query): JsonResponse
    {
        abort_if(Gate::none(self::READ_ABILITIES), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_unless($query->isVisibleBy(auth()->id()), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // « depth » n'est plus accepté par le validateur mais peut subsister en base.
        return $this->executeDsl(QueryDslValidator::validate(Arr::except($query->query ?? [], 'depth')));
    }

    /**
     * Exécute un DSL libre et retourne le résultat JSON.
     */
    public function execute(Request $request): JsonResponse
    {
        abort_if(Gate::none(self::EDITOR_ABILITIES), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->executeDsl(QueryDslValidator::validate($request->all()));
    }

    private function executeDsl(array $dsl): JsonResponse
    {
        $denied = QueryAccessChecker::deniedModels($dsl);
        if (! empty($denied)) {
            return response()->json([
                'message' => QueryAccessChecker::deniedMessage($denied),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json($this->resolver->executeToArray($dsl));
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
        abort_if(Gate::denies('query_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Même règle que destroy() : seul le propriétaire supprime ses requêtes.
        SavedQuery::query()
            ->whereIn('id', $request->input('ids', []))
            ->where('user_id', auth()->id())
            ->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    // ─── Private ───────────────────────────────────────────────

    private function canExport(SavedQuery $query): bool
    {
        return Gate::allows('query_export') && $this->canReadResult($query);
    }

    private function canReadResult(SavedQuery $query): bool
    {
        try {
            return QueryAccessChecker::deniedModels($query->query ?? []) === [];
        } catch (\Throwable) {
            return false; // DSL enregistré mal formé
        }
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
