<?php

namespace App\Http\Controllers;

use App\Models\Consignment;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

        // Omzet penjualan langsung = qty terjual x harga jual master (tanpa HPP).
        $salesOmzetMonth = (float) StockMovement::where('stock_movements.type', 'sale')
            ->whereBetween('stock_movements.date', [$startOfMonth, $endOfMonth])
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->sum(DB::raw('stock_movements.quantity * products.sale_price'));

        // Omzet konsinyasi = qty "lapor terjual" x harga jual master konsinyasi.
        $consignmentOmzetMonth = (float) Consignment::where('consignments.type', 'sold')
            ->whereBetween('consignments.date', [$startOfMonth, $endOfMonth])
            ->join('consignment_products', 'consignment_products.id', '=', 'consignments.consignment_product_id')
            ->sum(DB::raw('consignments.quantity * consignment_products.sale_price'));

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
            'salesOmzetMonth',
            'consignmentOmzetMonth',
            'totalProducts',
            'lowStockCount',
            'recentMovements',
            'months'
        ));
    }
}
