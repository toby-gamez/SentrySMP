<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImageController extends Controller
{
    private const API = 'https://images.sentrysmp.eu/api/Images';

    // Returns JSON list — called by the product image picker via fetch()
    public function index(Request $request)
    {
        $subDir = $request->query('subDirectory', '');
        $url    = $subDir ? self::API . '?subDirectory=' . urlencode($subDir) : self::API;

        try {
            $response = Http::timeout(8)->get($url);
            return response()->json($response->successful() ? $response->json() : []);
        } catch (\Throwable) {
            return response()->json([]);
        }
    }

    // Upload — called by the product image picker via fetch(); returns JSON
    public function upload(Request $request)
    {
        if (!$request->hasFile('image')) {
            return response()->json(['ok' => false, 'error' => 'No file received by server.'], 422);
        }

        $file = $request->file('image');

        if (!$file->isValid()) {
            return response()->json(['ok' => false, 'error' => 'File upload failed: ' . $file->getErrorMessage()], 422);
        }

        if ($file->getSize() > 8 * 1024 * 1024) {
            return response()->json(['ok' => false, 'error' => 'File too large (max 8 MB).'], 422);
        }

        $subDir = $request->input('subDirectory', 'keys');

        try {
            $response = Http::timeout(15)
                ->withBasicAuth('admin', 'DoMin1c@l')
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post(self::API . '/upload?subDirectory=' . urlencode($subDir));

            if ($response->successful()) {
                return response()->json(['ok' => true, 'data' => $response->json()]);
            }

            // Surface the upstream error clearly
            $upstream = $response->json();
            $msg = $upstream['error'] ?? $upstream['title'] ?? ('HTTP ' . $response->status() . ' from image server');
            return response()->json(['ok' => false, 'error' => $msg], 422);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Connection to image server failed: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $filename)
    {
        try {
            Http::timeout(8)->delete(self::API . '/' . $filename);
        } catch (\Throwable) {}

        return response()->json(['ok' => true]);
    }
}
