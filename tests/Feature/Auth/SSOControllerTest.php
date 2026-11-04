<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Role;
use App\Models\User;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Créer des rôles de test
    Role::create(['title' => 'User', 'id' => 1]);
    Role::create(['title' => 'Admin', 'id' => 2]);
    Role::create(['title' => 'Editor', 'id' => 3]);
});

// ============================================================================
// Tests Auto-Provisioning
// ============================================================================

it('auto-provisions a new user when enabled', function () {
    Config::set('services.keycloak.enabled', true);
    Config::set('services.keycloak.auto_provision', true);
    Config::set('services.keycloak.auto_provision_role', 'User');

    $keycloakUser = mockKeycloakUser([
        'nickname' => 'john.doe',
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'roles' => ['User'],
    ]);

    mockSocialiteKeycloak($keycloakUser);

    $response = $this->get(route('keycloak.callback'));

    expect(User::where('login', 'john.doe')->exists())->toBeTrue();

    $user = User::where('login', 'john.doe')->first();
    expect($user->name)->toBe('John Doe');
    expect($user->email)->toBe('john@example.com');
    expect($user->roles->pluck('title')->toArray())->toContain('User');

    $response->assertRedirect('/home');
});

it('does not auto-provision when disabled', function () {
    Config::set('services.keycloak.enabled', true);
    Config::set('services.keycloak.auto_provision', false);
    Config::set('services.keycloak.fallback_local', false);

    $keycloakUser = mockKeycloakUser([
        'nickname' => 'jane.doe',
        'email' => 'jane@example.com',
        'roles' => [],
    ]);

    mockSocialiteKeycloak($keycloakUser);

    $response = $this->get(route('keycloak.callback'));

    expect(User::where('login', 'jane.doe')->exists())->toBeFalse();

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
});

// ============================================================================
// Tests Fallback Local
// ============================================================================

it('redirects to local login when keycloak fails and fallback enabled', function () {
    Config::set('services.keycloak.enabled', true);
    Config::set('services.keycloak.fallback_local', true);

    mockSocialiteKeycloakException(new \Exception('Keycloak unreachable'));

    $response = $this->get(route('keycloak.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('info', 'SSO authentication failed. Please use local credentials.');
});

it('shows error when keycloak fails and fallback disabled', function () {
    Config::set('services.keycloak.enabled', true);
    Config::set('services.keycloak.fallback_local', false);

    mockSocialiteKeycloakException(new \Exception('Keycloak unreachable'));

    $response = $this->get(route('keycloak.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Authentication failed.');
});

it('redirects to local login when user not found and fallback enabled', function () {
    Config::set('services.keycloak.enabled', true);
    Config::set('services.keycloak.auto_provision', false);
    Config::set('services.keycloak.fallback_local', true);

    $keycloakUser = mockKeycloakUser([
        'nickname' => 'unknown.user',
        'email' => 'unknown@example.com',
        'roles' => [],
    ]);

    mockSocialiteKeycloak($keycloakUser);

    $response = $this->get(route('keycloak.callback'));

    expect(User::where('login', 'unknown.user')->exists())->toBeFalse();

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('info');
});

// ============================================================================
// Tests Synchronisation des Rôles
// ============================================================================

it('syncs keycloak roles with local roles', function () {
    Config::set('services.keycloak.enabled', true);
    Config::set('services.keycloak.auto_provision', true);
    Config::set('services.keycloak.sync_roles', true);

    $keycloakUser = mockKeycloakUser([
        'nickname' => 'admin.user',
        'email' => 'admin@example.com',
        'name' => 'Admin User',
        'roles' => ['Admin', 'Editor'],
    ]);

    mockSocialiteKeycloak($keycloakUser);

    $response = $this->get(route('keycloak.callback'));

    $user = User::where('login', 'admin.user')->first();

    expect($user)->not->toBeNull();

    $userRoles = $user->roles->pluck('title')->toArray();

    expect($userRoles)->toContain('Admin');
    expect($userRoles)->toContain('Editor');

    $response->assertRedirect('/home');
});

it('preserves local roles when syncing keycloak roles', function () {
    // Créer un utilisateur existant avec un rôle local
    $user = User::create([
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'login' => 'existing.user',
        'password' => bcrypt('password'),
    ]);

    $user->roles()->attach(Role::where('title', 'User')->first());

    Config::set('services.keycloak.enabled', true);
    Config::set('services.keycloak.sync_roles', true);

    $keycloakUser = mockKeycloakUser([
        'nickname' => 'existing.user',
        'email' => 'existing@example.com',
        'roles' => ['Admin'], // Nouveau rôle de Keycloak
    ]);

    mockSocialiteKeycloak($keycloakUser);

    $response = $this->get(route('keycloak.callback'));

    $user->refresh();
    $userRoles = $user->roles->pluck('title')->toArray();

    // L'utilisateur devrait avoir à la fois User (local) et Admin (Keycloak)
    expect($userRoles)->toContain('User');
    expect($userRoles)->toContain('Admin');

    $response->assertRedirect('/home');
});

it('does not sync roles when disabled', function () {
    Config::set('services.keycloak.enabled', true);
    Config::set('services.keycloak.auto_provision', true);
    Config::set('services.keycloak.sync_roles', false);
    Config::set('services.keycloak.auto_provision_role', 'User');

    $keycloakUser = mockKeycloakUser([
        'nickname' => 'no.sync',
        'email' => 'nosync@example.com',
        'name' => 'No Sync User',
        'roles' => ['Admin', 'Editor'],
    ]);

    mockSocialiteKeycloak($keycloakUser);

    $response = $this->get(route('keycloak.callback'));

    $user = User::where('login', 'no.sync')->first();

    expect($user)->not->toBeNull();

    // Seul le rôle par défaut doit être attribué, pas ceux de Keycloak
    expect($user->roles->count())->toBe(1);
    expect($user->roles->first()->title)->toBe('User');
});

// ============================================================================
// Tests de Recherche Utilisateur
// ============================================================================

it('finds user by login', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'login' => 'test.user',
        'password' => bcrypt('password'),
    ]);

    Config::set('services.keycloak.enabled', true);

    $keycloakUser = mockKeycloakUser([
        'nickname' => 'test.user',
        'email' => 'different@example.com',
        'roles' => [],
    ]);

    mockSocialiteKeycloak($keycloakUser);

    $response = $this->get(route('keycloak.callback'));

    $this->assertAuthenticated();
    expect(Auth::user()->id)->toBe($user->id);
});

