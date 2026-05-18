@extends('layouts.admin')

@section('content')
<!-- Welcome Banner -->
<div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-2xl p-6 md:p-8 text-white mb-6 reveal shadow-lg">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold mb-2">Selamat Datang, Admin!</h2>
            <p class="text-blue-100 text-lg">Berikut adalah ringkasan aktivitas hari ini</p>
        </div>
        <div class="flex flex-col gap-2 text-sm">
            <div class="flex items-center gap-2">
                <i class="fas fa-calendar-alt"></i>
                <span id="currentDate">{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <i class="fas fa-clock"></i>
                <span id="currentTime">{{ now()->format('H:i') }} WIB</span>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats - Antrian -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6 reveal">
    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-users text-xl text-indigo-600"></i>
            </div>
            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                Total
            </span>
        </div>
        <h3 class="text-3xl font-extrabold text-gray-800 mb-1" id="totalAntrian">0</h3>
        <p class="text-sm text-gray-600 font-medium">Total Antrian</p>
    </div>

    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-clock text-xl text-amber-600"></i>
            </div>
            <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-full">
                <i class="fas fa-hourglass-half mr-1"></i>Menunggu
            </span>
        </div>
        <h3 class="text-3xl font-extrabold text-gray-800 mb-1" id="antrianMenunggu">0</h3>
        <p class="text-sm text-gray-600 font-medium">Antrian Menunggu</p>
    </div>

    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-spinner text-xl text-blue-600"></i>
            </div>
            <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">
                <i class="fas fa-cog fa-spin mr-1" style="animation-duration: 3s;"></i>Proses
            </span>
        </div>
        <h3 class="text-3xl font-extrabold text-gray-800 mb-1" id="antrianProses">0</h3>
        <p class="text-sm text-gray-600 font-medium">Sedang Diproses</p>
    </div>

    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-check-circle text-xl text-emerald-600"></i>
            </div>
            <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                <i class="fas fa-check mr-1"></i>Selesai
            </span>
        </div>
        <h3 class="text-3xl font-extrabold text-gray-800 mb-1" id="antrianSelesai">0</h3>
        <p class="text-sm text-gray-600 font-medium">Antrian Selesai</p>
    </div>
</div>

<!-- Quick Stats - Akun & Layanan -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6 reveal">
    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-user-shield text-xl text-purple-600"></i>
            </div>
            <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded-full">
                <i class="fas fa-user-check mr-1"></i>Aktif
            </span>
        </div>
        <h3 class="text-3xl font-extrabold text-gray-800 mb-1" id="totalAkun">0</h3>
        <p class="text-sm text-gray-600 font-medium">Total Akun Admin</p>
    </div>

    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-file-alt text-xl text-rose-600"></i>
            </div>
            <span class="text-xs font-medium text-rose-600 bg-rose-50 px-2 py-1 rounded-full">
                <i class="fas fa-list mr-1"></i>Tersedia
            </span>
        </div>
        <h3 class="text-3xl font-extrabold text-gray-800 mb-1" id="totalLayanan">0</h3>
        <p class="text-sm text-gray-600 font-medium">Layanan Tersedia</p>
    </div>

    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="window.location.href='{{ route('admin.antrian-online') }}'">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-ticket-alt text-xl text-cyan-600"></i>
            </div>
            <span class="text-xs font-medium text-cyan-600 bg-cyan-50 px-2 py-1 rounded-full">
                <i class="fas fa-external-link-alt mr-1"></i>Buka
            </span>
        </div>
        <h3 class="text-3xl font-extrabold text-gray-800 mb-1">Kelola</h3>
        <p class="text-sm text-gray-600 font-medium">Antrian Online</p>
    </div>

    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="window.location.href='{{ route('admin.manajemen-akun') }}'">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-users-cog text-xl text-orange-600"></i>
            </div>
            <span class="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-1 rounded-full">
                <i class="fas fa-external-link-alt mr-1"></i>Buka
            </span>
        </div>
        <h3 class="text-3xl font-extrabold text-gray-800 mb-1">Kelola</h3>
        <p class="text-sm text-gray-600 font-medium">Manajemen Akun</p>
    </div>
</div>

<!-- Main Grid -->
<div class="grid lg:grid-cols-3 gap-6 mb-6">
    <!-- Chart -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 p-6 shadow-sm reveal">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-800">Statistik Antrian (7 Hari Terakhir)</h3>
            <select id="chartPeriod" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="7">7 Hari Terakhir</option>
                <option value="30">30 Hari Terakhir</option>
            </select>
        </div>
        <div class="h-80">
            <canvas id="antrianChart"></canvas>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm reveal">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Aksi Cepat</h3>
        <div class="space-y-3">
            <a href="{{ route('admin.antrian-online') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-500 hover:bg-blue-50 transition group">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-500 transition">
                    <i class="fas fa-ticket-alt text-blue-600 group-hover:text-white transition"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-800 text-sm">Antrian Online</p>
                    <p class="text-xs text-gray-500">Kelola antrian</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 group-hover:text-blue-600 transition"></i>
            </a>

            <a href="{{ route('admin.berita') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-purple-500 hover:bg-purple-50 transition group">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-500 transition">
                    <i class="fas fa-newspaper text-purple-600 group-hover:text-white transition"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-800 text-sm">Kelola Berita</p>
                    <p class="text-xs text-gray-500">Tambah/edit berita</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 group-hover:text-purple-600 transition"></i>
            </a>

            <a href="{{ route('admin.manajemen-akun') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-orange-500 hover:bg-orange-50 transition group">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-500 transition">
                    <i class="fas fa-users-cog text-orange-600 group-hover:text-white transition"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-800 text-sm">Manajemen Akun</p>
                    <p class="text-xs text-gray-500">Kelola akun admin</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 group-hover:text-orange-600 transition"></i>
            </a>

            <a href="{{ route('admin.visualisasi-data') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 transition group">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-500 transition">
                    <i class="fas fa-chart-line text-indigo-600 group-hover:text-white transition"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-800 text-sm">Visualisasi Data</p>
                    <p class="text-xs text-gray-500">Lihat statistik</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 group-hover:text-indigo-600 transition"></i>
            </a>

            <a href="{{ route('admin.penerbitan-kk') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-green-500 hover:bg-green-50 transition group">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-500 transition">
                    <i class="fas fa-file-export text-green-600 group-hover:text-white transition"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-800 text-sm">Penerbitan KK</p>
                    <p class="text-xs text-gray-500">Proses KK</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 group-hover:text-green-600 transition"></i>
            </a>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm reveal">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-gray-800">Aktivitas Antrian Terbaru</h3>
        <a href="{{ route('admin.antrian-online') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Lihat Semua</a>
    </div>
    <div id="recentActivity" class="space-y-3">
        <div class="text-center py-8 text-gray-400">
            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
            <p>Memuat aktivitas terbaru...</p>
        </div>
    </div>
</div>

<script>
    // Update waktu setiap detik
    function updateDateTime() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', options);
        document.getElementById('currentTime').textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
    }
    updateDateTime();
    setInterval(updateDateTime, 1000);

    // Load statistik antrian
    async function loadStatistics() {
        try {
            const response = await fetch('{{ route('antrian.statistik') }}');
            const result = await response.json();
            if (result.success) {
                const data = result.data;
                document.getElementById('totalAntrian').textContent = data.total_antrian || 0;
                document.getElementById('antrianMenunggu').textContent = data.antrian_menunggu || 0;
                document.getElementById('antrianProses').textContent = data.antrian_diproses || 0;
                document.getElementById('antrianSelesai').textContent = data.antrian_selesai || 0;
            }
        } catch (error) {
            console.error('Error loading statistics:', error);
        }
    }

    // Load total akun
    async function loadTotalAkun() {
        try {
            const response = await fetch('{{ route('admin.api.total-akun') }}');
            const result = await response.json();
            if (result.success) {
                document.getElementById('totalAkun').textContent = result.total || 0;
            }
        } catch (error) {
            document.getElementById('totalAkun').textContent = '-';
        }
    }

    // Load total layanan
    async function loadTotalLayanan() {
        try {
            const response = await fetch('{{ route('api.layanan') }}');
            const result = await response.json();
            if (result.success) {
                document.getElementById('totalLayanan').textContent = result.data.length || 0;
            }
        } catch (error) {
            document.getElementById('totalLayanan').textContent = '0';
        }
    }

    // Load aktivitas terbaru
    async function loadRecentActivity() {
        try {
            const response = await fetch('{{ route('admin.antrian-online.data') }}');
            const result = await response.json();
            if (result.success && result.data.length > 0) {
                const recentData = result.data.slice(0, 5);
                const container = document.getElementById('recentActivity');

                container.innerHTML = recentData.map(item => {
                    const statusColors = {
                        'Menunggu': 'bg-amber-100 text-amber-700',
                        'Dokumen Diterima': 'bg-blue-100 text-blue-700',
                        'Verifikasi Data': 'bg-indigo-100 text-indigo-700',
                        'Proses Cetak': 'bg-purple-100 text-purple-700',
                        'Siap Pengambilan': 'bg-emerald-100 text-emerald-700',
                        'Ditolak': 'bg-red-100 text-red-700'
                    };
                    const statusIcon = {
                        'Menunggu': 'fa-clock',
                        'Dokumen Diterima': 'fa-file-check',
                        'Verifikasi Data': 'fa-search',
                        'Proses Cetak': 'fa-print',
                        'Siap Pengambilan': 'fa-box-open',
                        'Ditolak': 'fa-ban'
                    };
                    const colorClass = statusColors[item.status_antrian] || 'bg-gray-100 text-gray-700';
                    const icon = statusIcon[item.status_antrian] || 'fa-info-circle';
                    const timeAgo = getTimeAgo(new Date(item.created_at));

                    return `
                        <div class="flex items-center gap-4 p-4 rounded-lg border border-gray-100 hover:bg-gray-50 transition">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">${item.nama_lengkap}</p>
                                <p class="text-sm text-gray-500">${item.layanan?.nama_layanan || 'Layanan Umum'}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-3 py-1 rounded-full text-xs font-medium ${colorClass}">
                                    <i class="fas ${icon} mr-1"></i>${item.status_antrian}
                                </span>
                                <p class="text-xs text-gray-400 mt-1">${timeAgo}</p>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                document.getElementById('recentActivity').innerHTML = `
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-inbox text-2xl mb-2"></i>
                        <p>Belum ada aktivitas antrian</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading recent activity:', error);
        }
    }

    // Fungsi helper untuk waktu relatif
    function getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        const intervals = {
            tahun: 31536000,
            bulan: 2592000,
            minggu: 604800,
            hari: 86400,
            jam: 3600,
            menit: 60
        };

        for (const [unit, secondsInUnit] of Object.entries(intervals)) {
            const interval = Math.floor(seconds / secondsInUnit);
            if (interval >= 1) {
                return `${interval} ${unit} yang lalu`;
            }
        }
        return 'Baru saja';
    }

    // Chart Antrian
    let antrianChart;
    async function initChart() {
        const ctx = document.getElementById('antrianChart').getContext('2d');
        const period = document.getElementById('chartPeriod')?.value || 7;

        try {
            const response = await fetch(`{{ route('admin.api.chart-antrian') }}?days=${period}`);
            const result = await response.json();

            if (result.success) {
                antrianChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: result.labels,
                        datasets: [
                            {
                                label: 'Menunggu',
                                data: result.data.menunggu,
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Diproses',
                                data: result.data.proses,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Selesai',
                                data: result.data.selesai,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { padding: 20, font: { family: 'Plus Jakarta Sans' } }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { font: { family: 'Plus Jakarta Sans' } }
                            },
                            x: {
                                ticks: { font: { family: 'Plus Jakarta Sans' } }
                            }
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Error loading chart data:', error);
        }
    }

    // Reveal Animation
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

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        Chart.defaults.font.family = 'Plus Jakarta Sans';
        Chart.defaults.color = '#6b7280';

        loadStatistics();
        loadTotalAkun();
        loadTotalLayanan();
        loadRecentActivity();
        initChart();

        // Setup chart period change listener
        const chartPeriodSelect = document.getElementById('chartPeriod');
        if (chartPeriodSelect) {
            chartPeriodSelect.addEventListener('change', () => {
                if (antrianChart) {
                    antrianChart.destroy();
                }
                initChart();
            });
        }

        window.addEventListener('scroll', reveal);
        reveal();

        // Refresh data setiap 30 detik
        setInterval(() => {
            loadStatistics();
            loadRecentActivity();
        }, 30000);
    });
</script>

<style>
    .reveal {
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.6s ease;
    }
    .reveal.active {
        opacity: 1;
        transform: translateY(0);
    }
</style>
@endsection
