@extends('layouts.app')
@section('title', $product->exists ? 'Edit Barang' : 'Tambah Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
        <i class="bi bi-{{ $product->exists ? 'pencil' : 'plus-lg' }} me-1"></i>
        {{ $product->exists ? 'Edit Barang' : 'Tambah Barang' }}
    </h4>
    <a href="{{ route('products.index') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}">
    @csrf
    @if ($product->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-lg-8 mx-auto">
            <div class="card p-3">
                <h6 class="mb-3">Informasi Barang</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kode <span class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $product->code) }}" class="form-control" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Tanpa Kategori --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Kelola kategori di menu Kategori Barang.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="unit" value="{{ old('unit', $product->unit) }}" class="form-control" placeholder="pcs, dus, karung, kg, liter" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Stok {{ $product->exists ? 'Saat Ini' : 'Awal' }}</label>
                        <input type="number" step="1" min="0" name="stock" value="{{ old('stock', (int) $product->stock) }}" class="form-control" {{ $product->exists ? 'readonly' : '' }}>
                        @if ($product->exists)
                            <div class="form-text">Stok hanya bisa diubah lewat transaksi stok masuk / keluar.</div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Stok Minimum</label>
                        <input type="number" step="1" min="0" name="min_stock" value="{{ old('min_stock', (int) $product->min_stock) }}" class="form-control">
                        <div class="form-text">Akan ditandai jika stok ≤ minimum.</div>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" @checked(old('is_active', $product->is_active))>
                            <label class="form-check-label" for="is_active">Barang aktif</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-3">
        <button class="btn btn-brand"><i class="bi bi-save me-1"></i>Simpan</button>
        <a href="{{ route('products.index') }}" class="btn btn-link">Batal</a>
    </div>
</form>
@endsection
