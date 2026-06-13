@extends('layouts.user')

@section('content')
    {{-- Hero Section --}}
    <section class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-cyan-800 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-6">
                    Data Kependudukan Kabupaten Toba
                </h1>
                <p class="text-lg text-blue-100 mb-8">
                    Informasi statistik kependudukan yang transparan dan akuntabel
                </p>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#f9fafb"/>
            </svg>
        </div>
    </section>

    {{-- Charts Section --}}
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider">Visualisasi</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Grafik Kependudukan</h2>
            </div>

            {{-- Filter Tahun --}}
            <div class="mb-8 flex justify-center reveal">
                <div class="inline-flex items-center gap-3 bg-white rounded-2xl shadow-sm p-2">
                    <label class="text-sm font-semibold text-gray-700">Tahun:</label>
                    <select id="filterTahun" class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach($tahunTersedia as $tahun)
                            <option value="{{ $tahun }}" {{ $tahun == $tahunSekarang ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-8 reveal">
                {{-- Statistik Dokumen Bulanan --}}
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-file-alt text-purple-500 mr-2"></i>
                        Dokumen Diterbitkan
                    </h3>
                    <div class="h-96">
                        <canvas id="dokumenChart"></canvas>
                    </div>
                </div>

                {{-- Statistik Layanan --}}
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-clipboard-list text-emerald-500 mr-2"></i>
                        Statistik Layanan Bulanan
                    </h3>
                    <div class="h-96">
                        <canvas id="layananChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- District Stats --}}
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider">Per Kecamatan</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Data Kecamatan</h2>
            </div>

            {{-- Horizontal Scroll Container --}}
            <div class="relative reveal">
                {{-- Scroll Button Left --}}
                <button id="scrollLeft" class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white shadow-lg rounded-full p-3 hover:bg-gray-50 transition-all duration-300 hidden md:block">
                    <i class="fas fa-chevron-left text-gray-600"></i>
                </button>

                {{-- Cards Container --}}
                <div id="districtCardsContainer" class="flex gap-6 overflow-x-auto pb-4 px-1 scroll-smooth" style="scrollbar-width: thin; scrollbar-color: #cbd5e1 #f1f5f9;">
                    {{-- Cards will be dynamically loaded here --}}
                    <div class="flex items-center justify-center w-full py-20">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                            <p class="text-gray-600">Memuat data kecamatan...</p>
                        </div>
                    </div>
                </div>

                {{-- Scroll Button Right --}}
                <button id="scrollRight" class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white shadow-lg rounded-full p-3 hover:bg-gray-50 transition-all duration-300 hidden md:block">
                    <i class="fas fa-chevron-right text-gray-600"></i>
                </button>
            </div>
        </div>
    </section>

@endsection

@push('styles')
<style>
    @media (max-width: 640px) {
        #filterTahun {
            min-width: 7rem;
        }

        #dokumenChart,
        #layananChart {
            min-height: 280px;
        }

        #dokumenChart,
        #layananChart,
        #dokumenChart + *,
        #layananChart + * {
            max-width: 100%;
        }

        #districtCardsContainer > div {
            width: min(20rem, calc(100vw - 2.5rem)) !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Chart instances
    let dokumenChartInstance = null;
    let layananChartInstance = null;

    /**
     * Load data dokumen bulanan
     */
    async function loadDokumenChart(tahun) {
        try {
            const response = await fetch(`/statistik/data/dokumen?tahun=${tahun}`);
            const result = await response.json();

            if (result.success) {
                renderDokumenChart(result.data);
            }
        } catch (error) {
            console.error('Error loading dokumen data:', error);
        }
    }

    /**
     * Render chart dokumen bulanan
     */
    function renderDokumenChart(data) {
        const ctx = document.getElementById('dokumenChart')?.getContext('2d');
        if (!ctx) return;

        // Destroy existing chart
        if (dokumenChartInstance) {
            dokumenChartInstance.destroy();
        }

        const labels = data.map(item => item.nama_bulan);

        dokumenChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Kartu Keluarga',
                        data: data.map(item => item.jumlah_kk),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'KTP',
                        data: data.map(item => item.jumlah_ktp),
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Akte Lahir',
                        data: data.map(item => item.jumlah_akte_lahir),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Akte Kematian',
                        data: data.map(item => item.jumlah_akte_kematian),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    /**
     * Load data layanan bulanan
     */
    async function loadLayananChart(tahun) {
        try {
            const response = await fetch(`/statistik/data/layanan?tahun=${tahun}`);
            const result = await response.json();

            if (result.success) {
                renderLayananChart(result.data);
            }
        } catch (error) {
            console.error('Error loading layanan data:', error);
        }
    }

    /**
     * Render chart layanan bulanan
     */
    function renderLayananChart(data) {
        const ctx = document.getElementById('layananChart')?.getContext('2d');
        if (!ctx) return;

        // Destroy existing chart
        if (layananChartInstance) {
            layananChartInstance.destroy();
        }

        const labels = data.map(item => item.nama_bulan);

        layananChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Antrian',
                        data: data.map(item => item.total_antrian),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Selesai',
                        data: data.map(item => item.antrian_selesai),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Diproses',
                        data: data.map(item => item.antrian_diproses),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Menunggu',
                        data: data.map(item => item.antrian_menunggu),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    /**
     * Load semua data untuk tahun tertentu
     */
    function loadAllData(tahun) {
        loadDokumenChart(tahun);
        loadLayananChart(tahun);
        loadDistrictCards(tahun);
    }

    /**
     * Load dan render district cards
     */
    async function loadDistrictCards(tahun) {
        try {
            const response = await fetch(`/statistik/data/penduduk?tahun=${tahun}`);
            const result = await response.json();

            if (result.success) {
                renderDistrictCards(result.data);
            }
        } catch (error) {
            console.error('Error loading district data:', error);
        }
    }

    /**
     * Render district cards dengan horizontal scroll
     */
    function renderDistrictCards(data) {
        const container = document.getElementById('districtCardsContainer');
        if (!container) return;

        // Urutkan data berdasarkan jumlah penduduk (descending)
        const sortedData = [...data].sort((a, b) => b.total_penduduk - a.total_penduduk);

        // Tentukan kecamatan dengan penduduk tertinggi
        const maxPenduduk = sortedData.length > 0 ? sortedData[0].total_penduduk : 0;

        // Warna gradient untuk cards
        const gradients = [
            'from-blue-50 to-cyan-50 border-blue-100',
            'from-purple-50 to-pink-50 border-purple-100',
            'from-emerald-50 to-teal-50 border-emerald-100',
            'from-amber-50 to-orange-50 border-amber-100',
            'from-rose-50 to-red-50 border-rose-100',
        ];

        // Progress bar colors
        const progressColors = ['bg-blue-500', 'bg-purple-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500'];

        let html = '';
        sortedData.forEach((item, index) => {
            const gradientClass = gradients[index % gradients.length];
            const progressColor = progressColors[index % progressColors.length];
            const isHighest = item.total_penduduk === maxPenduduk;

            html += `
                <div class="flex-shrink-0 w-80 bg-gradient-to-br ${gradientClass} rounded-2xl p-6 border">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-bold text-gray-800">Kec. ${item.nama_kecamatan}</h4>
                        ${isHighest ? '<span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">Tertinggi</span>' : ''}
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Penduduk</span>
                            <span class="font-semibold">${number_format(item.total_penduduk)}</span>
                        </div>
                    </div>
                    <div class="mt-4 bg-gray-200 rounded-full h-2">
                        <div class="${progressColor} h-2 rounded-full transition-all duration-500" style="width: ${(item.total_penduduk / maxPenduduk) * 100}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">${Math.round((item.total_penduduk / maxPenduduk) * 100)}% dari kecamatan tertinggi</p>
                </div>
            `;
        });

        container.innerHTML = html;

        // Setup scroll buttons
        setupScrollButtons();
    }

    /**
     * Setup scroll buttons untuk horizontal scroll
     */
    function setupScrollButtons() {
        const container = document.getElementById('districtCardsContainer');
        const scrollLeft = document.getElementById('scrollLeft');
        const scrollRight = document.getElementById('scrollRight');

        if (!container || !scrollLeft || !scrollRight) return;

        const scrollAmount = 320; // Width of one card + gap

        scrollLeft.addEventListener('click', () => {
            container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });

        scrollRight.addEventListener('click', () => {
            container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });

        // Show/hide scroll buttons based on scroll position
        container.addEventListener('scroll', () => {
            scrollLeft.classList.toggle('opacity-50', container.scrollLeft <= 0);
            scrollRight.classList.toggle('opacity-50', container.scrollLeft >= container.scrollWidth - container.clientWidth);
        });
    }

    /**
     * Format number dengan pemisah ribuan
     */
    function number_format(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Event listener untuk filter tahun
    document.addEventListener('DOMContentLoaded', function() {
        const filterTahun = document.getElementById('filterTahun');
        const initialYear = filterTahun ? filterTahun.value : new Date().getFullYear();

        // Load data awal
        loadAllData(initialYear);

        // Event listener untuk perubahan filter
        if (filterTahun) {
            filterTahun.addEventListener('change', function() {
                const selectedYear = this.value;
                loadAllData(selectedYear);
            });
        }
    });
</script>
@endpush
