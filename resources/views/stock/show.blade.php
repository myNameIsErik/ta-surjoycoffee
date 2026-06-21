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
        @if ($movement->note)
        <div class="col-12"><strong>Catatan:</strong> {{ $movement->note }}</div>
        @endif
    </div>
</div>
@endsection
