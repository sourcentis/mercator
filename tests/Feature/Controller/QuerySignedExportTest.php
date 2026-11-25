<?php

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\SavedQuery;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);
    $this->user = User::query()->where('login', 'admin@admin.com')->first();
});

function makeSignedQuery(array $overrides = []): SavedQuery
{
    return SavedQuery::create(array_merge([
        'name' => 'Test Query',
        'query' => ['from' => 'applications', 'output' => 'list'],
        'is_public' => false,
        'user_id' => null,
    ], $overrides));
}

it('exports csv via a valid signed url without authentication', function () {
    Application::factory()->create();
    $query = makeSignedQuery();

    $url = URL::temporarySignedRoute(
        'queries.export.signed',
        now()->addDay(),
        ['query' => $query->id],
    );

    $response = $this->get($url);

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->streamedContent())->not->toBeEmpty();
});

it('forbids export when the signature is missing or tampered', function () {
    $query = makeSignedQuery();

    $signedUrl = URL::temporarySignedRoute(
        'queries.export.signed',
        now()->addDay(),
        ['query' => $query->id],
    );

    $unsignedUrl = strtok($signedUrl, '?');
    $this->get($unsignedUrl)->assertForbidden();

    $tamperedUrl = $signedUrl.'0';
    $this->get($tamperedUrl)->assertForbidden();
});

it('forbids export when the signed url has expired', function () {
    $query = makeSignedQuery();

    $url = URL::temporarySignedRoute(
        'queries.export.signed',
        now()->subMinute(),
        ['query' => $query->id],
    );

    $this->get($url)->assertForbidden();
});

it('returns 422 when the saved query output is a graph', function () {
    $query = makeSignedQuery([
        'query' => ['from' => 'applications', 'output' => 'graph'],
    ]);

    $url = URL::temporarySignedRoute(
        'queries.export.signed',
        now()->addDay(),
        ['query' => $query->id],
    );

    $this->get($url)
        ->assertUnprocessable()
        ->assertJsonStructure(['message']);
});

it('returns 204 when the saved query has no result', function () {
    $query = makeSignedQuery([
        'query' => ['from' => 'applications', 'output' => 'list', 'filters' => [
            ['field' => 'name', 'operator' => '=', 'value' => '__no_such_application__'],
        ]],
    ]);

    $url = URL::temporarySignedRoute(
        'queries.export.signed',
        now()->addDay(),
        ['query' => $query->id],
    );

    $this->get($url)->assertNoContent();
});

it('logs each signed download with ip and link creator', function () {
    Application::factory()->create();
    $query = makeSignedQuery();

    $url = URL::temporarySignedRoute(
        'queries.export.signed',
        now()->addDay(),
        ['query' => $query->id, 'by' => $this->user->id],
    );

    $this->get($url, ['REMOTE_ADDR' => '203.0.113.7'])->assertOk();

    $log = AuditLog::query()->where('description', 'export_link_used')->sole();
    expect($log->user_id)->toBe($this->user->id)
        ->and($log->host)->toBe('203.0.113.7')
        ->and($log->subject_id)->toBe($query->id)
        ->and($log->properties['status'])->toBe(200);
});

it('does not log a download refused for a bad signature', function () {
    $query = makeSignedQuery();

    $this->get(route('queries.export.signed', ['query' => $query->id]))->assertForbidden();

    expect(AuditLog::query()->where('description', 'export_link_used')->exists())->toBeFalse();
});

it('rejects a link whose creator was tampered with', function () {
    $query = makeSignedQuery();

    $url = URL::temporarySignedRoute(
        'queries.export.signed',
        now()->addDay(),
        ['query' => $query->id, 'by' => $this->user->id],
    );

    $this->get(str_replace('by='.$this->user->id, 'by=999', $url))->assertForbidden();
});
