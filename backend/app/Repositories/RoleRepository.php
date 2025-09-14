<?php

namespace App\Repositories;

use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class RoleRepository implements RoleRepositoryInterface
{
    protected Role $model;

    public function __construct(Role $model)
    {
        $this->model = $model;
    }

    /**
     * Get all roles with optional filters.
     */
    public function getAll(array $filters = [], array $with = [], array $withCount = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        if (!empty($withCount)) {
            $query->withCount($withCount);
        }

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Get paginated roles with optional filters.
     */
    public function getPaginated(array $filters = [], array $with = [], int $perPage = 15, array $withCount = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        if (!empty($withCount)) {
            $query->withCount($withCount);
        }

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Find role by ID.
     */
    public function findById(int $id, array $with = []): ?Role
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Find role by name.
     */
    public function findByName(string $name, array $with = []): ?Role
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('name', $name)->first();
    }

    /**
     * Create a new role.
     */
    public function create(array $data): Role
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing role.
     */
    public function update(Role $role, array $data): Role
    {
        $role->update($data);
        return $role->fresh();
    }

    /**
     * Delete a role.
     */
    public function delete(Role $role): bool
    {
        return $role->delete();
    }

    /**
     * Assign permissions to role.
     */
    public function assignPermissions(Role $role, array $permissions): Role
    {
        $role->givePermissionTo($permissions);
        return $role->fresh(['permissions']);
    }

    /**
     * Sync role permissions.
     */
    public function syncPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);
        return $role->fresh(['permissions']);
    }

    /**
     * Revoke permissions from role.
     */
    public function revokePermissions(Role $role, array $permissions): Role
    {
        $permissionModels = Permission::whereIn('id', $permissions)->get();
        $role->revokePermissionTo($permissionModels);
        return $role->fresh(['permissions']);
    }

    /**
     * Get roles with user count.
     */
    public function getRolesWithUserCount(): Collection
    {
        return $this->model->withCount('users')->get();
    }

    /**
     * Get roles by guard name.
     */
    public function getRolesByGuard(string $guardName, array $with = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('guard_name', $guardName)->get();
    }

    /**
     * Check if role has users assigned.
     */
    public function hasUsers(Role $role): bool
    {
        return $role->users()->exists();
    }

    /**
     * Get all permissions.
     */
    public function getAllPermissions(): Collection
    {
        return Permission::all();
    }

    /**
     * Get permissions by guard name.
     */
    public function getPermissionsByGuard(string $guardName): Collection
    {
        return Permission::where('guard_name', $guardName)->get();
    }

    /**
     * Check if role name exists.
     */
    public function roleNameExists(string $name, ?int $excludeId = null): bool
    {
        $query = $this->model->where('name', $name);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        // Filter by guard name
        if (isset($filters['guard_name']) && $filters['guard_name'] !== null && $filters['guard_name'] !== '') {
            $query->where('guard_name', $filters['guard_name']);
        }

        // Search filter
        if (isset($filters['search']) && $filters['search'] !== null && $filters['search'] !== '') {
            $search = trim($filters['search']);
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by permissions count
        if (isset($filters['has_permissions']) && $filters['has_permissions'] !== null) {
            if ($filters['has_permissions']) {
                $query->has('permissions');
            } else {
                $query->doesntHave('permissions');
            }
        }

        // Filter by users count
        if (isset($filters['has_users']) && $filters['has_users'] !== null) {
            if ($filters['has_users']) {
                $query->has('users');
            } else {
                $query->doesntHave('users');
            }
        }

        // Date range filters
        if (isset($filters['created_from']) && $filters['created_from'] !== null) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (isset($filters['created_to']) && $filters['created_to'] !== null) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $allowedSortColumns = ['id', 'name', 'guard_name', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}