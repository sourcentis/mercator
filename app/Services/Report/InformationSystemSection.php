<?php

namespace App\Services\Report;

use App\Models\Activity;
use App\Models\ActivityImpact;
use App\Models\Actor;
use App\Models\Cartographer;
use App\Models\Information;
use App\Models\MacroProcessus;
use App\Models\Operation;
use App\Models\Process;
use App\Models\Task;
use App\Services\Graph\InformationSystemGraphBuilder;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;

class InformationSystemSection implements ReportSection
{
    public function build(Section $section, WordHelper $helper, array $selectedVues): void
    {
        $section->addTitle(trans('cruds.report.cartography.information_system'), 1);

        $macroProcessuses = Cartographer::scopedQuery(MacroProcessus::query())
            ->with('processes')
            ->get()
            ->sortBy(fn (MacroProcessus $item) => mb_strtolower((string) $item->name));

        $processes = Cartographer::scopedQuery(Process::query())
            ->with(['macroProcess', 'activities', 'entities', 'information', 'applications'])
            ->get()
            ->sortBy(fn (Process $item) => mb_strtolower((string) $item->name));

        $activities = Cartographer::scopedQuery(Activity::query())
            ->with(['processes', 'operations', 'applications', 'impacts'])
            ->get()
            ->sortBy(fn (Activity $item) => mb_strtolower((string) $item->name));

        $operations = Cartographer::scopedQuery(Operation::query())
            ->with(['process', 'activities', 'actors', 'tasks'])
            ->get()
            ->sortBy(fn (Operation $item) => mb_strtolower((string) $item->name));

        $tasks = Cartographer::scopedQuery(Task::query())
            ->with('operations')
            ->get()
            ->sortBy(fn (Task $item) => mb_strtolower((string) $item->name));

        $actors = Cartographer::scopedQuery(Actor::query())
            ->with('operations')
            ->get()
            ->sortBy(fn (Actor $item) => mb_strtolower((string) $item->name));

        $informations = Cartographer::scopedQuery(Information::query())
            ->with(['parents', 'children', 'processes'])
            ->get()
            ->sortBy(fn (Information $item) => mb_strtolower((string) $item->name));

        $this->addMacroProcessuses($section, $helper, $macroProcessuses, $processes, $activities, $operations, $tasks, $actors, $informations, $selectedVues);
        $this->addProcesses($section, $helper, $processes, $selectedVues);
        $this->addActivities($section, $helper, $activities, $selectedVues);
        $this->addOperations($section, $helper, $operations, $selectedVues);
        $this->addTasks($section, $helper, $tasks, $selectedVues);
        $this->addActors($section, $helper, $actors, $selectedVues);
        $this->addInformation($section, $helper, $informations, $selectedVues);
    }

