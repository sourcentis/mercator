<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyApplicationServiceRequest;
use App\Http\Requests\MassStoreApplicationServiceRequest;
use App\Http\Requests\MassUpdateApplicationServiceRequest;
use App\Http\Requests\StoreApplicationServiceRequest;
use App\Http\Requests\UpdateApplicationServiceRequest;
use App\Models\ApplicationService;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ApplicationServiceController extends APIController
{
    protected string $modelClass = ApplicationService::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('application_service_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreApplicationServiceRequest $request)
    {
        abort_if(Gate::denies('application_service_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationService = ApplicationService::query()->create($request->all());
        $applicationService->modules()->sync($request->input('modules', []));
        $applicationService->applications()->sync($request->input('applications', []));

        return response()->json($applicationService, Response::HTTP_CREATED);
    }

    public function show(ApplicationService $applicationService)
    {
        abort_if(Gate::denies('show-object', $applicationService), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationService['modules'] = $applicationService->modules()->pluck('id');
        $applicationService['applications'] = $applicationService->applications()->pluck('id');

        return new JsonResource($applicationService);
    }

    public function update(UpdateApplicationServiceRequest $request, ApplicationService $applicationService)
    {
        abort_if(Gate::denies('edit-object', $applicationService), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationService->update($request->all());
        if ($request['modules'] !== null) {
            $applicationService->modules()->sync($request->input('modules', []));
        }
        if ($request['applications'] !== null) {
            $applicationService->applications()->sync($request->input('applications', []));
        }

        return response()->json();
    }

    public function destroy(ApplicationService $applicationService)
    {
        abort_if(Gate::denies('application_service_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationService->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyApplicationServiceRequest $request)
    {
        abort_if(Gate::denies('application_service_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        ApplicationService::query()->whereIn('id', request('ids'))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreApplicationServiceRequest $request)
    {
        // L’authorize() du FormRequest gère déjà l’appel Gate::denies('application_service_create')
        $createdIds = [];

        $model = new ApplicationService;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $item) {
            $modules = $item['modules'] ?? null;
            $applications = $item['applications'] ?? null;

            // Ne garde que les colonnes du modèle, sans les relations
            $attributes = collect($item)
                ->except(['modules', 'applications'])
                ->only($fillable)
                ->toArray();

            /** @var ApplicationService $applicationService */
            $applicationService = ApplicationService::query()->create($attributes);

            // Relations uniquement si la clé est présente dans l’item
            if (array_key_exists('modules', $item)) {
                $applicationService->modules()->sync($modules ?? []);
            }
            if (array_key_exists('applications', $item)) {
                $applicationService->applications()->sync($applications ?? []);
            }

            $createdIds[] = $applicationService->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateApplicationServiceRequest $request)
    {
        // L’authorize() du FormRequest gère déjà l’appel Gate::denies('application_service_edit')
        $model = new ApplicationService;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $modules = $rawItem['modules'] ?? null;
            $applications = $rawItem['applications'] ?? null;

            /** @var ApplicationService $applicationService */
            $applicationService = ApplicationService::query()->findOrFail($id);

            // Ne garde que les colonnes du modèle, sans l'id ni les relations
            $attributes = collect($rawItem)
                ->except(['id', 'modules', 'applications'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $applicationService->update($attributes);
            }

            // Si la clé est présente dans l’item, on sync (même [] -> vide la relation)
            if (array_key_exists('modules', $rawItem)) {
                $applicationService->modules()->sync($modules ?? []);
            }
            if (array_key_exists('applications', $rawItem)) {
                $applicationService->applications()->sync($applications ?? []);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
