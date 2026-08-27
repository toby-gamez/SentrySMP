<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    public function index()
    {
        $items = Voucher::withCount('usages')->orderByDesc('id')->get();
        return view('admin.vouchers.index', compact('items'));
    }

    public function create()
    {
        $scopes     = ['All', 'Category', 'Product'];
        $categories = Category::orderBy('name')->get();
        $products   = Product::with('category')->orderBy('name')->get();
        return view('admin.vouchers.create', compact('scopes', 'categories', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'             => 'required|string|max:50|unique:vouchers,code',
            'description'      => 'nullable|string|max:500',
            'start_date'       => 'required|date',
            'expiration_date'  => 'required|date|after:start_date',
            'max_uses'         => 'nullable|integer|min:1',
            'discount_percent' => 'required|numeric|min:1|max:100',
            'scope'            => 'required|in:All,Category,Product',
            'scope_category'   => 'nullable|string',
            'scope_item_id'    => 'nullable|integer',
            'is_active'        => 'boolean',
        ]);

        $data['code']         = strtoupper($data['code']);
        $data['is_active']    = $request->boolean('is_active', true);
        $data['current_uses'] = 0;

        Voucher::create($data);
        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher created.');
    }

    public function edit(Voucher $voucher)
    {
        $scopes     = ['All', 'Category', 'Product'];
        $categories = Category::orderBy('name')->get();
        $products   = Product::with('category')->orderBy('name')->get();
        return view('admin.vouchers.edit', compact('voucher', 'scopes', 'categories', 'products'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'code'             => ['required', 'string', 'max:50', Rule::unique('vouchers', 'code')->ignore($voucher->id)],
            'description'      => 'nullable|string|max:500',
            'start_date'       => 'required|date',
            'expiration_date'  => 'required|date|after:start_date',
            'max_uses'         => 'nullable|integer|min:1',
            'discount_percent' => 'required|numeric|min:1|max:100',
            'scope'            => 'required|in:All,Category,Product',
            'scope_category'   => 'nullable|string',
            'scope_item_id'    => 'nullable|integer',
            'is_active'        => 'boolean',
        ]);

        $data['code']      = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);

        $voucher->update($data);
        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher updated.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher deleted.');
    }
}
