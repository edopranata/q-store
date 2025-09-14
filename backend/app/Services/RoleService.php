<?php

namespace App\Services;

use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class RoleService
{
    protected RoleRepositoryInterface $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * Get all roles with optional filters.
     */
    public function getAllRoles(array $filters = [], array $with = [], array $withCount = []): Collection
    {
        return $this->roleRepository->getAll($filters, $with, $withCount);
    }

    /**
     * Get paginated roles with optional filters.
     */
    public function getPaginatedRoles(array $filters = [], array $with = [], int $perPage = 15, array $withCount = []): LengthAwarePaginator
    {
        return $this->roleRepository->getPaginated($filters, $with, $perPage, $withCount);
    }

    /**
     * Find role by ID.
     */
    public function findRoleById(int $id, array $with = []): ?Role
    {
        return $this->roleRepository->findById($id, $with);
    }

    /**
     * Create a new role with validation.
     */
    public function createRole(array $data): Role
    {
        $this->validateRoleData($data);

        DB::beginTransaction();
        try {
            $role = $this->roleRepository->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'guard_name' => $data['guard_name'] ?? 'web',
            ]);

            // Assign permissions if provided
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $this->assignPermissionsToRole($role, $data['permissions']);
            }

            DB::commit();
            return $role->fresh(['permissions']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing role with validation.
     */
    public function updateRole(int $id, array $data): Role
    {
        $role = $this->findRoleById($id);
        if (!$role) {
            throw new Exception('Role not found');
        }

        // Check if it's a system role that cannot be modified
        if ($this->isSystemRole($role)) {
            throw new Exception('Cannot modify system role');
        }

        $this->validateRoleData($data, $id);

        DB::beginTransaction();
        try {
            $role = $this->roleRepository->update($role, [
                'name' => $data['name'],
                'description' => $data['description'] ?? $role->description,
                'guard_name' => $data['guard_name'] ?? $role->guard_name,
            ]);

            // Sync permissions if provided
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $this->syncRolePermissions($role, $data['permissions']);
            }

            DB::commit();
            return $role->fresh(['permissions']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a role with validation.
     */
    public function deleteRole(int $id): bool
    {
        $role = $this->findRoleById($id);
        if (!$role) {
            throw new Exception('Role not found');
        }

        // Check if it's a system role that cannot be deleted
        if ($this->isSystemRole($role)) {
            throw new Exception('Cannot delete system role');
        }

        // Check if role has users assigned
        if ($this->roleRepository->hasUsers($role)) {
            throw new Exception('Cannot delete role that has users assigned');
        }

        return $this->roleRepository->delete($role);
    }

    /**
     * Assign permissions to role.
     */
    public function assignPermissionsToRole(Role $role, array $permissionIds): Role
    {
        $this->validatePermissions($permissionIds);
        return $this->roleRepository->assignPermissions($role, $permissionIds);
    }

    /**
     * Sync role permissions.
     */
    public function syncRolePermissions(Role $role, array $permissionIds): Role
    {
        $this->validatePermissions($permissionIds);
        return $this->roleRepository->syncPermissions($role, $permissionIds);
    }

    /**
     * Revoke permissions from role.
     */
    public function revokePermissionsFromRole(Role $role, array $permissionIds): Role
    {
        $this->validatePermissions($permissionIds);
        return $this->roleRepository->revokePermissions($role, $permissionIds);
    }

    /**
     * Get roles with user count.
     */
    public function getRolesWithUserCount(): Collection
    {
        return $this->roleRepository->getRolesWithUserCount();
    }

    /**
     * Get all permissions.
     */
    public function getAllPermissions(): Collection
    {
        return $this->roleRepository->getAllPermissions();
    }

    /**
     * Get permissions by guard name.
     */
    public function getPermissionsByGuard(string $guardName): Collection
    {
        return $this->roleRepository->getPermissionsByGuard($guardName);
    }

    /**
     * Validate role data.
     */
    protected function validateRoleData(array $data, ?int $excludeId = null): void
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_]+$/', // Only alphanumeric, spaces, hyphens, and underscores
            ],
            'guard_name' => 'sometimes|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ];

        $messages = [
            'name.required' => 'Role name is required',
            'name.string' => 'Role name must be a string',
            'name.max' => 'Role name cannot exceed 255 characters',
            'name.regex' => 'Role name can only contain letters, numbers, spaces, hyphens, and underscores',
            'guard_name.string' => 'Guard name must be a string',
            'guard_name.max' => 'Guard name cannot exceed 255 characters',
            'permissions.array' => 'Permissions must be an array',
            'permissions.*.integer' => 'Each permission must be an integer',
            'permissions.*.exists' => 'One or more permissions do not exist',
        ];

        $validator = Validator::make($data, $rules, $messages);

        // Check for unique role name
        if (isset($data['name'])) {
            if ($this->roleRepository->roleNameExists($data['name'], $excludeId)) {
                $validator->errors()->add('name', 'Role name already exists');
            }
        }

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate permissions.
     */
    protected function validatePermissions(array $permissionIds): void
    {
        if (empty($permissionIds)) {
            return;
        }

        $existingPermissions = Permission::whereIn('id', $permissionIds)->count();
        if ($existingPermissions !== count($permissionIds)) {
            throw new Exception('One or more permissions do not exist');
        }
    }

    /**
     * Check if role is a system role that cannot be modified/deleted.
     */
    protected function isSystemRole(Role $role): bool
    {
        $systemRoles = ['super-admin', 'admin', 'user']; // Define your system roles
        return in_array($role->name, $systemRoles);
    }

    /**
     * Get role statistics.
     */
    public function getRoleStatistics(): array
    {
        $totalRoles = $this->roleRepository->getAll()->count();
        $rolesWithUsers = $this->roleRepository->getAll()->filter(function ($role) {
            return $this->roleRepository->hasUsers($role);
        })->count();
        $rolesWithoutUsers = $totalRoles - $rolesWithUsers;
        $totalPermissions = $this->roleRepository->getAllPermissions()->count();

        return [
            'total_roles' => $totalRoles,
            'roles_with_users' => $rolesWithUsers,
            'roles_without_users' => $rolesWithoutUsers,
            'total_permissions' => $totalPermissions,
        ];
    }

    /**
     * Bulk assign permissions to multiple roles.
     */
    public function bulkAssignPermissions(array $roleIds, array $permissionIds): array
    {
        $this->validatePermissions($permissionIds);
        
        $results = [];
        DB::beginTransaction();
        
        try {
            foreach ($roleIds as $roleId) {
                $role = $this->findRoleById($roleId);
                if ($role && !$this->isSystemRole($role)) {
                    $this->assignPermissionsToRole($role, $permissionIds);
                    $results[] = ['role_id' => $roleId, 'status' => 'success'];
                } else {
                    $results[] = ['role_id' => $roleId, 'status' => 'skipped', 'reason' => 'System role or not found'];
                }
            }
            
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
        return $results;
    }

    /**
     * Get role options for select components
     */
    public function getRoleOptions(array $filters = []): array
    {
        $status = $filters['status'] ?? 'active';
        $fields = $filters['fields'] ?? ['id', 'name'];
        
        $roles = $this->roleRepository->getAll([
            'status' => $status
        ]);

        return $roles->map(function ($role) use ($fields) {
            $option = [];
            
            if (in_array('id', $fields)) {
                $option['value'] = $role->id;
            }
            
            if (in_array('name', $fields)) {
                $option['label'] = $role->name;
            }
            
            // Add any additional fields if requested
            foreach ($fields as $field) {
                if (!in_array($field, ['id', 'name']) && isset($role->$field)) {
                    $option[$field] = $role->$field;
                }
            }
            
            return $option;
        })->toArray();
    }
}