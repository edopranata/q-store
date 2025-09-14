<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Role extends SpatieRole
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'display_name',
        'users_count',
        'permissions_count',
    ];

    /**
     * Get all permissions assigned to this role.
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            config('permission.table_names.role_has_permissions'),
            'role_id',
            'permission_id'
        );
    }

    /**
     * Get all users that have this role.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->morphedByMany(
            getModelForGuard($this->attributes['guard_name'] ?? config('auth.defaults.guard')),
            'model',
            config('permission.table_names.model_has_roles'),
            'role_id',
            config('permission.column_names.model_morph_key')
        );
    }

    /**
     * Scope to filter roles by guard name.
     *
     * @param Builder $query
     * @param string $guardName
     * @return Builder
     */
    public function scopeForGuard(Builder $query, string $guardName): Builder
    {
        return $query->where('guard_name', $guardName);
    }

    /**
     * Scope to search roles by name or description.
     *
     * @param Builder $query
     * @param string $search
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to include users count.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithUsersCount(Builder $query): Builder
    {
        return $query->withCount('users');
    }

    /**
     * Scope to include permissions count.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithPermissionsCount(Builder $query): Builder
    {
        return $query->withCount('permissions');
    }

    /**
     * Scope to get roles with specific permission.
     *
     * @param Builder $query
     * @param string $permission
     * @return Builder
     */
    public function scopeWithPermission(Builder $query, string $permission): Builder
    {
        return $query->whereHas('permissions', function ($q) use ($permission) {
            $q->where('name', $permission);
        });
    }

    /**
     * Get role display name (description or formatted name).
     *
     * @return Attribute
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->description ?: ucwords(str_replace(['-', '_'], ' ', $this->name))
        );
    }

    /**
     * Get users count attribute.
     *
     * @return Attribute
     */
    protected function usersCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->users_count ?? $this->users()->count()
        );
    }

    /**
     * Get permissions count attribute.
     *
     * @return Attribute
     */
    protected function permissionsCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->permissions_count ?? $this->permissions()->count()
        );
    }

    /**
     * Check if role exists by name.
     *
     * @param string $name
     * @param string|null $guardName
     * @return bool
     */
    public static function exists(string $name, ?string $guardName = null): bool
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        
        return static::where('name', $name)
            ->where('guard_name', $guardName)
            ->exists();
    }

    /**
     * Create role if it doesn't exist.
     *
     * @param string $name
     * @param string|null $description
     * @param string|null $guardName
     * @return static
     */
    public static function createIfNotExists(string $name, ?string $description = null, ?string $guardName = null): static
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        
        return static::firstOrCreate(
            ['name' => $name, 'guard_name' => $guardName],
            ['description' => $description]
        );
    }

    /**
     * Assign multiple permissions to this role.
     *
     * @param array|Collection $permissions
     * @return $this
     */
    public function assignPermissions($permissions): static
    {
        $permissions = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::where('name', $permission)->first();
            }
            return $permission;
        })->filter();

        $this->syncPermissions($permissions);
        
        return $this;
    }

    /**
     * Remove multiple permissions from this role.
     *
     * @param array|Collection $permissions
     * @return $this
     */
    public function removePermissions($permissions): static
    {
        $permissions = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::where('name', $permission)->first();
            }
            return $permission;
        })->filter();

        foreach ($permissions as $permission) {
            $this->revokePermissionTo($permission);
        }
        
        return $this;
    }

    /**
     * Get permissions grouped by resource.
     *
     * @return Collection
     */
    public function getGroupedPermissions(): Collection
    {
        return $this->permissions->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);
            return $parts[1] ?? 'other';
        });
    }

    /**
     * Check if role has any permission for a specific resource.
     *
     * @param string $resource
     * @return bool
     */
    public function hasResourceAccess(string $resource): bool
    {
        return $this->permissions()->where('name', 'like', "api.{$resource}.%")->exists();
    }

    /**
     * Get all permissions for a specific resource.
     *
     * @param string $resource
     * @return Collection
     */
    public function getResourcePermissions(string $resource): Collection
    {
        return $this->permissions()->where('name', 'like', "api.{$resource}.%")->get();
    }

    /**
     * Check if this is a super admin role.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return strtolower($this->name) === 'super admin';
    }

    /**
     * Check if this is an admin role.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return in_array(strtolower($this->name), ['super admin', 'admin']);
    }

    /**
     * Get role hierarchy level (lower number = higher privilege).
     *
     * @return int
     */
    public function getHierarchyLevel(): int
    {
        return match($this->name) {
            'Super Admin' => 1,
            'Admin' => 2,
            'Manager' => 3,
            'Cashier' => 4,
            'Staff' => 5,
            default => 999
        };
    }

    /**
     * Check if this role has higher privilege than another role.
     *
     * @param Role $role
     * @return bool
     */
    public function hasHigherPrivilegeThan(Role $role): bool
    {
        return $this->getHierarchyLevel() < $role->getHierarchyLevel();
    }

    /**
     * Get default roles for the system.
     *
     * @return array
     */
    public static function getDefaultRoles(): array
    {
        return [
            [
                'name' => 'Super Admin',
                'description' => 'Administrator dengan akses penuh ke seluruh sistem',
            ],
            [
                'name' => 'Admin',
                'description' => 'Administrator dengan akses terbatas untuk mengelola sistem',
            ],
            [
                'name' => 'Manager',
                'description' => 'Manajer dengan akses untuk mengelola operasional dan laporan',
            ],
            [
                'name' => 'Cashier',
                'description' => 'Kasir dengan akses terbatas untuk transaksi penjualan',
            ],
        ];
    }
}