<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyApplicationRequest;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UpdateApplicationRequest;
use App\Models\Activity;
use App\Models\AdminUser;
use App\Models\Application;
use App\Models\ApplicationBlock;
use App\Models\ApplicationService;
use App\Models\Cartographer;
use App\Models\Container;
use App\Models\Database;
use App\Models\Entity;
use App\Models\LogicalServer;
use App\Models\Process;
use App\Models\SecurityDevice;
use App\Services\IconUploadService;
use Gate;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ApplicationController extends Controller
{
    public function __construct(private readonly IconUploadService $iconUploadService) {}

    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('application_access') ? null : Cartographer::allowedIdsFor($user, Application::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $applications = Application::with(
            'applicationBlock:id,name',
            'entityResp:id,name',
            'entities:id,name',
            'processes:id,name'
        )
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Application::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')

            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate($this->resolvePerPage());

        return view('admin.applications.index', compact('applications'));
    }

    public function create()
    {
        abort_if(Gate::denies('application_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        session()->put('documents', []);

        return view('admin.applications.create', $this->getCreateFormData());
    }

    public function clone(Request $request, int $id)
    {
        abort_if(Gate::denies('application_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->getCreateFormData();

        // Récupérer l'application avec ses relations
        $application = Application::query()
            ->with([
                'entities',
                'processes',
                'activities',
                'services',
                'databases',
                'workstations',
                'logicalServers',
                'containers',
                'securityDevices',
                'administrators',
                'securityControls',
            ])
            ->findOrFail($id);

        // Préparer les données du formulaire
        $formData = $application->only($application->getFillable());
        $formData['name'] = $application->name.' (copy)';

        // Champs multi-valeurs stockés en chaîne : la vue attend des tableaux
        $formData['attributes'] = array_values(array_filter(explode(' ', $application->attributes ?? '')));
        $formData['responsibles'] = array_values(array_filter(array_map('trim', explode(',', $application->responsible ?? ''))));
        $formData['technologies'] = array_values(array_filter(array_map('trim', explode(',', $application->technology ?? ''))));
        $formData['externals'] = array_values(array_filter(array_map('trim', explode(',', $application->external ?? ''))));

        // Ajouter les IDs des relations pour pré-sélection
        $formData['entities'] = $application->entities->pluck('id')->toArray();
        $formData['processes'] = $application->processes->pluck('id')->toArray();
        $formData['activities'] = $application->activities->pluck('id')->toArray();
        $formData['services'] = $application->services->pluck('id')->toArray();
        $formData['databases'] = $application->databases->pluck('id')->toArray();
        $formData['workstations'] = $application->workstations->pluck('id')->toArray();
        $formData['logical_servers'] = $application->logicalServers->pluck('id')->toArray();
        $formData['containers'] = $application->containers->pluck('id')->toArray();
        $formData['security_devices'] = $application->securityDevices->pluck('id')->toArray();
        $formData['administrators'] = $application->administrators->pluck('id')->toArray();
        $formData['security_controls'] = $application->securityControls->pluck('id')->toArray();

        $request->merge($formData);
        $request->flash();

        return view('admin.applications.create', $data);
    }

    private function getCreateFormData(): array
    {
        $entities = Entity::all()->sortBy('name')->pluck('name', 'id');
        $processes = Process::all()->sortBy('name')->pluck('name', 'id');
        $activities = Activity::all()->sortBy('name')->pluck('name', 'id');
        $services = ApplicationService::all()->sortBy('name')->pluck('name', 'id');
        $databases = Database::all()->sortBy('name')->pluck('name', 'id');
        $logical_servers = LogicalServer::all()->sortBy('name')->pluck('name', 'id');
        $containers = Container::all()->sortBy('name')->pluck('name', 'id');
        $security_devices = SecurityDevice::all()->sortBy('name')->pluck('name', 'id');
        $applicationBlocks = ApplicationBlock::all()->sortBy('name')->pluck('name', 'id');
        $icons = Application::select('icon_id')->whereNotNull('icon_id')->orderBy('icon_id')->distinct()->pluck('icon_id');
        $users = AdminUser::all()->sortBy('user_id')->mapWithKeys(function ($user) {
            return [$user->id => trim($user->firstname.' '.$user->lastname).' ['.$user->user_id.']'];
        });

        // lists
        $users_list = Application::select('users')->where('users', '<>', null)->distinct()->orderBy('users')->pluck('users');

        // Get Attributes
        $attributes_list = Application::select('attributes')
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
        sort($res);
        $attributes_list = array_unique($res);

        // Get Responsibles
        $responsible_list = Application::select('responsible')
            ->whereNotNull('responsible')
            ->distinct()
            ->orderBy('responsible')
            ->pluck('responsible');
        $res = [];
        foreach ($responsible_list as $i) {
            foreach (explode(',', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        $responsible_list = array_unique($res);

        // Get Technologies
        $technology_list = Application::select('technology')
            ->whereNotNull('technology')
            ->distinct()
            ->orderBy('technology')
            ->pluck('technology');
        $res = [];
        foreach ($technology_list as $i) {
            foreach (explode(',', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        $technology_list = array_unique($res);

        // Get Externals
        $external_list = Application::select('external')
            ->whereNotNull('external')
            ->distinct()
            ->orderBy('external')
            ->pluck('external');
        $res = [];
        foreach ($external_list as $i) {
            foreach (explode(',', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        $external_list = array_unique($res);

        // Get Types
        $type_list = Application::select('type')
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');
        $res = [];
        foreach ($type_list as $i) {
            foreach (explode(',', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        $type_list = array_unique($res);

        $referent_list = Application::select('functional_referent')->where('functional_referent', '<>', null)->distinct()->orderBy('functional_referent')->pluck('functional_referent');
        $editor_list = Application::select('editor')->where('editor', '<>', null)->distinct()->orderBy('editor')->pluck('editor');
        $hosting_list = Application::select('hosting')->where('hosting', '<>', null)->distinct()->orderBy('hosting')->pluck('hosting');
        $status_list = Application::select('status')->where('status', '<>', null)->distinct()->orderBy('status')->pluck('status');

        return compact(
            'entities',
            'processes',
            'activities',
            'services',
            'databases',
            'logical_servers',
            'containers',
            'security_devices',
            'applicationBlocks',
            'icons',
            'users',
            'type_list',
            'technology_list',
            'users_list',
            'external_list',
            'responsible_list',
            'referent_list',
            'editor_list',
            'hosting_list',
            'status_list',
            'attributes_list'
        );
    }

    public function store(StoreApplicationRequest $request)
    {
        $request->merge(['responsible' => implode(', ', $request->responsibles !== null ? $request->responsibles : [])]);
        $request->merge(['technology' => implode(', ', $request->technologies !== null ? $request->technologies : [])]);
        $request->merge(['external' => implode(', ', $request->externals !== null ? $request->externals : [])]);
        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        // Create application
        $application = Application::create($request->all());

        // Compute RTO - RPO
        $application->rto = $request->rto_days * 60 * 24 + $request->rto_hours * 60 + $request->rto_minutes;
        $application->rpo = $request->rpo_days * 60 * 24 + $request->rpo_hours * 60 + $request->rpo_minutes;

        // Save icon
        $this->iconUploadService->handle($request, $application);

        // Save application
        $application->save();

        // Save relations
        $application->entities()->sync($request->input('entities', []));
        $application->processes()->sync($request->input('processes', []));
        $application->activities()->sync($request->input('activities', []));
        $application->services()->sync($request->input('services', []));
        $application->databases()->sync($request->input('databases', []));
        $application->logicalServers()->sync($request->input('logical_servers', []));
        $application->containers()->sync($request->input('containers', []));
        $application->securityDevices()->sync($request->input('security_devices', []));
        $application->administrators()->sync($request->input('administrators', []));
        $application->documents()->sync(session()->get('documents', []));

        session()->forget('documents');

        return redirect()->route('admin.applications.index');
    }

    public function edit(Application $application)
    {
        abort_if(Gate::denies('edit-object', $application), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $entities = Entity::all()->sortBy('name')->pluck('name', 'id');
        $processes = Process::all()->sortBy('name')->pluck('name', 'id');
        $activities = Activity::all()->sortBy('name')->pluck('name', 'id');
        $services = ApplicationService::all()->sortBy('name')->pluck('name', 'id');
        $databases = Database::all()->sortBy('name')->pluck('name', 'id');
        $logical_servers = LogicalServer::all()->sortBy('name')->pluck('name', 'id');
        $containers = Container::all()->sortBy('name')->pluck('name', 'id');
        $security_devices = SecurityDevice::all()->sortBy('name')->pluck('name', 'id');
        $applicationBlocks = ApplicationBlock::all()->sortBy('name')->pluck('name', 'id');
        $icons = Application::select('icon_id')->whereNotNull('icon_id')->orderBy('icon_id')->distinct()->pluck('icon_id');
        $users = AdminUser::all()->sortBy('user_id')->mapWithKeys(function ($user) {
            return [$user->id => trim($user->firstname.' '.$user->lastname).' ['.$user->user_id.']'];
        });

        // rto-rpo
        $application['rto_days'] = intdiv($application->rto, 60 * 24);
        $application['rto_hours'] = intdiv($application->rto, 60) % 24;
        $application['rto_minutes'] = $application->rto % 60;

        $application['rpo_days'] = intdiv($application->rpo, 60 * 24);
        $application['rpo_hours'] = intdiv($application->rpo, 60) % 24;
        $application['rpo_minutes'] = $application->rpo % 60;

        // lists
        $users_list = Application::select('users')->where('users', '<>', null)->distinct()->orderBy('users')->pluck('users');

        // Get Attributes
        $attributes_list = Application::select('attributes')
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
        sort($res);
        $attributes_list = array_unique($res);

        $responsible_list = Application::select('responsible')->where('responsible', '<>', null)->distinct()->orderBy('responsible')->pluck('responsible');
        $res = [];
        foreach ($responsible_list as $i) {
            foreach (explode(',', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        $responsible_list = array_unique($res);

        $technology_list = Application::select('technology')->where('technology', '<>', null)->distinct()->orderBy('technology')->pluck('technology');
        $res = [];
        foreach ($technology_list as $i) {
            foreach (explode(',', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        $technology_list = array_unique($res);

        $external_list = Application::select('external')->where('external', '<>', null)->distinct()->orderBy('external')->pluck('external');
        $res = [];
        foreach ($external_list as $i) {
            foreach (explode(',', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        $external_list = array_unique($res);

        $type_list = Application::select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $res = [];
        foreach ($type_list as $i) {
            foreach (explode(',', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        $type_list = array_unique($res);

        $referent_list = Application::select('functional_referent')->where('functional_referent', '<>', null)->distinct()->orderBy('functional_referent')->pluck('functional_referent');
        $editor_list = Application::select('editor')->where('editor', '<>', null)->distinct()->orderBy('editor')->pluck('editor');
        $hosting_list = Application::select('hosting')->where('hosting', '<>', null)->distinct()->orderBy('hosting')->pluck('hosting');
        $status_list = Application::select('status')->where('status', '<>', null)->distinct()->orderBy('status')->pluck('status');

        $application->load('entities', 'entityResp', 'processes', 'services', 'databases', 'logicalServers', 'applicationBlock');

        $documents = [];
        foreach ($application->documents as $doc) {
            $documents[] = $doc->id;
        }
        session()->put('documents', $documents);

        return view(
            'admin.applications.edit',
            compact(
                'entities',
                'processes',
                'activities',
                'services',
                'databases',
                'logical_servers',
                'containers',
                'security_devices',
                'applicationBlocks',
                'icons',
                'users',
                'application',
                'type_list',
                'technology_list',
                'users_list',
                'external_list',
                'responsible_list',
                'referent_list',
                'editor_list',
                'hosting_list',
                'status_list',
                'attributes_list'
            )
        );
    }

    public function update(UpdateApplicationRequest $request, Application $application)
    {
        abort_if(Gate::denies('edit-object', $application), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $application->responsible = implode(', ', $request->responsibles !== null ? $request->responsibles : []);
        $application->technology = implode(', ', $request->technologies !== null ? $request->technologies : []);
        $application->external = implode(', ', $request->externals !== null ? $request->externals : []);
        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        // RTO-RPO
        $application->rto = $request->rto_days * 60 * 24 + $request->rto_hours * 60 + $request->rto_minutes;
        $application->rpo = $request->rpo_days * 60 * 24 + $request->rpo_hours * 60 + $request->rpo_minutes;

        // Save icon
        $this->iconUploadService->handle($request, $application);

        // Other fields
        $application->update($request->all());

        // Relations
        $application->entities()->sync($request->input('entities', []));
        $application->processes()->sync($request->input('processes', []));
        $application->activities()->sync($request->input('activities', []));
        $application->services()->sync($request->input('services', []));
        $application->databases()->sync($request->input('databases', []));
        $application->logicalServers()->sync($request->input('logical_servers', []));
        $application->containers()->sync($request->input('containers', []));
        $application->securityDevices()->sync($request->input('security_devices', []));
        $application->administrators()->sync($request->input('administrators', []));
        $application->documents()->sync(session()->get('documents', []));

        session()->forget('documents');

        return redirect()->route('admin.applications.index');
    }

    public function show(Application $application): View
    {
        abort_if(Gate::denies('show-object', $application), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $application->load('entities', 'entityResp', 'processes', 'services', 'databases', 'logicalServers', 'applicationBlock', 'applicationSourceFluxes', 'applicationDestFluxes');

        return view('admin.applications.show', compact('application'));
    }

    public function destroy(Application $application): Response
    {
        abort_if(Gate::denies('application_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $application->delete();

        return redirect()->route('admin.applications.index');
    }

    public function massDestroy(MassDestroyApplicationRequest $request): Response
    {
        Application::query()->whereIn('id', request('ids'))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
