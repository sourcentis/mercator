<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyLogicalServerRequest;
use App\Http\Requests\MassStoreLogicalServerRequest;
use App\Http\Requests\MassUpdateLogicalServerRequest;
use App\Http\Requests\StoreLogicalServerRequest;
use App\Http\Requests\UpdateLogicalServerRequest;
use App\Models\LogicalServer;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class LogicalServerController extends APIController
{
    protected string $modelClass = LogicalServer::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('logical_server_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreLogicalServerRequest $request)
    {
        abort_if(Gate::denies('logical_server_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logicalServer = LogicalServer::create($request->all());
        $logicalServer->physicalServers()->sync($request->input('physical_servers', []));
        $logicalServer->applications()->sync($request->input('applications', []));
        $logicalServer->databases()->sync($request->input('databases', []));
        $logicalServer->clusters()->sync($request->input('clusters', []));
        $logicalServer->containers()->sync($request->input('containers', []));

        return response()->json($logicalServer, 201);
    }

    public function show(LogicalServer $logicalServer)
    {
        abort_if(Gate::denies('show-object', $logicalServer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logicalServer['physical_servers'] = $logicalServer->physicalServers()->pluck('id');
        $logicalServer['applications'] = $logicalServer->applications()->pluck('id');
        $logicalServer['databases'] = $logicalServer->databases()->pluck('id');
        $logicalServer['clusters'] = $logicalServer->clusters()->pluck('id');
        $logicalServer['containers'] = $logicalServer->containers()->pluck('id');

        return new JsonResource($logicalServer);
    }

    public function update(UpdateLogicalServerRequest $request, LogicalServer $logicalServer)
    {
        abort_if(Gate::denies('edit-object', $logicalServer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logicalServer->update($request->all());
        if ($request->has('physical_servers')) {
            $logicalServer->physicalServers()->sync($request->input('physical_servers', []));
        }

        if ($request->has('applications')) {
            $logicalServer->applications()->sync($request->input('applications', []));
        }

        if ($request->has('databases')) {
            $logicalServer->databases()->sync($request->input('databases', []));
        }
        if ($request->has('clusters')) {
            $logicalServer->clusters()->sync($request->input('clusters', []));
        }
        if ($request->has('containers')) {
            $logicalServer->containers()->sync($request->input('containers', []));
        }

        return response()->json();
    }

    public function destroy(LogicalServer $logicalServer)
    {
        abort_if(Gate::denies('logical_server_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logicalServer->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyLogicalServerRequest $request)
    {
        abort_if(Gate::denies('logical_server_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        LogicalServer::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreLogicalServerRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('logical_server_create')

        $createdIds = [];
        $logicalServerModel = new LogicalServer;
        $fillable = $logicalServerModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            $physicalServers = $item['physicalServers'] ?? null;
            $applications = $item['applications'] ?? null;
            $databases = $item['databases'] ?? null;

            // Colonnes du modèle uniquement (sans les relations)
            $attributes = collect($item)
                ->except(['physicalServers', 'applications', 'databases'])
                ->only($fillable)
                ->toArray();

            /** @var LogicalServer $logicalServer */
            $logicalServer = LogicalServer::query()->create($attributes);

            if (array_key_exists('physicalServers', $item)) {
                $logicalServer->physicalServers()->sync($physicalServers ?? []);
            }

            if (array_key_exists('applications', $item)) {
                $logicalServer->applications()->sync($applications ?? []);
            }

            if (array_key_exists('databases', $item)) {
                $logicalServer->databases()->sync($databases ?? []);
            }

            $createdIds[] = $logicalServer->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateLogicalServerRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('logical_server_edit')
        $logicalServerModel = new LogicalServer;
        $fillable = $logicalServerModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $physicalServers = $rawItem['physicalServers'] ?? null;
            $applications = $rawItem['applications'] ?? null;
            $databases = $rawItem['databases'] ?? null;

            /** @var LogicalServer $logicalServer */
            $logicalServer = LogicalServer::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id ni relations)
            $attributes = collect($rawItem)
                ->except(['id', 'physicalServers', 'applications', 'databases'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $logicalServer->update($attributes);
            }

            if (array_key_exists('physicalServers', $rawItem)) {
                $logicalServer->physicalServers()->sync($physicalServers ?? []);
            }

            if (array_key_exists('applications', $rawItem)) {
                $logicalServer->applications()->sync($applications ?? []);
            }

            if (array_key_exists('databases', $rawItem)) {
                $logicalServer->databases()->sync($databases ?? []);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
