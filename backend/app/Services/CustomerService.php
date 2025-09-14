<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class CustomerService
{
    protected CustomerRepositoryInterface $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Get all Customers.
     */
    public function getAllCustomers(array $with = []): Collection
    {
        return $this->customerRepository->getAll($with);
    }

    /**
     * Get paginated Customers with filters.
     */
    public function getPaginatedCustomers(array $filters = [], array $with = [], array $withCount = [], int $perPage = 15, string $sortBy = 'id', string $sortOrder = 'desc'): LengthAwarePaginator
    {
        return $this->customerRepository->getPaginated($filters, $with, $withCount, $perPage, $sortBy, $sortOrder);
    }

    /**
     * Get Customer by ID.
     */
    public function getCustomerById(int $id, array $with = []): ?Customer
    {
        return $this->customerRepository->findById($id, $with);
    }

    /**
     * Get active Customers for options.
     */
    public function getActiveCustomersForOptions(): Collection
    {
        return $this->customerRepository->getActiveCustomers()->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                // Add other fields as needed
            ];
        });
    }

    /**
     * Create a new Customer.
     */
    public function createCustomer(array $data): Customer
    {
        $this->validateCustomerData($data);
        
        return $this->customerRepository->create($data);
    }

    /**
     * Update an existing Customer.
     */
    public function updateCustomer(int $id, array $data): Customer
    {
        $customer = $this->getCustomerById($id);
        
        if (!$customer) {
            throw new \Exception('Customer not found');
        }

        $this->validateCustomerData($data, $id);
        
        return $this->customerRepository->update($customer, $data);
    }

    /**
     * Delete a Customer.
     */
    public function deleteCustomer(int $id): bool
    {
        $customer = $this->getCustomerById($id);
        
        if (!$customer) {
            throw new \Exception('Customer not found');
        }

        // Check if Customer is being used
        if ($this->isCustomerInUse($id)) {
            throw new \Exception('Cannot delete Customer that is being used');
        }

        return $this->customerRepository->delete($customer);
    }

    /**
     * Search Customers.
     */
    public function searchCustomers(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->customerRepository->search($query, $with, $perPage);
    }

    /**
     * Get Customer statistics.
     */
    public function getCustomerStatistics(): array
    {
        return $this->customerRepository->getStatistics();
    }

    /**
     * Check if Customer is in use.
     */
    public function isCustomerInUse(int $customerId): bool
    {
        // Implement your business logic to check if the Customer is being used
        // Example: return $this->customerRepository->hasRelatedRecords($customerId);
        return false;
    }

    /**
     * Validate Customer data.
     */
    protected function validateCustomerData(array $data, ?int $excludeId = null): void
    {
        // Implement your validation logic here
        // Example:
        // if (isset($data['name']) && $this->customerRepository->nameExists($data['name'], $excludeId)) {
        //     throw ValidationException::withMessages([
        //         'name' => ['The Customer name has already been taken.']
        //     ]);
        // }
    }

    /**
     * Get Customers by status.
     */
    public function getCustomersByStatus(string $status, array $with = []): Collection
    {
        return $this->customerRepository->getByStatus($status, $with);
    }

    /**
     * Get active Customers.
     */
    public function getActiveCustomers(array $with = []): Collection
    {
        return $this->customerRepository->getActiveCustomers($with);
    }

    /**
     * Toggle Customer status.
     */
    public function toggleCustomerStatus(int $id): Customer
    {
        $customer = $this->getCustomerById($id);
        
        if (!$customer) {
            throw new \Exception('Customer not found');
        }

        $newStatus = $customer->status === 'active' ? 'inactive' : 'active';
        
        return $this->customerRepository->update($customer, ['status' => $newStatus]);
    }
}