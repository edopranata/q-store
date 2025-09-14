<?php

namespace App\Repositories\Contracts;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    /**
     * Get all Customers.
     */
    public function getAll(array $with = []): Collection;

    /**
     * Get paginated Customers.
     */
    public function getPaginated(array $filters = [], array $with = [], array $withCount = [], int $perPage = 15, string $sortBy = 'name', string $sortOrder = 'asc'): LengthAwarePaginator;

    /**
     * Find Customer by ID.
     */
    public function findById(int $id, array $with = []): ?Customer;

    /**
     * Find Customer by name.
     */
    public function findByName(string $name, array $with = []): ?Customer;

    /**
     * Create a new Customer.
     */
    public function create(array $data): Customer;

    /**
     * Update an existing Customer.
     */
    public function update(Customer $customer, array $data): Customer;

    /**
     * Delete a Customer.
     */
    public function delete(Customer $customer): bool;

    /**
     * Get Customers by status.
     */
    public function getByStatus(string $status, array $with = []): Collection;

    /**
     * Get active Customers.
     */
    public function getActiveCustomers(array $with = []): Collection;

    /**
     * Search Customers.
     */
    public function search(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if Customer name exists.
     */
    public function nameExists(string $name, ?int $excludeId = null): bool;
    
    /**
     * Get Customer statistics.
     */
    public function getStatistics(): array;

    /**
     * Check if Customer has related records.
     */
    public function hasRelatedRecords(int $customerId): bool;
}