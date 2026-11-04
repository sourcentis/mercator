<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyContainerRequest;
use App\Http\Requests\StoreContainerRequest;
use App\Http\Requests\UpdateContainerRequest;
use App\Models\Container;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ContainerController extends APIController
{
    protected string $modelClass = Container::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('container_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreContainerRequest $request)
    {
        abort_if(Gate::denies('container_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $container = Container::query()->create($request->all());

        $container->logicalServers()->sync($request->input('logical_servers', []));
        $container->applications()->sync($request->input('applications', []));
        $container->databases()->sync($request->input('databases', []));

        Log::Debug('ContainerController:store Done');

        return response()->json($container, 201);
    }

    public function show(Container $container)
    {
        abort_if(Gate::denies('show-object', $container), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $container['logical_servers'] = $container->logicalServers()->pluck('id');
        $container['applications'] = $container->applications()->pluck('id');
        $container['databases'] = $container->databases()->pluck('id');

        return new JsonResource($container);
    }

    public function update(UpdateContainerRequest $request, Container $container)
    {
        abort_if(Gate::denies('edit-object', $container), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $container->update($request->all());

        if ($request->has('logical_servers')) {
            $container->logicalServers()->sync($request->input('logical_servers', []));
        }
        if ($request->has('applications')) {
            $container->applications()->sync($request->input('applications', []));
        }
        if ($request->has('databases')) {
            $container->databases()->sync($request->input('databases', []));
        }

        return response()->json();
    }

    public function destroy(Container $container)
    {
        abort_if(Gate::denies('container_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $container->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyContainerRequest $request)
    {
        abort_if(Gate::denies('container_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Container::query()->whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
