<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi Keamanan - Disdukcapil Kabupaten Toba</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_toba.jpeg') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style-guide.css') }}">
    <link rel="stylesheet" href="@assetV('css/page-loading.css')">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/sweetalert-helper.js') }}"></script>
    <script src="@assetV('js/swal-final-fix.js')"></script>
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
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Float Animation */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        /* Pulse Animation */
        @keyframes pulse-ring {
            0% {
                transform: scale(0.8);
                opacity: 1;
            }

            100% {
                transform: scale(1.3);
                opacity: 0;
            }
        }

        .pulse-ring {
            animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
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

        /* Shake Animation for Error */
        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }

        .shake-animation {
            animation: shake 0.5s ease-in-out;
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
        <div class="absolute w-96 h-96 bg-white/10 rounded-full -bottom-48 -right-48 float-animation"
            style="animation-delay: 2s;"></div>
        <div class="absolute w-48 h-48 bg-white/10 rounded-full top-1/4 right-1/4 float-animation"
            style="animation-delay: 4s;"></div>
    </div>

    <!-- Verification Container -->
    <div class="auth-shell relative z-10 w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="relative inline-flex items-center justify-center">
                <div class="absolute w-24 h-24 bg-blue-400/30 rounded-full pulse-ring"></div>
                <div
                    class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-2xl shadow-xl mb-4 float-animation overflow-hidden border-4 border-white/30">
                    <img src="{{ asset('images/logo_toba.jpeg') }}" alt="Logo Kabupaten Toba"
                        class="w-full h-full object-contain">
                </div>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-2 mt-6">Verifikasi Keamanan</h1>
            <p class="text-blue-100 text-lg">Langkah Kedua</p>
        </div>

        <!-- Verification Card -->
        <div class="auth-card bg-white rounded-3xl shadow-2xl p-8">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-1">Password Benar!</h2>
                <p class="text-gray-600">Silakan jawab pertanyaan keamanan</p>
            </div>

            <!-- User Info -->
            <div class="bg-blue-50 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $user->name }}</p>
                    </div>
                </div>
            </div>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4 shake-animation">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-times-circle"></i>
                        <span>{{ $errors->first('security_answer') }}</span>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.verify.submit') }}" class="space-y-5" id="verifyForm">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">

                <!-- Security Question Display -->
                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl p-5 border-2 border-blue-200">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-shield-alt text-white"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Pertanyaan Keamanan Anda:</p>
                            <p class="text-lg font-semibold text-gray-800">{{ $user->securityQuestion->question }}</p>
                        </div>
                    </div>
                </div>

                <!-- Security Answer Input -->
                <div class="space-y-2">
                    <label for="security_answer" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-key mr-2"></i>Jawaban Anda
                    </label>
                    <input type="text" id="security_answer" name="security_answer" required
                        placeholder="Ketik jawaban Anda..."
                        class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-lg"
                        autofocus>
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Jawaban tidak bersifat case-sensitive (huruf besar/kecil tidak berpengaruh)
                    </p>
                </div>

                <!-- Attempts Warning -->
                @if (session('attempts'))
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p class="text-sm">Kesempatan tersisa: <strong>{{ session('attempts') }}</strong> kali</p>
                        </div>
                    </div>
                @endif

                <!-- Verify Button -->
                <button type="submit"
                    class="w-full py-4 bg-green-600 text-white rounded-xl font-bold text-lg hover:bg-green-700 transition-all transform hover:scale-[1.02] shadow-lg btn-ripple flex items-center justify-center gap-2">
                    <i class="fas fa-unlock"></i>
                    Verifikasi & Masuk
                </button>
            </form>

            <!-- Divider -->
            <div class="flex items-center gap-4 my-6">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-sm text-gray-400">batal</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <!-- Cancel Button -->
            <div class="text-center">
                <a href="{{ route('login') }}" data-style-guide-skip
                    class="inline-flex items-center gap-2 text-gray-600 hover:text-red-600 font-medium transition">
                    <i class="fas fa-times"></i>
                    Batalkan dan Kembali ke Login
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-blue-100 text-sm">
            <p>&copy; 2025 Disdukcapil Kabupaten Toba</p>
        </div>
    </div>

     <script>
        window.addEventListener('load', function() {
            var securityAnswerInput = document.getElementById('security_answer');
            if (securityAnswerInput) {
                securityAnswerInput.focus();
            }

            @if(session('success'))
                SwalHelper.toastSuccess("{{ session('success') }}");
            @endif

            @if(session('error'))
                SwalHelper.toastError("{{ session('error') }}");
            @endif

            @if(session('info'))
                SwalHelper.toastInfo("{{ session('info') }}");
            @endif

            @if(session('warning'))
                SwalHelper.toastWarning("{{ session('warning') }}");
            @endif

            @if ($errors->any())
                var form = document.getElementById('verifyForm');
                if (form) {
                    form.classList.add('shake-animation');
                    setTimeout(function() {
                        form.classList.remove('shake-animation');
                    }, 500);
                }

                var errorMessages = document.querySelectorAll('.bg-red-50');
                errorMessages.forEach(function(message) {
                    setTimeout(function() {
                        message.style.opacity = '0';
                        message.style.transition = 'opacity 0.5s ease';
                        setTimeout(function() {
                            message.remove();
                        }, 500);
                    }, 5000);
                });
            @endif
        });
    </script>
<script src="@assetV('js/page-loading.js')"></script>
<script src="@assetV('js/style-guide-enhancer.js')"></script>
    {{-- SweetAlert Final Fix --}}
    <script src="{{ asset('js/swal-final-fix.js') }}"></script>
</body>

</html>
