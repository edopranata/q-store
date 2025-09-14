<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'customer_id',
        'warehouse_id',
        'transaction_date',
        'status',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'grand_total',
        'paid_amount',
        'change_amount',
        'notes',
        'cashier_id',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function salesTransactionItems(): HasMany
    {
        return $this->hasMany(SalesTransactionItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'sales_transaction_id');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // Helper methods
    public function getTotalItemsAttribute(): int
    {
        return $this->salesTransactionItems()->count();
    }

    public function getTotalQuantityAttribute(): float
    {
        return $this->salesTransactionItems()->sum('quantity');
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->grand_total - $this->paid_amount;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function canBePaid(): bool
    {
        return in_array($this->status, ['draft', 'pending', 'partial']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    public function calculateTotals(): void
    {
        $items = $this->salesTransactionItems;
        
        $this->subtotal = $items->sum('total_price');
        
        // Calculate discount
        if ($this->discount_percentage > 0) {
            $this->discount_amount = ($this->subtotal * $this->discount_percentage) / 100;
        }
        
        $afterDiscount = $this->subtotal - $this->discount_amount;
        
        // Calculate tax
        if ($this->tax_percentage > 0) {
            $this->tax_amount = ($afterDiscount * $this->tax_percentage) / 100;
        }
        
        $this->grand_total = $afterDiscount + $this->tax_amount;
        $this->save();
    }

    public function addPayment(float $amount, int $paymentMethodId, ?string $referenceNumber = null): bool
    {
        if (!$this->canBePaid()) {
            return false;
        }

        $this->paid_amount += $amount;
        
        if ($this->paid_amount >= $this->grand_total) {
            $this->status = 'paid';
            $this->change_amount = $this->paid_amount - $this->grand_total;
        } else {
            $this->status = 'partial';
        }
        
        $this->save();
        
        // Create payment record
        $this->payments()->create([
            'payment_method_id' => $paymentMethodId,
            'amount' => $amount,
            'reference_number' => $referenceNumber,
            'payment_date' => now(),
        ]);
        
        return true;
    }

    public function generateTransactionNumber(): string
    {
        $date = now()->format('Ymd');
        $lastTransaction = static::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastTransaction ? 
            (int) substr($lastTransaction->transaction_number, -4) + 1 : 1;
        
        return 'TRX-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
