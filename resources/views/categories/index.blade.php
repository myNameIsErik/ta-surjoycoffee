@extends('layouts.app')
@section('title', 'Kategori Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-tags me-1"></i> Kategori Barang</h4>
    <a href="{{ route('categories.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Tambah Kategori</a>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2" method="GET">
        <div class="col-md-6"><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Cari nama kategori..."></div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="active"   @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>Cari</button>
            <a href="{{ route('categories.index') }}" class="btn btn-link">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>Nama</th>
                <th>Deskripsi</th>
                <th class="text-end">Jumlah Barang</th>
                <th class="text-center">Status</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($categories as $c)
                <tr>
                    <td class="fw-semibold">{{ $c->name }}</td>
                    <td class="text-muted small">{{ $c->description ?: '-' }}</td>
                    <td class="text-end"><span class="badge bg-secondary">{{ $c->products_count }}</span></td>
                    <td class="text-center">
                        @if ($c->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('categories.edit', $c) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('categories.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kategori {{ $c->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" {{ $c->products_count > 0 ? 'disabled title="Masih dipakai produk"' : '' }}><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada kategori. <a href="{{ route('categories.create') }}">Tambah sekarang</a>.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $categories->links() }}</div>
</div>
@endsection
