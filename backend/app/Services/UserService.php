<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Role;
use Carbon\Carbon;

class UserService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all users with optional filters.
     */
    public function getAllUsers(array $filters = [], array $with = []): Collection
    {
        return $this->userRepository->getAll($filters, $with);
    }

    /**
     * Get paginated users.
     */
    public function getPaginatedUsers(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            // Validate filters
            $this->validateFilters($filters);
            
            // Ensure per page is within reasonable limits
            $perPage = max(1, min($perPage, 100));
            
            return $this->userRepository->getPaginated($filters, $with, $perPage);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting paginated users: ' . $e->getMessage(), [
                'filters' => $filters,
                'with' => $with,
                'perPage' => $perPage
            ]);
            throw $e;
        }
    }

    /**
     * Find user by ID.
     */
    public function findUser(int $id, array $with = []): ?User
    {
        return $this->userRepository->findById($id, $with);
    }

    /**
     * Find user by email.
     */
    public function findUserByEmail(string $email, array $with = []): ?User
    {
        return $this->userRepository->findByEmail($email, $with);
    }

    /**
     * Find user by username.
     */
    public function findUserByUsername(string $username, array $with = []): ?User
    {
        return $this->userRepository->findByUsername($username, $with);
    }

    /**
     * Create a new user with validation.
     */
    public function createUser(array $data): User
    {
        // Validate unique constraints
        $this->validateUniqueFields($data);

        DB::beginTransaction();
        try {
            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Set default values
            $data['is_active'] = $data['is_active'] ?? true;

            $user = $this->userRepository->create($data);

            // Assign roles if provided
            if (isset($data['roles']) && is_array($data['roles'])) {
                $this->assignRolesToUser($user, $data['roles']);
            } else {
                // Assign default role if no roles specified
                $defaultRole = config('auth.default_role', 'user');
                if (Role::where('name', $defaultRole)->exists()) {
                    $user->assignRole($defaultRole);
                }
            }

            DB::commit();
            return $user->load(['roles']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing user.
     */
    public function updateUser(User $user, array $data): User
    {
        // Validate unique constraints (excluding current user)
        $this->validateUniqueFields($data, $user->id);

        DB::beginTransaction();
        try {
            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user = $this->userRepository->update($user, $data);

            // Update roles if provided
            if (isset($data['roles']) && is_array($data['roles'])) {
                $this->syncUserRoles($user, $data['roles']);
            }

            DB::commit();
            return $user->load(['roles']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user): bool
    {
        DB::beginTransaction();
        try {
            // Remove all roles before deleting
            $user->roles()->detach();
            
            $result = $this->userRepository->delete($user);
            
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Assign role to user.
     */
    public function assignRoleToUser(User $user, string $roleName): User
    {
        $this->validateRoleExists($roleName);
        return $this->userRepository->assignRole($user, $roleName);
    }

    /**
     * Remove role from user.
     */
    public function removeRoleFromUser(User $user, string $roleName): User
    {
        $this->validateRoleExists($roleName);
        return $this->userRepository->removeRole($user, $roleName);
    }

    /**
     * Sync user roles.
     */
    public function syncUserRoles(User $user, array $roles): User
    {
        $this->validateRolesExist($roles);
        return $this->userRepository->syncRoles($user, $roles);
    }

    /**
     * Assign multiple roles to user.
     */
    public function assignRolesToUser(User $user, array $roles): User
    {
        $this->validateRolesExist($roles);
        
        foreach ($roles as $role) {
            $user->assignRole($role);
        }
        
        return $user->fresh(['roles']);
    }

    /**
     * Get users by role.
     */
    public function getUsersByRole(string $roleName, array $with = []): Collection
    {
        $this->validateRoleExists($roleName);
        return $this->userRepository->getUsersByRole($roleName, $with);
    }

    /**
     * Get active users.
     */
    public function getActiveUsers(array $with = []): Collection
    {
        return $this->userRepository->getActiveUsers($with);
    }

    /**
     * Get inactive users.
     */
    public function getInactiveUsers(array $with = []): Collection
    {
        return $this->userRepository->getInactiveUsers($with);
    }

    /**
     * Toggle user status.
     */
    public function toggleUserStatus(User $user): User
    {
        return $this->userRepository->toggleStatus($user);
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(User $user): User
    {
        return $this->userRepository->updateLastLogin($user);
    }

    /**
     * Search users.
     */
    public function searchUsers(string $query, array $with = []): Collection
    {
        return $this->userRepository->search($query, $with);
    }

    /**
     * Get user statistics.
     */
    public function getUserStatistics(): array
    {
        return $this->userRepository->getStatistics();
    }

    /**
     * Change user password.
     */
    public function changePassword(User $user, string $newPassword): User
    {
        return $this->userRepository->update($user, [
            'password' => Hash::make($newPassword)
        ]);
    }

    /**
     * Activate user.
     */
    public function activateUser(User $user): User
    {
        return $this->userRepository->update($user, ['is_active' => true]);
    }

    /**
     * Deactivate user.
     */
    public function deactivateUser(User $user): User
    {
        return $this->userRepository->update($user, ['is_active' => false]);
    }

    /**
     * Get all available roles.
     */
    public function getAvailableRoles(): Collection
    {
        return Role::all();
    }

    /**
     * Validate unique fields.
     */
    protected function validateUniqueFields(array $data, ?int $excludeId = null): void
    {
        if (isset($data['email'])) {
            $query = User::where('email', $data['email']);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if ($query->exists()) {
                throw ValidationException::withMessages([
                    'email' => ['The email has already been taken.']
                ]);
            }
        }

        if (isset($data['username'])) {
            $query = User::where('username', $data['username']);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if ($query->exists()) {
                throw ValidationException::withMessages([
                    'username' => ['The username has already been taken.']
                ]);
            }
        }
    }

    /**
     * Validate role exists.
     */
    protected function validateRoleExists(string $roleName): void
    {
        if (!Role::where('name', $roleName)->exists()) {
            throw ValidationException::withMessages([
                'role' => ["Role '{$roleName}' does not exist."]
            ]);
        }
    }

    /**
     * Validate roles exist.
     */
    protected function validateRolesExist(array $roles): void
    {
        foreach ($roles as $role) {
            $this->validateRoleExists($role);
        }
    }

    /**
     * Validate filters for user queries.
     */
    protected function validateFilters(array $filters): void
    {
        $allowedFilters = [
            'status', 'role', 'search', 'created_from', 'created_to',
            'last_login_from', 'last_login_to', 'sort_by', 'sort_order'
        ];

        $allowedSortColumns = [
            'id', 'name', 'email', 'username', 'full_name', 
            'is_active', 'created_at', 'updated_at', 'last_login'
        ];

        foreach ($filters as $key => $value) {
            if (!in_array($key, $allowedFilters)) {
                throw ValidationException::withMessages([
                    $key => ["Filter '{$key}' is not allowed."]
                ]);
            }

            // Validate sort_by column
            if ($key === 'sort_by' && !in_array($value, $allowedSortColumns)) {
                throw ValidationException::withMessages([
                    'sort_by' => ["Sort column '{$value}' is not allowed."]
                ]);
            }

            // Validate sort_order
            if ($key === 'sort_order' && !in_array(strtolower($value), ['asc', 'desc'])) {
                throw ValidationException::withMessages([
                    'sort_order' => ["Sort order must be 'asc' or 'desc'."]
                ]);
            }

            // Validate role exists if role filter is provided
            if ($key === 'role' && !empty($value)) {
                if (!Role::where('name', $value)->orWhere('id', $value)->exists()) {
                    throw ValidationException::withMessages([
                        'role' => ["Role '{$value}' does not exist."]
                    ]);
                }
            }
        }
    }

    // ========================================
    // ENHANCED FEATURES FOR POS SYSTEM
    // ========================================

    /**
     * Get users with transaction statistics.
     */
    public function getUsersWithTransactionStats(array $filters = [])
    {
        try {
            $this->validateFilters($filters);
            return $this->userRepository->getUsersWithTransactionStats($filters);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting users with transaction stats', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get user performance metrics.
     */
    public function getUserPerformanceMetrics(int $userId, array $dateRange = [])
    {
        try {
            $user = $this->findUser($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }

            return $this->userRepository->getUserPerformanceMetrics($userId, $dateRange);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting user performance metrics', [
                'user_id' => $userId,
                'date_range' => $dateRange,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get users who can be safely deleted.
     */
    public function getUsersForDeletion()
    {
        try {
            return $this->userRepository->getUsersForDeletion();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting users for deletion', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get users with recent activity.
     */
    public function getUsersWithRecentActivity(int $days = 30)
    {
        try {
            return $this->userRepository->getUsersWithRecentActivity($days);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting users with recent activity', [
                'days' => $days,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Bulk update user status.
     */
    public function bulkUpdateUserStatus(array $userIds, bool $isActive)
    {
        try {
            DB::beginTransaction();

            // Validate that all users exist
             $existingUsersCount = $this->userRepository->getAll()
                 ->whereIn('id', $userIds)
                 ->count();
             if ($existingUsersCount !== count($userIds)) {
                 throw new \Exception('Some users not found');
             }

            $result = $this->userRepository->bulkUpdateStatus($userIds, $isActive);

            DB::commit();

            \Illuminate\Support\Facades\Log::info('Bulk user status update completed', [
                'user_ids' => $userIds,
                'is_active' => $isActive,
                'affected_rows' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error in bulk user status update', [
                'user_ids' => $userIds,
                'is_active' => $isActive,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get user activity timeline.
     */
    public function getUserActivityTimeline(int $userId, int $limit = 50)
    {
        try {
            $user = $this->findUser($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }

            return $this->userRepository->getUserActivityTimeline($userId, $limit);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting user activity timeline', [
                'user_id' => $userId,
                'limit' => $limit,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Check if user can be safely deleted.
     */
    public function canUserBeDeleted(int $userId): bool
    {
        try {
            $user = $this->findUser($userId);
            if (!$user) {
                return false;
            }

            return $user->canBeDeleted();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error checking if user can be deleted', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get comprehensive user dashboard data.
     */
    public function getUserDashboardData(int $userId)
    {
        try {
            $user = $this->findUser($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }

            return [
                'user' => $user->load('roles'),
                'performance_stats' => $user->getPerformanceStats(),
                'activity_summary' => $user->getActivitySummary(),
                'recent_sales' => $user->getRecentSalesTransactions(5),
                'recent_purchases' => $user->getRecentPurchaseOrders(5),
                'monthly_summary' => $user->getMonthlySalesSummary(),
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting user dashboard data', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get enhanced user statistics including transaction data.
     */
    public function getEnhancedUserStatistics()
    {
        try {
            $basicStats = $this->getUserStatistics();
            
            // Add transaction-related statistics
             $transactionStats = [
                 'users_with_sales' => \App\Models\User::whereHas('salesTransactions')
                     ->count(),
                 'users_with_purchases' => \App\Models\User::whereHas('purchaseOrders')
                     ->count(),
                 'top_cashiers' => \App\Models\User::withCount(['salesTransactions as sales_count'])
                     ->having('sales_count', '>', 0)
                     ->orderBy('sales_count', 'desc')
                     ->limit(5)
                     ->get(['id', 'name', 'email']),
                 'recent_activity_count' => $this->userRepository
                     ->getUsersWithRecentActivity(7)
                     ->count(),
             ];

            return array_merge($basicStats, $transactionStats);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting enhanced user statistics', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}