<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consignee extends Model
{
    protected $fillable = ['name', 'phone', 'address', 'notes', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function consignments(): HasMany
    {
        return $this->hasMany(Consignment::class);
    }

    /**
     * Outstanding qty per product (sum send - sum sold) untuk consignee ini.
     *
     * @return \Illuminate\Support\Collection berisi { product_id, product, outstanding }
     */
    public function outstandingByProduct()
    {
        $rows = $this->consignments()
            ->with('product')
            ->get()
            ->groupBy('product_id')
            ->map(function ($items) {
                $product = $items->first()->product;
                $sent = $items->where('type', 'send')->sum('quantity');
                $sold = $items->where('type', 'sold')->sum('quantity');

                return [
                    'product' => $product,
                    'outstanding' => (float) $sent - (float) $sold,
                ];
            })
            ->filter(fn ($row) => $row['outstanding'] > 0)
            ->values();

        return $rows;
    }
}
