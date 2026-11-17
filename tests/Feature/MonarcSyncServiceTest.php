<?php

use App\Exceptions\MonarcApiException;
use App\Models\MonarcSyncItem;
use App\Models\Process;
use App\Services\MonarcExportService;
use App\Services\MonarcSyncService;
use App\Support\MonarcSyncState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    config([
        'monarc.url' => 'http://monarc.test',
        'monarc.uid' => 'admin@admin.localhost',
        'monarc.password' => 'admin',
        'monarc.cache_ttl' => 300,
        'monarc.timeout' => 15,
    ]);
    Cache::flush();
});

function monarcSyncFakeAuth(): array
{
    return ['monarc.test/auth' => Http::response(['token' => 'tok', 'uid' => 1, 'language' => 1], 200)];
}

/**
 * POST /api/client-anr (createAnr) returns the new id; any other verb to the
 * same URL (anrExists's GET list) reports the given ids as still existing.
 */
function monarcSyncClientAnrFake(int $createdAnrId = 18, array $existingAnrIds = [18]): Closure
{
    return function ($request) use ($createdAnrId, $existingAnrIds) {
        if ($request->method() === 'POST') {
            return Http::response(['status' => 'ok', 'id' => $createdAnrId], 200);
        }

        return Http::response([
            'count' => count($existingAnrIds),
            'anrs' => array_map(fn (int $id) => ['id' => $id, 'label' => "Analysis {$id}"], $existingAnrIds),
        ], 200);
    };
}

/** Minimal knowledgeBase with a single PROC asset carrying one AMV. */
function monarcSyncKnowledgeBase(): array
{
    return [
        'assets' => [
            ['uuid' => 'asset-proc', 'code' => 'PROC', 'label' => 'Processus', 'description' => '', 'type' => 1, 'status' => 1],
        ],
        'threats' => [['uuid' => 't1', 'label' => 'Threat', 'description' => '', 'theme' => ['id' => 1, 'label' => 'Theme'], 'status' => 1, 'mode' => 0, 'code' => 'T1', 'confidentiality' => 1, 'integrity' => 1, 'availability' => 1, 'trend' => -1, 'comment' => '', 'qualification' => -1]],
        'vulnerabilities' => [['uuid' => 'v1', 'label' => 'Vuln', 'description' => '', 'code' => '1', 'status' => 1]],
        'referentials' => [],
        'informationRisks' => [
            ['uuid' => 'amv1', 'asset' => ['uuid' => 'asset-proc'], 'threat' => ['uuid' => 't1'], 'vulnerability' => ['uuid' => 'v1'], 'measures' => [], 'status' => 1],
        ],
        'rolfTags' => [],
        'operationalRisks' => [],
        'recommendationSets' => [],
    ];
}

function monarcSyncScalesAndMethod(): array
{
    return [
        'monarc_version' => '2.13.3',
        'scales' => [],
        'operationalRiskScales' => [],
        'method' => [],
        'thresholds' => [],
        'soas' => [],
        'soaScaleComments' => [],
    ];
}

test('first sync creates a new ANR (no existing anr_id, a typed label) and imports the full selection', function () {
    Http::fake(monarcSyncFakeAuth() + [
        'monarc.test/api/client-anr' => monarcSyncClientAnrFake(),
        'monarc.test/api/client-anr/18/instances/import' => Http::response(['id' => [1], 'errors' => []], 200),
    ]);

    $process = Process::factory()->create(['name' => 'Urgences']);
    $selection = [
        ['model' => 'Process', 'id' => $process->id, 'name' => $process->name, 'asset_uuid' => 'asset-proc', 'scope' => 2],
    ];

    $result = app(MonarcSyncService::class)->sync(null, 31, 'My analysis', 'My analysis', '', 'fr', monarcSyncKnowledgeBase(), monarcSyncScalesAndMethod(), $selection);

    expect($result['status'])->toBe('synced');
    expect($result['anr_id'])->toBe(18);
    expect($result['anr_label'])->toBe('My analysis');
    expect($result['created'])->toBeTrue();
    expect($result['sent_count'])->toBe(1);

    expect(MonarcSyncState::anrId())->toBe(18);
    expect(MonarcSyncItem::query()->where('anr_id', 18)->count())->toBe(1);
    $item = MonarcSyncItem::query()->where('anr_id', 18)->first();
    expect($item->model)->toBe('Process');
    expect($item->mercator_id)->toBe($process->id);
    expect($item->object_uuid)->toBe(MonarcExportService::objectUuid('Process', $process->id));

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/client-anr')
        && $request->method() === 'POST'
        && $request['model'] === 31
        && $request['label'] === 'My analysis');
});

