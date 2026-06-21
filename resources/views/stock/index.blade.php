@extends('layouts.app')
@section('title', 'Transaksi Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-arrow-left-right me-1"></i> Transaksi Stok</h4>
    <div class="btn-group">
        <a href="{{ route('stock.create', ['type' => 'purchase']) }}" class="btn btn-success"><i class="bi bi-box-arrow-in-down me-1"></i>Stok Masuk (Beli)</a>
        <a href="{{ route('stock.create', ['type' => 'sale']) }}" class="btn btn-primary"><i class="bi bi-cart-check me-1"></i>Penjualan</a>
        <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"></button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="{{ route('stock.create', ['type' => 'adjustment_in']) }}"><i class="bi bi-plus-circle me-1"></i>Koreksi Tambah</a></li>
            <li><a class="dropdown-item" href="{{ route('stock.create', ['type' => 'adjustment_out']) }}"><i class="bi bi-dash-circle me-1"></i>Koreksi Kurang</a></li>
        </ul>
    </div>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-3">
            <label class="form-label small mb-0">Tipe</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach ($types as $k => $label)
                    <option value="{{ $k }}" @selected(($filters['type'] ?? '') === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-0">Barang</label>
            <select name="product_id" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" @selected((string)($filters['product_id'] ?? '') === (string)$p->id)>{{ $p->code }} — {{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><label class="form-label small mb-0">Dari</label><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm"></div>
        <div class="col-md-2"><label class="form-label small mb-0">Sampai</label><input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm"></div>
        <div class="col-md-2 d-flex gap-1">
            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-funnel"></i></button>
            <a href="{{ route('stock.index') }}" class="btn btn-sm btn-link">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Ref</th>
                <th>Tipe</th>
                <th>Barang</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Harga Satuan</th>
                <th class="text-end">Total</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($movements as $m)
                <tr>
                    <td class="text-nowrap">{{ $m->date->isoFormat('DD MMM YY') }}</td>
                    <td><code>{{ $m->reference }}</code></td>
                    <td>
                        @php
                            $badges = ['purchase'=>'bg-success','sale'=>'bg-primary','adjustment_in'=>'bg-info','adjustment_out'=>'bg-warning text-dark'];
                        @endphp
                        <span class="badge {{ $badges[$m->type] ?? 'bg-secondary' }}">{{ $m->typeLabel() }}</span>
                    </td>
                    <td>{{ $m->product->code }} — {{ $m->product->name }}</td>
                    <td class="text-end">@qty($m->quantity) {{ $m->product->unit }}</td>
                    <td class="text-end">
                        @rupiah($m->type === 'sale' ? $m->unit_price : $m->unit_cost)
                    </td>
                    <td class="text-end fw-semibold">
                        @rupiah($m->type === 'sale' ? $m->total_price : $m->total_cost)
                    </td>
                    <td class="text-end">
                        <a href="{{ route('stock.show', $m) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        <form action="{{ route('stock.destroy', $m) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus transaksi ini? Stok akan dikembalikan.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada transaksi stok.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $movements->links() }}</div>
</div>
@endsection
