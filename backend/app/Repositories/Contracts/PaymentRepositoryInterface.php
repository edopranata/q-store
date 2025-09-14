<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface
{
    /**
     * Get all Payments.
     */
    public function getAll(array $with = []): Collection;

    /**
     * Get paginated Payments.
     */
    public function getPaginated(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find Payment by ID.
     */
    public function findById(int $id, array $with = []): ?Payment;

    /**
     * Find Payment by name.
     */
    public function findByName(string $name, array $with = []): ?Payment;

    /**
     * Create a new Payment.
     */
    public function create(array $data): Payment;

    /**
     * Update an existing Payment.
     */
    public function update(Payment $payment, array $data): Payment;

    /**
     * Delete a Payment.
     */
    public function delete(Payment $payment): bool;

    /**
     * Get Payments by status.
     */
    public function getByStatus(string $status, array $with = []): Collection;

    /**
     * Get active Payments.
     */
    public function getActivePayments(array $with = []): Collection;

    /**
     * Search Payments.
     */
    public function search(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if Payment name exists.
     */
    public function nameExists(string $name, ?int $excludeId = null): bool;
    
    /**
     * Get Payment statistics.
     */
    public function getStatistics(): array;

    /**
     * Check if Payment has related records.
     */
    public function hasRelatedRecords(int $paymentId): bool;
}