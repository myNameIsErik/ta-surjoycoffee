@extends('layouts.app')
@section('title', 'Neraca Saldo')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-table me-1"></i> Neraca Saldo</h4>
    <button onclick="window.print()" class="btn btn-outline-secondary no-print"><i class="bi bi-printer me-1"></i>Cetak</button>
</div>

<form class="card p-3 mb-3 no-print" method="GET">
    <div class="row g-2 align-items-end">
        <div class="col-md-4"><label class="form-label small mb-0">Dari</label><input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm"></div>
        <div class="col-md-4"><label class="form-label small mb-0">Sampai</label><input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm"></div>
        <div class="col-md-4"><button class="btn btn-sm btn-brand"><i class="bi bi-funnel me-1"></i>Tampilkan</button></div>
    </div>
</form>

<div class="card p-4">
    <div class="text-center mb-4">
        <h5 class="mb-1">{{ strtoupper(config('app.name')) }}</h5>
        <h6>NERACA SALDO</h6>
        <div class="text-muted small">Periode {{ \Carbon\Carbon::parse($from)->isoFormat('DD MMM YYYY') }} s/d {{ \Carbon\Carbon::parse($to)->isoFormat('DD MMM YYYY') }}</div>
    </div>

    <table class="table table-sm align-middle report-table">
        <thead>
        <tr>
            <th>Kode</th>
            <th>Nama Akun</th>
            <th>Tipe</th>
            <th class="text-end">Debit</th>
            <th class="text-end">Kredit</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($accounts as $a)
            <tr>
                <td>{{ $a->code }}</td>
                <td>{{ $a->name }}</td>
                <td><span class="text-muted small">{{ $a->typeLabel() }}</span></td>
                <td class="text-end">@if($a->balance_debit > 0)@rupiah($a->balance_debit)@endif</td>
                <td class="text-end">@if($a->balance_credit > 0)@rupiah($a->balance_credit)@endif</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada data transaksi.</td></tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr>
            <th colspan="3" class="text-end">TOTAL</th>
            <th class="text-end">@rupiah($totalDebit)</th>
            <th class="text-end">@rupiah($totalCredit)</th>
        </tr>
        @if (round($totalDebit, 2) !== round($totalCredit, 2))
            <tr><td colspan="5" class="text-center text-danger">⚠ Neraca saldo tidak seimbang. Periksa kembali jurnal Anda.</td></tr>
        @endif
        </tfoot>
    </table>
</div>
@endsection
