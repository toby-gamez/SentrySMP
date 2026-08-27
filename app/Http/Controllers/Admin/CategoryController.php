<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $items = Category::withCount('products')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('items'));
    }

    public function move(Request $request, Category $category)
    {
        $direction = $request->input('direction');

        if ($direction === 'up') {
            $sibling = Category::where('sort_order', '<', $category->sort_order)->orderByDesc('sort_order')->first();
        } else {
            $sibling = Category::where('sort_order', '>', $category->sort_order)->orderBy('sort_order')->first();
        }

        if ($sibling) {
            [$category->sort_order, $sibling->sort_order] = [$sibling->sort_order, $category->sort_order];
            $category->save();
            $sibling->save();
        }

        return response()->json(['ok' => true]);
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'slug'  => 'nullable|string|max:100|unique:categories,slug',
            'color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'image' => 'nullable|string|max:255',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        Category::create($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'slug'  => 'nullable|string|max:100|unique:categories,slug,' . $category->id,
            'color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'image' => 'nullable|string|max:255',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $category->update($data);
        return redirect()->route('admin.categories.edit', $category)->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }
}
