<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Command;
use Illuminate\Http\Request;

class CoinController extends Controller
{
    public function index()
    {
        $items = Coin::all();
        return view('admin.coins.index', compact('items'));
    }

    public function create()
    {
        return view('admin.coins.create');
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
        Coin::create($data);
        return redirect()->route('admin.coins.index')->with('success', 'Coin created.');
    }

    public function edit(Coin $coin)
    {
        $commands = Command::where('Type', 'Coin')->where('TypeId', $coin->Id)->get();
        return view('admin.coins.edit', compact('coin', 'commands'));
    }

    public function update(Request $request, Coin $coin)
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
        $coin->update($data);
        return redirect()->route('admin.coins.edit', $coin)->with('success', 'Coin updated.');
    }

    public function destroy(Coin $coin)
    {
        Command::where('Type', 'Coin')->where('TypeId', $coin->Id)->delete();
        $coin->delete();
        return redirect()->route('admin.coins.index')->with('success', 'Coin deleted.');
    }
}
