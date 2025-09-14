<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\Permission;

class PermissionMiddleware
{
    /**
     * Routes that are excluded from permission checking.
     * These routes only require authentication, not specific permissions.
     *
     * @var array
     */
    protected $excludedRoutes = [
        'api.auth.logout',
        'api.auth.me',
        'api.dashboard.stats',
        'api.dashboard.top-products',
        'api.dashboard.recent-transactions',
        'api.dashboard.low-stock',
        'api.dashboard.sales-chart',
        'api.roles.options',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the current route name
        $routeName = $request->route()->getName();
        
        // Skip permission check if route has no name
        if (!$routeName) {
            return $next($request);
        }
        
        // Skip permission check for excluded routes
        if (in_array($routeName, $this->excludedRoutes)) {
            return $next($request);
        }
        
        // Get authenticated user
        $user = Auth::user();
        
        // Skip permission check if user is not authenticated
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }
        
        // Check if user has permission for this route
        try {
            // Check if permission exists first
            $permission = Permission::where('name', $routeName)->first();
            
            if ($permission && !Gate::forUser($user)->allows($permission->name)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to access this resource',
                    'required_permission' => $routeName
                ], 403);
            }
        } catch (\Exception $e) {
            // If permission checking fails, allow access but log the error
            // If permission checking fails, allow access but log the error
             logger('Permission check failed for route: ' . $routeName . ' - ' . $e->getMessage());
        }
        
        return $next($request);
    }
}
