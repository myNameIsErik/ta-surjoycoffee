<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::query();

        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }
        if ($status = $request->string('status')->toString()) {
            $query->where('is_active', $status === 'active');
        }

        $accounts = $query->orderBy('code')->paginate(20)->withQueryString();

        return view('accounts.index', [
            'accounts' => $accounts,
            'types' => Account::TYPES,
            'filters' => $request->only('q', 'type', 'status'),
        ]);
    }

    public function create()
    {
        return view('accounts.form', [
            'account' => new Account(['is_active' => true]),
            'types' => Account::TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['normal_balance'] = Account::NORMAL_BALANCE_BY_TYPE[$data['type']];
        Account::create($data);

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    public function show(Account $account)
    {
        return redirect()->route('reports.ledger', ['account_id' => $account->id]);
    }

    public function edit(Account $account)
    {
        return view('accounts.form', [
            'account' => $account,
            'types' => Account::TYPES,
        ]);
    }

    public function update(Request $request, Account $account)
    {
        $data = $this->validateData($request, $account->id);
        $data['normal_balance'] = Account::NORMAL_BALANCE_BY_TYPE[$data['type']];
        $account->update($data);

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(Account $account)
    {
        if ($account->entries()->exists()) {
            return back()->with('error', 'Akun tidak dapat dihapus karena sudah memiliki transaksi.');
        }
        $account->delete();

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil dihapus.');
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('accounts', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Account::TYPES))],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
