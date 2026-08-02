<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignmentProduct extends Model
{
    protected $fillable = [
        'code',
        'name',
        'unit',
        'sale_price',
        'stock',
        'min_stock',
        'is_active',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function consignments(): HasMany
    {
        return $this->hasMany(Consignment::class);
    }

    public function isLowStock(): bool
    {
        return $this->min_stock > 0 && $this->stock <= $this->min_stock;
    }
}
