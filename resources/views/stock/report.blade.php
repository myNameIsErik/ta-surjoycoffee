@extends('layouts.app')
@section('title', 'Laporan Posisi Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-clipboard-data me-1"></i> Laporan Posisi Stok</h4>
    <button onclick="window.print()" class="btn btn-outline-secondary no-print"><i class="bi bi-printer me-1"></i>Cetak</button>
</div>

<div class="card p-4">
    <div class="text-center mb-4">
        <h5 class="mb-1">{{ strtoupper(config('app.name')) }}</h5>
        <h6>LAPORAN POSISI STOK</h6>
        <div class="text-muted small">Per {{ now()->isoFormat('DD MMMM YYYY HH:mm') }}</div>
    </div>

    <table class="table table-sm align-middle report-table">
        <thead>
        <tr>
            <th>Kode</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th class="text-end">Stok</th>
            <th class="text-end">Min. Stok</th>
            <th class="text-end">Harga Beli Avg</th>
            <th class="text-end">Nilai Persediaan</th>
            <th class="text-center">Status</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($products as $p)
            <tr class="{{ $p->isLowStock() ? 'table-warning' : '' }}">
                <td>{{ $p->code }}</td>
                <td>{{ $p->name }}</td>
                <td class="text-muted small">{{ $p->category }}</td>
                <td class="text-end">@qty($p->stock) {{ $p->unit }}</td>
                <td class="text-end text-muted">@qty($p->min_stock)</td>
                <td class="text-end">@rupiah($p->cost_price)</td>
                <td class="text-end fw-semibold">@rupiah($p->stock_value)</td>
                <td class="text-center">
                    @if (!$p->is_active)
                        <span class="badge bg-secondary">Nonaktif</span>
                    @elseif ($p->isLowStock())
                        <span class="badge bg-danger">Stok Rendah</span>
                    @else
                        <span class="badge bg-success">OK</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-3">Belum ada master barang.</td></tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr>
            <th colspan="6" class="text-end">TOTAL NILAI PERSEDIAAN</th>
            <th class="text-end">@rupiah($totalValue)</th>
            <th></th>
        </tr>
        </tfoot>
    </table>
</div>
@endsection
