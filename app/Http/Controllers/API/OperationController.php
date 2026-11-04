<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyOperationRequest;
use App\Http\Requests\MassStoreOperationRequest;
use App\Http\Requests\MassUpdateOperationRequest;
use App\Http\Requests\StoreOperationRequest;
use App\Http\Requests\UpdateOperationRequest;
use App\Models\Operation;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class OperationController extends APIController
{
    protected string $modelClass = Operation::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('operation_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreOperationRequest $request)
    {
        abort_if(Gate::denies('operation_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Operation $operation */
        $operation = Operation::query()->create($request->all());

        $operation->actors()->sync($request->input('actors', []));
        $operation->tasks()->sync($request->input('tasks', []));
        $operation->activities()->sync($request->input('activities', []));

        return response()->json($operation, 201);
    }

    public function show(Operation $operation)
    {
        abort_if(Gate::denies('show-object', $operation), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $operation['actors'] = $operation->actors()->pluck('id');
        $operation['tasks'] = $operation->tasks()->pluck('id');
        $operation['activities'] = $operation->activities()->pluck('id');

        return new JsonResource($operation);
    }

    public function update(UpdateOperationRequest $request, Operation $operation)
    {
        abort_if(Gate::denies('edit-object', $operation), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $operation->update($request->all());

        if ($request->has('actors')) {
            $operation->actors()->sync($request->input('actors', []));
        }

        if ($request->has('tasks')) {
            $operation->tasks()->sync($request->input('tasks', []));
        }

        if ($request->has('activities')) {
            $operation->activities()->sync($request->input('activities', []));
        }

        return response()->json();
    }

    public function destroy(Operation $operation)
    {
        abort_if(Gate::denies('operation_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $operation->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyOperationRequest $request)
    {
        abort_if(Gate::denies('operation_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Operation::whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreOperationRequest $request)
    {
        // authorize() in the FormRequest already checks `operation_create`

        $createdIds = [];
        $operationModel = new Operation;
        $fillable = $operationModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            $actors = $item['actors'] ?? null;
            $tasks = $item['tasks'] ?? null;
            $activities = $item['activities'] ?? null;

            // Only model columns, relations are excluded
            $attributes = collect($item)
                ->except(['actors', 'tasks', 'activities'])
                ->only($fillable)
                ->toArray();

            /** @var Operation $operation */
            $operation = Operation::query()->create($attributes);

            if (array_key_exists('actors', $item)) {
                $operation->actors()->sync($actors ?? []);
            }

            if (array_key_exists('tasks', $item)) {
                $operation->tasks()->sync($tasks ?? []);
            }

            if (array_key_exists('activities', $item)) {
                $operation->activities()->sync($activities ?? []);
            }

            $createdIds[] = $operation->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateOperationRequest $request)
    {
        // authorize() in the FormRequest already checks `operation_edit`
        $operationModel = new Operation;
        $fillable = $operationModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $actors = $rawItem['actors'] ?? null;
            $tasks = $rawItem['tasks'] ?? null;
            $activities = $rawItem['activities'] ?? null;

            /** @var Operation $operation */
            $operation = Operation::query()->findOrFail($id);

            // Only model columns (no id or relations)
            $attributes = collect($rawItem)
                ->except(['id', 'actors', 'tasks', 'activities'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $operation->update($attributes);
            }

            if (array_key_exists('actors', $rawItem)) {
                $operation->actors()->sync($actors ?? []);
            }

            if (array_key_exists('tasks', $rawItem)) {
                $operation->tasks()->sync($tasks ?? []);
            }

            if (array_key_exists('activities', $rawItem)) {
                $operation->activities()->sync($activities ?? []);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
