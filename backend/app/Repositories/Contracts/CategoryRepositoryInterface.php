<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    /**
     * Get all categories with optional filters.
     */
    public function getAll(array $filters = [], array $with = []): Collection;

    /**
     * Get paginated categories with optional filters.
     */
    public function getPaginated(array $filters = [], array $with = [], array $withCount = [], int $perPage = 15, string $sortBy = 'name', string $sortOrder = 'asc'): LengthAwarePaginator;

    /**
     * Find category by ID.
     */
    public function findById(int $id, array $with = []): ?Category;

    /**
     * Find category by name.
     */
    public function findByName(string $name, array $with = []): ?Category;

    /**
     * Create a new category.
     */
    public function create(array $data): Category;

    /**
     * Update an existing category.
     */
    public function update(Category $category, array $data): Category;

    /**
     * Delete a category.
     */
    public function delete(Category $category): bool;

    /**
     * Get categories by status.
     */
    public function getByStatus(string $status = 'active', array $with = []): Collection;

    /**
     * Get active categories.
     */
    public function getActiveCategories(array $with = []): Collection;

    /**
     * Check if category has products.
     */
    public function hasProducts(int $categoryId): bool;

    /**
     * Search categories by name or description.
     */
    public function search(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if category name exists.
     */
    public function nameExists(string $name, ?int $excludeId = null): bool;
    
    /**
     * Get category statistics.
     */
    public function getStatistics(): array;
}