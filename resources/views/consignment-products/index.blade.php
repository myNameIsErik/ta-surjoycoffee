@extends('layouts.app')
@section('title', 'Master Barang Konsinyasi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-box-seam me-1"></i> Master Barang Konsinyasi</h4>
    @if (auth()->user()->isAdmin())
        <a href="{{ route('consignment-products.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Tambah Barang</a>
    @endif
</div>

<div class="alert alert-info small">
    <i class="bi bi-info-circle me-1"></i>Daftar barang khusus konsinyasi (titip jual), <strong>terpisah</strong> dari Master Barang penjualan. Stok gudang di sini diisi lewat menu <strong>Stok Masuk (Konsinyasi)</strong>.
</div>

<div class="card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-5">
            <label class="form-label small mb-0">Cari</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Kode atau nama...">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-0">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="active"   @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
            </select>
        </div>
        <div class="col-md-4 d-flex flex-column gap-1">
            <div class="form-check small">
                <input type="checkbox" name="low_stock" value="1" id="low_stock" class="form-check-input" @checked($filters['low_stock'] ?? false)>
                <label for="low_stock" class="form-check-label">Stok di bawah minimum</label>
            </div>
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-funnel"></i></button>
                <a href="{{ route('consignment-products.index') }}" class="btn btn-sm btn-link">Reset</a>
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
                <th class="text-end">Harga Jual</th>
                <th class="text-end">Stok Gudang</th>
                <th class="text-end">Stok Min</th>
                <th class="text-center">Status</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($products as $p)
                <tr>
                    <td class="fw-semibold">{{ $p->code }}</td>
                    <td>{{ $p->name }}</td>
                    <td class="text-end">@rupiah($p->sale_price)</td>
                    <td class="text-end">
                        @qty($p->stock) {{ $p->unit }}
                        @if ($p->isLowStock())
                            <span class="badge bg-danger ms-1" title="Di bawah minimum">!</span>
                        @endif
                    </td>
                    <td class="text-end text-muted">@qty($p->min_stock)</td>
                    <td class="text-center">
                        @if ($p->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('consignment-products.edit', $p) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('consignment-products.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus barang konsinyasi ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada barang konsinyasi. @if (auth()->user()->isAdmin())<a href="{{ route('consignment-products.create') }}">Tambah sekarang</a>.@endif</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $products->links() }}</div>
</div>
@endsection
