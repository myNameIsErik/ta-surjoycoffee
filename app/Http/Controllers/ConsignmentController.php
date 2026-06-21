<?php

namespace App\Http\Controllers;

use App\Models\Consignee;
use App\Models\Consignment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ConsignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Consignment::with(['consignee', 'product'])
            ->orderByDesc('date')->orderByDesc('id');

        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }
        if ($consigneeId = $request->integer('consignee_id')) {
            $query->where('consignee_id', $consigneeId);
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

        return view('consignments.index', [
            'movements' => $movements,
            'consignees' => Consignee::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'types' => Consignment::TYPES,
            'filters' => $request->only('type', 'consignee_id', 'product_id', 'from', 'to'),
        ]);
    }

    public function create(Request $request)
    {
        $type = $request->string('type')->toString() ?: 'send';
        abort_unless(array_key_exists($type, Consignment::TYPES), 404);

        return view('consignments.form', [
            'type' => $type,
            'types' => Consignment::TYPES,
            'consignees' => Consignee::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(Consignment::TYPES))],
            'date' => ['required', 'date'],
            'consignee_id' => ['required', 'exists:consignees,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $qty = (float) $data['quantity'];

        if ($data['type'] === 'send') {
            // Validasi stok gudang cukup
            if ($qty > (float) $product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok gudang tidak cukup. Saat ini: {$product->stock} {$product->unit}.",
                ]);
            }
        } else { // sold
            // Validasi outstanding qty di consignee cukup
            $outstanding = Consignment::outstanding($data['consignee_id'], $data['product_id']);
            if ($qty > $outstanding) {
                throw ValidationException::withMessages([
                    'quantity' => "Sisa titipan di penerima ini hanya {$outstanding} {$product->unit}.",
                ]);
            }
        }

        $unitCost = (float) $product->cost_price;
        $unitPrice = (float) ($data['unit_price'] ?? $product->sale_price);

        DB::transaction(function () use ($data, $product, $qty, $unitCost, $unitPrice) {
            $totalCost = $unitCost * $qty;
            $totalPrice = $unitPrice * $qty;

            Consignment::create([
                'reference' => Consignment::generateReference($data['type']),
                'date' => $data['date'],
                'type' => $data['type'],
                'consignee_id' => $data['consignee_id'],
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'unit_price' => $unitPrice,
                'total_cost' => $totalCost,
                'total_price' => $totalPrice,
                'user_id' => auth()->id(),
                'note' => $data['note'] ?? null,
            ]);

            // Saat KIRIM titipan, stok gudang berkurang. Saat LAPOR TERJUAL, stok gudang tetap (sudah keluar saat kirim).
            if ($data['type'] === 'send') {
                $product->decrement('stock', $qty);
            }
        });

        $label = $data['type'] === 'send' ? 'Kirim titipan' : 'Lapor terjual';

        return redirect()->route('consignments.index')->with('success', "{$label} berhasil dicatat.");
    }

    public function show(Consignment $consignment)
    {
        $consignment->load(['consignee', 'product']);

        return view('consignments.show', ['movement' => $consignment]);
    }

    /** AJAX: kembalikan outstanding qty per (consignee, product) untuk validasi UI. */
    public function outstanding(Request $request)
    {
        $consigneeId = $request->integer('consignee_id');
        $productId = $request->integer('product_id');
        if (!$consigneeId || !$productId) {
            return response()->json(['outstanding' => 0]);
        }

        return response()->json(['outstanding' => Consignment::outstanding($consigneeId, $productId)]);
    }

    public function destroy(Consignment $consignment)
    {
        DB::transaction(function () use ($consignment) {
            // Rollback stok kalau type=send
            if ($consignment->type === 'send') {
                $consignment->product->increment('stock', (float) $consignment->quantity);
            }
            $consignment->delete();
        });

        return back()->with('success', 'Transaksi konsinyasi dihapus & stok dikembalikan (untuk pengiriman).');
    }

    /**
     * Laporan Posisi Konsinyasi — per penerima, list barang outstanding.
     */
    public function position(Request $request)
    {
        $consigneeId = $request->integer('consignee_id');

        $query = Consignee::with(['consignments.product'])->where('is_active', true)->orderBy('name');
        if ($consigneeId) {
            $query->where('id', $consigneeId);
        }
        $consignees = $query->get()->map(function ($c) {
            $rows = $c->outstandingByProduct();
            $c->outstanding_rows = $rows;
            $c->outstanding_total_cost = $rows->sum(fn ($r) => $r['outstanding'] * (float) $r['product']->cost_price);
            $c->outstanding_total_price = $rows->sum(fn ($r) => $r['outstanding'] * (float) $r['product']->sale_price);
            return $c;
        })->filter(fn ($c) => $c->outstanding_rows->count() > 0)->values();

        $grandTotalCost = $consignees->sum('outstanding_total_cost');
        $grandTotalPrice = $consignees->sum('outstanding_total_price');

        return view('consignments.position', [
            'consignees' => $consignees,
            'allConsignees' => Consignee::orderBy('name')->get(),
            'filters' => ['consignee_id' => $consigneeId],
            'grandTotalCost' => $grandTotalCost,
            'grandTotalPrice' => $grandTotalPrice,
        ]);
    }
}
