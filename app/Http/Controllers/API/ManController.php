<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyManRequest;
use App\Http\Requests\MassStoreManRequest;
use App\Http\Requests\MassUpdateManRequest;
use App\Http\Requests\StoreManRequest;
use App\Http\Requests\UpdateManRequest;
use App\Models\Man;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ManController extends APIController
{
    protected string $modelClass = Man::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('man_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreManRequest $request)
    {
        abort_if(Gate::denies('man_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Man $man */
        $man = Man::query()->create($request->all());

        return response()->json($man, 201);
    }

    public function show(Man $man)
    {
        abort_if(Gate::denies('show-object', $man), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($man);
    }

    public function update(UpdateManRequest $request, Man $man)
    {
        abort_if(Gate::denies('edit-object', $man), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $man->update($request->all());

        return response()->json();
    }

    public function destroy(Man $man)
    {
        abort_if(Gate::denies('man_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $man->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyManRequest $request)
    {
        abort_if(Gate::denies('man_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Man::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreManRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('man_create')

        $createdIds = [];
        $manModel = new Man;
        $fillable = $manModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Colonnes du modèle uniquement
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var Man $man */
            $man = Man::query()->create($attributes);

            $createdIds[] = $man->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateManRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('man_edit')
        $manModel = new Man;
        $fillable = $manModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var Man $man */
            $man = Man::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id)
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $man->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
