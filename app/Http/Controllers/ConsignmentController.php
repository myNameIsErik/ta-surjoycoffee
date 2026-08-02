<?php

namespace App\Http\Controllers;

use App\Models\Consignee;
use App\Models\Consignment;
use App\Models\ConsignmentProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ConsignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Consignment::with(['consignee', 'consignmentProduct'])
            ->orderByDesc('date')->orderByDesc('id');

        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }
        if ($consigneeId = $request->integer('consignee_id')) {
            $query->where('consignee_id', $consigneeId);
        }
        if ($productId = $request->integer('consignment_product_id')) {
            $query->where('consignment_product_id', $productId);
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
            'products' => ConsignmentProduct::orderBy('name')->get(),
            'types' => Consignment::TYPES,
            'filters' => $request->only('type', 'consignee_id', 'consignment_product_id', 'from', 'to'),
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
            'products' => ConsignmentProduct::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $type = $request->string('type')->toString();

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(Consignment::TYPES))],
            'date' => ['required', 'date'],
            // Penerima wajib untuk kirim/terjual; tidak dipakai untuk stok masuk.
            'consignee_id' => [Rule::requiredIf(in_array($type, ['send', 'sold'])), 'nullable', 'exists:consignees,id'],
            'consignment_product_id' => ['required', 'exists:consignment_products,id'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $product = ConsignmentProduct::findOrFail($data['consignment_product_id']);
        $qty = (float) $data['quantity'];
        $consigneeId = in_array($data['type'], ['send', 'sold']) ? $data['consignee_id'] : null;

        if ($data['type'] === 'send') {
            if ($qty > (float) $product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok gudang konsinyasi tidak cukup. Saat ini: {$product->stock} {$product->unit}.",
                ]);
            }
        } elseif ($data['type'] === 'sold') {
            $outstanding = Consignment::outstanding((int) $consigneeId, (int) $product->id);
            if ($qty > $outstanding) {
                throw ValidationException::withMessages([
                    'quantity' => "Sisa titipan di penerima ini hanya {$outstanding} {$product->unit}.",
                ]);
            }
        }

        DB::transaction(function () use ($data, $product, $qty, $consigneeId) {
            Consignment::create([
                'reference' => Consignment::generateReference($data['type']),
                'date' => $data['date'],
                'type' => $data['type'],
                'consignee_id' => $consigneeId,
                'consignment_product_id' => $product->id,
                'quantity' => $qty,
                'user_id' => auth()->id(),
                'note' => $data['note'] ?? null,
            ]);

            // Stok gudang konsinyasi: naik saat STOK MASUK, turun saat KIRIM titipan.
            // Saat LAPOR TERJUAL stok gudang tetap (sudah keluar saat kirim).
            if ($data['type'] === 'stock_in') {
                $product->increment('stock', $qty);
            } elseif ($data['type'] === 'send') {
                $product->decrement('stock', $qty);
            }
        });

        $label = Consignment::TYPES[$data['type']];

        return redirect()->route('consignments.index')->with('success', "{$label} berhasil dicatat.");
    }

    public function show(Consignment $consignment)
    {
        $consignment->load(['consignee', 'consignmentProduct']);

        return view('consignments.show', ['movement' => $consignment]);
    }

    /** AJAX: kembalikan outstanding qty per (consignee, consignment_product) untuk validasi UI. */
    public function outstanding(Request $request)
    {
        $consigneeId = $request->integer('consignee_id');
        $productId = $request->integer('consignment_product_id');
        if (! $consigneeId || ! $productId) {
            return response()->json(['outstanding' => 0]);
        }

        return response()->json(['outstanding' => Consignment::outstanding($consigneeId, $productId)]);
    }

    public function destroy(Consignment $consignment)
    {
        DB::transaction(function () use ($consignment) {
            $product = $consignment->consignmentProduct;
            $qty = (float) $consignment->quantity;

            // Rollback stok gudang: stok masuk -> kurangi lagi, kirim -> kembalikan.
            if ($consignment->type === 'stock_in') {
                $product?->decrement('stock', $qty);
            } elseif ($consignment->type === 'send') {
                $product?->increment('stock', $qty);
            }

            $consignment->delete();
        });

        return back()->with('success', 'Transaksi konsinyasi dihapus & stok disesuaikan kembali.');
    }

    /**
     * Laporan Posisi Konsinyasi — per penerima, list barang outstanding + nilainya.
     */
    public function position(Request $request)
    {
        $consigneeId = $request->integer('consignee_id');

        $query = Consignee::with(['consignments.consignmentProduct'])->where('is_active', true)->orderBy('name');
        if ($consigneeId) {
            $query->where('id', $consigneeId);
        }
        $consignees = $query->get()->map(function ($c) {
            $rows = $c->outstandingByProduct();
            $c->outstanding_rows = $rows;
            $c->outstanding_total_qty = $rows->sum(fn ($r) => $r['outstanding']);
            $c->outstanding_total_value = $rows->sum(fn ($r) => $r['outstanding'] * (float) ($r['product']->sale_price ?? 0));
            return $c;
        })->filter(fn ($c) => $c->outstanding_rows->count() > 0)->values();

        $totalConsignees = $consignees->count();
        $totalLines = $consignees->sum(fn ($c) => $c->outstanding_rows->count());
        $totalValue = $consignees->sum(fn ($c) => $c->outstanding_total_value);

        return view('consignments.position', [
            'consignees' => $consignees,
            'allConsignees' => Consignee::orderBy('name')->get(),
            'filters' => ['consignee_id' => $consigneeId],
            'totalConsignees' => $totalConsignees,
            'totalLines' => $totalLines,
            'totalValue' => $totalValue,
        ]);
    }
}
