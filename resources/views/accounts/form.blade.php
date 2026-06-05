@extends('layouts.app')
@section('title', $account->exists ? 'Edit Akun' : 'Tambah Akun')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="mb-3"><i class="bi bi-{{ $account->exists ? 'pencil' : 'plus-lg' }} me-1"></i>
                {{ $account->exists ? 'Edit Akun' : 'Tambah Akun Baru' }}
            </h5>
            <form method="POST" action="{{ $account->exists ? route('accounts.update', $account) : route('accounts.store') }}">
                @csrf
                @if ($account->exists) @method('PUT') @endif

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kode Akun <span class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $account->code) }}" class="form-control @error('code') is-invalid @enderror" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Nama Akun <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $account->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipe Akun <span class="text-danger">*</span></label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="">-- Pilih Tipe --</option>
                            @foreach ($types as $k => $label)
                                <option value="{{ $k }}" @selected(old('type', $account->type) === $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Saldo normal akan ditetapkan otomatis (Aset & Beban = Debit; Kewajiban, Modal, Pendapatan = Kredit).</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Saldo Awal (opsional)</label>
                        <input type="number" step="0.01" min="0" name="opening_balance" value="{{ old('opening_balance', $account->opening_balance) }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" rows="2" class="form-control">{{ old('description', $account->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" @checked(old('is_active', $account->is_active))>
                            <label class="form-check-label" for="is_active">Akun aktif</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-brand"><i class="bi bi-save me-1"></i>Simpan</button>
                    <a href="{{ route('accounts.index') }}" class="btn btn-link">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
