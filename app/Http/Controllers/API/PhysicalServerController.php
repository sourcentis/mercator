<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyPhysicalServerRequest;
use App\Http\Requests\MassStorePhysicalServerRequest;
use App\Http\Requests\MassUpdatePhysicalServerRequest;
use App\Http\Requests\StorePhysicalServerRequest;
use App\Http\Requests\UpdatePhysicalServerRequest;
use App\Models\PhysicalServer;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class PhysicalServerController extends APIController
{
    protected string $modelClass = PhysicalServer::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('physical_server_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StorePhysicalServerRequest $request)
    {
        abort_if(Gate::denies('physical_server_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $physicalServer = PhysicalServer::query()->create($request->all());
        $physicalServer->applications()->sync($request->input('applications', []));
        $physicalServer->clusters()->sync($request->input('clusters', []));

        // Support for logical servers association via API
        if ($request->has('logical_servers')) {
            $logicalServerIds = $request->input('logical_servers', []);
            $physicalServer->logicalServers()->sync($logicalServerIds);
        }

        return response()->json($physicalServer, 201);
    }

    public function show(PhysicalServer $physicalServer)
    {
        abort_if(Gate::denies('show-object', $physicalServer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $physicalServer['applications'] = $physicalServer->applications()->pluck('id');
        $physicalServer['clusters'] = $physicalServer->clusters()->pluck('id');
        $physicalServer['logical_servers'] = $physicalServer->logicalServers()->pluck('id');

        return new JsonResource($physicalServer);
    }

    public function update(UpdatePhysicalServerRequest $request, PhysicalServer $physicalServer)
    {
        abort_if(Gate::denies('edit-object', $physicalServer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $physicalServer->update($request->all());

        if ($request->has('logical_servers')) {
            $physicalServer->logicalServers()->sync($request->input('logical_servers', []));
        }

        if ($request->has('applications')) {
            $physicalServer->applications()->sync($request->input('applications', []));
        }

        if ($request->has('clusters')) {
            $physicalServer->clusters()->sync($request->input('clusters', []));
        }

        return response()->json();
    }

    public function destroy(PhysicalServer $physicalServer)
    {
        abort_if(Gate::denies('physical_server_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $physicalServer->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyPhysicalServerRequest $request)
    {
        abort_if(Gate::denies('physical_server_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        PhysicalServer::query()->whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStorePhysicalServerRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `physical_server_create`

        $createdIds = [];
        $physicalServerModel = new PhysicalServer;
        $fillable = $physicalServerModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            $applications = $item['applications'] ?? null;
            $logicalServers = $item['logicalServers'] ?? null;

            // Colonnes du modèle uniquement (sans les relations)
            $attributes = collect($item)
                ->except(['applications', 'logicalServers'])
                ->only($fillable)
                ->toArray();

            /** @var PhysicalServer $physicalServer */
            $physicalServer = PhysicalServer::query()->create($attributes);

            if (array_key_exists('applications', $item)) {
                $physicalServer->applications()->sync($applications ?? []);
            }

            if (array_key_exists('logicalServers', $item)) {
                $physicalServer->logicalServers()->sync($logicalServers ?? []);
            }

            $createdIds[] = $physicalServer->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdatePhysicalServerRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `physical_server_edit`
        $physicalServerModel = new PhysicalServer;
        $fillable = $physicalServerModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $applications = $rawItem['applications'] ?? null;
            $logicalServers = $rawItem['logicalServers'] ?? null;

            /** @var PhysicalServer $physicalServer */
            $physicalServer = PhysicalServer::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id ni relations)
            $attributes = collect($rawItem)
                ->except(['id', 'applications', 'logicalServers'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $physicalServer->update($attributes);
            }

            if (array_key_exists('applications', $rawItem)) {
                $physicalServer->applications()->sync($applications ?? []);
            }

            if (array_key_exists('logicalServers', $rawItem)) {
                $physicalServer->logicalServers()->sync($logicalServers ?? []);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
