@extends('layouts.app')
@section('title', 'Master Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-box-seam me-1"></i> Master Barang</h4>
    @if (auth()->user()->isAdmin())
        <a href="{{ route('products.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Tambah Barang</a>
    @endif
</div>

<div class="card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-4">
            <label class="form-label small mb-0">Cari</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Kode atau nama...">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-0">Kategori</label>
            <select name="category_id" class="form-select form-select-sm">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string)($filters['category_id'] ?? '') === (string)$cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-0">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="active"   @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
            </select>
        </div>
        <div class="col-md-3 d-flex flex-column gap-1">
            <div class="form-check small">
                <input type="checkbox" name="low_stock" value="1" id="low_stock" class="form-check-input" @checked($filters['low_stock'] ?? false)>
                <label for="low_stock" class="form-check-label">Stok di bawah minimum</label>
            </div>
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-funnel"></i></button>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-link">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th class="text-end">Stok</th>
                <th class="text-end">Harga Beli</th>
                <th class="text-end">Harga Jual</th>
                <th class="text-center">Status</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($products as $p)
                <tr>
                    <td class="fw-semibold">{{ $p->code }}</td>
                    <td>{{ $p->name }}</td>
                    <td><span class="text-muted small">{{ $p->category?->name ?: '-' }}</span></td>
                    <td class="text-end">
                        @qty($p->stock) {{ $p->unit }}
                        @if ($p->isLowStock())
                            <span class="badge bg-danger ms-1" title="Di bawah minimum">!</span>
                        @endif
                    </td>
                    <td class="text-end">@rupiah($p->cost_price)</td>
                    <td class="text-end">@rupiah($p->sale_price)</td>
                    <td class="text-center">
                        @if ($p->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('stock.card', $p) }}" class="btn btn-sm btn-outline-secondary" title="Kartu Stok"><i class="bi bi-journal"></i></a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('products.edit', $p) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('products.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus barang ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada barang. <a href="{{ route('products.create') }}">Tambah sekarang</a>.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $products->links() }}</div>
</div>
@endsection
