<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RefreshRolePermissions
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $lastUpdate = Cache::get('roles_last_update', 0);
            $sessionAt = session('auth_permissions_at', 0);

            if ($sessionAt < $lastUpdate) {
                session($request->user()->sessionPermissionData());
            }
        }

        return $next($request);
    }
}
