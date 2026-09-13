<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Proteksi ringan untuk endpoint tulis.
 *
 * Aktif hanya kalau env API_KEY diisi; kalau kosong middleware jadi no-op
 * supaya API gampang dicoba saat development lokal.
 */
class ApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('apotek.api_key');

        if (blank($expected)) {
            return $next($request);
        }

        $given = $request->header('X-API-KEY') ?? $request->bearerToken();

        if (! is_string($given) || ! hash_equals($expected, $given)) {
            return response()->json([
                'success' => false,
                'message' => 'API key tidak valid atau tidak disertakan.',
            ], 401);
        }

        return $next($request);
    }
}
