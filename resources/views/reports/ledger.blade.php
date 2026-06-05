@extends('layouts.app')
@section('title', 'Buku Besar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-journal me-1"></i> Buku Besar</h4>
    <button onclick="window.print()" class="btn btn-outline-secondary no-print"><i class="bi bi-printer me-1"></i>Cetak</button>
</div>

<form class="card p-3 mb-3 no-print" method="GET">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label small mb-0">Akun</label>
            <select name="account_id" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach ($accounts as $a)
                    <option value="{{ $a->id }}" @selected($account && $account->id === $a->id)>{{ $a->code }} — {{ $a->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-0">Dari</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-0">Sampai</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-1"><button class="btn btn-sm btn-brand w-100"><i class="bi bi-funnel"></i></button></div>
    </div>
</form>

@if ($account)
<div class="card p-4">
    <div class="text-center mb-4">
        <h5 class="mb-1">BUKU BESAR</h5>
        <div>{{ $account->code }} — {{ $account->name }} ({{ $account->typeLabel() }})</div>
        <div class="text-muted small">Periode: {{ \Carbon\Carbon::parse($from)->isoFormat('DD MMM YYYY') }} s/d {{ \Carbon\Carbon::parse($to)->isoFormat('DD MMM YYYY') }}</div>
    </div>

    <table class="table table-sm align-middle report-table">
        <thead>
        <tr>
            <th>Tanggal</th>
            <th>Ref</th>
            <th>Keterangan</th>
            <th class="text-end">Debit</th>
            <th class="text-end">Kredit</th>
            <th class="text-end">Saldo</th>
        </tr>
        </thead>
        <tbody>
        <tr class="table-light">
            <td colspan="5"><em>Saldo Awal Periode</em></td>
            <td class="text-end fw-semibold">@rupiah($openingBalance)</td>
        </tr>
        @forelse ($entries as $e)
            <tr>
                <td>{{ $e->journal->date->isoFormat('DD MMM YY') }}</td>
                <td><a href="{{ route('journals.show', $e->journal) }}"><code>{{ $e->journal->reference }}</code></a></td>
                <td>{{ $e->memo ?: $e->journal->description }}</td>
                <td class="text-end">@if($e->debit > 0)@rupiah($e->debit)@endif</td>
                <td class="text-end">@if($e->credit > 0)@rupiah($e->credit)@endif</td>
                <td class="text-end">@rupiah($e->running_balance)</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada transaksi pada periode ini.</td></tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr>
            <th colspan="3" class="text-end">Total Pergerakan</th>
            <th class="text-end">@rupiah($totalDebit)</th>
            <th class="text-end">@rupiah($totalCredit)</th>
            <th class="text-end">@rupiah($endingBalance)</th>
        </tr>
        </tfoot>
    </table>
</div>
@endif
@endsection
