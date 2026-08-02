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
        'consignment_product_id',
        'quantity',
        'user_id',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'decimal:2',
    ];

    public const TYPES = [
        'stock_in' => 'Stok Masuk (Konsinyasi)',
        'send' => 'Kirim Titipan',
        'sold' => 'Lapor Terjual',
    ];

    public function consignee(): BelongsTo
    {
        return $this->belongsTo(Consignee::class);
    }

    public function consignmentProduct(): BelongsTo
    {
        return $this->belongsTo(ConsignmentProduct::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /** Nilai omzet untuk baris "Lapor Terjual" (qty x harga jual master konsinyasi). */
    public function omzet(): float
    {
        if ($this->type !== 'sold') {
            return 0.0;
        }

        return (float) $this->quantity * (float) ($this->consignmentProduct->sale_price ?? 0);
    }

    public static function generateReference(string $type): string
    {
        $prefixMap = [
            'stock_in' => 'KSM',
            'send' => 'KSK',
            'sold' => 'KSJ',
        ];
        $prefix = ($prefixMap[$type] ?? 'KS') . '-' . now()->format('Ymd') . '-';
        $last = static::where('reference', 'like', $prefix . '%')->orderByDesc('reference')->value('reference');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Outstanding qty global per (consignee_id, consignment_product_id).
     * Returns float (sent - sold). "stock_in" tidak dihitung (itu stok gudang, bukan titipan).
     */
    public static function outstanding(int $consigneeId, int $consignmentProductId): float
    {
        $sent = static::where('consignee_id', $consigneeId)
            ->where('consignment_product_id', $consignmentProductId)
            ->where('type', 'send')->sum('quantity');
        $sold = static::where('consignee_id', $consigneeId)
            ->where('consignment_product_id', $consignmentProductId)
            ->where('type', 'sold')->sum('quantity');

        return (float) $sent - (float) $sold;
    }
}
