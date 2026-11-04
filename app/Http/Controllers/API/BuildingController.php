<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyBuildingRequest;
use App\Http\Requests\MassStoreBuildingRequest;
use App\Http\Requests\MassUpdateBuildingRequest;
use App\Http\Requests\StoreBuildingRequest;
use App\Http\Requests\UpdateBuildingRequest;
use App\Models\Building;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class BuildingController extends APIController
{
    protected string $modelClass = Building::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('building_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreBuildingRequest $request)
    {
        abort_if(Gate::denies('building_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $building = Building::create($request->all());

        return response()->json($building, 201);
    }

    public function show(Building $building)
    {
        abort_if(Gate::denies('show-object', $building), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($building);
    }

    public function update(UpdateBuildingRequest $request, Building $building)
    {
        abort_if(Gate::denies('edit-object', $building), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $building->update($request->all());

        return response()->json();
    }

    public function destroy(Building $building)
    {
        abort_if(Gate::denies('building_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $building->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyBuildingRequest $request)
    {
        abort_if(Gate::denies('building_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Building::whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreBuildingRequest $request)
    {
        // L’authorize() du FormRequest protège déjà l’accès

        $createdIds = [];
        $fillable = (new Building)->getFillable();

        foreach ($request->input('items', []) as $item) {
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var Building $building */
            $building = Building::create($attributes);
            $createdIds[] = $building->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateBuildingRequest $request)
    {
        // L’authorize() du FormRequest protège déjà l’accès
        $fillable = (new Building)->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var Building $building */
            $building = Building::findOrFail($id);

            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $building->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
