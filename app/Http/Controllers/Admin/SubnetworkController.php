<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroySubnetworkRequest;
use App\Http\Requests\StoreSubnetworkRequest;
use App\Http\Requests\UpdateSubnetworkRequest;
use App\Models\Cartographer;
use App\Models\Gateway;
use App\Models\Network;
use App\Models\Subnetwork;
use App\Models\Vlan;
use Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SubnetworkController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('subnetwork_access') ? null : Cartographer::allowedIdsFor($user, Subnetwork::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $subnetworks = Subnetwork::query()
            ->with('network', 'vlan')
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Subnetwork::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
		    }
                        $q->orWhereHas('vlan', function ($v) use ($search) {
                            if (ctype_digit($search)) {
                                $v->where('vlans.vlan_id', (int) $search);
                            } else {
                                $v->whereRaw('1 = 0');
			    }
			});
                });
            })
            ->orderBy('name')
            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate($this->resolvePerPage());

        return view('admin.subnetworks.index', compact('subnetworks'));
    }

    public function store(StoreSubnetworkRequest $request)
    {
        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        Subnetwork::create($request->all());

        return redirect()->route('admin.subnetworks.index');
    }

    /**
     * Display the form to create a new Subnetwork and prepare selection lists for the view.
     *
     * Prepares ordered lists for gateways, vlans, networks, subnetworks and distinct non-null
     * values for ip_allocation_type, responsible_exp, dmz, wifi, and zone. Aborts with HTTP 403
     * if the current user is not authorized to create subnetworks.
     *
     * @return View The create view populated with the prepared lists:
     *              `gateways`, `vlans`, `networks`, `subnetworks`,
     *              `ip_allocation_type_list`, `responsible_exp_list`,
     *              `dmz_list`, `wifi_list`, and `zone_list`.
     */
    public function create()
    {
        abort_if(Gate::denies('subnetwork_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // $connected_subnets = Subnetwork::all()->sortBy('name')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $gateways = Gateway::query()->orderBy('name')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $vlans = Vlan::query()->orderBy('name')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $networks = Network::query()->orderBy('name')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $subnetworks = Subnetwork::query()->orderBy('name')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        // lists
        $ip_allocation_type_list = Subnetwork::select('ip_allocation_type')->where('ip_allocation_type', '<>', null)->distinct()->orderBy('ip_allocation_type')->pluck('ip_allocation_type');
        $responsible_exp_list = Subnetwork::select('responsible_exp')->where('responsible_exp', '<>', null)->distinct()->orderBy('responsible_exp')->pluck('responsible_exp');
        $dmz_list = Subnetwork::select('dmz')->where('dmz', '<>', null)->distinct()->orderBy('dmz')->pluck('dmz');
        $wifi_list = Subnetwork::select('wifi')->where('wifi', '<>', null)->distinct()->orderBy('wifi')->pluck('wifi');
        $zone_list = Subnetwork::select('zone')->where('zone', '<>', null)->distinct()->orderBy('zone')->pluck('zone');
        $type_list = Subnetwork::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        return view(
            'admin.subnetworks.create',
            compact(
                'gateways',
                'vlans',
                'networks',
                'subnetworks',
                'ip_allocation_type_list',
                'responsible_exp_list',
                'dmz_list',
                'wifi_list',
                'zone_list',
                'type_list',
                'attributes_list'
            )
        );
    }

    /**
     * Display the edit form for a given subnetwork.
     *
     * Loads the `connected_subnets` and `gateway` relationships on the provided model
     * and prepares lists used to populate select inputs in the edit view.
     *
     * @param  Subnetwork  $subnetwork  The Subnetwork model to edit.
     * @return View The edit view populated with the subnetwork and selection lists.
     */
    public function edit(Subnetwork $subnetwork)
    {
        abort_if(Gate::denies('edit-object', $subnetwork), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // $connected_subnets = Subnetwork::all()->sortBy('name')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $gateways = Gateway::query()->orderBy('name')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $vlans = Vlan::query()->orderBy('name')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $networks = Network::query()->orderBy('name')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $subnetworks = Subnetwork::query()->whereKeyNot($subnetwork->id)->orderBy('name')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        // lists
        $ip_allocation_type_list = Subnetwork::select('ip_allocation_type')->where('ip_allocation_type', '<>', null)->distinct()->orderBy('ip_allocation_type')->pluck('ip_allocation_type');
        $responsible_exp_list = Subnetwork::select('responsible_exp')->where('responsible_exp', '<>', null)->distinct()->orderBy('responsible_exp')->pluck('responsible_exp');
        $dmz_list = Subnetwork::select('dmz')->where('dmz', '<>', null)->distinct()->orderBy('dmz')->pluck('dmz');
        $wifi_list = Subnetwork::select('wifi')->where('wifi', '<>', null)->distinct()->orderBy('wifi')->pluck('wifi');
        $zone_list = Subnetwork::select('zone')->where('zone', '<>', null)->distinct()->orderBy('zone')->pluck('zone');
        $type_list = Subnetwork::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        $subnetwork->load('connectedSubnets', 'gateway');

        return view(
            'admin.subnetworks.edit',
            compact(
                'subnetwork',
                'gateways',
                'vlans',
                'networks',
                'subnetworks',
                'ip_allocation_type_list',
                'responsible_exp_list',
                'dmz_list',
                'wifi_list',
                'zone_list',
                'type_list',
                'attributes_list'
            )
        );
    }

    public function update(UpdateSubnetworkRequest $request, Subnetwork $subnetwork)
    {
        abort_if(Gate::denies('edit-object', $subnetwork), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $subnetwork->update($request->all());

        return redirect()->route('admin.subnetworks.index');
    }

    public function show(Subnetwork $subnetwork)
    {
        abort_if(Gate::denies('show-object', $subnetwork), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subnetwork->load('connectedSubnets', 'gateway');

        return view('admin.subnetworks.show', compact('subnetwork'));
    }

    public function destroy(Subnetwork $subnetwork)
    {
        abort_if(Gate::denies('subnetwork_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subnetwork->delete();

        return redirect()->route('admin.subnetworks.index');
    }

    public function massDestroy(MassDestroySubnetworkRequest $request)
    {
        Subnetwork::whereIn('id', request('ids'))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function getAttributes()
    {
        $attributes_list = Subnetwork::query()
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
