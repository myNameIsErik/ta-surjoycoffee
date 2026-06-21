<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // Total penjualan & pembelian bulan ini
        $salesMonth = StockMovement::where('type', 'sale')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('total_price');

        $purchaseMonth = StockMovement::where('type', 'purchase')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('total_cost');

        // Laba kotor bulan ini = revenue - HPP (cost)
        $cogsMonth = StockMovement::where('type', 'sale')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('total_cost');
        $grossProfitMonth = $salesMonth - $cogsMonth;

        // Total nilai stok saat ini
        $totalStockValue = Product::all()->sum(fn ($p) => (float) $p->stock * (float) $p->cost_price);

        // Jumlah produk stok rendah
        $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->where('is_active', true)
            ->count();

        $recentMovements = StockMovement::with('product')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        // Tren 6 bulan terakhir
        $months = collect(range(5, 0))->map(function ($i) {
            $start = Carbon::now()->startOfMonth()->subMonths($i);
            $end = (clone $start)->endOfMonth();
            $from = $start->toDateString();
            $to = $end->toDateString();

            $sales = (float) StockMovement::where('type', 'sale')
                ->whereBetween('date', [$from, $to])->sum('total_price');
            $purchase = (float) StockMovement::where('type', 'purchase')
                ->whereBetween('date', [$from, $to])->sum('total_cost');

            return [
                'label' => $start->isoFormat('MMM YY'),
                'sales' => $sales,
                'purchase' => $purchase,
            ];
        });

        return view('dashboard', compact(
            'salesMonth',
            'purchaseMonth',
            'grossProfitMonth',
            'totalStockValue',
            'lowStockCount',
            'recentMovements',
            'months'
        ));
    }
}
