<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disdukcapil Kabupaten Toba - Portal Pelayanan Kependudukan</title>

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
                        blue: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#0052CC',
                            700: '#003d99',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        teal: {
                            500: '#00B8D9',
                            600: '#0097b8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
            scroll-behavior: smooth;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #0052CC;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #003d99;
        }

        /* Hero Background Figures */
        .hero-bg-left,
        .hero-bg-right {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 450px;
            height: 650px;
            z-index: 1;
            opacity: 0.25;
            pointer-events: none;
        }

        .hero-bg-left {
            left: -100px;
        }

        .hero-bg-right {
            right: -100px;
        }

        .hero-figure {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .hero-figure-emoji {
            font-size: 380px;
            filter: blur(1px);
            animation: figureFloat 6s ease-in-out infinite;
        }

        .hero-figure-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-top: -30px;
        }

        .hero-figure-title {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .hero-bg-right .hero-figure-emoji {
            animation: figureFloat 6s ease-in-out infinite reverse;
        }

        @keyframes figureFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* News Modal */
        .news-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .news-modal-overlay.active {
            display: flex;
        }

        .news-modal {
            background: white;
            border-radius: 24px;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes modalSlideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Hide scrollbar for tabs */
        .tabs::-webkit-scrollbar {
            display: none;
        }
        .tabs {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease-out;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Skeleton Loading */
        .skeleton {
            background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 8px;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }

        .skeleton-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .skeleton-text {
            height: 16px;
            margin-bottom: 8px;
        }

        .skeleton-title {
            height: 24px;
            width: 60%;
            margin-bottom: 12px;
        }

        .skeleton-circle {
            border-radius: 50%;
        }

    </style>
</head>
<body class="bg-gray-50">
@include('components.page-loading')

    <!-- Header Navigation -->
    <header class="fixed top-0 left-0 right-0 bg-white/95 backdrop-blur-md shadow-sm z-50 transition-all duration-300" id="mainHeader">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 hover:scale-105 transition-transform">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-landmark text-white text-xl"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-gray-800">Disdukcapil Toba</span>
                        <p class="text-xs text-gray-500 -mt-1">Kabupaten Toba</p>
                    </div>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('home') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-blue-600 bg-blue-50">
                        <i class="fas fa-home mr-2"></i>Beranda
                    </a>
                    <a href="#" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition">
                        <i class="fas fa-ticket-alt mr-2"></i>Antrian Online
                    </a>
                    <a href="#" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition">
                        <i class="fas fa-rocket mr-2"></i>Layanan Mandiri
                    </a>
                    <a href="#" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition">
                        <i class="fas fa-chart-line mr-2"></i>Statistik
                    </a>
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-green-600 hover:bg-green-50 transition">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                </nav>

                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-bars text-gray-600"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div id="mobileMenu" class="md:hidden hidden bg-white border-t">
            <nav class="px-4 py-3 space-y-1">
                <a href="{{ route('home') }}" class="block px-4 py-2 rounded-lg text-sm font-medium text-blue-600 bg-blue-50">
                    <i class="fas fa-home mr-2"></i>Beranda
                </a>
                <a href="#" class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50">
                    <i class="fas fa-ticket-alt mr-2"></i>Antrian Online
                </a>
                <a href="#" class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50">
                    <i class="fas fa-rocket mr-2"></i>Layanan Mandiri
                </a>
                <a href="#" class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50">
                    <i class="fas fa-chart-line mr-2"></i>Statistik
                </a>
                <a href="{{ route('login') }}" class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-green-600 hover:bg-green-50">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </a>
            </nav>
        </div>
    </header>

    <main class="pt-16">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="fixed top-20 right-4 z-50 max-w-md">
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-lg">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if (session('info'))
            <div class="fixed top-20 right-4 z-50 max-w-md">
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl shadow-lg">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-info-circle"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div class="fixed top-20 right-4 z-50 max-w-md">
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-xl shadow-lg">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Hero Section -->
        <section class="relative min-h-[600px] bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 text-white overflow-hidden">
            <!-- Background Figures -->
            <div class="hero-bg-left">
                <div class="hero-figure">
                    <div class="hero-figure-emoji"><i class="fas fa-user-tie"></i></div>
                    <div class="hero-figure-name">[Nama Bupati Toba]</div>
                    <div class="hero-figure-title">Bupati Kabupaten Toba</div>
                </div>
            </div>

            <div class="hero-bg-right">
                <div class="hero-figure">
                    <div class="hero-figure-emoji"><i class="fas fa-user"></i></div>
                    <div class="hero-figure-name">[Nama Wakil Bupati Toba]</div>
                    <div class="hero-figure-title">Wakil Bupati Kabupaten Toba</div>
                </div>
            </div>

            <!-- Hero Content -->
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/20 backdrop-blur-sm text-sm font-medium mb-6 animate-fade-in-up">
                        <i class="fas fa-rocket"></i>
                        Platform Digital Terintegrasi
                    </span>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 animate-fade-in-up" style="animation-delay: 0.1s;">
                        Urus Dokumen Kependudukan
                        <span class="block text-blue-200">Kini Lebih Mudah & Cepat</span>
                    </h1>
                    <p class="text-lg md:text-xl text-blue-100 mb-8 animate-fade-in-up" style="animation-delay: 0.2s;">
                        Layanan pendaftaran, pencatatan sipil, dan informasi kependudukan yang
                        modern, transparan, dan dapat diakses kapan saja, di mana saja.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in-up" style="animation-delay: 0.3s;">
                        <a href="#" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-blue-700 rounded-xl font-semibold hover:bg-blue-50 transition-all hover:scale-105 shadow-lg">
                            <i class="fas fa-rocket"></i>
                            Layanan Mandiri
                        </a>
                        <a href="#" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-blue-500/30 backdrop-blur-sm border-2 border-white/30 text-white rounded-xl font-semibold hover:bg-blue-500/50 transition-all hover:scale-105">
                            <i class="fas fa-chart-line"></i>
                            Lihat Statistik
                        </a>
                    </div>
                </div>
            </div>

            <!-- Wave Divider -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#f9fafb"/>
                </svg>
            </div>
        </section>

        <!-- Welcome Section -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-8 md:p-12 text-white text-center reveal">
                    <div class="text-4xl mb-4"><i class="fas fa-hand-wave"></i></div>
                    <h2 class="text-2xl md:text-3xl font-bold mb-3">Selamat Datang di Portal Disdukcapil</h2>
                    <p class="text-blue-100 text-lg max-w-3xl mx-auto">
                        Kabupaten Toba berkomitmen memberikan pelayanan administrasi kependudukan
                        kelas dunia dengan memanfaatkan teknologi terkini untuk kenyamanan masyarakat.
                    </p>
                </div>
            </div>
        </section>

        <!-- Profil Disdukcapil Section -->
        <section id="profil" class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 reveal">
                    <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider">Tentang Kami</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Profil Disdukcapil</h2>
                    <p class="text-gray-600 mt-3 max-w-2xl mx-auto">
                        Mengenal lebih dekat visi, misi, dan dedikasi kami dalam melayani masyarakat
                    </p>
                </div>

                <!-- Horizontal Tabs Navigation -->
                <div class="bg-white rounded-2xl shadow-lg p-2 mb-8 overflow-x-auto reveal">
                    <div class="tabs flex gap-2 min-w-max justify-center">
                        <button onclick="switchTab(event, 'visi')" class="tab-btn active flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold whitespace-nowrap transition-all">
                            <i class="fas fa-bullseye"></i>
                            Visi & Misi
                        </button>
                        <button onclick="switchTab(event, 'motto')" class="tab-btn flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold whitespace-nowrap transition-all">
                            <i class="fas fa-quote-left"></i>
                            Motto & Nilai
                        </button>
                        <button onclick="switchTab(event, 'sejarah')" class="tab-btn flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold whitespace-nowrap transition-all">
                            <i class="fas fa-history"></i>
                            Sejarah
                        </button>
                        <button onclick="switchTab(event, 'penghargaan')" class="tab-btn flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold whitespace-nowrap transition-all">
                            <i class="fas fa-trophy"></i>
                            Penghargaan
                        </button>
                        <button onclick="switchTab(event, 'dasar-hukum')" class="tab-btn flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold whitespace-nowrap transition-all">
                            <i class="fas fa-balance-scale"></i>
                            Dasar Hukum
                        </button>
                        <button onclick="switchTab(event, 'tugas-fungsi')" class="tab-btn flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold whitespace-nowrap transition-all">
                            <i class="fas fa-tasks"></i>
                            Tugas & Fungsi
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="tab-content reveal">
                    <div id="visi" class="tab-panel active">
                        <div class="bg-white rounded-2xl shadow-lg p-8">
                            <div class="grid md:grid-cols-2 gap-8">
                                <div>
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-bullseye text-2xl text-blue-600"></i>
                                        </div>
                                        <h3 class="text-2xl font-bold text-gray-800">Visi</h3>
                                    </div>
                                    <p class="text-gray-700 text-lg leading-relaxed bg-blue-50 rounded-xl p-6 border-l-4 border-blue-500">
                                        "Terwujudnya masyarakat Kabupaten Toba yang tertib administrasi kependudukan dan layanan pencatatan sipil yang berkualitas"
                                    </p>
                                </div>
                                <div>
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-rocket text-2xl text-teal-600"></i>
                                        </div>
                                        <h3 class="text-2xl font-bold text-gray-800">Misi</h3>
                                    </div>
                                    <ul class="space-y-3">
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-blue-600 mt-1"></i>
                                            <span class="text-gray-700">Meningkatkan kualitas pelayanan administrasi kependudukan</span>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-blue-600 mt-1"></i>
                                            <span class="text-gray-700">Mengembangkan sistem informasi administrasi kependudukan terpadu</span>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-blue-600 mt-1"></i>
                                            <span class="text-gray-700">Meningkatkan profesionalisme SDM aparatur</span>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-blue-600 mt-1"></i>
                                            <span class="text-gray-700">Mewujudkan pelayanan prima yang transparan dan akuntabel</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="motto" class="tab-panel hidden">
                        <div class="bg-white rounded-2xl shadow-lg p-8">
                            <div class="text-center mb-8">
                                <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-quote-left text-3xl text-white"></i>
                                </div>
                                <h3 class="text-3xl font-bold text-gray-800 mb-2">Motto Pelayanan</h3>
                            </div>
                            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-8 text-white text-center mb-8">
                                <p class="text-2xl md:text-3xl font-bold">"CEPAT, TEPAT, DAN RAMAH"</p>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800 mb-6 text-center">Nilai-Nilai Pelayanan</h4>
                            <div class="grid md:grid-cols-3 gap-6">
                                <div class="text-center p-6 bg-blue-50 rounded-xl">
                                    <div class="w-14 h-14 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-clock text-2xl text-white"></i>
                                    </div>
                                    <h5 class="font-bold text-gray-800 mb-2">Cepat</h5>
                                    <p class="text-gray-600 text-sm">Pelayanan efisien dengan waktu proses yang optimal</p>
                                </div>
                                <div class="text-center p-6 bg-teal-50 rounded-xl">
                                    <div class="w-14 h-14 bg-teal-500 rounded-xl flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-bullseye text-2xl text-white"></i>
                                    </div>
                                    <h5 class="font-bold text-gray-800 mb-2">Tepat</h5>
                                    <p class="text-gray-600 text-sm">Hasil layanan akurat dan sesuai ketentuan peraturan</p>
                                </div>
                                <div class="text-center p-6 bg-purple-50 rounded-xl">
                                    <div class="w-14 h-14 bg-purple-500 rounded-xl flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-heart text-2xl text-white"></i>
                                    </div>
                                    <h5 class="font-bold text-gray-800 mb-2">Ramah</h5>
                                    <p class="text-gray-600 text-sm">Pelayanan dengan senyum dan sikap yang menyenangkan</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="sejarah" class="tab-panel hidden">
                        <div class="bg-white rounded-2xl shadow-lg p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-history text-2xl text-amber-600"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800">Sejarah Disdukcapil</h3>
                            </div>
                            <div class="space-y-6">
                                <p class="text-gray-700 leading-relaxed">
                                    Dinas Kependudukan dan Pencatatan Sipil (Disdukcapil) Kabupaten Toba merupakan unsur pelaksana urusan pemerintahan di bidang administrasi kependudukan dan pencatatan sipil.
                                </p>
                                <p class="text-gray-700 leading-relaxed">
                                    Sejak berdirinya Kabupaten Toba, Disdukcapil terus berkomitmen untuk memberikan pelayanan terbaik bagi masyarakat dalam hal pengurusan dokumen kependudukan seperti KTP, Kartu Keluarga, Akta Kelahiran, dan dokumen lainnya.
                                </p>
                                <p class="text-gray-700 leading-relaxed">
                                    Dengan perkembangan teknologi dan digitalisasi, Disdukcapil Kabupaten Toba kini telah mengimplementasikan berbagai sistem online untuk memudahkan masyarakat dalam mengurus dokumen kependudukan tanpa harus datang langsung ke kantor.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div id="penghargaan" class="tab-panel hidden">
                        <div class="bg-white rounded-2xl shadow-lg p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-trophy text-2xl text-yellow-600"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800">Penghargaan</h3>
                            </div>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="flex gap-4 p-4 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl border-l-4 border-yellow-500">
                                    <div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-award text-xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800">Pelayanan Terbaik 2024</h4>
                                        <p class="text-gray-600 text-sm">Penghargaan tingkat Provinsi Sumatera Utara</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl border-l-4 border-blue-500">
                                    <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-digital-tachograph text-xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800">Digitalisasi Terbaik 2023</h4>
                                        <p class="text-gray-600 text-sm">Inovasi pelayanan online terintegrasi</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border-l-4 border-green-500">
                                    <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-users text-xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800">Satyalancana Karya Bhakti</h4>
                                        <p class="text-gray-600 text-sm">Penghargaan pelayanan publik prima</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl border-l-4 border-purple-500">
                                    <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-star text-xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800">Predikat WTP</h4>
                                        <p class="text-gray-600 text-sm">Opini tertinggi atas laporan keuangan</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="dasar-hukum" class="tab-panel hidden">
                        <div class="bg-white rounded-2xl shadow-lg p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-balance-scale text-2xl text-indigo-600"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800">Dasar Hukum</h3>
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-blue-50 transition">
                                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-sm">1</span>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Undang-Undang No. 24 Tahun 2013</h4>
                                        <p class="text-gray-600 text-sm">Tentang Perubahan atas Undang-Undang No. 23 Tahun 2006 tentang Administrasi Kependudukan</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-blue-50 transition">
                                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-sm">2</span>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Peraturan Pemerintah No. 40 Tahun 2010</h4>
                                        <p class="text-gray-600 text-sm">Tentang Pelaksanaan Undang-Undang Nomor 23 Tahun 2006</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-blue-50 transition">
                                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-sm">3</span>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Peraturan Daerah Kabupaten Toba</h4>
                                        <p class="text-gray-600 text-sm">Tentang Penyelenggaraan Administrasi Kependudukan di Kabupaten Toba</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="tugas-fungsi" class="tab-panel hidden">
                        <div class="bg-white rounded-2xl shadow-lg p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-tasks text-2xl text-teal-600"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800">Tugas & Fungsi</h3>
                            </div>
                            <div class="grid md:grid-cols-2 gap-8">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                        <i class="fas fa-clipboard-check text-blue-500"></i>
                                        Tugas Pokok
                                    </h4>
                                    <p class="text-gray-700 leading-relaxed bg-blue-50 rounded-xl p-6">
                                        Melaksanakan urusan pemerintahan daerah di bidang administrasi kependudukan dan pencatatan sipil yang menjadi kewenangan daerah dan tugas pembantuan yang ditugaskan kepada Daerah.
                                    </p>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                        <i class="fas fa-cogs text-teal-500"></i>
                                        Fungsi
                                    </h4>
                                    <ul class="space-y-3">
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-chevron-right text-blue-500 mt-1"></i>
                                            <span class="text-gray-700">Perumusan kebijakan teknis di bidang administrasi kependudukan</span>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-chevron-right text-blue-500 mt-1"></i>
                                            <span class="text-gray-700">Penyelenggaraan pendaftaran penduduk dan pencatatan sipil</span>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-chevron-right text-blue-500 mt-1"></i>
                                            <span class="text-gray-700">Penerbitan dokumen kependudukan</span>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-chevron-right text-blue-500 mt-1"></i>
                                            <span class="text-gray-700">Pengelolaan sistem informasi administrasi kependudukan</span>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-chevron-right text-blue-500 mt-1"></i>
                                            <span class="text-gray-700">Pembinaan dan pengawasan di bidang administrasi kependudukan</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-landmark text-white text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">Disdukcapil Toba</h3>
                            <p class="text-gray-400 text-sm">Kabupaten Toba</p>
                        </div>
                    </div>
                    <p class="text-gray-400 mb-4">
                        Melayani dengan sepenuh hati untuk administrasi kependudukan yang tertib dan modern
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-blue-600 rounded-lg flex items-center justify-center transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-blue-600 rounded-lg flex items-center justify-center transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-blue-600 rounded-lg flex items-center justify-center transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-blue-600 rounded-lg flex items-center justify-center transition">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Layanan</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-blue-400 transition">Layanan Mandiri</a></li>
                        <li><a href="#" class="hover:text-blue-400 transition">Antrian Online</a></li>
                        <li><a href="#" class="hover:text-blue-400 transition">Statistik</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Kontak</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-blue-500"></i>
                            Balige, Kabupaten Toba
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-phone text-blue-500"></i>
                            (0632) 123456
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-envelope text-blue-500"></i>
                            info@disdukcapil-toba.go.id
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-500">
                <p>&copy; 2025 Disdukcapil Kabupaten Toba. Seluruh hak cipta dilindungi.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Tab Switching
        function switchTab(event, tabId) {
            // Remove active class from all buttons and panels
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active', 'bg-blue-600', 'text-white');
                btn.classList.add('text-gray-600', 'hover:bg-gray-100');
            });
            document.querySelectorAll('.tab-panel').forEach(panel => {
                panel.classList.add('hidden');
                panel.classList.remove('active');
            });

            // Add active class to clicked button
            event.currentTarget.classList.add('active', 'bg-blue-600', 'text-white');
            event.currentTarget.classList.remove('text-gray-600', 'hover:bg-gray-100');

            // Show corresponding panel
            document.getElementById(tabId).classList.remove('hidden');
            document.getElementById(tabId).classList.add('active');
        }

        // Initialize first tab as active
        document.addEventListener('DOMContentLoaded', () => {
            const firstTab = document.querySelector('.tab-btn');
            if (firstTab) {
                firstTab.classList.add('bg-blue-600', 'text-white');
                firstTab.classList.remove('text-gray-600', 'hover:bg-gray-100');
            }
        });

        // Scroll Reveal Animation
        function reveal() {
            const reveals = document.querySelectorAll('.reveal');
            reveals.forEach(element => {
                const windowHeight = window.innerHeight;
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;

                if (elementTop < windowHeight - elementVisible) {
                    element.classList.add('active');
                }
            });
        }

        window.addEventListener('scroll', reveal);
        reveal(); // Initial call

        // Header Scroll Effect
        let lastScroll = 0;
        const header = document.getElementById('mainHeader');

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;

            if (currentScroll > 50) {
                header.classList.add('shadow-lg');
            } else {
                header.classList.remove('shadow-lg');
            }

            lastScroll = currentScroll;
        });
    </script>
<script src="{{ asset('js/page-loading.js') }}?v={{ filemtime(public_path('js/page-loading.js')) }}"></script>
<script src="{{ asset('js/style-guide-enhancer.js') }}?v={{ filemtime(public_path('js/style-guide-enhancer.js')) }}"></script>
</body>
</html>
