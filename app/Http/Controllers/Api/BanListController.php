<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ban;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BanListController extends Controller
{
    /**
     * Replace the entire ban list with the reported snapshot.
     *
     * Expects:
     * {
     *   "bans": [
     *     {
     *       "player":    "Steve",
     *       "uuid":      "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
     *       "reason":    "Griefing",
     *       "banner":    "Admin",
     *       "active":    true,
     *       "bannedAgo": "1 hour ago",
     *       "expiresAt": "7 days"
     *     },
     *     ...
     *   ]
     * }
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'bans'              => 'present|array|max:5000',
            'bans.*.player'     => 'required|string|max:64',
            'bans.*.uuid'       => 'nullable|string|max:36',
            'bans.*.banner'     => 'nullable|string|max:64',
            'bans.*.reason'     => 'nullable|string|max:2048',
            'bans.*.active'     => 'nullable|boolean',
            'bans.*.bannedAgo'  => 'nullable|string|max:64',
            'bans.*.expiresAt'  => 'nullable|string|max:64',
        ]);

        $now = now();

        Ban::truncate();

        if (count($request->bans) > 0) {
            $rows = array_map(fn(array $b) => [
                'player'     => $b['player'],
                'uuid'       => $b['uuid'] ?? null,
                'banner'     => $b['banner'] ?? null,
                'reason'     => $b['reason'] ?? null,
                'active'     => $b['active'] ?? true,
                'banned_ago' => $b['bannedAgo'] ?? null,
                'expires_at' => $b['expiresAt'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ], $request->bans);

            Ban::insert($rows);
        }

        return response()->json(['ok' => true, 'count' => count($request->bans)]);
    }
}
