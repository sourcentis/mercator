<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyBackupRequest;
use App\Http\Requests\MassStoreBackupRequest;
use App\Http\Requests\MassUpdateBackupRequest;
use App\Http\Requests\StoreBackupRequest;
use App\Http\Requests\UpdateBackupRequest;
use App\Models\Backup;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class BackupController extends APIController
{
    protected string $modelClass = Backup::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('backup_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreBackupRequest $request)
    {
        abort_if(Gate::denies('backup_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $backup = Backup::query()->create($request->validated());

        if ($request->has('logical_server_ids')) {
            $backup->logicalServers()->sync($request->input('logical_server_ids', []));
        }
        if ($request->has('storage_device_ids')) {
            $backup->storageDevices()->sync($request->input('storage_device_ids', []));
        }

        return response()->json($backup->load('logicalServers', 'storageDevices'), Response::HTTP_CREATED);
    }

    public function show(Backup $backup)
    {
        abort_if(Gate::denies('show-object', $backup), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($backup->load('logicalServers', 'storageDevices'));
    }

    public function update(UpdateBackupRequest $request, Backup $backup)
    {
        abort_if(Gate::denies('edit-object', $backup), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $backup->update($request->validated());

        if ($request->has('logical_server_ids')) {
            $backup->logicalServers()->sync($request->input('logical_server_ids', []));
        }
        if ($request->has('storage_device_ids')) {
            $backup->storageDevices()->sync($request->input('storage_device_ids', []));
        }

        return response()->json();
    }

    public function destroy(Backup $backup)
    {
        abort_if(Gate::denies('backup_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $backup->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyBackupRequest $request)
    {
        abort_if(Gate::denies('backup_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Backup::query()->whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreBackupRequest $request)
    {
        $createdIds = [];

        foreach ($request->input('items', []) as $item) {
            $backup = Backup::query()->create(
                collect($item)->only((new Backup)->getFillable())->all()
            );
            $createdIds[] = $backup->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateBackupRequest $request)
    {
        $fillable = (new Backup)->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $backup = Backup::query()->findOrFail($rawItem['id']);
            $backup->update(collect($rawItem)->only($fillable)->all());
        }

        return response()->json(['status' => 'ok']);
    }
}
