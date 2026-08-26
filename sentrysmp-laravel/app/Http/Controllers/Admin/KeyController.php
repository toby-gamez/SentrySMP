<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\Key;
use Illuminate\Http\Request;

class KeyController extends Controller
{
    public function index()
    {
        $items = Key::all();
        return view('admin.keys.index', compact('items'));
    }

    public function create()
    {
        return view('admin.keys.create');
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
        Key::create($data);
        return redirect()->route('admin.keys.index')->with('success', 'Key created.');
    }

    public function edit(Key $key)
    {
        $commands = Command::where('Type', 'Key')->where('TypeId', $key->Id)->get();
        return view('admin.keys.edit', compact('key', 'commands'));
    }

    public function update(Request $request, Key $key)
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
        $key->update($data);
        return redirect()->route('admin.keys.edit', $key)->with('success', 'Key updated.');
    }

    public function destroy(Key $key)
    {
        Command::where('Type', 'Key')->where('TypeId', $key->Id)->delete();
        $key->delete();
        return redirect()->route('admin.keys.index')->with('success', 'Key deleted.');
    }
}
