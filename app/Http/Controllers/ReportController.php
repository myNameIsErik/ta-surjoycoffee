<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function ledger(Request $request)
    {
        $accounts = Account::orderBy('code')->get();

        $accountId = $request->integer('account_id') ?: $accounts->first()?->id;
        $from = $request->string('from')->toString() ?: Carbon::now()->startOfMonth()->toDateString();
        $to = $request->string('to')->toString() ?: Carbon::now()->endOfMonth()->toDateString();

        $account = $accountId ? Account::find($accountId) : null;

        $entries = collect();
        $openingBalance = 0;
        $runningBalance = 0;
        $totalDebit = 0;
        $totalCredit = 0;

        if ($account) {
            $openingDebit = (float) JournalEntry::where('account_id', $account->id)
                ->whereHas('journal', fn ($q) => $q->where('date', '<', $from))
                ->sum('debit');
            $openingCredit = (float) JournalEntry::where('account_id', $account->id)
                ->whereHas('journal', fn ($q) => $q->where('date', '<', $from))
                ->sum('credit');

            $openingBalance = (float) $account->opening_balance
                + ($account->normal_balance === 'debit'
                    ? ($openingDebit - $openingCredit)
                    : ($openingCredit - $openingDebit));

            $entries = JournalEntry::with('journal')
                ->where('account_id', $account->id)
                ->whereHas('journal', fn ($q) => $q->whereBetween('date', [$from, $to]))
                ->get()
                ->sortBy(fn ($e) => $e->journal->date->format('Y-m-d') . str_pad($e->id, 10, '0', STR_PAD_LEFT))
                ->values();

            $runningBalance = $openingBalance;
            foreach ($entries as $e) {
                $delta = $account->normal_balance === 'debit'
                    ? ((float) $e->debit - (float) $e->credit)
                    : ((float) $e->credit - (float) $e->debit);
                $runningBalance += $delta;
                $e->running_balance = $runningBalance;
                $totalDebit += (float) $e->debit;
                $totalCredit += (float) $e->credit;
            }
        }

        return view('reports.ledger', [
            'accounts' => $accounts,
            'account' => $account,
            'from' => $from,
            'to' => $to,
            'entries' => $entries,
            'openingBalance' => $openingBalance,
            'endingBalance' => $runningBalance,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
        ]);
    }

    public function trialBalance(Request $request)
    {
        $from = $request->string('from')->toString() ?: Carbon::now()->startOfYear()->toDateString();
        $to = $request->string('to')->toString() ?: Carbon::now()->toDateString();

        $accounts = Account::orderBy('code')->get()->map(function ($acc) use ($from, $to) {
            $debit = (float) JournalEntry::where('account_id', $acc->id)
                ->whereHas('journal', fn ($q) => $q->whereBetween('date', [$from, $to]))
                ->sum('debit');
            $credit = (float) JournalEntry::where('account_id', $acc->id)
                ->whereHas('journal', fn ($q) => $q->whereBetween('date', [$from, $to]))
                ->sum('credit');

            $opening = (float) $acc->opening_balance;
            $balance = $acc->normal_balance === 'debit'
                ? $opening + ($debit - $credit)
                : $opening + ($credit - $debit);

            $acc->debit_movement = $debit;
            $acc->credit_movement = $credit;
            $acc->balance_debit = $acc->normal_balance === 'debit' && $balance > 0 ? $balance : ($acc->normal_balance === 'kredit' && $balance < 0 ? -$balance : 0);
            $acc->balance_credit = $acc->normal_balance === 'kredit' && $balance > 0 ? $balance : ($acc->normal_balance === 'debit' && $balance < 0 ? -$balance : 0);

            return $acc;
        })->filter(fn ($a) => $a->debit_movement > 0 || $a->credit_movement > 0 || $a->opening_balance > 0)->values();

        return view('reports.trial-balance', [
            'accounts' => $accounts,
            'from' => $from,
            'to' => $to,
            'totalDebit' => $accounts->sum('balance_debit'),
            'totalCredit' => $accounts->sum('balance_credit'),
        ]);
    }

    public function incomeStatement(Request $request)
    {
        $from = $request->string('from')->toString() ?: Carbon::now()->startOfMonth()->toDateString();
        $to = $request->string('to')->toString() ?: Carbon::now()->endOfMonth()->toDateString();

        $revenueAccounts = $this->accountsByType('pendapatan', $from, $to);
        $expenseAccounts = $this->accountsByType('beban', $from, $to);

        $totalRevenue = $revenueAccounts->sum('period_balance');
        $totalExpense = $expenseAccounts->sum('period_balance');
        $netIncome = $totalRevenue - $totalExpense;

        return view('reports.income-statement', compact(
            'revenueAccounts',
            'expenseAccounts',
            'totalRevenue',
            'totalExpense',
            'netIncome',
            'from',
            'to'
        ));
    }

    public function balanceSheet(Request $request)
    {
        $asOf = $request->string('as_of')->toString() ?: Carbon::now()->toDateString();

        $assets = $this->accountsByType('aset', null, $asOf);
        $liabilities = $this->accountsByType('kewajiban', null, $asOf);
        $equityAccounts = $this->accountsByType('modal', null, $asOf);

        $revenueAccounts = $this->accountsByType('pendapatan', null, $asOf);
        $expenseAccounts = $this->accountsByType('beban', null, $asOf);
        $currentEarnings = $revenueAccounts->sum('period_balance') - $expenseAccounts->sum('period_balance');

        $totalAssets = $assets->sum('period_balance');
        $totalLiabilities = $liabilities->sum('period_balance');
        $totalEquity = $equityAccounts->sum('period_balance') + $currentEarnings;

        return view('reports.balance-sheet', compact(
            'assets',
            'liabilities',
            'equityAccounts',
            'currentEarnings',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'asOf'
        ));
    }

    protected function accountsByType(string $type, ?string $from, ?string $to)
    {
        return Account::where('type', $type)->orderBy('code')->get()->map(function ($acc) use ($from, $to) {
            $q = JournalEntry::where('account_id', $acc->id)->whereHas('journal', function ($q) use ($from, $to) {
                if ($from) $q->where('date', '>=', $from);
                if ($to) $q->where('date', '<=', $to);
            });

            $debit = (float) (clone $q)->sum('debit');
            $credit = (float) (clone $q)->sum('credit');

            $balance = $acc->normal_balance === 'debit'
                ? ($debit - $credit)
                : ($credit - $debit);

            $acc->period_balance = $balance + (float) $acc->opening_balance;

            return $acc;
        })->filter(fn ($a) => abs($a->period_balance) > 0.001)->values();
    }
}
