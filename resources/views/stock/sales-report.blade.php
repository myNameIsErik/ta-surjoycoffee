@extends('layouts.app')
@section('title', 'Laporan Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-cart-check me-1"></i> Laporan Penjualan</h4>
    <button onclick="window.print()" class="btn btn-outline-secondary no-print"><i class="bi bi-printer me-1"></i>Cetak</button>
</div>

<form class="card p-3 mb-3 no-print" method="GET">
    <div class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label small mb-0">Dari</label><input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm"></div>
        <div class="col-md-3"><label class="form-label small mb-0">Sampai</label><input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm"></div>
        <div class="col-md-4">
            <label class="form-label small mb-0">Barang</label>
            <select name="product_id" class="form-select form-select-sm">
                <option value="">Semua Barang</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" @selected((string)($filters['productId'] ?? '') === (string)$p->id)>{{ $p->code }} — {{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button class="btn btn-sm btn-brand"><i class="bi bi-funnel me-1"></i>Tampilkan</button>
            <a href="{{ route('stock.sales-report') }}" class="btn btn-sm btn-link">Reset</a>
        </div>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-6"><div class="card p-3"><div class="text-muted small">Total Transaksi</div><div class="h5 mb-0">{{ $totalTransactions }}</div></div></div>
    <div class="col-md-6"><div class="card p-3"><div class="text-muted small">Total Qty Terjual</div><div class="h5 mb-0 text-primary">@qty($totalQty)</div></div></div>
</div>

<div class="card p-4">
    <div class="text-center mb-3">
        <h5 class="mb-1">{{ strtoupper(config('app.name')) }}</h5>
        <h6>LAPORAN PENJUALAN</h6>
        <div class="text-muted small">Periode {{ \Carbon\Carbon::parse($from)->isoFormat('DD MMM YYYY') }} s/d {{ \Carbon\Carbon::parse($to)->isoFormat('DD MMM YYYY') }}</div>
    </div>

    <table class="table table-sm align-middle report-table">
        <thead>
        <tr>
            <th>Tanggal</th>
            <th>No. Ref</th>
            <th>Barang</th>
            <th class="text-end">Qty</th>
            <th>Catatan</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($movements as $m)
            <tr>
                <td class="text-nowrap">{{ $m->date->isoFormat('DD MMM YY') }}</td>
                <td><code>{{ $m->reference }}</code></td>
                <td>{{ $m->product->name }}</td>
                <td class="text-end fw-semibold">@qty($m->quantity) {{ $m->product->unit }}</td>
                <td class="text-muted small">{{ $m->note }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-3">Tidak ada penjualan pada periode ini.</td></tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr>
            <th colspan="3" class="text-end">TOTAL ({{ $totalTransactions }} transaksi)</th>
            <th class="text-end">@qty($totalQty)</th>
            <th></th>
        </tr>
        </tfoot>
    </table>
</div>
@endsection
