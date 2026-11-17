<?php

use App\Support\ModelRegistry;
use App\Traits\Auditable;
use App\Traits\Blameable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('adds the blame columns to every blameable table', function () {
    foreach (ModelRegistry::BLAMEABLE_MODELS as $class) {
        $table = (new $class)->getTable();
        foreach (['created_by', 'updated_by', 'deleted_by'] as $column) {
            expect(Schema::hasColumn($table, $column))->toBeTrue("{$table}.{$column} is missing");
        }
    }
    expect(ModelRegistry::BLAMEABLE_MODELS)->toHaveCount(57);
});

it('keeps BLAMEABLE_MODELS equal to the set of Auditable models, all using Blameable', function () {
    $auditable = [];
    foreach (glob(app_path('Models/*.php')) as $file) {
        $class = 'App\\Models\\'.basename($file, '.php');
        if (class_exists($class) && in_array(Auditable::class, class_uses_recursive($class), true)) {
            $auditable[] = $class;
        }
    }
    sort($auditable);

    $registry = ModelRegistry::BLAMEABLE_MODELS;
    sort($registry);

    expect($registry)->toBe($auditable);

    foreach ($registry as $class) {
        expect(in_array(Blameable::class, class_uses_recursive($class), true))
            ->toBeTrue("{$class} does not use Blameable");
    }
});
