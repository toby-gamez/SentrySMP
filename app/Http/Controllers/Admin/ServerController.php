<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function index()
    {
        $items = Server::all();
        return view('admin.servers.index', compact('items'));
    }

    public function create()
    {
        return view('admin.servers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Name'         => 'required|string|max:100',
            'RCONIP'       => 'required|string|max:50',
            'RCONPort'     => 'required|integer|min:1|max:65535',
            'RCONPassword' => 'required|string|max:100',
        ]);

        Server::create($data);
        return redirect()->route('admin.servers.index')->with('success', 'Server created.');
    }

    public function edit(Server $server)
    {
        return view('admin.servers.edit', compact('server'));
    }

    public function update(Request $request, Server $server)
    {
        $data = $request->validate([
            'Name'         => 'required|string|max:100',
            'RCONIP'       => 'required|string|max:50',
            'RCONPort'     => 'required|integer|min:1|max:65535',
            'RCONPassword' => 'nullable|string|max:100',
        ]);

        // Only update password if a new one was provided
        if (empty($data['RCONPassword'])) {
            unset($data['RCONPassword']);
        }

        $server->update($data);
        return redirect()->route('admin.servers.index')->with('success', 'Server updated.');
    }

    public function destroy(Server $server)
    {
        $server->delete();
        return redirect()->route('admin.servers.index')->with('success', 'Server deleted.');
    }
}
