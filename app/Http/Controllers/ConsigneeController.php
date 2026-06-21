<?php

namespace App\Http\Controllers;

use App\Models\Consignee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConsigneeController extends Controller
{
    public function index(Request $request)
    {
        $query = Consignee::withCount('consignments')->orderBy('name');

        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }
        if ($status = $request->string('status')->toString()) {
            $query->where('is_active', $status === 'active');
        }

        $consignees = $query->paginate(15)->withQueryString();

        return view('consignees.index', [
            'consignees' => $consignees,
            'filters' => $request->only('q', 'status'),
        ]);
    }

    public function create()
    {
        return view('consignees.form', [
            'consignee' => new Consignee(['is_active' => true]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Consignee::create($data);

        return redirect()->route('consignees.index')->with('success', 'Penerima konsinyasi berhasil ditambahkan.');
    }

    public function show(Consignee $consignee)
    {
        return redirect()->route('consignees.edit', $consignee);
    }

    public function edit(Consignee $consignee)
    {
        return view('consignees.form', ['consignee' => $consignee]);
    }

    public function update(Request $request, Consignee $consignee)
    {
        $data = $this->validateData($request, $consignee->id);
        $consignee->update($data);

        return redirect()->route('consignees.index')->with('success', 'Penerima konsinyasi berhasil diperbarui.');
    }

    public function destroy(Consignee $consignee)
    {
        if ($consignee->consignments()->exists()) {
            return back()->with('error', 'Penerima tidak dapat dihapus karena sudah memiliki transaksi.');
        }

        $consignee->delete();

        return redirect()->route('consignees.index')->with('success', 'Penerima konsinyasi berhasil dihapus.');
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('consignees', 'name')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
