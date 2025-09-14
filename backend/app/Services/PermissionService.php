<?php

namespace App\Services;

use App\Models\Permission;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class PermissionService
{
    protected PermissionRepositoryInterface $permissionRepository;

    public function __construct(PermissionRepositoryInterface $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Get all Permissions.
     */
    public function getAllPermissions(array $with = []): Collection
    {
        return $this->permissionRepository->getAll($with);
    }

    /**
     * Get paginated Permissions with filters.
     */
    public function getPaginatedPermissions(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->permissionRepository->getPaginated($filters, $with, $perPage);
    }

    /**
     * Get Permission by ID.
     */
    public function getPermissionById(int $id, array $with = []): ?Permission
    {
        return $this->permissionRepository->findById($id, $with);
    }

    /**
     * Get active Permissions for options.
     */
    public function getActivePermissionsForOptions(): Collection
    {
        return $this->permissionRepository->getActivePermissions()->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                // Add other fields as needed
            ];
        });
    }

    /**
     * Create a new Permission.
     */
    public function createPermission(array $data): Permission
    {
        $this->validatePermissionData($data);
        
        return $this->permissionRepository->create($data);
    }

    /**
     * Update an existing Permission.
     */
    public function updatePermission(int $id, array $data): Permission
    {
        $permission = $this->getPermissionById($id);
        
        if (!$permission) {
            throw new \Exception('Permission not found');
        }

        $this->validatePermissionData($data, $id);
        
        return $this->permissionRepository->update($permission, $data);
    }

    /**
     * Delete a Permission.
     */
    public function deletePermission(int $id): bool
    {
        $permission = $this->getPermissionById($id);
        
        if (!$permission) {
            throw new \Exception('Permission not found');
        }

        // Check if Permission is being used
        if ($this->isPermissionInUse($id)) {
            throw new \Exception('Cannot delete Permission that is being used');
        }

        return $this->permissionRepository->delete($permission);
    }

    /**
     * Search Permissions.
     */
    public function searchPermissions(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->permissionRepository->search($query, $with, $perPage);
    }

    /**
     * Get Permission statistics.
     */
    public function getPermissionStatistics(): array
    {
        return $this->permissionRepository->getStatistics();
    }

    /**
     * Check if Permission is in use.
     */
    public function isPermissionInUse(int $permissionId): bool
    {
        // Implement your business logic to check if the Permission is being used
        // Example: return $this->permissionRepository->hasRelatedRecords($permissionId);
        return false;
    }

    /**
     * Validate Permission data.
     */
    protected function validatePermissionData(array $data, ?int $excludeId = null): void
    {
        // Implement your validation logic here
        // Example:
        // if (isset($data['name']) && $this->permissionRepository->nameExists($data['name'], $excludeId)) {
        //     throw ValidationException::withMessages([
        //         'name' => ['The Permission name has already been taken.']
        //     ]);
        // }
    }

    /**
     * Get Permissions by status.
     */
    public function getPermissionsByStatus(string $status, array $with = []): Collection
    {
        return $this->permissionRepository->getByStatus($status, $with);
    }

    /**
     * Get active Permissions.
     */
    public function getActivePermissions(array $with = []): Collection
    {
        return $this->permissionRepository->getActivePermissions($with);
    }
}