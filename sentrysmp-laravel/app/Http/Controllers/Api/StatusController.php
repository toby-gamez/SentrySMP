<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DiscordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    public function status(DiscordService $discord): JsonResponse
    {
        $row = DB::table('player_counts')->find(1);

        $fresh = $row && now()->diffInMinutes($row->updated_at) < 5;
        $playerCount = $fresh ? (int) $row->count : 0;

        return response()->json([
            'online'          => true,
            'timestamp'       => now()->toIso8601String(),
            'player_count'    => $playerCount,
            'player_fresh'    => $fresh,
            'discord_members' => $discord->getMemberCount(),
        ]);
    }
}
