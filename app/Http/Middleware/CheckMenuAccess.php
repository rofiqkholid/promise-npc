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
    public function handle(Request $request, Closure $next, $menuRoute = null, $action = null): Response
    {
        $routeName = $request->route()->getName();
        
        // If route has no name or user is not logged in, we let it pass or block depending on auth middleware
        if (!$routeName || !auth()->check()) {
            return $next($request);
        }

        if (empty($menuRoute)) {
            $menuRoute = null;
        }

        // Use explicitly provided menu route or attempt to resolve the base route automatically
        $checkRoute = $menuRoute ?: $this->resolveBaseRoute($routeName);
        
        // Auto resolve action if not explicitly specified
        if (empty($action)) {
            $action = $this->resolveAction($routeName);
        }

        // We check if the user has access to this route with the specific action
        $hasAccess = auth()->user()->hasMenuAccess($checkRoute, $action);

        // BYPASS: If user doesn't have access, but the action is strictly 'view' and URL/Referer contains 'from_dashboard'
        $isFromDashboard = $request->has('from_dashboard') || str_contains($request->headers->get('referer', ''), 'from_dashboard=1');
        if (!$hasAccess && $action === 'view' && $isFromDashboard) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'You do not have permission to perform this action.'], 403);
            }
            abort(403, 'You do not have permission to access this page or perform this action.');
        }

        return $next($request);
    }
    
    private function resolveBaseRoute($routeName)
    {
        $suffixes = ['.create', '.store', '.show', '.edit', '.update', '.destroy', '.import', '.import.template', '.import.store', '.approve', '.reject'];
        foreach ($suffixes as $suffix) {
            if (str_ends_with($routeName, $suffix)) {
                return str_replace($suffix, '.index', $routeName);
            }
        }
        return $routeName;
    }

    private function resolveAction($routeName)
    {
        if (str_ends_with($routeName, '.create') || str_ends_with($routeName, '.store') || str_ends_with($routeName, '.import') || str_ends_with($routeName, '.import.template') || str_ends_with($routeName, '.import.store')) {
            return 'create';
        }
        if (str_ends_with($routeName, '.edit') || str_ends_with($routeName, '.update')) {
            return 'update';
        }
        if (str_ends_with($routeName, '.destroy')) {
            return 'delete';
        }
        if (str_ends_with($routeName, '.approve') || str_ends_with($routeName, '.reject')) {
            return 'approve';
        }
        return 'view';
    }
}
