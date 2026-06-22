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

        $salesQtyMonth = (float) StockMovement::where('type', 'sale')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('quantity');

        $purchaseQtyMonth = (float) StockMovement::where('type', 'purchase')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('quantity');

        $totalProducts = Product::where('is_active', true)->count();
        $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->where('is_active', true)
            ->count();

        $recentMovements = StockMovement::with('product')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $months = collect(range(5, 0))->map(function ($i) {
            $start = Carbon::now()->startOfMonth()->subMonths($i);
            $end = (clone $start)->endOfMonth();
            $from = $start->toDateString();
            $to = $end->toDateString();

            $sales = (float) StockMovement::where('type', 'sale')
                ->whereBetween('date', [$from, $to])->sum('quantity');
            $purchase = (float) StockMovement::where('type', 'purchase')
                ->whereBetween('date', [$from, $to])->sum('quantity');

            return [
                'label' => $start->isoFormat('MMM YY'),
                'sales' => $sales,
                'purchase' => $purchase,
            ];
        });

        return view('dashboard', compact(
            'salesQtyMonth',
            'purchaseQtyMonth',
            'totalProducts',
            'lowStockCount',
            'recentMovements',
            'months'
        ));
    }
}
