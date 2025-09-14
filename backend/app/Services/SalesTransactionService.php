<?php

namespace App\Services;

use App\Models\SalesTransaction;
use App\Repositories\Contracts\SalesTransactionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class SalesTransactionService
{
    protected SalesTransactionRepositoryInterface $salesTransactionRepository;

    public function __construct(SalesTransactionRepositoryInterface $salesTransactionRepository)
    {
        $this->salesTransactionRepository = $salesTransactionRepository;
    }

    /**
     * Get all SalesTransactions.
     */
    public function getAllSalesTransactions(array $with = []): Collection
    {
        return $this->salesTransactionRepository->getAll($with);
    }

    /**
     * Get paginated SalesTransactions with filters.
     */
    public function getPaginatedSalesTransactions(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->salesTransactionRepository->getPaginated($filters, $with, $perPage);
    }

    /**
     * Get SalesTransaction by ID.
     */
    public function getSalesTransactionById(int $id, array $with = []): ?SalesTransaction
    {
        return $this->salesTransactionRepository->findById($id, $with);
    }

    /**
     * Get active SalesTransactions for options.
     */
    public function getActiveSalesTransactionsForOptions(): Collection
    {
        return $this->salesTransactionRepository->getActiveSalesTransactions()->map(function ($salesTransaction) {
            return [
                'id' => $salesTransaction->id,
                'name' => $salesTransaction->name,
                // Add other fields as needed
            ];
        });
    }

    /**
     * Create a new SalesTransaction.
     */
    public function createSalesTransaction(array $data): SalesTransaction
    {
        $this->validateSalesTransactionData($data);
        
        return $this->salesTransactionRepository->create($data);
    }

    /**
     * Update an existing SalesTransaction.
     */
    public function updateSalesTransaction(int $id, array $data): SalesTransaction
    {
        $salesTransaction = $this->getSalesTransactionById($id);
        
        if (!$salesTransaction) {
            throw new \Exception('SalesTransaction not found');
        }

        $this->validateSalesTransactionData($data, $id);
        
        return $this->salesTransactionRepository->update($salesTransaction, $data);
    }

    /**
     * Delete a SalesTransaction.
     */
    public function deleteSalesTransaction(int $id): bool
    {
        $salesTransaction = $this->getSalesTransactionById($id);
        
        if (!$salesTransaction) {
            throw new \Exception('SalesTransaction not found');
        }

        // Check if SalesTransaction is being used
        if ($this->isSalesTransactionInUse($id)) {
            throw new \Exception('Cannot delete SalesTransaction that is being used');
        }

        return $this->salesTransactionRepository->delete($salesTransaction);
    }

    /**
     * Search SalesTransactions.
     */
    public function searchSalesTransactions(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->salesTransactionRepository->search($query, $with, $perPage);
    }

    /**
     * Get SalesTransaction statistics.
     */
    public function getSalesTransactionStatistics(): array
    {
        return $this->salesTransactionRepository->getStatistics();
    }

    /**
     * Check if SalesTransaction is in use.
     */
    public function isSalesTransactionInUse(int $salesTransactionId): bool
    {
        // Implement your business logic to check if the SalesTransaction is being used
        // Example: return $this->salesTransactionRepository->hasRelatedRecords($salesTransactionId);
        return false;
    }

    /**
     * Validate SalesTransaction data.
     */
    protected function validateSalesTransactionData(array $data, ?int $excludeId = null): void
    {
        // Implement your validation logic here
        // Example:
        // if (isset($data['name']) && $this->salesTransactionRepository->nameExists($data['name'], $excludeId)) {
        //     throw ValidationException::withMessages([
        //         'name' => ['The SalesTransaction name has already been taken.']
        //     ]);
        // }
    }

    /**
     * Get SalesTransactions by status.
     */
    public function getSalesTransactionsByStatus(string $status, array $with = []): Collection
    {
        return $this->salesTransactionRepository->getByStatus($status, $with);
    }

    /**
     * Get active SalesTransactions.
     */
    public function getActiveSalesTransactions(array $with = []): Collection
    {
        return $this->salesTransactionRepository->getActiveSalesTransactions($with);
    }
}