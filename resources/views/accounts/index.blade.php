@extends('layouts.app')
@section('title', 'Bagan Akun')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-list-columns me-1"></i> Bagan Akun</h4>
    <a href="{{ route('accounts.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Tambah Akun</a>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-4">
            <label class="form-label small mb-0">Cari</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Kode atau nama akun...">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-0">Tipe</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">Semua Tipe</option>
                @foreach ($types as $k => $label)
                    <option value="{{ $k }}" @selected(($filters['type'] ?? '') === $k)>{{ $label }}</option>
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
            <a href="{{ route('accounts.index') }}" class="btn btn-sm btn-link">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Akun</th>
                <th>Tipe</th>
                <th>Saldo Normal</th>
                <th class="text-end">Saldo Berjalan</th>
                <th class="text-center">Aktif</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($accounts as $account)
                <tr>
                    <td class="fw-semibold">{{ $account->code }}</td>
                    <td>{{ $account->name }}</td>
                    <td><span class="badge bg-secondary">{{ $account->typeLabel() }}</span></td>
                    <td><span class="text-uppercase small">{{ $account->normal_balance }}</span></td>
                    <td class="text-end">@rupiah($account->balance())</td>
                    <td class="text-center">
                        @if ($account->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('reports.ledger', ['account_id' => $account->id]) }}" class="btn btn-sm btn-outline-secondary" title="Buku Besar"><i class="bi bi-journal"></i></a>
                        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus akun ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada akun. <a href="{{ route('accounts.create') }}">Tambah sekarang</a>.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $accounts->links() }}</div>
</div>
@endsection
