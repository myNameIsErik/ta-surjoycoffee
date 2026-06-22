@extends('layouts.app')
@section('title', $types[$type])

@section('content')
@php
    $icons = ['purchase'=>'box-arrow-in-down','sale'=>'cart-check','adjustment_in'=>'plus-circle','adjustment_out'=>'dash-circle'];
    $colors = ['purchase'=>'success','sale'=>'primary','adjustment_in'=>'info','adjustment_out'=>'warning'];
    $isOut = in_array($type, ['sale','adjustment_out']);
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-{{ $icons[$type] }} me-1 text-{{ $colors[$type] }}"></i> {{ $types[$type] }}</h4>
    <a href="{{ route('stock.index') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<form method="POST" action="{{ route('stock.store') }}" id="stockForm">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card p-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="form-control" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Barang <span class="text-danger">*</span></label>
                        <select name="product_id" id="product_id" class="form-select" required>
                            <option value="">-- Pilih Barang --</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}"
                                    data-stock="{{ $p->stock }}"
                                    data-unit="{{ $p->unit }}"
                                    @selected(old('product_id') == $p->id)>
                                    {{ $p->code }} — {{ $p->name }} (stok: @qty($p->stock) {{ $p->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="1" min="1" name="quantity" id="quantity" value="{{ old('quantity') }}" class="form-control" required>
                            <span class="input-group-text" id="unitLabel">-</span>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="note" rows="2" class="form-control" placeholder="Opsional: nomor PO, nama supplier/customer, dll">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card p-3">
                <h6 class="mb-3">Ringkasan</h6>
                <div class="d-flex justify-content-between mb-2"><span>Stok saat ini:</span><span id="currentStock" class="fw-semibold">-</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Qty:</span><span id="qtyPreview">-</span></div>
                <hr class="my-2">
                <div class="d-flex justify-content-between"><span>Stok setelah transaksi:</span><span id="afterStock" class="fw-semibold">-</span></div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-{{ $colors[$type] }}"><i class="bi bi-save me-1"></i>Simpan</button>
        <a href="{{ route('stock.index') }}" class="btn btn-link">Batal</a>
    </div>
</form>

@push('scripts')
<script>
(function() {
    const isOut = {{ $isOut ? 'true' : 'false' }};
    const numFmt = v => (Number(v) || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });

    const productSel = document.getElementById('product_id');
    const qtyEl = document.getElementById('quantity');
    const unitLabel = document.getElementById('unitLabel');

    function recalc() {
        const opt = productSel.options[productSel.selectedIndex];
        const stock = opt?.dataset?.stock ? parseFloat(opt.dataset.stock) : 0;
        const unit = opt?.dataset?.unit || '';
        unitLabel.textContent = unit || '-';

        const qty = parseFloat(qtyEl.value) || 0;

        document.getElementById('currentStock').textContent = productSel.value ? `${numFmt(stock)} ${unit}` : '-';
        document.getElementById('qtyPreview').textContent = qty ? `${numFmt(qty)} ${unit}` : '-';

        const after = isOut ? stock - qty : stock + qty;
        const afterEl = document.getElementById('afterStock');
        afterEl.textContent = productSel.value ? `${numFmt(after)} ${unit}` : '-';
        afterEl.className = 'fw-semibold ' + (after < 0 ? 'text-danger' : '');
    }

    productSel.addEventListener('change', recalc);
    qtyEl.addEventListener('input', recalc);
    recalc();
})();
</script>
@endpush
@endsection
