<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockBatch extends Model
{
    protected $fillable = [
        'batch_code',
        'product_id',
        'warehouse_id',
        'purchase_order_id',
        'purchase_price',
        'selling_price',
        'initial_qty',
        'current_qty',
        'unit_id',
        'expiry_date',
        'received_date',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'initial_qty' => 'decimal:2',
        'current_qty' => 'decimal:2',
        'expiry_date' => 'date',
        'received_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function salesTransactionItems(): HasMany
    {
        return $this->hasMany(SalesTransactionItem::class, 'batch_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('current_qty', '>', 0);
    }

    public function scopeFifoOrder($query)
    {
        return $query->orderBy('received_date', 'asc')->orderBy('id', 'asc');
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', now()->addDays($days));
    }

    // Methods
    public function reduceQuantity(float $quantity): bool
    {
        if ($this->current_qty >= $quantity) {
            $this->current_qty -= $quantity;
            return $this->save();
        }
        return false;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date && $this->expiry_date->lte(now()->addDays($days));
    }

    public function getAvailableQuantityAttribute(): float
    {
        return $this->current_qty;
    }

    public function getUsedQuantityAttribute(): float
    {
        return $this->initial_qty - $this->current_qty;
    }

    public function getUsagePercentageAttribute(): float
    {
        if ($this->initial_qty == 0) {
            return 0;
        }
        return ($this->used_quantity / $this->initial_qty) * 100;
    }
}
