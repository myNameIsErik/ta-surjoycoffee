<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'reference',
        'date',
        'type',
        'product_id',
        'quantity',
        'user_id',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'decimal:2',
    ];

    public const TYPES = [
        'purchase' => 'Pembelian (Stok Masuk)',
        'sale' => 'Penjualan (Stok Keluar)',
        'adjustment_in' => 'Koreksi Tambah',
        'adjustment_out' => 'Koreksi Kurang',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function isIncoming(): bool
    {
        return in_array($this->type, ['purchase', 'adjustment_in']);
    }

    public static function generateReference(string $type): string
    {
        $prefixMap = [
            'purchase' => 'PB',
            'sale' => 'PJ',
            'adjustment_in' => 'KP',
            'adjustment_out' => 'KM',
        ];
        $prefix = ($prefixMap[$type] ?? 'SM') . '-' . now()->format('Ymd') . '-';
        $last = static::where('reference', 'like', $prefix . '%')->orderByDesc('reference')->value('reference');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
