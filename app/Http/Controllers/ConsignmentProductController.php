<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentProduct;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConsignmentProductController extends Controller
{
    public function index(Request $request)
    {
        $query = ConsignmentProduct::orderBy('code');

        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }
        if ($status = $request->string('status')->toString()) {
            $query->where('is_active', $status === 'active');
        }
        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock', '<=', 'min_stock')->where('min_stock', '>', 0);
        }

        $products = $query->paginate(15)->withQueryString();

        return view('consignment-products.index', [
            'products' => $products,
            'filters' => $request->only('q', 'status', 'low_stock'),
        ]);
    }

    public function create()
    {
        return view('consignment-products.form', [
            'product' => new ConsignmentProduct(['is_active' => true, 'unit' => 'pcs']),
        ]);
    }

    public function store(Request $request)
    {
        ConsignmentProduct::create($this->validateData($request));

        return redirect()->route('consignment-products.index')->with('success', 'Barang konsinyasi berhasil ditambahkan.');
    }

    public function edit(ConsignmentProduct $consignment_product)
    {
        return view('consignment-products.form', ['product' => $consignment_product]);
    }

    public function update(Request $request, ConsignmentProduct $consignment_product)
    {
        $consignment_product->update($this->validateData($request, $consignment_product->id));

        return redirect()->route('consignment-products.index')->with('success', 'Barang konsinyasi berhasil diperbarui.');
    }

    public function destroy(ConsignmentProduct $consignment_product)
    {
        if ($consignment_product->consignments()->exists()) {
            return back()->with('error', 'Barang tidak dapat dihapus karena sudah memiliki transaksi konsinyasi.');
        }
        $consignment_product->delete();

        return redirect()->route('consignment-products.index')->with('success', 'Barang konsinyasi berhasil dihapus.');
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('consignment_products', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:20'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
