<?php

namespace App\Services;

use App\Models\InventoryStock;
use App\Models\StockMovement;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    protected InventoryRepositoryInterface $inventoryRepository;

    public function __construct(InventoryRepositoryInterface $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * Get all inventory stocks with filters.
     */
    public function getAllStocks(array $filters = []): Collection
    {
        $with = ['product', 'warehouse'];
        return $this->inventoryRepository->getAllStocks($filters, $with);
    }

    /**
     * Get paginated inventory stocks.
     */
    public function getPaginatedStocks(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $with = ['product', 'warehouse'];
        return $this->inventoryRepository->getPaginatedStocks($filters, $with, $perPage);
    }

    /**
     * Get stock by product and warehouse.
     */
    public function getStockByProductAndWarehouse(int $productId, int $warehouseId): ?InventoryStock
    {
        return $this->inventoryRepository->getStockByProductAndWarehouse($productId, $warehouseId);
    }

    /**
     * Get stocks by warehouse.
     */
    public function getStocksByWarehouse(int $warehouseId): Collection
    {
        $with = ['product'];
        return $this->inventoryRepository->getStocksByWarehouse($warehouseId, $with);
    }

    /**
     * Get stocks by product across all warehouses.
     */
    public function getStocksByProduct(int $productId): Collection
    {
        $with = ['warehouse'];
        return $this->inventoryRepository->getStocksByProduct($productId, $with);
    }

    /**
     * Get low stock products.
     */
    public function getLowStockProducts(int $threshold = 10): Collection
    {
        $with = ['product', 'warehouse'];
        return $this->inventoryRepository->getLowStockProducts($threshold, $with);
    }

    /**
     * Get out of stock products.
     */
    public function getOutOfStockProducts(): Collection
    {
        $with = ['product', 'warehouse'];
        return $this->inventoryRepository->getOutOfStockProducts($with);
    }

    /**
     * Add stock to inventory.
     */
    public function addStock(int $productId, int $warehouseId, float $quantity, ?float $purchasePrice = null, ?string $notes = null): InventoryStock
    {
        try {
            DB::beginTransaction();

            $stock = $this->inventoryRepository->addStock($productId, $warehouseId, $quantity, $purchasePrice);

            // Log stock movement (will be implemented when StockMovement model is ready)
            $this->logStockMovement($productId, null, $warehouseId, 'in', $quantity, $notes ?? 'Stock added');

            DB::commit();

            Log::info('Stock added successfully', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'purchase_price' => $purchasePrice
            ]);

            return $stock;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add stock', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reduce stock from inventory.
     */
    public function reduceStock(int $productId, int $warehouseId, float $quantity, ?string $notes = null): InventoryStock
    {
        try {
            DB::beginTransaction();

            // Check if sufficient stock is available
            if (!$this->inventoryRepository->hasSufficientStock($productId, $warehouseId, $quantity)) {
                throw new \Exception('Insufficient stock available');
            }

            $stock = $this->inventoryRepository->reduceStock($productId, $warehouseId, $quantity);

            // Log stock movement
            $this->logStockMovement($productId, $warehouseId, null, 'out', $quantity, $notes ?? 'Stock reduced');

            DB::commit();

            Log::info('Stock reduced successfully', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity
            ]);

            return $stock;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reduce stock', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reserve stock for pending transactions.
     */
    public function reserveStock(int $productId, int $warehouseId, float $quantity, ?string $notes = null): InventoryStock
    {
        try {
            DB::beginTransaction();

            $stock = $this->inventoryRepository->reserveStock($productId, $warehouseId, $quantity);

            // Log stock movement
            $this->logStockMovement($productId, $warehouseId, null, 'reserved', $quantity, $notes ?? 'Stock reserved');

            DB::commit();

            Log::info('Stock reserved successfully', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity
            ]);

            return $stock;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reserve stock', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Release reserved stock.
     */
    public function releaseReservedStock(int $productId, int $warehouseId, float $quantity, ?string $notes = null): InventoryStock
    {
        try {
            DB::beginTransaction();

            $stock = $this->inventoryRepository->releaseReservedStock($productId, $warehouseId, $quantity);

            // Log stock movement
            $this->logStockMovement($productId, null, $warehouseId, 'released', $quantity, $notes ?? 'Reserved stock released');

            DB::commit();

            Log::info('Reserved stock released successfully', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity
            ]);

            return $stock;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to release reserved stock', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Perform stock adjustment.
     */
    public function adjustStock(int $productId, int $warehouseId, float $newQuantity, ?string $reason = null): InventoryStock
    {
        try {
            DB::beginTransaction();

            $currentStock = $this->inventoryRepository->getStockByProductAndWarehouse($productId, $warehouseId);
            $currentQuantity = $currentStock ? $currentStock->quantity : 0;
            $difference = $newQuantity - $currentQuantity;

            if ($difference > 0) {
                $stock = $this->inventoryRepository->addStock($productId, $warehouseId, $difference);
                $movementType = 'adjustment_in';
            } elseif ($difference < 0) {
                $stock = $this->inventoryRepository->reduceStock($productId, $warehouseId, abs($difference));
                $movementType = 'adjustment_out';
            } else {
                // No change needed
                return $currentStock;
            }

            // Log stock movement
            if ($difference > 0) {
                $this->logStockMovement($productId, null, $warehouseId, $movementType, abs($difference), $reason ?? 'Stock adjustment');
            } else {
                $this->logStockMovement($productId, $warehouseId, null, $movementType, abs($difference), $reason ?? 'Stock adjustment');
            }

            DB::commit();

            Log::info('Stock adjusted successfully', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'old_quantity' => $currentQuantity,
                'new_quantity' => $newQuantity,
                'difference' => $difference
            ]);

            return $stock;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to adjust stock', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'new_quantity' => $newQuantity,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get total stock for a product across all warehouses.
     */
    public function getTotalStockByProduct(int $productId): float
    {
        return $this->inventoryRepository->getTotalStockByProduct($productId);
    }

    /**
     * Get available stock for a product across all warehouses.
     */
    public function getAvailableStockByProduct(int $productId): float
    {
        return $this->inventoryRepository->getAvailableStockByProduct($productId);
    }

    /**
     * Check if sufficient stock is available.
     */
    public function hasSufficientStock(int $productId, int $warehouseId, float $requiredQuantity): bool
    {
        return $this->inventoryRepository->hasSufficientStock($productId, $warehouseId, $requiredQuantity);
    }

    /**
     * Get inventory valuation report.
     */
    public function getInventoryValuation(array $filters = []): array
    {
        return $this->inventoryRepository->getInventoryValuation($filters);
    }

    /**
     * Bulk update stocks.
     */
    public function bulkUpdateStocks(array $stockUpdates): bool
    {
        try {
            DB::beginTransaction();

            $result = $this->inventoryRepository->bulkUpdateStocks($stockUpdates);

            // Log bulk update
            foreach ($stockUpdates as $update) {
                $this->logStockMovement(
                    $update['product_id'],
                    null,
                    $update['warehouse_id'],
                    'bulk_update',
                    $update['quantity'] ?? 0,
                    'Bulk stock update'
                );
            }

            DB::commit();

            Log::info('Bulk stock update completed', [
                'total_updates' => count($stockUpdates)
            ]);

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to perform bulk stock update', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get stock alerts (low stock, out of stock).
     */
    public function getStockAlerts(int $lowStockThreshold = 10): array
    {
        $lowStock = $this->getLowStockProducts($lowStockThreshold);
        $outOfStock = $this->getOutOfStockProducts();

        return [
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'low_stock_count' => $lowStock->count(),
            'out_of_stock_count' => $outOfStock->count()
        ];
    }

    /**
     * Log stock movement for audit trail.
     */
    protected function logStockMovement(
        int $productId, 
        ?int $fromWarehouseId, 
        ?int $toWarehouseId, 
        string $movementType, 
        float $quantity, 
        string $notes,
        ?int $batchId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?float $unitCost = null
    ): void {
        StockMovement::create([
            'movement_type' => $movementType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'product_id' => $productId,
            'batch_id' => $batchId,
            'from_warehouse_id' => $fromWarehouseId,
            'to_warehouse_id' => $toWarehouseId,
            'quantity' => $quantity,
            'unit_id' => 1, // Default unit, should be passed as parameter in real implementation
            'unit_cost' => $unitCost,
            'notes' => $notes,
            'created_by' => Auth::check() ? Auth::id() : null,
            'movement_date' => now(),
        ]);
    }

    /**
     * FIFO stock allocation for sales.
     * This will be fully implemented when StockBatch model is created.
     */
    public function allocateStockFifo(int $productId, int $warehouseId, float $quantity): array
    {
        // Placeholder for FIFO allocation
        // Will be implemented when StockBatch model is ready
        return [
            'allocated_batches' => [],
            'total_allocated' => 0,
            'remaining_quantity' => $quantity
        ];
    }
}