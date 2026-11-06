<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroySecurityDeviceRequest;
use App\Http\Requests\MassStoreSecurityDeviceRequest;
use App\Http\Requests\MassUpdateSecurityDeviceRequest;
use App\Http\Requests\StoreSecurityDeviceRequest;
use App\Http\Requests\UpdateSecurityDeviceRequest;
use App\Models\SecurityDevice;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class SecurityDeviceController extends APIController
{
    protected string $modelClass = SecurityDevice::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('security_device_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreSecurityDeviceRequest $request)
    {
        abort_if(Gate::denies('security_device_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var SecurityDevice $securityDevice */
        $securityDevice = SecurityDevice::query()->create($request->all());
        // syncs
        // $securityDevice->roles()->sync($request->input('roles', []));

        return response()->json($securityDevice, Response::HTTP_CREATED);
    }

    public function show(SecurityDevice $securityDevice)
    {
        abort_if(Gate::denies('show-object', $securityDevice), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($securityDevice);
    }

    public function update(UpdateSecurityDeviceRequest $request, SecurityDevice $securityDevice)
    {
        abort_if(Gate::denies('edit-object', $securityDevice), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $securityDevice->update($request->all());
        // syncs
        // if ($request->has('roles')) {
        //     $securityDevice->roles()->sync($request->input('roles', []));
        // }

        return response()->json();
    }

    public function destroy(SecurityDevice $securityDevice)
    {
        abort_if(Gate::denies('security_device_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $securityDevice->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroySecurityDeviceRequest $request)
    {
        abort_if(Gate::denies('security_device_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        SecurityDevice::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreSecurityDeviceRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `security_device_create`

        $createdIds = [];
        $securityDeviceModel = new SecurityDevice;
        $fillable = $securityDeviceModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Colonnes du modèle uniquement (sans relations)
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var SecurityDevice $securityDevice */
            $securityDevice = SecurityDevice::query()->create($attributes);

            // syncs relations éventuelles ici si nécessaire (ex: roles)
            // if (array_key_exists('roles', $item)) {
            //     $securityDevice->roles()->sync($item['roles'] ?? []);
            // }

            $createdIds[] = $securityDevice->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateSecurityDeviceRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `security_device_edit`
        $securityDeviceModel = new SecurityDevice;
        $fillable = $securityDeviceModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var SecurityDevice $securityDevice */
            $securityDevice = SecurityDevice::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id ni relations)
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $securityDevice->update($attributes);
            }

            // syncs relations éventuelles ici si nécessaire (ex: roles)
            // if (array_key_exists('roles', $rawItem)) {
            //     $securityDevice->roles()->sync($rawItem['roles'] ?? []);
            // }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
