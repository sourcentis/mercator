<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyInformationRequest;
use App\Http\Requests\MassStoreInformationRequest;
use App\Http\Requests\MassUpdateInformationRequest;
use App\Http\Requests\StoreInformationRequest;
use App\Http\Requests\UpdateInformationRequest;
use App\Models\Information;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class InformationController extends APIController
{
    protected string $modelClass = Information::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('information_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreInformationRequest $request)
    {
        abort_if(Gate::denies('information_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Information $information */
        $information = Information::query()->create($request->all());

        $information->processes()->sync($request->input('processes', []));
        $information->parents()->sync($request->input('parents', []));
        $information->children()->sync($request->input('children', []));

        return response()->json($information, 201);
    }

    public function show(Information $information)
    {
        abort_if(Gate::denies('show-object', $information), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $information['processes'] = $information->processes()->pluck('id');
        $information['parents'] = $information->parents()->pluck('id');
        $information['children'] = $information->children()->pluck('id');

        return new JsonResource($information);
    }

    public function update(UpdateInformationRequest $request, Information $information)
    {
        abort_if(Gate::denies('edit-object', $information), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $information->update($request->all());

        if ($request->has('processes')) {
            $information->processes()->sync($request->input('processes', []));
        }
        if ($request->has('parents')) {
            $information->parents()->sync($request->input('parents', []));
        }
        if ($request->has('children')) {
            $information->children()->sync($request->input('children', []));
        }

        return response()->json();
    }

    public function destroy(Information $information)
    {
        abort_if(Gate::denies('information_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $information->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyInformationRequest $request)
    {
        abort_if(Gate::denies('information_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Information::query()->whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreInformationRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('information_create')

        $createdIds = [];
        $informationModel = new Information;
        $fillable = $informationModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            $processes = $item['processes'] ?? null;

            // Colonnes du modèle uniquement (sans les relations)
            $attributes = collect($item)
                ->except(['processes'])
                ->only($fillable)
                ->toArray();

            /** @var Information $information */
            $information = Information::query()->create($attributes);

            if (array_key_exists('processes', $item)) {
                $information->processes()->sync($processes ?? []);
            }

            $createdIds[] = $information->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateInformationRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('information_edit')
        $informationModel = new Information;
        $fillable = $informationModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $processes = $rawItem['processes'] ?? null;

            /** @var Information $information */
            $information = Information::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id ni relations)
            $attributes = collect($rawItem)
                ->except(['id', 'processes'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $information->update($attributes);
            }

            if (array_key_exists('processes', $rawItem)) {
                $information->processes()->sync($processes ?? []);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
