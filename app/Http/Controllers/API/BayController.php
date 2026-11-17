<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyBayRequest;
use App\Http\Requests\MassStoreBayRequest;
use App\Http\Requests\MassUpdateBayRequest;
use App\Http\Requests\StoreBayRequest;
use App\Http\Requests\UpdateBayRequest;
use App\Models\Bay;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class BayController extends APIController
{
    protected string $modelClass = Bay::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('bay_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreBayRequest $request)
    {
        abort_if(Gate::denies('bay_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $bay = Bay::create($request->all());

        return response()->json($bay, 201);
    }

    public function show(Bay $bay)
    {
        abort_if(Gate::denies('show-object', $bay), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($bay);
    }

    public function update(UpdateBayRequest $request, Bay $bay)
    {
        abort_if(Gate::denies('edit-object', $bay), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $bay->update($request->all());

        return response()->json();
    }

    public function destroy(Bay $bay)
    {
        abort_if(Gate::denies('bay_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $bay->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyBayRequest $request)
    {
        abort_if(Gate::denies('bay_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Bay::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreBayRequest $request)
    {
        // L’authorize() du FormRequest protège déjà l’accès

        $createdIds = [];
        $fillable = (new Bay)->getFillable();

        foreach ($request->input('items', []) as $item) {
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var Bay $bay */
            $bay = Bay::create($attributes);
            $createdIds[] = $bay->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateBayRequest $request)
    {
        // L’authorize() du FormRequest protège déjà l’accès
        $fillable = (new Bay)->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var Bay $bay */
            $bay = Bay::findOrFail($id);

            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $bay->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
