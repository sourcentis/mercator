<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyPhysicalLinkRequest;
use App\Http\Requests\MassStorePhysicalLinkRequest;
use App\Http\Requests\MassUpdatePhysicalLinkRequest;
use App\Http\Requests\StorePhysicalLinkRequest;
use App\Http\Requests\UpdatePhysicalLinkRequest;
use App\Models\PhysicalLink;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class PhysicalLinkController extends APIController
{
    protected string $modelClass = PhysicalLink::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('physical_link_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StorePhysicalLinkRequest $request)
    {
        abort_if(Gate::denies('physical_link_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var PhysicalLink $physicalLink */
        $physicalLink = PhysicalLink::query()->create($request->all());

        return response()->json($physicalLink, 201);
    }

    public function show(PhysicalLink $physicalLink)
    {
        abort_if(Gate::denies('show-object', $physicalLink), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($physicalLink);
    }

    public function update(UpdatePhysicalLinkRequest $request, PhysicalLink $physicalLink)
    {
        abort_if(Gate::denies('edit-object', $physicalLink), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $physicalLink->update($request->all());

        return response()->json();
    }

    public function destroy(PhysicalLink $physicalLink)
    {
        abort_if(Gate::denies('physical_link_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $physicalLink->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyPhysicalLinkRequest $request)
    {
        abort_if(Gate::denies('physical_link_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        PhysicalLink::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStorePhysicalLinkRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `physical_link_create`

        $createdIds = [];
        $physicalLinkModel = new PhysicalLink;
        $fillable = $physicalLinkModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Colonnes du modèle uniquement
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var PhysicalLink $physicalLink */
            $physicalLink = PhysicalLink::query()->create($attributes);

            $createdIds[] = $physicalLink->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdatePhysicalLinkRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `physical_link_edit`
        $physicalLinkModel = new PhysicalLink;
        $fillable = $physicalLinkModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var PhysicalLink $physicalLink */
            $physicalLink = PhysicalLink::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id)
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $physicalLink->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
