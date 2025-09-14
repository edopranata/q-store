<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Permission extends SpatiePermission
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
     * Get all roles that have this permission.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.role_has_permissions'),
            'permission_id',
            'role_id'
        );
    }

    /**
     * Get all users that have this permission directly.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->morphedByMany(
            getModelForGuard($this->attributes['guard_name'] ?? config('auth.defaults.guard')),
            'model',
            config('permission.table_names.model_has_permissions'),
            'permission_id',
            config('permission.column_names.model_morph_key')
        );
    }

    /**
     * Scope to filter permissions by guard name.
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
     * Scope to search permissions by name or description.
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
     * Scope to filter permissions by resource (e.g., 'users', 'products').
     *
     * @param Builder $query
     * @param string $resource
     * @return Builder
     */
    public function scopeForResource(Builder $query, string $resource): Builder
    {
        return $query->where('name', 'like', "api.{$resource}.%");
    }

    /**
     * Get permissions grouped by resource.
     *
     * @return Collection
     */
    public static function getGroupedByResource(): Collection
    {
        return static::all()->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);
            return $parts[1] ?? 'other';
        });
    }

    /**
     * Get all available resources from permissions.
     *
     * @return Collection
     */
    public static function getAvailableResources(): Collection
    {
        return static::all()
            ->map(function ($permission) {
                $parts = explode('.', $permission->name);
                return $parts[1] ?? null;
            })
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Check if permission exists by name.
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
     * Create permission if it doesn't exist.
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
     * Get permission display name (description or formatted name).
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->description ?: ucwords(str_replace(['.', '-', '_'], ' ', $this->name));
    }

    /**
     * Get the resource name from permission name.
     *
     * @return string|null
     */
    public function getResourceAttribute(): ?string
    {
        $parts = explode('.', $this->name);
        return $parts[1] ?? null;
    }

    /**
     * Get the action name from permission name.
     *
     * @return string|null
     */
    public function getActionAttribute(): ?string
    {
        $parts = explode('.', $this->name);
        return $parts[2] ?? null;
    }

    /**
     * Check if this is an API permission.
     *
     * @return bool
     */
    public function isApiPermission(): bool
    {
        return str_starts_with($this->name, 'api.');
    }

    /**
     * Check if this is a web permission.
     *
     * @return bool
     */
    public function isWebPermission(): bool
    {
        return str_starts_with($this->name, 'web.');
    }
}