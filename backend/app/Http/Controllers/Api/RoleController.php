<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Services\RoleService;
use App\Services\BaseResponseService;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Exception;

class RoleController extends Controller
{
    use ApiResponseTrait;

    protected RoleService $roleService;
    protected BaseResponseService $responseService;

    public function __construct(RoleService $roleService, BaseResponseService $responseService)
    {
        $this->roleService = $roleService;
        $this->responseService = $responseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $filters = $request->only(['search', 'guard_name', 'has_permissions', 'has_users', 'created_from', 'created_to', 'sort_by', 'sort_order']);
            $perPage = $request->get('per_page', 15);
            $with = ['permissions'];
            $withCount = ['permissions'];
            $fields = $request->get('fields');

            if ($request->get('paginate', true)) {
                $roles = $this->roleService->getPaginatedRoles($filters, $with, $perPage, $withCount);
                
                // Apply field selection if specified
                if ($fields && is_string($fields)) {
                    $selectedFields = array_map('trim', explode(',', $fields));
                    $roles = $this->selectFields($roles, $selectedFields);
                }
                
                return $this->paginatedResponse($roles, 'Roles retrieved successfully');
            } else {
                $roles = $this->roleService->getAllRoles($filters, $with, $withCount);
                
                // Apply field selection if specified
                if ($fields && is_string($fields)) {
                    $selectedFields = array_map('trim', explode(',', $fields));
                    $roles = $this->selectFields($roles, $selectedFields);
                }
                
                return $this->collectionResponse($roles, 'Roles retrieved successfully');
            }
        }, 'Failed to retrieve roles');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $data = $request->only(['name', 'description', 'guard_name', 'permissions']);
            $role = $this->roleService->createRole($data);

            return $this->createdResponse($role, 'Role created successfully');
        }, 'Failed to create role');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($id) {
            $role = $this->roleService->findRoleById($id, ['permissions']);
            
            if (!$role) {
                return $this->notFoundResponse('Role not found');
            }
            
            return $this->successResponse($role, 'Role retrieved successfully');
        }, 'Failed to retrieve role');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $request->only(['name', 'description', 'guard_name', 'permissions']);
            $role = $this->roleService->updateRole($id, $data);

            return $this->successResponse($role, 'Role updated successfully');
        }, 'Failed to update role');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($id) {
            $this->roleService->deleteRole($id);

            return $this->successResponse(null, 'Role deleted successfully');
        }, 'Failed to delete role');
    }

    /**
     * Get all permissions for role assignment
     */
    public function permissions(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $guardName = $request->get('guard_name', 'web');
            $permissions = $this->roleService->getPermissionsByGuard($guardName);
            
            return $this->collectionResponse($permissions, 'Permissions retrieved successfully');
        }, 'Failed to retrieve permissions');
    }

    /**
     * Assign permissions to role
     */
    public function assignPermissions(Request $request, int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $id) {
            $request->validate([
                'permissions' => 'required|array',
                'permissions.*' => 'integer|exists:permissions,id'
            ]);

            $role = $this->roleService->findRoleById($id);
            if (!$role) {
                return $this->notFoundResponse('Role not found');
            }

            $role = $this->roleService->syncRolePermissions($role, $request->permissions);

            return $this->successResponse($role, 'Permissions assigned successfully');
        }, 'Failed to assign permissions');
    }

    /**
     * Remove permissions from role
     */
    public function revokePermissions(Request $request, int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $id) {
            $request->validate([
                'permissions' => 'required|array',
                'permissions.*' => 'integer|exists:permissions,id'
            ]);

            $role = $this->roleService->findRoleById($id);
            if (!$role) {
                return $this->notFoundResponse('Role not found');
            }

            $role = $this->roleService->revokePermissionsFromRole($role, $request->permissions);

            return $this->successResponse($role, 'Permissions revoked successfully');
        }, 'Failed to revoke permissions');
     }

    /**
     * Get role statistics
     */
    public function statistics(): JsonResponse
    {
        return $this->handleRequest(function () {
            $statistics = $this->roleService->getRoleStatistics();
            
            return $this->successResponse($statistics, 'Role statistics retrieved successfully');
        }, 'Failed to retrieve statistics');
    }

    /**
     * Get roles with user count
     */
    public function rolesWithUserCount(): JsonResponse
    {
        return $this->handleRequest(function () {
            $roles = $this->roleService->getRolesWithUserCount();
            
            return $this->collectionResponse($roles, 'Roles with user count retrieved successfully');
        }, 'Failed to retrieve roles with user count');
    }

    /**
     * Bulk assign permissions to multiple roles
     */
    public function bulkAssignPermissions(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $request->validate([
                'role_ids' => 'required|array',
                'role_ids.*' => 'integer|exists:roles,id',
                'permission_ids' => 'required|array',
                'permission_ids.*' => 'integer|exists:permissions,id'
            ]);

            $results = $this->roleService->bulkAssignPermissions(
                $request->role_ids,
                $request->permission_ids
            );

            return $this->successResponse($results, 'Bulk permission assignment completed');
        }, 'Failed to bulk assign permissions');
    }

    /**
     * Select specific fields from roles data
     * @param mixed $roles
     * @param array $fields
     * @return mixed
     */
    private function selectFields($roles, array $fields)
    {
        if (is_object($roles) && method_exists($roles, 'getCollection')) {
            // Handle paginated results
            $collection = $roles->getCollection();
            $filtered = $collection->map(function ($role) use ($fields) {
                return $this->filterRoleFields($role, $fields);
            });
            $roles->setCollection($filtered);
            return $roles;
        } elseif (is_iterable($roles)) {
            // Handle collection or array
            return collect($roles)->map(function ($role) use ($fields) {
                return $this->filterRoleFields($role, $fields);
            });
        }
        
        return $roles;
    }

    /**
     * Get role options for select components
     */
    public function options(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $roles = $this->roleService->getRoleOptions([
                'status' => $request->get('status', 'active'),
                'fields' => ['id', 'name']
            ]);

            return $this->successResponse($roles, 'Role options retrieved successfully');
        }, 'Failed to retrieve role options');
    }

    /**
     * Filter role fields
     * @param mixed $role
     * @param array $fields
     * @return array
     */
    private function filterRoleFields($role, array $fields)
    {
        $roleArray = is_array($role) ? $role : $role->toArray();
        $filtered = [];
        
        foreach ($fields as $field) {
            if (array_key_exists($field, $roleArray)) {
                $filtered[$field] = $roleArray[$field];
            }
        }
        
        return $filtered;
    }
}