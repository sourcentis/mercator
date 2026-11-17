<?php

use App\Models\Application;
use App\Models\LogicalServer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

/**
 * Creates a row without any blame (as before the migration) and returns its id.
 */
function unblamedRow(string $class, bool $deleted = false): int
{
    $model = $class::factory()->create();
    DB::table($model->getTable())->where('id', $model->id)->update([
        'created_by' => null,
        'updated_by' => null,
        'deleted_by' => null,
        'updated_at' => '2025-01-01 00:00:00',
        'deleted_at' => $deleted ? '2025-01-02 00:00:00' : null,
    ]);
    // Only the hand-written audit entries must drive the backfill
    DB::table('audit_logs')->delete();

    return $model->id;
}

function audit(string $type, int $subjectId, ?int $userId, string $description): void
{
    DB::table('audit_logs')->insert([
        'description' => $description,
        'subject_id' => $subjectId,
        'subject_type' => $type,
        'user_id' => $userId,
        'properties' => null,
        'host' => '127.0.0.1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/**
 * The columns already exist in the test database: up() only runs the backfill.
 */
function runBlameMigration(): void
{
    (require database_path('migrations/2026_11_06_000000_add_blame_columns_to_audited_tables.php'))->up();
}

beforeEach(function () {
    $this->userA = User::factory()->create();
    $this->userB = User::factory()->create();
    $this->userC = User::factory()->create();
});

it('fills the blame columns from the audit history without touching updated_at', function () {
    $appId = unblamedRow(Application::class, deleted: true);
    $serverId = unblamedRow(LogicalServer::class);

    audit(Application::class, $appId, $this->userA->id, 'created');
    audit(Application::class, $appId, $this->userB->id, 'updated');
    audit(Application::class, $appId, $this->userC->id, 'updated');
    audit(Application::class, $appId, $this->userB->id, 'deleted');

    audit(LogicalServer::class, $serverId, $this->userB->id, 'created');
    audit(LogicalServer::class, $serverId, $this->userA->id, 'deleted'); // not deleted any more

    runBlameMigration();

    $app = DB::table('applications')->where('id', $appId)->first();
    expect($app->created_by)->toBe($this->userA->id)
        ->and($app->updated_by)->toBe($this->userC->id)
        ->and($app->deleted_by)->toBe($this->userB->id)
        ->and($app->updated_at)->toBe('2025-01-01 00:00:00');

    $server = DB::table('logical_servers')->where('id', $serverId)->first();
    expect($server->created_by)->toBe($this->userB->id)
        ->and($server->updated_by)->toBe($this->userB->id)
        ->and($server->deleted_by)->toBeNull();
});

it('handles the historical MApplication alias', function () {
    $appId = unblamedRow(Application::class);
    audit('App\\Models\\MApplication', $appId, $this->userA->id, 'created');

    runBlameMigration();

    expect(DB::table('applications')->where('id', $appId)->value('created_by'))->toBe($this->userA->id);
});

it('leaves null for purged users and ignores unknown subject types', function () {
    $appId = unblamedRow(Application::class);
    audit(Application::class, $appId, 987654, 'created');
    audit('App\\Models\\Unknown', 1, $this->userA->id, 'created');

    runBlameMigration();

    expect(DB::table('applications')->where('id', $appId)->value('created_by'))->toBeNull();
});

it('is idempotent and never overwrites a filled column', function () {
    $appId = unblamedRow(Application::class);
    DB::table('applications')->where('id', $appId)->update(['created_by' => $this->userC->id]);
    audit(Application::class, $appId, $this->userA->id, 'created');

    runBlameMigration();

    $row = DB::table('applications')->where('id', $appId)->first();
    expect($row->created_by)->toBe($this->userC->id)
        ->and($row->updated_by)->toBe($this->userA->id);

    audit(Application::class, $appId, $this->userB->id, 'updated');
    runBlameMigration();

    // updated_by is already filled: the second run does not change it
    expect(DB::table('applications')->where('id', $appId)->value('updated_by'))->toBe($this->userA->id);
});
