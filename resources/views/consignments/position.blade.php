@extends('layouts.app')
@section('title', 'Posisi Konsinyasi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-clipboard-data me-1"></i> Posisi Konsinyasi</h4>
    <button onclick="window.print()" class="btn btn-outline-secondary no-print"><i class="bi bi-printer me-1"></i>Cetak</button>
</div>

<form class="card p-3 mb-3 no-print" method="GET">
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label small mb-0">Penerima Konsinyasi</label>
            <select name="consignee_id" class="form-select form-select-sm">
                <option value="">Semua Penerima</option>
                @foreach ($allConsignees as $c)
                    <option value="{{ $c->id }}" @selected((string)($filters['consignee_id'] ?? '') === (string)$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 d-flex gap-1">
            <button class="btn btn-sm btn-brand"><i class="bi bi-funnel me-1"></i>Tampilkan</button>
            <a href="{{ route('consignments.position') }}" class="btn btn-sm btn-link">Reset</a>
        </div>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-6"><div class="card p-3"><div class="text-muted small">Total Nilai Modal Titipan</div><div class="h5 mb-0">@rupiah($grandTotalCost)</div></div></div>
    <div class="col-md-6"><div class="card p-3"><div class="text-muted small">Estimasi Nilai Penjualan</div><div class="h5 mb-0 text-success">@rupiah($grandTotalPrice)</div></div></div>
</div>

<div class="card p-4">
    <div class="text-center mb-3">
        <h5 class="mb-1">{{ strtoupper(config('app.name')) }}</h5>
        <h6>POSISI KONSINYASI (Barang Titipan)</h6>
        <div class="text-muted small">Per {{ now()->isoFormat('DD MMMM YYYY HH:mm') }}</div>
    </div>

    @forelse ($consignees as $c)
        <div class="mb-4">
            <h6 class="mb-2 text-brand">
                <i class="bi bi-shop-window me-1"></i>{{ $c->name }}
                @if ($c->phone)<small class="text-muted ms-2">{{ $c->phone }}</small>@endif
            </h6>
            <table class="table table-sm align-middle report-table">
                <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th class="text-end">Qty Titipan</th>
                    <th class="text-end">Harga Beli</th>
                    <th class="text-end">Harga Jual</th>
                    <th class="text-end">Nilai Modal</th>
                    <th class="text-end">Nilai Penjualan</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($c->outstanding_rows as $row)
                    <tr>
                        <td>{{ $row['product']->code }}</td>
                        <td>{{ $row['product']->name }}</td>
                        <td class="text-end fw-semibold">@qty($row['outstanding']) {{ $row['product']->unit }}</td>
                        <td class="text-end text-muted">@rupiah($row['product']->cost_price)</td>
                        <td class="text-end">@rupiah($row['product']->sale_price)</td>
                        <td class="text-end">@rupiah($row['outstanding'] * (float) $row['product']->cost_price)</td>
                        <td class="text-end text-success">@rupiah($row['outstanding'] * (float) $row['product']->sale_price)</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Subtotal {{ $c->name }}</th>
                    <th class="text-end">@rupiah($c->outstanding_total_cost)</th>
                    <th class="text-end text-success">@rupiah($c->outstanding_total_price)</th>
                </tr>
                </tfoot>
            </table>
        </div>
    @empty
        <div class="text-center text-muted py-4">Tidak ada barang titipan yang masih outstanding.</div>
    @endforelse

    @if ($consignees->count() > 0)
        <div class="border-top pt-3 mt-2">
            <div class="d-flex justify-content-between">
                <strong>GRAND TOTAL</strong>
                <div>
                    Modal: <strong>@rupiah($grandTotalCost)</strong> &nbsp;|&nbsp;
                    Estimasi Penjualan: <strong class="text-success">@rupiah($grandTotalPrice)</strong>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
