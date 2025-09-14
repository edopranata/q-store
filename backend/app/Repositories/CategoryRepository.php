<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CategoryRepository implements CategoryRepositoryInterface
{
    protected Category $model;

    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    /**
     * Get all categories with optional filters.
     */
    public function getAll(array $filters = [], array $with = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        // Add products count
        $query->withCount('products');

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Get paginated categories with optional filters.
     */
    public function getPaginated(array $filters = [], array $with = [], array $withCount = [], int $perPage = 15, string $sortBy = 'name', string $sortOrder = 'asc'): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        if(!empty($withCount)){
            $query->withCount($withCount);
        }

        $this->applyFilters($query, $filters);

        // Validate and apply sorting
        $allowedSortFields = [
            'id', 'name', 'description',
            'created_at', 'updated_at', 'products_count'
        ];
        
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            // Default sorting if invalid field
            $query->orderBy('name', 'asc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Find category by ID.
     */
    public function findById(int $id, array $with = []): ?Category
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Find category by name.
     */
    public function findByName(string $name, array $with = []): ?Category
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('name', $name)->first();
    }

    /**
     * Create a new category.
     */
    public function create(array $data): Category
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing category.
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category->fresh();
    }

    /**
     * Delete a category.
     */
    public function delete(Category $category): bool
    {
        return $category->delete();
    }

    /**
     * Get categories by status.
     */
    public function getByStatus(string $status = 'active', array $with = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('status', $status)->get();
    }

    /**
     * Get active categories.
     */
    public function getActiveCategories(array $with = []): Collection
    {
        return $this->getByStatus('active', $with);
    }

    /**
     * Check if category has products.
     */
    public function hasProducts(int $categoryId): bool
    {
        return $this->model->find($categoryId)?->products()->exists() ?? false;
    }

    /**
     * Search categories by name or description.
     */
    public function search(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        $queryBuilder = $this->model->newQuery();

        if (!empty($with)) {
            $queryBuilder->with($with);
        }

        return $queryBuilder->where(function (Builder $q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('description', 'LIKE', "%{$query}%");
        })
        ->paginate($perPage);
    }

    /**
     * Check if name exists (excluding specific category ID).
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $query = $this->model->newQuery()->where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }



    /**
     * Get category statistics.
     */
    public function getStatistics(): array
    {
        $totalCategories = $this->model->count();
        $activeCategories = $this->model->where('status', 'active')->count();
        $inactiveCategories = $this->model->where('status', 'inactive')->count();
        
        $categoriesWithProducts = $this->model->has('products')->count();
        $categoriesWithoutProducts = $totalCategories - $categoriesWithProducts;

        return [
            'total_categories' => $totalCategories,
            'active_categories' => $activeCategories,
            'inactive_categories' => $inactiveCategories,
            'categories_with_products' => $categoriesWithProducts,
            'categories_without_products' => $categoriesWithoutProducts,
        ];
    }

    /**
     * Apply filters to the query.
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['has_products'])) {
            if ($filters['has_products']) {
                $query->has('products');
            } else {
                $query->doesntHave('products');
            }
        }

        if (isset($filters['created_from'])) {
            $query->where('created_at', '>=', $filters['created_from']);
        }

        if (isset($filters['created_to'])) {
            $query->where('created_at', '<=', $filters['created_to']);
        }
    }
}