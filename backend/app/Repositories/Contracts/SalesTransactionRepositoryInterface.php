<?php

namespace App\Repositories\Contracts;

use App\Models\SalesTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SalesTransactionRepositoryInterface
{
    /**
     * Get all SalesTransactions.
     */
    public function getAll(array $with = []): Collection;

    /**
     * Get paginated SalesTransactions.
     */
    public function getPaginated(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find SalesTransaction by ID.
     */
    public function findById(int $id, array $with = []): ?SalesTransaction;

    /**
     * Find SalesTransaction by name.
     */
    public function findByName(string $name, array $with = []): ?SalesTransaction;

    /**
     * Create a new SalesTransaction.
     */
    public function create(array $data): SalesTransaction;

    /**
     * Update an existing SalesTransaction.
     */
    public function update(SalesTransaction $salesTransaction, array $data): SalesTransaction;

    /**
     * Delete a SalesTransaction.
     */
    public function delete(SalesTransaction $salesTransaction): bool;

    /**
     * Get SalesTransactions by status.
     */
    public function getByStatus(string $status, array $with = []): Collection;

    /**
     * Get active SalesTransactions.
     */
    public function getActiveSalesTransactions(array $with = []): Collection;

    /**
     * Search SalesTransactions.
     */
    public function search(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if SalesTransaction name exists.
     */
    public function nameExists(string $name, ?int $excludeId = null): bool;
    
    /**
     * Get SalesTransaction statistics.
     */
    public function getStatistics(): array;

    /**
     * Check if SalesTransaction has related records.
     */
    public function hasRelatedRecords(int $salesTransactionId): bool;
}