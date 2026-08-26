<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Command;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    public function index()
    {
        $items = Bundle::all();
        return view('admin.bundles.index', compact('items'));
    }

    public function create()
    {
        return view('admin.bundles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Name'           => 'required|string|max:100',
            'Description'    => 'nullable|string|max:500',
            'Price'          => 'required|numeric|min:0',
            'Sale'           => 'nullable|integer|min:0|max:100',
            'GlobalMaxOrder' => 'nullable|integer|min:1',
            'Image'          => 'nullable|string|max:500',
        ]);

        $data['Description'] ??= '';
        Bundle::create($data);
        return redirect()->route('admin.bundles.index')->with('success', 'Bundle created.');
    }

    public function edit(Bundle $bundle)
    {
        $commands = Command::where('Type', 'Bundle')->where('TypeId', $bundle->Id)->get();
        return view('admin.bundles.edit', compact('bundle', 'commands'));
    }

    public function update(Request $request, Bundle $bundle)
    {
        $data = $request->validate([
            'Name'           => 'required|string|max:100',
            'Description'    => 'nullable|string|max:500',
            'Price'          => 'required|numeric|min:0',
            'Sale'           => 'nullable|integer|min:0|max:100',
            'GlobalMaxOrder' => 'nullable|integer|min:1',
            'Image'          => 'nullable|string|max:500',
        ]);

        $data['Description'] ??= '';
        $bundle->update($data);
        return redirect()->route('admin.bundles.edit', $bundle)->with('success', 'Bundle updated.');
    }

    public function destroy(Bundle $bundle)
    {
        Command::where('Type', 'Bundle')->where('TypeId', $bundle->Id)->delete();
        $bundle->delete();
        return redirect()->route('admin.bundles.index')->with('success', 'Bundle deleted.');
    }
}
