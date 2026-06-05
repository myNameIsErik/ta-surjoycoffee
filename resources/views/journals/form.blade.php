@extends('layouts.app')
@section('title', $journal->exists ? 'Edit Jurnal' : 'Tambah Jurnal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
        <i class="bi bi-{{ $journal->exists ? 'pencil' : 'plus-lg' }} me-1"></i>
        {{ $journal->exists ? 'Edit Jurnal' : 'Tambah Jurnal Baru' }}
    </h4>
    <a href="{{ route('journals.index') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<form method="POST" action="{{ $journal->exists ? route('journals.update', $journal) : route('journals.store') }}" id="journalForm">
    @csrf
    @if ($journal->exists) @method('PUT') @endif

    <div class="card p-3 mb-3">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                <input type="date" name="date" value="{{ old('date', $journal->date instanceof \Carbon\Carbon ? $journal->date->toDateString() : $journal->date) }}" class="form-control" required>
            </div>
            <div class="col-md-9">
                <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                <input type="text" name="description" value="{{ old('description', $journal->description) }}" class="form-control" placeholder="Contoh: Penjualan tunai harian" required>
            </div>
        </div>
    </div>

    <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Rincian Akun (Debit / Kredit)</h6>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="addRow"><i class="bi bi-plus-lg me-1"></i>Baris</button>
        </div>
        <p class="text-muted small mb-3">Pastikan total Debit = total Kredit (double entry). Satu baris hanya boleh diisi salah satu (debit ATAU kredit).</p>

        <div class="table-responsive">
            <table class="table align-middle" id="linesTable">
                <thead>
                <tr>
                    <th style="width: 30%;">Akun</th>
                    <th style="width: 25%;">Memo</th>
                    <th style="width: 18%;" class="text-end">Debit</th>
                    <th style="width: 18%;" class="text-end">Kredit</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @php
                    $oldLines = old('lines');
                    if ($oldLines) {
                        $rows = $oldLines;
                    } elseif ($entries->count() > 0) {
                        $rows = $entries->map(fn ($e) => [
                            'account_id' => $e->account_id,
                            'memo' => $e->memo,
                            'debit' => $e->debit > 0 ? $e->debit : null,
                            'credit' => $e->credit > 0 ? $e->credit : null,
                        ])->toArray();
                    } else {
                        $rows = [['account_id'=>'','memo'=>'','debit'=>'','credit'=>''], ['account_id'=>'','memo'=>'','debit'=>'','credit'=>'']];
                    }
                @endphp
                @foreach ($rows as $i => $row)
                    <tr class="journal-line">
                        <td>
                            <select name="lines[{{ $i }}][account_id]" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Akun --</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}" @selected((string)($row['account_id'] ?? '') === (string)$acc->id)>
                                        {{ $acc->code }} — {{ $acc->name }} ({{ ucfirst($acc->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="text" name="lines[{{ $i }}][memo]" value="{{ $row['memo'] ?? '' }}" class="form-control form-control-sm"></td>
                        <td><input type="number" step="0.01" min="0" name="lines[{{ $i }}][debit]" value="{{ $row['debit'] ?? '' }}" class="form-control form-control-sm text-end debit-input"></td>
                        <td><input type="number" step="0.01" min="0" name="lines[{{ $i }}][credit]" value="{{ $row['credit'] ?? '' }}" class="form-control form-control-sm text-end credit-input"></td>
                        <td class="text-end"><button type="button" class="btn btn-sm btn-link text-danger remove-row"><i class="bi bi-x-circle"></i></button></td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="2" class="text-end">Total</th>
                    <th class="text-end"><span id="totalDebit">Rp 0</span></th>
                    <th class="text-end"><span id="totalCredit">Rp 0</span></th>
                    <th></th>
                </tr>
                <tr>
                    <th colspan="2" class="text-end">Selisih</th>
                    <th colspan="2" class="text-end"><span id="balanceStatus" class="badge bg-secondary">Rp 0</span></th>
                    <th></th>
                </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-3">
            <button class="btn btn-brand"><i class="bi bi-save me-1"></i>Simpan Jurnal</button>
            <a href="{{ route('journals.index') }}" class="btn btn-link">Batal</a>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function() {
    const tbody = document.querySelector('#linesTable tbody');
    const fmt = v => 'Rp ' + (Number(v) || 0).toLocaleString('id-ID');

    function reindex() {
        [...tbody.querySelectorAll('tr.journal-line')].forEach((tr, i) => {
            tr.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/lines\[\d+\]/, 'lines[' + i + ']');
            });
        });
    }

    function recalc() {
        let d = 0, c = 0;
        tbody.querySelectorAll('.debit-input').forEach(i => d += parseFloat(i.value) || 0);
        tbody.querySelectorAll('.credit-input').forEach(i => c += parseFloat(i.value) || 0);
        document.getElementById('totalDebit').textContent = fmt(d);
        document.getElementById('totalCredit').textContent = fmt(c);
        const diff = d - c;
        const badge = document.getElementById('balanceStatus');
        badge.textContent = fmt(Math.abs(diff)) + (diff === 0 ? ' (Seimbang)' : (diff > 0 ? ' (Debit lebih)' : ' (Kredit lebih)'));
        badge.className = 'badge ' + (diff === 0 && d > 0 ? 'bg-success' : 'bg-warning text-dark');
    }

    document.getElementById('addRow').addEventListener('click', () => {
        const last = tbody.querySelector('tr.journal-line');
        const clone = last.cloneNode(true);
        clone.querySelectorAll('input').forEach(i => i.value = '');
        clone.querySelector('select').selectedIndex = 0;
        tbody.appendChild(clone);
        reindex();
        recalc();
    });

    tbody.addEventListener('click', e => {
        if (e.target.closest('.remove-row')) {
            if (tbody.querySelectorAll('tr.journal-line').length <= 2) return;
            e.target.closest('tr').remove();
            reindex();
            recalc();
        }
    });

    tbody.addEventListener('input', e => {
        if (e.target.classList.contains('debit-input') && parseFloat(e.target.value) > 0) {
            e.target.closest('tr').querySelector('.credit-input').value = '';
        }
        if (e.target.classList.contains('credit-input') && parseFloat(e.target.value) > 0) {
            e.target.closest('tr').querySelector('.debit-input').value = '';
        }
        recalc();
    });

    recalc();
})();
</script>
@endpush
@endsection
