<?php

namespace App\Repositories\Contracts;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface WarehouseRepositoryInterface
{
    /**
     * Get all Warehouses.
     */
    public function getAll(array $with = []): Collection;

    /**
     * Get paginated Warehouses.
     */
    public function getPaginated(array $filters = [], array $with = [], array $withCount = [], int $perPage = 15, string $sortBy = 'name', string $sortOrder = 'asc'): LengthAwarePaginator;

    /**
     * Find Warehouse by ID.
     */
    public function findById(int $id, array $with = []): ?Warehouse;

    /**
     * Find Warehouse by name.
     */
    public function findByName(string $name, array $with = []): ?Warehouse;

    /**
     * Find Warehouse by code.
     */
    public function findByCode(string $code, array $with = []): ?Warehouse;

    /**
     * Create a new Warehouse.
     */
    public function create(array $data): Warehouse;

    /**
     * Update an existing Warehouse.
     */
    public function update(Warehouse $warehouse, array $data): Warehouse;

    /**
     * Delete a Warehouse.
     */
    public function delete(Warehouse $warehouse): bool;

    /**
     * Get Warehouses by status.
     */
    public function getByStatus(string $status, array $with = []): Collection;

    /**
     * Get active Warehouses.
     */
    public function getActiveWarehouses(array $with = []): Collection;

    /**
     * Search Warehouses.
     */
    public function search(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if Warehouse name exists.
     */
    public function nameExists(string $name, ?int $excludeId = null): bool;

    /**
     * Check if Warehouse code exists.
     */
    public function codeExists(string $code, ?int $excludeId = null): bool;
    
    /**
     * Get Warehouse statistics.
     */
    public function getStatistics(): array;

    /**
     * Check if Warehouse has related records.
     */
    public function hasRelatedRecords(int $warehouseId): bool;
}