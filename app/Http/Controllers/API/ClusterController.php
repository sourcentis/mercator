<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyClusterRequest;
use App\Http\Requests\MassStoreClusterRequest;
use App\Http\Requests\MassUpdateClusterRequest;
use App\Http\Requests\StoreClusterRequest;
use App\Http\Requests\UpdateClusterRequest;
use App\Models\Cluster;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ClusterController extends APIController
{
    protected string $modelClass = Cluster::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('cluster_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreClusterRequest $request)
    {
        Log::debug('ClusterController:store Start');

        abort_if(Gate::denies('cluster_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $cluster = Cluster::query()->create($request->all());

        $cluster->logicalServers()->sync($request->input('logical_servers', []));
        $cluster->physicalServers()->sync($request->input('physical_servers', []));
        $cluster->routers()->sync($request->input('routers', []));

        Log::debug('ClusterController:store Done');

        return response()->json($cluster, Response::HTTP_CREATED);
    }

    public function show(Cluster $cluster)
    {
        abort_if(Gate::denies('show-object', $cluster), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($cluster);
    }

    public function update(UpdateClusterRequest $request, Cluster $cluster)
    {
        abort_if(Gate::denies('edit-object', $cluster), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $cluster->update($request->all());

        if ($request->has('logical_servers')) {
            $cluster->logicalServers()->sync($request->input('logical_servers', []));
        }
        if ($request->has('physical_servers')) {
            $cluster->physicalServers()->sync($request->input('physical_servers', []));
        }
        if ($request->has('routers')) {
            $cluster->routers()->sync($request->input('routers', []));
        }

        return response()->json();
    }

    public function destroy(Cluster $cluster)
    {
        abort_if(Gate::denies('cluster_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $cluster->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyClusterRequest $request)
    {
        abort_if(Gate::denies('cluster_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Cluster::query()->whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreClusterRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('logical_server_create')
        $createdIds = [];

        $model = new Cluster;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Ne garde que les colonnes du modèle (les relations sont ignorées pour le moment)
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var Cluster $cluster */
            $cluster = Cluster::query()->create($attributes);

            // Exemple si besoin plus tard :
            // if (array_key_exists('servers', $item)) {
            //     $cluster->servers()->sync($item['servers'] ?? []);
            // }
            // if (array_key_exists('applications', $item)) {
            //     $cluster->applications()->sync($item['applications'] ?? []);
            // }

            $createdIds[] = $cluster->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateClusterRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('logical_server_edit')
        $model = new Cluster;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var Cluster $cluster */
            $cluster = Cluster::query()->findOrFail($id);

            // Ne garde que les colonnes du modèle, sans l'id
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $cluster->update($attributes);
            }

            // Exemple si besoin plus tard :
            // if (array_key_exists('servers', $rawItem)) {
            //     $cluster->servers()->sync($rawItem['servers'] ?? []);
            // }
            // if (array_key_exists('applications', $rawItem)) {
            //     $cluster->applications()->sync($rawItem['applications'] ?? []);
            // }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
