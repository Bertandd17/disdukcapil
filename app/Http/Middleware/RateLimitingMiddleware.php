<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitingMiddleware
{
    public function handle(Request $request, Closure $next, ?string $maxAttempts = '60', ?string $decayMinutes = '1'): Response
    {
        $key = $this->resolveKey($request);
        $max = (int) $maxAttempts;
        $decay = (int) $decayMinutes;

        if (Cache::has($key.':lockout') && Cache::get($key.':lockout') > now()->timestamp) {
            return response()->json([
                'message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
            ], 429);
        }

        $current = Cache::get($key, 0) + 1;
        Cache::put($key, $current, now()->addMinutes($decay));

        if ($current > $max) {
            Cache::put($key.':lockout', now()->addMinutes($decay)->timestamp, now()->addMinutes($decay));
            return response()->json([
                'message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
            ], 429);
        }

        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit', (string) $max);
        $response->headers->set('X-RateLimit-Remaining', (string) max(0, $max - $current));

        return $response;
    }

    protected function resolveKey(Request $request): string
    {
        $user = $request->user();
        return 'rate_limit:'.($user ? 'u'.$user->id : 'ip'.$request->ip()).':'.sha1($request->path());
    }
}
