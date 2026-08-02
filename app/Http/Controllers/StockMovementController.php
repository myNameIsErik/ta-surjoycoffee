<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['product'])->orderByDesc('date')->orderByDesc('id');

        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }
        if ($productId = $request->integer('product_id')) {
            $query->where('product_id', $productId);
        }
        if ($from = $request->string('from')->toString()) {
            $query->where('date', '>=', $from);
        }
        if ($to = $request->string('to')->toString()) {
            $query->where('date', '<=', $to);
        }

        $movements = $query->paginate(20)->withQueryString();

        return view('stock.index', [
            'movements' => $movements,
            'products' => Product::orderBy('name')->get(),
            'types' => StockMovement::TYPES,
            'filters' => $request->only('type', 'product_id', 'from', 'to'),
        ]);
    }

    public function create(Request $request)
    {
        $type = $request->string('type')->toString() ?: 'purchase';
        abort_unless(array_key_exists($type, StockMovement::TYPES), 404);

        return view('stock.form', [
            'type' => $type,
            'types' => StockMovement::TYPES,
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(StockMovement::TYPES))],
            'date' => ['required', 'date'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        if (in_array($data['type'], ['sale', 'adjustment_out']) && $data['quantity'] > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Stok tidak mencukupi. Stok saat ini: {$product->stock} {$product->unit}.",
            ]);
        }

        DB::transaction(function () use ($data, $product) {
            $qty = (float) $data['quantity'];

            $movement = StockMovement::create([
                'reference' => StockMovement::generateReference($data['type']),
                'date' => $data['date'],
                'type' => $data['type'],
                'product_id' => $product->id,
                'quantity' => $qty,
                'user_id' => auth()->id(),
                'note' => $data['note'] ?? null,
            ]);

            $this->updateStock($product, $movement);
        });

        return redirect()->route('stock.index')->with('success', 'Transaksi stok berhasil dicatat.');
    }

    public function show(StockMovement $stock)
    {
        $stock->load(['product']);

        return view('stock.show', ['movement' => $stock]);
    }

    public function destroy(StockMovement $stock)
    {
        DB::transaction(function () use ($stock) {
            $product = $stock->product;
            $qty = (float) $stock->quantity;

            if ($stock->isIncoming()) {
                $product->decrement('stock', $qty);
            } else {
                $product->increment('stock', $qty);
            }

            $stock->delete();
        });

        return back()->with('success', 'Transaksi stok berhasil dihapus & stok dikembalikan.');
    }

    public function card(Product $product, Request $request)
    {
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $query = $product->movements();
        if ($from) $query->where('date', '>=', $from);
        if ($to) $query->where('date', '<=', $to);

        $movements = $query->get();

        $opening = (float) StockMovement::where('product_id', $product->id)
            ->when($from, fn ($q) => $q->where('date', '<', $from))
            ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('purchase','adjustment_in') THEN quantity ELSE -quantity END), 0) as total")
            ->value('total');

        $running = $opening;
        foreach ($movements as $m) {
            $delta = $m->isIncoming() ? (float) $m->quantity : -(float) $m->quantity;
            $running += $delta;
            $m->running_stock = $running;
        }

        return view('stock.card', [
            'product' => $product,
            'movements' => $movements,
            'opening' => $opening,
            'ending' => $running,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function report(Request $request)
    {
        $products = Product::with('category')->orderBy('code')->get();

        return view('stock.report', [
            'products' => $products,
            'totalProducts' => $products->count(),
            'lowStockCount' => $products->filter(fn ($p) => $p->isLowStock())->count(),
        ]);
    }

    public function salesReport(Request $request)
    {
        $from = $request->string('from')->toString() ?: now()->startOfMonth()->toDateString();
        $to = $request->string('to')->toString() ?: now()->endOfMonth()->toDateString();
        $productId = $request->integer('product_id');

        $query = StockMovement::with('product')
            ->where('type', 'sale')
            ->whereBetween('date', [$from, $to])
            ->orderBy('date');

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $movements = $query->get();

        return view('stock.sales-report', [
            'movements' => $movements,
            'products' => Product::orderBy('name')->get(),
            'filters' => compact('from', 'to', 'productId'),
            'from' => $from,
            'to' => $to,
            'totalQty' => $movements->sum('quantity'),
            'totalTransactions' => $movements->count(),
            'totalOmzet' => $movements->sum(fn ($m) => (float) $m->quantity * (float) ($m->product->sale_price ?? 0)),
        ]);
    }

    public function purchaseReport(Request $request)
    {
        $from = $request->string('from')->toString() ?: now()->startOfMonth()->toDateString();
        $to = $request->string('to')->toString() ?: now()->endOfMonth()->toDateString();
        $productId = $request->integer('product_id');

        $query = StockMovement::with('product')
            ->where('type', 'purchase')
            ->whereBetween('date', [$from, $to])
            ->orderBy('date');

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $movements = $query->get();

        return view('stock.purchase-report', [
            'movements' => $movements,
            'products' => Product::orderBy('name')->get(),
            'filters' => compact('from', 'to', 'productId'),
            'from' => $from,
            'to' => $to,
            'totalQty' => $movements->sum('quantity'),
            'totalTransactions' => $movements->count(),
        ]);
    }

    protected function updateStock(Product $product, StockMovement $movement): void
    {
        $qty = (float) $movement->quantity;

        if (in_array($movement->type, ['purchase', 'adjustment_in'])) {
            $product->stock = (float) $product->stock + $qty;
        } elseif (in_array($movement->type, ['sale', 'adjustment_out'])) {
            $product->stock = (float) $product->stock - $qty;
        }

        $product->save();
    }
}
