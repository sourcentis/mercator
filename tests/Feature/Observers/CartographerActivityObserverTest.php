<?php

use App\Events\CartographerModifiedObject;
use App\Listeners\NotifyCartographerModification;
use App\Models\Application;
use App\Models\Cartographer;
use App\Models\Role;
use App\Models\User;
use App\Services\MailerService;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
    ]);

    Config::set('mercator.cartography.notifier_enabled', true);
    Config::set('mercator.cartography.notifier_from', 'mercator@test.local');
    Config::set('mercator.cartography.notifier_to', 'admin@test.local');
    Config::set('mercator.cartography.notifier_subject', '[Test] :user modified :object #:id');
    Config::set('mercator.cartography.notifier_body', ':user (:email) modified :object « :name » id=:id fields=:fields date=:date');
});

// ─── Level 1: Observer — does it dispatch the event? ────────────────────────

it('dispatches CartographerModifiedObject when a cartographer updates their object', function (): void {
    Event::fake([CartographerModifiedObject::class]);

    $user = User::factory()->create();
    $app = Application::factory()->create();

    Cartographer::create([
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $app->id,
        'user_id' => $user->id,
    ]);

    session([
        'cartographer_permissions' => [Application::class => [$app->id]],
        'cartographer_permissions_at' => now()->timestamp,
    ]);

    $this->actingAs($user);

    $app->update(['name' => 'Modified Name']);

    Event::assertDispatched(CartographerModifiedObject::class, function ($event) use ($user, $app) {
        return $event->user->id === $user->id
            && $event->object->id === $app->id
            && $event->objectType === 'Application';
    });
});

it('does not dispatch the event when notifier_enabled is false', function (): void {
    Config::set('mercator.cartography.notifier_enabled', false);
    Event::fake([CartographerModifiedObject::class]);

    $user = User::factory()->create();
    $app = Application::factory()->create();

    Cartographer::create([
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $app->id,
        'user_id' => $user->id,
    ]);

    session([
        'cartographer_permissions' => [Application::class => [$app->id]],
        'cartographer_permissions_at' => now()->timestamp,
    ]);

    $this->actingAs($user);

    $app->update(['name' => 'Modified Name']);

    Event::assertNotDispatched(CartographerModifiedObject::class);
});

it('does not dispatch when the user is not a cartographer of the object', function (): void {
    Event::fake([CartographerModifiedObject::class]);

    $user = User::factory()->create();
    $app = Application::factory()->create();

    // No Cartographer entry for this user+object
    session([
        'cartographer_permissions' => [],
        'cartographer_permissions_at' => now()->timestamp,
    ]);

    $this->actingAs($user);

    $app->update(['name' => 'Modified']);

    Event::assertNotDispatched(CartographerModifiedObject::class);
});

it('does not dispatch when an admin (non-cartographer) modifies an object', function (): void {
    Event::fake([CartographerModifiedObject::class]);

    // Create an admin user (role id=1 is the admin role)
    $adminRole = Role::find(1);
    $admin = User::factory()->create();
    $admin->roles()->attach($adminRole);

    $app = Application::factory()->create();

    $this->actingAs($admin);

    $app->update(['name' => 'Admin edit']);

    Event::assertNotDispatched(CartographerModifiedObject::class);
});

it('captures the dirty fields in the dispatched event', function (): void {
    Event::fake([CartographerModifiedObject::class]);

    $user = User::factory()->create();
    $app = Application::factory()->create(['name' => 'Original']);

    Cartographer::create([
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $app->id,
        'user_id' => $user->id,
    ]);

    session([
        'cartographer_permissions' => [Application::class => [$app->id]],
        'cartographer_permissions_at' => now()->timestamp,
    ]);

    $this->actingAs($user);

    $app->update(['name' => 'Changed']);

    Event::assertDispatched(CartographerModifiedObject::class, function ($event) {
        return array_key_exists('name', $event->dirty);
    });
});

// ─── Level 2: Listener — does it send the mail correctly? ───────────────────

it('does not send mail when notifier_enabled is false', function (): void {
    Config::set('mercator.cartography.notifier_enabled', false);

    $mailer = Mockery::mock(MailerService::class);
    $mailer->shouldNotReceive('send');
    $this->app->instance(MailerService::class, $mailer);

    $user = User::factory()->create(['name' => 'Alice', 'email' => 'alice@test.com']);
    $app = Application::factory()->create(['name' => 'MyApp']);
    $event = new CartographerModifiedObject($user, $app, ['name' => 'Changed'], 'Application');

    app(NotifyCartographerModification::class)->handle($event);
});

it('sends mail when notifier_enabled is true', function (): void {
    $mailer = Mockery::mock(MailerService::class);
    $mailer->shouldReceive('send')->once();
    $this->app->instance(MailerService::class, $mailer);

    $user = User::factory()->create(['name' => 'Alice', 'email' => 'alice@test.com']);
    $app = Application::factory()->create(['name' => 'MyApp']);
    $event = new CartographerModifiedObject($user, $app, ['name' => 'Changed'], 'Application');

    app(NotifyCartographerModification::class)->handle($event);
});

