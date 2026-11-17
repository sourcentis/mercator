<?php

use App\Console\Commands\SanitizeHtml;
use App\Models\Network;
use App\Models\Process;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

const SANITIZE_PAYLOAD = '<p>ok</p><script>alert(1)</script><img src="x" onerror="alert(2)">';

// Données enregistrées sans passer par l'assainissement en entrée
function storeRaw(string $table, int $id, array $values): void
{
    DB::table($table)->where('id', $id)->update($values);
}

test('discovers rich fields from form requests', function () {
    $fields = SanitizeHtml::htmlFieldsByModel();

    expect($fields[Network::class])->toBe(['description'])
        ->and($fields[Process::class])->toContain('description', 'in_out');
});

test('sanitizes stored rich fields and leaves plain fields untouched', function () {
    $network = Network::factory()->create();
    storeRaw('networks', $network->id, ['description' => SANITIZE_PAYLOAD, 'protocol_type' => '<b>IPv4</b>']);

    $this->artisan('mercator:sanitize-html')->assertExitCode(0);

    $row = DB::table('networks')->find($network->id);
    expect($row->description)->not->toContain('<script', 'onerror')
        ->and($row->description)->toContain('<p>ok</p>')
        ->and($row->protocol_type)->toBe('<b>IPv4</b>');
});

test('dry run does not modify data', function () {
    $process = Process::factory()->create();
    storeRaw('processes', $process->id, ['in_out' => SANITIZE_PAYLOAD]);

    $this->artisan('mercator:sanitize-html', ['--dry-run' => true])
        ->expectsOutputToContain('processes : 1 to sanitize')
        ->assertExitCode(0);

    expect(DB::table('processes')->where('id', $process->id)->value('in_out'))->toBe(SANITIZE_PAYLOAD);
});

test('is idempotent', function () {
    $network = Network::factory()->create();
    storeRaw('networks', $network->id, ['description' => SANITIZE_PAYLOAD]);

    $this->artisan('mercator:sanitize-html')->assertExitCode(0);
    $this->artisan('mercator:sanitize-html')
        ->expectsOutputToContain('0 record(s) sanitized.')
        ->assertExitCode(0);
});

test('also sanitizes soft-deleted records', function () {
    $network = Network::factory()->create();
    storeRaw('networks', $network->id, ['description' => SANITIZE_PAYLOAD, 'deleted_at' => now()]);

    $this->artisan('mercator:sanitize-html')->assertExitCode(0);

    expect(DB::table('networks')->where('id', $network->id)->value('description'))->not->toContain('<script');
});
