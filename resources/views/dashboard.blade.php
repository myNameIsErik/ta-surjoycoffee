@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-speedometer2 me-1"></i> Dashboard</h4>
    <span class="text-muted small">Periode: {{ now()->isoFormat('MMMM Y') }}</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Qty Penjualan Bulan Ini</div>
            <div class="h4 mb-0 text-primary">@qty($salesQtyMonth)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Qty Pembelian Bulan Ini</div>
            <div class="h4 mb-0 text-success">@qty($purchaseQtyMonth)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Jumlah Jenis Barang</div>
            <div class="h4 mb-0 text-brand">{{ $totalProducts }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Barang Stok Rendah</div>
            <div class="h4 mb-0 {{ $lowStockCount > 0 ? 'text-danger' : '' }}">{{ $lowStockCount }}</div>
            @if ($lowStockCount > 0)
                <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="small">
                    <i class="bi bi-exclamation-triangle me-1"></i>Lihat detail
                </a>
            @endif
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Tren Qty Penjualan vs Pembelian (6 Bulan)</h6>
            </div>
            <canvas id="trendChart" height="120"></canvas>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Transaksi Stok Terakhir</h6>
                <a href="{{ route('stock.index') }}" class="small">Lihat semua &raquo;</a>
            </div>
            @if ($recentMovements->isEmpty())
                <div class="text-muted small">Belum ada transaksi stok.</div>
            @else
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Tanggal</th><th>Barang</th><th class="text-end">Qty</th></tr></thead>
                    <tbody>
                    @foreach ($recentMovements as $m)
                        @php
                            $badges = ['purchase'=>'bg-success','sale'=>'bg-primary','adjustment_in'=>'bg-info','adjustment_out'=>'bg-warning text-dark'];
                        @endphp
                        <tr>
                            <td class="text-nowrap small">{{ $m->date->isoFormat('DD MMM') }}</td>
                            <td>
                                <a href="{{ route('stock.show', $m) }}" class="text-decoration-none">{{ $m->product->name }}</a>
                                <span class="badge {{ $badges[$m->type] ?? 'bg-secondary' }} small">{{ $m->typeLabel() }}</span>
                            </td>
                            <td class="text-end">@qty($m->quantity) {{ $m->product->unit }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="/assets/js/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('trendChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($months->pluck('label')),
            datasets: [
                { label: 'Qty Penjualan', data: @json($months->pluck('sales')), backgroundColor: 'rgba(13,110,253,.7)' },
                { label: 'Qty Pembelian', data: @json($months->pluck('purchase')), backgroundColor: 'rgba(40,167,69,.7)' },
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('id-ID') } } }
        }
    });
</script>
@endpush
@endsection
