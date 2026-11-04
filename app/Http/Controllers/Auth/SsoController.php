<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\AbstractUser;
use Laravel\Socialite\Facades\Socialite;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SsoController extends Controller
{
    public function redirectToKeycloak(): RedirectResponse
    {
        // Vérifier si Keycloak est activé
        if (!config('services.keycloak.enabled', false)) {
            return redirect()->route('login')
                ->with('error', 'SSO Keycloak is not enabled.');
        }

        return Socialite::driver('keycloak')->redirect();
    }

    /**
     * Handle the Keycloak OAuth callback and authenticate or provision a local user.
     *
     * Retrieves the Keycloak user from Socialite, finds or (optionally) creates a corresponding local User,
     * synchronizes any matching local Role models from the Keycloak roles without detaching existing roles,
     * logs the local user in, and redirects to the intended page. If authentication with Keycloak fails
     * and fallback is enabled, redirects to local login; otherwise shows error.
     *
     * @return RedirectResponse A redirect response to the intended URL on success, or to the login route with an error/message on failure.
     */
    public function handleKeycloakCallback(): RedirectResponse
    {
        $fallbackLocal = (bool) config('services.keycloak.fallback_local', false);
        $autoProvision = (bool) config('services.keycloak.auto_provision', true);

        try {
            $keycloakUser = Socialite::driver('keycloak')->user();
        } catch (\Exception $e) {
            Log::warning('Keycloak authentication failed', [
                'error' => $e->getMessage(),
            ]);

            // Si fallback activé, rediriger vers login local
            if ($fallbackLocal) {
                return redirect()->route('login')
                    ->with('info', 'SSO authentication failed. Please use local credentials.');
            }

            return redirect()->route('login')
                ->with('error', 'Authentication failed.');
        }

        // Récupérer l'identifiant et l'email
        $login = $keycloakUser->getNickname() ?? $keycloakUser->getEmail();
        $email = $keycloakUser->getEmail();
        $name = $keycloakUser->getName() ?? $login;

        // Récupérer les rôles de Keycloak
        $keycloakRoles = $keycloakUser instanceof AbstractUser
            ? ($keycloakUser->getRaw()['realm_access']['roles'] ?? [])
            : [];

        // Chercher l'utilisateur local par login OU par email
        $existingUser = User::where('login', $login)->first()
            ?? User::where('email', $email)->first();

        if (!$existingUser) {
            if (!$autoProvision) {
                Log::info('Keycloak user not found locally and auto-provision disabled', [
                    'login' => $login,
                ]);

                // Si fallback activé, proposer login local
                if ($fallbackLocal) {
                    return redirect()->route('login')
                        ->with('info', "User \"{$login}\" not found. Please use local credentials.");
                }

                return redirect()->route('login')
                    ->with('error', "User \"{$login}\" is not a valid Mercator user.");
            }

            // Auto-provision de l'utilisateur
            $existingUser = User::create([
                'name' => $name,
                'email' => $email ?? "{$login}@localhost.local",
                'login' => $login ?? $email,
                'password' => Hash::make(Str::random(32)), // mot de passe inutilisable
            ]);

            // Attribuer le rôle par défaut si configuré
            $defaultRoleName = config('services.keycloak.auto_provision_role');
            if ($defaultRoleName) {
                $defaultRole = Role::where('title', $defaultRoleName)->first();
                if ($defaultRole) {
                    $existingUser->roles()->attach($defaultRole->id);
                }
            }

            Log::info('Keycloak user auto-provisioned', [
                'user_id' => $existingUser->id,
                'login' => $login,
            ]);
        }

        // Synchroniser les rôles Keycloak avec les rôles locaux
        $this->syncKeycloakRoles($existingUser, $keycloakRoles);

        // Connecter l'utilisateur
        Auth::login($existingUser);

        // Charger les rôles et permissions (par périmètre) pour la session
        session($existingUser->sessionPermissionData());

        // Audit log
        try {
            AuditLog::create([
                'description' => 'SSO Login (Keycloak)',
                'subject_id' => $existingUser->id,
                'subject_type' => User::class,
                'user_id' => $existingUser->id,
                'properties' => [
                    'user_agent' => request()->userAgent(),
                    'method' => request()->method(),
                    'url' => request()->fullUrl(),
                    'keycloak_roles' => $keycloakRoles,
                ],
                'host' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to create SSO login audit log', ['error' => $e->getMessage()]);
        }

        Log::info('Keycloak login successful', [
            'user_id' => $existingUser->id,
            'login' => $login,
        ]);

        // Rediriger vers la page souhaitée
        return redirect()->intended('/home');
    }

    /**
     * Synchronise les rôles Keycloak avec les rôles locaux.
     */
    protected function syncKeycloakRoles(User $user, array $keycloakRoles): void
    {
        $syncRoles = (bool) config('services.keycloak.sync_roles', false);

        if (!$syncRoles || empty($keycloakRoles)) {
            return;
        }

        $localRoleIds = Role::whereIn('title', $keycloakRoles)
            ->pluck('id')
            ->all();

        if (!empty($localRoleIds)) {
            // Option 1 : Sync sans détacher (préserve les rôles locaux existants)
            $user->roles()->syncWithoutDetaching($localRoleIds);

            // Option 2 : Sync complet (remplace tous les rôles par ceux de Keycloak)
            // Décommenter si vous préférez une synchronisation stricte
            // $user->roles()->sync($localRoleIds);
        }
    }
}