<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->role) {
            return $next($request);
        }

        // Allow access to standard dashboard, profile, and functional file actions
        $allowedRoutes = ['dashboard', 'login', 'logout', 'welcome', 'users.stop-impersonating'];
        $globalFunctionalBases = ['documents', 'web.folders', 'share-links', 'shared', 'drive', 'download'];
        
        $currentRoute = $request->route()->getName();
        $currentBaseRoute = str_contains($currentRoute, '.') ? explode('.', $currentRoute)[0] : $currentRoute;

        if (in_array($currentRoute, $allowedRoutes) || in_array($currentBaseRoute, $globalFunctionalBases)) {
            return $next($request);
        }

        // Get all base route names from allowed menus (e.g., if 'documents.index', base is 'documents')
        $allowedMenuRoutes = $user->role->menus()->pluck('route')->toArray();
        $currentBaseRoute = str_contains($currentRoute, '.') ? explode('.', $currentRoute)[0] : $currentRoute;

        foreach ($allowedMenuRoutes as $route) {
            if ($route === $currentRoute) return $next($request);
            
            $baseMenuRoute = str_contains($route, '.') ? explode('.', $route)[0] : $route;
            if ($baseMenuRoute === $currentBaseRoute) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized access to this menu (' . $currentRoute . ')');
    }
}
