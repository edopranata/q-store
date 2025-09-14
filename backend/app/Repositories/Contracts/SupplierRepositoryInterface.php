<?php

namespace App\Repositories\Contracts;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SupplierRepositoryInterface
{
    /**
     * Get all Suppliers.
     */
    public function getAll(array $with = []): Collection;

    /**
     * Get paginated Suppliers.
     */
    public function getPaginated(array $filters = [], array $with = [], array $withCount = [], int $perPage = 15, string $sortBy = 'name', string $sortOrder = 'asc'): LengthAwarePaginator;

    /**
     * Find Supplier by ID.
     */
    public function findById(int $id, array $with = []): ?Supplier;

    /**
     * Find Supplier by name.
     */
    public function findByName(string $name, array $with = []): ?Supplier;

    /**
     * Create a new Supplier.
     */
    public function create(array $data): Supplier;

    /**
     * Update an existing Supplier.
     */
    public function update(Supplier $supplier, array $data): Supplier;

    /**
     * Delete a Supplier.
     */
    public function delete(Supplier $supplier): bool;

    /**
     * Get Suppliers by status.
     */
    public function getByStatus(string $status, array $with = []): Collection;

    /**
     * Get active Suppliers.
     */
    public function getActiveSuppliers(array $with = []): Collection;

    /**
     * Search Suppliers.
     */
    public function search(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if Supplier name exists.
     */
    public function nameExists(string $name, ?int $excludeId = null): bool;
    
    /**
     * Get Supplier statistics.
     */
    public function getStatistics(): array;

    /**
     * Check if Supplier has related records.
     */
    public function hasRelatedRecords(int $supplierId): bool;
}