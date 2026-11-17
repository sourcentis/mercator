<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyDomainRequest;
use App\Http\Requests\StoreDomainRequest;
use App\Http\Requests\UpdateDomainRequest;
use App\Models\Cartographer;
use App\Models\Domain;
use App\Models\ForestAd;
use App\Models\LogicalServer;
use Gate;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DomainController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('domain_access') ? null : Cartographer::allowedIdsFor($user, Domain::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $domains = Domain::query()
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Domain::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')
            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate($this->resolvePerPage());

        return view(
            'admin.domains.index',
            compact('domains')
        );
    }

    public function create()
    {
        abort_if(Gate::denies('domain_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $forestAds = ForestAd::all()->sortBy('name')->pluck('name', 'id');
        $logicalServers = LogicalServer::all()->sortBy('name')->pluck('name', 'id');
        $type_list = Domain::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        return view(
            'admin.domains.create',
            compact('forestAds', 'logicalServers', 'type_list', 'attributes_list')
        );
    }

    public function store(StoreDomainRequest $request)
    {
        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $domainAd = Domain::query()->create($request->all());
        $domainAd->forestAds()->sync($request->input('forestAds', []));

        LogicalServer::whereIn('id', $request->input('logicalServers', []))
            ->update(['domain_id' => $domainAd->id, 'updated_by' => Auth::id()]);

        return redirect()->route('admin.domains.index');
    }

    public function edit(Domain $domain)
    {
        abort_if(Gate::denies('edit-object', $domain), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $forestAds = ForestAd::all()->sortBy('name')->pluck('name', 'id');
        $logicalServers = LogicalServer::all()->sortBy('name')->pluck('name', 'id');
        $type_list = Domain::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();
        $domain->load('forestAds');

        return view(
            'admin.domains.edit',
            compact('domain', 'forestAds', 'logicalServers', 'type_list', 'attributes_list')
        );
    }

    public function update(UpdateDomainRequest $request, Domain $domain)
    {
        abort_if(Gate::denies('edit-object', $domain), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $domain->update($request->all());
        $domain->forestAds()->sync($request->input('forestAds', []));

        LogicalServer::where('domain_id', $domain->id)
            ->update(['domain_id' => null, 'updated_by' => Auth::id()]);

        LogicalServer::whereIn('id', $request->input('logicalServers', []))
            ->update(['domain_id' => $domain->id, 'updated_by' => Auth::id()]);

        return redirect()->route('admin.domains.index');
    }

    public function show(Domain $domain)
    {
        abort_if(Gate::denies('show-object', $domain), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $domain->load('forestAds');

        return view('admin.domains.show', compact('domain'));
    }

    public function destroy(Domain $domain)
    {
        abort_if(Gate::denies('domain_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $domain->delete();

        return redirect()->route('admin.domains.index');
    }

    public function massDestroy(MassDestroyDomainRequest $request)
    {
        Domain::whereIn('id', request('ids'))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function getAttributes()
    {
        $attributes_list = Domain::query()
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
