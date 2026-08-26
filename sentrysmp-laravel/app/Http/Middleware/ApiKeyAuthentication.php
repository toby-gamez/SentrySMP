<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = config('services.game_server.api_key', '');
        $providedKey = $request->query('api_key', $request->header('X-API-Key', ''));

        if (!$expectedKey || $providedKey !== $expectedKey) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
