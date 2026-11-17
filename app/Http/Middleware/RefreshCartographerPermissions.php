<?php

namespace App\Http\Middleware;

use App\Models\Cartographer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RefreshCartographerPermissions
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $lastUpdate = Cache::get('cartographers_last_update', 0);
            $sessionAt = session('cartographer_permissions_at', 0);

            if ($sessionAt < $lastUpdate) {
                Cartographer::loadSessionFor($request->user());
            }
        }

        return $next($request);
    }
}
