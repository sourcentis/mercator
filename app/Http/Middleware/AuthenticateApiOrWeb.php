<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateApiOrWeb
{
    public function handle($request, Closure $next)
    {
        // DEBUG TEMPORAIRE
        /*
        \Log::debug('AuthenticateApiOrWeb', [
            'session_id'     => $request->hasSession() ? $request->session()->getId() : 'NO SESSION',
            'session_started'=> $request->hasSession() ? $request->session()->isStarted() : false,
            'session_user'   => $request->hasSession() ? $request->session()->get('login_web_' . sha1('Illuminate\Auth\SessionGuard')) : 'N/A',
            'web_check'      => Auth::guard('web')->check(),
            'api_check'      => Auth::guard('api')->check(),
            'has_bearer'     => $request->bearerToken() ? 'yes' : 'no',
            'has_laravel_token' => $request->cookie('laravel_token') ? 'yes' : 'no',
            'cookies'        => array_keys($request->cookies->all()),
        ]);


        try {
            $webUser = Auth::guard('web')->user();
            \Log::debug('Web guard user', [
                'user'       => $webUser?->id,
                'user_error' => $webUser === null ? 'null' : 'ok',
            ]);
        } catch (\Throwable $e) {
            \Log::debug('Web guard exception', [
                'message' => $e->getMessage(),
                'class'   => get_class($e),
            ]);
        }
        */

        // Essayer de charger l'utilisateur depuis la session manuellement
        if ($request->hasSession()) {
            $userId = $request->session()->get('login_web_'.sha1(static::class));

            // Si pas trouvé avec le hash de classe, essayer avec le guard name
            if (! $userId) {
                $userId = $request->session()->get('login_web_'.sha1('Illuminate\Auth\SessionGuard'));
            }

        }

        // Essayer web (session) d'abord
        if (Auth::guard('web')->check()) {
            Auth::shouldUse('web');

            return $next($request);
        }

        // Puis api (Passport token)
        if (Auth::guard('api')->check()) {
            Auth::shouldUse('api');

            return $next($request);
        }

        abort(401, 'Unauthenticated');
    }
}
