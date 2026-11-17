<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroySubnetworkRequest;
use App\Http\Requests\MassStoreSubnetworkRequest;
use App\Http\Requests\MassUpdateSubnetworkRequest;
use App\Http\Requests\StoreSubnetworkRequest;
use App\Http\Requests\UpdateSubnetworkRequest;
use App\Models\Subnetwork;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class SubnetworkController extends APIController
{
    protected string $modelClass = Subnetwork::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('subnetwork_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreSubnetworkRequest $request)
    {
        abort_if(Gate::denies('subnetwork_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Subnetwork $subnetwork */
        $subnetwork = Subnetwork::query()->create($request->all());

        return response()->json($subnetwork, Response::HTTP_CREATED);
    }

    public function show(Subnetwork $subnetwork)
    {
        abort_if(Gate::denies('show-object', $subnetwork), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($subnetwork);
    }

    public function update(UpdateSubnetworkRequest $request, Subnetwork $subnetwork)
    {
        abort_if(Gate::denies('edit-object', $subnetwork), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subnetwork->update($request->all());

        return response()->json();
    }

    public function destroy(Subnetwork $subnetwork)
    {
        abort_if(Gate::denies('subnetwork_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subnetwork->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroySubnetworkRequest $request)
    {
        abort_if(Gate::denies('subnetwork_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Subnetwork::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreSubnetworkRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `subnetwork_create`

        $createdIds = [];
        $subnetworkModel = new Subnetwork;
        $fillable = $subnetworkModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Colonnes du modèle uniquement
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var Subnetwork $subnetwork */
            $subnetwork = Subnetwork::query()->create($attributes);

            $createdIds[] = $subnetwork->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateSubnetworkRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `subnetwork_edit`
        $subnetworkModel = new Subnetwork;
        $fillable = $subnetworkModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var Subnetwork $subnetwork */
            $subnetwork = Subnetwork::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id)
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $subnetwork->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
