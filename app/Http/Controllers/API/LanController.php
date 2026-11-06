<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyLanRequest;
use App\Http\Requests\MassStoreLanRequest;
use App\Http\Requests\MassUpdateLanRequest;
use App\Http\Requests\StoreLanRequest;
use App\Http\Requests\UpdateLanRequest;
use App\Models\Lan;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class LanController extends APIController
{
    protected string $modelClass = Lan::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('lan_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreLanRequest $request)
    {
        abort_if(Gate::denies('lan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Lan $lan */
        $lan = Lan::query()->create($request->all());

        if ($request->has('mans')) {
            $lan->Mans()->sync($request->input('mans', []));
        }

        if ($request->has('wans')) {
            $lan->Wans()->sync($request->input('wans', []));
        }

        return response()->json($lan, 201);
    }

    public function show(Lan $lan)
    {
        abort_if(Gate::denies('show-object', $lan), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $lan['mans'] = $lan->mans()->pluck('id');
        $lan['wans'] = $lan->wans()->pluck('id');

        return new JsonResource($lan);
    }

    public function update(UpdateLanRequest $request, Lan $lan)
    {
        abort_if(Gate::denies('edit-object', $lan), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $lan->update($request->all());

        if ($request->has('mans')) {
            $lan->Mans()->sync($request->input('mans', []));
        }

        if ($request->has('wans')) {
            $lan->Wans()->sync($request->input('wans', []));
        }

        return response()->json();
    }

    public function destroy(Lan $lan)
    {
        abort_if(Gate::denies('lan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $lan->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyLanRequest $request)
    {
        abort_if(Gate::denies('lan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Lan::query()->whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreLanRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('lan_create')

        $createdIds = [];
        $lanModel = new Lan;
        $fillable = $lanModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            $mans = $item['mans'] ?? null;
            $wans = $item['wans'] ?? null;

            // Colonnes du modèle uniquement (sans les relations)
            $attributes = collect($item)
                ->except(['mans', 'wans'])
                ->only($fillable)
                ->toArray();

            /** @var Lan $lan */
            $lan = Lan::query()->create($attributes);

            if (array_key_exists('mans', $item)) {
                $lan->Mans()->sync($mans ?? []);
            }

            if (array_key_exists('wans', $item)) {
                $lan->Wans()->sync($wans ?? []);
            }

            $createdIds[] = $lan->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateLanRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('lan_edit')
        $lanModel = new Lan;
        $fillable = $lanModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $mans = $rawItem['mans'] ?? null;
            $wans = $rawItem['wans'] ?? null;

            /** @var Lan $lan */
            $lan = Lan::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id ni relations)
            $attributes = collect($rawItem)
                ->except(['id', 'mans', 'wans'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $lan->update($attributes);
            }

            if (array_key_exists('mans', $rawItem)) {
                $lan->Mans()->sync($mans ?? []);
            }

            if (array_key_exists('wans', $rawItem)) {
                $lan->Wans()->sync($wans ?? []);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
