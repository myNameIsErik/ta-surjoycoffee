@extends('layouts.app')
@section('title', $types[$type])

@section('content')
@php
    $needsPayment = in_array($type, ['purchase', 'sale']);
    $isSale = $type === 'sale';
    $icons = ['purchase'=>'box-arrow-in-down','sale'=>'cart-check','adjustment_in'=>'plus-circle','adjustment_out'=>'dash-circle'];
    $colors = ['purchase'=>'success','sale'=>'primary','adjustment_in'=>'info','adjustment_out'=>'warning'];
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
                                    data-cost="{{ $p->cost_price }}"
                                    data-price="{{ $p->sale_price }}"
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

                    @if ($isSale)
                    <div class="col-md-4">
                        <label class="form-label">Harga Jual / satuan</label>
                        <input type="number" step="0.01" min="0" name="unit_price" id="unit_price" value="{{ old('unit_price') }}" class="form-control" placeholder="Default dari master">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Harga Pokok / satuan</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" id="unit_cost" value="{{ old('unit_cost') }}" class="form-control" readonly>
                    </div>
                    @else
                    <div class="col-md-4">
                        <label class="form-label">Harga / satuan @if($type==='purchase')<span class="text-danger">*</span>@endif</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" id="unit_cost" value="{{ old('unit_cost') }}" class="form-control" {{ $type==='purchase' ? 'required' : '' }}>
                    </div>
                    @endif

                    @if ($needsPayment)
                    <div class="col-md-8">
                        <label class="form-label">Akun {{ $type === 'purchase' ? 'Pembayaran' : 'Penerimaan' }} <span class="text-danger">*</span></label>
                        <select name="payment_account_id" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            @foreach ($paymentAccounts as $a)
                                <option value="{{ $a->id }}" @selected(old('payment_account_id') == $a->id)>{{ $a->code }} — {{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="note" rows="2" class="form-control">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card p-3">
                <h6 class="mb-3">Ringkasan</h6>
                <div class="d-flex justify-content-between mb-2"><span>Stok saat ini:</span><span id="currentStock" class="fw-semibold">-</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Qty:</span><span id="qtyPreview">-</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Total {{ $isSale ? 'Penjualan' : 'Nilai' }}:</span><span id="totalPreview" class="text-brand fw-semibold">Rp 0</span></div>
                @if ($isSale)
                <div class="d-flex justify-content-between mb-2"><span>Total HPP:</span><span id="cogsPreview" class="text-muted">Rp 0</span></div>
                <hr class="my-2">
                <div class="d-flex justify-content-between"><span>Estimasi Laba Kotor:</span><span id="profitPreview" class="text-success fw-semibold">Rp 0</span></div>
                @endif
                <hr class="my-2">
                <div class="d-flex justify-content-between"><span>Stok setelah transaksi:</span><span id="afterStock" class="fw-semibold">-</span></div>

                <div class="alert alert-info small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>Jurnal akuntansi akan otomatis dibuat saat transaksi disimpan.
                </div>
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
    const isSale = {{ $isSale ? 'true' : 'false' }};
    const isOut = {{ in_array($type, ['sale','adjustment_out']) ? 'true' : 'false' }};
    const fmt = v => 'Rp ' + (Number(v) || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    const numFmt = v => (Number(v) || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });

    const productSel = document.getElementById('product_id');
    const qtyEl = document.getElementById('quantity');
    const costEl = document.getElementById('unit_cost');
    const priceEl = document.getElementById('unit_price');
    const unitLabel = document.getElementById('unitLabel');

    function recalc() {
        const opt = productSel.options[productSel.selectedIndex];
        const cost = opt?.dataset?.cost ? parseFloat(opt.dataset.cost) : 0;
        const price = opt?.dataset?.price ? parseFloat(opt.dataset.price) : 0;
        const stock = opt?.dataset?.stock ? parseFloat(opt.dataset.stock) : 0;
        const unit = opt?.dataset?.unit || '';
        unitLabel.textContent = unit || '-';

        if (productSel.value && costEl && !costEl.value) costEl.value = cost.toFixed(2);
        if (isSale && priceEl && !priceEl.value) priceEl.value = price.toFixed(2);

        const qty = parseFloat(qtyEl.value) || 0;
        const usedCost = parseFloat(costEl?.value) || 0;
        const usedPrice = parseFloat(priceEl?.value) || 0;

        document.getElementById('currentStock').textContent = productSel.value ? `${numFmt(stock)} ${unit}` : '-';
        document.getElementById('qtyPreview').textContent = qty ? `${numFmt(qty)} ${unit}` : '-';
        document.getElementById('totalPreview').textContent = fmt(isSale ? qty * usedPrice : qty * usedCost);
        if (isSale) {
            document.getElementById('cogsPreview').textContent = fmt(qty * usedCost);
            document.getElementById('profitPreview').textContent = fmt(qty * (usedPrice - usedCost));
        }
        const after = isOut ? stock - qty : stock + qty;
        const afterEl = document.getElementById('afterStock');
        afterEl.textContent = productSel.value ? `${numFmt(after)} ${unit}` : '-';
        afterEl.className = 'fw-semibold ' + (after < 0 ? 'text-danger' : '');
    }

    productSel.addEventListener('change', () => {
        if (costEl) costEl.value = '';
        if (priceEl) priceEl.value = '';
        recalc();
    });
    [qtyEl, costEl, priceEl].forEach(el => el && el.addEventListener('input', recalc));
    recalc();
})();
</script>
@endpush
@endsection
