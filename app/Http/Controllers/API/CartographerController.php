<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyCartographerRequest;
use App\Http\Requests\MassStoreCartographerRequest;
use App\Http\Requests\MassUpdateCartographerRequest;
use App\Http\Requests\StoreCartographerRequest;
use App\Http\Requests\UpdateCartographerRequest;
use App\Models\Cartographer;
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class CartographerController extends APIController
{
    protected string $modelClass = Cartographer::class;

    public function index(Request $request): JsonResponse
    {
        abort_if(Gate::denies('cartographer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreCartographerRequest $request): JsonResponse
    {
        abort_if(Gate::denies('cartographer_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $cartographer = Cartographer::query()->firstOrCreate(
            array_filter($request->only(['cartographiable_type', 'cartographiable_id', 'user_id', 'role_id']))
        );

        $status = $cartographer->wasRecentlyCreated ? Response::HTTP_CREATED : Response::HTTP_OK;

        return response()->json($cartographer, $status);
    }

    public function show(Cartographer $cartographer): JsonResource
    {
        abort_if(Gate::denies('show-object', $cartographer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($cartographer);
    }

    public function update(UpdateCartographerRequest $request, Cartographer $cartographer): JsonResponse
    {
        abort_if(Gate::denies('edit-object', $cartographer), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $cartographer->update($request->validated());

        return response()->json();
    }

    public function destroy(Cartographer $cartographer): JsonResponse
    {
        abort_if(Gate::denies('cartographer_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $cartographer->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyCartographerRequest $request): Response
    {
        // L'authorize() du FormRequest gère déjà la permission `cartographer_delete`
        Cartographer::whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreCartographerRequest $request): JsonResponse
    {
        // L'authorize() du FormRequest gère déjà la permission `cartographer_create`
        $ids = [];

        foreach ($request->input('items', []) as $item) {
            $cartographer = Cartographer::firstOrCreate(
                array_filter(
                    collect($item)
                        ->only(['cartographiable_type', 'cartographiable_id', 'user_id', 'role_id'])
                        ->toArray()
                )
            );
            $ids[] = $cartographer->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($ids),
            'ids' => $ids,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateCartographerRequest $request): JsonResponse
    {
        // L'authorize() du FormRequest gère déjà la permission `cartographer_edit`
        foreach ($request->input('items', []) as $item) {
            $cartographer = Cartographer::query()->findOrFail($item['id']);

            $attributes = collect($item)
                ->except(['id'])
                ->only(['user_id', 'role_id'])
                ->toArray();

            if (! empty($attributes)) {
                $cartographer->update($attributes);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
