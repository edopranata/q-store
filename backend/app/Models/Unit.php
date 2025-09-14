<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'symbol',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships
    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function salesTransactionItems()
    {
        return $this->hasMany(SalesTransactionItem::class);
    }
}
