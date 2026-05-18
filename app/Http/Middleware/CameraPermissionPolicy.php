<?php

namespace App\Http\Middleware;

use Closure;

class CameraPermissionPolicy
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('Permissions-Policy', 'camera=(self)', true);
        return $response;
    }
}