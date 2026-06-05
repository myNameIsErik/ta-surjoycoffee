<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $query = Journal::with('entries.account')->orderByDesc('date')->orderByDesc('id');

        if ($from = $request->string('from')->toString()) {
            $query->where('date', '>=', $from);
        }
        if ($to = $request->string('to')->toString()) {
            $query->where('date', '<=', $to);
        }
        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $journals = $query->paginate(15)->withQueryString();

        return view('journals.index', [
            'journals' => $journals,
            'filters' => $request->only('from', 'to', 'q'),
        ]);
    }

    public function create()
    {
        return view('journals.form', [
            'journal' => new Journal(['date' => now()->toDateString()]),
            'entries' => collect(),
            'accounts' => Account::where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::transaction(function () use ($data) {
            $journal = Journal::create([
                'reference' => Journal::generateReference(),
                'date' => $data['date'],
                'description' => $data['description'],
                'user_id' => auth()->id(),
                'total' => collect($data['lines'])->sum('debit'),
            ]);

            foreach ($data['lines'] as $line) {
                $journal->entries()->create([
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'memo' => $line['memo'] ?? null,
                ]);
            }
        });

        return redirect()->route('journals.index')->with('success', 'Jurnal berhasil disimpan.');
    }

    public function show(Journal $journal)
    {
        $journal->load('entries.account');

        return view('journals.show', compact('journal'));
    }

    public function edit(Journal $journal)
    {
        $journal->load('entries.account');

        return view('journals.form', [
            'journal' => $journal,
            'entries' => $journal->entries,
            'accounts' => Account::where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function update(Request $request, Journal $journal)
    {
        $data = $this->validateData($request);

        DB::transaction(function () use ($journal, $data) {
            $journal->update([
                'date' => $data['date'],
                'description' => $data['description'],
                'total' => collect($data['lines'])->sum('debit'),
            ]);
            $journal->entries()->delete();
            foreach ($data['lines'] as $line) {
                $journal->entries()->create([
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'memo' => $line['memo'] ?? null,
                ]);
            }
        });

        return redirect()->route('journals.show', $journal)->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function destroy(Journal $journal)
    {
        $journal->delete();

        return redirect()->route('journals.index')->with('success', 'Jurnal berhasil dihapus.');
    }

    protected function validateData(Request $request): array
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:accounts,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.memo' => ['nullable', 'string', 'max:255'],
        ]);

        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($data['lines'] as $i => $line) {
            $debit = (float) ($line['debit'] ?? 0);
            $credit = (float) ($line['credit'] ?? 0);

            if ($debit <= 0 && $credit <= 0) {
                throw ValidationException::withMessages([
                    "lines.$i.debit" => 'Setiap baris harus memiliki nilai debit atau kredit.',
                ]);
            }
            if ($debit > 0 && $credit > 0) {
                throw ValidationException::withMessages([
                    "lines.$i.debit" => 'Satu baris hanya boleh diisi debit atau kredit, tidak keduanya.',
                ]);
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw ValidationException::withMessages([
                'lines' => 'Total debit dan kredit harus sama (saat ini: Rp '
                    . number_format($totalDebit, 0, ',', '.') . ' vs Rp '
                    . number_format($totalCredit, 0, ',', '.') . ').',
            ]);
        }

        return $data;
    }
}
