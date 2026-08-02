@extends('layouts.app')
@section('title', 'Detail Transaksi Konsinyasi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-receipt me-1"></i> Detail Konsinyasi</h4>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>Cetak</button>
        <a href="{{ route('consignments.index') }}" class="btn btn-link">Kembali</a>
    </div>
</div>

<div class="card p-4">
    <div class="row g-3 mb-3">
        <div class="col-md-3"><strong>No. Ref:</strong> <code>{{ $movement->reference }}</code></div>
        <div class="col-md-3"><strong>Tanggal:</strong> {{ $movement->date->isoFormat('DD MMMM YYYY') }}</div>
        @php $badges = ['stock_in' => 'bg-success', 'send' => 'bg-warning text-dark', 'sold' => 'bg-primary']; @endphp
        <div class="col-md-3"><strong>Tipe:</strong> <span class="badge {{ $badges[$movement->type] ?? 'bg-secondary' }}">{{ $movement->typeLabel() }}</span></div>
        <div class="col-md-3"><strong>Penerima:</strong> {{ $movement->consignee?->name ?? '-' }}</div>
        <div class="col-md-6"><strong>Barang:</strong> {{ $movement->consignmentProduct?->code }} — {{ $movement->consignmentProduct?->name }}</div>
        <div class="col-md-6"><strong>Qty:</strong> <span class="text-brand fw-semibold">@qty($movement->quantity) {{ $movement->consignmentProduct?->unit }}</span></div>
        @if ($movement->type === 'sold')
        <div class="col-md-6"><strong>Harga Jual:</strong> @rupiah($movement->consignmentProduct?->sale_price ?? 0)</div>
        <div class="col-md-6"><strong>Omzet:</strong> <span class="text-primary fw-semibold">@rupiah($movement->omzet())</span></div>
        @endif
        @if ($movement->note)
        <div class="col-12"><strong>Catatan:</strong> {{ $movement->note }}</div>
        @endif
    </div>
</div>
@endsection
