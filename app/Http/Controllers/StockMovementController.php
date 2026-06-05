<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Journal;
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
        $query = StockMovement::with(['product', 'journal'])->orderByDesc('date')->orderByDesc('id');

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
            'paymentAccounts' => Account::whereIn('code', ['1101', '1102'])->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(StockMovement::TYPES))],
            'date' => ['required', 'date'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'payment_account_id' => ['nullable', 'exists:accounts,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        $needsPayment = in_array($data['type'], ['purchase', 'sale']);
        if ($needsPayment && empty($data['payment_account_id'])) {
            throw ValidationException::withMessages(['payment_account_id' => 'Akun Kas/Bank wajib dipilih.']);
        }

        if (in_array($data['type'], ['sale', 'adjustment_out']) && $data['quantity'] > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Stok tidak mencukupi. Stok saat ini: {$product->stock} {$product->unit}.",
            ]);
        }

        $unitCost = (float) ($data['unit_cost'] ?? $product->cost_price);
        $unitPrice = (float) ($data['unit_price'] ?? $product->sale_price);

        DB::transaction(function () use ($data, $product, $unitCost, $unitPrice) {
            $qty = (float) $data['quantity'];
            $totalCost = $unitCost * $qty;
            $totalPrice = $unitPrice * $qty;

            $movement = StockMovement::create([
                'reference' => StockMovement::generateReference($data['type']),
                'date' => $data['date'],
                'type' => $data['type'],
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'unit_price' => $unitPrice,
                'total_cost' => $totalCost,
                'total_price' => $totalPrice,
                'payment_account_id' => $data['payment_account_id'] ?? null,
                'user_id' => auth()->id(),
                'note' => $data['note'] ?? null,
            ]);

            $this->updateStockAndCost($product, $movement);

            $journal = $this->generateJournal($product, $movement);
            if ($journal) {
                $movement->update(['journal_id' => $journal->id]);
            }
        });

        return redirect()->route('stock.index')->with('success', 'Transaksi stok berhasil dicatat & jurnal otomatis dibuat.');
    }

    public function show(StockMovement $stock)
    {
        $stock->load(['product', 'journal.entries.account', 'paymentAccount']);

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

            if ($stock->journal) {
                $stock->journal->delete();
            }
            $stock->delete();
        });

        return back()->with('success', 'Transaksi stok berhasil dihapus & jurnal ikut dibatalkan.');
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
        $products = Product::with('inventoryAccount')->orderBy('code')->get()->map(function ($p) {
            $p->stock_value = (float) $p->stock * (float) $p->cost_price;
            return $p;
        });

        return view('stock.report', [
            'products' => $products,
            'totalValue' => $products->sum('stock_value'),
        ]);
    }

    protected function updateStockAndCost(Product $product, StockMovement $movement): void
    {
        $qty = (float) $movement->quantity;

        if ($movement->type === 'purchase') {
            $newQty = (float) $product->stock + $qty;
            if ($newQty > 0) {
                $oldValue = (float) $product->stock * (float) $product->cost_price;
                $newValue = $qty * (float) $movement->unit_cost;
                $product->cost_price = ($oldValue + $newValue) / $newQty;
            }
            $product->stock = $newQty;
        } elseif ($movement->type === 'adjustment_in') {
            $product->stock = (float) $product->stock + $qty;
        } elseif (in_array($movement->type, ['sale', 'adjustment_out'])) {
            $product->stock = (float) $product->stock - $qty;
        }

        $product->save();
    }

    protected function generateJournal(Product $product, StockMovement $movement): ?Journal
    {
        $lines = [];
        $desc = '';

        switch ($movement->type) {
            case 'purchase':
                $lines = [
                    ['account_id' => $product->inventory_account_id, 'debit' => $movement->total_cost, 'credit' => 0],
                    ['account_id' => $movement->payment_account_id,   'debit' => 0, 'credit' => $movement->total_cost],
                ];
                $desc = "Pembelian {$product->name} ({$this->formatQty($movement->quantity)} {$product->unit})";
                break;

            case 'sale':
                $lines = [
                    ['account_id' => $movement->payment_account_id,    'debit' => $movement->total_price, 'credit' => 0],
                    ['account_id' => $product->revenue_account_id,     'debit' => 0, 'credit' => $movement->total_price],
                    ['account_id' => $product->cogs_account_id,        'debit' => $movement->total_cost, 'credit' => 0],
                    ['account_id' => $product->inventory_account_id,   'debit' => 0, 'credit' => $movement->total_cost],
                ];
                $desc = "Penjualan {$product->name} ({$this->formatQty($movement->quantity)} {$product->unit})";
                break;

            case 'adjustment_in':
                $lines = [
                    ['account_id' => $product->inventory_account_id, 'debit' => $movement->total_cost, 'credit' => 0],
                    ['account_id' => $this->incomeOtherAccountId(),  'debit' => 0, 'credit' => $movement->total_cost],
                ];
                $desc = "Koreksi tambah stok {$product->name} ({$this->formatQty($movement->quantity)} {$product->unit})";
                break;

            case 'adjustment_out':
                $lines = [
                    ['account_id' => $this->expenseOtherAccountId(), 'debit' => $movement->total_cost, 'credit' => 0],
                    ['account_id' => $product->inventory_account_id, 'debit' => 0, 'credit' => $movement->total_cost],
                ];
                $desc = "Koreksi kurang stok {$product->name} ({$this->formatQty($movement->quantity)} {$product->unit})";
                break;
        }

        $lines = array_values(array_filter($lines, fn ($l) => $l['debit'] > 0 || $l['credit'] > 0));
        if (count($lines) < 2) {
            return null;
        }

        $journal = Journal::create([
            'reference' => Journal::generateReference(),
            'date' => $movement->date,
            'description' => $desc . ' [' . $movement->reference . ']',
            'total' => collect($lines)->sum('debit'),
            'user_id' => auth()->id(),
        ]);

        foreach ($lines as $line) {
            $journal->entries()->create([
                'account_id' => $line['account_id'],
                'debit' => $line['debit'],
                'credit' => $line['credit'],
                'memo' => $movement->reference,
            ]);
        }

        return $journal;
    }

    protected function formatQty($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
    }

    protected function incomeOtherAccountId(): int
    {
        return Account::where('code', '4103')->value('id')
            ?? Account::where('type', 'pendapatan')->orderBy('code')->value('id');
    }

    protected function expenseOtherAccountId(): int
    {
        return Account::where('code', '5109')->value('id')
            ?? Account::where('type', 'beban')->orderBy('code')->value('id');
    }
}
