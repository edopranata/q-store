<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get all categories with optional filters.
     */
    public function getAll(array $filters = [], array $with = []): Collection
    {
        return $this->categoryRepository->getAll($filters, $with);
    }

    /**
     * Get paginated categories with optional filters.
     */
    public function getPaginated(array $filters = [], array $with = [], array $withCount = [], int $perPage = 15, string $sortBy = 'id', string $sortOrder = 'asc'): LengthAwarePaginator
    {
        return $this->categoryRepository->getPaginated($filters, $with, $withCount, $perPage, $sortBy, $sortOrder);
    }

    /**
     * Find category by ID.
     */
    public function find(int $id, array $with = []): ?Category
    {
        return $this->categoryRepository->findById($id, $with);
    }

    /**
     * Find category by name.
     */
    public function findByName(string $name, array $with = []): ?Category
    {
        return $this->categoryRepository->findByName($name, $with);
    }

    /**
     * Create a new category with validation.
     */
    public function createCategory(array $data): Category
    {
        // Validate unique constraints
        $this->validateUniqueFields($data);

        DB::beginTransaction();
        try {
            $category = $this->categoryRepository->create($data);
            
            DB::commit();
            return $category->load(['products']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing category.
     */
    public function updateCategory(Category $category, array $data): Category
    {
        // Validate unique constraints (excluding current category)
        $this->validateUniqueFields($data, $category->id);

        DB::beginTransaction();
        try {
            $updatedCategory = $this->categoryRepository->update($category, $data);
            
            DB::commit();
            return $updatedCategory->load(['products']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(Category $category): bool
    {
        // Check if category has products
        if ($this->hasProducts($category)) {
            throw new \InvalidArgumentException('Cannot delete category with existing products. Please move or delete products first.');
        }

        // Additional validation can be added here if needed

        DB::beginTransaction();
        try {
            $result = $this->categoryRepository->delete($category);
            
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get categories by status.
     */
    public function getByStatus(string $status = 'active', array $with = []): Collection
    {
        return $this->categoryRepository->getByStatus($status, $with);
    }

    /**
     * Get active categories.
     */
    public function getActiveCategories(array $with = []): Collection
    {
        return $this->categoryRepository->getActiveCategories($with);
    }

    /**
     * Search categories.
     */
    public function search(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->categoryRepository->search($query, $with, $perPage);
    }

    /**
     * Get category statistics.
     */
    public function getStatistics(): array
    {
        return $this->categoryRepository->getStatistics();
    }

    /**
     * Validate unique fields.
     */
    protected function validateUniqueFields(array $data, ?int $excludeId = null): void
    {
        $errors = [];

        // Check name uniqueness
        if (isset($data['name'])) {
            if ($this->categoryRepository->nameExists($data['name'], $excludeId)) {
                $errors['name'] = ['The category name has already been taken.'];
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }



    /**
     * Check if category has products.
     */
    protected function hasProducts(Category $category): bool
    {
        return $category->products()->exists();
    }

    /**
     * Toggle category status.
     */
    public function toggleStatus(Category $category): Category
    {
        $newStatus = $category->status === 'active' ? 'inactive' : 'active';
        return $this->updateCategory($category, ['status' => $newStatus]);
    }

    /**
     * Activate category.
     */
    public function activateCategory(Category $category): Category
    {
        return $this->updateCategory($category, ['status' => 'active']);
    }

    /**
     * Deactivate category.
     */
    public function deactivateCategory(Category $category): Category
    {
        return $this->updateCategory($category, ['status' => 'inactive']);
    }
}