<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyApplicationModuleRequest;
use App\Http\Requests\MassStoreApplicationModuleRequest;
use App\Http\Requests\MassUpdateApplicationModuleRequest;
use App\Http\Requests\StoreApplicationModuleRequest;
use App\Http\Requests\UpdateApplicationModuleRequest;
use App\Models\ApplicationModule;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ApplicationModuleController extends APIController
{
    protected string $modelClass = ApplicationModule::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('application_module_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreApplicationModuleRequest $request)
    {
        abort_if(Gate::denies('application_module_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationModule = ApplicationModule::query()->create($request->all());

        $applicationModule->applicationServices()->sync($request->input('application_services', []));
        $applicationModule->entities()->sync($request->input('entities', []));

        return response()->json($applicationModule, Response::HTTP_CREATED);
    }

    public function show(ApplicationModule $applicationModule)
    {
        abort_if(Gate::denies('show-object', $applicationModule), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationModule['application_services'] = $applicationModule->applicationServices()->pluck('id');
        $applicationModule['entities'] = $applicationModule->entities()->pluck('id');

        return new JsonResource($applicationModule);
    }

    public function update(UpdateApplicationModuleRequest $request, ApplicationModule $applicationModule)
    {
        abort_if(Gate::denies('edit-object', $applicationModule), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationModule->update($request->all());

        if ($request->has('application_services')) {
            $applicationModule->applicationServices()->sync($request->input('application_services', []));
        }
        if ($request->has('entities')) {
            $applicationModule->entities()->sync($request->input('entities', []));
        }

        return response()->json();
    }

    public function destroy(ApplicationModule $applicationModule)
    {
        abort_if(Gate::denies('application_module_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationModule->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyApplicationModuleRequest $request)
    {
        abort_if(Gate::denies('application_module_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        ApplicationModule::query()->whereIn('id', request('ids'))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreApplicationModuleRequest $request)
    {
        // L’authorize() du FormRequest gère déjà l’appel Gate::denies('application_module_create')
        $createdIds = [];

        $model = new ApplicationModule;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Ne garde que les colonnes du modèle
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var ApplicationModule $applicationModule */
            $applicationModule = ApplicationModule::query()->create($attributes);

            $createdIds[] = $applicationModule->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateApplicationModuleRequest $request)
    {
        // L’authorize() du FormRequest gère déjà l’appel Gate::denies('application_module_edit')
        $model = new ApplicationModule;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var ApplicationModule $applicationModule */
            $applicationModule = ApplicationModule::query()->findOrFail($id);

            // Ne garde que les colonnes du modèle, sans l'id
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $applicationModule->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
