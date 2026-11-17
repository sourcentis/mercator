<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyActorRequest;
use App\Http\Requests\MassStoreActorRequest;
use App\Http\Requests\MassUpdateActorRequest;
use App\Http\Requests\StoreActorRequest;
use App\Http\Requests\UpdateActorRequest;
use App\Models\Actor;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ActorController extends APIController
{
    protected string $modelClass = Actor::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('actor_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreActorRequest $request)
    {
        abort_if(Gate::denies('actor_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $actor = Actor::query()->create($request->all());

        return response()->json($actor, Response::HTTP_CREATED);
    }

    public function show(Actor $actor): JsonResource
    {
        abort_if(Gate::denies('show-object', $actor), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // On encapsule le modèle dans une JsonResource pour rester cohérent
        return $this->asJsonResource($actor);
    }

    public function update(UpdateActorRequest $request, Actor $actor)
    {
        abort_if(Gate::denies('edit-object', $actor), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $actor->update($request->all());

        return response()->json();
    }

    public function destroy(Actor $actor)
    {
        abort_if(Gate::denies('actor_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->destroyResource($actor);

        return response()->json();
    }

    public function massDestroy(MassDestroyActorRequest $request)
    {
        abort_if(Gate::denies('actor_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->massDestroyByIds($request->input('ids', []));

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreActorRequest $request)
    {
        $createdIds = $this->massStoreItems($request->input('items', []));

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateActorRequest $request)
    {
        $this->massUpdateItems($request->input('items', []));

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
