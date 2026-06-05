<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'normal_balance',
        'opening_balance',
        'description',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'aset' => 'Aset',
        'kewajiban' => 'Kewajiban',
        'modal' => 'Modal',
        'pendapatan' => 'Pendapatan',
        'beban' => 'Beban',
    ];

    public const NORMAL_BALANCE_BY_TYPE = [
        'aset' => 'debit',
        'beban' => 'debit',
        'kewajiban' => 'kredit',
        'modal' => 'kredit',
        'pendapatan' => 'kredit',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function balance(?string $startDate = null, ?string $endDate = null): float
    {
        $query = $this->entries();

        if ($startDate) {
            $query->whereHas('journal', fn ($q) => $q->where('date', '>=', $startDate));
        }
        if ($endDate) {
            $query->whereHas('journal', fn ($q) => $q->where('date', '<=', $endDate));
        }

        $totals = $query->selectRaw('COALESCE(SUM(debit),0) as debit_sum, COALESCE(SUM(credit),0) as credit_sum')->first();
        $debit = (float) ($totals->debit_sum ?? 0);
        $credit = (float) ($totals->credit_sum ?? 0);

        $opening = (float) $this->opening_balance;

        return $this->normal_balance === 'debit'
            ? $opening + ($debit - $credit)
            : $opening + ($credit - $debit);
    }
}
