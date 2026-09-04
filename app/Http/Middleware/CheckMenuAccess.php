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

        // Support multiple routes separated by pipe (|)
        $checkRoutes = explode('|', $checkRoute);
        $hasAccess = false;
        foreach ($checkRoutes as $cr) {
            if (auth()->user()->hasMenuAccess($cr, $action)) {
                $hasAccess = true;
                break;
            }
        }

        // BYPASS: If user doesn't have access, but the action is strictly 'view' and URL/Referer contains 'from_dashboard'
        $referer = (string) $request->headers->get('referer', '');
        $isFromDashboard = $request->has('from_dashboard') || \Illuminate\Support\Str::contains($referer, 'from_dashboard=1');
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
        $suffixes = [
            '.import.template',
            '.import.store',
            '.import',
            '.create',
            '.store',
            '.show',
            '.edit',
            '.update',
            '.destroy',
            '.approve',
            '.reject'
        ];

        foreach ($suffixes as $suffix) {
            if (\Illuminate\Support\Str::endsWith($routeName, $suffix)) {
                return str_replace($suffix, '.index', $routeName);
            }
        }
        return $routeName;
    }

    private function resolveAction($routeName)
    {
        if (\Illuminate\Support\Str::endsWith($routeName, '.create') || \Illuminate\Support\Str::endsWith($routeName, '.store') || \Illuminate\Support\Str::endsWith($routeName, '.import') || \Illuminate\Support\Str::endsWith($routeName, '.import.template') || \Illuminate\Support\Str::endsWith($routeName, '.import.store')) {
            return 'create';
        }
        if (\Illuminate\Support\Str::endsWith($routeName, '.edit') || \Illuminate\Support\Str::endsWith($routeName, '.update')) {
            return 'update';
        }
        if (\Illuminate\Support\Str::endsWith($routeName, '.destroy')) {
            return 'delete';
        }
        if (\Illuminate\Support\Str::endsWith($routeName, '.approve') || \Illuminate\Support\Str::endsWith($routeName, '.reject')) {
            return 'approve';
        }
        return 'view';
    }
}
