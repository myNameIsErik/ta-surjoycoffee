@extends('layouts.app')
@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-graph-up-arrow me-1"></i> Laporan Laba Rugi</h4>
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
        <h6>LAPORAN LABA RUGI</h6>
        <div class="text-muted small">Periode {{ \Carbon\Carbon::parse($from)->isoFormat('DD MMM YYYY') }} s/d {{ \Carbon\Carbon::parse($to)->isoFormat('DD MMM YYYY') }}</div>
    </div>

    <table class="table report-table">
        <tbody>
        <tr class="table-light"><th colspan="3">PENDAPATAN</th></tr>
        @forelse ($revenueAccounts as $a)
            <tr>
                <td style="width:20%">{{ $a->code }}</td>
                <td>{{ $a->name }}</td>
                <td class="text-end" style="width:25%">@rupiah($a->period_balance)</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-muted text-center"><em>Tidak ada pendapatan pada periode ini</em></td></tr>
        @endforelse
        <tr><th colspan="2" class="text-end">Total Pendapatan</th><th class="text-end text-success">@rupiah($totalRevenue)</th></tr>

        <tr class="table-light"><th colspan="3">BEBAN</th></tr>
        @forelse ($expenseAccounts as $a)
            <tr>
                <td>{{ $a->code }}</td>
                <td>{{ $a->name }}</td>
                <td class="text-end">@rupiah($a->period_balance)</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-muted text-center"><em>Tidak ada beban pada periode ini</em></td></tr>
        @endforelse
        <tr><th colspan="2" class="text-end">Total Beban</th><th class="text-end text-danger">@rupiah($totalExpense)</th></tr>

        <tr class="table-warning">
            <th colspan="2" class="text-end">{{ $netIncome >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}</th>
            <th class="text-end {{ $netIncome >= 0 ? 'text-success' : 'text-danger' }}">@rupiah($netIncome)</th>
        </tr>
        </tbody>
    </table>
</div>
@endsection
