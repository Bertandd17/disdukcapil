<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class Login_Controller extends Controller
{
    public function tampilkan_form_login()
    {
        $adminExists = User::whereHas('roles', function ($query) {
            $query->where('name', 'Admin');
        })->exists();

        // Jika belum ada admin sama sekali (mis. setelah fresh install / migrate:fresh --seed),
        // arahkan langsung ke halaman registrasi admin (registrasi sekali pakai).
        if (! $adminExists) {
            return redirect()->route('admin.register')
                ->with('info', 'Belum ada admin terdaftar. Silakan lakukan registrasi admin terlebih dahulu.');
        }

        return response()
            ->view('auth.login', [
                'isAdmin' => false,
                'adminExists' => $adminExists,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Proses login (Gabungan: Admin & Keagamaan)
     * - Admin: Melalui verifikasi security question
     * - Keagamaan: Langsung login dan redirect ke dashboard keagamaan
     */
    public function proses_login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $rawUsername = $request->username;
        $username = preg_replace('/\s+/', ' ', trim((string) $rawUsername));
        $usernameLower = mb_strtolower($username, 'UTF-8');

        Log::info('Login attempt', [
            'username_hash' => sha1($usernameLower),
            'ip' => $request->ip(),
        ]);

        $throttleKey = 'login:'.sha1($usernameLower.'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            Log::warning('Login lockout triggered', [
                'username_hash' => sha1($usernameLower),
                'ip' => $request->ip(),
                'retry_after_seconds' => $seconds,
            ]);

            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('username');
        }

        $user = User::whereRaw('LOWER(username) = ?', [$usernameLower])->first();

        if (! $user && $username !== $rawUsername) {
            $user = User::where('username', $rawUsername)->first();
        }

        $genericError = ['username' => 'Username atau password salah.'];

        if (! $user) {
            // Equalize timing: run Hash::check against a dummy hash so the missing-user
            // path takes roughly the same time as the wrong-password path.
            Hash::check($request->password, '$2y$12$'.str_repeat('a', 53));

            Log::warning('Login failed - user not found', [
                'username_hash' => sha1($usernameLower),
                'ip' => $request->ip(),
            ]);

            RateLimiter::hit($throttleKey, 900);

            return back()->withErrors($genericError)->onlyInput('username');
        }

        if (! Hash::check($request->password, $user->password)) {
            Log::warning('Login failed - invalid password', [
                'username_hash' => sha1($usernameLower),
                'ip' => $request->ip(),
            ]);

            RateLimiter::hit($throttleKey, 900);

            return back()->withErrors($genericError)->onlyInput('username');
        }

        RateLimiter::clear($throttleKey);

        // Jika Admin, cek security question dan arahkan ke verifikasi
        if ($user->hasRole('Admin')) {
            $user->load('securityQuestion');

            if (! $user->securityQuestion || ! $user->security_question_id || ! $user->security_question_answer) {
                Log::warning('Admin login failed - no security question', [
                    'username' => $request->username,
                    'id' => $user->id,
                ]);

                return back()->withErrors([
                    'username' => 'Akun Anda belum lengkap. Silakan hubungi administrator.',
                ])->onlyInput('username');
            }

            if (Auth::check()) {
                Log::info('User already logged in, clearing session for admin login', [
                    'current_user_id' => Auth::id(),
                    'target_user_id' => $user->id,
                ]);
                Auth::logout();
            }

            $request->session()->forget('security_question_attempts');
            $request->session()->forget('security_question_user_id');
            $request->session()->put('security_question_user_id', $user->id);

            Log::info('Admin credentials verified, redirecting to security question', [
                'username' => $user->username,
                'id' => $user->id,
                'session_id' => $request->session()->getId(),
            ]);

            return redirect()->route('admin.verify.question', ['user_id' => $user->id])
                ->with('info', 'Username dan password benar. Silakan verifikasi dengan pertanyaan keamanan.');
        }

        // Jika Keagamaan, cek status akun
        if ($user->hasRole('Keagamaan')) {
            // Cek status akun keagamaan — gunakan pesan generik untuk mencegah
            // enumerasi akun non-aktif via response yang berbeda.
            if ($user->detail_keagamaan && $user->detail_keagamaan->status === 'non-aktif') {
                Log::warning('Keagamaan login blocked - account inactive', [
                    'username_hash' => sha1($usernameLower),
                ]);

                RateLimiter::hit($throttleKey, 900);

                return back()->withErrors($genericError)->onlyInput('username');
            }

            // Load roles sebelum login untuk memastikan role tersimpan di session
            $user->load('roles');

            // Regenerate session SEBELUM Auth::login untuk mencegah session fixation
            $request->session()->regenerate(true);

            try {
                // Login langsung untuk Keagamaan
                Auth::login($user, true);
            } catch (\Throwable $e) {
                Log::error('Keagamaan login - Auth::login failed', [
                    'username' => $user->username,
                    'id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'username' => 'Terjadi kesalahan saat login. Silakan coba lagi.',
                ])->onlyInput('username');
            }

            Log::info('Keagamaan login successful for: '.$user->username.' (ID: '.$user->id.')');
            Log::info('User roles after login: ' . json_encode($user->roles->pluck('name')->toArray()));

            $loginMessage = 'Anda telah berhasil login sebagai Petugas Keagamaan.';

            return redirect()->route('keagamaan.dashboard')
                ->with('login_success', $loginMessage);
        }

        // User tanpa role khusus - tidak diizinkan login
        Log::warning('Login failed - no valid role: '.$request->username);

        return back()->withErrors([
            'username' => 'Akun Anda tidak memiliki role yang valid. Silakan hubungi administrator.',
        ])->onlyInput('username');
    }

    public function showVerifyQuestion(Request $request)
    {
        $userId = $request->session()->get('security_question_user_id');

        if (! $userId) {
            return redirect()->route('login')
                ->withErrors(['login_error' => 'Sesi verifikasi telah berakhir. Silakan login ulang.']);
        }

        $user = User::find($userId);

        if (! $user) {
            $request->session()->forget('security_question_user_id');

            return redirect()->route('login')
                ->withErrors(['login_error' => 'Sesi verifikasi tidak valid. Silakan login ulang.']);
        }

        $user->load('securityQuestion');

        if (! $user->securityQuestion) {
            Log::error('Security question page - no question found', [
                'user_id' => $user->id,
                'username' => $user->username,
            ]);

            return redirect()->route('login')
                ->withErrors(['login_error' => 'Pertanyaan keamanan tidak ditemukan.']);
        }

        return view('auth.verify-question', compact('user'));
    }

    /**
     * Proses logout
     */
    public function proses_logout(Request $request)
    {
        $userName = Auth::user()?->name ?? 'Pengguna';

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('logout_success', 'Terima kasih, '.$userName.'. Anda telah berhasil logout dari sistem.');
    }
}
