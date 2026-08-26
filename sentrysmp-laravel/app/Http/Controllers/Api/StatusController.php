<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    public function status(): JsonResponse
    {
        return response()->json(['online' => true, 'timestamp' => now()->toIso8601String()]);
    }
}
