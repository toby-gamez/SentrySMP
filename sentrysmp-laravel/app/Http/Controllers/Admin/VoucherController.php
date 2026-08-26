<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BattlePass;
use App\Models\Bundle;
use App\Models\Coin;
use App\Models\Key;
use App\Models\Other;
use App\Models\Rank;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    public function index()
    {
        $items = Voucher::withCount('usages')->orderByDesc('Id')->get();
        return view('admin.vouchers.index', compact('items'));
    }

    public function create()
    {
        $scopes     = ['All', 'Category', 'Product'];
        $categories = ['Key', 'Coin', 'Bundle', 'Rank', 'BattlePass', 'Other'];
        $products   = $this->loadProducts();
        return view('admin.vouchers.create', compact('scopes', 'categories', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code'            => 'required|string|max:50|unique:vouchers,Code',
            'Description'     => 'nullable|string|max:500',
            'StartDate'       => 'required|date',
            'ExpirationDate'  => 'required|date|after:StartDate',
            'MaxUses'         => 'nullable|integer|min:1',
            'DiscountPercent' => 'required|numeric|min:1|max:100',
            'Scope'           => 'required|in:All,Category,Product',
            'ScopeCategory'   => 'nullable|string',
            'ScopeItemId'     => 'nullable|integer',
            'IsActive'        => 'boolean',
        ]);

        $data['Code']        = strtoupper($data['Code']);
        $data['IsActive']    = $request->boolean('IsActive', true);
        $data['CurrentUses'] = 0;

        Voucher::create($data);
        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher created.');
    }

    public function edit(Voucher $voucher)
    {
        $scopes     = ['All', 'Category', 'Product'];
        $categories = ['Key', 'Coin', 'Bundle', 'Rank', 'BattlePass', 'Other'];
        $products   = $this->loadProducts();
        return view('admin.vouchers.edit', compact('voucher', 'scopes', 'categories', 'products'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'Code'            => ['required', 'string', 'max:50', Rule::unique('vouchers', 'Code')->ignore($voucher->Id, 'Id')],
            'Description'     => 'nullable|string|max:500',
            'StartDate'       => 'required|date',
            'ExpirationDate'  => 'required|date|after:StartDate',
            'MaxUses'         => 'nullable|integer|min:1',
            'DiscountPercent' => 'required|numeric|min:1|max:100',
            'Scope'           => 'required|in:All,Category,Product,Item',
            'ScopeCategory'   => 'nullable|string',
            'ScopeItemId'     => 'nullable|integer',
            'IsActive'        => 'boolean',
        ]);

        // Normalise legacy value
        if ($data['Scope'] === 'Item') $data['Scope'] = 'Product';

        $data['Code']     = strtoupper($data['Code']);
        $data['IsActive'] = $request->boolean('IsActive', true);

        $voucher->update($data);
        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher updated.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher deleted.');
    }

    private function loadProducts(): array
    {
        return [
            'Key'        => Key::select('Id', 'Name')->orderBy('Name')->get(),
            'Coin'       => Coin::select('Id', 'Name')->orderBy('Name')->get(),
            'Bundle'     => Bundle::select('Id', 'Name')->orderBy('Name')->get(),
            'Rank'       => Rank::select('Id', 'Name')->orderBy('Name')->get(),
            'BattlePass' => BattlePass::select('Id', 'Name')->orderBy('Name')->get(),
            'Other'      => Other::select('Id', 'Name')->orderBy('Name')->get(),
        ];
    }
}
