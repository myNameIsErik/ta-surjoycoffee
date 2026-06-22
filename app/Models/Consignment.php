<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consignment extends Model
{
    protected $fillable = [
        'reference',
        'date',
        'type',
        'consignee_id',
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
        'send' => 'Kirim Titipan',
        'sold' => 'Lapor Terjual',
    ];

    public function consignee(): BelongsTo
    {
        return $this->belongsTo(Consignee::class);
    }

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

    public static function generateReference(string $type): string
    {
        $prefix = ($type === 'send' ? 'KSK' : 'KSJ') . '-' . now()->format('Ymd') . '-';
        $last = static::where('reference', 'like', $prefix . '%')->orderByDesc('reference')->value('reference');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Outstanding qty global per (consignee_id, product_id).
     * Returns float (sent - sold).
     */
    public static function outstanding(int $consigneeId, int $productId): float
    {
        $sent = static::where('consignee_id', $consigneeId)
            ->where('product_id', $productId)
            ->where('type', 'send')->sum('quantity');
        $sold = static::where('consignee_id', $consigneeId)
            ->where('product_id', $productId)
            ->where('type', 'sold')->sum('quantity');

        return (float) $sent - (float) $sold;
    }
}
