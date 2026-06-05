@extends('layouts.app')
@section('title', 'Detail Transaksi Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-receipt me-1"></i> Detail Transaksi Stok</h4>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>Cetak</button>
        <a href="{{ route('stock.index') }}" class="btn btn-link">Kembali</a>
    </div>
</div>

<div class="card p-4">
    <div class="row g-3 mb-3">
        <div class="col-md-3"><strong>No. Ref:</strong> <code>{{ $movement->reference }}</code></div>
        <div class="col-md-3"><strong>Tanggal:</strong> {{ $movement->date->isoFormat('DD MMMM YYYY') }}</div>
        <div class="col-md-3"><strong>Tipe:</strong> {{ $movement->typeLabel() }}</div>
        <div class="col-md-3"><strong>Barang:</strong> {{ $movement->product->code }} — {{ $movement->product->name }}</div>
        <div class="col-md-3"><strong>Qty:</strong> @qty($movement->quantity) {{ $movement->product->unit }}</div>
        <div class="col-md-3"><strong>Harga Pokok:</strong> @rupiah($movement->unit_cost)</div>
        <div class="col-md-3"><strong>Harga Jual:</strong> @rupiah($movement->unit_price)</div>
        <div class="col-md-3"><strong>Total:</strong> <span class="text-brand">@rupiah($movement->type === 'sale' ? $movement->total_price : $movement->total_cost)</span></div>
        @if ($movement->paymentAccount)
        <div class="col-md-6"><strong>Akun Bayar/Terima:</strong> {{ $movement->paymentAccount->code }} — {{ $movement->paymentAccount->name }}</div>
        @endif
        @if ($movement->note)
        <div class="col-12"><strong>Catatan:</strong> {{ $movement->note }}</div>
        @endif
    </div>

    @if ($movement->journal)
    <hr>
    <h6 class="mt-3 mb-3"><i class="bi bi-journal-text me-1"></i> Jurnal Otomatis: <a href="{{ route('journals.show', $movement->journal) }}"><code>{{ $movement->journal->reference }}</code></a></h6>
    <table class="table align-middle">
        <thead><tr><th>Akun</th><th>Memo</th><th class="text-end">Debit</th><th class="text-end">Kredit</th></tr></thead>
        <tbody>
        @foreach ($movement->journal->entries as $e)
            <tr>
                <td>{{ $e->account->code }} — {{ $e->account->name }}</td>
                <td class="text-muted small">{{ $e->memo }}</td>
                <td class="text-end">@if($e->debit > 0)@rupiah($e->debit)@endif</td>
                <td class="text-end">@if($e->credit > 0)@rupiah($e->credit)@endif</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr class="report-table"><th colspan="2" class="text-end">Total</th><th class="text-end">@rupiah($movement->journal->entries->sum('debit'))</th><th class="text-end">@rupiah($movement->journal->entries->sum('credit'))</th></tr>
        </tfoot>
    </table>
    @endif
</div>
@endsection
