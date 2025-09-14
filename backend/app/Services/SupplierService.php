<?php

namespace App\Services;

use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class SupplierService
{
    protected SupplierRepositoryInterface $supplierRepository;

    public function __construct(SupplierRepositoryInterface $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    /**
     * Get all Suppliers.
     */
    public function getAllSuppliers(array $with = []): Collection
    {
        return $this->supplierRepository->getAll($with);
    }

    /**
     * Get paginated Suppliers with filters.
     */
    public function getPaginatedSuppliers(array $filters = [], array $with = [], array $withCount = [], int $perPage = 15, string $sortBy = 'name', string $sortOrder = 'asc'): LengthAwarePaginator
    {
        return $this->supplierRepository->getPaginated($filters, $with, $withCount, $perPage, $sortBy, $sortOrder);
    }

    /**
     * Get Supplier by ID.
     */
    public function getSupplierById(int $id, array $with = []): ?Supplier
    {
        return $this->supplierRepository->findById($id, $with);
    }

    /**
     * Get active Suppliers for options.
     */
    public function getActiveSuppliersForOptions(): Collection
    {
        return $this->supplierRepository->getActiveSuppliers()->map(function ($supplier) {
            return [
                'value' => $supplier->id,
                'label' => $supplier->name,
                'contact_person' => $supplier->contact_person,
                'phone' => $supplier->phone,
                'email' => $supplier->email
            ];
        });
    }

    /**
     * Create a new Supplier.
     */
    public function createSupplier(array $data): Supplier
    {
        // Validate unique name
        if ($this->supplierRepository->nameExists($data['name'])) {
            throw ValidationException::withMessages([
                'name' => ['The supplier name has already been taken.']
            ]);
        }

        return $this->supplierRepository->create($data);
    }

    /**
     * Update an existing Supplier.
     */
    public function updateSupplier(int $id, array $data): Supplier
    {
        $supplier = $this->getSupplierById($id);
        
        if (!$supplier) {
            throw new \Exception('Supplier not found');
        }

        // Validate unique name (excluding current supplier)
        if (isset($data['name']) && $this->supplierRepository->nameExists($data['name'], $id)) {
            throw ValidationException::withMessages([
                'name' => ['The supplier name has already been taken.']
            ]);
        }

        return $this->supplierRepository->update($supplier, $data);
    }

    /**
     * Delete a Supplier.
     */
    public function deleteSupplier(int $id): bool
    {
        $supplier = $this->getSupplierById($id);
        
        if (!$supplier) {
            throw new \Exception('Supplier not found');
        }

        // Check if supplier has related records
        if ($this->supplierRepository->hasRelatedRecords($id)) {
            throw new \Exception('Cannot delete supplier with existing purchase orders');
        }

        return $this->supplierRepository->delete($supplier);
    }

    /**
     * Search Suppliers.
     */
    public function searchSuppliers(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->supplierRepository->search($query, $with, $perPage);
    }

    /**
     * Get Suppliers by status.
     */
    public function getSuppliersByStatus(string $status, array $with = []): Collection
    {
        return $this->supplierRepository->getByStatus($status, $with);
    }

    /**
     * Get Supplier statistics.
     */
    public function getSupplierStatistics(): array
    {
        return $this->supplierRepository->getStatistics();
    }

    /**
     * Toggle Supplier status.
     */
    public function toggleSupplierStatus(int $id): Supplier
    {
        $supplier = $this->getSupplierById($id);
        
        if (!$supplier) {
            throw new \Exception('Supplier not found');
        }

        $newStatus = $supplier->status === 'active' ? 'inactive' : 'active';
        
        return $this->supplierRepository->update($supplier, ['status' => $newStatus]);
    }

    /**
     * Validate supplier data.
     */
    public function validateSupplierData(array $data, ?int $excludeId = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ];

        // Add unique validation for name
        if ($excludeId) {
            $rules['name'] .= '|unique:suppliers,name,' . $excludeId;
        } else {
            $rules['name'] .= '|unique:suppliers,name';
        }

        return $rules;
    }
}