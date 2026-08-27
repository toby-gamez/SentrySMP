<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommandQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommandController extends Controller
{
    /**
     * Return all pending commands and mark them as delivered atomically.
     * lockForUpdate prevents two simultaneous polls from fetching the same batch (G12).
     */
    public function pending(): JsonResponse
    {
        $commands = DB::transaction(function () {
            $rows = CommandQueue::where('status', 'pending')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($rows->isNotEmpty()) {
                CommandQueue::whereIn('id', $rows->pluck('id'))->update([
                    'status'       => 'delivered',
                    'delivered_at' => now(),
                    'updated_at'   => now(),
                ]);
            }

            return $rows;
        });

        if ($commands->isEmpty()) {
            return response()->json(['commands' => [], 'count' => 0]);
        }

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
     * Acknowledge commands as executed by the game server.
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
