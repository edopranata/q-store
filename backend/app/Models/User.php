<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;
use App\Models\SalesTransaction;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Role;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, HasPermissions;

    /**
     * The attributes that are mass assignable.
     * Updated sesuai spesifikasi DATABASE_SCHEMA.md
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'full_name',
        'is_active',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive users.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get the user's full display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ?: $this->name;
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if user has specific role.
     */
    public function hasRoleName(string $roleName): bool
    {
        return $this->hasRole($roleName);
    }

    /**
     * Get user's role names.
     */
    public function getRoleNamesAttribute(): array
    {
        return $this->roles->pluck('name')->toArray();
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Admin') || $this->isSuperAdmin();
    }

    /**
     * Check if user is manager.
     */
    public function isManager(): bool
    {
        return $this->hasRole('Manager');
    }

    /**
     * Check if user is cashier.
     */
    public function isCashier(): bool
    {
        return $this->hasRole('Cashier');
    }

    /**
     * Get user's highest role based on hierarchy.
     */
    public function getHighestRole(): ?Role
    {
        return $this->roles->sortBy(function ($role) {
            return $role->getHierarchyLevel();
        })->first();
    }

    /**
     * Check if user has permission (via role or direct assignment).
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        // Super admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        return parent::hasPermissionTo($permission, $guardName);
    }

    /**
     * Get all permissions for this user (via roles and direct assignment).
     */
    public function getAllPermissions(): Collection
    {
        // Super admin has all permissions
        if ($this->isSuperAdmin()) {
            return \Spatie\Permission\Models\Permission::all();
        }

        // Use the trait's method directly
        return $this->permissions()->get()->merge($this->getPermissionsViaRoles());
    }

    /**
     * Get permissions grouped by resource.
     */
    public function getGroupedPermissions(): Collection
    {
        return $this->getAllPermissions()->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);
            return $parts[1] ?? 'other';
        });
    }

    /**
     * Check if user has access to specific resource.
     */
    public function hasResourceAccess(string $resource): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->getAllPermissions()
            ->contains(function ($permission) use ($resource) {
                return str_starts_with($permission->name, "api.{$resource}.");
            });
    }

    /**
     * Assign role to user with validation.
     */
    public function assignRoleWithValidation(string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            return false;
        }

        $this->assignRole($role);
        return true;
    }

    /**
     * Sync user roles.
     */
    public function syncRoles($roles): static
    {
        $roleIds = collect($roles)->map(function ($role) {
            if (is_string($role)) {
                $roleModel = Role::where('name', $role)->first();
                return $roleModel ? $roleModel->id : null;
            }
            if (is_object($role) && isset($role->id)) {
                return $role->id;
            }
            return $role; // Assume it's already an ID
        })->filter()->toArray();

        $this->roles()->sync($roleIds);
        return $this;
    }

    /**
     * Get user's role hierarchy level.
     */
    public function getRoleHierarchyLevel(): int
    {
        $highestRole = $this->getHighestRole();
        return $highestRole ? $highestRole->getHierarchyLevel() : 999;
    }

    /**
     * Check if user can manage another user based on role hierarchy.
     */
    public function canManageUser(User $user): bool
    {
        // Super admin can manage everyone
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Can't manage users with same or higher privilege
        return $this->getRoleHierarchyLevel() < $user->getRoleHierarchyLevel();
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login' => now()]);
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Sales transactions created by this user (as cashier).
     */
    public function salesTransactions()
    {
        return $this->hasMany(SalesTransaction::class, 'cashier_id');
    }

    /**
     * Purchase orders created by this user.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }

    /**
     * Stock movements created by this user.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'created_by');
    }

    /**
     * Get sales transactions count for this user.
     */
    public function getSalesTransactionsCountAttribute(): int
    {
        return $this->salesTransactions()->count();
    }

    /**
     * Get purchase orders count for this user.
     */
    public function getPurchaseOrdersCountAttribute(): int
    {
        return $this->purchaseOrders()->count();
    }

    /**
     * Get total sales amount for this user.
     */
    public function getTotalSalesAmountAttribute(): float
    {
        return $this->salesTransactions()
            ->where('status', 'paid')
            ->sum('grand_total');
    }

    /**
     * Get recent sales transactions for this user.
     */
    public function getRecentSalesTransactions(int $limit = 10)
    {
        return $this->salesTransactions()
            ->with(['customer', 'warehouse'])
            ->orderBy('transaction_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent purchase orders for this user.
     */
    public function getRecentPurchaseOrders(int $limit = 10)
    {
        return $this->purchaseOrders()
            ->with(['supplier', 'warehouse'])
            ->orderBy('po_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user performance statistics.
     */
    public function getPerformanceStats()
    {
        return [
            'total_sales_transactions' => $this->salesTransactions()->count(),
            'total_sales_amount' => $this->getTotalSalesAmountAttribute(),
            'total_purchase_orders' => $this->purchaseOrders()->count(),
            'avg_transaction_amount' => $this->salesTransactions()
                ->where('status', 'paid')
                ->avg('grand_total'),
            'last_transaction_date' => $this->salesTransactions()
                ->latest('transaction_date')
                ->value('transaction_date'),
        ];
    }

    /**
     * Get sales transactions for a specific date range.
     */
    public function getSalesTransactionsByDateRange($startDate, $endDate)
    {
        return $this->salesTransactions()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with(['customer', 'warehouse', 'salesTransactionItems.product'])
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * Get monthly sales summary for this user.
     */
    public function getMonthlySalesSummary($year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        return $this->salesTransactions()
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->where('status', 'paid')
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(grand_total) as total_amount,
                AVG(grand_total) as avg_amount,
                DATE(transaction_date) as date
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Check if user can be deleted (has no related transactions).
     */
    public function canBeDeleted(): bool
    {
        return $this->salesTransactions()->count() === 0 &&
               $this->purchaseOrders()->count() === 0 &&
               $this->stockMovements()->count() === 0;
    }

    /**
     * Get user activity summary.
     */
    public function getActivitySummary()
    {
        return [
            'sales_transactions_count' => $this->salesTransactions()->count(),
            'purchase_orders_count' => $this->purchaseOrders()->count(),
            'stock_movements_count' => $this->stockMovements()->count(),
            'last_activity' => collect([
                $this->salesTransactions()->latest('created_at')->value('created_at'),
                $this->purchaseOrders()->latest('created_at')->value('created_at'),
                $this->stockMovements()->latest('created_at')->value('created_at'),
            ])->filter()->max(),
        ];
    }
}
