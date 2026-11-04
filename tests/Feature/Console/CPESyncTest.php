<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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
