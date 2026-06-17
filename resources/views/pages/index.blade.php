@extends('layouts.user')

@section('content')
    {{-- Hero Section --}}
    <section class="user-home-hero relative min-h-[600px] bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 text-white overflow-hidden">
        {{-- Background Figures --}}
        <div class="hero-bg-left">
            <div class="hero-figure">
                <div class="hero-figure-image">
                    <img src="{{ asset('images/Bupati_Toba_Effendi_Sintong_Panangian_Napitupulu.png') }}"
                         alt="Bupati Toba"
                         class="w-full h-full object-cover">
                </div>
                <div class="hero-figure-name">Bupati Toba</div>
                <div class="hero-figure-title">Effendi Sintong Panangian Napitupulu</div>
            </div>
        </div>

        <div class="hero-bg-right">
            <div class="hero-figure">
                <div class="hero-figure-image">
                    <img src="{{ asset('images/Wakil_Bupati_Toba_Audi_Murphy_O._Sitorus.png') }}"
                         alt="Wakil Bupati Toba"
                         class="w-full h-full object-cover">
                </div>
                <div class="hero-figure-name">Wakil Bupati Toba</div>
                <div class="hero-figure-title">Audi Murphy O. Sitorus</div>
            </div>
        </div>

        {{-- Hero Content --}}
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 animate-fade-in-up" style="animation-delay: 0.1s;">
                    Urus Dokumen Kependudukan
                    <span class="block text-blue-200">Kini Lebih Mudah & Cepat</span>
                </h1>
                <p class="text-lg md:text-xl text-blue-100 mb-8 animate-fade-in-up" style="animation-delay: 0.2s;">
                    Layanan pendaftaran, pencatatan sipil, dan informasi kependudukan yang
                    modern, transparan, dan dapat diakses kapan saja, di mana saja.
                </p>
            </div>
        </div>

        {{-- Wave Divider --}}
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#f9fafb"/>
            </svg>
        </div>
    </section>

    {{-- Welcome Section --}}
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-blue-600 rounded-2xl p-8 md:p-12 text-white text-center reveal">
                <h2 class="text-2xl md:text-3xl font-bold mb-3">Selamat Datang di Portal Disdukcapil</h2>
                <p class="text-blue-100 text-lg max-w-3xl mx-auto">
                    Kabupaten Toba berkomitmen memberikan pelayanan administrasi kependudukan
                    kelas dunia dengan memanfaatkan teknologi terkini untuk kenyamanan masyarakat.
                </p>
            </div>
        </div>
    </section>

    {{-- Profil Disdukcapil Section --}}
    <section id="profil" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider">Tentang Kami</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Profil Disdukcapil</h2>
                <p class="text-gray-600 mt-3 max-w-2xl mx-auto">
                    Mengenal lebih dekat visi, misi, dan dedikasi kami dalam melayani masyarakat
                </p>
            </div>

            {{-- Horizontal Tabs Navigation --}}
            <div class="bg-white rounded-2xl shadow-lg p-2 mb-8 overflow-x-auto reveal">
                <div class="tabs flex gap-2 min-w-max justify-center">
                    <button onclick="switchTab(event, 'visi')" class="tab-btn active flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold whitespace-nowrap transition-all text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-bullseye"></i>
                        Visi & Misi
                    </button>
                    <button onclick="switchTab(event, 'motto')" class="tab-btn flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold whitespace-nowrap transition-all text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-quote-left"></i>
                        Motto & Nilai
                    </button>
                    <button onclick="switchTab(event, 'penghargaan')" class="tab-btn flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold whitespace-nowrap transition-all text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-trophy"></i>
                        Penghargaan
                    </button>
                    <button onclick="switchTab(event, 'dasar-hukum')" class="tab-btn flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold whitespace-nowrap transition-all text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-balance-scale"></i>
                        Dasar Hukum
                    </button>
                    <button onclick="switchTab(event, 'tugas-fungsi')" class="tab-btn flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold whitespace-nowrap transition-all text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-tasks"></i>
                        Tugas & Fungsi
                    </button>
                    <button onclick="switchTab(event, 'struktur-organisasi')" class="tab-btn flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold whitespace-nowrap transition-all text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-sitemap"></i>
                        Struktur Organisasi
                    </button>
                </div>
            </div>

            {{-- Tab Content --}}
            <div class="tab-content reveal">
                {{-- Visi & Misi --}}
                <div id="visi" class="tab-panel active">
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        {{-- Header Kabupaten Toba --}}
                        <div class="text-center mb-8">
                        </div>

                        <div class="grid md:grid-cols-2 gap-8">
                            <div>
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-bullseye text-2xl text-blue-600"></i>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-800">Visi</h3>
                                </div>
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border-l-4 border-blue-600">
                                    <p class="text-3xl font-extrabold text-blue-700 mb-3">TOBA MANTAP 2029</p>
                                    <p class="text-gray-700 text-lg leading-relaxed italic">
                                        "Maju Daerahnya, Sejahtera Rakyatnya dan Berkelanjutan Pembangunannya"
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-rocket text-2xl text-teal-600"></i>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-800">Misi</h3>
                                </div>
                                <ul class="space-y-3">
                                    <li class="flex items-start gap-3 p-3 bg-teal-50 rounded-lg">
                                        <span class="w-6 h-6 bg-teal-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <span class="text-white text-xs font-bold">1</span>
                                        </span>
                                        <span class="text-gray-700">Membangun Sumber Daya Manusia yang berdaya saing dan berakhlak</span>
                                    </li>
                                    <li class="flex items-start gap-3 p-3 bg-teal-50 rounded-lg">
                                        <span class="w-6 h-6 bg-teal-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <span class="text-white text-xs font-bold">2</span>
                                        </span>
                                        <span class="text-gray-700">Membangun Infrastruktur yang terintegrasi berkualitas dan merata</span>
                                    </li>
                                    <li class="flex items-start gap-3 p-3 bg-teal-50 rounded-lg">
                                        <span class="w-6 h-6 bg-teal-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <span class="text-white text-xs font-bold">3</span>
                                        </span>
                                        <span class="text-gray-700">Meningkatkan pembangunan ekonomi yang berkelanjutan berbasis potensi daerah</span>
                                    </li>
                                    <li class="flex items-start gap-3 p-3 bg-teal-50 rounded-lg">
                                        <span class="w-6 h-6 bg-teal-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <span class="text-white text-xs font-bold">4</span>
                                        </span>
                                        <span class="text-gray-700">Mewujudkan tata kelola pemerintahan yang baik dan bersih sebagai parhobas rakyat</span>
                                    </li>
                                    <li class="flex items-start gap-3 p-3 bg-teal-50 rounded-lg">
                                        <span class="w-6 h-6 bg-teal-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <span class="text-white text-xs font-bold">5</span>
                                        </span>
                                        <span class="text-gray-700">Meningkatkan keamanan dan ketertiban</span>
                                    </li>
                                    <li class="flex items-start gap-3 p-3 bg-teal-50 rounded-lg">
                                        <span class="w-6 h-6 bg-teal-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <span class="text-white text-xs font-bold">6</span>
                                        </span>
                                        <span class="text-gray-700">Melestarikan nilai budaya dan kearifan lokal</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Motto & Nilai --}}
                <div id="motto" class="tab-panel hidden">
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <div class="text-center mb-8">
                            <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-quote-left text-3xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-800 mb-2">Motto Pelayanan</h3>
                            <p class="text-gray-500">Dinas Kependudukan dan Pencatatan Sipil Kabupaten Toba</p>
                        </div>

                        {{-- SOBAT DUKCAPIL --}}
                        <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-blue-800 rounded-2xl p-8 text-white text-center mb-8 relative overflow-hidden">
                            <div class="absolute inset-0 opacity-10">
                                <div class="absolute top-0 left-0 w-40 h-40 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                                <div class="absolute bottom-0 right-0 w-60 h-60 bg-white rounded-full translate-x-1/2 translate-y-1/2"></div>
                            </div>
                            <div class="relative z-10">
                                <p class="text-4xl md:text-5xl font-extrabold mb-2">SOBAT DUKCAPIL</p>
                                <p class="text-blue-100 text-lg">Standar Pelayanan Prima</p>
                            </div>
                        </div>

                        <h4 class="text-xl font-bold text-gray-800 mb-6 text-center">Nilai-Nilai Pelayanan</h4>
                        <div class="grid md:grid-cols-5 gap-4">
                            <div class="text-center p-5 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200">
                                <div class="w-14 h-14 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                                    <span class="text-2xl font-extrabold text-white">S</span>
                                </div>
                                <h5 class="font-bold text-gray-800 mb-1">SOPAN</h5>
                                <p class="text-gray-600 text-xs">Pelayanan dengan etika dan kesopanan</p>
                            </div>
                            <div class="text-center p-5 bg-gradient-to-br from-teal-50 to-teal-100 rounded-xl border border-teal-200">
                                <div class="w-14 h-14 bg-teal-500 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                                    <span class="text-2xl font-extrabold text-white">O</span>
                                </div>
                                <h5 class="font-bold text-gray-800 mb-1">OBJEKTIF</h5>
                                <p class="text-gray-600 text-xs">Pelayanan adil dan tidak pilih kasih</p>
                            </div>
                            <div class="text-center p-5 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200">
                                <div class="w-14 h-14 bg-green-500 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                                    <span class="text-2xl font-extrabold text-white">B</span>
                                </div>
                                <h5 class="font-bold text-gray-800 mb-1">BERSIH</h5>
                                <p class="text-gray-600 text-xs">Pelayanan transparan dan bebas KKN</p>
                            </div>
                            <div class="text-center p-5 bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl border border-amber-200">
                                <div class="w-14 h-14 bg-amber-500 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                                    <span class="text-2xl font-extrabold text-white">A</span>
                                </div>
                                <h5 class="font-bold text-gray-800 mb-1">AKUNTABEL</h5>
                                <p class="text-gray-600 text-xs">Pertanggungjawaban yang jelas</p>
                            </div>
                            <div class="text-center p-5 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200">
                                <div class="w-14 h-14 bg-purple-500 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                                    <span class="text-2xl font-extrabold text-white">T</span>
                                </div>
                                <h5 class="font-bold text-gray-800 mb-1">TANGKAS</h5>
                                <p class="text-gray-600 text-xs">Pelayanan cepat dan responsif</p>
                            </div>
                        </div>

                        {{-- Parhobas Info --}}
                        <div class="mt-8 p-6 bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl border-l-4 border-orange-500">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-orange-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-hands-helping text-xl text-white"></i>
                                </div>
                                <div>
                                    <h5 class="font-bold text-gray-800 mb-1">Parhobas - Pelayan Rakyat</h5>
                                    <p class="text-gray-600 text-sm">
                                        Sebagai abdi negara, kami berkomitmen untuk menjadi "parhobas" (pelayan) yang setia melayani masyarakat Kabupaten Toba dengan sepenuh hati.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Penghargaan --}}
                <div id="penghargaan" class="tab-panel hidden">
                        <div class="bg-white rounded-2xl shadow-lg p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-trophy text-2xl text-yellow-600"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800">Penghargaan</h3>
                            </div>
                            @if(isset($penghargaan) && $penghargaan->count() > 0)
                                <div class="grid md:grid-cols-2 gap-4">
                                    @foreach($penghargaan as $item)
                                        @php
                                            $colors = [
                                                'Nasional'  => ['border' => 'border-yellow-500', 'bg' => 'from-yellow-50 to-amber-50',  'icon_bg' => 'bg-yellow-500',  'badge' => 'bg-red-100 text-red-700'],
                                                'Provinsi'  => ['border' => 'border-blue-500',   'bg' => 'from-blue-50 to-cyan-50',     'icon_bg' => 'bg-blue-500',    'badge' => 'bg-blue-100 text-blue-700'],
                                                'Kabupaten' => ['border' => 'border-green-500',  'bg' => 'from-green-50 to-emerald-50', 'icon_bg' => 'bg-green-500',   'badge' => 'bg-green-100 text-green-700'],
                                            ];
                                            $c = $colors[$item->tingkat] ?? ['border' => 'border-gray-300', 'bg' => 'from-gray-50 to-gray-100', 'icon_bg' => 'bg-gray-400', 'badge' => 'bg-gray-100 text-gray-600'];
                                        @endphp
                                        <div class="flex gap-4 p-4 bg-gradient-to-r {{ $c['bg'] }} rounded-xl border-l-4 {{ $c['border'] }}">
                                            <div class="w-12 h-12 {{ $c['icon_bg'] }} rounded-xl flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-award text-xl text-white"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-2">
                                                    <h4 class="font-bold text-gray-800 text-sm leading-snug">{{ $item->nama }}</h4>
                                                    <span class="px-2 py-0.5 {{ $c['badge'] }} rounded-full text-xs font-semibold flex-shrink-0">{{ $item->tingkat }}</span>
                                                </div>
                                                <p class="text-gray-600 text-xs mt-1">{{ $item->instansi }}</p>
                                                @if($item->tahun || $item->lokasi)
                                                    <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-400">
                                                        @if($item->tahun)<span><i class="fas fa-calendar mr-1"></i>{{ $item->tahun }}</span>@endif
                                                        @if($item->lokasi)<span><i class="fas fa-map-marker-alt mr-1"></i>{{ $item->lokasi }}</span>@endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-trophy text-gray-300 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-sm">Belum ada data penghargaan.</p>
                                </div>
                            @endif
                        </div>
                    </div>
 
                    {{-- Dasar Hukum --}}
                    <div id="dasar-hukum" class="tab-panel hidden">
                        <div class="bg-white rounded-2xl shadow-lg p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-balance-scale text-2xl text-indigo-600"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800">Dasar Hukum</h3>
                            </div>
                            @if(isset($dasarHukum) && $dasarHukum->count() > 0)
                                <div class="space-y-4">
                                    @foreach($dasarHukum as $index => $item)
                                        <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-blue-50 transition group">
                                            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <span class="text-white font-bold text-sm">{{ $loop->iteration }}</span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-semibold text-gray-800">{{ $item->nama }}</h4>
                                                <p class="text-gray-600 text-sm mt-1">{{ $item->deskripsi_singkat }}</p>
                                            </div>
                                            @if($item->file)
                                                <a href="{{ asset('storage/' . $item->file) }}" target="_blank" rel="noopener"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-balance-scale text-gray-300 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-sm">Belum ada data dasar hukum.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                {{-- Tugas & Fungsi --}}
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

                {{-- Struktur Organisasi --}}
                <div id="struktur-organisasi" class="tab-panel hidden">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-11 h-11 bg-indigo-50 rounded-xl flex items-center justify-center">
                                <i class="fas fa-sitemap text-xl text-indigo-600"></i>
                            </div>
                            <h3 class="text-xl md:text-2xl font-bold text-gray-800">Struktur Organisasi</h3>
                        </div>

                        {{-- Info banner --}}
                        <div class="flex items-start gap-3 p-3 bg-blue-50/60 rounded-lg mb-8 border border-blue-100">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                            <p class="text-xs md:text-sm text-blue-700 leading-relaxed">
                                Struktur organisasi Dinas Kependudukan dan Pencatatan Sipil Kabupaten Toba. Nama pejabat dapat diperbarui melalui menu admin.
                            </p>
                        </div>

                        {{-- ORGANIZATION CHART --}}
                        <style>
                            .oc-card {
                                position: relative;
                                border-radius: 10px;
                                padding: 10px 14px;
                                text-align: center;
                                border: 1.5px solid #e5e7eb;
                                background: #fff;
                                box-shadow: 0 1px 3px rgba(0,0,0,.06);
                                word-break: break-word;
                            }
                            .oc-badge {
                                display: inline-block;
                                font-size: 9px;
                                font-weight: 700;
                                letter-spacing: .5px;
                                padding: 2px 8px;
                                border-radius: 999px;
                                margin-bottom: 5px;
                            }
                            .oc-title {
                                font-size: 12.5px;
                                font-weight: 700;
                                line-height: 1.35;
                                color: #111827;
                            }
                            .oc-name {
                                font-size: 11px;
                                font-style: italic;
                                color: #6b7280;
                                margin-top: 4px;
                                line-height: 1.3;
                            }
                            .oc-name.empty { color: #9ca3af; }

                            .oc-kadis      { background:#fef2f2; border-color:#fecaca; }
                            .oc-kadis      .oc-title { color:#991b1b; }
                            .oc-kadis      .oc-badge { background:#dc2626; color:#fff; }

                            .oc-sekretaris { background:#f5f3ff; border-color:#ddd6fe; }
                            .oc-sekretaris .oc-title { color:#5b21b6; }
                            .oc-sekretaris .oc-badge { background:#7c3aed; color:#fff; }

                            .oc-blue       { background:#eff6ff; border-color:#bfdbfe; }
                            .oc-blue       .oc-title { color:#1e40af; }
                            .oc-blue       .oc-badge { background:#2563eb; color:#fff; }

                            .oc-green      { background:#ecfdf5; border-color:#a7f3d0; }
                            .oc-green      .oc-title { color:#065f46; }
                            .oc-green      .oc-badge { background:#059669; color:#fff; }

                            .oc-orange     { background:#fff7ed; border-color:#fed7aa; }
                            .oc-orange     .oc-title { color:#9a3412; }
                            .oc-orange     .oc-badge { background:#ea580c; color:#fff; }

                            .oc-teal       { background:#f0fdfa; border-color:#99f6e4; }
                            .oc-teal       .oc-title { color:#115e59; }
                            .oc-teal       .oc-badge { background:#0d9488; color:#fff; }

                            .oc-sub        { background:#f9fafb; border-color:#e5e7eb; }
                            .oc-sub        .oc-title { font-size:11px; color:#374151; }
                            .oc-sub        .oc-name  { font-size:10px; }

                            .oc-vline { width:2px; background:#cbd5e1; flex-shrink:0; }

                            .oc-root {
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                width: 100%;
                            }

                            .oc-l2-row {
                                display: flex;
                                justify-content: center;
                                align-items: flex-start;
                                width: 100%;
                                gap: 8px;
                            }

                            .oc-l2-col {
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                flex: 1;
                                min-width: 0;
                            }

                            .oc-sub-row {
                                display: flex;
                                justify-content: center;
                                flex-wrap: wrap;
                                gap: 6px;
                                width: 100%;
                            }
                            .oc-sub-col {
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                flex: 1;
                                min-width: 90px;
                                max-width: 150px;
                            }

                            .oc-bus {
                                width: 100%;
                                max-width: 900px;
                                height: 2px;
                                background: #cbd5e1;
                            }

                            @media (max-width: 640px) {
                                .oc-l2-row   { flex-direction: column; align-items: center; gap: 0; }
                                .oc-l2-col   { width: 100%; max-width: 280px; flex: none; }
                                .oc-bus      { display: none; }
                                .oc-bus-mob  { display: block !important; }
                            }
                            .oc-bus-mob { display: none; }
                        </style>

                        @php
                            $kadis = $organisasiByLevel['pimpinan_utama']->firstWhere('kode_posisi', 'kadis')
                                ?? (object)['nama_jabatan' => 'Kepala Dinas', 'eselon' => 'II.b', 'nama_pejabat' => null];

                            $sekdin = $organisasiByLevel['pimpinan_utama']->firstWhere('kode_posisi', 'sekdin')
                                ?? (object)['nama_jabatan' => 'Sekretaris', 'eselon' => 'III.d', 'nama_pejabat' => null];

                            $subBagianList = [
                                (object)[
                                    'nama_jabatan' => 'Sub Bagian Umum & Kepegawaian',
                                    'nama_pejabat' => $organisasiByLevel['sub_bagian']->firstWhere('kode_posisi', 'subbag_umum')?->nama_pejabat,
                                ],
                                (object)[
                                    'nama_jabatan' => 'Sub Bagian Perencanaan',
                                    'nama_pejabat' => $organisasiByLevel['sub_bagian']->firstWhere('kode_posisi', 'subbag_perencanaan')?->nama_pejabat,
                                ],
                                (object)[
                                    'nama_jabatan' => 'Kelompok Fungsional',
                                    'nama_pejabat' => null,
                                ],
                            ];

                            $bidafduk = $organisasiByLevel['bidang']->firstWhere('kode_posisi', 'bidafduk')
                                ?? (object)['nama_jabatan' => 'Pelayanan Pendaftaran Penduduk', 'eselon' => 'III.c', 'nama_pejabat' => null];
                            $bidPencatatan = $organisasiByLevel['bidang']->firstWhere('kode_posisi', 'bidapencatatan')
                                ?? (object)['nama_jabatan' => 'Pencatatan Sipil', 'eselon' => 'III.c', 'nama_pejabat' => null];
                            $bidInformasi = $organisasiByLevel['bidang']->firstWhere('kode_posisi', 'bid_informasi')
                                ?? (object)['nama_jabatan' => 'PIAK', 'eselon' => 'III.c', 'nama_pejabat' => null];
                            $bidPemanfaatan = $organisasiByLevel['bidang']->firstWhere('kode_posisi', 'bid_pemanfaatan')
                                ?? (object)['nama_jabatan' => 'PDIP', 'eselon' => 'III.c', 'nama_pejabat' => null];

                            $bidangList = [
                                ['data' => $bidafduk, 'label' => 'Pelayanan Pendaftaran Penduduk', 'color' => 'blue'],
                                ['data' => $bidPencatatan, 'label' => 'Pencatatan Sipil', 'color' => 'green'],
                                ['data' => $bidInformasi, 'label' => 'PIAK', 'color' => 'orange'],
                                ['data' => $bidPemanfaatan, 'label' => 'PDIP', 'color' => 'teal'],
                            ];
                        @endphp

                        <div class="oc-root">
                            <div class="oc-card oc-kadis" style="min-width:180px;max-width:240px;width:100%">
                                <span class="oc-badge">{{ $kadis->eselon ?? 'II.b' }}</span>
                                <div class="oc-title">{{ $kadis->nama_jabatan ?? 'Kepala Dinas' }}</div>
                                @if(!empty($kadis->nama_pejabat))
                                    <div class="oc-name">{{ $kadis->nama_pejabat }}</div>
                                @else
                                    <div class="oc-name empty">Pejabat belum ditentukan</div>
                                @endif
                            </div>

                            <div class="oc-vline" style="height:20px"></div>

                            <div class="oc-bus" style="max-width:900px"></div>
                            <div class="oc-bus-mob oc-vline" style="height:14px"></div>

                            <div class="oc-l2-row" style="max-width:900px">
                                <div class="oc-l2-col">
                                    <div class="oc-vline" style="height:16px"></div>
                                    <div class="oc-card oc-sekretaris" style="width:100%">
                                        <span class="oc-badge">{{ $sekdin->eselon ?? 'III.d' }}</span>
                                        <div class="oc-title">{{ $sekdin->nama_jabatan ?? 'Sekretaris' }}</div>
                                        @if(!empty($sekdin->nama_pejabat))
                                            <div class="oc-name">{{ $sekdin->nama_pejabat }}</div>
                                        @else
                                            <div class="oc-name empty">Pejabat belum ditentukan</div>
                                        @endif
                                    </div>

                                    <div class="oc-vline" style="height:14px"></div>
                                    <div style="width:100%;height:2px;background:#cbd5e1;"></div>

                                    <div class="oc-sub-row">
                                        @foreach($subBagianList as $sub)
                                            <div class="oc-sub-col">
                                                <div class="oc-vline" style="height:12px"></div>
                                                <div class="oc-card oc-sub" style="width:100%">
                                                    <div class="oc-title">{{ $sub->nama_jabatan }}</div>
                                                    @if(!empty($sub->nama_pejabat))
                                                        <div class="oc-name">{{ $sub->nama_pejabat }}</div>
                                                    @else
                                                        <div class="oc-name empty">–</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                @foreach($bidangList as $bidang)
                                    <div class="oc-l2-col">
                                        <div class="oc-vline" style="height:16px"></div>
                                        <div class="oc-card oc-{{ $bidang['color'] }}" style="width:100%">
                                            <span class="oc-badge">{{ $bidang['data']->eselon ?? 'III.c' }}</span>
                                            <div class="oc-title">{{ $bidang['label'] }}</div>
                                            @if(!empty($bidang['data']->nama_pejabat))
                                                <div class="oc-name">{{ $bidang['data']->nama_pejabat }}</div>
                                            @else
                                                <div class="oc-name empty">Pejabat belum ditentukan</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- ============================================================
                             DETAIL UNIT KERJA
                             ============================================================ --}}
                        @php
                            $sekretariatChildren = [
                                'subbag'  => $organisasiByLevel['sub_bagian'] ?? collect(),
                                'koord'   => ($organisasiByLevel['koordinator'] ?? collect())->filter(fn($i) => str_starts_with($i->kode_posisi ?? '', 'koord_sekretariat')),
                                'kf'      => $organisasiByLevel['kelompok_fungsional_sekretariat'] ?? collect(),
                            ];

                            $unitDetails = [
                                [
                                    'label' => 'Sekretariat',
                                    'icon'  => 'fa-briefcase',
                                    'color' => 'violet',
                                    'subbag'=> $sekretariatChildren['subbag'],
                                    'koord' => $sekretariatChildren['koord'],
                                    'kf'    => $sekretariatChildren['kf'],
                                ],
                                [
                                    'label' => 'Bidang Pelayanan Pendaftaran Penduduk',
                                    'icon'  => 'fa-id-card',
                                    'color' => 'blue',
                                    'subbag'=> collect(),
                                    'koord' => ($organisasiByLevel['koordinator'] ?? collect())->filter(fn($i) => str_starts_with($i->kode_posisi ?? '', 'koord_dafduk')),
                                    'kf'    => ($organisasiByLevel['kelompok_fungsional_bidang'] ?? collect())->filter(fn($i) => ($i->kode_posisi ?? '') === 'kf_dafduk'),
                                ],
                                [
                                    'label' => 'Bidang Pelayanan Pencatatan Sipil',
                                    'icon'  => 'fa-file-signature',
                                    'color' => 'emerald',
                                    'subbag'=> collect(),
                                    'koord' => ($organisasiByLevel['koordinator'] ?? collect())->filter(fn($i) => str_starts_with($i->kode_posisi ?? '', 'koord_pencatatan')),
                                    'kf'    => ($organisasiByLevel['kelompok_fungsional_bidang'] ?? collect())->filter(fn($i) => ($i->kode_posisi ?? '') === 'kf_pencatatan'),
                                ],
                                [
                                    'label' => 'Bidang Pengelolaan Informasi Adm. Kependudukan',
                                    'icon'  => 'fa-database',
                                    'color' => 'orange',
                                    'subbag'=> collect(),
                                    'koord' => ($organisasiByLevel['koordinator'] ?? collect())->filter(fn($i) => str_starts_with($i->kode_posisi ?? '', 'koord_informasi')),
                                    'kf'    => ($organisasiByLevel['kelompok_fungsional_bidang'] ?? collect())->filter(fn($i) => ($i->kode_posisi ?? '') === 'kf_informasi'),
                                ],
                                [
                                    'label' => 'Bidang Pemanfaatan Data & Inovasi Pelayanan',
                                    'icon'  => 'fa-chart-line',
                                    'color' => 'teal',
                                    'subbag'=> collect(),
                                    'koord' => ($organisasiByLevel['koordinator'] ?? collect())->filter(fn($i) => str_starts_with($i->kode_posisi ?? '', 'koord_pemanfaatan')),
                                    'kf'    => ($organisasiByLevel['kelompok_fungsional_bidang'] ?? collect())->filter(fn($i) => ($i->kode_posisi ?? '') === 'kf_pemanfaatan'),
                                ],
                            ];

                            $unitColorMap = [
                                'violet'  => ['bg' => 'bg-violet-50',  'border' => 'border-violet-200',  'text' => 'text-violet-700',  'icon' => 'bg-violet-600'],
                                'blue'    => ['bg' => 'bg-blue-50',    'border' => 'border-blue-200',    'text' => 'text-blue-700',    'icon' => 'bg-blue-600'],
                                'emerald' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'icon' => 'bg-emerald-600'],
                                'orange'  => ['bg' => 'bg-orange-50',  'border' => 'border-orange-200',  'text' => 'text-orange-700',  'icon' => 'bg-orange-600'],
                                'teal'    => ['bg' => 'bg-teal-50',    'border' => 'border-teal-200',    'text' => 'text-teal-700',    'icon' => 'bg-teal-600'],
                            ];
                        @endphp

                        <div class="mt-10">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-layer-group text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg md:text-xl font-bold text-gray-800">Detail Unit Kerja</h4>
                                    <p class="text-xs md:text-sm text-gray-500">Sub Bagian, Koordinator, dan Kelompok Jabatan Fungsional di setiap unit.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                                @foreach($unitDetails as $unit)
                                    @php $c = $unitColorMap[$unit['color']]; @endphp
                                    <div class="rounded-2xl border {{ $c['border'] }} {{ $c['bg'] }} overflow-hidden flex flex-col">
                                        {{-- Header unit --}}
                                        <div class="flex items-start gap-3 p-4 border-b {{ $c['border'] }} bg-white/60">
                                            <div class="w-10 h-10 {{ $c['icon'] }} rounded-lg flex items-center justify-center text-white flex-shrink-0">
                                                <i class="fas {{ $unit['icon'] }} text-sm"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <h5 class="text-sm font-bold {{ $c['text'] }} leading-snug">{{ $unit['label'] }}</h5>
                                            </div>
                                        </div>

                                        {{-- Body --}}
                                        <div class="p-4 space-y-4 flex-1">
                                            {{-- Sub Bagian --}}
                                            @if($unit['subbag']->isNotEmpty())
                                                <div>
                                                    <p class="text-[10px] uppercase tracking-widest font-bold text-gray-500 mb-2">Sub Bagian</p>
                                                    <ul class="space-y-2">
                                                        @foreach($unit['subbag'] as $sb)
                                                            <li class="bg-white rounded-lg border border-gray-200 px-3 py-2">
                                                                <p class="text-xs font-semibold text-gray-800 leading-snug">{{ $sb->nama_jabatan }}</p>
                                                                <p class="text-[11px] mt-0.5 {{ $sb->nama_pejabat ? 'text-gray-600 italic' : 'text-gray-400 italic' }}">
                                                                    {{ $sb->nama_pejabat ?: 'Pejabat belum ditentukan' }}
                                                                </p>
                                                                @if($sb->eselon)
                                                                    <span class="inline-block mt-1 text-[9px] font-bold px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded">{{ $sb->eselon }}</span>
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            {{-- Koordinator --}}
                                            @if($unit['koord']->isNotEmpty())
                                                <div>
                                                    <p class="text-[10px] uppercase tracking-widest font-bold text-gray-500 mb-2">Koordinator / Sub Koordinator</p>
                                                    <ul class="space-y-2">
                                                        @foreach($unit['koord'] as $k)
                                                            <li class="bg-white rounded-lg border border-gray-200 px-3 py-2">
                                                                <p class="text-xs font-semibold text-gray-800 leading-snug">{{ $k->nama_jabatan }}</p>
                                                                <p class="text-[11px] mt-0.5 {{ $k->nama_pejabat ? 'text-gray-600 italic' : 'text-gray-400 italic' }}">
                                                                    {{ $k->nama_pejabat ?: 'Pejabat belum ditentukan' }}
                                                                </p>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            {{-- Kelompok Fungsional --}}
                                            @if($unit['kf']->isNotEmpty())
                                                <div>
                                                    <p class="text-[10px] uppercase tracking-widest font-bold text-gray-500 mb-2">Kelompok Jabatan Fungsional</p>
                                                    <ul class="space-y-2">
                                                        @foreach($unit['kf'] as $kf)
                                                            <li class="bg-white rounded-lg border border-dashed border-gray-300 px-3 py-2">
                                                                <p class="text-xs font-semibold text-gray-800 leading-snug">{{ $kf->nama_jabatan }}</p>
                                                                <p class="text-[11px] mt-0.5 {{ $kf->nama_pejabat ? 'text-gray-600 italic' : 'text-gray-400 italic' }}">
                                                                    {{ $kf->nama_pejabat ?: 'Pejabat belum ditentukan' }}
                                                                </p>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            @if($unit['subbag']->isEmpty() && $unit['koord']->isEmpty() && $unit['kf']->isEmpty())
                                                <p class="text-xs text-gray-400 italic text-center py-4">Belum ada unit di bawah.</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Berita & Pengumuman --}}
    <section id="berita" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider">Kabar Terkini</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Berita & Pengumuman</h2>
                <p class="text-gray-600 mt-3 max-w-2xl mx-auto">
                    Informasi terbaru seputar layanan dan kegiatan Disdukcapil Kabupaten Toba
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 reveal">
                @forelse ($beritas as $item)
                    <div class="news-card-large bg-white rounded-2xl shadow-lg overflow-hidden cursor-pointer hover:shadow-2xl transition-all duration-300 hover:-translate-y-2"
                         role="button"
                         tabindex="0"
                         onclick="openNewsModal({{ $item->id }})"
                         onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openNewsModal({{ $item->id }});}">
                        <div class="p-6">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <span class="px-3 py-1 berita-badge rounded-full text-xs font-semibold whitespace-nowrap max-w-[65%] truncate">
                                    {{ $item->judul }}
                                </span>
                                <span class="text-gray-500 text-sm whitespace-nowrap">
                                    {{ ($item->published_at ?? $item->created_at)->locale('id')->translatedFormat('d M Y') }}
                                </span>
                            </div>

                            <h3 class="text-xl font-bold text-gray-800 mb-2 line-clamp-2">{{ $item->judul }}</h3>
                            <p class="text-gray-600 text-sm mb-5 line-clamp-4">
                                {{ \Illuminate\Support\Str::limit(trim(strip_tags($item->konten)), 160) }}
                            </p>

                            <span class="inline-flex items-center gap-2 text-blue-600 font-semibold text-sm hover:gap-3 transition-all">
                                Baca Selengkapnya <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="md:col-span-3 text-center text-gray-500 py-12 text-lg">
                        Belum ada berita yang dipublikasikan. Silakan cek kembali nanti.
                    </p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Modal baca berita --}}
    <div class="news-modal-overlay" id="newsModalOverlay" onclick="closeNewsModal()">
        <div class="news-modal" onclick="event.stopPropagation()">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between gap-4">
                <span class="px-4 py-2 berita-badge rounded-full text-sm font-semibold shrink-0 max-w-[60%] truncate" id="modalCategory">Kategori</span>
                <button type="button" onclick="closeNewsModal()" class="w-10 h-10 hover:bg-gray-100 rounded-lg flex items-center justify-center transition shrink-0" aria-label="Tutup">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
            <div class="p-8">
                <span class="text-gray-500 text-sm" id="modalDate">Tanggal</span>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mt-2 mb-6" id="modalTitle">Judul Berita</h2>
                <div class="prose max-w-none text-gray-700" id="modalContent"></div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* Hero Background */
    .hero-bg-left,
    .hero-bg-right {
        position: absolute;
        top: 60%;
        transform: translateY(-50%);
        width: 450px;
        height: 650px;
        z-index: 1;
        opacity: 0.25;
        pointer-events: none;
    }

    .hero-bg-left {
        left: -50px;
    }

    .hero-bg-right {
        right: -50px;
    }

    .hero-figure {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 0 20px;
    }

    .hero-figure-image {
        width: 280px;
        height: 380px;
        border-radius: 16px;
        overflow: hidden;
        filter: blur(0.3px);
        animation: figureFloat 6s ease-in-out infinite;
        box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.4);
        border: 4px solid rgba(255, 255, 255, 0.2);
    }

    .hero-figure-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.95);
        margin-top: 16px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        max-width: 100%;
        word-wrap: break-word;
    }

    .hero-figure-title {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.8);
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        max-width: 100%;
        word-wrap: break-word;
        line-height: 1.3;
    }

    .hero-bg-right .hero-figure-emoji {
        animation: figureFloat 6s ease-in-out infinite reverse;
    }

    @keyframes figureFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }

    /* News modal (beranda) */
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
        width: 100%;
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

    .berita-badge {
        background: #FEF7E0;
        color: #B06000;
    }

    @media (max-width: 767px) {
        .user-home-hero {
            min-height: 720px;
        }

        .user-home-hero .relative.z-10 {
            padding-top: 8rem;
            padding-bottom: 12rem;
        }

        .hero-bg-left,
        .hero-bg-right {
            top: auto;
            right: auto;
            left: 50%;
            width: 190px;
            height: auto;
            transform: translateX(-50%);
            opacity: 0.2;
            z-index: 0;
        }

        .hero-bg-left {
            top: 1rem;
        }

        .hero-bg-right {
            bottom: 3.25rem;
        }

        .hero-figure {
            padding: 0;
        }

        .hero-figure-image {
            width: 120px;
            height: 162px;
            border-radius: 12px;
            border-width: 3px;
            animation: none;
        }

        .hero-figure-name {
            margin-top: 8px;
            font-size: 0.8rem;
        }

        .hero-figure-title {
            font-size: 0.68rem;
        }

        .news-modal-overlay {
            align-items: flex-end;
            padding: 0.75rem;
        }

        .news-modal {
            max-height: 88vh;
            border-radius: 18px;
        }
    }

    @media (min-width: 768px) and (max-width: 1180px) {
        .hero-bg-left,
        .hero-bg-right {
            width: 320px;
            height: 520px;
            opacity: 0.18;
        }

        .hero-bg-left {
            left: -110px;
        }

        .hero-bg-right {
            right: -110px;
        }

        .hero-figure-image {
            width: 210px;
            height: 300px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const newsData = @json($newsForModal);

    function openNewsModal(newsId) {
        const overlay = document.getElementById('newsModalOverlay');
        if (!overlay) return;
        const news = newsData[String(newsId)] || newsData[newsId];
        if (!news) return;

        document.getElementById('modalCategory').textContent = news.category;
        document.getElementById('modalDate').textContent = news.date;
        document.getElementById('modalTitle').textContent = news.title;
        document.getElementById('modalContent').innerHTML = news.content;

        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeNewsModal() {
        const overlay = document.getElementById('newsModalOverlay');
        if (!overlay) return;
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeNewsModal();
        }
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
        document.getElementById(tabId).classList.remove('hidden');
        document.getElementById(tabId).classList.add('active');

        // Add active class to clicked button
        event.currentTarget.classList.add('active', 'bg-blue-600', 'text-white');
        event.currentTarget.classList.remove('text-gray-600', 'hover:bg-gray-100');

        // Show corresponding panel
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
</script>
@endpush