test('first sync links directly to an existing ANR chosen from the list, without creating one', function () {
    Http::fake(monarcSyncFakeAuth() + [
        'monarc.test/api/client-anr' => monarcSyncClientAnrFake(createdAnrId: 99, existingAnrIds: [18]),
        'monarc.test/api/client-anr/18/instances/import' => Http::response(['id' => [1], 'errors' => []], 200),
    ]);

    $process = Process::factory()->create(['name' => 'Urgences']);
    $selection = [
        ['model' => 'Process', 'id' => $process->id, 'name' => $process->name, 'asset_uuid' => 'asset-proc', 'scope' => 2],
    ];

    $result = app(MonarcSyncService::class)->sync(18, null, 'Analysis 18', 'My analysis', '', 'fr', monarcSyncKnowledgeBase(), monarcSyncScalesAndMethod(), $selection);

    expect($result['status'])->toBe('synced');
    expect($result['anr_id'])->toBe(18);
    expect($result['created'])->toBeFalse();
    expect(MonarcSyncState::anrId())->toBe(18);

    Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/api/client-anr') && $request->method() === 'POST');
});

test('sync throws when neither an existing ANR nor a new label is provided', function () {
    Http::fake(monarcSyncFakeAuth());

    $process = Process::factory()->create(['name' => 'Urgences']);
    $selection = [
        ['model' => 'Process', 'id' => $process->id, 'name' => $process->name, 'asset_uuid' => 'asset-proc', 'scope' => 2],
    ];

    expect(fn () => app(MonarcSyncService::class)->sync(null, null, null, 'My analysis', '', 'fr', monarcSyncKnowledgeBase(), monarcSyncScalesAndMethod(), $selection))
        ->toThrow(MonarcApiException::class);

    expect(MonarcSyncState::anrId())->toBeNull();
});

test('sync throws when linking to an existing ANR that no longer exists', function () {
    Http::fake(monarcSyncFakeAuth() + [
        'monarc.test/api/client-anr' => monarcSyncClientAnrFake(existingAnrIds: []),
    ]);

    $process = Process::factory()->create(['name' => 'Urgences']);
    $selection = [
        ['model' => 'Process', 'id' => $process->id, 'name' => $process->name, 'asset_uuid' => 'asset-proc', 'scope' => 2],
    ];

    expect(fn () => app(MonarcSyncService::class)->sync(18, null, 'Analysis 18', 'My analysis', '', 'fr', monarcSyncKnowledgeBase(), monarcSyncScalesAndMethod(), $selection))
        ->toThrow(MonarcApiException::class);

    expect(MonarcSyncState::anrId())->toBeNull();
});

test('a second sync with no cartography change sends nothing (already up to date)', function () {
    Http::fake(monarcSyncFakeAuth() + [
        'monarc.test/api/client-anr' => monarcSyncClientAnrFake(),
        'monarc.test/api/client-anr/18/instances/import' => Http::response(['id' => [1], 'errors' => []], 200),
    ]);

    $process = Process::factory()->create(['name' => 'Urgences']);
    $selection = [
        ['model' => 'Process', 'id' => $process->id, 'name' => $process->name, 'asset_uuid' => 'asset-proc', 'scope' => 2],
    ];

    $service = app(MonarcSyncService::class);
    $service->sync(null, 31, 'My analysis', 'My analysis', '', 'fr', monarcSyncKnowledgeBase(), monarcSyncScalesAndMethod(), $selection);

    $result = $service->sync(null, null, null, 'My analysis', '', 'fr', monarcSyncKnowledgeBase(), monarcSyncScalesAndMethod(), $selection);

    expect($result['status'])->toBe('up_to_date');
    expect($result['sent_count'])->toBe(0);
    expect(MonarcSyncItem::query()->count())->toBe(1);

    $importCalls = collect(Http::recorded())
        ->filter(fn ($pair) => str_ends_with($pair[0]->url(), '/instances/import'))
        ->count();
    expect($importCalls)->toBe(1); // only the first sync's import, never a second
});

