<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Report\Concerns\AutoloadsRelations;
use App\Models\Application;
use App\Models\ApplicationBlock;
use App\Models\ApplicationFlow;
use App\Models\ApplicationModule;
use App\Models\ApplicationService;
use App\Models\Cartographer;
use App\Models\Database;
use App\Services\Graph\ApplicationGraphBuilder;
use App\Services\Graph\GraphSize;
use Gate;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ApplicationView extends Controller
{
    use AutoloadsRelations;

    /**
     * Prepare data for the applications report view based on the requested application block and application.
     *
     * Persists selected `applicationBlock` and `application` values in session, applies those selections to filter
     * application-related collections (blocks, applications, services, modules, databases, and fluxes), and returns
     * the view used to render the applications report. Access is denied with a 403 response when the current user
     * lacks the `reports_access` permission.
     *
     * @param  Request  $request  Request that may contain `applicationBlock` and `application` parameters used to filter results; values are stored in session when present.
     * @return View A view for 'admin/reports/applications' populated with the following keys: `all_applicationBlocks`, `all_applications`, `applicationBlocks`, `applications`, `applicationServices`, `applicationModules`, `databases`, and `fluxes`.
     */
    public function generate(Request $request): View
    {
        $allowed = Gate::allows('explore_access') || Cartographer::canAccessAny([ApplicationBlock::class, Application::class, ApplicationService::class, ApplicationModule::class, Database::class, ApplicationFlow::class]);
        abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->applicationBlock == null) {
            $request->session()->put('applicationBlock', null);
            $applicationBlock = null;
            $request->session()->put('application', null);
            $application = null;
        } else {
            $applicationBlock = intval($request->applicationBlock);
            $request->session()->put('applicationBlock', $applicationBlock);

            if ($request->application == null) {
                $request->session()->put('application', null);
                $application = null;
            } else {
                $application = intval($request->application);
                $request->session()->put('application', $application);
            }
        }

        $all_applicationBlocks = Cartographer::scopedQuery(ApplicationBlock::query())->orderBy('name')->get();

        if ($applicationBlock !== null) {
            $applicationBlocks = Cartographer::scopedQuery(ApplicationBlock::query())
                ->with(['perimeter', 'applications'])
                ->where('id', $applicationBlock)
                ->get();

            $all_applications = Cartographer::scopedQuery(Application::query())
                ->where('application_block_id', $applicationBlock)
                ->orderBy('name')
                ->get();

            $applications = Cartographer::scopedQuery(Application::query())
                ->with(self::applicationEagerLoads())
                ->when($application !== null, fn ($q) => $q->where('id', $application))
                ->when($application === null, fn ($q) => $q->where('application_block_id', $applicationBlock))
                ->orderBy('name')
                ->get();

            $serviceIds = $applications->flatMap(fn ($app) => $app->services->pluck('id'))->unique();

            $applicationServices = Cartographer::scopedQuery(ApplicationService::query())
                ->with(['perimeter', 'modules'])
                ->whereIn('id', $serviceIds)
                ->orderBy('name')
                ->get();

            $moduleIds = $applicationServices->flatMap(fn ($svc) => $svc->modules->pluck('id'))->unique();

            $applicationModules = Cartographer::scopedQuery(ApplicationModule::query())
                ->with(['perimeter', 'entities', 'applicationServices'])
                ->whereIn('id', $moduleIds)
                ->orderBy('name')
                ->get();

            $databaseIds = $applications->flatMap(fn ($app) => $app->databases->pluck('id'))->unique();

            $databases = Cartographer::scopedQuery(Database::query())
                ->with(self::databaseEagerLoads())
                ->whereIn('id', $databaseIds)
                ->orderBy('name')
                ->get();

            $appIds = $applications->pluck('id');
            $flows = Cartographer::scopedQuery(ApplicationFlow::query())
                ->with(self::flowEagerLoads())
                ->where(function ($q) use ($appIds, $moduleIds, $databaseIds) {
                    $q->whereIn('application_source_id', $appIds)
                        ->orWhereIn('application_dest_id', $appIds)
                        ->orWhereIn('module_source_id', $moduleIds)
                        ->orWhereIn('module_dest_id', $moduleIds)
                        ->orWhereIn('database_source_id', $databaseIds)
                        ->orWhereIn('database_dest_id', $databaseIds);
                })
                ->orderBy('name')
                ->get();
        } else {
            $applicationBlocks = Cartographer::scopedQuery(ApplicationBlock::query())->with(['perimeter', 'applications'])->orderBy('name')->get();
            $applications = Cartographer::scopedQuery(Application::query())->with(self::applicationEagerLoads())->orderBy('name')->get();
            $applicationServices = Cartographer::scopedQuery(ApplicationService::query())->with(['perimeter', 'modules'])->orderBy('name')->get();
            $applicationModules = Cartographer::scopedQuery(ApplicationModule::query())->with(['perimeter', 'entities', 'applicationServices'])->orderBy('name')->get();
            $databases = Cartographer::scopedQuery(Database::query())->with(self::databaseEagerLoads())->orderBy('name')->get();
            $flows = Cartographer::scopedQuery(ApplicationFlow::query())->with(self::flowEagerLoads())->orderBy('name')->get();
            $all_applications = null;
        }

        $this->autoloadRelations($applicationBlocks, $applications, $applicationServices, $applicationModules, $databases, $flows);

        // Compte les nœuds avant de construire le DOT : un graphe trop grand n'est ni construit ni envoyé
        $graphTooLarge = GraphSize::tooLarge(GraphSize::count($applicationBlocks, $applications, $applicationServices, $applicationModules, $databases));

        $graphBuilder = new ApplicationGraphBuilder;

        return view('admin/reports/applications')
            ->with('all_applicationBlocks', $all_applicationBlocks)
            ->with('all_applications', $all_applications)
            ->with('applicationBlocks', $applicationBlocks)
            ->with('applications', $applications)
            ->with('applicationServices', $applicationServices)
            ->with('applicationModules', $applicationModules)
            ->with('databases', $databases)
            ->with('flows', $flows)
            ->with('graphTooLarge', $graphTooLarge)
            ->with('dotSrc', $graphTooLarge ? '' : $graphBuilder->buildDot($applicationBlocks, $applications, $applicationServices, $applicationModules, $databases))
            ->with('imageManifest', $graphBuilder->imageManifest($applications, $databases));
    }

    /**
     * Relations the `_details` partial (and the "with link" perimeter label) read on
     * each Application, so the query builder can eager-load them instead of the view
     * lazy-loading one row at a time.
     *
     * @return array<int, string>
     */
    private static function applicationEagerLoads(): array
    {
        $relations = ['perimeter', 'applicationBlock', 'services', 'databases'];

        if (config('mercator.parameters.application_documents')) {
            $relations[] = 'documents';
        }

        return $relations;
    }

    /**
     * @return array<int, string>
     */
    private static function databaseEagerLoads(): array
    {
        return ['perimeter', 'entities', 'entityResp', 'informations', 'applications', 'logicalServers', 'containers'];
    }

    /**
     * @return array<int, string>
     */
    private static function flowEagerLoads(): array
    {
        return [
            'perimeter',
            'applicationSource', 'serviceSource', 'moduleSource', 'databaseSource',
            'applicationDest', 'serviceDest', 'moduleDest', 'databaseDest',
            'informations',
        ];
    }
}
