<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Si c'est une route API, ne jamais rediriger (forcer un 401)
        if ($request->is('api/*')) {
            return null;
        }

        // Pour les routes web, rediriger vers login si ce n'est pas du JSON
        if (! $request->expectsJson()) {
            return route('login', ['locale' => app()->getLocale()]);
        }

        return null;
    }
}
