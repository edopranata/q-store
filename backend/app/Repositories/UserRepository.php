<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Role;
use Carbon\Carbon;

class UserRepository implements UserRepositoryInterface
{
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Get all users with optional filters.
     */
    public function getAll(array $filters = [], array $with = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Get paginated users with optional filters.
     */
    public function getPaginated(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Find user by ID.
     */
    public function findById(int $id, array $with = []): ?User
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email, array $with = []): ?User
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('email', $email)->first();
    }

    /**
     * Find user by username.
     */
    public function findByUsername(string $username, array $with = []): ?User
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('username', $username)->first();
    }

    /**
     * Create a new user.
     */
    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing user.
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    /**
     * Delete a user.
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Assign role to user.
     */
    public function assignRole(User $user, string|Role $role): User
    {
        $user->assignRole($role);
        return $user->fresh(['roles']);
    }

    /**
     * Remove role from user.
     */
    public function removeRole(User $user, string|Role $role): User
    {
        $user->removeRole($role);
        return $user->fresh(['roles']);
    }

    /**
     * Sync user roles.
     */
    public function syncRoles(User $user, array $roles): User
    {
        $user->syncRoles($roles);
        return $user->fresh(['roles']);
    }

    /**
     * Get users by role.
     */
    public function getUsersByRole(string $roleName, array $with = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->role($roleName)->get();
    }

    /**
     * Get active users.
     */
    public function getActiveUsers(array $with = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('is_active', true)->get();
    }

    /**
     * Get inactive users.
     */
    public function getInactiveUsers(array $with = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('is_active', false)->get();
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus(User $user): User
    {
        $user->update(['is_active' => !$user->is_active]);
        return $user->fresh();
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(User $user): User
    {
        $user->update(['last_login' => Carbon::now()]);
        return $user->fresh();
    }

    /**
     * Search users by name, email, or username.
     */
    public function search(string $query, array $with = []): Collection
    {
        $queryBuilder = $this->model->newQuery();

        if (!empty($with)) {
            $queryBuilder->with($with);
        }

        return $queryBuilder->where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('full_name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('username', 'like', "%{$query}%");
        })->get();
    }

    /**
     * Get user statistics.
     */
    public function getStatistics(): array
    {
        $total = $this->model->count();
        $active = $this->model->where('is_active', true)->count();
        $inactive = $this->model->where('is_active', false)->count();
        $recentLogins = $this->model->whereNotNull('last_login')
            ->where('last_login', '>=', Carbon::now()->subDays(30))
            ->count();

        return [
            'total_users' => $total,
            'active_users' => $active,
            'inactive_users' => $inactive,
            'recent_logins' => $recentLogins,
            'users_by_role' => $this->getUserCountByRole()
        ];
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        // Filter by status
        if (isset($filters['status']) && $filters['status'] !== null && $filters['status'] !== '') {
            if (is_bool($filters['status'])) {
                $query->where('is_active', $filters['status']);
            } else {
                $query->where('is_active', $filters['status'] === 'true' || $filters['status'] === '1' || $filters['status'] === 'active');
            }
        }

        // Filter by role
        if (isset($filters['role']) && $filters['role'] !== null && $filters['role'] !== '') {
            $roleFilter = $filters['role'];
            
            // Handle both role ID and role name
            if (is_numeric($roleFilter)) {
                // Filter by role ID
                $query->whereHas('roles', function ($q) use ($roleFilter) {
                    $q->where('roles.id', $roleFilter);
                });
            } else {
                // Filter by role name
                $query->whereHas('roles', function ($q) use ($roleFilter) {
                    $q->where('roles.name', $roleFilter);
                });
            }
        }

        // Search filter
        if (isset($filters['search']) && $filters['search'] !== null && $filters['search'] !== '') {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Date range filters
        if (isset($filters['created_from']) && $filters['created_from'] !== null) {
            $query->where('created_at', '>=', Carbon::parse($filters['created_from'])->startOfDay());
        }

        if (isset($filters['created_to']) && $filters['created_to'] !== null) {
            $query->where('created_at', '<=', Carbon::parse($filters['created_to'])->endOfDay());
        }

        if (isset($filters['last_login_from']) && $filters['last_login_from'] !== null) {
            $query->where('last_login', '>=', Carbon::parse($filters['last_login_from'])->startOfDay());
        }

        if (isset($filters['last_login_to']) && $filters['last_login_to'] !== null) {
            $query->where('last_login', '<=', Carbon::parse($filters['last_login_to'])->endOfDay());
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        // Validate sort column to prevent SQL injection
        $allowedSortColumns = ['id', 'name', 'email', 'username', 'full_name', 'is_active', 'created_at', 'updated_at', 'last_login'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, strtolower($sortOrder) === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Get user count by role.
     */
    protected function getUserCountByRole(): array
    {
        $roles = Role::all();
        $result = [];

        foreach ($roles as $role) {
            $result[$role->name] = $this->model->whereHas('roles', function ($q) use ($role) {
                $q->where('roles.id', $role->id);
            })->count();
        }

        return $result;
    }

    /**
     * Get users with their transaction statistics.
     */
    public function getUsersWithTransactionStats(array $filters = [])
    {
        $query = $this->model
            ->with(['roles'])
            ->leftJoin('sales_transactions', 'users.id', '=', 'sales_transactions.cashier_id')
            ->leftJoin('purchase_orders', 'users.id', '=', 'purchase_orders.created_by')
            ->selectRaw('
                users.*,
                COUNT(DISTINCT sales_transactions.id) as sales_count,
                COUNT(DISTINCT purchase_orders.id) as purchase_orders_count,
                COALESCE(SUM(CASE WHEN sales_transactions.status = "paid" THEN sales_transactions.grand_total ELSE 0 END), 0) as total_sales_amount
            ')
            ->groupBy('users.id');

        // Apply filters
        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('users.is_active', $filters['status'] === 'active');
        }

        if (!empty($filters['date_from'])) {
            $query->where('users.created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('users.created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('users.created_at', 'desc')->get();
    }

    /**
     * Get user performance metrics.
     */
    public function getUserPerformanceMetrics(int $userId, array $dateRange = [])
    {
        $user = $this->findById($userId);
        if (!$user) {
            return null;
        }

        $salesQuery = $user->salesTransactions();
        $purchaseQuery = $user->purchaseOrders();

        if (!empty($dateRange['start'])) {
            $salesQuery->where('transaction_date', '>=', $dateRange['start']);
            $purchaseQuery->where('po_date', '>=', $dateRange['start']);
        }

        if (!empty($dateRange['end'])) {
            $salesQuery->where('transaction_date', '<=', $dateRange['end']);
            $purchaseQuery->where('po_date', '<=', $dateRange['end']);
        }

        return [
            'user' => $user,
            'sales_stats' => [
                'total_transactions' => $salesQuery->count(),
                'total_amount' => $salesQuery->where('status', 'paid')->sum('grand_total'),
                'avg_amount' => $salesQuery->where('status', 'paid')->avg('grand_total'),
                'pending_transactions' => $salesQuery->whereIn('status', ['draft', 'pending'])->count(),
            ],
            'purchase_stats' => [
                'total_orders' => $purchaseQuery->count(),
                'total_amount' => $purchaseQuery->sum('grand_total'),
                'pending_orders' => $purchaseQuery->whereIn('status', ['draft', 'ordered', 'partial'])->count(),
            ],
        ];
    }

    /**
     * Get users who can be safely deleted (no related transactions).
     */
    public function getUsersForDeletion()
    {
        return $this->model
            ->leftJoin('sales_transactions', 'users.id', '=', 'sales_transactions.cashier_id')
            ->leftJoin('purchase_orders', 'users.id', '=', 'purchase_orders.created_by')
            ->leftJoin('stock_movements', 'users.id', '=', 'stock_movements.created_by')
            ->whereNull('sales_transactions.id')
            ->whereNull('purchase_orders.id')
            ->whereNull('stock_movements.id')
            ->select('users.*')
            ->distinct()
            ->get();
    }

    /**
     * Get users with recent activity.
     */
    public function getUsersWithRecentActivity(int $days = 30)
    {
        $cutoffDate = now()->subDays($days);
        
        return $this->model
            ->with(['roles'])
            ->where(function ($query) use ($cutoffDate) {
                $query->whereHas('salesTransactions', function ($q) use ($cutoffDate) {
                    $q->where('created_at', '>=', $cutoffDate);
                })
                ->orWhereHas('purchaseOrders', function ($q) use ($cutoffDate) {
                    $q->where('created_at', '>=', $cutoffDate);
                })
                ->orWhereHas('stockMovements', function ($q) use ($cutoffDate) {
                    $q->where('created_at', '>=', $cutoffDate);
                })
                ->orWhere('last_login', '>=', $cutoffDate);
            })
            ->orderBy('last_login', 'desc')
            ->get();
    }

    /**
     * Bulk update user status.
     */
    public function bulkUpdateStatus(array $userIds, bool $isActive)
    {
        return $this->model
            ->whereIn('id', $userIds)
            ->update(['is_active' => $isActive]);
    }

    /**
     * Get user activity timeline.
     */
    public function getUserActivityTimeline(int $userId, int $limit = 50)
    {
        $user = $this->findById($userId);
        if (!$user) {
            return collect();
        }

        $activities = collect();

        // Sales transactions
        $salesActivities = $user->salesTransactions()
            ->select('id', 'transaction_number as reference', 'transaction_date as date', 'grand_total as amount', 'status')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'sales_transaction',
                    'reference' => $item->reference,
                    'date' => $item->date,
                    'amount' => $item->amount,
                    'status' => $item->status,
                ];
            });

        // Purchase orders
        $purchaseActivities = $user->purchaseOrders()
            ->select('id', 'po_number as reference', 'po_date as date', 'grand_total as amount', 'status')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'purchase_order',
                    'reference' => $item->reference,
                    'date' => $item->date,
                    'amount' => $item->amount,
                    'status' => $item->status,
                ];
            });

        return $activities
            ->merge($salesActivities)
            ->merge($purchaseActivities)
            ->sortByDesc('date')
            ->take($limit)
            ->values();
    }
}