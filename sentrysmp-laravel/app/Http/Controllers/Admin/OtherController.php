<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\Other;
use Illuminate\Http\Request;

class OtherController extends Controller
{
    public function index()
    {
        $items = Other::all();
        return view('admin.others.index', compact('items'));
    }

    public function create()
    {
        return view('admin.others.create');
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
        Other::create($data);
        return redirect()->route('admin.others.index')->with('success', 'Item created.');
    }

    public function edit(Other $other)
    {
        $commands = Command::where('Type', 'Other')->where('TypeId', $other->Id)->get();
        return view('admin.others.edit', compact('other', 'commands'));
    }

    public function update(Request $request, Other $other)
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
        $other->update($data);
        return redirect()->route('admin.others.edit', $other)->with('success', 'Item updated.');
    }

    public function destroy(Other $other)
    {
        Command::where('Type', 'Other')->where('TypeId', $other->Id)->delete();
        $other->delete();
        return redirect()->route('admin.others.index')->with('success', 'Item deleted.');
    }
}
