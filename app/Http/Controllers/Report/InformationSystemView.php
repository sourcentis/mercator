<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Actor;
use App\Models\Cartographer;
use App\Models\Information;
use App\Models\MacroProcessus;
use App\Models\Operation;
use App\Models\Process;
use App\Models\Task;
use App\Services\Graph\GraphSize;
use App\Services\Graph\InformationSystemGraphBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InformationSystemView extends Controller
{
    /**
     * Prepare data for and render the information system report view.
     *
     * Builds collections of macroprocesses, processes, activities, operations, tasks, actors,
     * and informations filtered by the optional `macroprocess` and `process` request inputs,
     * stores selected identifiers in session, and returns the report view.
     *
     * @param  Request  $request  HTTP request; may include `macroprocess` and `process` inputs used to filter results and persisted to session.
     * @return View The rendered 'admin/reports/information_system' view with these variables:
     *              - `all_macroprocess`: all MacroProcessus sorted by name
     *              - `macroProcessuses`: selected MacroProcessus collection
     *              - `processes`: filtered Process collection
     *              - `all_process`: all processes belonging to selected macroprocesses or null
     *              - `activities`: filtered Activity collection
     *              - `operations`: filtered Operation collection
     *              - `tasks`: filtered Task collection
     *              - `actors`: filtered Actor collection
     *              - `informations`: filtered Information collection
     *
     * @throws HttpException If the current user is denied the 'reports_access' permission (responds with 403).
     */
    public function generate(Request $request): View
    {
        $allowed = Gate::allows('explore_access') || Cartographer::canAccessAny([MacroProcessus::class, Process::class, Activity::class, Operation::class, Task::class, Actor::class, Information::class]);
        abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->macroprocess == null) {
            $request->session()->put('macroprocess', null);
            $macroprocess = null;
            $request->session()->put('process', null);
            $process = null;
        } else {
            if ($request->macroprocess != null) {
                $macroprocess = intval($request->macroprocess);
                $request->session()->put('macroprocess', $macroprocess);
            } else {
                $macroprocess = $request->session()->get('macroprocess');
            }

            if ($request->process == null) {
                $request->session()->put('process', null);
                $process = null;
            } elseif ($request->process != null) {
                $process = intval($request->process);
                $request->session()->put('process', $process);
            } else {
                $process = $request->session()->get('process');
            }
        }

        $all_macroprocess = Cartographer::scopedQuery(MacroProcessus::query())->orderBy('name')->get();

        // Relations consumed both by the view partials (admin/*/_details.blade.php) and by
        // InformationSystemGraphBuilder, eager-loaded up front to avoid per-row N+1 queries.
        $processWith = ['perimeter', 'macroProcess', 'activities', 'entities', 'information', 'applications', 'operations'];
        $activityWith = ['perimeter', 'processes', 'operations', 'applications'];
        $operationWith = ['perimeter', 'process', 'activities', 'actors', 'tasks'];
        $taskWith = ['perimeter', 'operations'];
        $actorWith = ['perimeter', 'operations'];
        $informationWith = ['perimeter', 'children'];

        if ($macroprocess !== null) {
            $macroProcessuses = MacroProcessus::where('macro_processuses.id', $macroprocess)
                ->with(['perimeter', 'processes'])
                ->get();
            $macroProcessusIds = $macroProcessuses->pluck('id');

            $processesQuery = Cartographer::scopedQuery(Process::query())->with($processWith);
            if ($process !== null) {
                $processesQuery->where('id', $process);
            } else {
                $processesQuery->whereIn('macroprocess_id', $macroProcessusIds);
            }
            $processes = $processesQuery->orderBy('name')->get();

            $all_process = Cartographer::scopedQuery(Process::query())
                ->whereIn('macroprocess_id', $macroProcessusIds)
                ->orderBy('name')
                ->get();

            $processIds = $processes->pluck('id');
            $activities = Cartographer::scopedQuery(Activity::query())
                ->whereHas('processes', fn ($q) => $q->whereIn('processes.id', $processIds))
                ->with($activityWith)
                ->orderBy('name')
                ->get();

            $activityIds = $activities->pluck('id');
            $operations = Cartographer::scopedQuery(Operation::query())
                ->whereHas('activities', fn ($q) => $q->whereIn('activities.id', $activityIds))
                ->with($operationWith)
                ->orderBy('name')
                ->get();

            $operationIds = $operations->pluck('id');
            $tasks = Cartographer::scopedQuery(Task::query())
                ->whereHas('operations', fn ($q) => $q->whereIn('operations.id', $operationIds))
                ->with($taskWith)
                ->orderBy('name')
                ->get();

            $actors = Cartographer::scopedQuery(Actor::query())
                ->whereHas('operations', fn ($q) => $q->whereIn('operations.id', $operationIds))
                ->with($actorWith)
                ->orderBy('name')
                ->get();

            // Collecter les IDs des informations liés aux processus (relation déjà eager-chargée ci-dessus)
            $directIds = $processes
                ->flatMap(fn ($process) => $process->information->pluck('id'))
                ->unique();

            // Descendre récursivement dans les enfants
            $allIds = $directIds->toArray();
            $toProcess = $directIds->toArray();

            while (! empty($toProcess)) {
                $childIds = Information::query()->whereIn('id', $toProcess)
                    ->with('children:id')
                    ->get()
                    ->flatMap(fn ($info) => $info->children->pluck('id'))
                    ->diff($allIds)   // évite les cycles et les doublons
                    ->unique()
                    ->values();

                $toProcess = $childIds->toArray();
                $allIds = array_merge($allIds, $toProcess);
            }

            $informations = Information::query()->whereIn('id', $allIds)
                ->with($informationWith)
                ->orderBy('name')
                ->get();

        } else {
            $macroProcessuses = Cartographer::scopedQuery(MacroProcessus::query())->with(['perimeter', 'processes'])->orderBy('name')->get();
            $processes = Cartographer::scopedQuery(Process::query())->with($processWith)->orderBy('name')->get();
            $activities = Cartographer::scopedQuery(Activity::query())->with($activityWith)->orderBy('name')->get();
            $operations = Cartographer::scopedQuery(Operation::query())->with($operationWith)->orderBy('name')->get();
            $tasks = Cartographer::scopedQuery(Task::query())->with($taskWith)->orderBy('name')->get();
            $actors = Cartographer::scopedQuery(Actor::query())->with($actorWith)->orderBy('name')->get();
            $informations = Cartographer::scopedQuery(Information::query()->with($informationWith)->orderBy('name'))->get();
            $all_process = null;
        }

        // Compte les nœuds avant de construire le DOT : un graphe trop grand n'est ni construit ni envoyé
        $graphTooLarge = GraphSize::tooLarge(GraphSize::count($macroProcessuses, $processes, $activities, $operations, $tasks, $actors, $informations));

        $graphBuilder = new InformationSystemGraphBuilder;

        return view('admin/reports/information_system')
            ->with('all_macroprocess', $all_macroprocess)
            ->with('macroProcessuses', $macroProcessuses)
            ->with('processes', $processes)
            ->with('all_process', $all_process)
            ->with('activities', $activities)
            ->with('operations', $operations)
            ->with('tasks', $tasks)
            ->with('actors', $actors)
            ->with('informations', $informations)
            ->with('graphTooLarge', $graphTooLarge)
            ->with('dotSrc', $graphTooLarge ? '' : $graphBuilder->buildDot($macroProcessuses, $processes, $activities, $operations, $tasks, $actors, $informations))
            ->with('imageManifest', $graphBuilder->imageManifest($processes, $informations));
    }
}
