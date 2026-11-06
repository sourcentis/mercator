<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyPeripheralRequest;
use App\Http\Requests\MassStorePeripheralRequest;
use App\Http\Requests\MassUpdatePeripheralRequest;
use App\Http\Requests\StorePeripheralRequest;
use App\Http\Requests\UpdatePeripheralRequest;
use App\Models\Peripheral;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class PeripheralController extends APIController
{
    protected string $modelClass = Peripheral::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('peripheral_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StorePeripheralRequest $request)
    {
        abort_if(Gate::denies('peripheral_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Peripheral $peripheral */
        $peripheral = Peripheral::query()->create($request->all());

        $peripheral->applications()->sync($request->input('applications', []));

        return response()->json($peripheral, 201);
    }

    public function show(Peripheral $peripheral)
    {
        abort_if(Gate::denies('show-object', $peripheral), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($peripheral);
    }

    public function update(UpdatePeripheralRequest $request, Peripheral $peripheral)
    {
        abort_if(Gate::denies('edit-object', $peripheral), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $peripheral->update($request->all());

        if ($request->has('applications')) {
            $peripheral->applications()->sync($request->input('applications', []));
        }

        return response()->json();
    }

    public function destroy(Peripheral $peripheral)
    {
        abort_if(Gate::denies('peripheral_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $peripheral->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyPeripheralRequest $request)
    {
        abort_if(Gate::denies('peripheral_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Peripheral::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStorePeripheralRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('peripheral_create')

        $createdIds = [];
        $peripheralModel = new Peripheral;
        $fillable = $peripheralModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            $applications = $item['applications'] ?? null;

            // Colonnes du modèle uniquement (sans les relations)
            $attributes = collect($item)
                ->except(['applications'])
                ->only($fillable)
                ->toArray();

            /** @var Peripheral $peripheral */
            $peripheral = Peripheral::query()->create($attributes);

            if (array_key_exists('applications', $item)) {
                $peripheral->applications()->sync($applications ?? []);
            }

            $createdIds[] = $peripheral->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdatePeripheralRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('peripheral_edit')
        $peripheralModel = new Peripheral;
        $fillable = $peripheralModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $applications = $rawItem['applications'] ?? null;

            /** @var Peripheral $peripheral */
            $peripheral = Peripheral::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id ni relations)
            $attributes = collect($rawItem)
                ->except(['id', 'applications'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $peripheral->update($attributes);
            }

            if (array_key_exists('applications', $rawItem)) {
                $peripheral->applications()->sync($applications ?? []);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
