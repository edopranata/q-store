<?php

namespace App\Services;

use App\Models\Unit;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class UnitService
{
    protected UnitRepositoryInterface $unitRepository;

    public function __construct(UnitRepositoryInterface $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    /**
     * Get all units.
     */
    public function getAllUnits(array $with = []): Collection
    {
        return $this->unitRepository->getAll($with);
    }

    /**
     * Get paginated units with filters.
     */
    public function getPaginatedUnits(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->unitRepository->getPaginated($filters, $with, $perPage);
    }

    /**
     * Get unit by ID.
     */
    public function getUnitById(int $id, array $with = []): ?Unit
    {
        return $this->unitRepository->findById($id, $with);
    }

    /**
     * Get active units for options.
     */
    public function getActiveUnitsForOptions(): Collection
    {
        return $this->unitRepository->getActiveUnits()->map(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'symbol' => $unit->symbol,
            ];
        });
    }

    /**
     * Create a new unit.
     */
    public function createUnit(array $data): Unit
    {
        $this->validateUnitData($data);
        
        return $this->unitRepository->create($data);
    }

    /**
     * Update an existing unit.
     */
    public function updateUnit(int $id, array $data): Unit
    {
        $unit = $this->getUnitById($id);
        
        if (!$unit) {
            throw new \Exception('Unit not found');
        }

        $this->validateUnitData($data, $id);
        
        return $this->unitRepository->update($unit, $data);
    }

    /**
     * Delete a unit.
     */
    public function deleteUnit(int $id): bool
    {
        $unit = $this->getUnitById($id);
        
        if (!$unit) {
            throw new \Exception('Unit not found');
        }

        // Check if unit is being used
        if ($this->isUnitInUse($id)) {
            throw new \Exception('Cannot delete unit that is being used in products or sales transactions');
        }

        return $this->unitRepository->delete($unit);
    }

    /**
     * Search units.
     */
    public function searchUnits(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->unitRepository->search($query, $with, $perPage);
    }

    /**
     * Get unit statistics.
     */
    public function getUnitStatistics(): array
    {
        return $this->unitRepository->getStatistics();
    }

    /**
     * Check if unit is in use.
     */
    public function isUnitInUse(int $unitId): bool
    {
        return $this->unitRepository->hasProductUnits($unitId) || 
               $this->unitRepository->hasSalesTransactionItems($unitId);
    }

    /**
     * Validate unit data.
     */
    protected function validateUnitData(array $data, ?int $excludeId = null): void
    {
        // Check if name already exists
        if (isset($data['name']) && $this->unitRepository->nameExists($data['name'], $excludeId)) {
            throw ValidationException::withMessages([
                'name' => ['The unit name has already been taken.']
            ]);
        }

        // Check if symbol already exists
        if (isset($data['symbol']) && $this->unitRepository->symbolExists($data['symbol'], $excludeId)) {
            throw ValidationException::withMessages([
                'symbol' => ['The unit symbol has already been taken.']
            ]);
        }
    }

    /**
     * Get units by status.
     */
    public function getUnitsByStatus(string $status, array $with = []): Collection
    {
        return $this->unitRepository->getByStatus($status, $with);
    }

    /**
     * Get active units.
     */
    public function getActiveUnits(array $with = []): Collection
    {
        return $this->unitRepository->getActiveUnits($with);
    }
}