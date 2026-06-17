<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akhiri sesi admin/keagamaan setelah tidak aktif selama batas waktu tertentu.
 */
class IdleSessionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        if (! $user->hasAnyRole(['Admin', 'Keagamaan'])) {
            return $next($request);
        }

        $timeoutMinutes = (int) config('security.session.admin_idle_timeout', 10);
        $lastActivity = $request->session()->get('last_activity_at');

        if ($lastActivity !== null) {
            $inactiveMinutes = Carbon::parse($lastActivity)->diffInMinutes(now());

            if ($inactiveMinutes >= $timeoutMinutes) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = 'Sesi Anda berakhir karena tidak aktif selama '.$timeoutMinutes.' menit. Silakan login kembali.';

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'session_expired' => true,
                        'redirect_url' => route('login'),
                    ], 401);
                }

                return redirect()->route('login')->with('info', $message);
            }
        }

        $request->session()->put('last_activity_at', now()->toDateTimeString());

        return $next($request);
    }

    public static function resetActivity(Request $request): void
    {
        $request->session()->put('last_activity_at', now()->toDateTimeString());
    }
}
