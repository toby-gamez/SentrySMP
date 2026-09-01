<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommandQueue;
use App\Models\PaymentTransaction;
use App\Services\CommandQueueService;
use Illuminate\Http\Request;

class CommandQueueController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', '');
        $player = $request->query('player', '');

        $query = CommandQueue::with('transaction')->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }
        if ($player) {
            $query->where('player_name', 'like', "%{$player}%");
        }

        $items = $query->paginate(50)->withQueryString();

        return view('admin.command-queue.index', compact('items', 'status', 'player'));
    }

    public function markExecuted(CommandQueue $commandQueue)
    {
        $commandQueue->update(['status' => 'executed']);
        return back()->with('success', 'Marked as executed.');
    }

    public function markFailed(CommandQueue $commandQueue)
    {
        $commandQueue->update(['status' => 'failed']);
        return back()->with('success', 'Marked as failed.');
    }

    public function markPending(CommandQueue $commandQueue)
    {
        $commandQueue->update(['status' => 'pending']);
        return back()->with('success', 'Reset to pending.');
    }

    public function addDebug(Request $request)
    {
        $request->validate([
            'player_name'  => 'required|string|max:255',
            'command_text' => 'required|string|max:1000',
        ]);

        CommandQueue::create([
            'transaction_id' => null,
            'player_name'    => $request->player_name,
            'command_text'   => $request->command_text,
            'status'         => 'pending',
        ]);

        return back()->with('success', 'Debug command added to queue.');
    }
}