it('passes correct from, to, subject and body to the mailer', function (): void {
    Config::set('mercator.cartography.notifier_from', 'from@test.com');
    Config::set('mercator.cartography.notifier_to', 'to@test.com');
    Config::set('mercator.cartography.notifier_subject', 'Subj :user');
    Config::set('mercator.cartography.notifier_body', 'Body :user :email :object :id :name :fields :date :object_url :object_history_url');

    $captured = [];

    $mailer = Mockery::mock(MailerService::class);
    $mailer->shouldReceive('send')
        ->once()
        ->withArgs(function (string $from, string $to, string $subject, string $body) use (&$captured) {
            $captured = compact('from', 'to', 'subject', 'body');

            return true;
        });
    $this->app->instance(MailerService::class, $mailer);

    $user = User::factory()->create(['name' => 'Alice Dupont', 'email' => 'alice@example.com']);
    $app = Application::factory()->create(['name' => 'TestApp']);
    $event = new CartographerModifiedObject($user, $app, ['name' => 'New', 'description' => 'Desc'], 'Application');

    app(NotifyCartographerModification::class)->handle($event);

    expect($captured['from'])->toBe('from@test.com');
    expect($captured['to'])->toBe('to@test.com');
    expect($captured['subject'])->toBe('Subj Alice Dupont');
    expect($captured['body'])->toContain('Alice Dupont');
    expect($captured['body'])->toContain('alice@example.com');
    expect($captured['body'])->toContain('Application');
    expect($captured['body'])->toContain((string) $app->id);
    expect($captured['body'])->toContain('TestApp');
    expect($captured['body'])->toContain('name');
    expect($captured['body'])->toContain('description');
    expect($captured['body'])->toContain(now()->format('d/m/Y'));
    // URL placeholders
    expect($captured['body'])->toContain('/admin/applications/'.$app->id);
    expect($captured['body'])->toContain('audit-logs/history');
});

it('inserts object show URL and history URL as placeholders', function (): void {
    Config::set('mercator.cartography.notifier_body', 'Objet: :object_url — Historique: :object_history_url');

    $captured = [];

    $mailer = Mockery::mock(MailerService::class);
    $mailer->shouldReceive('send')
        ->once()
        ->withArgs(function (string $from, string $to, string $subject, string $body) use (&$captured) {
            $captured['body'] = $body;

            return true;
        });
    $this->app->instance(MailerService::class, $mailer);

    $user = User::factory()->create();
    $app = Application::factory()->create(['name' => 'MyApp']);
    $event = new CartographerModifiedObject($user, $app, [], 'Application');

    app(NotifyCartographerModification::class)->handle($event);

    expect($captured['body'])->toContain('/admin/applications/'.$app->id);
    expect($captured['body'])->toContain('audit-logs/history');
    // Class name is URL-encoded in the history route path segment
    expect($captured['body'])->toContain(urlencode(Application::class));
});

it('substitutes :name with #id when the object has no name attribute', function (): void {
    Config::set('mercator.cartography.notifier_body', 'object: :name');

    $captured = [];

    $mailer = Mockery::mock(MailerService::class);
    $mailer->shouldReceive('send')
        ->once()
        ->withArgs(function (string $from, string $to, string $subject, string $body) use (&$captured) {
            $captured['body'] = $body;

            return true;
        });
    $this->app->instance(MailerService::class, $mailer);

    $user = User::factory()->create();
    $app = Application::factory()->create();
    $app->offsetUnset('name'); // remove name from attributes to simulate no-name

    // Rebuild a model-like object without 'name'
    $anonymousApp = Application::find($app->id);
    $anonymousApp->setAttribute('name', null);

    $event = new CartographerModifiedObject($user, $anonymousApp, [], 'Application');

    app(NotifyCartographerModification::class)->handle($event);

    expect($captured['body'])->toContain('#'.$anonymousApp->id);
});

it('sends to each address in a CSV notifier_to', function (): void {
    Config::set('mercator.cartography.notifier_to', 'a@test.com,b@test.com');

    $capturedTo = null;

    $mailer = Mockery::mock(MailerService::class);
    $mailer->shouldReceive('send')
        ->once()
        ->withArgs(function (string $from, string $to) use (&$capturedTo) {
            $capturedTo = $to;

            return true;
        });
    $this->app->instance(MailerService::class, $mailer);

    $user = User::factory()->create();
    $app = Application::factory()->create();
    $event = new CartographerModifiedObject($user, $app, [], 'Application');

    app(NotifyCartographerModification::class)->handle($event);

    // MailerService receives the raw CSV; it handles splitting internally
    expect($capturedTo)->toBe('a@test.com,b@test.com');
});
