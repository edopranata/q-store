<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository implements ProductRepositoryInterface
{
    protected Product $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    /**
     * Get all products with optional filters.
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
     * Get paginated products with optional filters.
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
     * Find product by ID.
     */
    public function findById(int $id, array $with = []): ?Product
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Find product by SKU.
     */
    public function findBySku(string $sku, array $with = []): ?Product
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('sku', $sku)->first();
    }

    /**
     * Find product by barcode.
     */
    public function findByBarcode(string $barcode, array $with = []): ?Product
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('barcode', $barcode)->first();
    }

    /**
     * Create a new product.
     */
    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing product.
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    /**
     * Delete a product.
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Get products by category.
     */
    public function getByCategory(int $categoryId, array $with = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('category_id', $categoryId)->get();
    }

    /**
     * Search products by name or SKU.
     */
    public function search(string $query, array $with = [], int $limit = 10): Collection
    {
        $queryBuilder = $this->model->newQuery();

        if (!empty($with)) {
            $queryBuilder->with($with);
        }

        return $queryBuilder->where(function (Builder $q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('sku', 'LIKE', "%{$query}%")
              ->orWhere('barcode', 'LIKE', "%{$query}%");
        })
        ->limit($limit)
        ->get();
    }

    /**
     * Get products with low stock.
     */
    public function getLowStockProducts(int $threshold = 10, array $with = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->whereHas('inventoryStocks', function (Builder $q) use ($threshold) {
            $q->havingRaw('SUM(quantity) <= ?', [$threshold]);
        })
        ->with(['inventoryStocks' => function ($q) {
            $q->selectRaw('product_id, SUM(quantity) as total_stock')
              ->groupBy('product_id');
        }])
        ->get();
    }

    /**
     * Get active products.
     */
    public function getActive(array $with = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->where('status', 'active')->get();
    }

    /**
     * Get products with their current prices.
     */
    public function getWithCurrentPrices(array $filters = [], string $priceType = 'selling'): Collection
    {
        $query = $this->model->newQuery();

        $query->with([
            'productPrices' => function ($q) use ($priceType) {
                $q->with('unit')
                  ->byPriceType($priceType)
                  ->active()
                  ->effective()
                  ->orderBy('effective_date', 'desc');
            }
        ]);

        $this->applyFilters($query, $filters);

        return $query->get()->map(function ($product) {
            // Group prices by unit and get the most recent for each
            $currentPrices = $product->productPrices
                ->groupBy('unit_id')
                ->map(function ($prices) {
                    return $prices->first();
                })
                ->values();
            
            $product->current_prices = $currentPrices;
            unset($product->productPrices);
            
            return $product;
        });
    }

    /**
     * Get products with their units.
     */
    public function getWithUnits(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        $query->with(['productUnits.unit']);

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Check if SKU exists (excluding specific product ID).
     */
    public function skuExists(string $sku, ?int $excludeId = null): bool
    {
        $query = $this->model->newQuery()->where('sku', $sku);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if barcode exists (excluding specific product ID).
     */
    public function barcodeExists(string $barcode, ?int $excludeId = null): bool
    {
        $query = $this->model->newQuery()->where('barcode', $barcode);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get product statistics.
     */
    public function getStatistics(): array
    {
        $totalProducts = $this->model->count();
        $activeProducts = $this->model->where('status', 'active')->count();
        $inactiveProducts = $totalProducts - $activeProducts;
        
        $productsByCategory = $this->model
            ->selectRaw('category_id, COUNT(*) as count')
            ->with('category:id,name')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category->name ?? 'Uncategorized',
                    'count' => $item->count
                ];
            });

        return [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'inactive_products' => $inactiveProducts,
            'products_by_category' => $productsByCategory,
            'products_with_units' => $this->model->has('productUnits')->count(),
            'products_with_prices' => $this->model->has('productPrices')->count(),
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

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('barcode', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['has_units'])) {
            if ($filters['has_units']) {
                $query->has('productUnits');
            } else {
                $query->doesntHave('productUnits');
            }
        }

        if (isset($filters['has_prices'])) {
            if ($filters['has_prices']) {
                $query->has('productPrices');
            } else {
                $query->doesntHave('productPrices');
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