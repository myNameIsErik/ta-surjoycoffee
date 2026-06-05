@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-people me-1"></i> Manajemen User</h4>
    <a href="{{ route('users.create') }}" class="btn btn-brand"><i class="bi bi-person-plus me-1"></i>Tambah User</a>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-4">
            <label class="form-label small mb-0">Cari</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Nama atau email...">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-0">Role</label>
            <select name="role" class="form-select form-select-sm">
                <option value="">Semua Role</option>
                @foreach ($roles as $k => $label)
                    <option value="{{ $k }}" @selected(($filters['role'] ?? '') === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-0">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="active"   @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-funnel"></i></button>
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-link">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th class="text-center">Status</th>
                <th>Dibuat</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($users as $u)
                <tr>
                    <td>
                        <i class="bi bi-person-circle me-1"></i>
                        <span class="fw-semibold">{{ $u->name }}</span>
                        @if ($u->id === auth()->id())
                            <span class="badge bg-info ms-1">Anda</span>
                        @endif
                    </td>
                    <td>{{ $u->email }}</td>
                    <td>
                        <span class="badge {{ $u->role === 'admin' ? 'bg-brand' : 'bg-secondary' }}">
                            <i class="bi bi-{{ $u->role === 'admin' ? 'shield-check' : 'person-badge' }} me-1"></i>{{ $u->roleLabel() }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if ($u->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $u->created_at?->isoFormat('DD MMM YYYY') }}</td>
                    <td class="text-end">
                        <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        @if ($u->id !== auth()->id())
                            <form action="{{ route('users.destroy', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus user {{ $u->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada user.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $users->links() }}</div>
</div>
@endsection
