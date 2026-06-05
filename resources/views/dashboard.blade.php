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
            <div class="text-muted small">Pendapatan Bulan Ini</div>
            <div class="h4 mb-0 text-success">@rupiah($revenueMonth)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Beban Bulan Ini</div>
            <div class="h4 mb-0 text-danger">@rupiah($expenseMonth)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Laba / Rugi Bulan Ini</div>
            <div class="h4 mb-0 {{ $profitMonth >= 0 ? 'text-success' : 'text-danger' }}">@rupiah($profitMonth)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Saldo Kas &amp; Bank</div>
            <div class="h4 mb-0 text-brand">@rupiah($cashBalance)</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Tren 6 Bulan Terakhir</h6>
            </div>
            <canvas id="trendChart" height="120"></canvas>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Jurnal Terakhir</h6>
                <a href="{{ route('journals.index') }}" class="small">Lihat semua &raquo;</a>
            </div>
            @if ($recentJournals->isEmpty())
                <div class="text-muted small">Belum ada transaksi.</div>
            @else
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Tanggal</th><th>Keterangan</th><th class="text-end">Jumlah</th></tr></thead>
                    <tbody>
                    @foreach ($recentJournals as $j)
                        <tr>
                            <td class="text-nowrap">{{ $j->date->isoFormat('DD MMM YY') }}</td>
                            <td><a href="{{ route('journals.show', $j) }}">{{ $j->description }}</a></td>
                            <td class="text-end">@rupiah($j->total)</td>
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
                { label: 'Pendapatan', data: @json($months->pluck('revenue')), backgroundColor: 'rgba(40,167,69,.7)' },
                { label: 'Beban',      data: @json($months->pluck('expense')), backgroundColor: 'rgba(220,53,69,.7)' },
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } } }
        }
    });
</script>
@endpush
@endsection
