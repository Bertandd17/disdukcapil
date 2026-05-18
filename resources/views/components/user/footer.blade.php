{{-- Footer Compact dengan desain Hero Section --}}
<footer class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-cyan-800 text-white">
    {{-- SVG Wave di bagian atas sebagai transisi (warna biru, bukan putih) --}}
    <div class="absolute top-0 left-0 right-0 transform rotate-180">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
            <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#1e40af"/>
        </svg>
    </div>

    {{-- Konten Footer Compact --}}
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 relative z-10">
        {{-- Grid 3 Kolom dengan spacing lebih kecil --}}
        <div class="grid md:grid-cols-3 gap-6 mb-4">

            {{-- Kolom 1: Logo & Deskripsi --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-9 h-9 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center overflow-hidden border border-white/30 flex-shrink-0">
                        <img src="{{ asset('images/logo_toba.jpeg') }}" alt="Logo Toba" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h3 class="text-sm font-bold">Disdukcapil Toba</h3>
                        <p class="text-xs text-blue-100 -mt-0.5">Kabupaten Toba</p>
                    </div>
                </div>
                <p class="text-blue-100 text-xs mb-3 leading-snug">
                    Melayani dengan sepenuh hati untuk administrasi kependudukan.
                </p>

                {{-- Social Media Icons --}}
                <div class="flex gap-2">
                    <a href="#" class="w-8 h-8 bg-white/20 backdrop-blur-sm hover:bg-white hover:text-blue-600 border border-white/30 rounded-lg flex items-center justify-center transition-all duration-300">
                        <i class="fab fa-facebook-f text-xs"></i>
                    </a>
                    <a href="#" class="w-8 h-8 bg-white/20 backdrop-blur-sm hover:bg-white hover:text-pink-600 border border-white/30 rounded-lg flex items-center justify-center transition-all duration-300">
                        <i class="fab fa-instagram text-xs"></i>
                    </a>
                    <a href="#" class="w-8 h-8 bg-white/20 backdrop-blur-sm hover:bg-white hover:text-sky-500 border border-white/30 rounded-lg flex items-center justify-center transition-all duration-300">
                        <i class="fab fa-twitter text-xs"></i>
                    </a>
                    <a href="#" class="w-8 h-8 bg-white/20 backdrop-blur-sm hover:bg-white hover:text-red-600 border border-white/30 rounded-lg flex items-center justify-center transition-all duration-300">
                        <i class="fab fa-youtube text-xs"></i>
                    </a>
                </div>
            </div>

            {{-- Kolom 2: Layanan --}}
            <div>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ url('layanan-mandiri') }}" class="flex items-center gap-2 px-2 py-1 rounded text-xs font-medium text-blue-100 hover:text-white hover:bg-white/20 backdrop-blur-sm transition-all duration-300">
                            <i class="fas fa-rocket text-xs"></i>
                            <span>Layanan Mandiri</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('antrian-online') }}" class="flex items-center gap-2 px-2 py-1 rounded text-xs font-medium text-blue-100 hover:text-white hover:bg-white/20 backdrop-blur-sm transition-all duration-300">
                            <i class="fas fa-ticket-alt text-xs"></i>
                            <span>Antrian Online</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('statistik') }}" class="flex items-center gap-2 px-2 py-1 rounded text-xs font-medium text-blue-100 hover:text-white hover:bg-white/20 backdrop-blur-sm transition-all duration-300">
                            <i class="fas fa-chart-line text-xs"></i>
                            <span>Statistik</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('tracking') }}" class="flex items-center gap-2 px-2 py-1 rounded text-xs font-medium text-blue-100 hover:text-white hover:bg-white/20 backdrop-blur-sm transition-all duration-300">
                            <i class="fas fa-search text-xs"></i>
                            <span>Lacak Berkas</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Kolom 3: Kontak --}}
            <div>
                <ul class="space-y-2 text-xs">
                    <li class="flex items-start gap-2 p-1 rounded hover:bg-white/20 backdrop-blur-sm transition-colors">
                        <div class="w-6 h-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-xs"></i>
                        </div>
                        <div>
                            <p class="font-medium text-xs">Balige, Kabupaten Toba</p>
                            <p class="text-blue-100 text-xs">Sumatera Utara</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-2 p-1 rounded hover:bg-white/20 backdrop-blur-sm transition-colors">
                        <div class="w-6 h-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-phone text-xs"></i>
                        </div>
                        <div>
                            <p class="font-medium text-xs">(0632) 123456</p>
                            <p class="text-blue-100 text-xs">08.00 - 16.00</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-2 p-1 rounded hover:bg-white/20 backdrop-blur-sm transition-colors">
                        <div class="w-6 h-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-xs"></i>
                        </div>
                        <div>
                            <p class="font-medium text-xs">info@disdukcapil-toba.go.id</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Copyright Bar Compact --}}
        <div class="border-t border-white/20 pt-3">
            <div class="flex flex-col md:flex-row justify-between items-center gap-2">
                <p class="text-blue-100 text-xs text-center md:text-left">
                    &copy; {{ date('Y') }} Disdukcapil Kabupaten Toba.
                </p>
                <div class="flex gap-3 text-xs">
                    <a href="#" class="text-blue-100 hover:text-white transition-colors">Privasi</a>
                    <a href="#" class="text-blue-100 hover:text-white transition-colors">Syarat</a>
                    <a href="#" class="text-blue-100 hover:text-white transition-colors">Bantuan</a>
                </div>
            </div>
        </div>
    </div>
</footer>
