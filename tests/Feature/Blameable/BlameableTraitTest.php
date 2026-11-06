<?php

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\LogicalServer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->userA = User::factory()->create();
    $this->userB = User::factory()->create();
});

dataset('blameable models', [
    'Application' => [Application::class],
    'LogicalServer' => [LogicalServer::class],
    'Document' => [Document::class],
]);

function blameRow(string $class, int $id): ?object
{
    return DB::table((new $class)->getTable())->where('id', $id)->first();
}

it('sets created_by and updated_by on creation', function (string $class) {
    $this->actingAs($this->userA);

    $model = $class::factory()->create();

    $row = blameRow($class, $model->id);
    expect($row->created_by)->toBe($this->userA->id)
        ->and($row->updated_by)->toBe($this->userA->id)
        ->and($row->deleted_by)->toBeNull();
})->with('blameable models');

it('sets updated_by on update and keeps created_by', function (string $class) {
    $this->actingAs($this->userA);
    $model = $class::factory()->create();

    $this->actingAs($this->userB);
    $model->forceFill([$class === Document::class ? 'filename' : 'name' => 'changed-'.uniqid()])->save();

    $row = blameRow($class, $model->id);
    expect($row->created_by)->toBe($this->userA->id)
        ->and($row->updated_by)->toBe($this->userB->id);
})->with('blameable models');

it('does not change updated_by when saving without changes', function (string $class) {
    $this->actingAs($this->userA);
    $model = $class::factory()->create();

    $this->actingAs($this->userB);
    $model->save();

    expect(blameRow($class, $model->id)->updated_by)->toBe($this->userA->id);
})->with('blameable models');

it('stores null without an authenticated user', function (string $class) {
    $model = $class::factory()->create();
    expect(blameRow($class, $model->id)->created_by)->toBeNull()
        ->and(blameRow($class, $model->id)->updated_by)->toBeNull();

    $this->actingAs($this->userA);
    $model->forceFill([$class === Document::class ? 'filename' : 'name' => 'changed-'.uniqid()])->save();
    expect(blameRow($class, $model->id)->updated_by)->toBe($this->userA->id);

    Auth::logout();
    $model->forceFill([$class === Document::class ? 'filename' : 'name' => 'again-'.uniqid()])->save();
    expect(blameRow($class, $model->id)->updated_by)->toBeNull();
})->with('blameable models');

it('ignores an injected created_by', function () {
    $this->actingAs($this->userA);

    $application = Application::create(['name' => 'Injected', 'created_by' => 999]);
    $logicalServer = LogicalServer::factory()->make();
    $logicalServer->forceFill(['created_by' => $this->userB->id, 'updated_by' => $this->userB->id])->save();

    expect(blameRow(Application::class, $application->id)->created_by)->toBe($this->userA->id)
        ->and(blameRow(LogicalServer::class, $logicalServer->id)->created_by)->toBe($this->userA->id)
        ->and(blameRow(LogicalServer::class, $logicalServer->id)->updated_by)->toBe($this->userA->id);
});

it('blames the replicating user, not the original author', function (string $class) {
    $this->actingAs($this->userA);
    $model = $class::factory()->create();

    $this->actingAs($this->userB);
    $copy = $model->replicate();
    if ($class !== Document::class) {
        $copy->name = 'copy-'.uniqid();
    }
    $copy->save();

    $row = blameRow($class, $copy->id);
    expect($row->created_by)->toBe($this->userB->id)
        ->and($row->updated_by)->toBe($this->userB->id);
})->with('blameable models');

it('records deleted_by on soft delete without touching updated_at', function (string $class) {
    Carbon::setTestNow('2026-01-01 10:00:00');
    $this->actingAs($this->userA);
    $model = $class::factory()->create();

    Carbon::setTestNow('2026-01-02 10:00:00');
    $this->actingAs($this->userB);
    $model->delete();

    $row = blameRow($class, $model->id);
    expect($row->deleted_by)->toBe($this->userB->id)
        // updated_at is only set by the soft delete itself, at deletion time
        ->and(Carbon::parse($row->updated_at)->equalTo(Carbon::parse($row->deleted_at)))->toBeTrue()
        // the deleted_by write does not count as an update
        ->and($row->updated_by)->toBe($this->userA->id);

    $audit = AuditLog::query()
        ->where('subject_type', $class)
        ->where('subject_id', $model->id)
        ->where('description', 'deleted')
        ->firstOrFail();
    expect($audit->properties['deleted_by'])->toBe($this->userB->id);

    Carbon::setTestNow();
})->with('blameable models');

it('clears deleted_by on restore and blames the restoring user', function (string $class) {
    $this->actingAs($this->userB);
    $model = $class::factory()->create();
    $model->delete();

    $this->actingAs($this->userA);
    $class::withTrashed()->findOrFail($model->id)->restore();

    $row = blameRow($class, $model->id);
    expect($row->deleted_at)->toBeNull()
        ->and($row->deleted_by)->toBeNull()
        ->and($row->updated_by)->toBe($this->userA->id);
})->with('blameable models');

it('force deletes without error', function (string $class) {
    $this->actingAs($this->userA);
    $model = $class::factory()->create();

    $model->forceDelete();

    expect(blameRow($class, $model->id))->toBeNull();
})->with('blameable models');

it('sets the blame columns to null when the user is purged', function () {
    $this->actingAs($this->userA);
    $application = Application::factory()->create();

    Auth::logout();
    $this->userA->forceDelete();

    $row = blameRow(Application::class, $application->id);
    expect($row->created_by)->toBeNull()
        ->and($row->updated_by)->toBeNull();
});

it('resolves creator, updater and deleter even when soft deleted', function () {
    $this->actingAs($this->userA);
    $application = Application::factory()->create();
    $this->actingAs($this->userB);
    $application->update(['name' => 'changed-'.uniqid()]);
    $application->delete();

    Auth::logout();
    $this->userA->delete();
    $this->userB->delete();

    $application = Application::withTrashed()->findOrFail($application->id);
    expect($application->creator?->id)->toBe($this->userA->id)
        ->and($application->creator->trashed())->toBeTrue()
        ->and($application->updater?->id)->toBe($this->userB->id)
        ->and($application->deleter?->id)->toBe($this->userB->id);
});
