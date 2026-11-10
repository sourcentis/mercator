<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroySavedQueryRequest;
use App\Http\Requests\MassStoreSavedQueryRequest;
use App\Http\Requests\MassUpdateSavedQueryRequest;
use App\Http\Requests\StoreSavedQueryRequest;
use App\Http\Requests\UpdateSavedQueryRequest;
use App\Models\SavedQuery;
use App\Services\QueryEngine\GraphResult;
use App\Services\QueryEngine\ListResult;
use App\Services\QueryEngine\QueryAccessChecker;
use App\Services\QueryEngine\QueryResolver;
use Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class QueryController extends APIController
{
    protected string $modelClass = SavedQuery::class;

    public function __construct(protected QueryResolver $resolver) {}

    /**
     * Requêtes visibles (les siennes + les publiques) ; un administrateur voit tout.
     */
    protected function newQuery(): Builder
    {
        return auth()->user()->isAdmin() ? SavedQuery::query() : SavedQuery::visibleBy(auth()->id());
    }

    public function index(Request $request)
    {
        abort_if(Gate::denies('query_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreSavedQueryRequest $request)
    {
        abort_if(Gate::denies('query_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = SavedQuery::query()->create($request->all());

        return response()->json($query, Response::HTTP_CREATED);
    }

    public function show(SavedQuery $query): JsonResource
    {
        abort_if(Gate::denies('query_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorizeRead($query);

        return $this->asJsonResource($query);
    }

    public function update(UpdateSavedQueryRequest $request, SavedQuery $query)
    {
        abort_if(Gate::denies('query_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorizeWrite($query);

        // validated() et non all() : user_id ne doit pas pouvoir être réaffecté.
        $query->update($request->validated());

        return response()->json();
    }

    /**
     * Exécute une SavedQuery et retourne le résultat en CSV.
     * Seul un résultat de type ListResult est exportable.
     * Un résultat GraphResult provoque une erreur 422.
     */
    public function execute(SavedQuery $query): StreamedResponse|JsonResponse
    {
        abort_if(Gate::denies('query_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorizeRead($query);

        $denied = QueryAccessChecker::deniedModels($query->query ?? []);
        if (! empty($denied)) {
            return response()->json(
                ['message' => QueryAccessChecker::deniedMessage($denied)],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return $this->exportCsv($query);
    }

    /**
     * Export CSV via URL signée (signature = autorisation).
     * Pas de Gate ici : aucun utilisateur authentifié.
     */
    public function exportSigned(SavedQuery $query): StreamedResponse|JsonResponse
    {
        return $this->exportCsv($query);
    }

    private function exportCsv(SavedQuery $query): StreamedResponse|JsonResponse
    {
        $dsl = $query->query;

        if (empty($dsl) || empty($dsl['from'])) {
            return response()->json(
                ['message' => 'Le DSL de la requête est invalide ou incomplet.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $result = $this->resolver->execute($dsl);
        } catch (ValidationException $e) {
            return response()->json(
                ['message' => 'DSL invalide.', 'errors' => $e->errors()],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (HttpException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e->getStatusCode()
            );
        } catch (\Throwable $e) {
            return response()->json(
                ['message' => 'Erreur lors de l\'exécution de la requête.', 'error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Un GraphResult n'est pas tabulaire : non exportable en CSV
        if ($result instanceof GraphResult) {
            return response()->json(
                ['message' => 'La requête retourne un graphe, pas une liste. Changez "output" en "list" pour exporter en CSV.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        /** @var ListResult $result */
        if ($result->rowCount() === 0) {
            return response()->json(
                ['message' => 'La requête ne retourne aucun résultat.'],
                Response::HTTP_NO_CONTENT
            );
        }

        $filename = str($query->name)->slug()->append('.csv')->toString();

        return response()->streamDownload(function () use ($result) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 pour compatibilité Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // En-têtes : colonnes déclarées par le ListResult
            fputcsv($handle, $result->columns, ';');

            // Lignes
            foreach ($result->rows as $row) {
                $row = is_object($row) ? (array) $row : $row;

                // S'assurer que l'ordre des colonnes est respecté
                $line = array_map(fn (string $col) => $row[$col] ?? '', $result->columns);

                fputcsv($handle, $line, ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function destroy(SavedQuery $query)
    {
        abort_if(Gate::denies('query_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorizeWrite($query);

        $this->destroyResource($query);

        return response()->json();
    }

    public function massDestroy(MassDestroySavedQueryRequest $request)
    {
        abort_if(Gate::denies('query_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->manageableQueries()->whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreSavedQueryRequest $request)
    {
        $items = array_map(
            fn (array $item) => array_merge($item, ['user_id' => auth()->id()]),
            $request->input('items', [])
        );

        $createdIds = $this->massStoreItems($items);

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateSavedQueryRequest $request)
    {
        $items = $request->input('items', []);
        $ids = collect($items)->pluck('id')->unique();

        abort_if(
            $this->manageableQueries()->whereIn('id', $ids)->count() !== $ids->count(),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );

        $this->massUpdateItems(array_map(fn (array $item) => Arr::except($item, 'user_id'), $items));

        return response()->json([
            'status' => 'ok',
        ]);
    }

    private function authorizeRead(SavedQuery $query): void
    {
        abort_unless(
            auth()->user()->isAdmin() || $query->isVisibleBy(auth()->id()),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );
    }

    private function authorizeWrite(SavedQuery $query): void
    {
        abort_unless(
            auth()->user()->isAdmin() || $query->user_id === auth()->id(),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );
    }

    /**
     * Requêtes modifiables : les siennes ; un administrateur les gère toutes.
     */
    private function manageableQueries(): Builder
    {
        return SavedQuery::query()->when(
            ! auth()->user()->isAdmin(),
            fn (Builder $query) => $query->where('user_id', auth()->id())
        );
    }
}
