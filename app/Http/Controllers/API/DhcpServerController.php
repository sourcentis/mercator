<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyDhcpServerRequest;
use App\Http\Requests\MassStoreDhcpServerRequest;
use App\Http\Requests\MassUpdateDhcpServerRequest;
use App\Http\Requests\StoreDhcpServerRequest;
use App\Http\Requests\UpdateDhcpServerRequest;
use App\Models\DhcpServer;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class DhcpServerController extends APIController
{
    protected string $modelClass = DhcpServer::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('dhcp_server_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreDhcpServerRequest $request)
    {
        abort_if(Gate::denies('dhcp_server_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var DhcpServer $dhcpserver */
        $dhcpserver = DhcpServer::create($request->all());
        // $dhcpserver->roles()->sync($request->input('roles', []));

        return response()->json($dhcpserver, Response::HTTP_CREATED);
    }

    public function show(DhcpServer $dhcpServer)
    {
        abort_if(Gate::denies('show-object', $dhcpServer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($dhcpServer);
    }

    public function update(UpdateDhcpServerRequest $request, DhcpServer $dhcpServer)
    {
        abort_if(Gate::denies('edit-object', $dhcpServer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dhcpServer->update($request->all());
        // $dhcpServer->roles()->sync($request->input('roles', []));

        return response()->json();
    }

    public function destroy(DhcpServer $dhcpServer)
    {
        abort_if(Gate::denies('dhcp_server_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dhcpServer->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyDhcpServerRequest $request)
    {
        abort_if(Gate::denies('dhcp_server_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DhcpServer::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreDhcpServerRequest $request)
    {
        // L’authorize() du FormRequest gère déjà dhcp_server_create
        $createdIds = [];

        $model = new DhcpServer;
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

            /** @var DhcpServer $dhcpserver */
            $dhcpserver = DhcpServer::query()->create($attributes);

            // if (array_key_exists('roles', $item)) {
            //     $dhcpserver->roles()->sync($roles ?? []);
            // }

            $createdIds[] = $dhcpserver->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateDhcpServerRequest $request)
    {
        // L’authorize() du FormRequest gère déjà dhcp_server_edit
        $model = new DhcpServer;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            // $roles = $rawItem['roles'] ?? null;

            /** @var DhcpServer $dhcpServer */
            $dhcpServer = DhcpServer::query()->findOrFail($id);

            // Ne garde que les colonnes du modèle, sans l'id ni les relations
            $attributes = collect($rawItem)
                ->except([
                    'id',
                    // 'roles',
                ])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $dhcpServer->update($attributes);
            }

            // if (array_key_exists('roles', $rawItem)) {
            //     $dhcpServer->roles()->sync($roles ?? []);
            // }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
