<?php

namespace App\Repositories\Contracts;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UnitRepositoryInterface
{
    /**
     * Get all units.
     */
    public function getAll(array $with = []): Collection;

    /**
     * Get paginated units.
     */
    public function getPaginated(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find unit by ID.
     */
    public function findById(int $id, array $with = []): ?Unit;

    /**
     * Find unit by name.
     */
    public function findByName(string $name, array $with = []): ?Unit;

    /**
     * Find unit by symbol.
     */
    public function findBySymbol(string $symbol, array $with = []): ?Unit;

    /**
     * Create a new unit.
     */
    public function create(array $data): Unit;

    /**
     * Update an existing unit.
     */
    public function update(Unit $unit, array $data): Unit;

    /**
     * Delete a unit.
     */
    public function delete(Unit $unit): bool;

    /**
     * Get units by status.
     */
    public function getByStatus(string $status, array $with = []): Collection;

    /**
     * Get active units.
     */
    public function getActiveUnits(array $with = []): Collection;

    /**
     * Check if unit has product units.
     */
    public function hasProductUnits(int $unitId): bool;

    /**
     * Check if unit has sales transaction items.
     */
    public function hasSalesTransactionItems(int $unitId): bool;

    /**
     * Search units.
     */
    public function search(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if unit name exists.
     */
    public function nameExists(string $name, ?int $excludeId = null): bool;

    /**
     * Check if unit symbol exists.
     */
    public function symbolExists(string $symbol, ?int $excludeId = null): bool;
    
    /**
     * Get unit statistics.
     */
    public function getStatistics(): array;
}