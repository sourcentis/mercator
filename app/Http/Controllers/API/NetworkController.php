<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyNetworkRequest;
use App\Http\Requests\MassStoreNetworkRequest;
use App\Http\Requests\MassUpdateNetworkRequest;
use App\Http\Requests\StoreNetworkRequest;
use App\Http\Requests\UpdateNetworkRequest;
use App\Models\Network;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class NetworkController extends APIController
{
    protected string $modelClass = Network::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('network_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreNetworkRequest $request)
    {
        abort_if(Gate::denies('network_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Network $network */
        $network = Network::query()->create($request->all());

        return response()->json($network, 201);
    }

    public function show(Network $network)
    {
        abort_if(Gate::denies('show-object', $network), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($network);
    }

    public function update(UpdateNetworkRequest $request, Network $network)
    {
        abort_if(Gate::denies('edit-object', $network), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $network->update($request->all());
        // syncs
        // $network->roles()->sync($request->input('roles', []));

        return response()->json();
    }

    public function destroy(Network $network)
    {
        abort_if(Gate::denies('network_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $network->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyNetworkRequest $request)
    {
        abort_if(Gate::denies('network_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Network::whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreNetworkRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('network_create')

        $createdIds = [];
        $model = new Network;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Colonnes du modèle uniquement
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var Network $network */
            $network = Network::query()->create($attributes);

            $createdIds[] = $network->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateNetworkRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('network_edit')
        $model = new Network;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var Network $network */
            $network = Network::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id)
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $network->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
