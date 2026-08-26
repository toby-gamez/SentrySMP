<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommandQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommandController extends Controller
{
    /**
     * Return all pending commands and mark them as delivered.
     */
    public function pending(): JsonResponse
    {
        $commands = CommandQueue::where('status', 'pending')->orderBy('id')->get();

        if ($commands->isEmpty()) {
            return response()->json(['commands' => [], 'count' => 0]);
        }

        // Mark as delivered
        CommandQueue::whereIn('id', $commands->pluck('id'))->update([
            'status'     => 'delivered',
            'updated_at' => now(),
        ]);

        return response()->json([
            'commands' => $commands->map(fn($c) => [
                'id'           => $c->id,
                'player_name'  => $c->player_name,
                'command_text' => $c->command_text,
            ]),
            'count' => $commands->count(),
        ]);
    }

    /**
     * Acknowledge commands as executed.
     */
    public function acknowledge(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        CommandQueue::whereIn('id', $request->ids)->update([
            'status'     => 'executed',
            'updated_at' => now(),
        ]);

        return response()->json(['acknowledged' => count($request->ids)]);
    }
}
