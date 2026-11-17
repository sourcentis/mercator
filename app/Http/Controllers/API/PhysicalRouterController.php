<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyPhysicalRouterRequest;
use App\Http\Requests\MassStorePhysicalRouterRequest;
use App\Http\Requests\MassUpdatePhysicalRouterRequest;
use App\Http\Requests\StorePhysicalRouterRequest;
use App\Http\Requests\UpdatePhysicalRouterRequest;
use App\Models\PhysicalRouter;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class PhysicalRouterController extends APIController
{
    protected string $modelClass = PhysicalRouter::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('physical_router_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StorePhysicalRouterRequest $request)
    {
        abort_if(Gate::denies('physical_router_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var PhysicalRouter $physicalRouter */
        $physicalRouter = PhysicalRouter::query()->create($request->all());

        $physicalRouter->vlans()->sync($request->input('vlans', []));
        $physicalRouter->routers()->sync($request->input('routers', []));

        return response()->json($physicalRouter, 201);
    }

    public function show(PhysicalRouter $physicalRouter)
    {
        abort_if(Gate::denies('show-object', $physicalRouter), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $physicalRouter['vlans'] = $physicalRouter->vlans()->pluck('id');
        $physicalRouter['routers'] = $physicalRouter->routers()->pluck('id');

        return new JsonResource($physicalRouter);
    }

    public function update(UpdatePhysicalRouterRequest $request, PhysicalRouter $physicalRouter)
    {
        abort_if(Gate::denies('edit-object', $physicalRouter), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $physicalRouter->update($request->all());

        if ($request->has('vlans')) {
            $physicalRouter->vlans()->sync($request->input('vlans', []));
        }
        if ($request->has('routers')) {
            $physicalRouter->routers()->sync($request->input('routers', []));
        }

        return response()->json();
    }

    public function destroy(PhysicalRouter $physicalRouter)
    {
        abort_if(Gate::denies('physical_router_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $physicalRouter->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyPhysicalRouterRequest $request)
    {
        abort_if(Gate::denies('physical_router_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        PhysicalRouter::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStorePhysicalRouterRequest $request)
    {
        // authorize() in the FormRequest already checks `physical_router_create`

        $createdIds = [];
        $physicalRouterModel = new PhysicalRouter;
        $fillable = $physicalRouterModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            $vlans = $item['vlans'] ?? null;

            // Model columns only (no relations)
            $attributes = collect($item)
                ->except(['vlans'])
                ->only($fillable)
                ->toArray();

            /** @var PhysicalRouter $physicalRouter */
            $physicalRouter = PhysicalRouter::query()->create($attributes);

            if (array_key_exists('vlans', $item)) {
                $physicalRouter->vlans()->sync($vlans ?? []);
            }

            $createdIds[] = $physicalRouter->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdatePhysicalRouterRequest $request)
    {
        // authorize() in the FormRequest already checks `physical_router_edit`
        $physicalRouterModel = new PhysicalRouter;
        $fillable = $physicalRouterModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $vlans = $rawItem['vlans'] ?? null;

            /** @var PhysicalRouter $physicalRouter */
            $physicalRouter = PhysicalRouter::query()->findOrFail($id);

            // Model columns only (no id or relations)
            $attributes = collect($rawItem)
                ->except(['id', 'vlans'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $physicalRouter->update($attributes);
            }

            if (array_key_exists('vlans', $rawItem)) {
                $physicalRouter->vlans()->sync($vlans ?? []);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
