<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class AuthGates
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user) {
            $this->defineGates();
        }

        return $next($request);
    }

    /**
     * Définir les Gates basés sur les permissions et rôles
     */
    private function defineGates(): void
    {
        $permissionsArray = Cache::rememberForever('permissions_roles_map', function () {
            $roles = Role::with('permissions')->get();
            $map = [];

            foreach ($roles as $role) {
                foreach ($role->permissions as $permission) {
                    $map[$permission->title][] = $role->id;
                }
            }

            return $map;
        });

        foreach ($permissionsArray as $title => $roleIds) {
            Gate::define($title, function (User $user) use ($roleIds) {
                return $user->roles
                    ->pluck('id')
                    ->intersect($roleIds)
                    ->isNotEmpty();
            });
        }
    }
}
