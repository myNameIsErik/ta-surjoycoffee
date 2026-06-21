@extends('layouts.app')
@section('title', 'Penerima Konsinyasi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-shop-window me-1"></i> Penerima Konsinyasi</h4>
    <a href="{{ route('consignees.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Tambah Penerima</a>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-6">
            <label class="form-label small mb-0">Cari</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Nama, telepon, alamat...">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-0">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="active"   @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-1">
            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-funnel"></i></button>
            <a href="{{ route('consignees.index') }}" class="btn btn-sm btn-link">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>Nama Toko / Reseller</th>
                <th>Telepon</th>
                <th>Alamat</th>
                <th class="text-end">Transaksi</th>
                <th class="text-center">Status</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($consignees as $c)
                <tr>
                    <td class="fw-semibold">{{ $c->name }}</td>
                    <td>{{ $c->phone ?: '-' }}</td>
                    <td class="text-muted small">{{ $c->address ?: '-' }}</td>
                    <td class="text-end"><span class="badge bg-secondary">{{ $c->consignments_count }}</span></td>
                    <td class="text-center">
                        @if ($c->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('consignments.position', ['consignee_id' => $c->id]) }}" class="btn btn-sm btn-outline-secondary" title="Lihat Posisi Titipan"><i class="bi bi-clipboard-data"></i></a>
                        <a href="{{ route('consignees.edit', $c) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('consignees.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus penerima {{ $c->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" {{ $c->consignments_count > 0 ? 'disabled title="Masih punya transaksi"' : '' }}><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada penerima konsinyasi. <a href="{{ route('consignees.create') }}">Tambah sekarang</a>.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $consignees->links() }}</div>
</div>
@endsection
