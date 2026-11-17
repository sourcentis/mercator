<?php

/**
 * Regression tests for the XSS sink escaping and the HttpOnly XSRF-TOKEN cookie.
 */

use App\Models\Application;
use App\Models\ApplicationEvent;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    $this->admin = User::query()->where('login', 'admin@admin.com')->first();
    $this->actingAs($this->admin);
});

// -----------------------------------------------------------------------------
// 1. XSS sink escaping (event.message / event.user.name)
// -----------------------------------------------------------------------------

describe('XSS remediation: event.message sink escaping', function () {

    it('the show page escapes event.message and event.user.name', function () {
        $app = Application::factory()->create();
        ApplicationEvent::factory()
            ->for($app, 'application')
            ->for($this->admin)
            ->create(['message' => '<img src=x onerror=alert(1)>']);

        $response = $this->get(route('admin.applications.show', $app->id));

        $response->assertOk();
        $response->assertSee('function escapeHtml(value)', false);
        $response->assertSee('escapeHtml(event.message)', false);
        $response->assertSee('escapeHtml(event.user.name)', false);
    });

    it('the edit page escapes event.message and event.user.name', function () {
        $app = Application::factory()->create();

        $response = $this->get(route('admin.applications.edit', $app));

        $response->assertOk();
        $response->assertSee('function escapeHtml(value)', false);
        $response->assertSee('escapeHtml(event.message)', false);
        $response->assertSee('escapeHtml(event.user.name)', false);
    });

    it('no longer injects the raw ${event.message} interpolation into innerHTML', function () {
        $app = Application::factory()->create();

        $response = $this->get(route('admin.applications.show', $app->id));

        // The old code injected ${event.message} directly into innerHTML.
        $response->assertDontSee('${event.message}', false);
    });
});

// -----------------------------------------------------------------------------
// 2. HttpOnly XSRF-TOKEN cookie
// -----------------------------------------------------------------------------

describe('Remediation: HttpOnly XSRF-TOKEN cookie', function () {

    it('sets an HttpOnly XSRF-TOKEN cookie on a public page', function () {
        auth()->logout();

        $response = $this->get('/login');

        $xsrf = collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === 'XSRF-TOKEN');

        expect($xsrf)->not->toBeNull()
            ->and($xsrf->isHttpOnly())->toBeTrue();
    });

    it('sets an HttpOnly XSRF-TOKEN cookie on an authenticated page', function () {
        $response = $this->get(route('admin.applications.index'));

        $xsrf = collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === 'XSRF-TOKEN');

        expect($xsrf)->not->toBeNull()
            ->and($xsrf->isHttpOnly())->toBeTrue();
    });

    it('keeps the CSRF token available via <meta name="csrf-token">', function () {
        $response = $this->get(route('admin.applications.index'));

        $response->assertSee('meta name="csrf-token"', false);
    });
});

// -----------------------------------------------------------------------------
// 3. Non-regression: key pages still reachable
// -----------------------------------------------------------------------------

describe('Non-regression: key pages still reachable', function () {

    it('the login page returns 200', function () {
        auth()->logout();

        $this->get('/login')->assertOk();
    });

    it('the show page of an application returns 200', function () {
        $app = Application::factory()->create();
        ApplicationEvent::factory()
            ->for($app, 'application')
            ->for($this->admin)
            ->create(['message' => 'Legitimate message <b>important</b>']);

        $this->get(route('admin.applications.show', $app->id))
            ->assertOk()
            ->assertViewIs('admin.applications.show');
    });

    it('the edit page of an application returns 200', function () {
        $app = Application::factory()->create();

        $this->get(route('admin.applications.edit', $app))
            ->assertOk()
            ->assertViewIs('admin.applications.edit');
    });
});
