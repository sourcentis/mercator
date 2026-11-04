<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyActivityRequest;
use App\Http\Requests\MassStoreActivityRequest;
use App\Http\Requests\MassUpdateActivityRequest;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ActivityController extends APIController
{
    protected string $modelClass = Activity::class;

    public function index(Request $request): JsonResponse
    {
        abort_if(Gate::denies('activity_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreActivityRequest $request)
    {
        abort_if(Gate::denies('activity_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $activity = Activity::query()->create($request->all());

        return response()->json($activity, Response::HTTP_CREATED);
    }

    public function show(Activity $activity): JsonResource
    {
        abort_if(Gate::denies('show-object', $activity), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->asJsonResource($activity);
    }

    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        abort_if(Gate::denies('edit-object', $activity), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $activity->update($request->all());

        return response()->json();
    }

    public function destroy(Activity $activity)
    {
        abort_if(Gate::denies('activity_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->destroyResource($activity);

        return response()->json();
    }

    public function massDestroy(MassDestroyActivityRequest $request)
    {
        abort_if(Gate::denies('activity_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->massDestroyByIds($request->input('ids', []));

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreActivityRequest $request)
    {
        abort_if(Gate::denies('activity_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $createdIds = $this->massStoreItems($request->input('items', []));

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateActivityRequest $request)
    {
        abort_if(Gate::denies('activity_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->massUpdateItems($request->input('items', []));

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
