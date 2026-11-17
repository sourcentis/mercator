<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

const ZIP_SIGNATURE = 'PK';

/**
 * DATASETS
 * - FILE_REPORTS : routes qui renvoient un fichier + extension attendue
 */
$FILE_REPORTS = [
    // endpoint                               ext
    ['/api/report/cartography',              'docx'],
    ['/api/report/entities',                 'xlsx'],
    ['/api/report/applicationsByBlocks',     'xlsx'],
    ['/api/report/directory',                'docx'],
    ['/api/report/logicalServers',           'xlsx'],
    ['/api/report/securityNeeds',            'xlsx'],
    ['/api/report/logicalServerConfigs',     'xlsx'],
    ['/api/report/externalAccess',           'xlsx'],
    ['/api/report/physicalInventory',        'xlsx'],
    ['/api/report/vlans',                    'xlsx'],
    ['/api/report/workstations',             'xlsx'],

    // GDPR
    ['/api/report/activityList',             'xlsx'],
    ['/api/report/activityReport',           'docx'],

    // CVE
    ['/api/report/cve',                      'xlsx'],

    // BCP : Impacts & RTO
    ['/api/report/impacts',                  'xlsx'],
    ['/api/report/rto',                      'xlsx'],

];
/**
 * TESTS "forbidden" (403) pour TOUTES les routes,
 * avec utilisateur authentifié SANS permissions.
 */
it('forbids report endpoints without permission', function (string $endpoint) {
    $user = User::factory()->create();
    Passport::actingAs($user); // authentifié mais sans droits

    $this->get($endpoint)->assertForbidden();
})->with(
    array_map(fn ($e) => [$e[0]], $FILE_REPORTS)
);

/**
 * TESTS "permitted" pour les endpoints FICHIERS
 */
it('returns a file download for report endpoints when permitted', function (string $endpoint, string $ext) {
    // Récupère (ou crée) un user "admin" et bypass toutes les autorisations
    $user = User::query()->find(1) ?? User::factory()->create(['id' => 1]);
    Passport::actingAs($user);

    // Bypass complet des Gates/Policies pendant ce test
    Gate::before(function ($user, $ability) {
        return true; // autorise tout
    });

    $response = $this->get($endpoint);
    $response->assertOk();
    $response->assertHeader('Content-Disposition');

    $disposition = $response->headers->get('Content-Disposition');
    expect($disposition)->toBeString()
        ->and(strtolower($disposition))->toContain('attachment;')
        ->and(strtolower($disposition))->toContain('filename=')
        ->and(strtolower($disposition))->toContain('.'.strtolower($ext));

    $base = $response->baseResponse;
    expect($base)->toBeInstanceOf(BinaryFileResponse::class);

    $path = $base->getFile()->getPathname();
    expect(is_file($path))->toBeTrue();

    $content = file_get_contents($path);
    expect(strlen($content))->toBeGreaterThan(100);

    // DOCX/XLSX sont des ZIP → début "PK"
    if (in_array(strtolower($ext), ['docx', 'xlsx'], true)) {
        expect(substr($content, 0, 2))->toBe(ZIP_SIGNATURE);
    }
})->with($FILE_REPORTS);
