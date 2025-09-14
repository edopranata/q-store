<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Role;

interface UserRepositoryInterface
{
    /**
     * Get all users with optional filters.
     */
    public function getAll(array $filters = [], array $with = []): Collection;

    /**
     * Get paginated users with optional filters.
     */
    public function getPaginated(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find user by ID.
     */
    public function findById(int $id, array $with = []): ?User;

    /**
     * Find user by email.
     */
    public function findByEmail(string $email, array $with = []): ?User;

    /**
     * Find user by username.
     */
    public function findByUsername(string $username, array $with = []): ?User;

    /**
     * Create a new user.
     */
    public function create(array $data): User;

    /**
     * Update an existing user.
     */
    public function update(User $user, array $data): User;

    /**
     * Delete a user.
     */
    public function delete(User $user): bool;

    /**
     * Assign role to user.
     */
    public function assignRole(User $user, string|Role $role): User;

    /**
     * Remove role from user.
     */
    public function removeRole(User $user, string|Role $role): User;

    /**
     * Sync user roles.
     */
    public function syncRoles(User $user, array $roles): User;

    /**
     * Get users by role.
     */
    public function getUsersByRole(string $roleName, array $with = []): Collection;

    /**
     * Get active users.
     */
    public function getActiveUsers(array $with = []): Collection;

    /**
     * Get inactive users.
     */
    public function getInactiveUsers(array $with = []): Collection;

    /**
     * Toggle user status.
     */
    public function toggleStatus(User $user): User;

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(User $user): User;

    /**
     * Search users by name, email, or username.
     */
    public function search(string $query, array $with = []): Collection;

    /**
     * Get user statistics.
     */
    public function getStatistics(): array;

    /**
     * Get users with their transaction statistics.
     */
    public function getUsersWithTransactionStats(array $filters = []);

    /**
     * Get user performance metrics.
     */
    public function getUserPerformanceMetrics(int $userId, array $dateRange = []);

    /**
     * Get users who can be safely deleted (no related transactions).
     */
    public function getUsersForDeletion();

    /**
     * Get users with recent activity.
     */
    public function getUsersWithRecentActivity(int $days = 30);

    /**
     * Bulk update user status.
     */
    public function bulkUpdateStatus(array $userIds, bool $isActive);

    /**
     * Get user activity timeline.
     */
    public function getUserActivityTimeline(int $userId, int $limit = 50);
}