<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyApplicationRequest;
use App\Http\Requests\MassStoreApplicationRequest;
use App\Http\Requests\MassUpdateApplicationRequest;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UpdateApplicationRequest;
use App\Models\Application;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ApplicationController extends APIController
{
    protected string $modelClass = Application::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('application_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreApplicationRequest $request)
    {
        abort_if(Gate::denies('application_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Application $application */
        $application = Application::query()->create($request->all());
        $application->entities()->sync($request->input('entities', []));
        $application->processes()->sync($request->input('processes', []));
        $application->services()->sync($request->input('application_services', []));
        $application->databases()->sync($request->input('databases', []));
        $application->logicalServers()->sync($request->input('logical_servers', []));
        $application->activities()->sync($request->input('activities', []));
        $application->containers()->sync($request->input('containers', []));

        return response()->json($application, Response::HTTP_CREATED);
    }

    public function show(Application $application)
    {
        abort_if(Gate::denies('show-object', $application), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $application['entities'] = $application->entities()->pluck('id');
        $application['processes'] = $application->processes()->pluck('id');
        $application['services'] = $application->services()->pluck('id');
        $application['databases'] = $application->databases()->pluck('id');
        $application['logical_servers'] = $application->logicalServers()->pluck('id');
        $application['activities'] = $application->activities()->pluck('id');
        $application['containers'] = $application->containers()->pluck('id');

        return new JsonResource($application);
    }

    public function update(UpdateApplicationRequest $request, Application $application)
    {
        abort_if(Gate::denies('edit-object', $application), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $application->update($request->all());

        if ($request->has('entities')) {
            $application->entities()->sync($request->input('entities', []));
        }
        if ($request->has('processes')) {
            $application->processes()->sync($request->input('processes', []));
        }
        if ($request->has('activities')) {
            $application->activities()->sync($request->input('activities', []));
        }
        if ($request->has('databases')) {
            $application->databases()->sync($request->input('databases', []));
        }
        if ($request->has('logical_servers')) {
            $application->logicalServers()->sync($request->input('logical_servers', []));
        }
        if ($request->has('application_services')) {
            $application->services()->sync($request->input('application_services', []));
        }
        if ($request->has('containers')) {
            $application->containers()->sync($request->input('containers', []));
        }

        return response()->json();
    }

    public function destroy(Application $application)
    {
        abort_if(Gate::denies('application_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $application->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyApplicationRequest $request)
    {
        abort_if(Gate::denies('application_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Application::whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreApplicationRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('application_create')
        $createdIds = [];

        $model = new Application;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $item) {
            $entities = $item['entities'] ?? null;
            $processes = $item['processes'] ?? null;
            $activities = $item['activities'] ?? null;
            $databases = $item['databases'] ?? null;
            $logicalServersIds = $item['logical_servers'] ?? null;
            $applicationServices = $item['application_services'] ?? null;

            // Ne garde que les colonnes du modèle, sans les relations
            $attributes = collect($item)
                ->except([
                    'entities',
                    'processes',
                    'activities',
                    'databases',
                    'logical_servers',
                    'application_services',
                ])
                ->only($fillable)
                ->toArray();

            /** @var Application $application */
            $application = Application::query()->create($attributes);

            // Relations uniquement si la clé est présente dans l’item
            if (array_key_exists('entities', $item)) {
                $application->entities()->sync($entities ?? []);
            }
            if (array_key_exists('processes', $item)) {
                $application->processes()->sync($processes ?? []);
            }
            if (array_key_exists('activities', $item)) {
                $application->activities()->sync($activities ?? []);
            }
            if (array_key_exists('databases', $item)) {
                $application->databases()->sync($databases ?? []);
            }
            if (array_key_exists('logical_servers', $item)) {
                $application->logicalServers()->sync($logicalServersIds ?? []);
            }
            if (array_key_exists('application_services', $item)) {
                $application->services()->sync($applicationServices ?? []);
            }

            $createdIds[] = $application->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateApplicationRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('application_edit')
        $model = new Application;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $entities = $rawItem['entities'] ?? null;
            $processes = $rawItem['processes'] ?? null;
            $activities = $rawItem['activities'] ?? null;
            $databases = $rawItem['databases'] ?? null;
            $logicalServersIds = $rawItem['logical_servers'] ?? null;
            $applicationServices = $rawItem['application_services'] ?? null;

            /** @var Application $application */
            $application = Application::query()->findOrFail($id);

            // Ne garde que les colonnes du modèle, sans l'id ni les relations
            $attributes = collect($rawItem)
                ->except([
                    'id',
                    'entities',
                    'processes',
                    'activities',
                    'databases',
                    'logical_servers',
                    'application_services',
                ])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $application->update($attributes);
            }

            // Si la clé est présente dans l’item, on sync (même [] -> vide la relation)
            if (array_key_exists('entities', $rawItem)) {
                $application->entities()->sync($entities ?? []);
            }
            if (array_key_exists('processes', $rawItem)) {
                $application->processes()->sync($processes ?? []);
            }
            if (array_key_exists('activities', $rawItem)) {
                $application->activities()->sync($activities ?? []);
            }
            if (array_key_exists('databases', $rawItem)) {
                $application->databases()->sync($databases ?? []);
            }
            if (array_key_exists('logical_servers', $rawItem)) {
                $application->logicalServers()->sync($logicalServersIds ?? []);
            }
            if (array_key_exists('application_services', $rawItem)) {
                $application->services()->sync($applicationServices ?? []);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
