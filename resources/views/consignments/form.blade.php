@extends('layouts.app')
@section('title', $types[$type])

@section('content')
@php
    $isSend = $type === 'send';
    $icon = $isSend ? 'box-arrow-right' : 'cash-coin';
    $color = $isSend ? 'success' : 'primary';
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-{{ $icon }} me-1 text-{{ $color }}"></i> {{ $types[$type] }}</h4>
    <a href="{{ route('consignments.index') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<form method="POST" action="{{ route('consignments.store') }}">
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
                        <label class="form-label">Penerima Konsinyasi <span class="text-danger">*</span></label>
                        <select name="consignee_id" id="consignee_id" class="form-select" required>
                            <option value="">-- Pilih Penerima --</option>
                            @foreach ($consignees as $c)
                                <option value="{{ $c->id }}" @selected(old('consignee_id') == $c->id)>{{ $c->name }}@if($c->phone) ({{ $c->phone }})@endif</option>
                            @endforeach
                        </select>
                        @if ($consignees->isEmpty())
                            <div class="form-text text-danger">Belum ada penerima konsinyasi. <a href="{{ route('consignees.create') }}">Tambah dulu</a>.</div>
                        @endif
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Barang <span class="text-danger">*</span></label>
                        <select name="product_id" id="product_id" class="form-select" required>
                            <option value="">-- Pilih Barang --</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}"
                                    data-price="{{ $p->sale_price }}"
                                    data-stock="{{ $p->stock }}"
                                    data-unit="{{ $p->unit }}"
                                    @selected(old('product_id') == $p->id)>
                                    {{ $p->code }} — {{ $p->name }}
                                    @if ($isSend) (stok: @qty($p->stock) {{ $p->unit }}) @endif
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
                        @if (!$isSend)<div class="form-text" id="outstandingHint">Sisa di penerima ini: -</div>@endif
                    </div>

                    @if (!$isSend)
                    <div class="col-md-4">
                        <label class="form-label">Harga Jual / satuan</label>
                        <input type="number" step="1" min="0" name="unit_price" id="unit_price" value="{{ old('unit_price') }}" class="form-control" placeholder="Default dari master">
                    </div>
                    @endif

                    <div class="col-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="note" rows="2" class="form-control" placeholder="Opsional: catatan / PIC">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card p-3">
                <h6 class="mb-3">Ringkasan</h6>
                @if ($isSend)
                    <div class="d-flex justify-content-between mb-2"><span>Stok gudang saat ini:</span><span id="currentStock" class="fw-semibold">-</span></div>
                @endif
                <div class="d-flex justify-content-between mb-2"><span>Qty:</span><span id="qtyPreview">-</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Nilai {{ $isSend ? 'Modal' : 'Penjualan' }}:</span><span id="totalPreview" class="text-brand fw-semibold">Rp 0</span></div>
                @if ($isSend)
                    <div class="d-flex justify-content-between"><span>Stok gudang setelah:</span><span id="afterStock" class="fw-semibold">-</span></div>
                @endif

                <div class="alert alert-info small mt-3 mb-0">
                    @if ($isSend)
                        <i class="bi bi-info-circle me-1"></i>Stok fisik akan berkurang dari gudang & masuk ke titipan penerima. Belum jadi penjualan sampai dilaporkan terjual.
                    @else
                        <i class="bi bi-info-circle me-1"></i>Pastikan penerima sudah melaporkan barang yang terjual. Stok gudang tidak berubah (sudah berkurang saat dikirim).
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-{{ $color }}"><i class="bi bi-save me-1"></i>Simpan</button>
        <a href="{{ route('consignments.index') }}" class="btn btn-link">Batal</a>
    </div>
</form>

@push('scripts')
<script>
(function() {
    const isSend = {{ $isSend ? 'true' : 'false' }};
    const fmt = v => 'Rp ' + (Number(v) || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    const numFmt = v => (Number(v) || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });

    const productSel = document.getElementById('product_id');
    const consigneeSel = document.getElementById('consignee_id');
    const qtyEl = document.getElementById('quantity');
    const priceEl = document.getElementById('unit_price');
    const unitLabel = document.getElementById('unitLabel');

    async function fetchOutstanding(consigneeId, productId) {
        if (!consigneeId || !productId) return 0;
        try {
            const res = await fetch(`/consignments/outstanding?consignee_id=${consigneeId}&product_id=${productId}`);
            if (!res.ok) return 0;
            const j = await res.json();
            return Number(j.outstanding || 0);
        } catch (e) { return 0; }
    }

    async function recalc() {
        const opt = productSel.options[productSel.selectedIndex];
        const price = opt?.dataset?.price ? parseFloat(opt.dataset.price) : 0;
        const stock = opt?.dataset?.stock ? parseFloat(opt.dataset.stock) : 0;
        const unit = opt?.dataset?.unit || '';
        unitLabel.textContent = unit || '-';

        if (!isSend && priceEl && !priceEl.value) priceEl.value = price;

        const qty = parseFloat(qtyEl.value) || 0;
        const used = !isSend ? (parseFloat(priceEl?.value) || price) : (opt?.dataset?.cost || 0);

        if (isSend) {
            document.getElementById('currentStock').textContent = productSel.value ? `${numFmt(stock)} ${unit}` : '-';
            const after = stock - qty;
            const afterEl = document.getElementById('afterStock');
            afterEl.textContent = productSel.value ? `${numFmt(after)} ${unit}` : '-';
            afterEl.className = 'fw-semibold ' + (after < 0 ? 'text-danger' : '');
        } else {
            const hint = document.getElementById('outstandingHint');
            if (consigneeSel.value && productSel.value) {
                const outstanding = await fetchOutstanding(consigneeSel.value, productSel.value);
                hint.textContent = `Sisa di penerima ini: ${numFmt(outstanding)} ${unit}`;
                hint.className = 'form-text ' + (qty > outstanding ? 'text-danger' : '');
            } else {
                hint.textContent = 'Sisa di penerima ini: -';
            }
        }
        document.getElementById('qtyPreview').textContent = qty ? `${numFmt(qty)} ${unit}` : '-';

        const total = isSend ? qty * (parseFloat(opt?.dataset?.cost) || 0) : qty * (parseFloat(priceEl?.value) || price);
        document.getElementById('totalPreview').textContent = fmt(total);
    }

    productSel.addEventListener('change', () => { if (priceEl) priceEl.value = ''; recalc(); });
    consigneeSel.addEventListener('change', recalc);
    [qtyEl, priceEl].forEach(el => el && el.addEventListener('input', recalc));
    recalc();
})();
</script>
@endpush
@endsection
