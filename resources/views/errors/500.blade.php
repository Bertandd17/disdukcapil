<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Terjadi Kesalahan Server</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_toba.jpeg') }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style-guide.css') }}">
    <link rel="stylesheet" href="{{ asset_v('css/page-loading.css') }}">
    <link rel="stylesheet" href="{{ asset_v('css/error-pages.css') }}">

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

        /* Shake Animation */
        @keyframes shake {
            0%, 100% {
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
    </style>
</head>

<body class="error-page bg-animated min-h-screen flex justify-center p-3 sm:p-4 relative">
@include('components.page-loading')

    <!-- Background Particles -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="floating-shapes" style="top: 20%; left: 10%;"></div>
        <div class="floating-shapes" style="top: 60%; right: 20%; animation-delay: 2s;"></div>
        <div class="floating-shapes" style="bottom: 20%; left: 30%; animation-delay: 4s;"></div>
    </div>

    <!-- Error Container -->
    <div class="relative z-10 error-page__container w-full mx-auto">
        <!-- Logo & Icon -->
        <div class="text-center error-page__hero">
            <div class="relative inline-flex items-center justify-center">
                <div class="absolute error-page__logo-ring bg-blue-400/30 rounded-full pulse-ring"></div>
                <div class="error-page__logo-wrap bg-white rounded-2xl shadow-xl float-animation overflow-hidden border-4 border-white/30 flex items-center justify-center mx-auto">
                    <img src="{{ asset('images/logo_toba.jpeg') }}" alt="Logo Kabupaten Toba" class="w-full h-full object-contain">
                </div>
            </div>

            <h1 class="error-page__code font-black text-white mb-2 sm:mb-3 shake-animation">500</h1>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-white mb-1 sm:mb-2">Terjadi Kesalahan Server</p>
            <p class="text-blue-100 text-sm sm:text-base px-2">Maaf, terjadi kesalahan pada server kami. Tim kami sedang memperbaikinya.</p>
        </div>

        <!-- Error Card -->
        <div class="bg-white rounded-2xl shadow-2xl error-page__card">
            <div class="text-center error-page__card-inner mb-4 sm:mb-6">
                <div class="error-page__card-icon bg-red-100 rounded-2xl flex items-center justify-center mx-auto">
                    <i class="fas fa-server text-red-600"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-1 sm:mb-2">Kesalahan Tidak Terduga</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-0">Kami mengalami masalah teknis sementara waktu. Jangan khawatir, ini bukan kesalahan dari Anda.</p>
            </div>

            <div class="flex flex-col error-page__tips-wrap error-page__tips mb-4 sm:mb-6">
                <div class="flex items-start error-page__tip bg-gray-50 rounded-xl hover:bg-blue-50 transition">
                    <div class="error-page__tip-icon bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-history text-blue-600"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800 text-sm sm:text-base">Coba Lagi Nanti</p>
                        <p class="text-xs sm:text-sm text-gray-600">Kesalahan ini bersifat sementara. Silakan coba lagi dalam beberapa menit.</p>
                    </div>
                </div>

                <div class="group flex items-start error-page__tip bg-gray-50 rounded-xl hover:bg-blue-50 transition">
                    <div class="error-page__tip-icon bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-home text-purple-600"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800 text-sm sm:text-base group-hover:text-blue-600 transition">Kembali ke Beranda</p>
                        <p class="text-xs sm:text-sm text-gray-600">Kembali ke halaman utama dan coba aksi lain.</p>
                    </div>
                </div>

                <div class="flex items-start error-page__tip bg-gray-50 rounded-xl hover:bg-blue-50 transition">
                    <div class="error-page__tip-icon bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-headset text-amber-600"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800 text-sm sm:text-base">Lapor Masalah</p>
                        <p class="text-xs sm:text-sm text-gray-600">Jika masalah berlanjut, hubungi tim admin untuk bantuan.</p>
                    </div>
                </div>

                <div class="flex items-start error-page__tip bg-blue-50 rounded-xl">
                    <div class="error-page__tip-icon bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-600"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800 text-sm sm:text-base">Error Reference</p>
                        <p class="text-xs sm:text-sm text-gray-600">Simpan error ini jika Anda perlu melaporkannya ke admin.</p>
                        <code class="mt-1.5 block bg-gray-100 px-2 py-1.5 rounded text-[11px] sm:text-xs text-gray-600 break-all">Error ID: {{ time() }}</code>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-3 error-page__actions">
                <button type="button" onclick="location.reload()" class="error-page__btn bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg flex items-center justify-center gap-2">
                    <i class="fas fa-sync-alt"></i>
                    Coba Lagi
                </button>
                <a href="{{ route('home') }}" class="error-page__btn bg-gray-200 text-gray-800 rounded-xl font-bold hover:bg-gray-300 transition-all shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-home"></i>
                    Beranda
                </a>
                <a href="mailto:admin@disdukcapil.go.id" class="error-page__btn bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-all shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-envelope"></i>
                    Lapor
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-blue-100 text-xs sm:text-sm px-2">
            <p>&copy; {{ date('Y') }} Disdukcapil Kabupaten Toba. Hak Cipta Dilindungi.</p>
            <p class="text-blue-200 mt-1">Tim teknis kami telah diberitahu tentang masalah ini</p>
        </div>
    </div>

<script src="{{ asset_v('js/page-loading.js') }}"></script>
<script src="{{ asset_v('js/style-guide-enhancer.js') }}"></script>
</body>
</html>
