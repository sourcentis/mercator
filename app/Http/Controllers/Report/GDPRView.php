<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Report\Concerns\AutoloadsRelations;
use App\Models\Application;
use App\Models\Cartographer;
use App\Models\DataProcessing;
use App\Models\MacroProcessus;
use App\Models\Process;
use App\Models\SecurityControl;
use App\Services\Graph\GdprGraphBuilder;
use App\Services\Graph\GraphSize;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GDPRView extends Controller
{
    use AutoloadsRelations;

    /*
    * GDPR
    */
    public function generate(Request $request)
    {
        $allowed = Gate::allows('explore_access') || Cartographer::canAccessAny([DataProcessing::class, MacroProcessus::class, Process::class, SecurityControl::class]);
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

        // All macroprocess with process having a data_processing
        $all_macroprocess = Cartographer::scopedQuery(MacroProcessus::query()
            ->whereExists(function ($query): void {
                $query->select('processes.id')
                    ->from('processes')
                    ->join('data_processing_process', 'processes.id', '=', 'data_processing_process.process_id')
                    ->join('data_processing', 'data_processing_process.data_processing_id', '=', 'data_processing.id')
                    ->whereNull('data_processing.deleted_at')
                    ->whereRaw('macro_processuses.id = processes.macroprocess_id');
            })
            ->orderBy('name'))->get();

        if ($macroprocess !== null) {
            $macroProcessuses = Cartographer::scopedQuery(MacroProcessus::where('id', $macroprocess))->get();

            $all_process = Cartographer::scopedQuery(Process::orderBy('name')
                ->where('macroprocess_id', $macroprocess)
                ->whereExists(function ($query): void {
                    $query->select('data_processing_process.process_id')
                        ->from('data_processing_process')
                        ->join('data_processing', 'data_processing_process.data_processing_id', '=', 'data_processing.id')
                        ->whereNull('data_processing.deleted_at')
                        ->whereRaw('data_processing_process.process_id = processes.id');
                }))->get();

            if ($process !== null) {
                // Data processing of this process
                $dataProcessings = Cartographer::scopedQuery(DataProcessing::query()
                    ->whereExists(function ($query) use ($process): void {
                        $query->select('data_processing_id')
                            ->from('data_processing_process')
                            ->where('data_processing_process.process_id', $process)
                            ->whereRaw('data_processing_process.data_processing_id = data_processing.id');
                    })
                    ->orderBy('name'))->get();

                $processes = Cartographer::scopedQuery(Process::where('id', $process))->get();
            } else {
                // Data processing for this macroprocess
                $dataProcessings = Cartographer::scopedQuery(DataProcessing::query()
                    ->whereExists(function ($query) use ($macroprocess): void {
                        $query->select('data_processing_id')
                            ->from('data_processing_process')
                            ->join('processes', 'processes.id', 'data_processing_process.process_id')
                            ->where('processes.macroprocess_id', $macroprocess)
                            ->whereRaw('data_processing_process.data_processing_id = data_processing.id');
                    })
                    ->orderBy('name'))->get();
                $processes = $all_process;
            }
        } else {
            // only macroProcesses with data processisng
            $macroProcessuses = Cartographer::scopedQuery(MacroProcessus::orderBy('name')
                ->whereExists(function ($query): void {
                    $query->select('processes.id')
                        ->from('processes')
                        ->join('data_processing_process', 'data_processing_process.process_id', '=', 'processes.id')
                        ->join('data_processing', 'data_processing_process.data_processing_id', '=', 'data_processing.id')
                        ->whereNull('data_processing.deleted_at')
                        ->whereRaw('processes.macroprocess_id = macro_processuses.id');
                }))->get();

            // only process with data processisng
            $processes = Cartographer::scopedQuery(Process::query()
                ->whereExists(function ($query): void {
                    $query->select('data_processing_id')
                        ->from('data_processing_process')
                        ->join('data_processing', 'data_processing_process.data_processing_id', '=', 'data_processing.id')
                        ->whereNull('data_processing.deleted_at')
                        ->whereRaw('data_processing_process.process_id = processes.id');
                })
                ->orderBy('name'))->get();

            $dataProcessings = Cartographer::scopedQuery(DataProcessing::query()
                ->orderBy('name'))->get();

            $all_process = Cartographer::scopedQuery(Process::query()
                ->orderBy('name')
                ->where('macroprocess_id', $macroprocess)
                ->whereExists(function ($query): void {
                    $query->select('data_processing_process.process_id')
                        ->from('data_processing_process')
                        ->whereRaw('data_processing_process.process_id = processes.id');
                }))->get();
        }

        // Select applications
        $allowedAppIds = Cartographer::allowedIdsFor(auth()->user(), Application::class);
        $appQuery = Application::query()
            ->join('application_data_processing', 'application_id', 'applications.id')
            ->whereIn('data_processing_id', $dataProcessings->pluck('id')->all());
        if ($allowedAppIds && ! Gate::allows('application_access')) {
            $appQuery->whereIn('applications.id', $allowedAppIds);
        } elseif (! Gate::allows('application_access') && ! $allowedAppIds) {
            $appQuery->whereRaw('0 = 1');
        }
        $applications = $appQuery->get();

        $this->autoloadRelations($macroProcessuses, $processes, $dataProcessings, $applications);

        // Compte les nœuds avant de construire le DOT : un graphe trop grand n'est ni construit ni envoyé
        $graphTooLarge = GraphSize::tooLarge(GraphSize::count($macroProcessuses, $processes, $dataProcessings, $applications));

        $graphBuilder = new GdprGraphBuilder;

        return view('admin/reports/gdpr')
            ->with('all_macroprocess', $all_macroprocess)
            ->with('macroProcessuses', $macroProcessuses)
            ->with('processes', $processes)
            ->with('all_process', $all_process)
            ->with('dataProcessings', $dataProcessings)
            ->with('applications', $applications)
            ->with('graphTooLarge', $graphTooLarge)
            ->with('dotSrc', $graphTooLarge ? '' : $graphBuilder->buildDot($macroProcessuses, $processes, $dataProcessings, $applications))
            ->with('imageManifest', $graphBuilder->imageManifest());
    }
}
