<?php

use App\Models\Activity;
use App\Models\Actor;
use App\Models\Document;
use App\Models\Information;
use App\Models\MacroProcessus;
use App\Models\Operation;
use App\Models\Process;
use App\Models\Task;
use App\Models\User;
use App\Services\Graph\InformationSystemGraphBuilder;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    $this->actingAs(User::query()->where('login', 'admin@admin.com')->first());
});

test('buildDot draws the full macroprocess-to-information chain', function () {
    $macroProcess = MacroProcessus::factory()->create();
    $process = Process::factory()->create(['macroprocess_id' => $macroProcess->id]);
    $activity = Activity::factory()->create();
    $process->activities()->attach($activity->id);
    $operation = Operation::factory()->create();
    $activity->operations()->attach($operation->id);
    $task = Task::factory()->create();
    $operation->tasks()->attach($task->id);
    $actor = Actor::factory()->create();
    $operation->actors()->attach($actor->id);
    $information = Information::factory()->create();
    $process->information()->attach($information->id);

    $builder = new InformationSystemGraphBuilder;
    $dot = $builder->buildDot(
        MacroProcessus::all(),
        Process::with('activities', 'information', 'operations')->get(),
        Activity::with('operations')->get(),
        Operation::with('tasks', 'actors')->get(),
        Task::all(),
        Actor::all(),
        Information::with('children')->get(),
    );

    expect($dot)
        ->toContain('MP'.$macroProcess->id)
        ->toContain('MP'.$macroProcess->id.' -> P'.$process->id)
        ->toContain('P'.$process->id.' -> A'.$activity->id)
        ->toContain('A'.$activity->id.' -> O'.$operation->id)
        ->toContain('O'.$operation->id.' -> T'.$task->id)
        ->toContain('O'.$operation->id.' -> ACT'.$actor->id)
        ->toContain('P'.$process->id.' -> I'.$information->id);
});

test('imageManifest returns the fixed 7-entry icon list', function () {
    $manifest = (new InformationSystemGraphBuilder)->imageManifest();

    expect($manifest)->toHaveCount(7)
        ->and(collect($manifest)->pluck('path')->all())->toBe([
            '/images/macroprocess.png',
            '/images/process.png',
            '/images/activity.png',
            '/images/operation.png',
            '/images/task.png',
            '/images/actor.png',
            '/images/information.png',
        ]);
});

test('buildDot and imageManifest use the process custom icon when set', function () {
    $document = Document::factory()->create();
    $process = Process::factory()->create(['icon_id' => $document->id]);
    $processes = Process::with('activities', 'information', 'operations')->get();

    $builder = new InformationSystemGraphBuilder;
    $dot = $builder->buildDot(collect(), $processes, collect(), collect(), collect(), collect(), collect());
    $iconUrl = route('admin.documents.show', $document->id);

    expect($dot)->toContain($iconUrl)
        ->and(collect($builder->imageManifest($processes))->pluck('path'))->toContain($iconUrl);
});

test('buildDot and imageManifest use the information custom icon when set', function () {
    $document = Document::factory()->create();
    Information::factory()->create(['icon_id' => $document->id]);
    $informations = Information::with('children')->get();

    $builder = new InformationSystemGraphBuilder;
    $dot = $builder->buildDot(collect(), collect(), collect(), collect(), collect(), collect(), $informations);
    $iconUrl = route('admin.documents.show', $document->id);

    expect($dot)->toContain($iconUrl)
        ->and(collect($builder->imageManifest(collect(), $informations))->pluck('path'))->toContain($iconUrl);
});
