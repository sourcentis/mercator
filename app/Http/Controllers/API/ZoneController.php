<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyZoneRequest;
use App\Http\Requests\MassStoreZoneRequest;
use App\Http\Requests\MassUpdateZoneRequest;
use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Models\Zone;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ZoneController extends APIController
{
    protected string $modelClass = Zone::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('zone_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreZoneRequest $request)
    {
        abort_if(Gate::denies('zone_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->except(['parentZones', 'childZones', 'buildings', 'adminUsers']);
        $data['attributes'] = implode(' ', (array) ($data['attributes'] ?? []));

        /** @var Zone $zone */
        $zone = Zone::create($data);

        if ($request->has('parentZones') && $request->input('parentZones') !== null) {
            $zone->parentZones()->sync($request->input('parentZones', []));
        }
        if ($request->has('childZones') && $request->input('childZones') !== null) {
            $zone->childZones()->sync($request->input('childZones', []));
        }
        if ($request->has('buildings') && $request->input('buildings') !== null) {
            $zone->buildings()->sync($request->input('buildings', []));
        }
        if ($request->has('adminUsers') && $request->input('adminUsers') !== null) {
            $zone->adminUsers()->sync($request->input('adminUsers', []));
        }

        return response()->json($zone, Response::HTTP_CREATED);
    }

    public function show(Zone $zone)
    {
        abort_if(Gate::denies('show-object', $zone), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $zone['parentZones'] = $zone->parentZones()->pluck('id');
        $zone['childZones'] = $zone->childZones()->pluck('id');
        $zone['buildings'] = $zone->buildings()->pluck('id');
        $zone['adminUsers'] = $zone->adminUsers()->pluck('id');

        return new JsonResource($zone);
    }

    public function update(UpdateZoneRequest $request, Zone $zone)
    {
        abort_if(Gate::denies('edit-object', $zone), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->except(['parentZones', 'childZones', 'buildings', 'adminUsers']);
        $data['attributes'] = implode(' ', (array) ($data['attributes'] ?? []));

        $zone->update($data);

        if ($request->has('parentZones') && $request->input('parentZones') !== null) {
            $zone->parentZones()->sync($request->input('parentZones', []));
        }
        if ($request->has('childZones') && $request->input('childZones') !== null) {
            $zone->childZones()->sync($request->input('childZones', []));
        }
        if ($request->has('buildings') && $request->input('buildings') !== null) {
            $zone->buildings()->sync($request->input('buildings', []));
        }
        if ($request->has('adminUsers') && $request->input('adminUsers') !== null) {
            $zone->adminUsers()->sync($request->input('adminUsers', []));
        }

        return response()->json();
    }

    public function destroy(Zone $zone)
    {
        abort_if(Gate::denies('zone_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $zone->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyZoneRequest $request)
    {
        abort_if(Gate::denies('zone_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Zone::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreZoneRequest $request)
    {
        $createdIds = [];
        $fillable = (new Zone)->getFillable();

        foreach ($request->input('items', []) as $item) {
            $parentZones = $item['parentZones'] ?? null;
            $childZones = $item['childZones'] ?? null;
            $buildings = $item['buildings'] ?? null;
            $adminUsers = $item['adminUsers'] ?? null;

            $attributes = collect($item)
                ->except(['parentZones', 'childZones', 'buildings', 'adminUsers'])
                ->only($fillable)
                ->toArray();
            $attributes['attributes'] = implode(' ', (array) ($attributes['attributes'] ?? []));

            /** @var Zone $zone */
            $zone = Zone::create($attributes);

            if (array_key_exists('parentZones', $item) && $parentZones !== null) {
                $zone->parentZones()->sync($parentZones);
            }
            if (array_key_exists('childZones', $item) && $childZones !== null) {
                $zone->childZones()->sync($childZones);
            }
            if (array_key_exists('buildings', $item) && $buildings !== null) {
                $zone->buildings()->sync($buildings);
            }
            if (array_key_exists('adminUsers', $item) && $adminUsers !== null) {
                $zone->adminUsers()->sync($adminUsers);
            }

            $createdIds[] = $zone->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateZoneRequest $request)
    {
        $fillable = (new Zone)->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $parentZones = $rawItem['parentZones'] ?? null;
            $childZones = $rawItem['childZones'] ?? null;
            $buildings = $rawItem['buildings'] ?? null;
            $adminUsers = $rawItem['adminUsers'] ?? null;

            /** @var Zone $zone */
            $zone = Zone::findOrFail($id);

            $attributes = collect($rawItem)
                ->except(['id', 'parentZones', 'childZones', 'buildings', 'adminUsers'])
                ->only($fillable)
                ->toArray();
            $attributes['attributes'] = implode(' ', (array) ($attributes['attributes'] ?? []));

            $zone->update($attributes);

            if (array_key_exists('parentZones', $rawItem) && $parentZones !== null) {
                $zone->parentZones()->sync($parentZones);
            }
            if (array_key_exists('childZones', $rawItem) && $childZones !== null) {
                $zone->childZones()->sync($childZones);
            }
            if (array_key_exists('buildings', $rawItem) && $buildings !== null) {
                $zone->buildings()->sync($buildings);
            }
            if (array_key_exists('adminUsers', $rawItem) && $adminUsers !== null) {
                $zone->adminUsers()->sync($adminUsers);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
