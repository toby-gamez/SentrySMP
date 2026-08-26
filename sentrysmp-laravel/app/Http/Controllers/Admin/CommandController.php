<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Command;
use Illuminate\Http\Request;

class CommandController extends Controller
{
    public function index()
    {
        $items = Command::orderBy('Type')->orderBy('TypeId')->get();
        return view('admin.commands.index', compact('items'));
    }

    public function create()
    {
        $types = ['Key', 'Coin', 'Bundle', 'Rank', 'BattlePass', 'Other'];
        return view('admin.commands.create', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'CommandText' => 'required|string',
            'Type'        => 'required|in:Key,Coin,Bundle,Rank,BattlePass,Other,Shard,SHARD',
            'TypeId'      => 'required|integer|min:1',
        ]);

        Command::create($data);

        // Redirect back to the product edit page if possible
        return redirect()->back()->with('success', 'Command added.');
    }

    public function update(Request $request, Command $command)
    {
        $data = $request->validate([
            'CommandText' => 'required|string',
        ]);

        $command->update($data);
        return redirect()->back()->with('success', 'Command updated.');
    }

    public function destroy(Command $command)
    {
        $command->delete();
        return redirect()->back()->with('success', 'Command deleted.');
    }
}
