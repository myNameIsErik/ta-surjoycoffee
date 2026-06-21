@extends('layouts.app')
@section('title', $consignee->exists ? 'Edit Penerima' : 'Tambah Penerima')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
        <i class="bi bi-{{ $consignee->exists ? 'pencil' : 'plus-lg' }} me-1"></i>
        {{ $consignee->exists ? 'Edit Penerima Konsinyasi' : 'Tambah Penerima Konsinyasi' }}
    </h4>
    <a href="{{ route('consignees.index') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
            <form method="POST" action="{{ $consignee->exists ? route('consignees.update', $consignee) : route('consignees.store') }}">
                @csrf
                @if ($consignee->exists) @method('PUT') @endif

                <div class="mb-3">
                    <label class="form-label">Nama Toko / Reseller <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $consignee->name) }}" class="form-control" required>
                </div>

                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Telepon / WhatsApp</label>
                        <input type="text" name="phone" value="{{ old('phone', $consignee->phone) }}" class="form-control" placeholder="0812xxxxxxxx">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">Alamat</label>
                        <input type="text" name="address" value="{{ old('address', $consignee->address) }}" class="form-control" placeholder="Alamat singkat">
                    </div>
                </div>

                <div class="my-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="Opsional: termin pembayaran, PIC, dll">{{ old('notes', $consignee->notes) }}</textarea>
                </div>

                <div class="mb-3 form-check">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" @checked(old('is_active', $consignee->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Penerima aktif</label>
                    <div class="form-text">Penerima nonaktif tidak muncul saat input transaksi konsinyasi baru.</div>
                </div>

                <button class="btn btn-brand"><i class="bi bi-save me-1"></i>Simpan</button>
                <a href="{{ route('consignees.index') }}" class="btn btn-link">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