it('finds user by email when login differs', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'login' => 'old.login',
        'password' => bcrypt('password'),
    ]);

    Config::set('services.keycloak.enabled', true);

    $keycloakUser = mockKeycloakUser([
        'nickname' => 'new.login',
        'email' => 'test@example.com',
        'roles' => [],
    ]);

    mockSocialiteKeycloak($keycloakUser);

    $response = $this->get(route('keycloak.callback'));

    $this->assertAuthenticated();
    expect(Auth::user()->id)->toBe($user->id);
});

// ============================================================================
// Tests Audit Logs
// ============================================================================

it('creates audit log on successful sso login', function () {
    Config::set('services.keycloak.enabled', true);
    Config::set('services.keycloak.auto_provision', true);

    $keycloakUser = mockKeycloakUser([
        'nickname' => 'audit.test',
        'email' => 'audit@example.com',
        'roles' => ['User'],
    ]);

    mockSocialiteKeycloak($keycloakUser);

    $this->get(route('keycloak.callback'));

    $user = User::where('login', 'audit.test')->first();

    expect($user)->not->toBeNull();

    $this->assertDatabaseHas('audit_logs', [
        'description' => 'SSO Login (Keycloak)',
        'user_id' => $user->id,
        'subject_type' => User::class,
        'subject_id' => $user->id,
    ]);
});

// ============================================================================
// Helpers
// ============================================================================

/**
 * Crée un mock de Keycloak user reproduisant Socialite\Two\User :
 * les rôles sont dans les données brutes du userinfo (getRaw()['realm_access']['roles'])
 */
function mockKeycloakUser(array $attributes): object
{
    return new class($attributes) {
        private $attributes;

        public function __construct(array $attributes)
        {
            $this->attributes = $attributes;
        }

        public function getNickname()
        {
            return $this->attributes['nickname'] ?? null;
        }

        public function getEmail()
        {
            return $this->attributes['email'] ?? null;
        }

        public function getName()
        {
            return $this->attributes['name'] ?? $this->attributes['nickname'] ?? null;
        }

        public function getRaw(): array
        {
            return [
                'preferred_username' => $this->attributes['nickname'] ?? null,
                'email' => $this->attributes['email'] ?? null,
                'name' => $this->attributes['name'] ?? null,
                'realm_access' => [
                    'roles' => $this->attributes['roles'] ?? [],
                ],
            ];
        }
    };
}

function mockSocialiteKeycloak(object $keycloakUser): void
{
    Socialite::shouldReceive('driver')
        ->with('keycloak')
        ->andReturnSelf();
    Socialite::shouldReceive('user')
        ->andReturn($keycloakUser);
}

function mockSocialiteKeycloakException(\Exception $exception): void
{
    Socialite::shouldReceive('driver')
        ->with('keycloak')
        ->andReturnSelf();
    Socialite::shouldReceive('user')
        ->andThrow($exception);
}