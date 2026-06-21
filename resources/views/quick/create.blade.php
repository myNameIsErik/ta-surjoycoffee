@extends('layouts.app')
@section('title', 'Transaksi Cepat')

@section('content')
@php
    $isIncome = $type === 'income';
    $title = $isIncome ? 'Pemasukan' : 'Pengeluaran';
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-lightning-charge-fill me-1"></i> Transaksi Cepat</h4>
    <a href="{{ route('journals.index') }}" class="btn btn-link"><i class="bi bi-journal-text me-1"></i>Lihat Jurnal Umum</a>
</div>

<ul class="nav nav-pills mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $isIncome ? 'active' : '' }}" href="{{ route('quick.create', ['type' => 'income']) }}" style="{{ $isIncome ? 'background-color: var(--brand);' : '' }}">
            <i class="bi bi-arrow-down-circle me-1"></i>Pemasukan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ !$isIncome ? 'active' : '' }}" href="{{ route('quick.create', ['type' => 'expense']) }}" style="{{ !$isIncome ? 'background-color: var(--brand);' : '' }}">
            <i class="bi bi-arrow-up-circle me-1"></i>Pengeluaran
        </a>
    </li>
</ul>

<form method="POST" action="{{ route('quick.store') }}">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card p-3">
                <h6 class="mb-3"><i class="bi bi-pencil-square me-1"></i>Detail {{ $title }}</h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="form-control" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="1" name="amount" value="{{ old('amount') }}" class="form-control" placeholder="0" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Kategori {{ $title }} <span class="text-danger">*</span></label>
                        <select name="category_account_id" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categoryAccounts as $a)
                                <option value="{{ $a->id }}" @selected(old('category_account_id') == $a->id)>{{ $a->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            @if ($isIncome) Contoh: Penjualan Barang Dagang, Pendapatan Lain
                            @else Contoh: Sewa Gudang, Gaji, Listrik, Pengiriman, BBM
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ $isIncome ? 'Uang Masuk Ke' : 'Uang Diambil Dari' }} <span class="text-danger">*</span></label>
                        <select name="payment_account_id" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            @foreach ($paymentAccounts as $a)
                                <option value="{{ $a->id }}" @selected(old('payment_account_id') == $a->id)>{{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                        <input type="text" name="description" value="{{ old('description') }}" class="form-control" placeholder="@if($isIncome)Contoh: Penjualan harian 26 Mei@else Contoh: Bayar sewa bulan Mei @endif" required>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-brand"><i class="bi bi-save me-1"></i>Simpan {{ $title }}</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-link">Batal</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-3 mb-3">
                <h6 class="mb-2"><i class="bi bi-info-circle me-1"></i>Cara Pakai</h6>
                <ol class="small mb-0 ps-3">
                    <li>Pilih <strong>{{ $title }}</strong> di tab atas.</li>
                    <li>Isi tanggal & nominal.</li>
                    <li>Pilih kategori sesuai jenis transaksi.</li>
                    <li>Pilih sumber/tujuan uang (Kas atau Bank).</li>
                    <li>Tulis keterangan, lalu klik Simpan.</li>
                </ol>
            </div>

            <div class="card p-3 mb-3">
                <h6 class="mb-2"><i class="bi bi-lightbulb me-1"></i>Contoh Penggunaan</h6>
                @if ($isIncome)
                    <ul class="small mb-0 ps-3">
                        <li>Penjualan tunai harian &rarr; Penjualan Barang Dagang ke Kas</li>
                        <li>Transfer pelanggan via bank &rarr; Penjualan ke Bank</li>
                        <li>Pendapatan tambahan &rarr; Pendapatan Lain ke Kas/Bank</li>
                    </ul>
                @else
                    <ul class="small mb-0 ps-3">
                        <li>Bayar sewa tempat &rarr; Sewa Tempat dari Kas/Bank</li>
                        <li>Bayar gaji karyawan &rarr; Gaji Karyawan dari Bank</li>
                        <li>Bayar listrik/air &rarr; Listrik &amp; Air dari Kas/Bank</li>
                        <li>Beli barang dari supplier &rarr; Persediaan Barang Dagang dari Bank</li>
                        <li>Bayar pengiriman/ekspedisi &rarr; Pengiriman & Ekspedisi dari Kas</li>
                    </ul>
                @endif
            </div>

            <div class="alert alert-light border small mb-0">
                <i class="bi bi-shield-check text-brand me-1"></i>
                Sistem akan otomatis membuat jurnal akuntansi double-entry yang seimbang di belakang layar. Anda tidak perlu paham debit/kredit.
            </div>
        </div>
    </div>
</form>
@endsection
