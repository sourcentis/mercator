<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroySecurityControlRequest;
use App\Http\Requests\MassStoreSecurityControlRequest;
use App\Http\Requests\MassUpdateSecurityControlRequest;
use App\Http\Requests\StoreSecurityControlRequest;
use App\Http\Requests\UpdateSecurityControlRequest;
use App\Models\SecurityControl;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class SecurityControlController extends APIController
{
    protected string $modelClass = SecurityControl::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('security_control_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreSecurityControlRequest $request)
    {
        abort_if(Gate::denies('security_control_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var SecurityControl $securityControl */
        $securityControl = SecurityControl::query()->create($request->all());

        return response()->json($securityControl, Response::HTTP_CREATED);
    }

    public function show(SecurityControl $securityControl)
    {
        abort_if(Gate::denies('show-object', $securityControl), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($securityControl);
    }

    public function update(UpdateSecurityControlRequest $request, SecurityControl $securityControl)
    {
        abort_if(Gate::denies('edit-object', $securityControl), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $securityControl->update($request->all());

        return response()->json();
    }

    public function destroy(SecurityControl $securityControl)
    {
        abort_if(Gate::denies('security_control_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $securityControl->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroySecurityControlRequest $request)
    {
        abort_if(Gate::denies('security_control_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        SecurityControl::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreSecurityControlRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `security_control_create`

        $createdIds = [];
        $securityControlModel = new SecurityControl;
        $fillable = $securityControlModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Colonnes du modèle uniquement
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var SecurityControl $securityControl */
            $securityControl = SecurityControl::query()->create($attributes);

            $createdIds[] = $securityControl->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateSecurityControlRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `security_control_edit`
        $securityControlModel = new SecurityControl;
        $fillable = $securityControlModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var SecurityControl $securityControl */
            $securityControl = SecurityControl::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id)
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $securityControl->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
