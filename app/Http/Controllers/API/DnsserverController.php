<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyDnsserverRequest;
use App\Http\Requests\MassStoreDnsserverRequest;
use App\Http\Requests\MassUpdateDnsserverRequest;
use App\Http\Requests\StoreDnsserverRequest;
use App\Http\Requests\UpdateDnsserverRequest;
use App\Models\Dnsserver;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class DnsserverController extends APIController
{
    protected string $modelClass = Dnsserver::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('dnsserver_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreDnsserverRequest $request)
    {
        abort_if(Gate::denies('dnsserver_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Dnsserver $dnsserver */
        $dnsserver = Dnsserver::create($request->all());
        // $dnsserver->roles()->sync($request->input('roles', []));

        return response()->json($dnsserver, Response::HTTP_CREATED);
    }

    public function show(Dnsserver $dnsserver)
    {
        abort_if(Gate::denies('show-object', $dnsserver), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($dnsserver);
    }

    public function update(UpdateDnsserverRequest $request, Dnsserver $dnsserver)
    {
        abort_if(Gate::denies('edit-object', $dnsserver), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dnsserver->update($request->all());
        // $dnsserver->roles()->sync($request->input('roles', []));

        return response()->json();
    }

    public function destroy(Dnsserver $dnsserver)
    {
        abort_if(Gate::denies('dnsserver_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dnsserver->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyDnsserverRequest $request)
    {
        abort_if(Gate::denies('dnsserver_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Dnsserver::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreDnsserverRequest $request)
    {
        // L’authorize() du FormRequest gère déjà dnsserver_create
        $createdIds = [];

        $model = new Dnsserver;
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

            /** @var Dnsserver $dnsserver */
            $dnsserver = Dnsserver::query()->create($attributes);

            // if (array_key_exists('roles', $item)) {
            //     $dnsserver->roles()->sync($roles ?? []);
            // }

            $createdIds[] = $dnsserver->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateDnsserverRequest $request)
    {
        // L’authorize() du FormRequest gère déjà dnsserver_edit
        $model = new Dnsserver;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            // $roles = $rawItem['roles'] ?? null;

            /** @var Dnsserver $dnsserver */
            $dnsserver = Dnsserver::query()->findOrFail($id);

            // Ne garde que les colonnes du modèle, sans l'id ni les relations
            $attributes = collect($rawItem)
                ->except([
                    'id',
                    // 'roles',
                ])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $dnsserver->update($attributes);
            }

            // if (array_key_exists('roles', $rawItem)) {
            //     $dnsserver->roles()->sync($roles ?? []);
            // }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
