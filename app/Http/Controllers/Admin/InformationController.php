<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyInformationRequest;
use App\Http\Requests\StoreInformationRequest;
use App\Http\Requests\UpdateInformationRequest;
use App\Models\Cartographer;
use App\Models\Information;
use App\Models\Process;
use App\Services\IconUploadService;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class InformationController extends Controller
{
    public function __construct(private readonly IconUploadService $iconUploadService) {}

    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('information_access') ? null : Cartographer::allowedIdsFor($user, Information::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $information = Information::query()
            ->with('parents', 'children')
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Information::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')
            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate($this->resolvePerPage());

        return view('admin.information.index', compact('information'));
    }

    public function create()
    {
        abort_if(Gate::denies('information_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $processes = Process::all()->sortBy('name')->pluck('name', 'id');
        $information_list = Information::all()->sortBy('name')->pluck('name', 'id');

        // lists
        $owner_list = Information::select('owner')->where('owner', '<>', null)->distinct()->orderBy('owner')->pluck('owner');
        $storage_list = Information::select('storage')->where('storage', '<>', null)->distinct()->orderBy('storage')->pluck('storage');
        $sensitivity_list = Information::select('sensitivity')->where('sensitivity', '<>', null)->distinct()->orderBy('sensitivity')->pluck('sensitivity');
        $administrator_list = Information::select('administrator')->where('administrator', '<>', null)->distinct()->orderBy('administrator')->pluck('administrator');
        $type_list = Information::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $retention_period_list = Information::query()->select('retention_period')->whereNotNull('retention_period')->distinct()->orderBy('retention_period')->pluck('retention_period');
        $final_disposition_list = Information::query()->select('final_disposition')->whereNotNull('final_disposition')->distinct()->orderBy('final_disposition')->pluck('final_disposition');
        $attributes_list = $this->getAttributes();
        $icons = Information::select('icon_id')->whereNotNull('icon_id')->orderBy('icon_id')->distinct()->pluck('icon_id');

        return view('admin.information.create', compact(
            'processes',
            'owner_list',
            'storage_list',
            'sensitivity_list',
            'administrator_list',
            'information_list',
            'type_list',
            'attributes_list',
            'retention_period_list',
            'final_disposition_list',
            'icons'
        ));
    }

    public function store(StoreInformationRequest $request)
    {
        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $information = Information::create($request->all());

        // Save icon
        $this->iconUploadService->handle($request, $information);
        $information->save();

        $information->processes()->sync($request->input('processes', []));
        $information->parents()->sync($request->input('parents', []));
        $information->children()->sync($request->input('children', []));

        return redirect()->route('admin.information.index');
    }

    public function edit(Information $information)
    {
        abort_if(Gate::denies('edit-object', $information), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $information->load('processes');

        // links
        $processes = Process::all()->sortBy('name')->pluck('name', 'id');
        $information_list = Information::all()->sortBy('name')->pluck('name', 'id');

        // lists
        $owner_list = Information::select('owner')->where('owner', '<>', null)->distinct()->orderBy('owner')->pluck('owner');
        $storage_list = Information::select('storage')->where('storage', '<>', null)->distinct()->orderBy('storage')->pluck('storage');
        $sensitivity_list = Information::select('sensitivity')->where('sensitivity', '<>', null)->distinct()->orderBy('sensitivity')->pluck('sensitivity');
        $administrator_list = Information::select('administrator')->where('administrator', '<>', null)->distinct()->orderBy('administrator')->pluck('administrator');
        $type_list = Information::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $retention_period_list = Information::query()->select('retention_period')->whereNotNull('retention_period')->distinct()->orderBy('retention_period')->pluck('retention_period');
        $final_disposition_list = Information::query()->select('final_disposition')->whereNotNull('final_disposition')->distinct()->orderBy('final_disposition')->pluck('final_disposition');
        $attributes_list = $this->getAttributes();
        $icons = Information::select('icon_id')->whereNotNull('icon_id')->orderBy('icon_id')->distinct()->pluck('icon_id');

        return view('admin.information.edit', compact(
            'processes',
            'information',
            'owner_list',
            'storage_list',
            'sensitivity_list',
            'administrator_list',
            'information_list',
            'type_list',
            'attributes_list',
            'retention_period_list',
            'final_disposition_list',
            'icons'
        ));
    }

    public function update(UpdateInformationRequest $request, Information $information)
    {
        abort_if(Gate::denies('edit-object', $information), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        // Save icon
        $this->iconUploadService->handle($request, $information);

        $information->update($request->all());

        $information->processes()->sync($request->input('processes', []));
        $information->parents()->sync($request->input('parents', []));
        $information->children()->sync($request->input('children', []));

        return redirect()->route('admin.information.index');
    }

    public function show(Information $information)
    {
        abort_if(Gate::denies('show-object', $information), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $information->load('processes', 'parents', 'children');

        return view('admin.information.show', compact('information'));
    }

    public function destroy(Information $information)
    {
        abort_if(Gate::denies('information_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $information->delete();

        return redirect()->route('admin.information.index');
    }

    public function massDestroy(MassDestroyInformationRequest $request)
    {
        Information::query()->whereIn('id', request('ids'))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function getAttributes()
    {
        $attributes_list = Information::query()
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
