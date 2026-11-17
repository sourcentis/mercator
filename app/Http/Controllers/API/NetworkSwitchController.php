<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyNetworkSwitchRequest;
use App\Http\Requests\MassStoreNetworkSwitchRequest;
use App\Http\Requests\MassUpdateNetworkSwitchRequest;
use App\Http\Requests\StoreNetworkSwitchRequest;
use App\Http\Requests\UpdateNetworkSwitchRequest;
use App\Models\NetworkSwitch;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class NetworkSwitchController extends APIController
{
    protected string $modelClass = NetworkSwitch::class;

    /**
     * Return a JSON list of NetworkSwitch resources, optionally filtered by query parameters.
     *
     * Aborts with HTTP 403 if the current user lacks the `network_switch_access` permission.
     */
    public function index(Request $request)
    {
        abort_if(Gate::denies('network_switch_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    /**
     * Create a new NetworkSwitch resource and persist its relationships.
     *
     * Creates a NetworkSwitch from validated request data and synchronizes its
     * `physicalSwitches` and `vlans` relationships using the corresponding
     * request inputs (defaults to empty arrays if not provided).
     */
    public function store(StoreNetworkSwitchRequest $request)
    {
        abort_if(Gate::denies('network_switch_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $networkSwitch = NetworkSwitch::query()->create($request->all());

        $networkSwitch->physicalSwitches()->sync($request->input('physical_switches', []));

        $networkSwitch->vlans()->sync($request->input('vlans', []));

        return response()->json($networkSwitch, 201);
    }

    /**
     * Get the specified NetworkSwitch as a JSON resource.
     *
     * Aborts with HTTP 403 if the caller lacks the `network_switch_show` permission.
     */
    public function show(NetworkSwitch $networkSwitch)
    {
        abort_if(Gate::denies('show-object', $networkSwitch), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $networkSwitch['physical_switches'] = $networkSwitch->physicalSwitches()->pluck('id');

        return new JsonResource($networkSwitch);
    }

    /**
     * Update the specified NetworkSwitch with request data and, when provided,
     * synchronize its related physical switches and VLANs.
     *
     * Aborts with HTTP 403 if the current user lacks the `network_switch_edit` permission.
     */
    public function update(UpdateNetworkSwitchRequest $request, NetworkSwitch $networkSwitch)
    {
        abort_if(Gate::denies('edit-object', $networkSwitch), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $networkSwitch->update($request->all());

        if ($request->has('physical_switches')) {
            $networkSwitch->physicalSwitches()->sync($request->input('physical_switches', []));
        }

        if ($request->has('vlans')) {
            $networkSwitch->vlans()->sync($request->input('vlans', []));
        }

        return response()->json();
    }

    public function destroy(NetworkSwitch $networkSwitch)
    {
        abort_if(Gate::denies('network_switch_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $networkSwitch->delete();

        return response()->json();
    }

    /**
     * Deletes multiple NetworkSwitch records identified by IDs provided in the request.
     *
     * Expects the request to include an `ids` array containing the IDs of NetworkSwitch records to delete.
     */
    public function massDestroy(MassDestroyNetworkSwitchRequest $request)
    {
        abort_if(Gate::denies('network_switch_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        NetworkSwitch::query()->whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Create multiple NetworkSwitch resources in a single request.
     *
     * Each item may optionally contain `physicalSwitches` and `vlans` arrays
     * to synchronize the corresponding relationships.
     */
    public function massStore(MassStoreNetworkSwitchRequest $request)
    {
        // authorize() in the FormRequest already checks `network_switch_create`

        $createdIds = [];
        $networkSwitchModel = new NetworkSwitch;
        $fillable = $networkSwitchModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            $physicalSwitches = $item['physical_switches'] ?? null;
            $vlans = $item['vlans'] ?? null;

            // Only model columns, relations are excluded
            $attributes = collect($item)
                ->except(['physical_switches', 'vlans'])
                ->only($fillable)
                ->toArray();

            /** @var NetworkSwitch $networkSwitch */
            $networkSwitch = NetworkSwitch::query()->create($attributes);

            if (array_key_exists('physical_switches', $item)) {
                $networkSwitch->physicalSwitches()->sync($physicalSwitches ?? []);
            }

            if (array_key_exists('vlans', $item)) {
                $networkSwitch->vlans()->sync($vlans ?? []);
            }

            $createdIds[] = $networkSwitch->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    /**
     * Update multiple NetworkSwitch resources in a single request.
     *
     * Each item must contain an `id`, and may optionally contain
     * `physicalSwitches` and/or `vlans` arrays to synchronize relationships.
     */
    public function massUpdate(MassUpdateNetworkSwitchRequest $request)
    {
        // authorize() in the FormRequest already checks `network_switch_edit`
        $networkSwitchModel = new NetworkSwitch;
        $fillable = $networkSwitchModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $physicalSwitches = $rawItem['physical_switches'] ?? null;
            $vlans = $rawItem['vlans'] ?? null;

            /** @var NetworkSwitch $networkSwitch */
            $networkSwitch = NetworkSwitch::query()->findOrFail($id);

            // Only model columns (no id or relations)
            $attributes = collect($rawItem)
                ->except(['id', 'physical_switches', 'vlans'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $networkSwitch->update($attributes);
            }

            if (array_key_exists('physical_switches', $rawItem)) {
                $networkSwitch->physicalSwitches()->sync($physicalSwitches ?? []);
            }

            if (array_key_exists('vlans', $rawItem)) {
                $networkSwitch->vlans()->sync($vlans ?? []);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
