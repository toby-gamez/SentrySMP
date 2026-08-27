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
     *     { "player": "Steve", "uuid": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx", "reason": "Griefing" },
     *     ...
     *   ]
     * }
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'bans'          => 'required|array|max:5000',
            'bans.*.player' => 'required|string|max:64',
            'bans.*.uuid'   => 'nullable|string|max:36',
            'bans.*.reason' => 'nullable|string|max:512',
        ]);

        $now = now();

        Ban::truncate();

        if (count($request->bans) > 0) {
            $rows = array_map(fn(array $b) => [
                'player'     => $b['player'],
                'uuid'       => $b['uuid'] ?? null,
                'reason'     => $b['reason'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ], $request->bans);

            Ban::insert($rows);
        }

        return response()->json(['ok' => true, 'count' => count($request->bans)]);
    }
}
