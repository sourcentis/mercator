<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyDatabaseRequest;
use App\Http\Requests\MassStoreDatabaseRequest;
use App\Http\Requests\MassUpdateDatabaseRequest;
use App\Http\Requests\StoreDatabaseRequest;
use App\Http\Requests\UpdateDatabaseRequest;
use App\Models\Database;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class DatabaseController extends APIController
{
    protected string $modelClass = Database::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('database_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreDatabaseRequest $request)
    {
        abort_if(Gate::denies('database_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $database = Database::query()->create($request->all());

        $database->entities()->sync($request->input('entities', []));
        $database->informations()->sync($request->input('informations', []));
        $database->applications()->sync($request->input('applications', []));
        $database->logicalServers()->sync($request->input('logical_servers', []));

        return response()->json($database, Response::HTTP_CREATED);
    }

    public function show(Database $database)
    {
        abort_if(Gate::denies('show-object', $database), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($database);
    }

    public function update(UpdateDatabaseRequest $request, Database $database)
    {
        abort_if(Gate::denies('edit-object', $database), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $database->update($request->all());

        if ($request->has('entities')) {
            $database->entities()->sync($request->input('entities', []));
        }
        if ($request->has('informations')) {
            $database->informations()->sync($request->input('informations', []));
        }
        if ($request->has('applications')) {
            $database->applications()->sync($request->input('applications', []));
        }
        if ($request->has('logical_servers')) {
            $database->logicalServers()->sync($request->input('logical_servers', []));
        }

        return response()->json();
    }

    public function destroy(Database $database)
    {
        abort_if(Gate::denies('database_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $database->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyDatabaseRequest $request)
    {
        abort_if(Gate::denies('database_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Database::query()->whereIn('id', request('ids'))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreDatabaseRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('database_create')
        $createdIds = [];

        $model = new Database;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $item) {
            $entities = $item['entities'] ?? null;
            $informations = $item['informations'] ?? null;
            $applications = $item['applications'] ?? null;
            $logicalServers = $item['logical_servers'] ?? null;
            // $roles          = $item['roles'] ?? null;

            // Ne garde que les colonnes du modèle, sans les relations
            $attributes = collect($item)
                ->except([
                    'entities',
                    'informations',
                    'applications',
                    'logical_servers',
                    // 'roles',
                ])
                ->only($fillable)
                ->toArray();

            /** @var Database $database */
            $database = Database::query()->create($attributes);

            if (array_key_exists('entities', $item)) {
                $database->entities()->sync($entities ?? []);
            }
            if (array_key_exists('informations', $item)) {
                $database->informations()->sync($informations ?? []);
            }
            if (array_key_exists('applications', $item)) {
                $database->applications()->sync($applications ?? []);
            }
            if (array_key_exists('logical_servers', $item)) {
                $database->logicalServers()->sync($logicalServers ?? []);
            }
            // if (array_key_exists('roles', $item)) {
            //     $database->roles()->sync($roles ?? []);
            // }

            $createdIds[] = $database->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateDatabaseRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('database_edit')
        $model = new Database;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $entities = $rawItem['entities'] ?? null;
            $informations = $rawItem['informations'] ?? null;
            $applications = $rawItem['applications'] ?? null;
            $logicalServers = $rawItem['logical_servers'] ?? null;
            // $roles          = $rawItem['roles'] ?? null;

            /** @var Database $database */
            $database = Database::query()->findOrFail($id);

            // Ne garde que les colonnes du modèle, sans l'id ni les relations
            $attributes = collect($rawItem)
                ->except([
                    'id',
                    'entities',
                    'informations',
                    'applications',
                    'logical_servers',
                    // 'roles',
                ])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $database->update($attributes);
            }

            if (array_key_exists('entities', $rawItem)) {
                $database->entities()->sync($entities ?? []);
            }
            if (array_key_exists('informations', $rawItem)) {
                $database->informations()->sync($informations ?? []);
            }
            if (array_key_exists('applications', $rawItem)) {
                $database->applications()->sync($applications ?? []);
            }
            if (array_key_exists('logical_servers', $rawItem)) {
                $database->logicalServers()->sync($logicalServers ?? []);
            }
            // if (array_key_exists('roles', $rawItem)) {
            //     $database->roles()->sync($roles ?? []);
            // }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
