<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyMacroProcessusRequest;
use App\Http\Requests\MassStoreMacroProcessusRequest;
use App\Http\Requests\MassUpdateMacroProcessusRequest;
use App\Http\Requests\StoreMacroProcessusRequest;
use App\Http\Requests\UpdateMacroProcessusRequest;
use App\Models\MacroProcessus;
use App\Models\Process;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MacroProcessusController extends APIController
{
    protected string $modelClass = MacroProcessus::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('macro_processus_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreMacroProcessusRequest $request)
    {
        abort_if(Gate::denies('macro_processus_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var MacroProcessus $macroProcessus */
        $macroProcessus = MacroProcessus::query()->create($request->all());

        Process::whereIn('id', $request->input('processes', []))
            ->update(['macroprocess_id' => $macroProcessus->id, 'updated_by' => Auth::id()]);

        return response()->json($macroProcessus, 201);
    }

    public function show(MacroProcessus $macroProcessus)
    {
        abort_if(Gate::denies('show-object', $macroProcessus), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($macroProcessus);
    }

    public function update(UpdateMacroProcessusRequest $request, MacroProcessus $macroProcessus)
    {
        abort_if(Gate::denies('edit-object', $macroProcessus), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $macroProcessus->update($request->all());

        if ($request->has('processes')) {
            Process::whereIn('id', $request->input('processes', []))
                ->update(['macroprocess_id' => $macroProcessus->id, 'updated_by' => Auth::id()]);
        }

        return response()->json();
    }

    public function destroy(MacroProcessus $macroProcessus)
    {
        abort_if(Gate::denies('macro_processus_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $macroProcessus->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyMacroProcessusRequest $request)
    {
        abort_if(Gate::denies('macro_processus_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        MacroProcessus::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreMacroProcessusRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('macro_processus_create')

        $createdIds = [];
        $macroProcessusModel = new MacroProcessus;
        $fillable = $macroProcessusModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            $processes = $item['processes'] ?? null;

            // Colonnes du modèle uniquement (sans les relations)
            $attributes = collect($item)
                ->except(['processes'])
                ->only($fillable)
                ->toArray();

            /** @var MacroProcessus $macroProcessus */
            $macroProcessus = MacroProcessus::query()->create($attributes);

            if (array_key_exists('processes', $item) && ! empty($processes)) {
                Process::whereIn('id', $processes)
                    ->update(['macroprocess_id' => $macroProcessus->id, 'updated_by' => Auth::id()]);
            }

            $createdIds[] = $macroProcessus->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateMacroProcessusRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('macro_processus_edit')
        $macroProcessusModel = new MacroProcessus;
        $fillable = $macroProcessusModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $processes = $rawItem['processes'] ?? null;

            /** @var MacroProcessus $macroProcessus */
            $macroProcessus = MacroProcessus::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id ni relations)
            $attributes = collect($rawItem)
                ->except(['id', 'processes'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $macroProcessus->update($attributes);
            }

            if (array_key_exists('processes', $rawItem) && ! empty($processes)) {
                Process::whereIn('id', $processes)
                    ->update(['macroprocess_id' => $macroProcessus->id, 'updated_by' => Auth::id()]);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
