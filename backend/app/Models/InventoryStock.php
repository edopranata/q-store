<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class InventoryStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_qty',
        'last_purchase_price',
        'average_cost', 
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'reserved_qty' => 'decimal:3',
        'last_purchase_price' => 'decimal:2',
        'average_cost' => 'decimal:2',
    ];

    protected $appends = [
        'available_qty',
        'is_out_of_stock',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Accessors
    public function getAvailableQtyAttribute()
    {
        return $this->quantity - $this->reserved_qty;
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->quantity <= 0;
    }

    // Scopes
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    // Methods
    public function addStock($quantity, $purchasePrice = null)
    {
        $this->quantity += $quantity;
        
        if ($purchasePrice !== null) {
            $this->updateAverageCost($quantity, $purchasePrice);
            $this->last_purchase_price = $purchasePrice;
        }
        
        return $this->save();
    }

    public function reduceStock($quantity)
    {
        if ($this->available_qty < $quantity) {
            throw new \Exception('Insufficient stock available');
        }
        
        $this->quantity -= $quantity;
        return $this->save();
    }

    public function reserveStock($quantity)
    {
        if ($this->available_qty < $quantity) {
            throw new \Exception('Insufficient stock to reserve');
        }
        
        $this->reserved_qty += $quantity;
        return $this->save();
    }

    public function releaseReservedStock($quantity)
    {
        $this->reserved_qty = max(0, $this->reserved_qty - $quantity);
        return $this->save();
    }

    public function updateAverageCost($addedQuantity, $purchasePrice)
    {
        if ($this->quantity > 0) {
            $totalValue = ($this->quantity * $this->average_cost) + ($addedQuantity * $purchasePrice);
            $totalQuantity = $this->quantity + $addedQuantity;
            $this->average_cost = $totalValue / $totalQuantity;
        } else {
            $this->average_cost = $purchasePrice;
        }
        
        return $this;
    }
}
