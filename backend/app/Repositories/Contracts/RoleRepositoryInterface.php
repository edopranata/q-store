<?php

namespace App\Repositories\Contracts;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface RoleRepositoryInterface
{
    /**
     * Get all roles with optional filters.
     */
    public function getAll(array $filters = [], array $with = [], array $withCount = []): Collection;

    /**
     * Get paginated roles with optional filters.
     */
    public function getPaginated(array $filters = [], array $with = [], int $perPage = 15, array $withCount = []): LengthAwarePaginator;

    /**
     * Find role by ID.
     */
    public function findById(int $id, array $with = []): ?Role;

    /**
     * Find role by name.
     */
    public function findByName(string $name, array $with = []): ?Role;

    /**
     * Create a new role.
     */
    public function create(array $data): Role;

    /**
     * Update an existing role.
     */
    public function update(Role $role, array $data): Role;

    /**
     * Delete a role.
     */
    public function delete(Role $role): bool;

    /**
     * Assign permissions to role.
     */
    public function assignPermissions(Role $role, array $permissions): Role;

    /**
     * Sync role permissions.
     */
    public function syncPermissions(Role $role, array $permissions): Role;

    /**
     * Revoke permissions from role.
     */
    public function revokePermissions(Role $role, array $permissions): Role;

    /**
     * Get roles with user count.
     */
    public function getRolesWithUserCount(): Collection;

    /**
     * Get roles by guard name.
     */
    public function getRolesByGuard(string $guardName, array $with = []): Collection;

    /**
     * Check if role has users assigned.
     */
    public function hasUsers(Role $role): bool;

    /**
     * Get all permissions.
     */
    public function getAllPermissions(): Collection;

    /**
     * Get permissions by guard name.
     */
    public function getPermissionsByGuard(string $guardName): Collection;

    /**
     * Check if role name exists.
     */
    public function roleNameExists(string $name, ?int $excludeId = null): bool;
}