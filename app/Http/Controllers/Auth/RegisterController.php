<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\DatabaseException;
use App\Http\Controllers\Controller;
use App\Models\SecurityQuestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    /**
     * Tampilkan form registrasi admin
     */
    public function showRegistrationForm()
    {
        // Cek apakah sudah ada user dengan role Admin
        $adminRole = Role::where('name', 'Admin')->first();
        $adminExists = $adminRole && $adminRole->users()->exists();

        // Jika sudah ada admin, redirect ke login
        if ($adminExists) {
            return redirect()->route('login')
                ->with('info', 'Registrasi sudah dilakukan. Silakan login dengan akun yang sudah terdaftar.');
        }

        // Ambil semua pertanyaan keamanan
        $securityQuestions = SecurityQuestion::all();

        return view('auth.register', compact('securityQuestions'));
    }

    /**
     * Proses registrasi admin
     */
    public function register(Request $request)
    {
        // Cek apakah sudah ada user dengan role Admin
        $adminRole = Role::where('name', 'Admin')->first();
        $adminExists = $adminRole && $adminRole->users()->exists();

        // Jika sudah ada admin, kembalikan error
        if ($adminExists) {
            return redirect()->route('login')
                ->with('warning', 'Registrasi sudah dilakukan sebelumnya. Hanya boleh ada satu admin.');
        }

        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
            'security_question_id' => 'required|exists:security_questions,id',
            'security_answer' => 'required|string|min:2',
        ], [
            'name.required' => 'Nama lengkap harus diisi.',
            'username.required' => 'Username harus diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'security_question_id.required' => 'Silakan pilih pertanyaan keamanan.',
            'security_question_id.exists' => 'Pertanyaan keamanan tidak valid.',
            'security_answer.required' => 'Jawaban pertanyaan keamanan harus diisi.',
            'security_answer.min' => 'Jawaban terlalu pendek.',
        ]);

        try {
            DB::beginTransaction();

            // Buat user admin baru (password di-hash otomatis oleh User model, security_question_answer di-encrypt oleh trait)
            $admin = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'password' => $validated['password'],
                'security_question_id' => $validated['security_question_id'],
                'security_question_answer' => $validated['security_answer'],
            ]);

            // Assign role Admin ke user
            $admin->assignRole($adminRole);

            DB::commit();

            Log::info('Admin registered successfully', [
                'username' => $admin->username,
                'id' => $admin->id,
            ]);

            return redirect()->route('login')
                ->with('success', 'Registrasi berhasil! Akun admin telah berhasil dibuat. Username: '.$admin->username.'. Silakan login untuk melanjutkan.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Format error untuk user
            $errorInfo = DatabaseException::formatForUser($e);

            Log::error('Admin registration failed', [
                'error_code' => $errorInfo['error_code'],
                'error' => $e->getMessage(),
                'location' => $errorInfo['location'],
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['registration_error' => $errorInfo['user_message']])
                ->with('error', $errorInfo['user_message'])
                ->with('error_detail', $errorInfo['technical_detail'])
                ->with('error_location', $errorInfo['location'])
                ->with('error_solution', $errorInfo['solution'])
                ->with('error_code', $errorInfo['error_code']);
        }
    }

    /**
     * Verifikasi jawaban pertanyaan keamanan
     */
    public function verifySecurityQuestion(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'security_answer' => 'required|string|min:1',
        ], [
            'user_id.required' => 'ID user wajib diisi.',
            'user_id.exists' => 'User tidak ditemukan.',
            'security_answer.required' => 'Jawaban pertanyaan keamanan wajib diisi.',
            'security_answer.min' => 'Jawaban tidak boleh kosong.',
        ]);

        $user = User::find($request->user_id);

        if (! $user) {
            Log::warning('Security question verification - user not found', [
                'user_id' => $request->user_id,
                'ip' => $request->ip(),
            ]);

            return redirect()->route('login')
                ->withErrors(['login_error' => 'Sesi telah kedaluwarsa. Silakan login kembali.']);
        }

        Log::info('Security question verification attempt', [
            'user_id' => $user->id,
            'username' => $user->username,
            'current_auth_user' => Auth::check() ? Auth::id() : 'not_logged_in',
            'session_user_id' => $request->session()->get('security_question_user_id'),
        ]);

        $storedAnswerRaw = $user->getAttributes()['security_question_answer'] ?? null;
        $storedAnswer = null;

        if (is_string($storedAnswerRaw) && $storedAnswerRaw !== '') {
            try {
                $storedAnswer = \Illuminate\Support\Facades\Crypt::decryptString($storedAnswerRaw);
            } catch (\Exception $e) {
                $storedAnswer = $storedAnswerRaw;
                Log::warning('Security answer decryption failed, falling back to raw value', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $userAnswer = trim($request->security_answer);

        if ($storedAnswer === null || $storedAnswer === '') {
            Log::error('Security question verification - no answer stored', [
                'user_id' => $user->id,
                'username' => $user->username,
            ]);

            return redirect()->route('login')
                ->withErrors(['login_error' => 'Data pertanyaan keamanan tidak lengkap. Silakan hubungi administrator.']);
        }

        $attempts = $request->session()->get('security_question_attempts', 0);
        $maxAttempts = 5;

        if ($attempts >= $maxAttempts) {
            Log::warning('Security question verification - max attempts reached', [
                'user_id' => $user->id,
                'username' => $user->username,
                'attempts' => $attempts,
                'ip' => $request->ip(),
            ]);

            $request->session()->forget('security_question_attempts');
            $request->session()->forget('security_question_user_id');

            return redirect()->route('login')
                ->withErrors(['login_error' => 'Terlalu banyak percobaan gagal. Silakan tunggu 15 menit sebelum mencoba lagi.']);
        }

        try {
            if (strcasecmp($userAnswer, trim($storedAnswer)) === 0) {
                $request->session()->forget('security_question_attempts');
                $request->session()->forget('security_question_user_id');

                if (Auth::check()) {
                    Log::info('User already logged in, logging out first', [
                        'current_user_id' => Auth::id(),
                        'new_user_id' => $user->id,
                    ]);
                    Auth::logout();
                }

                $user->setRememberToken(null);
                $user->load('roles');

                $request->session()->regenerate(true);
                Auth::login($user, false);
                $request->session()->migrate(true);

                Log::info('Admin login successful with security question', [
                    'username' => $user->username,
                    'id' => $user->id,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'session_id' => $request->session()->getId(),
                    'auth_check_after' => Auth::check(),
                    'auth_id_after' => Auth::id(),
                ]);

                $loginMessage = "Verifikasi pertanyaan keamanan telah berhasil.";

                if (! Auth::check() || Auth::id() !== $user->id) {
                    Log::error('Auth state lost after login', [
                        'expected_id' => $user->id,
                        'auth_id' => Auth::id(),
                        'auth_check' => Auth::check(),
                    ]);
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')
                        ->withErrors(['login_error' => 'Sesi login tidak valid. Silakan coba lagi.']);
                }

                return redirect()->route('admin.dashboard')
                    ->with('login_success', $loginMessage);
            } else {
                $attempts++;
                $remainingAttempts = $maxAttempts - $attempts;

                $request->session()->put('security_question_attempts', $attempts);
                $request->session()->put('security_question_user_id', $user->id);

                Log::warning('Security question verification failed', [
                    'username' => $user->username,
                    'id' => $user->id,
                    'attempts' => $attempts,
                    'remaining_attempts' => $remainingAttempts,
                    'ip' => $request->ip(),
                ]);

                return redirect()->back()
                    ->withInput()
                    ->with('attempts', $remainingAttempts)
                    ->withErrors([
                        'security_answer' => 'Jawaban pertanyaan keamanan salah. Silakan coba lagi.',
                    ]);
            }
        } catch (\Exception $e) {
            Log::error('Security question verification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan teknis. Silakan coba lagi atau hubungi administrator jika masalah berlanjut.']);
        }
    }
}
