<?php

namespace App\Repositories\Contracts;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PermissionRepositoryInterface
{
    /**
     * Get all Permissions.
     */
    public function getAll(array $with = []): Collection;

    /**
     * Get paginated Permissions.
     */
    public function getPaginated(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find Permission by ID.
     */
    public function findById(int $id, array $with = []): ?Permission;

    /**
     * Find Permission by name.
     */
    public function findByName(string $name, array $with = []): ?Permission;

    /**
     * Create a new Permission.
     */
    public function create(array $data): Permission;

    /**
     * Update an existing Permission.
     */
    public function update(Permission $permission, array $data): Permission;

    /**
     * Delete a Permission.
     */
    public function delete(Permission $permission): bool;

    /**
     * Get Permissions by status.
     */
    public function getByStatus(string $status, array $with = []): Collection;

    /**
     * Get active Permissions.
     */
    public function getActivePermissions(array $with = []): Collection;

    /**
     * Search Permissions.
     */
    public function search(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if Permission name exists.
     */
    public function nameExists(string $name, ?int $excludeId = null): bool;
    
    /**
     * Get Permission statistics.
     */
    public function getStatistics(): array;

    /**
     * Check if Permission has related records.
     */
    public function hasRelatedRecords(int $permissionId): bool;
}