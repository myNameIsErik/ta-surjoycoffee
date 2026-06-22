@extends('layouts.app')
@section('title', 'Transaksi Konsinyasi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-truck me-1"></i> Transaksi Konsinyasi</h4>
    <div class="btn-group">
        <a href="{{ route('consignments.create', ['type' => 'send']) }}" class="btn btn-success"><i class="bi bi-box-arrow-right me-1"></i>Kirim Titipan</a>
        <a href="{{ route('consignments.create', ['type' => 'sold']) }}" class="btn btn-primary"><i class="bi bi-cash-coin me-1"></i>Lapor Terjual</a>
    </div>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-2">
            <label class="form-label small mb-0">Tipe</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach ($types as $k => $label)
                    <option value="{{ $k }}" @selected(($filters['type'] ?? '') === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-0">Penerima</label>
            <select name="consignee_id" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach ($consignees as $c)
                    <option value="{{ $c->id }}" @selected((string)($filters['consignee_id'] ?? '') === (string)$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-0">Barang</label>
            <select name="product_id" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" @selected((string)($filters['product_id'] ?? '') === (string)$p->id)>{{ $p->code }} — {{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><label class="form-label small mb-0">Dari</label><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm"></div>
        <div class="col-md-2 d-flex flex-column gap-1">
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm" placeholder="Sampai">
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-funnel"></i></button>
                <a href="{{ route('consignments.index') }}" class="btn btn-sm btn-link">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Ref</th>
                <th>Tipe</th>
                <th>Penerima</th>
                <th>Barang</th>
                <th class="text-end">Qty</th>
                <th>Catatan</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($movements as $m)
                <tr>
                    <td class="text-nowrap">{{ $m->date->isoFormat('DD MMM YY') }}</td>
                    <td><code>{{ $m->reference }}</code></td>
                    <td>
                        <span class="badge {{ $m->type === 'send' ? 'bg-success' : 'bg-primary' }}">{{ $m->typeLabel() }}</span>
                    </td>
                    <td>{{ $m->consignee->name }}</td>
                    <td>{{ $m->product->name }}</td>
                    <td class="text-end fw-semibold">@qty($m->quantity) {{ $m->product->unit }}</td>
                    <td class="text-muted small">{{ \Illuminate\Support\Str::limit($m->note, 40) }}</td>
                    <td class="text-end">
                        <a href="{{ route('consignments.show', $m) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        <form action="{{ route('consignments.destroy', $m) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus transaksi ini? {{ $m->type === 'send' ? 'Stok gudang akan dikembalikan.' : '' }}')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada transaksi konsinyasi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $movements->links() }}</div>
</div>
@endsection
