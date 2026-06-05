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
        <div class="col-lg-7">
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
                        <input type="text" name="unit" value="{{ old('unit', $product->unit) }}" class="form-control" placeholder="pcs, kg, liter, cup" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Harga Beli (per satuan) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Harga Jual (per satuan) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="form-control" required>
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

        <div class="col-lg-5">
            <div class="card p-3">
                <h6 class="mb-3"><i class="bi bi-link-45deg me-1"></i>Mapping Akun Akuntansi</h6>
                <p class="text-muted small">Setiap transaksi stok untuk barang ini akan otomatis menggunakan akun-akun berikut.</p>

                <div class="mb-3">
                    <label class="form-label">Akun Persediaan <span class="text-danger">*</span></label>
                    <select name="inventory_account_id" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        @foreach ($accounts['inventory'] as $a)
                            <option value="{{ $a->id }}" @selected(old('inventory_account_id', $product->inventory_account_id) == $a->id)>{{ $a->code }} — {{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Akun Pendapatan <span class="text-danger">*</span></label>
                    <select name="revenue_account_id" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        @foreach ($accounts['revenue'] as $a)
                            <option value="{{ $a->id }}" @selected(old('revenue_account_id', $product->revenue_account_id) == $a->id)>{{ $a->code }} — {{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Akun HPP (Harga Pokok Penjualan) <span class="text-danger">*</span></label>
                    <select name="cogs_account_id" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        @foreach ($accounts['cogs'] as $a)
                            <option value="{{ $a->id }}" @selected(old('cogs_account_id', $product->cogs_account_id) == $a->id)>{{ $a->code }} — {{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="alert alert-info small mb-0">
                    <strong>Contoh penjualan:</strong><br>
                    Dr. Kas / Bank<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;Cr. Pendapatan<br>
                    Dr. HPP<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;Cr. Persediaan
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
