<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BattlePass;
use App\Models\Command;
use Illuminate\Http\Request;

class BattlePassController extends Controller
{
    public function index()
    {
        $items = BattlePass::all();
        return view('admin.battlepasses.index', compact('items'));
    }

    public function create()
    {
        return view('admin.battlepasses.create');
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
        BattlePass::create($data);
        return redirect()->route('admin.battlepasses.index')->with('success', 'Battle Pass created.');
    }

    public function edit(BattlePass $battlepass)
    {
        $commands = Command::where('Type', 'BattlePass')->where('TypeId', $battlepass->Id)->get();
        return view('admin.battlepasses.edit', compact('battlepass', 'commands'));
    }

    public function update(Request $request, BattlePass $battlepass)
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
        $battlepass->update($data);
        return redirect()->route('admin.battlepasses.edit', $battlepass)->with('success', 'Battle Pass updated.');
    }

    public function destroy(BattlePass $battlepass)
    {
        Command::where('Type', 'BattlePass')->where('TypeId', $battlepass->Id)->delete();
        $battlepass->delete();
        return redirect()->route('admin.battlepasses.index')->with('success', 'Battle Pass deleted.');
    }
}
