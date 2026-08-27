<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Command;
use Illuminate\Http\Request;

class CommandController extends Controller
{
    public function index()
    {
        $items = Command::with('product')->orderBy('product_id')->get();
        return view('admin.commands.index', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'command_text' => 'required|string',
            'product_id'   => 'required|exists:products,id',
        ]);

        Command::create($data);
        return redirect()->back()->with('success', 'Command added.');
    }

    public function update(Request $request, Command $command)
    {
        $data = $request->validate([
            'command_text' => 'required|string',
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
