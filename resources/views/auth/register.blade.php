<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Admin - Disdukcapil Kabupaten Toba</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_toba.jpeg') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style-guide.css') }}">
    <link rel="stylesheet" href="@assetV('css/page-loading.css')">
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

    @if(auth()->check())
        <script>window.location.href = "{{ route('admin.dashboard') }}";</script>
    @endif
    {{-- SweetAlert Final Fix --}}
    <link rel="stylesheet" href="{{ asset('css/swal-final-fix.css') }}">
</head>
<body class="bg-animated min-h-screen flex items-center justify-center p-4">
@include('components.page-loading')

    <!-- Background Particles -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute w-64 h-64 bg-white/10 rounded-full -top-32 -left-32 float-animation"></div>
        <div class="absolute w-96 h-96 bg-white/10 rounded-full -bottom-48 -right-48 float-animation" style="animation-delay: 2s;"></div>
        <div class="absolute w-48 h-48 bg-white/10 rounded-full top-1/4 right-1/4 float-animation" style="animation-delay: 4s;"></div>
    </div>

    <!-- Register Container -->
    <div class="relative z-10 w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-2xl shadow-xl mb-4 float-animation overflow-hidden border-4 border-white/30">
                <img src="{{ asset('images/logo_toba.jpeg') }}" alt="Logo Kabupaten Toba" class="w-full h-full object-contain">
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-2">Disdukcapil Toba</h1>
            <p class="text-blue-100 text-lg">Registrasi Admin</p>
        </div>

        <!-- Register Card -->
        <div class="bg-white rounded-3xl shadow-2xl p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-1">Buat Akun Admin</h2>
                <p class="text-gray-600">Registrasi hanya dapat dilakukan sekali</p>
            </div>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-exclamation-circle mt-0.5"></i>
                        <div class="flex-1">
                            <p class="font-semibold">Terjadi kesalahan:</p>
                            <ul class="list-disc list-inside text-sm mt-1 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.register.submit') }}" class="space-y-4" id="registerForm">
                @csrf

                <!-- Name Input -->
                <div class="input-group">
                    <input type="text" id="name" name="name" required placeholder=" "
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition peer placeholder-shown:border-gray-300 placeholder-shown:focus:border-blue-500"
                           value="{{ old('name') }}">
                    <label for="name" class="absolute left-4 top-3 text-gray-400 pointer-events-none transition-all duration-300 peer-focus:text-blue-600 peer-focus:-translate-y-6 peer-focus:scale-90 peer-placeholder-shown:opacity-100">
                        <i class="fas fa-user mr-2"></i>Nama Lengkap
                    </label>
                </div>

                <!-- Username Input -->
                <div class="input-group">
                    <input type="text" id="username" name="username" required placeholder=" "
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition peer placeholder-shown:border-gray-300 placeholder-shown:focus:border-blue-500"
                           value="{{ old('username') }}">
                    <label for="username" class="absolute left-4 top-3 text-gray-400 pointer-events-none transition-all duration-300 peer-focus:text-blue-600 peer-focus:-translate-y-6 peer-focus:scale-90 peer-placeholder-shown:opacity-100">
                        <i class="fas fa-at mr-2"></i>Username
                    </label>
                </div>

                <!-- Password Input -->
                <div class="input-group">
                    <div class="relative">
                        <input type="password" id="password" name="password" required placeholder=" "
                               class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition peer placeholder-shown:border-gray-300 placeholder-shown:focus:border-blue-500">
                        <label for="password" class="absolute left-4 top-3 text-gray-400 pointer-events-none transition-all duration-300 peer-focus:text-blue-600 peer-focus:-translate-y-6 peer-focus:scale-90 peer-placeholder-shown:opacity-100">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <button type="button" onclick="togglePassword('password', 'eyeIcon1')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-eye" id="eyeIcon1"></i>
                        </button>
                    </div>
                    <!-- Password Strength Indicator -->
                    <div class="mt-2 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                        <div id="strengthMeter" class="h-full strength-meter transition-all duration-300"></div>
                    </div>
                    <p id="strengthText" class="text-xs mt-1 text-gray-500"></p>
                </div>

                <!-- Confirm Password Input -->
                <div class="input-group">
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder=" "
                               class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition peer placeholder-shown:border-gray-300 placeholder-shown:focus:border-blue-500">
                        <label for="password_confirmation" class="absolute left-4 top-3 text-gray-400 pointer-events-none transition-all duration-300 peer-focus:text-blue-600 peer-focus:-translate-y-6 peer-focus:scale-90 peer-placeholder-shown:opacity-100">
                            <i class="fas fa-lock mr-2"></i>Konfirmasi Password
                        </label>
                        <button type="button" onclick="togglePassword('password_confirmation', 'eyeIcon2')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-eye" id="eyeIcon2"></i>
                        </button>
                    </div>
                </div>

                <!-- Security Question Select -->
                <div class="input-group">
                    <select id="security_question_id" name="security_question_id" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition peer appearance-none bg-white placeholder-shown:border-gray-300 placeholder-shown:focus:border-blue-500 cursor-pointer">
                        <option value="" disabled selected></option>
                        @foreach($securityQuestions as $question)
                            <option value="{{ $question->id }}" {{ old('security_question_id') == $question->id ? 'selected' : '' }}>
                                {{ $question->question }}
                            </option>
                        @endforeach
                    </select>
                    <label for="security_question_id" class="absolute left-4 top-3 text-gray-400 pointer-events-none transition-all duration-300 peer-focus:text-blue-600 peer-focus:-translate-y-6 peer-focus:scale-90 peer-placeholder-shown:opacity-100">
                        <i class="fas fa-shield-alt mr-2"></i>Pertanyaan Keamanan
                    </label>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>

                <!-- Security Answer Input -->
                <div class="input-group">
                    <input type="text" id="security_answer" name="security_answer" required placeholder=" "
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition peer placeholder-shown:border-gray-300 placeholder-shown:focus:border-blue-500"
                           value="{{ old('security_answer') }}">
                    <label for="security_answer" class="absolute left-4 top-3 text-gray-400 pointer-events-none transition-all duration-300 peer-focus:text-blue-600 peer-focus:-translate-y-6 peer-focus:scale-90 peer-placeholder-shown:opacity-100">
                        <i class="fas fa-key mr-2"></i>Jawaban Pertanyaan Keamanan
                    </label>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Jawaban akan dienkripsi untuk keamanan
                    </p>
                </div>

                <!-- Warning Message -->
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-exclamation-triangle mt-0.5"></i>
                        <p class="text-sm">
                            <strong>PENTING:</strong> Registrasi hanya dapat dilakukan sekali. Pastikan Anda mengingat username, password, dan jawaban pertanyaan keamanan dengan baik.
                        </p>
                    </div>
                </div>

                <!-- Register Button -->
                <button type="submit" class="w-full py-4 bg-green-600 text-white rounded-xl font-bold text-lg hover:bg-green-700 transition-all transform hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center gap-2 relative overflow-hidden group border-2 border-emerald-300">
                    <span class="relative z-10 flex items-center gap-2">
                        <i class="fas fa-user-plus"></i>
                        Daftar Sekarang
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-green-600 to-emerald-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </button>
            </form>

            <!-- Divider -->
            <div class="flex items-center gap-4 my-6">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-sm text-gray-400">atau</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

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
    <script src="@assetV('js/swal-final-fix.js')"></script>

    <script>
        // Toggle Password Visibility
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);

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

        // Password Strength Checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.getElementById('strengthMeter');
            const strengthText = document.getElementById('strengthText');

            let strength = 0;

            // Check password length
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;

            // Check for lowercase
            if (/[a-z]/.test(password)) strength++;

            // Check for uppercase
            if (/[A-Z]/.test(password)) strength++;

            // Check for numbers
            if (/[0-9]/.test(password)) strength++;

            // Check for special characters
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            // Reset classes
            strengthMeter.className = 'strength-meter h-full transition-all duration-300';
            strengthText.className = 'text-xs mt-1';

            if (strength <= 2) {
                strengthMeter.classList.add('strength-weak');
                strengthText.textContent = 'Lemah - Tambahkan karakter, angka, dan simbol';
                strengthText.classList.add('text-red-500');
            } else if (strength <= 4) {
                strengthMeter.classList.add('strength-medium');
                strengthText.textContent = 'Sedang - Bisa lebih kuat';
                strengthText.classList.add('text-yellow-500');
            } else {
                strengthMeter.classList.add('strength-strong');
                strengthText.textContent = 'Kuat - Password yang baik!';
                strengthText.classList.add('text-green-500');
            }
        });

        // Form submission dengan SweetAlert Helper
        const registerForm = document.getElementById('registerForm');
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');

            // Tampilkan konfirmasi menggunakan helper global
            SwalHelper.confirm(
                'Konfirmasi Registrasi',
                'Pastikan semua data yang Anda masukkan sudah benar. Registrasi hanya dapat dilakukan sekali. Lanjutkan?',
                function() {
                    // Disable button dan ubah teks
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Memproses...';

                    // Tampilkan loading menggunakan helper global
                    SwalHelper.loading('Memproses Registrasi', 'Sedang membuat akun admin...');

                    // Submit form dengan delay untuk update UI
                    setTimeout(() => {
                        registerForm.submit();
                    }, 500);
                }
            );
        });

        // Tampilkan pesan error/success dari session (jika ada)
        @if(session('error'))
        SwalHelper.error(
            'Registrasi gagal.',
            @json(session('error_solution') ?? session('error') ?? 'Periksa data yang Anda masukkan, lalu coba lagi.')
        );
        @endif

        @if(session('success'))
        SwalHelper.success(@json(session('success')));
        @endif

        @if(session('warning'))
        SwalHelper.warning(@json(session('warning')));
        @endif
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
        .input-group input:not(:placeholder-shown) ~ label,
        .input-group select:focus ~ label,
        .input-group select:valid ~ label {
            transform: translateY(-24px) scale(0.85);
            color: #0052CC;
        }

        .input-group label {
            transition: all 0.3s ease;
        }

        /* Strength Meter */
        .strength-meter {
            transition: all 0.3s ease;
        }

        .strength-weak {
            background: linear-gradient(to right, #ef4444 0%, #ef4444 33%);
        }

        .strength-medium {
            background: linear-gradient(to right, #f59e0b 0%, #f59e0b 50%, #f59e0b 100%);
        }

        .strength-strong {
            background: linear-gradient(to right, #10b981 0%, #10b981 50%, #10b981 100%);
        }
    </style>
<script src="@assetV('js/page-loading.js')"></script>
<script src="@assetV('js/style-guide-enhancer.js')"></script>
    {{-- SweetAlert Final Fix --}}
    <script src="{{ asset('js/swal-final-fix.js') }}"></script>
</body>
</html>
