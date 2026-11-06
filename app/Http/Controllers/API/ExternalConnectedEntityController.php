<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyExternalConnectedEntityRequest;
use App\Http\Requests\MassStoreExternalConnectedEntityRequest;
use App\Http\Requests\MassUpdateExternalConnectedEntityRequest;
use App\Http\Requests\StoreExternalConnectedEntityRequest;
use App\Http\Requests\UpdateExternalConnectedEntityRequest;
use App\Models\ExternalConnectedEntity;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ExternalConnectedEntityController extends APIController
{
    protected string $modelClass = ExternalConnectedEntity::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('external_connected_entity_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreExternalConnectedEntityRequest $request)
    {
        abort_if(Gate::denies('external_connected_entity_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var ExternalConnectedEntity $externalConnectedEntity */
        $externalConnectedEntity = ExternalConnectedEntity::query()->create($request->all());

        $externalConnectedEntity->subnetworks()->sync($request->input('subnetworks', []));

        return response()->json($externalConnectedEntity, Response::HTTP_CREATED);
    }

    public function show(ExternalConnectedEntity $externalConnectedEntity)
    {
        abort_if(Gate::denies('show-object', $externalConnectedEntity), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $externalConnectedEntity['subnetworks'] = $externalConnectedEntity->subnetworks()->pluck('id');

        return new JsonResource($externalConnectedEntity);
    }

    public function update(UpdateExternalConnectedEntityRequest $request, ExternalConnectedEntity $externalConnectedEntity)
    {
        abort_if(Gate::denies('edit-object', $externalConnectedEntity), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $externalConnectedEntity->update($request->all());

        if ($request->has('subnetworks')) {
            $externalConnectedEntity->subnetworks()->sync($request->input('subnetworks', []));
        }

        return response()->json();
    }

    public function destroy(ExternalConnectedEntity $externalConnectedEntity)
    {
        abort_if(Gate::denies('external_connected_entity_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $externalConnectedEntity->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyExternalConnectedEntityRequest $request)
    {
        abort_if(Gate::denies('external_connected_entity_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        ExternalConnectedEntity::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreExternalConnectedEntityRequest $request)
    {
        // L’authorize() du FormRequest gère déjà external_connected_entity_create
        $createdIds = [];

        $model = new ExternalConnectedEntity;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $item) {
            // $roles = $item['roles'] ?? null;

            // Ne garde que les colonnes du modèle, sans les relations
            $attributes = collect($item)
                ->except([
                    // 'roles',
                ])
                ->only($fillable)
                ->toArray();

            /** @var ExternalConnectedEntity $externalConnectedEntity */
            $externalConnectedEntity = ExternalConnectedEntity::query()->create($attributes);

            // if (array_key_exists('roles', $item)) {
            //     $externalConnectedEntity->roles()->sync($roles ?? []);
            // }

            $createdIds[] = $externalConnectedEntity->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateExternalConnectedEntityRequest $request)
    {
        // L’authorize() du FormRequest gère déjà external_connected_entity_edit
        $model = new ExternalConnectedEntity;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            // $roles = $rawItem['roles'] ?? null;

            /** @var ExternalConnectedEntity $externalConnectedEntity */
            $externalConnectedEntity = ExternalConnectedEntity::query()->findOrFail($id);

            // Ne garde que les colonnes du modèle, sans l'id ni les relations
            $attributes = collect($rawItem)
                ->except([
                    'id',
                    // 'roles',
                ])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $externalConnectedEntity->update($attributes);
            }

            // if (array_key_exists('roles', $rawItem)) {
            //     $externalConnectedEntity->roles()->sync($roles ?? []);
            // }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
