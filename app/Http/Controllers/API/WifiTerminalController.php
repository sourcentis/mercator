<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyWifiTerminalRequest;
use App\Http\Requests\MassStoreWifiTerminalRequest;
use App\Http\Requests\MassUpdateWifiTerminalRequest;
use App\Http\Requests\StoreWifiTerminalRequest;
use App\Http\Requests\UpdateWifiTerminalRequest;
use App\Models\WifiTerminal;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class WifiTerminalController extends APIController
{
    protected string $modelClass = WifiTerminal::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('wifi_terminal_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreWifiTerminalRequest $request)
    {
        abort_if(Gate::denies('wifi_terminal_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $wifiTerminal = WifiTerminal::create($request->all());

        return response()->json($wifiTerminal, 201);
    }

    public function show(WifiTerminal $wifiTerminal)
    {
        abort_if(Gate::denies('show-object', $wifiTerminal), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($wifiTerminal);
    }

    public function update(UpdateWifiTerminalRequest $request, WifiTerminal $wifiTerminal)
    {
        abort_if(Gate::denies('edit-object', $wifiTerminal), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $wifiTerminal->update($request->all());

        return response()->json();
    }

    public function destroy(WifiTerminal $wifiTerminal)
    {
        abort_if(Gate::denies('wifi_terminal_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $wifiTerminal->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyWifiTerminalRequest $request)
    {
        abort_if(Gate::denies('wifi_terminal_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        WifiTerminal::whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreWifiTerminalRequest $request)
    {
        // L’authorize() du FormRequest protège déjà l’accès

        $createdIds = [];
        $fillable = (new WifiTerminal)->getFillable();

        foreach ($request->input('items', []) as $item) {
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var WifiTerminal $wifiTerminal */
            $wifiTerminal = WifiTerminal::create($attributes);
            $createdIds[] = $wifiTerminal->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateWifiTerminalRequest $request)
    {
        // L’authorize() du FormRequest protège déjà l’accès
        $fillable = (new WifiTerminal)->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var WifiTerminal $wifiTerminal */
            $wifiTerminal = WifiTerminal::findOrFail($id);

            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $wifiTerminal->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
