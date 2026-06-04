<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IPWhitelistMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = config('security.ip_whitelist', []);

        if (empty($allowed) || !config('security.ip_whitelist_enabled', false)) {
            return $next($request);
        }

        $clientIp = $request->ip();

        foreach ((array) $allowed as $entry) {
            if ($entry === $clientIp) {
                return $next($request);
            }
            if (str_contains($entry, '/') && $this->cidrMatch($clientIp, $entry)) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Akses ditolak dari IP ini.',
        ], 403);
    }

    protected function cidrMatch(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        if ($ipLong === false || $subnetLong === false) {
            return false;
        }
        $mask = -1 << (32 - (int) $bits);
        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}
