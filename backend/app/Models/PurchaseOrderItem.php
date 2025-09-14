<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_unit_id',
        'quantity',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'total_price',
        'received_qty',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'received_qty' => 'decimal:2',
    ];

    // Relationships
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    // Scopes
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopePending($query)
    {
        return $query->whereColumn('received_qty', '<', 'quantity');
    }

    public function scopeFullyReceived($query)
    {
        return $query->whereColumn('received_qty', '>=', 'quantity');
    }

    // Helper methods
    public function getRemainingQuantityAttribute(): float
    {
        return $this->quantity - $this->received_qty;
    }

    public function getReceivedPercentageAttribute(): float
    {
        return $this->quantity > 0 ? ($this->received_qty / $this->quantity) * 100 : 0;
    }

    public function isFullyReceived(): bool
    {
        return $this->received_qty >= $this->quantity;
    }

    public function isPending(): bool
    {
        return $this->received_qty < $this->quantity;
    }

    public function canReceive(float $qty = null): bool
    {
        if ($qty === null) {
            return $this->received_qty < $this->quantity;
        }
        
        return ($this->received_qty + $qty) <= $this->quantity;
    }

    public function receiveQuantity(float $qty): bool
    {
        if (!$this->canReceive($qty)) {
            return false;
        }

        $this->received_qty += $qty;
        return $this->save();
    }

    public function calculateTotalPrice(): void
    {
        $this->total_price = ($this->quantity * $this->unit_price) + $this->tax_amount - $this->discount_amount;
        $this->save();
    }
}
