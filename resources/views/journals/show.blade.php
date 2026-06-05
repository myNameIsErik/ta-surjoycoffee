@extends('layouts.app')
@section('title', 'Detail Jurnal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-journal-text me-1"></i> Detail Jurnal</h4>
    <div class="no-print">
        <a href="{{ route('journals.edit', $journal) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>Cetak</button>
        <a href="{{ route('journals.index') }}" class="btn btn-link">Kembali</a>
    </div>
</div>

<div class="card p-4">
    <div class="row mb-3">
        <div class="col-md-4"><strong>Referensi:</strong> <code>{{ $journal->reference }}</code></div>
        <div class="col-md-4"><strong>Tanggal:</strong> {{ $journal->date->isoFormat('DD MMMM YYYY') }}</div>
        <div class="col-md-4"><strong>Total:</strong> <span class="text-brand">@rupiah($journal->total)</span></div>
        <div class="col-12 mt-2"><strong>Keterangan:</strong> {{ $journal->description }}</div>
    </div>

    <table class="table align-middle">
        <thead>
        <tr>
            <th>Kode Akun</th>
            <th>Nama Akun</th>
            <th>Memo</th>
            <th class="text-end">Debit</th>
            <th class="text-end">Kredit</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($journal->entries as $e)
            <tr>
                <td>{{ $e->account->code }}</td>
                <td>{{ $e->account->name }}</td>
                <td class="text-muted small">{{ $e->memo }}</td>
                <td class="text-end">@if($e->debit > 0)@rupiah($e->debit)@endif</td>
                <td class="text-end">@if($e->credit > 0)@rupiah($e->credit)@endif</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr class="report-table">
            <th colspan="3" class="text-end">Total</th>
            <th class="text-end">@rupiah($journal->entries->sum('debit'))</th>
            <th class="text-end">@rupiah($journal->entries->sum('credit'))</th>
        </tr>
        </tfoot>
    </table>
</div>
@endsection
