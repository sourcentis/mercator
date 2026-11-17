<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UseCachedAuthUser
{
    /**
     * Cache l'utilisateur authentifié pour éviter les requêtes DB répétées.
     * Ce middleware doit être placé AVANT AuthGates dans la stack.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si l'utilisateur est authentifié, on le charge une fois
        if (Auth::check()) {
            // Force le chargement et le met en cache pour cette requête
            Auth::user();
        }

        return $next($request);
    }
}
