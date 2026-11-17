<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyPhoneRequest;
use App\Http\Requests\MassStorePhoneRequest;
use App\Http\Requests\MassUpdatePhoneRequest;
use App\Http\Requests\StorePhoneRequest;
use App\Http\Requests\UpdatePhoneRequest;
use App\Models\Phone;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class PhoneController extends APIController
{
    protected string $modelClass = Phone::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('phone_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StorePhoneRequest $request)
    {
        abort_if(Gate::denies('phone_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Phone $phone */
        $phone = Phone::query()->create($request->all());

        return response()->json($phone, 201);
    }

    public function show(Phone $phone)
    {
        abort_if(Gate::denies('show-object', $phone), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($phone);
    }

    public function update(UpdatePhoneRequest $request, Phone $phone)
    {
        abort_if(Gate::denies('edit-object', $phone), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $phone->update($request->all());

        return response()->json();
    }

    public function destroy(Phone $phone)
    {
        abort_if(Gate::denies('phone_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $phone->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyPhoneRequest $request)
    {
        abort_if(Gate::denies('phone_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Phone::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStorePhoneRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `phone_create`

        $createdIds = [];
        $phoneModel = new Phone;
        $fillable = $phoneModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Colonnes du modèle uniquement
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var Phone $phone */
            $phone = Phone::query()->create($attributes);

            $createdIds[] = $phone->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdatePhoneRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `phone_edit`
        $phoneModel = new Phone;
        $fillable = $phoneModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var Phone $phone */
            $phone = Phone::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id)
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $phone->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
