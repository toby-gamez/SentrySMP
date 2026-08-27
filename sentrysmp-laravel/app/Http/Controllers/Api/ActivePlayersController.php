<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivePlayer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivePlayersController extends Controller
{
    /**
     * Replace the entire active player list with the reported snapshot.
     *
     * Expects:
     * {
     *   "players": [
     *     {
     *       "player": "Steve",
     *       "coins": 1000,
     *       "money": 250.50,
     *       "rank": "Admin",
     *       "statistics": {
     *         "playTimeSeconds": 3600,
     *         "playTimeTicks": 72000,
     *         "deaths": 5,
     *         "playerKills": 12,
     *         "mobsKilled": 340,
     *         "blocksTravelled": 8000
     *       },
     *       "error": null
     *     }
     *   ]
     * }
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'players'                          => 'required|array|max:200',
            'players.*.player'                 => 'required|string|max:64',
            'players.*.coins'                  => 'nullable|integer|min:0',
            'players.*.money'                  => 'nullable|numeric|min:0',
            'players.*.rank'                   => 'nullable|string|max:64',
            'players.*.statistics'             => 'nullable|array',
            'players.*.statistics.playTimeSeconds'  => 'nullable|integer|min:0',
            'players.*.statistics.playTimeTicks'    => 'nullable|integer|min:0',
            'players.*.statistics.deaths'           => 'nullable|integer|min:0',
            'players.*.statistics.playerKills'      => 'nullable|integer|min:0',
            'players.*.statistics.mobsKilled'       => 'nullable|integer|min:0',
            'players.*.statistics.blocksTravelled'  => 'nullable|integer|min:0',
            'players.*.error'                  => 'nullable|string|max:512',
        ]);

        $now = now();

        ActivePlayer::truncate();

        if (count($request->players) > 0) {
            $rows = array_map(function (array $p) use ($now): array {
                $stats = $p['statistics'] ?? [];
                return [
                    'username'          => $p['player'],
                    'coins'             => $p['coins'] ?? 0,
                    'money'             => $p['money'] ?? 0,
                    'rank'              => $p['rank'] ?? null,
                    'play_time_seconds' => $stats['playTimeSeconds'] ?? 0,
                    'play_time_ticks'   => $stats['playTimeTicks'] ?? 0,
                    'deaths'            => $stats['deaths'] ?? 0,
                    'player_kills'      => $stats['playerKills'] ?? 0,
                    'mobs_killed'       => $stats['mobsKilled'] ?? 0,
                    'blocks_travelled'  => $stats['blocksTravelled'] ?? 0,
                    'error'             => $p['error'] ?? null,
                    'reported_at'       => $now,
                ];
            }, $request->players);

            ActivePlayer::insert($rows);
        }

        return response()->json(['ok' => true, 'count' => count($request->players)]);
    }
}
