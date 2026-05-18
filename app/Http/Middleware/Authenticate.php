<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Jika request AJAX atau JSON, return null untuk JSON response
        if ($request->expectsJson() || $request->wantsJson()) {
            return null;
        }

        // Untuk request biasa, redirect ke login
        return route('login');
    }
}
