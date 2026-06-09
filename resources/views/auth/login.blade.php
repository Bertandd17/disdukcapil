<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Disdukcapil Kabupaten Toba</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_toba.jpeg') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style-guide.css') }}">
    <link rel="stylesheet" href="{{ asset('css/page-loading.css') }}?v={{ filemtime(public_path('css/page-loading.css')) }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #0052CC;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #0047B3;
        }

        /* Animated Background */
        .bg-animated {
            background: linear-gradient(-45deg, #0052CC, #0066FF, #0047B3, #003D9A);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Float Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        /* Input Focus Animation */
        .input-group {
            position: relative;
        }

        .input-group input:focus ~ label,
        .input-group input:not(:placeholder-shown) ~ label {
            transform: translateY(-24px) scale(0.85);
            color: #0052CC;
        }

        .input-group label {
            transition: all 0.3s ease;
        }

        /* Button Ripple */
        .btn-ripple {
            position: relative;
            overflow: hidden;
        }

        .btn-ripple::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-ripple:active::after {
            width: 300px;
            height: 300px;
        }

        .auth-shell,
        .auth-card {
            transform: none !important;
            rotate: 0deg !important;
            skew: 0deg !important;
        }
    </style>
    {{-- SweetAlert Final Fix --}}
    <link rel="stylesheet" href="{{ asset('css/swal-final-fix.css') }}">
</head>
<body class="bg-animated min-h-screen flex items-center justify-center p-4 overflow-x-hidden">
@include('components.page-loading')

    <!-- Background Particles -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute w-64 h-64 bg-white/10 rounded-full -top-32 -left-32 float-animation"></div>
        <div class="absolute w-96 h-96 bg-white/10 rounded-full -bottom-48 -right-48 float-animation" style="animation-delay: 2s;"></div>
        <div class="absolute w-48 h-48 bg-white/10 rounded-full top-1/4 right-1/4 float-animation" style="animation-delay: 4s;"></div>
    </div>

    <!-- Login Container -->
    <div class="auth-shell relative z-10 w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-2xl shadow-xl mb-4 float-animation overflow-hidden border-4 border-white/30">
                <img src="{{ asset('images/logo_toba.jpeg') }}" alt="Logo Kabupaten Toba" class="w-full h-full object-contain">
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-2">Disdukcapil Toba</h1>
            <p class="text-blue-100 text-lg">Portal Sistem</p>
        </div>

        <!-- Login Card -->
        <div class="auth-card bg-white rounded-3xl shadow-2xl p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-1">Selamat Datang</h2>
                <p class="text-gray-600">Masuk ke dashboard sistem</p>
            </div>

            <!-- Notification for Already Logged In Users -->
            @if(auth()->check())
                <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-6 mb-6">
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-amber-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-check text-3xl text-amber-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-amber-800 mb-2">
                            Anda Sudah Login!
                        </h3>
                        <p class="text-sm text-amber-700 mb-4">
                            Anda sedang login sebagai <strong>{{ auth()->user()->name }}</strong>
                            <br>dengan role <strong>{{ auth()->user()->roles->first()->name ?? 'User' }}</strong>
                        </p>

                        <div class="flex flex-col gap-3">
                            @if(auth()->user()->hasRole('Admin'))
                                <a href="{{ route('admin.dashboard') }}"
                                   class="w-full py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-xl font-bold hover:from-blue-700 hover:to-cyan-700 transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-tachometer-alt"></i>
                                    Ke Dashboard Admin
                                </a>
                            @elseif(auth()->user()->hasRole('Keagamaan'))
                                <a href="{{ route('keagamaan.dashboard') }}"
                                   class="w-full py-3 bg-gradient-to-r from-teal-600 to-emerald-600 text-white rounded-xl font-bold hover:from-teal-700 hover:to-emerald-700 transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-tachometer-alt"></i>
                                    Ke Dashboard Keagamaan
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}" id="alreadyLoggedInLogoutForm">
                                @csrf
                                <button type="button" onclick="handleAlreadyLoggedInLogout()"
                                        class="w-full py-3 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Logout dari Akun Ini
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    function handleAlreadyLoggedInLogout() {
                        SwalHelper.customConfirm({
                            title: 'Konfirmasi Logout',
                            message: 'Sesi akun {{ auth()->user()->name }} akan diakhiri.',
                            subMessage: 'Apakah Anda yakin ingin melanjutkan?',
                            iconClass: 'fas fa-sign-out-alt',
                            iconColor: '#dc2626',
                            confirmText: 'Konfirmasi',
                            confirmColor: '#dc2626',
                            loadingTitle: 'Memproses Logout',
                            loadingMessage: 'Sedang mengakhiri session...',
                            onConfirm: () => {
                                setTimeout(function() {
                                    document.getElementById('alreadyLoggedInLogoutForm').submit();
                                }, 1000);
                            }
                        });
                    }
                </script>
            @endif

            <!-- Info Messages -->
            @if (session('info'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl mb-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-info-circle"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('warning'))
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-xl mb-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
                @csrf
                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Username Input -->
                <div class="input-group">
                    <input type="text" id="username" name="username" required placeholder=" "
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition peer"
                           value="{{ old('username') }}">
                    <label for="username" class="absolute left-4 top-3 text-gray-400 pointer-events-none">
                        <i class="fas fa-user mr-2"></i>Username
                    </label>
                </div>

                <!-- Password Input -->
                <div class="input-group">
                    <div class="relative">
                        <input type="password" id="password" name="password" required placeholder=" "
                               class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition peer">
                        <label for="password" class="absolute left-4 top-3 text-gray-400 pointer-events-none">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500" {{ old('remember') ? 'checked' : '' }}>
                        <span class="text-sm text-gray-600">Ingat saya</span>
                    </label>
                </div>

                <!-- Login Button -->
                <button type="submit" class="w-full py-4 bg-green-600 text-white rounded-xl font-bold text-lg hover:bg-green-700 transition-all transform hover:scale-[1.02] shadow-lg btn-ripple flex items-center justify-center gap-2">
                    <i class="fas fa-sign-in-alt"></i>
                    Masuk
                </button>
            </form>

            <!-- Back to Home -->
            <div class="text-center mt-6">
                <a href="{{ route('home') }}" data-style-guide-skip class="inline-flex items-center gap-2 text-gray-600 hover:text-blue-600 font-medium transition">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Beranda
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-blue-100 text-sm">
            <p>&copy; 2026 Disdukcapil Kabupaten Toba</p>
        </div>
    </div>

    <!-- Load SweetAlert Helper Global -->
    <script src="{{ asset('js/sweetalert-helper.js') }}"></script>
    <script src="{{ asset('js/sweetalert-disdukcapil.js') }}?v={{ filemtime(public_path('js/sweetalert-disdukcapil.js')) }}"></script>

    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('.bg-blue-50, .bg-green-50, .bg-yellow-50, .bg-red-50');
            flashMessages.forEach(function(message) {
                setTimeout(function() {
                    message.style.opacity = '0';
                    message.style.transition = 'opacity 0.5s ease';
                    setTimeout(function() {
                        message.remove();
                    }, 500);
                }, 5000);
            });

            // Show SweetAlert for session messages menggunakan helper global
            @if(session('logout_success'))
                SwalHelper.toastSuccess(@json(session('logout_success')));
            @endif

            @if(session('success'))
                SwalHelper.toastSuccess(@json(session('success')));
            @endif

            @if(session('error'))
                SwalHelper.toastError(@json(session('error')));
            @endif

            @if(session('info'))
                SwalHelper.toastInfo(@json(session('info')));
            @endif

            @if(session('warning'))
                SwalHelper.toastWarning(@json(session('warning')));
            @endif
        });

        // Enhanced form submission dengan loading
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';

            // Re-enable after 30 seconds in case of error
            setTimeout(function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 30000);
        });
    </script>
<script src="{{ asset('js/page-loading.js') }}?v={{ filemtime(public_path('js/page-loading.js')) }}"></script>
<script src="{{ asset('js/style-guide-enhancer.js') }}?v={{ filemtime(public_path('js/style-guide-enhancer.js')) }}"></script>
    {{-- SweetAlert Final Fix --}}
    <script src="{{ asset('js/swal-final-fix.js') }}"></script>
</body>
</html>
