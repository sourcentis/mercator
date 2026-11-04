<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationBlock;
use App\Models\ApplicationFlow;
use App\Models\ApplicationModule;
use App\Models\ApplicationService;
use App\Models\Cartographer;
use App\Models\Database;
use App\Services\Graph\ApplicationFlowGraphBuilder;
use App\Services\Graph\GraphSize;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ApplicationFlowView extends Controller
{
    public function generate(Request $request)
    {
        $allowed = Gate::allows('explore_access') || Cartographer::canAccessAny([
            ApplicationFlow::class, Application::class, ApplicationBlock::class,
        ]);
        abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Blocks
        if ($request->applicationBlocks == null) {
            $applicationBlocks = [];
            $request->session()->put('applicationBlocks', []);
        } else {
            if ($request->applicationBlocks != null) {
                $applicationBlocks = $request->applicationBlocks;
                $request->session()->put('applicationBlocks', $applicationBlocks);
            } else {
                $applicationBlocks = $request->session()->get('applicationBlocks');
            }
        }

        // Applications
        if ($request->applications == null) {
            $request->session()->put('applications', []);
            $applications = [];
        } else {
            if ($request->applications != null) {
                $applications = $request->applications;
                $request->session()->put('applications', $applications);
            } else {
                $applications = $request->session()->get('applications');
            }
        }

        // Databases
        if ($request->databases == null) {
            $request->session()->put('databases', []);
            $databases = [];
        } else {
            if ($request->databases != null) {
                $databases = $request->databases;
                $request->session()->put('databases', $databases);
            } else {
                $databases = $request->session()->get('databases');
            }
        }

        // Get assets
        $application_ids = DB::table('applications')
            ->whereIn('application_block_id', $applicationBlocks)
            ->whereNull('deleted_at')
            ->orWhereIn('id', $applications)
            ->pluck('id');

        $applicationservice_ids = DB::table('applications')
            ->join('application_application_service', 'applications.id', '=', 'application_application_service.application_id')
            ->whereIn('application_block_id', $applicationBlocks)
            ->whereNull('deleted_at')
            ->pluck('application_service_id')
            ->unique();

        $applicationmodule_ids = DB::table('applications')
            ->join('application_application_service', 'applications.id', '=', 'application_application_service.application_id')
            ->join('application_module_application_service', 'application_application_service.application_service_id', '=', 'application_module_application_service.application_service_id')
            ->whereNull('deleted_at')
            ->whereIn('application_block_id', $applicationBlocks)
            ->pluck('application_module_id')
            ->unique();

        $database_ids = collect($databases);

        // get all flows
        $flows = Cartographer::scopedQuery(ApplicationFlow::query())->orderBy('name')->get();

        // Filter Flows
        $flows = $flows
            ->filter(function ($item) use (
                $application_ids,
                $applicationservice_ids,
                $applicationmodule_ids,
                $database_ids
            ) {
                return // application
                    $application_ids->contains($item->application_source_id) ||
                    $application_ids->contains($item->application_dest_id) ||
                    // service
                    $applicationservice_ids->contains($item->service_source_id) ||
                    $applicationservice_ids->contains($item->service_dest_id) ||
                    // module
                    $applicationmodule_ids->contains($item->module_source_id) ||
                    $applicationmodule_ids->contains($item->module_dest_id) ||
                    // database
                    $database_ids->contains($item->database_source_id) ||
                    $database_ids->contains($item->database_dest_id);
            });

        // filter linked objects
        $application_ids = collect();
        $service_ids = collect();
        $module_ids = collect();

        // loop on flows
        foreach ($flows as $flow) {
            // applications
            if (($flow->application_source_id !== null) &&
               (! $application_ids->contains($flow->application_source_id))) {
                $application_ids->push($flow->application_source_id);
            }
            if (($flow->application_dest_id !== null) &&
               (! $application_ids->contains($flow->application_dest_id))) {
                $application_ids->push($flow->application_dest_id);
            }

            // services
            if (($flow->service_source_id !== null) &&
               (! $service_ids->contains($flow->service_source_id))) {
                $service_ids->push($flow->service_source_id);
            }
            if (($flow->service_dest_id !== null) &&
               (! $service_ids->contains($flow->service_dest_id))) {
                $service_ids->push($flow->service_dest_id);
            }

            // modules
            if (($flow->module_source_id !== null) &&
               (! $module_ids->contains($flow->module_source_id))) {
                $module_ids->push($flow->module_source_id);
            }
            if (($flow->module_dest_id !== null) &&
               (! $module_ids->contains($flow->module_dest_id))) {
                $module_ids->push($flow->module_dest_id);
            }

            // databases
            if (($flow->database_source_id !== null) &&
               (! $database_ids->contains($flow->database_source_id))) {
                $database_ids->push($flow->database_source_id);
            }
            if (($flow->database_dest_id !== null) &&
               (! $database_ids->contains($flow->database_dest_id))) {
                $database_ids->push($flow->database_dest_id);
            }
        }

        // get objects
        $applications = Cartographer::scopedQuery(Application::query()->whereIn('id', $application_ids))->orderBy('name')->get();
        $applicationServices = Cartographer::scopedQuery(ApplicationService::query()->whereIn('id', $service_ids))->orderBy('name')->get();
        $applicationModules = Cartographer::scopedQuery(ApplicationModule::query()->whereIn('id', $module_ids))->orderBy('name')->get();
        $databases = Cartographer::scopedQuery(Database::query()->whereIn('id', $database_ids))->orderBy('name')->get();

        // update lists
        $all_applicationBlocks = Cartographer::scopedQuery(ApplicationBlock::query())->orderBy('name')->pluck('name', 'id');
        $all_applications = Cartographer::scopedQuery(Application::query())->orderBy('name')->pluck('name', 'id');
        $all_databases = Cartographer::scopedQuery(Database::query())->orderBy('name')->pluck('name', 'id');

        // Compte les nœuds avant de construire le DOT : un graphe trop grand n'est ni construit ni envoyé
        $graphTooLarge = GraphSize::tooLarge(GraphSize::count($applications, $applicationServices, $applicationModules, $databases));

        $graphBuilder = new ApplicationFlowGraphBuilder;

        // return
        return view('admin/reports/application_flows')
            ->with('all_applicationBlocks', $all_applicationBlocks)
            ->with('all_applications', $all_applications)
            ->with('all_databases', $all_databases)
            ->with('applications', $applications)
            ->with('applicationServices', $applicationServices)
            ->with('applicationModules', $applicationModules)
            ->with('databases', $databases)
            ->with('flows', $flows)
            ->with('graphTooLarge', $graphTooLarge)
            ->with('dotSrc', $graphTooLarge ? '' : $graphBuilder->buildDot($applications, $applicationServices, $applicationModules, $databases, $flows))
            ->with('imageManifest', $graphBuilder->imageManifest($applications));
    }
}
