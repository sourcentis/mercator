<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyVlanRequest;
use App\Http\Requests\MassStoreVlanRequest;
use App\Http\Requests\MassUpdateVlanRequest;
use App\Http\Requests\StoreVlanRequest;
use App\Http\Requests\UpdateVlanRequest;
use App\Models\Vlan;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VlanController extends APIController
{
    protected string $modelClass = Vlan::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('vlan_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreVlanRequest $request)
    {
        abort_if(Gate::denies('vlan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Vlan $vlan */
        $vlan = Vlan::query()->create($request->all());

        // Réinitialise les subnets pointant vers ce VLAN, puis assigne ceux de la requête
        DB::table('subnetworks')
            ->where('vlan_id', $vlan->id)
            ->update(['vlan_id' => null, 'updated_by' => Auth::id()]);

        DB::table('subnetworks')
            ->whereIn('id', $request->input('subnetworks', []))
            ->update(['vlan_id' => $vlan->id, 'updated_by' => Auth::id()]);

        return response()->json($vlan, Response::HTTP_CREATED);
    }

    public function update(UpdateVlanRequest $request, Vlan $vlan)
    {
        abort_if(Gate::denies('edit-object', $vlan), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $vlan->update($request->all());

        if ($request->has('subnetworks')) {
            DB::table('subnetworks')
                ->where('vlan_id', $vlan->id)
                ->update(['vlan_id' => null, 'updated_by' => Auth::id()]);

            DB::table('subnetworks')
                ->whereIn('id', $request->input('subnetworks', []))
                ->update(['vlan_id' => $vlan->id, 'updated_by' => Auth::id()]);
        }

        return response()->json();
    }

    public function show(Vlan $vlan)
    {
        abort_if(Gate::denies('show-object', $vlan), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($vlan);
    }

    public function destroy(Vlan $vlan)
    {
        abort_if(Gate::denies('vlan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $vlan->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyVlanRequest $request)
    {
        abort_if(Gate::denies('vlan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Vlan::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreVlanRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `vlan_create`

        $createdIds = [];
        $vlanModel = new Vlan;
        $fillable = $vlanModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            $subnetworks = $item['subnetworks'] ?? null;

            // Colonnes du modèle uniquement (sans relations)
            $attributes = collect($item)
                ->except(['subnetworks'])
                ->only($fillable)
                ->toArray();

            /** @var Vlan $vlan */
            $vlan = Vlan::query()->create($attributes);

            if (array_key_exists('subnetworks', $item)) {
                DB::table('subnetworks')
                    ->where('vlan_id', $vlan->id)
                    ->update(['vlan_id' => null, 'updated_by' => Auth::id()]);

                DB::table('subnetworks')
                    ->whereIn('id', $subnetworks ?? [])
                    ->update(['vlan_id' => $vlan->id, 'updated_by' => Auth::id()]);
            }

            $createdIds[] = $vlan->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateVlanRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `vlan_edit`
        $vlanModel = new Vlan;
        $fillable = $vlanModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $subnetworks = $rawItem['subnetworks'] ?? null;

            /** @var Vlan $vlan */
            $vlan = Vlan::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id ni relations)
            $attributes = collect($rawItem)
                ->except(['id', 'subnetworks'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $vlan->update($attributes);
            }

            if (array_key_exists('subnetworks', $rawItem)) {
                DB::table('subnetworks')
                    ->where('vlan_id', $vlan->id)
                    ->update(['vlan_id' => null, 'updated_by' => Auth::id()]);

                DB::table('subnetworks')
                    ->whereIn('id', $subnetworks ?? [])
                    ->update(['vlan_id' => $vlan->id, 'updated_by' => Auth::id()]);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
