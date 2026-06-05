@extends('layouts.app')
@section('title', $user->exists ? 'Edit User' : 'Tambah User')

@section('content')
@php $isSelf = $user->exists && $user->id === auth()->id(); @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
        <i class="bi bi-{{ $user->exists ? 'pencil' : 'person-plus' }} me-1"></i>
        {{ $user->exists ? 'Edit User' : 'Tambah User' }}
    </h4>
    <a href="{{ route('users.index') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
            <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">
                @csrf
                @if ($user->exists) @method('PUT') @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required {{ $isSelf ? 'disabled' : '' }}>
                            @foreach ($roles as $k => $label)
                                <option value="{{ $k }}" @selected(old('role', $user->role) === $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @if ($isSelf)
                            <input type="hidden" name="role" value="{{ $user->role }}">
                            <div class="form-text">Anda tidak dapat mengubah role sendiri.</div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" @checked(old('is_active', $user->is_active ?? true)) {{ $isSelf ? 'disabled' : '' }}>
                            <label class="form-check-label" for="is_active">User aktif</label>
                        </div>
                        @if ($isSelf)<div class="form-text">Anda tidak dapat menonaktifkan diri sendiri.</div>@endif
                    </div>

                    <div class="col-12"><hr class="my-1"></div>

                    <div class="col-md-6">
                        <label class="form-label">Password @if(!$user->exists)<span class="text-danger">*</span>@endif</label>
                        <input type="password" name="password" class="form-control" {{ $user->exists ? '' : 'required' }} minlength="6">
                        @if ($user->exists)<div class="form-text">Kosongkan jika tidak ingin mengganti password.</div>@endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Konfirmasi Password @if(!$user->exists)<span class="text-danger">*</span>@endif</label>
                        <input type="password" name="password_confirmation" class="form-control" {{ $user->exists ? '' : 'required' }} minlength="6">
                    </div>
                </div>

                <div class="alert alert-info small mt-3 mb-3">
                    <strong><i class="bi bi-shield-check me-1"></i>Hak akses per role:</strong><br>
                    <strong>Administrator</strong> — akses penuh termasuk Manajemen User.<br>
                    <strong>Kasir</strong> — akses ke transaksi (Jurnal, Stok) & laporan, tanpa Manajemen User.
                </div>

                <button class="btn btn-brand"><i class="bi bi-save me-1"></i>Simpan</button>
                <a href="{{ route('users.index') }}" class="btn btn-link">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
