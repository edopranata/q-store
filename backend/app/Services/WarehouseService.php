<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class WarehouseService
{
    protected WarehouseRepositoryInterface $warehouseRepository;

    public function __construct(WarehouseRepositoryInterface $warehouseRepository)
    {
        $this->warehouseRepository = $warehouseRepository;
    }

    /**
     * Get all Warehouses.
     */
    public function getAllWarehouses(array $with = []): Collection
    {
        return $this->warehouseRepository->getAll($with);
    }

    /**
     * Get paginated Warehouses with filters.
     */
    public function getPaginatedWarehouses(array $filters = [], array $with = [], array $withCount = [], int $perPage = 15, string $sortBy = 'name', string $sortOrder = 'asc'): LengthAwarePaginator
    {
        return $this->warehouseRepository->getPaginated($filters, $with, $withCount, $perPage, $sortBy, $sortOrder);
    }

    /**
     * Get Warehouse by ID.
     */
    public function getWarehouseById(int $id, array $with = []): ?Warehouse
    {
        return $this->warehouseRepository->findById($id, $with);
    }

    /**
     * Get active Warehouses for options.
     */
    public function getActiveWarehousesForOptions(): Collection
    {
        return $this->warehouseRepository->getActiveWarehouses()->map(function ($warehouse) {
            return [
                'value' => $warehouse->id,
                'label' => $warehouse->name,
                'code' => $warehouse->code,
                'address' => $warehouse->address,
                'manager_name' => $warehouse->manager_name
            ];
        });
    }

    /**
     * Create a new Warehouse.
     */
    public function createWarehouse(array $data): Warehouse
    {
        // Validate unique name and code
        if ($this->warehouseRepository->nameExists($data['name'])) {
            throw ValidationException::withMessages([
                'name' => ['The warehouse name has already been taken.']
            ]);
        }

        if ($this->warehouseRepository->codeExists($data['code'])) {
            throw ValidationException::withMessages([
                'code' => ['The warehouse code has already been taken.']
            ]);
        }

        return $this->warehouseRepository->create($data);
    }

    /**
     * Update an existing Warehouse.
     */
    public function updateWarehouse(int $id, array $data): Warehouse
    {
        $warehouse = $this->getWarehouseById($id);
        
        if (!$warehouse) {
            throw new \Exception('Warehouse not found');
        }

        // Validate unique name and code (excluding current warehouse)
        if (isset($data['name']) && $this->warehouseRepository->nameExists($data['name'], $id)) {
            throw ValidationException::withMessages([
                'name' => ['The warehouse name has already been taken.']
            ]);
        }

        if (isset($data['code']) && $this->warehouseRepository->codeExists($data['code'], $id)) {
            throw ValidationException::withMessages([
                'code' => ['The warehouse code has already been taken.']
            ]);
        }

        return $this->warehouseRepository->update($warehouse, $data);
    }

    /**
     * Delete a Warehouse.
     */
    public function deleteWarehouse(int $id): bool
    {
        $warehouse = $this->getWarehouseById($id);
        
        if (!$warehouse) {
            throw new \Exception('Warehouse not found');
        }

        // Check if warehouse has related records
        if ($this->warehouseRepository->hasRelatedRecords($id)) {
            throw new \Exception('Cannot delete warehouse with existing inventory stocks or stock batches');
        }

        return $this->warehouseRepository->delete($warehouse);
    }

    /**
     * Search Warehouses.
     */
    public function searchWarehouses(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->warehouseRepository->search($query, $with, $perPage);
    }

    /**
     * Get Warehouses by status.
     */
    public function getWarehousesByStatus(string $status, array $with = []): Collection
    {
        return $this->warehouseRepository->getByStatus($status, $with);
    }

    /**
     * Get Warehouse statistics.
     */
    public function getWarehouseStatistics(): array
    {
        return $this->warehouseRepository->getStatistics();
    }

    /**
     * Toggle Warehouse status.
     */
    public function toggleWarehouseStatus(int $id): Warehouse
    {
        $warehouse = $this->getWarehouseById($id);
        
        if (!$warehouse) {
            throw new \Exception('Warehouse not found');
        }

        $newStatus = $warehouse->status === 'active' ? 'inactive' : 'active';
        
        return $this->warehouseRepository->update($warehouse, ['status' => $newStatus]);
    }

    /**
     * Generate unique warehouse code.
     */
    public function generateWarehouseCode(string $prefix = 'WH'): string
    {
        $counter = 1;
        do {
            $code = $prefix . str_pad($counter, 3, '0', STR_PAD_LEFT);
            $counter++;
        } while ($this->warehouseRepository->codeExists($code));

        return $code;
    }

    /**
     * Validate warehouse data.
     */
    public function validateWarehouseData(array $data, ?int $excludeId = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive'
        ];

        // Add unique validation for name and code
        if ($excludeId) {
            $rules['name'] .= '|unique:warehouses,name,' . $excludeId;
            $rules['code'] .= '|unique:warehouses,code,' . $excludeId;
        } else {
            $rules['name'] .= '|unique:warehouses,name';
            $rules['code'] .= '|unique:warehouses,code';
        }

        return $rules;
    }

    /**
     * Generate unique warehouse code.
     */
    public function generateUniqueCode(): string
    {
        do {
            $code = 'WH' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while ($this->warehouseRepository->codeExists($code));

        return $code;
    }
}