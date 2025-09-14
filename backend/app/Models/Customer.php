<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships
    public function salesTransactions()
    {
        return $this->hasMany(SalesTransaction::class);
    }
}
