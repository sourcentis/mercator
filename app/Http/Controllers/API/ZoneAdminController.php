<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyZoneAdminRequest;
use App\Http\Requests\MassStoreZoneAdminRequest;
use App\Http\Requests\MassUpdateZoneAdminRequest;
use App\Http\Requests\StoreZoneAdminRequest;
use App\Http\Requests\UpdateZoneAdminRequest;
use App\Models\ZoneAdmin;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ZoneAdminController extends APIController
{
    protected string $modelClass = ZoneAdmin::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('zone_admin_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreZoneAdminRequest $request)
    {
        abort_if(Gate::denies('zone_admin_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $zoneAdmin = ZoneAdmin::create($request->all());

        return response()->json($zoneAdmin, 201);
    }

    public function show(ZoneAdmin $zoneAdmin)
    {
        abort_if(Gate::denies('show-object', $zoneAdmin), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($zoneAdmin);
    }

    public function update(UpdateZoneAdminRequest $request, ZoneAdmin $zoneAdmin)
    {
        abort_if(Gate::denies('edit-object', $zoneAdmin), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $zoneAdmin->update($request->all());

        return response()->json();
    }

    public function destroy(ZoneAdmin $zoneAdmin)
    {
        abort_if(Gate::denies('zone_admin_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $zoneAdmin->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyZoneAdminRequest $request)
    {
        abort_if(Gate::denies('zone_admin_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        ZoneAdmin::whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreZoneAdminRequest $request)
    {
        // L’authorize() du FormRequest protège déjà l’accès

        $createdIds = [];
        $fillable = (new ZoneAdmin)->getFillable();

        foreach ($request->input('items', []) as $item) {
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var ZoneAdmin $zoneAdmin */
            $zoneAdmin = ZoneAdmin::create($attributes);
            $createdIds[] = $zoneAdmin->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateZoneAdminRequest $request)
    {
        // L’authorize() du FormRequest protège déjà l’accès
        $fillable = (new ZoneAdmin)->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var ZoneAdmin $zoneAdmin */
            $zoneAdmin = ZoneAdmin::findOrFail($id);

            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $zoneAdmin->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
