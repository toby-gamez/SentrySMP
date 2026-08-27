<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlayerCountController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $request->validate(['count' => 'required|integer|min:0']);

        DB::table('player_counts')->upsert(
            [['id' => 1, 'count' => $request->integer('count'), 'updated_at' => now()]],
            ['id'],
            ['count', 'updated_at'],
        );

        return response()->json(['ok' => true]);
    }
}
