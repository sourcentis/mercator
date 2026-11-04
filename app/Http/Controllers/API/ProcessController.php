<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyProcessRequest;
use App\Http\Requests\MassStoreProcessRequest;
use App\Http\Requests\MassUpdateProcessRequest;
use App\Http\Requests\StoreProcessRequest;
use App\Http\Requests\UpdateProcessRequest;
use App\Models\Operation;
use App\Models\Process;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ProcessController extends APIController
{
    protected string $modelClass = Process::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('process_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreProcessRequest $request)
    {
        abort_if(Gate::denies('process_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Process $process */
        $process = Process::query()->create($request->all());

        $process->activities()->sync($request->input('activities', []));
        $process->entities()->sync($request->input('entities', []));
        $process->information()->sync($request->input('informations', []));
        $process->applications()->sync($request->input('applications', []));

        Operation::whereIn('id', $request->input('operations', []))
            ->update(['process_id' => $process->id]);

        return response()->json($process, Response::HTTP_CREATED);
    }

    public function show(Process $process)
    {
        abort_if(Gate::denies('show-object', $process), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $process['activities'] = $process->activities()->pluck('id');
        $process['entities'] = $process->entities()->pluck('id');
        $process['informations'] = $process->information()->pluck('id');
        $process['applications'] = $process->applications()->pluck('id');
        $process['operations'] = $process->operations()->pluck('id');

        return new JsonResource($process);
    }

    public function update(UpdateProcessRequest $request, Process $process)
    {
        abort_if(Gate::denies('edit-object', $process), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $process->update($request->all());

        if ($request->has('activities')) {
            $process->activities()->sync($request->input('activities', []));
        }

        if ($request->has('entities')) {
            $process->entities()->sync($request->input('entities', []));
        }

        if ($request->has('informations')) {
            $process->information()->sync($request->input('informations', []));
        }

        if ($request->has('applications')) {
            $process->applications()->sync($request->input('applications', []));
        }

        if ($request->has('operations')) {
            Operation::whereIn('id', $request->input('operations', []))
                ->update(['process_id' => $process->id]);
        }

        return response()->json();
    }

    public function destroy(Process $process)
    {
        abort_if(Gate::denies('process_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $process->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyProcessRequest $request)
    {
        abort_if(Gate::denies('process_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Process::whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreProcessRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `process_create`

        $createdIds = [];
        $processModel = new Process;
        $fillable = $processModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            $activities = $item['activities'] ?? null;
            $entities = $item['entities'] ?? null;
            $informations = $item['informations'] ?? null;
            $applications = $item['applications'] ?? null;
            $operations = $item['operations'] ?? null;

            // Colonnes du modèle uniquement (sans les relations)
            $attributes = collect($item)
                ->except(['activities', 'entities', 'informations', 'applications', 'operations'])
                ->only($fillable)
                ->toArray();

            /** @var Process $process */
            $process = Process::query()->create($attributes);

            if (array_key_exists('activities', $item)) {
                $process->activities()->sync($activities ?? []);
            }

            if (array_key_exists('entities', $item)) {
                $process->entities()->sync($entities ?? []);
            }

            if (array_key_exists('informations', $item)) {
                $process->information()->sync($informations ?? []);
            }

            if (array_key_exists('applications', $item)) {
                $process->applications()->sync($applications ?? []);
            }

            if (array_key_exists('operations', $item) && ! empty($operations)) {
                Operation::whereIn('id', $operations)
                    ->update(['process_id' => $process->id]);
            }

            $createdIds[] = $process->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateProcessRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `process_edit`
        $processModel = new Process;
        $fillable = $processModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $activities = $rawItem['activities'] ?? null;
            $entities = $rawItem['entities'] ?? null;
            $informations = $rawItem['informations'] ?? null;
            $applications = $rawItem['applications'] ?? null;
            $operations = $rawItem['operations'] ?? null;

            /** @var Process $process */
            $process = Process::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id ni relations)
            $attributes = collect($rawItem)
                ->except(['id', 'activities', 'entities', 'informations', 'applications', 'operations'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $process->update($attributes);
            }

            if (array_key_exists('activities', $rawItem)) {
                $process->activities()->sync($activities ?? []);
            }

            if (array_key_exists('entities', $rawItem)) {
                $process->entities()->sync($entities ?? []);
            }

            if (array_key_exists('informations', $rawItem)) {
                $process->information()->sync($informations ?? []);
            }

            if (array_key_exists('applications', $rawItem)) {
                $process->applications()->sync($applications ?? []);
            }

            if (array_key_exists('operations', $rawItem) && ! empty($operations)) {
                Operation::whereIn('id', $operations)
                    ->update(['process_id' => $process->id]);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
