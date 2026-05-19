<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_toba.jpeg') }}">

    <!-- Tailwind CSS -->
    @include('partials.vite-assets')

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style-guide.css') }}">
    <link rel="stylesheet" href="{{ asset('css/page-loading.css') }}?v={{ filemtime(public_path('css/page-loading.css')) }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
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

        /* Bounce Animation */
        @keyframes bounce-slow {
            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .animate-bounce-slow {
            animation: bounce-slow 2s ease-in-out infinite;
        }

        /* Floating Shapes */
        .floating-shapes {
            position: absolute;
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }
    </style>
</head>

<body class="bg-animated min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
@include('components.page-loading')

    <!-- Background Particles -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="floating-shapes" style="top: 20%; left: 10%;"></div>
        <div class="floating-shapes" style="top: 60%; right: 20%; animation-delay: 2s;"></div>
        <div class="floating-shapes" style="bottom: 20%; left: 30%; animation-delay: 4s;"></div>
    </div>

    <!-- Error Container -->
    <div class="relative z-10 max-w-2xl w-full mx-auto">
        <!-- Logo & Icon -->
        <div class="text-center mb-8">
            <div class="relative inline-flex items-center justify-center">
                <div class="absolute w-32 h-32 bg-blue-400/30 rounded-full pulse-ring"></div>
                <div class="w-24 h-24 bg-white rounded-2xl shadow-xl mb-6 float-animation overflow-hidden border-4 border-white/30 flex items-center justify-center mx-auto">
                    <img src="{{ asset('images/logo_toba.jpeg') }}" alt="Logo Kabupaten Toba" class="w-full h-full object-contain">
                </div>
            </div>

            <div class="w-32 h-32 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-6 float-animation overflow-hidden">
                <i class="fas fa-ban text-6xl text-white"></i>
            </div>

            <h1 class="text-9xl font-black text-white mb-4 animate-bounce-slow">403</h1>
            <p class="text-2xl md:text-3xl font-bold text-white mb-2">Akses Ditolak</p>
            <p class="text-blue-100 text-lg">Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        </div>

        <!-- Error Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12 mb-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-4xl text-red-600"></i>
                </div>
                <h3 class="text-xl md:text-2xl font-bold text-gray-800 mb-2">Akses Tidak Diizinkan</h3>
                <p class="text-gray-600 mb-6">Anda tidak memiliki izin yang cukup untuk mengakses halaman ini. Silakan login dengan akun yang sesuai.</p>
            </div>

            <div class="space-y-4 mb-8">
                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-blue-50 transition">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-sign-in-alt text-blue-600"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Login Terlebih Dahulu</p>
                        <p class="text-sm text-gray-600">Pastikan Anda sudah login dengan akun yang memiliki izin akses.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-blue-50 transition">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user-shield text-purple-600"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Periksa Role Akun</p>
                        <p class="text-sm text-gray-600">Akun Anda mungkin tidak memiliki role yang cukup untuk halaman ini.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-blue-50 transition">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-key text-amber-600"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Hubungi Admin</p>
                        <p class="text-sm text-gray-600">Jika Anda merasa ini kesalahan, hubungi administrator sistem.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 bg-blue-50 rounded-xl">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-600"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Kode Error</p>
                        <p class="text-sm text-gray-600">HTTP 403 Forbidden - Anda tidak memiliki akses ke resource ini.</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('login') }}" class="flex-1 px-6 py-4 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl font-bold hover:from-green-700 hover:to-green-800 transition-all shadow-lg flex items-center justify-center gap-2">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>
                <a href="{{ route('home') }}" class="flex-1 px-6 py-4 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 rounded-xl font-bold hover:from-gray-200 hover:to-gray-300 transition-all shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-home"></i>
                    Beranda
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-blue-100 text-sm">
            <p>&copy; {{ date('Y') }} Disdukcapil Kabupaten Toba. Hak Cipta Dilindungi.</p>
            <p class="text-blue-200 mt-1">Sistem keamanan kami melindungi data dan akses pengguna.</p>
        </div>
    </div>

<script src="{{ asset('js/page-loading.js') }}?v={{ filemtime(public_path('js/page-loading.js')) }}"></script>
<script src="{{ asset('js/style-guide-enhancer.js') }}?v={{ filemtime(public_path('js/style-guide-enhancer.js')) }}"></script>
</body>
</html>
