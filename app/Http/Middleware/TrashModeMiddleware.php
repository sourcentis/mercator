<?php

namespace App\Http\Middleware;

use App\Support\TrashMode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applique le mode corbeille aux pages d'administration :
 *  - liste d'un modèle (`admin.*.index`) : seuls les objets supprimés de ce modèle sont listés ;
 *  - fiche d'un objet (`admin.*.show`) : les objets supprimés sont résolus par la liaison de route.
 *
 * Doit s'exécuter avant SubstituteBindings (voir bootstrap/app.php).
 */
class TrashModeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();

        if (! TrashMode::active() || $route === null || ! $request->user()) {
            return $next($request);
        }

        $name = (string) $route->getName();
        $model = TrashMode::modelForController($route->getControllerClass());

        if ($model !== null && str_ends_with($name, '.show')) {
            $route->withTrashed();
        }

        if ($model === null || ! str_ends_with($name, '.index')) {
            return $next($request);
        }

        View::share('trashListModel', $model);
        $restore = TrashMode::scopeToTrashed($model);

        try {
            return $next($request);
        } finally {
            $restore();
        }
    }
}
