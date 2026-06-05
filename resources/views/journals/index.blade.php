@extends('layouts.app')
@section('title', 'Jurnal Umum')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-journal-text me-1"></i> Jurnal Umum</h4>
    <a href="{{ route('journals.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Tambah Jurnal</a>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2" method="GET">
        <div class="col-md-3">
            <label class="form-label small mb-0">Dari Tanggal</label>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-0">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-4">
            <label class="form-label small mb-0">Cari</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="No. ref / keterangan">
        </div>
        <div class="col-md-2 d-flex align-items-end gap-1">
            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-funnel"></i></button>
            <a href="{{ route('journals.index') }}" class="btn btn-sm btn-link">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Referensi</th>
                <th>Keterangan</th>
                <th>Detail Akun</th>
                <th class="text-end">Total</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($journals as $j)
                <tr>
                    <td class="text-nowrap">{{ $j->date->isoFormat('DD MMM YYYY') }}</td>
                    <td><code>{{ $j->reference }}</code></td>
                    <td>{{ $j->description }}</td>
                    <td>
                        <small class="text-muted">
                            @foreach ($j->entries as $e)
                                {{ $e->account->code }} {{ $e->account->name }}
                                ({{ $e->debit > 0 ? 'D' : 'K' }})@if(!$loop->last), @endif
                            @endforeach
                        </small>
                    </td>
                    <td class="text-end fw-semibold">@rupiah($j->total)</td>
                    <td class="text-end">
                        <a href="{{ route('journals.show', $j) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('journals.edit', $j) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('journals.destroy', $j) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jurnal ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada jurnal. <a href="{{ route('journals.create') }}">Buat sekarang</a>.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $journals->links() }}</div>
</div>
@endsection
