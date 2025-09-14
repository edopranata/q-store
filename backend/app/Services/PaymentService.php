<?php

namespace App\Services;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class PaymentServiceService
{
    protected PaymentRepositoryInterface $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Get all Payments.
     */
    public function getAllPayments(array $with = []): Collection
    {
        return $this->paymentRepository->getAll($with);
    }

    /**
     * Get paginated Payments with filters.
     */
    public function getPaginatedPayments(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->paymentRepository->getPaginated($filters, $with, $perPage);
    }

    /**
     * Get Payment by ID.
     */
    public function getPaymentById(int $id, array $with = []): ?Payment
    {
        return $this->paymentRepository->findById($id, $with);
    }

    /**
     * Get active Payments for options.
     */
    public function getActivePaymentsForOptions(): Collection
    {
        return $this->paymentRepository->getActivePayments()->map(function ($payment) {
            return [
                'id' => $payment->id,
                'name' => $payment->name,
                // Add other fields as needed
            ];
        });
    }

    /**
     * Create a new Payment.
     */
    public function createPayment(array $data): Payment
    {
        $this->validatePaymentData($data);
        
        return $this->paymentRepository->create($data);
    }

    /**
     * Update an existing Payment.
     */
    public function updatePayment(int $id, array $data): Payment
    {
        $payment = $this->getPaymentById($id);
        
        if (!$payment) {
            throw new \Exception('Payment not found');
        }

        $this->validatePaymentData($data, $id);
        
        return $this->paymentRepository->update($payment, $data);
    }

    /**
     * Delete a Payment.
     */
    public function deletePayment(int $id): bool
    {
        $payment = $this->getPaymentById($id);
        
        if (!$payment) {
            throw new \Exception('Payment not found');
        }

        // Check if Payment is being used
        if ($this->isPaymentInUse($id)) {
            throw new \Exception('Cannot delete Payment that is being used');
        }

        return $this->paymentRepository->delete($payment);
    }

    /**
     * Search Payments.
     */
    public function searchPayments(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->paymentRepository->search($query, $with, $perPage);
    }

    /**
     * Get Payment statistics.
     */
    public function getPaymentStatistics(): array
    {
        return $this->paymentRepository->getStatistics();
    }

    /**
     * Check if Payment is in use.
     */
    public function isPaymentInUse(int $paymentId): bool
    {
        // Implement your business logic to check if the Payment is being used
        // Example: return $this->paymentRepository->hasRelatedRecords($paymentId);
        return false;
    }

    /**
     * Validate Payment data.
     */
    protected function validatePaymentData(array $data, ?int $excludeId = null): void
    {
        // Implement your validation logic here
        // Example:
        // if (isset($data['name']) && $this->paymentRepository->nameExists($data['name'], $excludeId)) {
        //     throw ValidationException::withMessages([
        //         'name' => ['The Payment name has already been taken.']
        //     ]);
        // }
    }

    /**
     * Get Payments by status.
     */
    public function getPaymentsByStatus(string $status, array $with = []): Collection
    {
        return $this->paymentRepository->getByStatus($status, $with);
    }

    /**
     * Get active Payments.
     */
    public function getActivePayments(array $with = []): Collection
    {
        return $this->paymentRepository->getActivePayments($with);
    }
}