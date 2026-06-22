@extends('layouts.app')
@section('title', 'Kartu Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-card-list me-1"></i> Kartu Stok</h4>
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
        <h5 class="mb-1">KARTU STOK</h5>
        <div>{{ $product->code }} — {{ $product->name }}</div>
        <div class="text-muted small">
            Satuan: {{ $product->unit }} &middot; Kategori: {{ $product->category?->name ?: '-' }}
        </div>
    </div>

    <table class="table table-sm align-middle report-table">
        <thead>
        <tr>
            <th>Tanggal</th>
            <th>No. Ref</th>
            <th>Tipe</th>
            <th class="text-end">Masuk</th>
            <th class="text-end">Keluar</th>
            <th class="text-end">Saldo Stok</th>
            <th>Catatan</th>
        </tr>
        </thead>
        <tbody>
        <tr class="table-light">
            <td colspan="5"><em>Saldo Awal</em></td>
            <td class="text-end fw-semibold">@qty($opening) {{ $product->unit }}</td>
            <td></td>
        </tr>
        @forelse ($movements as $m)
            <tr>
                <td>{{ $m->date->isoFormat('DD MMM YY') }}</td>
                <td><code>{{ $m->reference }}</code></td>
                <td>{{ $m->typeLabel() }}</td>
                <td class="text-end text-success">@if($m->isIncoming())@qty($m->quantity)@endif</td>
                <td class="text-end text-danger">@if(!$m->isIncoming())@qty($m->quantity)@endif</td>
                <td class="text-end">@qty($m->running_stock)</td>
                <td class="text-muted small">{{ $m->note }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada transaksi.</td></tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr>
            <th colspan="5" class="text-end">Saldo Akhir</th>
            <th class="text-end">@qty($ending) {{ $product->unit }}</th>
            <th></th>
        </tr>
        </tfoot>
    </table>
</div>
@endsection
