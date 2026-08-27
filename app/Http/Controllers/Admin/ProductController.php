<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Command;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('sort_order')->get();
        $categoryId = $request->query('category_id');

        $query = Product::with('category')->orderBy('sort_order');
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        $items = $query->get();

        return view('admin.products.index', compact('items', 'categories', 'categoryId'));
    }

    public function move(Request $request, Product $product)
    {
        $direction = $request->input('direction');
        $categoryId = $product->category_id;

        if ($direction === 'up') {
            $sibling = Product::where('category_id', $categoryId)
                ->where('sort_order', '<', $product->sort_order)
                ->orderByDesc('sort_order')->first();
        } else {
            $sibling = Product::where('category_id', $categoryId)
                ->where('sort_order', '>', $product->sort_order)
                ->orderBy('sort_order')->first();
        }

        if ($sibling) {
            [$product->sort_order, $sibling->sort_order] = [$sibling->sort_order, $product->sort_order];
            $product->save();
            $sibling->save();
        }

        return response()->json(['ok' => true]);
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string|max:500',
            'price'            => 'required|numeric|min:0',
            'sale'             => 'nullable|integer|min:0|max:100',
            'image'            => 'nullable|string|max:255',
            'global_max_order' => 'nullable|integer|min:1',
            'category_id'      => 'required|exists:categories,id',
        ]);

        $data['description'] ??= '';
        $data['sale']        ??= 0;

        Product::create($data);
        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $commands   = Command::where('product_id', $product->id)->get();
        return view('admin.products.edit', compact('product', 'categories', 'commands'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string|max:500',
            'price'            => 'required|numeric|min:0',
            'sale'             => 'nullable|integer|min:0|max:100',
            'image'            => 'nullable|string|max:255',
            'global_max_order' => 'nullable|integer|min:1',
            'category_id'      => 'required|exists:categories,id',
        ]);

        $data['description'] ??= '';
        $data['sale']        ??= 0;

        $product->update($data);
        return redirect()->route('admin.products.edit', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }
}