test('a later sync only sends the newly added Mercator object', function () {
    Http::fake(monarcSyncFakeAuth() + [
        'monarc.test/api/client-anr' => monarcSyncClientAnrFake(),
        'monarc.test/api/client-anr/18/instances/import' => Http::response(['id' => [1], 'errors' => []], 200),
    ]);

    $processA = Process::factory()->create(['name' => 'FirstProcess']);
    $processB = Process::factory()->create(['name' => 'SecondProcess']);

    $service = app(MonarcSyncService::class);

    $service->sync(null, 31, 'Analysis', 'Analysis', '', 'fr', monarcSyncKnowledgeBase(), monarcSyncScalesAndMethod(), [
        ['model' => 'Process', 'id' => $processA->id, 'name' => $processA->name, 'asset_uuid' => 'asset-proc', 'scope' => 2],
    ]);

    $result = $service->sync(null, null, null, 'Analysis', '', 'fr', monarcSyncKnowledgeBase(), monarcSyncScalesAndMethod(), [
        ['model' => 'Process', 'id' => $processA->id, 'name' => $processA->name, 'asset_uuid' => 'asset-proc', 'scope' => 2],
        ['model' => 'Process', 'id' => $processB->id, 'name' => $processB->name, 'asset_uuid' => 'asset-proc', 'scope' => 2],
    ]);

    expect($result['status'])->toBe('synced');
    expect($result['sent_count'])->toBe(1);
    expect(MonarcSyncItem::query()->count())->toBe(2);

    $importRequests = collect(Http::recorded())
        ->filter(fn ($pair) => str_ends_with($pair[0]->url(), '/instances/import'))
        ->values();
    expect($importRequests)->toHaveCount(2);

    $secondBody = $importRequests[1][0]->body();
    expect($secondBody)->toContain('SecondProcess');
    expect($secondBody)->not->toContain('FirstProcess');
});

test('sync throws and persists nothing when the import fails', function () {
    Http::fake(monarcSyncFakeAuth() + [
        'monarc.test/api/client-anr' => Http::response(['status' => 'ok', 'id' => 18], 200),
        'monarc.test/api/client-anr/18/instances/import' => Http::response(['errors' => ['boom']], 500),
    ]);

    $process = Process::factory()->create(['name' => 'Urgences']);
    $selection = [
        ['model' => 'Process', 'id' => $process->id, 'name' => $process->name, 'asset_uuid' => 'asset-proc', 'scope' => 2],
    ];

    expect(fn () => app(MonarcSyncService::class)->sync(null, 31, 'Analysis', 'Analysis', '', 'fr', monarcSyncKnowledgeBase(), monarcSyncScalesAndMethod(), $selection))
        ->toThrow(MonarcApiException::class);

    expect(MonarcSyncItem::query()->count())->toBe(0);
    expect(MonarcSyncState::anrId())->toBeNull();
});

test('sync reports anr_missing without recreating when the linked ANR was deleted in Monarc', function () {
    MonarcSyncState::save(['anr_id' => 18, 'anr_label' => 'Analysis', 'model_id' => 31, 'last_synced_at' => null]);

    Http::fake(monarcSyncFakeAuth() + [
        'monarc.test/api/client-anr' => Http::response(['count' => 0, 'anrs' => []], 200),
    ]);

    $process = Process::factory()->create(['name' => 'Urgences']);
    $selection = [
        ['model' => 'Process', 'id' => $process->id, 'name' => $process->name, 'asset_uuid' => 'asset-proc', 'scope' => 2],
    ];

    $result = app(MonarcSyncService::class)->sync(null, null, null, 'Analysis', '', 'fr', monarcSyncKnowledgeBase(), monarcSyncScalesAndMethod(), $selection);

    expect($result['status'])->toBe('anr_missing');
    expect($result['anr_id'])->toBe(18);
    Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/instances/import'));
});

test('reset forgets the local link and every tracked item, without deleting the remote ANR', function () {
    MonarcSyncState::save(['anr_id' => 18, 'anr_label' => 'Analysis', 'model_id' => 31, 'last_synced_at' => now()->toIso8601String()]);
    MonarcSyncItem::query()->create(['anr_id' => 18, 'model' => 'Process', 'mercator_id' => 1, 'object_uuid' => MonarcExportService::objectUuid('Process', 1), 'sent_at' => now()]);

    Http::fake(); // no request should be made at all

    app(MonarcSyncService::class)->reset();

    expect(MonarcSyncState::anrId())->toBeNull();
    expect(MonarcSyncItem::query()->count())->toBe(0);
    Http::assertNothingSent();
});
