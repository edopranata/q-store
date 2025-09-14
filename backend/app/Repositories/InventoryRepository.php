<?php

namespace App\Repositories;

use App\Models\InventoryStock;
use App\Models\StockMovement;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InventoryRepository implements InventoryRepositoryInterface
{
    protected InventoryStock $model;

    public function __construct(InventoryStock $model)
    {
        $this->model = $model;
    }

    /**
     * Get all inventory stocks with optional filters.
     */
    public function getAllStocks(array $filters = [], array $with = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Get paginated inventory stocks.
     */
    public function getPaginatedStocks(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Get inventory stock by product and warehouse.
     */
    public function getStockByProductAndWarehouse(int $productId, int $warehouseId): ?InventoryStock
    {
        return $this->model->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
    }

    /**
     * Get stocks by warehouse.
     */
    public function getStocksByWarehouse(int $warehouseId, array $with = []): Collection
    {
        $query = $this->model->where('warehouse_id', $warehouseId);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    /**
     * Get stocks by product.
     */
    public function getStocksByProduct(int $productId, array $with = []): Collection
    {
        $query = $this->model->where('product_id', $productId);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    /**
     * Get low stock products.
     */
    public function getLowStockProducts(int $threshold = 10, array $with = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->whereRaw('(quantity - reserved_qty) <= ?', [$threshold])
            ->where('quantity', '>', 0)
            ->get();
    }

    /**
     * Get out of stock products.
     */
    public function getOutOfStockProducts(array $with = []): Collection
    {
        $query = $this->model->outOfStock();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    /**
     * Create or update inventory stock.
     */
    public function createOrUpdateStock(array $data): InventoryStock
    {
        return $this->model->updateOrCreate(
            [
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id']
            ],
            $data
        );
    }

    /**
     * Add stock quantity.
     */
    public function addStock(int $productId, int $warehouseId, float $quantity, ?float $purchasePrice = null): InventoryStock
    {
        $stock = $this->getStockByProductAndWarehouse($productId, $warehouseId);

        if (!$stock) {
            $stock = $this->createOrUpdateStock([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => 0,
                'reserved_qty' => 0,
                'average_cost' => $purchasePrice ?? 0,
                'last_purchase_price' => $purchasePrice
            ]);
        }

        $stock->addStock($quantity, $purchasePrice);

        return $stock;
    }

    /**
     * Reduce stock quantity.
     */
    public function reduceStock(int $productId, int $warehouseId, float $quantity): InventoryStock
    {
        $stock = $this->getStockByProductAndWarehouse($productId, $warehouseId);

        if (!$stock) {
            throw new \Exception('Stock record not found');
        }

        $stock->reduceStock($quantity);

        return $stock;
    }

    /**
     * Reserve stock quantity.
     */
    public function reserveStock(int $productId, int $warehouseId, float $quantity): InventoryStock
    {
        $stock = $this->getStockByProductAndWarehouse($productId, $warehouseId);

        if (!$stock) {
            throw new \Exception('Stock record not found');
        }

        $stock->reserveStock($quantity);

        return $stock;
    }

    /**
     * Release reserved stock.
     */
    public function releaseReservedStock(int $productId, int $warehouseId, float $quantity): InventoryStock
    {
        $stock = $this->getStockByProductAndWarehouse($productId, $warehouseId);

        if (!$stock) {
            throw new \Exception('Stock record not found');
        }

        $stock->releaseReservedStock($quantity);

        return $stock;
    }

    /**
     * Get total stock across all warehouses for a product.
     */
    public function getTotalStockByProduct(int $productId): float
    {
        return $this->model->where('product_id', $productId)
            ->sum('quantity');
    }

    /**
     * Get available stock across all warehouses for a product.
     */
    public function getAvailableStockByProduct(int $productId): float
    {
        return $this->model->where('product_id', $productId)
            ->selectRaw('SUM(quantity - reserved_qty) as available')
            ->value('available') ?? 0;
    }

    /**
     * Check if sufficient stock is available.
     */
    public function hasSufficientStock(int $productId, int $warehouseId, float $requiredQuantity): bool
    {
        $stock = $this->getStockByProductAndWarehouse($productId, $warehouseId);

        if (!$stock) {
            return false;
        }

        return $stock->available_qty >= $requiredQuantity;
    }

    /**
     * Get stock movements for audit trail.
     */
    public function getStockMovements(array $filters = []): Collection
    {
        $query = StockMovement::with(['product', 'batch', 'fromWarehouse', 'toWarehouse', 'unit', 'createdBy']);

        // Apply filters
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['warehouse_id'])) {
            $query->byWarehouse($filters['warehouse_id']);
        }

        if (isset($filters['movement_type'])) {
            $query->byMovementType($filters['movement_type']);
        }

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }

        if (isset($filters['reference_type'])) {
            $query->where('reference_type', $filters['reference_type']);
        }

        if (isset($filters['reference_id'])) {
            $query->where('reference_id', $filters['reference_id']);
        }

        return $query->orderBy('movement_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Bulk update stocks.
     */
    public function bulkUpdateStocks(array $stockUpdates): bool
    {
        try {
            DB::beginTransaction();

            foreach ($stockUpdates as $update) {
                $this->createOrUpdateStock($update);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get inventory valuation.
     */
    public function getInventoryValuation(array $filters = []): array
    {
        $query = $this->model->newQuery()
            ->selectRaw('SUM(quantity * average_cost) as total_value')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->selectRaw('COUNT(*) as total_items');

        $this->applyFilters($query, $filters);

        $result = $query->first();

        return [
            'total_value' => $result->total_value ?? 0,
            'total_quantity' => $result->total_quantity ?? 0,
            'total_items' => $result->total_items ?? 0,
            'average_cost_per_unit' => $result->total_quantity > 0 
                ? ($result->total_value / $result->total_quantity) 
                : 0
        ];
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (isset($filters['low_stock'])) {
            $threshold = $filters['low_stock_threshold'] ?? 10;
            $query->whereRaw('(quantity - reserved_qty) <= ?', [$threshold]);
        }

        if (isset($filters['out_of_stock']) && $filters['out_of_stock']) {
            $query->where('quantity', '<=', 0);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }
    }
}