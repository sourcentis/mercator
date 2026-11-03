<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\LogicalServer;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PatchingController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('patching_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Get Attributes
        $attributes_list = $this->getAttributes();

        // Get attributes
        if ($request->get('clear') !== null) {
            session()->forget('attributes_filter');
            $attributes_filter = [];
        } else {
            $attributes_filter = $request->get('attributes_filter');
            if ($attributes_filter === null) {
                $attributes_filter = session()->get('attributes_filter');
                if ($attributes_filter === null) {
                    $attributes_filter = [];
                }
            } else {
                session()->put('attributes_filter', $attributes_filter);
            }
        }

        // Two separate queries (instead of a UNION) so that each row is hydrated as its
        // real model: links and permission checks then target the right object, and the
        // servers' applications can be eager-loaded in a single query (no N+1).
        $servers = LogicalServer::select(
            DB::raw("'SRV' as type"),
            'id',
            'name',
            'operating_system as version',
            'attributes',
            'update_date',
            'next_update',
            'perimeter_id'
        )
            ->where('active', '=', '1')
            ->with('applications:applications.id,applications.name,applications.responsible,applications.perimeter_id');

        $applications = Application::select(
            DB::raw("'APP' as type"),
            'id',
            'name',
            'vendor',
            'product',
            'version',
            'responsible',
            'attributes',
            'update_date',
            'next_update',
            'perimeter_id'
        );

        // Filter on attributes
        foreach ($attributes_filter as $a) {
            if (str_starts_with($a, '-')) {
                $servers->where('attributes', 'not like', '%'.substr($a, 1).'%');
                $applications->where('attributes', 'not like', '%'.substr($a, 1).'%');
            } else {
                $servers->where('attributes', 'like', '%'.$a.'%');
                $applications->where('attributes', 'like', '%'.$a.'%');
            }
        }
        $patches = $servers->get()
            ->concat($applications->get())
            ->sortBy('name', SORT_STRING | SORT_FLAG_CASE)
            ->values();

        return view('admin.patching.index', compact('patches', 'attributes_list', 'attributes_filter'));
    }

    public function editServer(Request $request)
    {
        abort_if(Gate::denies('patching_make'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $server = LogicalServer::find($request['id']);

        // Lists
        $attributes_list = $this->getAttributes();
        $operating_system_list = LogicalServer::select('operating_system')->where('operating_system', '<>', null)->distinct()->orderBy('operating_system')->pluck('operating_system');
        $environment_list = LogicalServer::select('environment')->where('environment', '<>', null)->distinct()->orderBy('environment')->pluck('environment');

        // Documents
        $documents = [];
        foreach ($server->documents as $doc) {
            $documents[] = $doc->id;
        }
        session()->put('documents', $documents);

        return view(
            'admin.patching.server',
            compact(
                'server',
                'operating_system_list',
                'environment_list',
                'attributes_list'
            )
        );
    }

    public function editApplication(Request $request)
    {
        abort_if(Gate::denies('patching_make'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $application = Application::find($request['id']);

        // Lists
        $attributes_list = $this->getAttributes();

        return view(
            'admin.patching.application',
            compact('application', 'attributes_list')
        );
    }

    public function updateServer(Request $request)
    {
        $logicalServer = LogicalServer::find($request['id']);
        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);
        $logicalServer->update($request->all());
        $logicalServer->documents()->sync(session()->get('documents'));
        session()->forget('documents');

        // Update frequency
        if ($request->get('global_periodicity') !== null) {
            $lservers = LogicalServer::query()->where('attributes', '=', $logicalServer->attributes)->get();
            foreach ($lservers as $s) {
                $s->patching_frequency = $logicalServer->patching_frequency;
                if ($s->update_date !== null) {
                    $s->next_update = Carbon::parse($s->update_date)
                        ->addMonths($logicalServer->patching_frequency);
                }
                $s->save();
            }
        }

        return redirect()->route('admin.patching.index');
    }

    public function updateApplication(Request $request)
    {
        $application = Application::find($request['id']);

        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);
        $application->update($request->all());

        // $logicalServer->documents()->sync(session()->get('documents'));
        // session()->forget('documents');

        // Update frequency
        if ($request->get('global_periodicity') !== null) {
            $apps = Application::where('attributes', '=', $application->attributes)->get();
            foreach ($apps as $s) {
                $s->patching_frequency = $application->patching_frequency;
                if ($s->update_date !== null) {
                    $s->next_update = Carbon::parse($s->update_date)->addMonths((int) $application->patching_frequency);
                }
                $s->save();
            }
        }

        return redirect()->route('admin.patching.index');
    }

    public function dashboard(Request $request)
    {
        // Get Attributes
        $attributes_list = $this->getAttributes();

        // Get attributes
        if ($request->get('clear') !== null) {
            session()->forget('attributes_filter');
            $attributes_filter = [];
        } else {
            $attributes_filter = $request->get('attributes_filter');
            if ($attributes_filter === null) {
                $attributes_filter = session()->get('attributes_filter');
                if ($attributes_filter === null) {
                    $attributes_filter = [];
                }
            } else {
                session()->put('attributes_filter', $attributes_filter);
            }
        }

        // Select
        $servers = LogicalServer::select(
            DB::raw("'SRV' as type"),
            'id',
            'name',
            DB::raw('null as vendor'),
            DB::raw('null as product'),
            'operating_system as version',
            DB::raw('null as responsible'),
            'attributes',
            'update_date',
            'next_update'
        )
            ->where('active', '=', 1);

        $applications = Application::select(
            DB::raw(
                "'APP' as type"
            ),
            'id',
            'name',
            'vendor',
            'product',
            'version',
            'responsible',
            'attributes',
            'update_date',
            'next_update'
        );

        // Filter on attributes
        foreach ($attributes_filter as $a) {
            if (str_starts_with($a, '-')) {
                $servers->where('attributes', 'not like', '%'.substr($a, 1).'%');
                $applications->where('attributes', 'not like', '%'.substr($a, 1).'%');
            } else {
                $servers->where('attributes', 'like', '%'.$a.'%');
                $applications->where('attributes', 'like', '%'.$a.'%');
            }
        }

        // Union
        $patches = $servers->union($applications)->orderBy('name')->get();

        // select distinct attributes
        $res = [];
        foreach ($patches as $p) {
            foreach (explode(' ', $p->attributes) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        sort($res);
        $active_attributes_list = array_unique($res);

        return view(
            'admin.patching.dashboard',
            compact(
                'patches',
                'attributes_list',
                'attributes_filter',
                'active_attributes_list'
            )
        );
    }

    private function getAttributes(): array
    {
        // Get Attributes
        $attributes_list = LogicalServer::select('attributes')
            ->whereNotNull('attributes')
            ->distinct()
            ->pluck('attributes');
        $res = [];
        foreach ($attributes_list as $i) {
            foreach (explode(' ', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        $attributes_list = Application::select('attributes')
            ->whereNotNull('attributes')
            ->distinct()
            ->pluck('attributes');
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
