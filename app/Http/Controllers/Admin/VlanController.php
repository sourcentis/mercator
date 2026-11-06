<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyVlanRequest;
use App\Http\Requests\StoreVlanRequest;
use App\Http\Requests\UpdateVlanRequest;
use App\Models\Cartographer;
use App\Models\Subnetwork;
use App\Models\Vlan;
use App\Services\SubnetworkDeviceLocator;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VlanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('vlan_access') ? null : Cartographer::allowedIdsFor($user, Vlan::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $vlans = Vlan::with('subnetworks')
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Vlan::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')

            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate($this->resolvePerPage());

        return view('admin.vlans.index', compact('vlans'));
    }

    public function clone(Request $request)
    {
        abort_if(Gate::denies('vlan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subnetworks = Subnetwork::all()->sortBy('name')->pluck('name', 'id');

        // Get Vlan
        $vlan = Vlan::find($request['id']);

        // Vlan not found
        abort_if($vlan === null, Response::HTTP_NOT_FOUND, '404 Not Found');

        $data = $vlan->only($vlan->getFillable());
        if (isset($data['attributes']) && is_string($data['attributes'])) {
            $data['attributes'] = array_filter(explode(' ', $data['attributes']));
        }

        $request->merge($data);
        $request->merge(['subnetworks' => $vlan->subnetworks()->pluck('id')->unique()->toArray()]);
        $request->flash();

        $type_list = Vlan::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        return view('admin.vlans.create', compact('subnetworks', 'type_list', 'attributes_list'));
    }

    public function store(StoreVlanRequest $request)
    {
        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $vlan = Vlan::create($request->all());

        DB::table('subnetworks')
            ->where('vlan_id', $vlan->id)
            ->update(['vlan_id' => null, 'updated_by' => Auth::id()]);

        DB::table('subnetworks')
            ->whereIn('id', $request->input('subnetworks', []))
            ->update(['vlan_id' => $vlan->id, 'updated_by' => Auth::id()]);

        return redirect()->route('admin.vlans.index');
    }

    public function create(Request $request)
    {
        abort_if(Gate::denies('vlan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subnetworks = Subnetwork::all()->sortBy('name')->pluck('name', 'id');
        $type_list = Vlan::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        return view('admin.vlans.create', compact('subnetworks', 'type_list', 'attributes_list'));
    }

    public function update(UpdateVlanRequest $request, Vlan $vlan)
    {
        abort_if(Gate::denies('edit-object', $vlan), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $vlan->update($request->all());

        DB::table('subnetworks')
            ->where('vlan_id', $vlan->id)
            ->update(['vlan_id' => null, 'updated_by' => Auth::id()]);

        DB::table('subnetworks')
            ->whereIn('id', $request->input('subnetworks', []))
            ->update(['vlan_id' => $vlan->id, 'updated_by' => Auth::id()]);

        return redirect()->route('admin.vlans.index');
    }

    public function edit(Vlan $vlan)
    {
        abort_if(Gate::denies('edit-object', $vlan), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $vlan->load('subnetworks');

        $subnetworks = Subnetwork::all()->sortBy('name')->pluck('name', 'id');
        $type_list = Vlan::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        return view('admin.vlans.edit', compact('vlan', 'subnetworks', 'type_list', 'attributes_list'));
    }

    public function show(Vlan $vlan)
    {
        abort_if(Gate::denies('show-object', $vlan), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $vlan->load('subnetworks');

        $rows = app(SubnetworkDeviceLocator::class)->devicesIn($vlan->subnetworks);
        $rowsBySubnetwork = $rows->groupBy('subnetwork_id');

        $subnetworksData = $vlan->subnetworks
            ->sortBy(function (Subnetwork $subnetwork) {
                $base = explode('/', (string) $subnetwork->address)[0];
                $ip = filter_var($base, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? ip2long($base) : null;

                return [$ip === null ? 1 : 0, $ip ?? 0, (string) $subnetwork->address];
            })
            ->map(fn (Subnetwork $subnetwork) => [
                'subnetwork' => $subnetwork,
                'ips' => ($rowsBySubnetwork->get($subnetwork->id) ?? collect())
                    ->groupBy('ip')
                    ->sortBy(fn ($items, $ip) => ip2long((string) $ip))
                    ->map(fn ($items) => $items
                        ->map(fn (array $row) => ['name' => $row['name'], 'route' => $row['route']])
                        ->sortBy(fn (array $item) => mb_strtolower($item['name']))
                        ->values()),
            ])
            ->values();

        return view('admin.vlans.show', compact('vlan', 'subnetworksData'));
    }

    public function destroy(Vlan $vlan)
    {
        abort_if(Gate::denies('vlan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $vlan->delete();

        return redirect()->route('admin.vlans.index');
    }

    public function massDestroy(MassDestroyVlanRequest $request)
    {
        Vlan::whereIn('id', request('ids'))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function getAttributes()
    {
        $attributes_list = Vlan::query()
            ->select('attributes')
            ->where('attributes', '<>', null)
            ->pluck('attributes');
        $res = [];
        foreach ($attributes_list as $i) {
            foreach (explode(' ', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        sort($res);

        return array_unique($res);
    }
}
