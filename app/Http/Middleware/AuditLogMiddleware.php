<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('security.audit.enabled', false)) {
            return $next($request);
        }

        $start = microtime(true);
        $response = $next($request);
        $duration = round((microtime(true) - $start) * 1000, 2);

        if ($this->shouldAudit($request, $response)) {
            Log::channel(config('security.audit.channel', 'stack'))->info('audit.request', [
                'method' => $request->method(),
                'path' => $request->path(),
                'status' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'user_id' => optional($request->user())->id,
                'ip' => $request->ip(),
            ]);
        }

        return $response;
    }

    protected function shouldAudit(Request $request, Response $response): bool
    {
        $methods = config('security.audit.methods', ['POST', 'PUT', 'PATCH', 'DELETE']);
        if (!in_array($request->method(), $methods, true)) {
            return false;
        }
        if (!config('security.audit.log_failed_attempts', true) && $response->getStatusCode() >= 400) {
            return false;
        }
        return true;
    }
}
