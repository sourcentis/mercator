<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyGraphRequest;
use App\Http\Requests\MassStoreGraphRequest;
use App\Http\Requests\MassUpdateGraphRequest;
use App\Http\Requests\StoreGraphRequest;
use App\Http\Requests\UpdateGraphRequest;
use App\Models\Graph;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class GraphController extends APIController
{
    protected string $modelClass = Graph::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('graph_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreGraphRequest $request)
    {
        abort_if(Gate::denies('graph_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $graph = Graph::query()->create($request->all());

        return response()->json($graph, Response::HTTP_CREATED);
    }

    public function show(Graph $graph): JsonResource
    {
        abort_if(Gate::denies('show-object', $graph), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // On encapsule le modèle dans une JsonResource pour rester cohérent
        return $this->asJsonResource($graph);
    }

    public function update(UpdateGraphRequest $request, Graph $graph)
    {
        abort_if(Gate::denies('edit-object', $graph), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $graph->update($request->all());

        return response()->json();
    }

    public function destroy(Graph $graph)
    {
        abort_if(Gate::denies('graph_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->destroyResource($graph);

        return response()->json();
    }

    public function massDestroy(MassDestroyGraphRequest $request)
    {
        abort_if(Gate::denies('graph_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->massDestroyByIds($request->input('ids', []));

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreGraphRequest $request)
    {
        $createdIds = $this->massStoreItems($request->input('items', []));

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateGraphRequest $request)
    {
        $this->massUpdateItems($request->input('items', []));

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
