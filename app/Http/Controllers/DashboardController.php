<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Journal;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $revenueMonth = (float) JournalEntry::whereHas('account', fn ($q) => $q->where('type', 'pendapatan'))
            ->whereHas('journal', fn ($q) => $q->whereBetween('date', [$startOfMonth, $endOfMonth]))
            ->selectRaw('COALESCE(SUM(credit) - SUM(debit),0) as total')
            ->value('total');

        $expenseMonth = (float) JournalEntry::whereHas('account', fn ($q) => $q->where('type', 'beban'))
            ->whereHas('journal', fn ($q) => $q->whereBetween('date', [$startOfMonth, $endOfMonth]))
            ->selectRaw('COALESCE(SUM(debit) - SUM(credit),0) as total')
            ->value('total');

        $profitMonth = $revenueMonth - $expenseMonth;

        $cashAccounts = Account::whereIn('code', ['1101', '1102'])->get();
        $cashBalance = $cashAccounts->sum(fn ($a) => $a->balance());

        $recentJournals = Journal::with('entries.account')->latest('date')->latest('id')->limit(5)->get();

        $months = collect(range(5, 0))->map(function ($i) {
            $start = Carbon::now()->startOfMonth()->subMonths($i);
            $end = (clone $start)->endOfMonth();

            $rev = (float) JournalEntry::whereHas('account', fn ($q) => $q->where('type', 'pendapatan'))
                ->whereHas('journal', fn ($q) => $q->whereBetween('date', [$start->toDateString(), $end->toDateString()]))
                ->selectRaw('COALESCE(SUM(credit) - SUM(debit),0) as total')->value('total');
            $exp = (float) JournalEntry::whereHas('account', fn ($q) => $q->where('type', 'beban'))
                ->whereHas('journal', fn ($q) => $q->whereBetween('date', [$start->toDateString(), $end->toDateString()]))
                ->selectRaw('COALESCE(SUM(debit) - SUM(credit),0) as total')->value('total');

            return [
                'label' => $start->isoFormat('MMM YY'),
                'revenue' => (float) $rev,
                'expense' => (float) $exp,
            ];
        });

        return view('dashboard', compact(
            'revenueMonth',
            'expenseMonth',
            'profitMonth',
            'cashBalance',
            'recentJournals',
            'months'
        ));
    }
}
