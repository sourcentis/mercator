<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyWorkstationRequest;
use App\Http\Requests\MassStoreWorkstationRequest;
use App\Http\Requests\MassUpdateWorkstationRequest;
use App\Http\Requests\StoreWorkstationRequest;
use App\Http\Requests\UpdateWorkstationRequest;
use App\Models\Workstation;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class WorkstationController extends APIController
{
    protected string $modelClass = Workstation::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('workstation_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreWorkstationRequest $request)
    {
        abort_if(Gate::denies('workstation_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $workstation = Workstation::query()->create($request->all());

        $workstation->applications()->sync($request->input('applications', []));

        return response()->json($workstation, 201);
    }

    public function show(Workstation $workstation)
    {
        abort_if(Gate::denies('show-object', $workstation), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $workstation['applications'] = $workstation->applications()->pluck('id');

        return new JsonResource($workstation);
    }

    public function update(UpdateWorkstationRequest $request, Workstation $workstation)
    {
        abort_if(Gate::denies('edit-object', $workstation), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $workstation->update($request->all());

        if ($request->has('applications')) {
            $workstation->applications()->sync($request->input('applications', []));
        }

        return response()->json();
    }

    public function destroy(Workstation $workstation)
    {
        abort_if(Gate::denies('workstation_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $workstation->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyWorkstationRequest $request)
    {
        abort_if(Gate::denies('workstation_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Workstation::query()->whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreWorkstationRequest $request)
    {
        // L’authorize() du FormRequest protège déjà l’accès

        $createdIds = [];
        $fillable = (new Workstation)->getFillable();

        foreach ($request->input('items', []) as $item) {
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var Workstation $workstation */
            $workstation = Workstation::create($attributes);
            $createdIds[] = $workstation->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateWorkstationRequest $request)
    {
        // L’authorize() du FormRequest protège déjà l’accès
        $fillable = (new Workstation)->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var Workstation $workstation */
            $workstation = Workstation::findOrFail($id);

            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $workstation->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
