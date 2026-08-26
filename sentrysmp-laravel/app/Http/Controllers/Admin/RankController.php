<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\Rank;
use Illuminate\Http\Request;

class RankController extends Controller
{
    public function index()
    {
        $items = Rank::all();
        return view('admin.ranks.index', compact('items'));
    }

    public function create()
    {
        return view('admin.ranks.create');
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
        Rank::create($data);
        return redirect()->route('admin.ranks.index')->with('success', 'Rank created.');
    }

    public function edit(Rank $rank)
    {
        $commands = Command::where('Type', 'Rank')->where('TypeId', $rank->Id)->get();
        return view('admin.ranks.edit', compact('rank', 'commands'));
    }

    public function update(Request $request, Rank $rank)
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
        $rank->update($data);
        return redirect()->route('admin.ranks.edit', $rank)->with('success', 'Rank updated.');
    }

    public function destroy(Rank $rank)
    {
        Command::where('Type', 'Rank')->where('TypeId', $rank->Id)->delete();
        $rank->delete();
        return redirect()->route('admin.ranks.index')->with('success', 'Rank deleted.');
    }
}
