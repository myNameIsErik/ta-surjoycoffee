<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuickTransactionController extends Controller
{
    public function create(Request $request)
    {
        $type = $request->string('type')->toString() ?: 'income';
        abort_unless(in_array($type, ['income', 'expense']), 404);

        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        return view('quick.create', [
            'type' => $type,
            'categoryAccounts' => $accounts->where('type', $type === 'income' ? 'pendapatan' : 'beban')->values(),
            'paymentAccounts' => $accounts->whereIn('code', ['1101', '1102'])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['income', 'expense'])],
            'date' => ['required', 'date'],
            'category_account_id' => ['required', 'exists:accounts,id'],
            'payment_account_id' => ['required', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $category = Account::findOrFail($data['category_account_id']);
        $payment = Account::findOrFail($data['payment_account_id']);

        if ($data['type'] === 'income') {
            $debitAccountId = $payment->id;
            $creditAccountId = $category->id;
        } else {
            $debitAccountId = $category->id;
            $creditAccountId = $payment->id;
        }

        DB::transaction(function () use ($data, $debitAccountId, $creditAccountId) {
            $journal = Journal::create([
                'reference' => Journal::generateReference(),
                'date' => $data['date'],
                'description' => $data['description'],
                'total' => $data['amount'],
                'user_id' => auth()->id(),
            ]);

            $journal->entries()->create([
                'account_id' => $debitAccountId,
                'debit' => $data['amount'],
                'credit' => 0,
            ]);
            $journal->entries()->create([
                'account_id' => $creditAccountId,
                'debit' => 0,
                'credit' => $data['amount'],
            ]);
        });

        $label = $data['type'] === 'income' ? 'pemasukan' : 'pengeluaran';

        return redirect()
            ->route('quick.create', ['type' => $data['type']])
            ->with('success', "Transaksi {$label} berhasil dicatat & jurnal otomatis dibuat.");
    }
}
