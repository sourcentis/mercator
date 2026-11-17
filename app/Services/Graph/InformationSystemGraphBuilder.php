<?php

namespace App\Services\Graph;

use App\Models\Activity;
use App\Models\Actor;
use App\Models\Cartographer;
use App\Models\Information;
use App\Models\MacroProcessus;
use App\Models\Operation;
use App\Models\Process;
use App\Models\Task;
use Illuminate\Support\Collection;

class InformationSystemGraphBuilder
{
    /**
     * @param  Collection<int, MacroProcessus>  $macroProcessuses
     * @param  Collection<int, Process>  $processes
     * @param  Collection<int, Activity>  $activities
     * @param  Collection<int, Operation>  $operations
     * @param  Collection<int, Task>  $tasks
     * @param  Collection<int, Actor>  $actors
     * @param  Collection<int, Information>  $informations
     * @param  array{withHref?: bool, iconResolver?: callable(?int, string): string}  $options
     */
    public function buildDot(
        Collection $macroProcessuses,
        Collection $processes,
        Collection $activities,
        Collection $operations,
        Collection $tasks,
        Collection $actors,
        Collection $informations,
        array $options = []
    ): string {
        $withHref = $options['withHref'] ?? true;
        // Resolves a per-record Document icon (Process and Information have one) or the type's static fallback
        // to whatever the consumer can actually embed: a route URL for the interactive/WASM
        // renderer (default), or a filesystem path for the server-side Word rasterization.
        $iconResolver = $options['iconResolver'] ?? fn (?int $iconId, string $fallback) => $iconId === null
            ? $fallback
            : route('admin.documents.show', $iconId);

        $lines = ['digraph  {'];

        // Precomputed id lookup sets: turns the O(n) Collection::contains() scan used below
        // for every edge candidate into an O(1) isset() check.
        $macroProcessusIds = array_flip($macroProcessuses->pluck('id')->all());
        $activityIds = array_flip($activities->pluck('id')->all());
        $operationIds = array_flip($operations->pluck('id')->all());
        $taskIds = array_flip($tasks->pluck('id')->all());
        $actorIds = array_flip($actors->pluck('id')->all());
        $informationIds = array_flip($informations->pluck('id')->all());

        foreach ($macroProcessuses as $macroProcess) {
            $lines[] = $this->node('MP', $macroProcess->id, $macroProcess->name, $iconResolver(null, '/images/macroprocess.png'), $macroProcess->getUID(), $withHref);
        }

        $canAccessInformation = Cartographer::canAccess(Information::class);

        foreach ($processes as $process) {
            $lines[] = $this->node('P', $process->id, $process->name, $iconResolver($process->icon_id, '/images/process.png'), $process->getUID(), $withHref);

            foreach ($process->activities as $activity) {
                if (isset($activityIds[$activity->id])) {
                    $lines[] = 'P'.$process->id.' -> A'.$activity->id;
                }
            }

            if ($canAccessInformation) {
                foreach ($process->information as $information) {
                    if (isset($informationIds[$information->id])) {
                        $lines[] = 'P'.$process->id.' -> I'.$information->id;
                    }
                }
            }

            if ($process->macroprocess_id !== null && isset($macroProcessusIds[$process->macroprocess_id])) {
                $lines[] = 'MP'.$process->macroprocess_id.' -> P'.$process->id;
            }

            foreach ($process->operations as $operation) {
                if (isset($operationIds[$operation->id])) {
                    $lines[] = 'P'.$process->id.' -> O'.$operation->id;
                }
            }
        }

        foreach ($activities as $activity) {
            $lines[] = $this->node('A', $activity->id, $activity->name, $iconResolver(null, '/images/activity.png'), $activity->getUID(), $withHref);

            foreach ($activity->operations as $operation) {
                if (isset($operationIds[$operation->id])) {
                    $lines[] = 'A'.$activity->id.' -> O'.$operation->id;
                }
            }
        }

        foreach ($operations as $operation) {
            $lines[] = $this->node('O', $operation->id, $operation->name, $iconResolver(null, '/images/operation.png'), $operation->getUID(), $withHref);

            foreach ($operation->tasks as $task) {
                if (isset($taskIds[$task->id])) {
                    $lines[] = 'O'.$operation->id.' -> T'.$task->id;
                }
            }

            foreach ($operation->actors as $actor) {
                if (isset($actorIds[$actor->id])) {
                    $lines[] = 'O'.$operation->id.' -> ACT'.$actor->id;
                }
            }
        }

        foreach ($tasks as $task) {
            $lines[] = $this->node('T', $task->id, $task->name, $iconResolver(null, '/images/task.png'), $task->getUID(), $withHref);
        }

        foreach ($actors as $actor) {
            $lines[] = $this->node('ACT', $actor->id, $actor->name, $iconResolver(null, '/images/actor.png'), $actor->getUID(), $withHref);
        }

        foreach ($informations as $information) {
            $lines[] = $this->node('I', $information->id, $information->name, $iconResolver($information->icon_id, '/images/information.png'), $information->getUID(), $withHref);

            foreach ($information->children as $child) {
                if (isset($informationIds[$child->id])) {
                    $lines[] = 'I'.$information->id.' -> I'.$child->id;
                }
            }
        }

        $lines[] = '}';

        return implode("\n", $lines);
    }

    /**
     * @param  Collection<int, Process>  $processes
     * @param  Collection<int, Information>  $informations
     * @return array<int, array{path: string, width: string, height: string}>
     */
    public function imageManifest(Collection $processes = new Collection, Collection $informations = new Collection): array
    {
        $manifest = [
            ['path' => '/images/macroprocess.png', 'width' => '64px', 'height' => '64px'],
            ['path' => '/images/process.png', 'width' => '64px', 'height' => '64px'],
            ['path' => '/images/activity.png', 'width' => '64px', 'height' => '64px'],
            ['path' => '/images/operation.png', 'width' => '64px', 'height' => '64px'],
            ['path' => '/images/task.png', 'width' => '64px', 'height' => '64px'],
            ['path' => '/images/actor.png', 'width' => '64px', 'height' => '64px'],
            ['path' => '/images/information.png', 'width' => '64px', 'height' => '64px'],
        ];

        if (Cartographer::canAccess(Process::class)) {
            foreach ($processes as $process) {
                if ($process->icon_id !== null) {
                    $manifest[] = ['path' => route('admin.documents.show', $process->icon_id), 'width' => '64px', 'height' => '64px'];
                }
            }
        }

        if (Cartographer::canAccess(Information::class)) {
            foreach ($informations as $information) {
                if ($information->icon_id !== null) {
                    $manifest[] = ['path' => route('admin.documents.show', $information->icon_id), 'width' => '64px', 'height' => '64px'];
                }
            }
        }

        return $manifest;
    }

    private function node(string $prefix, int $id, ?string $name, string $image, string $uid, bool $withHref): string
    {
        $href = $withHref ? ' href="#'.$uid.'"' : '';

        return DotNode::withImage($prefix.$id, $image, [e($name ?? '')], $href);
    }
}
