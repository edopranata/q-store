<?php

namespace App\Repositories;

use App\Models\Unit;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UnitRepository implements UnitRepositoryInterface
{
    protected Unit $model;

    public function __construct(Unit $model)
    {
        $this->model = $model;
    }

    /**
     * Get all units.
     */
    public function getAll(array $with = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    /**
     * Get paginated units.
     */
    public function getPaginated(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $allowedSortFields = ['name', 'symbol', 'status', 'created_at', 'updated_at'];
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('name', 'asc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Find unit by ID.
     */
    public function findById(int $id, array $with = []): ?Unit
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Find unit by name.
     */
    public function findByName(string $name, array $with = []): ?Unit
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('name', $name)->first();
    }

    /**
     * Find unit by symbol.
     */
    public function findBySymbol(string $symbol, array $with = []): ?Unit
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('symbol', $symbol)->first();
    }

    /**
     * Create a new unit.
     */
    public function create(array $data): Unit
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing unit.
     */
    public function update(Unit $unit, array $data): Unit
    {
        $unit->update($data);
        return $unit->fresh();
    }

    /**
     * Delete a unit.
     */
    public function delete(Unit $unit): bool
    {
        return $unit->delete();
    }

    /**
     * Get units by status.
     */
    public function getByStatus(string $status, array $with = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('status', $status)->get();
    }

    /**
     * Get active units.
     */
    public function getActiveUnits(array $with = []): Collection
    {
        return $this->getByStatus('active', $with);
    }

    /**
     * Check if unit has product units.
     */
    public function hasProductUnits(int $unitId): bool
    {
        return $this->model->find($unitId)?->productUnits()->exists() ?? false;
    }

    /**
     * Check if unit has sales transaction items.
     */
    public function hasSalesTransactionItems(int $unitId): bool
    {
        return $this->model->find($unitId)?->salesTransactionItems()->exists() ?? false;
    }

    /**
     * Search units.
     */
    public function search(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        $queryBuilder = $this->model->newQuery();

        if (!empty($with)) {
            $queryBuilder->with($with);
        }

        $queryBuilder->where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('symbol', 'LIKE', "%{$query}%")
              ->orWhere('description', 'LIKE', "%{$query}%");
        });

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Check if unit name exists.
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $query = $this->model->where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if unit symbol exists.
     */
    public function symbolExists(string $symbol, ?int $excludeId = null): bool
    {
        $query = $this->model->where('symbol', $symbol);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get unit statistics.
     */
    public function getStatistics(): array
    {
        $totalUnits = $this->model->count();
        $activeUnits = $this->model->where('status', 'active')->count();
        $inactiveUnits = $this->model->where('status', 'inactive')->count();
        
        $unitsWithProducts = $this->model->has('productUnits')->count();
        $unitsWithoutProducts = $totalUnits - $unitsWithProducts;

        return [
            'total_units' => $totalUnits,
            'active_units' => $activeUnits,
            'inactive_units' => $inactiveUnits,
            'units_with_products' => $unitsWithProducts,
            'units_without_products' => $unitsWithoutProducts,
        ];
    }

    /**
     * Apply filters to the query.
     */
    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('symbol', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
    }
}