@extends('layouts.app')
@section('title', 'Neraca')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-bank me-1"></i> Neraca (Laporan Posisi Keuangan)</h4>
    <button onclick="window.print()" class="btn btn-outline-secondary no-print"><i class="bi bi-printer me-1"></i>Cetak</button>
</div>

<form class="card p-3 mb-3 no-print" method="GET">
    <div class="row g-2 align-items-end">
        <div class="col-md-6"><label class="form-label small mb-0">Per Tanggal</label><input type="date" name="as_of" value="{{ $asOf }}" class="form-control form-control-sm"></div>
        <div class="col-md-6"><button class="btn btn-sm btn-brand"><i class="bi bi-funnel me-1"></i>Tampilkan</button></div>
    </div>
</form>

<div class="card p-4">
    <div class="text-center mb-4">
        <h5 class="mb-1">{{ strtoupper(config('app.name')) }}</h5>
        <h6>NERACA</h6>
        <div class="text-muted small">Per {{ \Carbon\Carbon::parse($asOf)->isoFormat('DD MMMM YYYY') }}</div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <table class="table table-sm report-table">
                <thead><tr><th colspan="3" class="text-center">ASET</th></tr></thead>
                <tbody>
                @forelse ($assets as $a)
                    <tr><td style="width:20%">{{ $a->code }}</td><td>{{ $a->name }}</td><td class="text-end">@rupiah($a->period_balance)</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted"><em>Tidak ada</em></td></tr>
                @endforelse
                </tbody>
                <tfoot><tr><th colspan="2" class="text-end">TOTAL ASET</th><th class="text-end">@rupiah($totalAssets)</th></tr></tfoot>
            </table>
        </div>

        <div class="col-md-6">
            <table class="table table-sm report-table">
                <thead><tr><th colspan="3" class="text-center">KEWAJIBAN</th></tr></thead>
                <tbody>
                @forelse ($liabilities as $a)
                    <tr><td style="width:20%">{{ $a->code }}</td><td>{{ $a->name }}</td><td class="text-end">@rupiah($a->period_balance)</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted"><em>Tidak ada</em></td></tr>
                @endforelse
                </tbody>
                <tfoot><tr><th colspan="2" class="text-end">Total Kewajiban</th><th class="text-end">@rupiah($totalLiabilities)</th></tr></tfoot>
            </table>

            <table class="table table-sm report-table mt-2">
                <thead><tr><th colspan="3" class="text-center">MODAL / EKUITAS</th></tr></thead>
                <tbody>
                @foreach ($equityAccounts as $a)
                    <tr><td style="width:20%">{{ $a->code }}</td><td>{{ $a->name }}</td><td class="text-end">@rupiah($a->period_balance)</td></tr>
                @endforeach
                <tr><td></td><td><em>Laba/Rugi Berjalan</em></td><td class="text-end {{ $currentEarnings >= 0 ? 'text-success' : 'text-danger' }}">@rupiah($currentEarnings)</td></tr>
                </tbody>
                <tfoot>
                    <tr><th colspan="2" class="text-end">Total Modal</th><th class="text-end">@rupiah($totalEquity)</th></tr>
                    <tr class="table-warning"><th colspan="2" class="text-end">TOTAL KEWAJIBAN + MODAL</th><th class="text-end">@rupiah($totalLiabilities + $totalEquity)</th></tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if (round($totalAssets, 2) !== round($totalLiabilities + $totalEquity, 2))
        <div class="alert alert-warning mt-3 mb-0 small">⚠ Total Aset tidak sama dengan Kewajiban + Modal. Pastikan semua jurnal sudah seimbang.</div>
    @endif
</div>
@endsection
