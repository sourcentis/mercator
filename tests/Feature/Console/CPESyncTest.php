<?php

use App\Console\Commands\CPESync;
use App\Models\Parameter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

it('test mercator:cpe-sync --now --max=10', function () {
    // Force un last_run très ancien pour garantir la présence des filtres

    $exit = Artisan::call('mercator:cpe-sync', [
        '--now' => true,
        '--full' => true,
        '--max' => 100,
    ]);

    expect($exit)->toBe(0);

    expect(DB::table('cpe_vendors')->count())->toBeGreaterThan(0);
    expect(DB::table('cpe_products')->count())->toBeGreaterThan(0);
    expect(DB::table('cpe_versions')->count())->toBeGreaterThan(0);
});

function nvdPage(int $total, array $names): array
{
    return [
        'totalResults' => $total,
        'products' => array_map(fn ($n) => ['cpe' => ['cpeName' => $n]], $names),
    ];
}

it('retries transient NVD errors and throttles requests', function () {
    Sleep::fake();
    config(['services.nvd.api_key' => null, 'services.nvd.api_url' => 'https://nvd.test/cpes']);

    Http::fakeSequence('nvd.test/*')
        ->push(nvdPage(2, ['cpe:2.3:a:acme:widget:1.0:*:*:*:*:*:*:*']))
        ->push('<h1>503 Service Unavailable</h1>', 503)
        ->push(nvdPage(2, ['cpe:2.3:a:acme:widget:2.0:*:*:*:*:*:*:*']));

    $exit = Artisan::call('mercator:cpe-sync', ['--now' => true, '--full' => true, '--per-page' => 1]);

    expect($exit)->toBe(0);
    expect(DB::table('cpe_versions')->pluck('name')->sort()->values()->all())->toBe(['1.0', '2.0']);
    expect(Parameter::getValue(CPESync::PARAM_RESUME))->toBeNull();
    expect(Parameter::getValue(CPESync::PARAM_LAST_RUN))->not->toBeNull();
    // 6 s throttle between pages (no API key) + one backoff delay for the 503
    Sleep::assertSlept(fn ($d) => (int) $d->totalSeconds === 6, 1);
    Sleep::assertSlept(fn ($d) => (int) $d->totalSeconds === 10, 1);
});

it('saves a checkpoint when NVD stays unavailable and resumes from it', function () {
    Sleep::fake();
    config(['services.nvd.api_key' => 'key', 'services.nvd.api_url' => 'https://nvd.test/cpes']);

    $sequence = Http::fakeSequence('nvd.test/*')
        ->push(nvdPage(2, ['cpe:2.3:a:acme:widget:1.0:*:*:*:*:*:*:*']));
    for ($i = 0; $i < 7; $i++) {
        $sequence->push('unavailable', 503);
    }

    $exit = Artisan::call('mercator:cpe-sync', ['--now' => true, '--full' => true, '--per-page' => 1]);

    expect($exit)->toBe(1);
    $checkpoint = json_decode(Parameter::getValue(CPESync::PARAM_RESUME), true);
    expect($checkpoint['startIndex'])->toBe(1);
    expect($checkpoint['full'])->toBeTrue();
    expect(Parameter::getValue(CPESync::PARAM_LAST_RUN))->toBeNull();

    // Second run resumes at index 1 instead of restarting from 0
    $sequence->push(nvdPage(2, ['cpe:2.3:a:acme:widget:2.0:*:*:*:*:*:*:*']));

    $exit = Artisan::call('mercator:cpe-sync', ['--now' => true, '--per-page' => 1]);

    expect($exit)->toBe(0);
    Http::assertSentCount(9); // 1 page + 7 failed attempts + 1 resumed page
    $last = Http::recorded()->last()[0];
    expect($last['startIndex'])->toBe(1);
    expect(DB::table('cpe_versions')->count())->toBe(2);
    expect(Parameter::getValue(CPESync::PARAM_RESUME))->toBeNull();
});