    /**
     * @param  Collection<int, MacroProcessus>  $macroProcessuses
     * @param  Collection<int, Process>  $processes
     * @param  Collection<int, Activity>  $activities
     * @param  Collection<int, Operation>  $operations
     * @param  Collection<int, Task>  $tasks
     * @param  Collection<int, Actor>  $actors
     * @param  Collection<int, Information>  $informations
     * @param  array<int, string>  $selectedVues
     */
    private function addMacroProcessuses(
        Section $section,
        WordHelper $helper,
        Collection $macroProcessuses,
        Collection $processes,
        Collection $activities,
        Collection $operations,
        Collection $tasks,
        Collection $actors,
        Collection $informations,
        array $selectedVues
    ): void {
        if ($macroProcessuses->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.macroProcessus.title'), 2);

        $graphBuilder = new InformationSystemGraphBuilder;

        foreach ($macroProcessuses as $macroProcess) {
            $helper->addBookmarkedTitle($section, $macroProcess->getUID(), (string) $macroProcess->name, 3);
            $this->addMacroProcessusGraph($section, $helper, $graphBuilder, $macroProcess, $processes, $activities, $operations, $tasks, $actors, $informations);

            $table = $helper->addTable($section, (string) $macroProcess->name);

            $helper->addTextRow($table, trans('cruds.macroProcessus.fields.type'), $macroProcess->type);
            $helper->addTextRow($table, trans('cruds.macroProcessus.fields.attributes'), $this->formatAttributes($macroProcess->attributes));
            $helper->addHTMLRow($table, trans('cruds.macroProcessus.fields.description'), $macroProcess->description);
            $helper->addHTMLRow($table, trans('cruds.macroProcessus.fields.io_elements'), $macroProcess->io_elements);
            $helper->addSecurityNeedRow(
                $table,
                trans('cruds.macroProcessus.fields.security_need'),
                $macroProcess->security_need_c,
                $macroProcess->security_need_i,
                $macroProcess->security_need_a,
                $macroProcess->security_need_t,
                $macroProcess->security_need_auth
            );
            $helper->addTextRow($table, trans('cruds.macroProcessus.fields.owner'), $macroProcess->owner);

            if ($macroProcess->processes->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.macroProcessus.fields.processes'), $macroProcess->processes, $selectedVues);
            }
        }
    }

    /**
     * Per-macro-processus subgraph: its own Process -> Activity/Information/Operation -> Task/Actor
     * descendants, computed by filtering the already-loaded master collections in-memory (no new
     * queries). Skipped when the macro-processus has no processes (nothing to draw beyond itself).
     *
     * @param  Collection<int, Process>  $processes
     * @param  Collection<int, Activity>  $activities
     * @param  Collection<int, Operation>  $operations
     * @param  Collection<int, Task>  $tasks
     * @param  Collection<int, Actor>  $actors
     * @param  Collection<int, Information>  $informations
     */
    private function addMacroProcessusGraph(
        Section $section,
        WordHelper $helper,
        InformationSystemGraphBuilder $graphBuilder,
        MacroProcessus $macroProcess,
        Collection $processes,
        Collection $activities,
        Collection $operations,
        Collection $tasks,
        Collection $actors,
        Collection $informations
    ): void {
        $ownProcesses = $processes->where('macroprocess_id', $macroProcess->id);
        if ($ownProcesses->isEmpty()) {
            return;
        }

        $activityIds = $ownProcesses->flatMap(fn (Process $process) => $process->activities->pluck('id'))->unique();
        $ownActivities = $activities->whereIn('id', $activityIds);

        $informationIds = $ownProcesses->flatMap(fn (Process $process) => $process->information->pluck('id'))->unique();
        $ownInformations = $informations->whereIn('id', $informationIds);

        $operationIds = $ownProcesses->flatMap(fn (Process $process) => $process->operations->pluck('id'))
            ->merge($ownActivities->flatMap(fn (Activity $activity) => $activity->operations->pluck('id')))
            ->unique();
        $ownOperations = $operations->whereIn('id', $operationIds);

        $taskIds = $ownOperations->flatMap(fn (Operation $operation) => $operation->tasks->pluck('id'))->unique();
        $ownTasks = $tasks->whereIn('id', $taskIds);

        $actorIds = $ownOperations->flatMap(fn (Operation $operation) => $operation->actors->pluck('id'))->unique();
        $ownActors = $actors->whereIn('id', $actorIds);

        $dot = $graphBuilder->buildDot(
            collect([$macroProcess]),
            $ownProcesses,
            $ownActivities,
            $ownOperations,
            $ownTasks,
            $ownActors,
            $ownInformations,
            [
                'withHref' => false,
                'iconResolver' => fn (?int $iconId, string $fallback) => $helper->resolveIconPath($iconId, ltrim($fallback, '/')),
            ]
        );
        $helper->insertGraph($section, $dot);
    }

    /**
     * @param  Collection<int, Process>  $processes
     * @param  array<int, string>  $selectedVues
     */
    private function addProcesses(Section $section, WordHelper $helper, Collection $processes, array $selectedVues): void
    {
        if ($processes->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.process.title'), 2);

        foreach ($processes as $process) {
            $helper->addBookmarkedTitle($section, $process->getUID(), (string) $process->name, 3);
            $table = $helper->addTable($section, (string) $process->name);

            if ($process->macroProcess !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.process.fields.macroprocessus'));
                $helper->linkOrText($run, $process->macroProcess, $selectedVues);
            }

            $helper->addTextRow($table, trans('cruds.process.fields.type'), $process->type);
            $helper->addTextRow($table, trans('cruds.process.fields.attributes'), $this->formatAttributes($process->attributes));
            $helper->addDescriptionCellWithIcon($table, trans('cruds.process.fields.description'), $process->description, $process, '/images/process.png');
            $helper->addHTMLRow($table, trans('cruds.process.fields.in_out'), $process->in_out);
            $helper->addSecurityNeedRow(
                $table,
                trans('cruds.process.fields.security_need'),
                $process->security_need_c,
                $process->security_need_i,
                $process->security_need_a,
                $process->security_need_t,
                $process->security_need_auth
            );
            $helper->addTextRow($table, trans('cruds.process.fields.owner'), $process->owner);

            if ($process->activities->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.process.fields.activities'), $process->activities, $selectedVues);
            }

            if ($process->entities->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.process.fields.entities'), $process->entities, $selectedVues);
            }

            if ($process->information->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.process.fields.informations'), $process->information, $selectedVues);
            }

            if ($process->applications->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.process.fields.applications'), $process->applications, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @param  array<int, string>  $selectedVues
     */
    private function addActivities(Section $section, WordHelper $helper, Collection $activities, array $selectedVues): void
    {
        if ($activities->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.activity.title'), 2);

        foreach ($activities as $activity) {
            $helper->addBookmarkedTitle($section, $activity->getUID(), (string) $activity->name, 3);
            $table = $helper->addTable($section, (string) $activity->name);

            $helper->addTextRow($table, trans('cruds.activity.fields.type'), $activity->type);
            $helper->addTextRow($table, trans('cruds.activity.fields.attributes'), $this->formatAttributes($activity->attributes));
            $helper->addHTMLRow($table, trans('cruds.activity.fields.description'), $activity->description);

            if ($activity->processes->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.activity.fields.processes'), $activity->processes, $selectedVues);
            }

            if ($activity->operations->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.activity.fields.operations'), $activity->operations, $selectedVues);
            }

            if ($activity->applications->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.activity.fields.applications'), $activity->applications, $selectedVues);
            }

            $helper->addTextRow($table, trans('cruds.activity.fields.maximum_tolerable_downtime'), $helper->formatMinutesDuration($activity->maximum_tolerable_downtime));
            $helper->addTextRow($table, trans('cruds.activity.fields.maximum_tolerable_data_loss'), $helper->formatMinutesDuration($activity->maximum_tolerable_data_loss));
            $helper->addTextRow($table, trans('cruds.activity.fields.recovery_time_objective'), $helper->formatMinutesDuration($activity->recovery_time_objective));
            $helper->addTextRow($table, trans('cruds.activity.fields.recovery_point_objective'), $helper->formatMinutesDuration($activity->recovery_point_objective));

            if ($activity->impacts->isNotEmpty()) {
                $helper->addNestedTableRow(
                    $table,
                    trans('cruds.activity.impacts'),
                    [trans('cruds.activity.fields.impact_type'), trans('cruds.activity.fields.gravity')],
                    $activity->impacts->map(fn (ActivityImpact $impact) => [
                        (string) $impact->impact_type,
                        $helper->riskLabel($impact->severity),
                    ])
                );
            }

            $helper->addHTMLRow($table, trans('cruds.activity.fields.drp'), $activity->drp);
            $this->addUrlOrTextRow($table, $helper, trans('cruds.activity.fields.drp_link'), $activity->drp_link);
        }
    }

    /**
     * @param  Collection<int, Operation>  $operations
     * @param  array<int, string>  $selectedVues
     */
    private function addOperations(Section $section, WordHelper $helper, Collection $operations, array $selectedVues): void
    {
        if ($operations->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.operation.title'), 2);

        foreach ($operations as $operation) {
            $helper->addBookmarkedTitle($section, $operation->getUID(), (string) $operation->name, 3);
            $table = $helper->addTable($section, (string) $operation->name);

            $helper->addTextRow($table, trans('cruds.operation.fields.type'), $operation->type);
            $helper->addTextRow($table, trans('cruds.operation.fields.attributes'), $this->formatAttributes($operation->attributes));
            $helper->addHTMLRow($table, trans('cruds.operation.fields.description'), $operation->description);

            if ($operation->process !== null) {
                $run = $helper->addTextRunRow($table, trans('cruds.operation.fields.process'));
                $helper->linkOrText($run, $operation->process, $selectedVues);
            }

            if ($operation->activities->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.operation.fields.activities'), $operation->activities, $selectedVues);
            }

            if ($operation->actors->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.operation.fields.actors'), $operation->actors, $selectedVues);
            }

            if ($operation->tasks->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.operation.fields.tasks'), $operation->tasks, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, Task>  $tasks
     * @param  array<int, string>  $selectedVues
     */
    private function addTasks(Section $section, WordHelper $helper, Collection $tasks, array $selectedVues): void
    {
        if ($tasks->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.task.title'), 2);

        foreach ($tasks as $task) {
            $helper->addBookmarkedTitle($section, $task->getUID(), (string) $task->name, 3);
            $table = $helper->addTable($section, (string) $task->name);

            $helper->addTextRow($table, trans('cruds.task.fields.type'), $task->type);
            $helper->addTextRow($table, trans('cruds.task.fields.attributes'), $this->formatAttributes($task->attributes));
            $helper->addHTMLRow($table, trans('cruds.task.fields.description'), $task->description);

            if ($task->operations->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.task.fields.operations'), $task->operations, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, Actor>  $actors
     * @param  array<int, string>  $selectedVues
     */
    private function addActors(Section $section, WordHelper $helper, Collection $actors, array $selectedVues): void
    {
        if ($actors->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.actor.title'), 2);

        foreach ($actors as $actor) {
            $helper->addBookmarkedTitle($section, $actor->getUID(), (string) $actor->name, 3);
            $table = $helper->addTable($section, (string) $actor->name);

            $helper->addTextRow($table, trans('cruds.actor.fields.contact'), $actor->contact);
            $helper->addTextRow($table, trans('cruds.actor.fields.nature'), $actor->nature);
            $helper->addTextRow($table, trans('cruds.actor.fields.type'), $actor->type);
            $helper->addTextRow($table, trans('cruds.actor.fields.attributes'), $this->formatAttributes($actor->attributes));

            if ($actor->operations->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.actor.fields.operations'), $actor->operations, $selectedVues);
            }
        }
    }

    /**
     * @param  Collection<int, Information>  $informations
     * @param  array<int, string>  $selectedVues
     */
    private function addInformation(Section $section, WordHelper $helper, Collection $informations, array $selectedVues): void
    {
        if ($informations->isEmpty()) {
            return;
        }

        $section->addTitle(trans('cruds.information.title'), 2);

        foreach ($informations as $information) {
            $helper->addBookmarkedTitle($section, $information->getUID(), (string) $information->name, 3);
            $table = $helper->addTable($section, (string) $information->name);

            $helper->addTextRow($table, trans('cruds.information.fields.type'), $information->type);
            $helper->addTextRow($table, trans('cruds.information.fields.attributes'), $this->formatAttributes($information->attributes));
            $helper->addDescriptionCellWithIcon($table, trans('cruds.information.fields.description'), $information->description, $information, '/images/information.png');
            $helper->addTextRow($table, trans('cruds.information.fields.owner'), $information->owner);
            $helper->addTextRow($table, trans('cruds.information.fields.administrator'), $information->administrator);
            $helper->addTextRow($table, trans('cruds.information.fields.storage'), $information->storage);
            $helper->addTextRow($table, trans('cruds.information.fields.sensitivity'), $information->sensitivity);
            $helper->addSecurityNeedRow(
                $table,
                trans('cruds.information.fields.security_need'),
                $information->security_need_c,
                $information->security_need_i,
                $information->security_need_a,
                $information->security_need_t,
                $information->security_need_auth
            );

            if ($information->parents->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.information.fields.parents'), $information->parents, $selectedVues);
            }

            if ($information->children->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.information.fields.children'), $information->children, $selectedVues);
            }

            if ($information->processes->isNotEmpty()) {
                $helper->addLinkListRow($table, trans('cruds.information.fields.processes'), $information->processes, $selectedVues);
            }

            $helper->addHTMLRow($table, trans('cruds.information.fields.constraints'), $information->constraints);
            $helper->addTextRow($table, trans('cruds.information.fields.retention_period'), $information->retention_period);
            $helper->addTextRow($table, trans('cruds.information.fields.final_disposition'), $information->final_disposition);
            $helper->addHTMLRow($table, trans('cruds.information.fields.retention_notes'), $information->retention_notes);
        }
    }

    private function formatAttributes(?string $attributes): ?string
    {
        if ($attributes === null || trim($attributes) === '') {
            return $attributes;
        }

        return implode(', ', array_filter(explode(' ', $attributes)));
    }

    private function addUrlOrTextRow(Table $table, WordHelper $helper, string $title, ?string $value): void
    {
        if ($value !== null && filter_var($value, FILTER_VALIDATE_URL)) {
            $run = $helper->addTextRunRow($table, $title);
            $run->addLink($value, $value, WordHelper::FANCY_LINK_STYLE, null, false);

            return;
        }

        $helper->addTextRow($table, $title, $value);
    }
}
