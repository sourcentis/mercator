<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyTaskRequest;
use App\Http\Requests\MassStoreTaskRequest;
use App\Http\Requests\MassUpdateTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends APIController
{
    protected string $modelClass = Task::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('task_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreTaskRequest $request)
    {
        abort_if(Gate::denies('task_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Task $task */
        $task = Task::query()->create($request->all());

        return response()->json($task, Response::HTTP_CREATED);
    }

    public function show(Task $task)
    {
        abort_if(Gate::denies('show-object', $task), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        abort_if(Gate::denies('edit-object', $task), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $task->update($request->all());

        return response()->json();
    }

    public function destroy(Task $task)
    {
        abort_if(Gate::denies('task_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $task->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyTaskRequest $request)
    {
        abort_if(Gate::denies('task_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Task::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreTaskRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `task_create`

        $createdIds = [];
        $taskModel = new Task;
        $fillable = $taskModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Colonnes du modèle uniquement (pas de relations ici)
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var Task $task */
            $task = Task::query()->create($attributes);

            $createdIds[] = $task->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateTaskRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `task_edit`
        $taskModel = new Task;
        $fillable = $taskModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var Task $task */
            $task = Task::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id)
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $task->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
