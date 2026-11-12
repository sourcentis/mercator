<?php

use App\Console\Commands\DatabaseCleanup;
use App\Models\Annuaire;
use App\Models\Application;
use App\Models\ApplicationEvent;
use App\Models\AuditLog;
use App\Models\Entity;
use App\Models\Parameter;
use App\Models\Relation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

function enableCleanup(bool $logs = true, bool $objects = true): void
{
    config(['mercator.cleanup' => [
        'audit_logs_enabled' => $logs,
        'audit_logs_retention' => 12,
        'deleted_objects_enabled' => $objects,
        'deleted_objects_retention' => 3,
    ]]);
}

function auditLogAt(string $date): int
{
    return DB::table('audit_logs')->insertGetId([
        'description' => 'updated',
        'subject_id' => 1,
        'subject_type' => Application::class,
        'created_at' => $date,
        'updated_at' => $date,
    ]);
}

function trashApplication(Application $application, string $date): void
{
    DB::table('applications')->where('id', $application->id)->update(['deleted_at' => $date]);
}

it('does nothing when the cleanup is disabled', function () {
    enableCleanup(false, false);
    $old = auditLogAt(now()->subYears(3)->toDateTimeString());
    $application = Application::factory()->create();
    trashApplication($application, now()->subYears(3)->toDateTimeString());

    expect(Artisan::call('mercator:db-cleanup'))->toBe(0);

    expect(DB::table('audit_logs')->where('id', $old)->exists())->toBeTrue();
    expect(Application::withTrashed()->find($application->id))->not->toBeNull();
    expect(Parameter::getValue(DatabaseCleanup::LAST_RUN_PARAMETER))->toBeNull();
});

it('purges audit log entries older than the retention period', function () {
    enableCleanup(true, false);
    $old = auditLogAt(now()->subMonths(13)->toDateTimeString());
    $recent = auditLogAt(now()->subMonths(11)->toDateTimeString());

    Artisan::call('mercator:db-cleanup');

    expect(DB::table('audit_logs')->where('id', $old)->exists())->toBeFalse();
    expect(DB::table('audit_logs')->where('id', $recent)->exists())->toBeTrue();

    $lastRun = json_decode(Parameter::getValue(DatabaseCleanup::LAST_RUN_PARAMETER), true);
    expect($lastRun['audit_logs'])->toBe(1);
});

it('permanently deletes objects deleted longer ago than the retention period', function () {
    enableCleanup(false, true);

    $old = Application::factory()->create();
    $recent = Application::factory()->create();
    $live = Application::factory()->create();

    // Références vers l'objet purgé : pivot, ligne dépendante et colonne nullable.
    $entity = Entity::factory()->create();
    $old->entities()->attach($entity->id);
    $event = ApplicationEvent::factory()->create(['application_id' => $old->id]);
    $annuaire = Annuaire::factory()->create(['application_id' => $old->id]);

    trashApplication($old, now()->subMonths(4)->toDateTimeString());
    trashApplication($recent, now()->subMonths(2)->toDateTimeString());
    $auditCount = AuditLog::query()->count();

    Artisan::call('mercator:db-cleanup');

    expect(Application::withTrashed()->find($old->id))->toBeNull();
    expect(Application::withTrashed()->find($recent->id))->not->toBeNull();
    expect(Application::find($live->id))->not->toBeNull();

    expect(DB::table('application_entity')->where('application_id', $old->id)->exists())->toBeFalse();
    expect(Entity::find($entity->id))->not->toBeNull();
    expect(ApplicationEvent::find($event->id))->toBeNull();
    expect(Annuaire::find($annuaire->id))->not->toBeNull()
        ->and(Annuaire::find($annuaire->id)->application_id)->toBeNull();

    // La purge ne génère pas d'entrées d'audit.
    expect(AuditLog::query()->count())->toBe($auditCount);

    $lastRun = json_decode(Parameter::getValue(DatabaseCleanup::LAST_RUN_PARAMETER), true);
    expect($lastRun['deleted_objects'])->toBe(1);
});

it('never purges deleted users', function () {
    enableCleanup(false, true);
    $user = User::factory()->create();
    DB::table('users')->where('id', $user->id)->update(['deleted_at' => now()->subYears(3)]);

    Artisan::call('mercator:db-cleanup');

    expect(User::withTrashed()->find($user->id))->not->toBeNull();
});

it('deletes nothing in dry-run mode', function () {
    enableCleanup();
    $old = auditLogAt(now()->subYears(3)->toDateTimeString());
    $application = Application::factory()->create();
    trashApplication($application, now()->subYears(3)->toDateTimeString());

    Artisan::call('mercator:db-cleanup', ['--dry-run' => true]);

    expect(DB::table('audit_logs')->where('id', $old)->exists())->toBeTrue();
    expect(Application::withTrashed()->find($application->id))->not->toBeNull();
    expect(Parameter::getValue(DatabaseCleanup::LAST_RUN_PARAMETER))->toBeNull();
});

it('skips objects still referenced by a mandatory foreign key', function () {
    enableCleanup(false, true);

    $source = Entity::factory()->create();
    $destination = Entity::factory()->create();
    $relation = Relation::factory()->create([
        'source_id' => $source->id,
        'destination_id' => $destination->id,
    ]);
    DB::table('entities')->where('id', $source->id)->update(['deleted_at' => now()->subYears(1)]);

    expect(Artisan::call('mercator:db-cleanup'))->toBe(0);

    expect(Entity::withTrashed()->find($source->id))->not->toBeNull();
    expect(Relation::find($relation->id))->not->toBeNull();

    $lastRun = json_decode(Parameter::getValue(DatabaseCleanup::LAST_RUN_PARAMETER), true);
    expect($lastRun['skipped_objects'])->toBe(1);
});
