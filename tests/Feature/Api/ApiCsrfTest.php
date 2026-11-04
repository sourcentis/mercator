<?php

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Activity;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

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
    Passport::actingAs($this->user);
});

describe('API Routes CSRF Protection', function () {
    it('should not require CSRF token for API POST requests with Bearer authentication', function () {
        // Simuler une requête API avec Bearer token (pas de CSRF)
        $response = $this->withoutMiddleware(VerifyCsrfToken::class) // Simule l'absence de CSRF
            ->postJson('/api/activities', [
                'name' => 'Test Activity',
            ]);

        // Ne devrait PAS retourner 419 (CSRF token mismatch)
        expect($response->status())->not->toBe(419);

        // Ne devrait PAS retourner du HTML
        expect($response->headers->get('content-type'))->toContain('application/json');

        // Ne devrait PAS contenir "Page Expired"
        expect($response->content())->not->toContain('Page Expired');
    });

    it('should accept API requests without CSRF token when using Bearer authentication', function () {
        // Test réel sans désactiver le middleware
        $response = $this->postJson('/api/activities', [
            'name' => 'Real API Test',
        ]);

        // Devrait soit créer (201) soit échouer pour une autre raison (validation, permissions)
        // mais JAMAIS 419 (CSRF)
        expect($response->status())
            ->not->toBe(419)
            ->and($response->headers->get('content-type'))
            ->toContain('application/json');
    });

    it('should return JSON errors, not HTML, for API authentication failures', function () {
        // Créer un nouvel utilisateur sans permissions
        $guestUser = User::factory()->create();

        // Requête SANS Passport (utilisateur authentifié mais sans permissions)
        $response = $this->actingAs($guestUser)
            ->postJson('/api/activities', [
                'name' => 'No Permission Test',
            ]);

        // Devrait retourner 403 (forbidden), mais en JSON, JAMAIS 419
        expect($response->status())
            ->not->toBe(419)
            ->and($response->headers->get('content-type'))
            ->toContain('application/json')
            ->and($response->content())
            ->not->toContain('<html>')
            ->not->toContain('Page Expired');
    });

    it('should not apply web middleware to API routes', function () {
        $response = $this->postJson('/api/activities', [
            'name' => 'Middleware Check',
        ]);

        // Vérifier que la réponse est bien JSON
        $response->assertHeader('Content-Type', 'application/json');

        // Ne devrait jamais recevoir une page HTML
        expect($response->content())->not->toContain('<!DOCTYPE html>');
    });
});

describe('API Mass Operations CSRF Protection', function () {
    it('should accept mass-store without CSRF token', function () {
        $response = $this->postJson('/api/activities/mass-store', [
            'items' => [
                ['name' => 'Activity 1'],
                ['name' => 'Activity 2'],
            ],
        ]);

        expect($response->status())->not->toBe(419);
        expect($response->headers->get('content-type'))->toContain('application/json');
    });

    it('should accept mass-update without CSRF token', function () {
        $activity = Activity::factory()->create();

        $response = $this->putJson('/api/activities/mass-update', [
            'items' => [
                [
                    'id' => $activity->id,
                    'name' => 'Updated Name',
                ],
            ],
        ]);

        expect($response->status())->not->toBe(419);
        expect($response->headers->get('content-type'))->toContain('application/json');
    });

    it('should accept mass-destroy without CSRF token', function () {
        $activities = Activity::factory()->count(2)->create();

        $response = $this->deleteJson('/api/activities/mass-destroy', [
            'ids' => $activities->pluck('id')->toArray(),
        ]);

        expect($response->status())->not->toBe(419);
        // Le content-type peut être null pour 204 No Content
        if ($response->headers->get('content-type')) {
            expect($response->headers->get('content-type'))->toContain('application/json');
        }
    });
});

describe('API Standard CRUD Operations CSRF Protection', function () {
    it('should handle GET requests without CSRF', function () {
        $response = $this->getJson('/api/activities');

        expect($response->status())->not->toBe(419);
    });

    it('should handle POST requests without CSRF', function () {
        $response = $this->postJson('/api/activities', ['name' => 'Test']);

        expect($response->status())->not->toBe(419);
    });

    it('should handle PUT requests without CSRF', function () {
        $activity = Activity::factory()->create();

        $response = $this->putJson("/api/activities/{$activity->id}", [
            'name' => 'Updated',
        ]);

        expect($response->status())->not->toBe(419);
    });

    it('should handle DELETE requests without CSRF', function () {
        $activity = Activity::factory()->create();

        $response = $this->deleteJson("/api/activities/{$activity->id}");

        expect($response->status())->not->toBe(419);
    });
});

// Test de régression spécifique
it('prevents regression: API routes must never include web middleware group', function () {
    // Ce test échouera si 'web' est ajouté au groupe 'api.protected'
    $response = $this->postJson('/api/activities', [
        'name' => 'Regression Test',
    ]);

    // Assertion critique : jamais de 419
    expect($response->status())
        ->not->toBe(419, 'REGRESSION: API routes are requiring CSRF token! Check app.php middleware configuration.');

    // Assertion critique : toujours JSON
    $contentType = $response->headers->get('content-type');
    expect($contentType)
        ->not->toBeNull('REGRESSION: API routes are returning no content-type! Check app.php middleware configuration.')
        ->and($contentType)
        ->toContain('application/json');
});
