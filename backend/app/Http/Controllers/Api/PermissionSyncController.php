<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use App\Models\Permission;
use Illuminate\Support\Str;

class PermissionSyncController extends Controller
{
    /**
     * Synchronize all API routes to permissions table
     */
    public function syncPermissions(): JsonResponse
    {
        try {
            $routes = Route::getRoutes();
            $apiRoutes = [];
            $createdPermissions = [];
            $existingPermissions = [];
            
            // Filter routes that have names and contain 'api' in their URI
            foreach ($routes as $route) {
                $routeName = $route->getName();
                $routeUri = $route->uri();
                
                // Only process named routes that contain 'api' in their path
                if ($routeName && Str::contains($routeUri, 'api')) {
                    $apiRoutes[] = [
                        'name' => $routeName,
                        'uri' => $routeUri,
                        'methods' => implode('|', $route->methods())
                    ];
                }
            }
            
            // Create or update permissions based on route names
            foreach ($apiRoutes as $route) {
                $permissionName = $route['name'];
                
                // Check if permission already exists
                $permission = Permission::where('name', $permissionName)->first();
                
                if (!$permission) {
                    // Create new permission
                    $permission = Permission::create([
                        'name' => $permissionName,
                        'guard_name' => 'web'
                    ]);
                    
                    $createdPermissions[] = [
                        'name' => $permissionName,
                        'route_uri' => $route['uri'],
                        'methods' => $route['methods']
                    ];
                } else {
                    $existingPermissions[] = [
                        'name' => $permissionName,
                        'route_uri' => $route['uri'],
                        'methods' => $route['methods']
                    ];
                }
            }
            
            // Get all current permissions to identify orphaned ones
            $allPermissions = Permission::all()->pluck('name')->toArray();
            $currentRouteNames = collect($apiRoutes)->pluck('name')->toArray();
            $orphanedPermissions = array_diff($allPermissions, $currentRouteNames);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_api_routes' => count($apiRoutes),
                    'created_permissions' => count($createdPermissions),
                    'existing_permissions' => count($existingPermissions),
                    'orphaned_permissions' => count($orphanedPermissions),
                    'created_permissions_list' => $createdPermissions,
                    'existing_permissions_list' => $existingPermissions,
                    'orphaned_permissions_list' => $orphanedPermissions,
                    'all_api_routes' => $apiRoutes
                ],
                'message' => 'Permissions synchronized successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync permissions: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get all current API routes with their permission status
     */
    public function getApiRoutes(): JsonResponse
    {
        try {
            $routes = Route::getRoutes();
            $apiRoutes = [];
            
            foreach ($routes as $route) {
                $routeName = $route->getName();
                $routeUri = $route->uri();
                
                if ($routeName && Str::contains($routeUri, 'api')) {
                    $permission = Permission::where('name', $routeName)->first();
                    
                    $apiRoutes[] = [
                        'name' => $routeName,
                        'uri' => $routeUri,
                        'methods' => implode('|', $route->methods()),
                        'has_permission' => $permission ? true : false,
                        'permission_id' => $permission ? $permission->id : null
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $apiRoutes,
                'message' => 'API routes retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get API routes: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clean up orphaned permissions (permissions without corresponding routes)
     */
    public function cleanupOrphanedPermissions(): JsonResponse
    {
        try {
            $routes = Route::getRoutes();
            $currentRouteNames = [];
            
            // Get all current API route names
            foreach ($routes as $route) {
                $routeName = $route->getName();
                $routeUri = $route->uri();
                
                if ($routeName && Str::contains($routeUri, 'api')) {
                    $currentRouteNames[] = $routeName;
                }
            }
            
            // Find orphaned permissions
            $orphanedPermissions = Permission::whereNotIn('name', $currentRouteNames)->get();
            $deletedCount = $orphanedPermissions->count();
            $deletedPermissions = $orphanedPermissions->pluck('name')->toArray();
            
            // Delete orphaned permissions
            Permission::whereNotIn('name', $currentRouteNames)->delete();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'deleted_count' => $deletedCount,
                    'deleted_permissions' => $deletedPermissions
                ],
                'message' => 'Orphaned permissions cleaned up successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup orphaned permissions: ' . $e->getMessage()
            ], 500);
        }
    }
}