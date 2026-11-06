<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyStorageDeviceRequest;
use App\Http\Requests\MassStoreStorageDeviceRequest;
use App\Http\Requests\MassUpdateStorageDeviceRequest;
use App\Http\Requests\StoreStorageDeviceRequest;
use App\Http\Requests\UpdateStorageDeviceRequest;
use App\Models\StorageDevice;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class StorageDeviceController extends APIController
{
    protected string $modelClass = StorageDevice::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('storage_device_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreStorageDeviceRequest $request)
    {
        abort_if(Gate::denies('storage_device_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var StorageDevice $storageDevice */
        $storageDevice = StorageDevice::query()->create($request->all());

        return response()->json($storageDevice, Response::HTTP_CREATED);
    }

    public function show(StorageDevice $storageDevice)
    {
        abort_if(Gate::denies('show-object', $storageDevice), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($storageDevice);
    }

    public function update(UpdateStorageDeviceRequest $request, StorageDevice $storageDevice)
    {
        abort_if(Gate::denies('edit-object', $storageDevice), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $storageDevice->update($request->all());

        return response()->json();
    }

    public function destroy(StorageDevice $storageDevice)
    {
        abort_if(Gate::denies('storage_device_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $storageDevice->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyStorageDeviceRequest $request)
    {
        abort_if(Gate::denies('storage_device_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        StorageDevice::query()->whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreStorageDeviceRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `storage_device_create`

        $createdIds = [];
        $storageDeviceModel = new StorageDevice;
        $fillable = $storageDeviceModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Colonnes du modèle uniquement
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var StorageDevice $storageDevice */
            $storageDevice = StorageDevice::query()->create($attributes);

            $createdIds[] = $storageDevice->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateStorageDeviceRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `storage_device_edit`
        $storageDeviceModel = new StorageDevice;
        $fillable = $storageDeviceModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var StorageDevice $storageDevice */
            $storageDevice = StorageDevice::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id)
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $storageDevice->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
