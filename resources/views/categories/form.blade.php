@extends('layouts.app')
@section('title', $category->exists ? 'Edit Kategori' : 'Tambah Kategori')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
        <i class="bi bi-{{ $category->exists ? 'pencil' : 'plus-lg' }} me-1"></i>
        {{ $category->exists ? 'Edit Kategori' : 'Tambah Kategori' }}
    </h4>
    <a href="{{ route('categories.index') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4">
            <form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}">
                @csrf
                @if ($category->exists) @method('PUT') @endif

                <div class="mb-3">
                    <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" class="form-control" required>
                    <div class="form-text">Contoh: Minuman, Makanan, Snack, Bahan Baku.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="3" class="form-control" placeholder="Opsional, untuk memperjelas pemakaian kategori">{{ old('description', $category->description) }}</textarea>
                </div>

                <div class="mb-3 form-check">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" @checked(old('is_active', $category->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Kategori aktif</label>
                    <div class="form-text">Kategori nonaktif tidak muncul saat menambah/edit produk.</div>
                </div>

                <button class="btn btn-brand"><i class="bi bi-save me-1"></i>Simpan</button>
                <a href="{{ route('categories.index') }}" class="btn btn-link">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
