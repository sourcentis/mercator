<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\AuthenticateApiOrWeb;
use App\Http\Middleware\AuthGates;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\EnsureActivePerimeter;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\ForceXForwardedProto;
use App\Http\Middleware\LicenseWarning;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RefreshCartographerPermissions;
use App\Http\Middleware\RefreshRolePermissions;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrashModeMiddleware;
use App\Http\Middleware\UseCachedAuthUser;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Http\Middleware\CreateFreshApiToken;
use Symfony\Component\HttpFoundation\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Middleware globaux
        $middleware->use([
            HandleCors::class,
            PreventRequestsDuringMaintenance::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
        ]);

        // Middlewares spécifiques au groupe 'web'
        $middleware->web(
            append: [
                ForceXForwardedProto::class,
                UseCachedAuthUser::class,
                SetLocale::class,
                LicenseWarning::class,
                SecurityHeaders::class,
                CreateFreshApiToken::class, // ✅
                RefreshCartographerPermissions::class,
                RefreshRolePermissions::class,
            ],
            replace: [
                // Replace the framework's PreventRequestForgery with VerifyCsrfToken (HttpOnly XSRF)
                PreventRequestForgery::class => VerifyCsrfToken::class,
            ],
        );

        $middleware->api(prepend: [
            ForceJsonResponse::class,   // ✅ force les erreurs en JSON sur l'API
            EncryptCookies::class,      // ✅ déchiffre mercator_session et laravel_token
            AddQueuedCookiesToResponse::class,
            StartSession::class, // ✅ charge la session existante
            'throttle:api',
        ]);

        $middleware->api(append: [
            UseCachedAuthUser::class,
        ]);

        // Alias de middlewares
        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.basic' => AuthenticateWithBasicAuth::class,
            'cache.headers' => SetCacheHeaders::class,
            'can' => Authorize::class,
            'guest' => RedirectIfAuthenticated::class,
            'signed' => ValidateSignature::class,
            'throttle' => ThrottleRequests::class,
            'password.confirm' => RequirePassword::class,
            'verified' => EnsureEmailIsVerified::class,
            'auth.multi' => AuthenticateApiOrWeb::class,
            'gates' => AuthGates::class,  // ✅ Alias pour utilisation manuelle
        ]);

        // Groupes de middlewares personnalisés
        $middleware->appendToGroup('api.protected', [
            'auth.multi',
            'gates',
        ]);

        $middleware->appendToGroup('web.protected', [
            'auth',
            'gates',
            EnsureActivePerimeter::class,
            TrashModeMiddleware::class,
        ]);

        // Le mode corbeille doit autoriser les objets supprimés avant la résolution des liaisons de route.
        $middleware->prependToPriorityList(SubstituteBindings::class, TrashModeMiddleware::class);

        // Configurer les trusted proxies
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                // ValidationException → 422 avec détail des erreurs
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'errors' => $e->errors(),
                        'code' => 422,
                    ], 422);
                }

                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = ($status >= 500 && ! config('app.debug'))
                    ? 'Server Error'
                    : ($e->getMessage() ?: 'Server Error');

                return response()->json([
                    'message' => $message,
                    'code' => $status,
                ], $status);
            }
        });
    })
    ->create();
