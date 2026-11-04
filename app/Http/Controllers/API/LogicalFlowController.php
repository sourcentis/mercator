<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyLogicalFlowRequest;
use App\Http\Requests\MassStoreLogicalFlowRequest;
use App\Http\Requests\MassUpdateLogicalFlowRequest;
use App\Http\Requests\StoreLogicalFlowRequest;
use App\Http\Requests\UpdateLogicalFlowRequest;
use App\Models\LogicalFlow;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogicalFlowController extends APIController
{
    protected string $modelClass = LogicalFlow::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('logical_flow_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreLogicalFlowRequest $request)
    {
        Log::debug('LogicalFlowController:store Start');

        abort_if(Gate::denies('logical_flow_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var LogicalFlow $logicalFlow */
        $logicalFlow = LogicalFlow::query()->create($request->all());

        Log::debug('LogicalFlowController:store Done');

        return response()->json($logicalFlow, 201);
    }

    public function show(LogicalFlow $logicalFlow)
    {
        abort_if(Gate::denies('show-object', $logicalFlow), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($logicalFlow);
    }

    public function update(UpdateLogicalFlowRequest $request, LogicalFlow $logicalFlow)
    {
        abort_if(Gate::denies('edit-object', $logicalFlow), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logicalFlow->update($request->all());

        return response()->json();
    }

    public function destroy(LogicalFlow $logicalFlow)
    {
        abort_if(Gate::denies('logical_flow_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logicalFlow->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyLogicalFlowRequest $request)
    {
        abort_if(Gate::denies('logical_flow_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        LogicalFlow::whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreLogicalFlowRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('logical_flow_create')

        $createdIds = [];
        $logicalFlowModel = new LogicalFlow;
        $fillable = $logicalFlowModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Colonnes du modèle uniquement
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var LogicalFlow $logicalFlow */
            $logicalFlow = LogicalFlow::query()->create($attributes);

            $createdIds[] = $logicalFlow->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateLogicalFlowRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('logical_flow_edit')
        $logicalFlowModel = new LogicalFlow;
        $fillable = $logicalFlowModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var LogicalFlow $logicalFlow */
            $logicalFlow = LogicalFlow::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id)
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $logicalFlow->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
