<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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

        Log::info('Login attempt for username: '.$request->username);

        $user = User::where('username', $request->username)->first();

        if (! $user) {
            Log::warning('Login failed - user not found: '.$request->username);

            return back()->withErrors([
                'username' => 'Username atau password salah.',
            ])->onlyInput('username');
        }

        if (! Hash::check($request->password, $user->password)) {
            Log::warning('Login failed - invalid password for: '.$request->username);

            return back()->withErrors([
                'username' => 'Username atau password salah.',
            ])->onlyInput('username');
        }

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
            // Cek status akun keagamaan
            if ($user->detail_keagamaan && $user->detail_keagamaan->status === 'non-aktif') {
                Log::warning('Keagamaan login failed - account inactive: '.$request->username);

                return back()->withErrors([
                    'username' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi administrator.',
                ])->onlyInput('username');
            }

            // Load roles sebelum login untuk memastikan role tersimpan di session
            $user->load('roles');

            // Login langsung untuk Keagamaan
            Auth::login($user, true);
            $request->session()->regenerate(true);

            Log::info('Keagamaan login successful for: '.$user->username.' (ID: '.$user->id.')');
            Log::info('User roles after login: ' . json_encode($user->roles->pluck('name')->toArray()));

            $loginMessage = "Anda telah berhasil login sebagai Petugas Keagamaan.";

            return redirect()->route('keagamaan.dashboard')
                ->with('login_success', $loginMessage);
        }

        // User tanpa role khusus - tidak diizinkan login
        Log::warning('Login failed - no valid role: '.$request->username);

        return back()->withErrors([
            'username' => 'Akun Anda tidak memiliki role yang valid. Silakan hubungi administrator.',
        ])->onlyInput('username');
    }

    public function showVerifyQuestion(Request $request, $user_id)
    {
        $user = User::find($user_id);

        if (! $user) {
            Log::warning('Security question page - user not found', [
                'user_id' => $user_id,
            ]);

            return redirect()->route('login')
                ->withErrors(['login_error' => 'Sesi telah kedaluwarsa. Silakan login kembali.']);
        }

        $currentUser = $request->session()->get('security_question_user_id');

        if ($currentUser !== $user_id) {
            Log::warning('Security question page - session mismatch', [
                'session_user_id' => $currentUser,
                'requested_user_id' => $user_id,
            ]);

            return redirect()->route('login')
                ->withErrors(['login_error' => 'Sesi tidak valid. Silakan login kembali.']);
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
        $request->session()->flush();

        return redirect()->route('login')
            ->with('logout_success', 'Terima kasih, '.$userName.'. Anda telah berhasil logout dari sistem.');
    }
}
