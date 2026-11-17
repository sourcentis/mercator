<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyRelationRequest;
use App\Http\Requests\MassStoreRelationRequest;
use App\Http\Requests\MassUpdateRelationRequest;
use App\Http\Requests\StoreRelationRequest;
use App\Http\Requests\UpdateRelationRequest;
use App\Models\Relation;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class RelationController extends APIController
{
    protected string $modelClass = Relation::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('relation_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreRelationRequest $request)
    {
        abort_if(Gate::denies('relation_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Relation $relation */
        $relation = Relation::query()->create($request->all());
        $relation->documents()->sync($request->input('documents', []));

        return response()->json($relation, Response::HTTP_CREATED);
    }

    public function show(Relation $relation)
    {
        abort_if(Gate::denies('show-object', $relation), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $relation['documents'] = $relation->documents()->pluck('id');

        return new JsonResource($relation);
    }

    public function update(UpdateRelationRequest $request, Relation $relation)
    {
        abort_if(Gate::denies('edit-object', $relation), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $relation->update($request->all());

        if ($request->has('documents')) {
            $relation->documents()->sync($request->input('documents', []));
        }

        return response()->json();
    }

    public function destroy(Relation $relation)
    {
        abort_if(Gate::denies('relation_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $relation->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyRelationRequest $request)
    {
        abort_if(Gate::denies('relation_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Relation::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreRelationRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `relation_create`

        $createdIds = [];
        $relationModel = new Relation;
        $fillable = $relationModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var Relation $relation */
            $relation = Relation::query()->create($attributes);

            $createdIds[] = $relation->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateRelationRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `relation_edit`
        $relationModel = new Relation;
        $fillable = $relationModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var Relation $relation */
            $relation = Relation::query()->findOrFail($id);

            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $relation->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
