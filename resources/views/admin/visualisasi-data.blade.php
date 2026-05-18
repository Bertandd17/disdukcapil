@extends('layouts.admin')

@section('content')
@php
    $page_title = 'Visualisasi Data - Admin';
@endphp

<!-- Page Header -->
<div class="mb-6 reveal">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Visualisasi Data</h1>
            <p class="text-gray-600 mt-1">Visualisasi statistik dan data kependudukan</p>
        </div>
        <div class="flex gap-3">
            <button class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                <i class="fas fa-filter"></i>
                <span>Filter</span>
            </button>
            <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <i class="fas fa-download"></i>
                <span>Export Laporan</span>
            </button>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6 reveal">
    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-users text-xl text-blue-600"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">250,487</p>
                <p class="text-sm text-gray-600">Total Penduduk</p>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-male text-xl text-emerald-600"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">127,234</p>
                <p class="text-sm text-gray-600">Laki-laki</p>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-female text-xl text-purple-600"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">123,253</p>
                <p class="text-sm text-gray-600">Perempuan</p>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-child text-xl text-orange-600"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">68,421</p>
                <p class="text-sm text-gray-600">Usia 0-17</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Grid -->
<div class="grid lg:grid-cols-2 gap-6 mb-6">
    <!-- Population by Age -->
    <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm reveal">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-800">Penduduk Berdasarkan Usia</h3>
            <select class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                <option>2026</option>
                <option>2025</option>
                <option>2024</option>
            </select>
        </div>
        <div class="h-80">
            <canvas id="ageChart"></canvas>
        </div>
    </div>

    <!-- Population by Gender per District -->
    <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm reveal">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-800">Penduduk per Kecamatan</h3>
            <select class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                <option>Semua Kecamatan</option>
                <option>Balige</option>
                <option>Borbor</option>
                <option>Lumban Julu</option>
            </select>
        </div>
        <div class="h-80">
            <canvas id="districtChart"></canvas>
        </div>
    </div>
</div>

<!-- Services Statistics -->
<div class="grid lg:grid-cols-3 gap-6 mb-6">
    <!-- Services Chart -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 p-6 shadow-sm reveal">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-800">Statistik Layanan Bulanan</h3>
            <div class="flex gap-2">
                <button class="px-3 py-1 bg-green-600 text-white rounded-lg text-sm">Bulanan</button>
                <button class="px-3 py-1 hover:bg-gray-100 rounded-lg text-sm text-gray-600">Mingguan</button>
            </div>
        </div>
        <div class="h-80">
            <canvas id="servicesChart"></canvas>
        </div>
    </div>

    <!-- Services Summary -->
    <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm reveal">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Ringkasan Layanan</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-blue-600 rounded-full"></div>
                    <span class="text-sm text-gray-700">KTP-el</span>
                </div>
                <span class="font-bold text-gray-800">1,234</span>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-emerald-600 rounded-full"></div>
                    <span class="text-sm text-gray-700">Kartu Keluarga</span>
                </div>
                <span class="font-bold text-gray-800">456</span>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-purple-600 rounded-full"></div>
                    <span class="text-sm text-gray-700">Akta Kelahiran</span>
                </div>
                <span class="font-bold text-gray-800">789</span>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-orange-600 rounded-full"></div>
                    <span class="text-sm text-gray-700">Akta Kematian</span>
                </div>
                <span class="font-bold text-gray-800">123</span>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-teal-600 rounded-full"></div>
                    <span class="text-sm text-gray-700">Pernikahan</span>
                </div>
                <span class="font-bold text-gray-800">234</span>
            </div>
        </div>
    </div>
</div>

<!-- Service Completion Rate -->
<div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm reveal">
    <h3 class="text-lg font-bold text-gray-800 mb-6">Tingkat Penyelesaian Layanan</h3>
    <div class="grid md:grid-cols-4 gap-6">
        <div class="text-center">
            <div class="relative w-32 h-32 mx-auto mb-4">
                <canvas id="ktpRate"></canvas>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-2xl font-bold text-gray-800">95%</span>
                </div>
            </div>
            <p class="font-semibold text-gray-800">KTP-el</p>
            <p class="text-sm text-gray-600">Rata-rata 3 hari</p>
        </div>

        <div class="text-center">
            <div class="relative w-32 h-32 mx-auto mb-4">
                <canvas id="kkRate"></canvas>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-2xl font-bold text-gray-800">92%</span>
                </div>
            </div>
            <p class="font-semibold text-gray-800">Kartu Keluarga</p>
            <p class="text-sm text-gray-600">Rata-rata 2 hari</p>
        </div>

        <div class="text-center">
            <div class="relative w-32 h-32 mx-auto mb-4">
                <canvas id="akteLahirRate"></canvas>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-2xl font-bold text-gray-800">88%</span>
                </div>
            </div>
            <p class="font-semibold text-gray-800">Akta Kelahiran</p>
            <p class="text-sm text-gray-600">Rata-rata 4 hari</p>
        </div>

        <div class="text-center">
            <div class="relative w-32 h-32 mx-auto mb-4">
                <canvas id="akteKematianRate"></canvas>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-2xl font-bold text-gray-800">90%</span>
                </div>
            </div>
            <p class="font-semibold text-gray-800">Akta Kematian</p>
            <p class="text-sm text-gray-600">Rata-rata 3 hari</p>
        </div>
    </div>
</div>

<script>
    Chart.defaults.font.family = 'Plus Jakarta Sans';
    Chart.defaults.color = '#6b7280';

    // Age Chart
    new Chart(document.getElementById('ageChart'), {
        type: 'doughnut',
        data: {
            labels: ['0-17 tahun', '18-30 tahun', '31-50 tahun', '51-60 tahun', '> 60 tahun'],
            datasets: [{
                data: [68421, 52345, 78923, 32156, 18642],
                backgroundColor: [
                    '#f97316',
                    '#3b82f6',
                    '#8b5cf6',
                    '#06b6d4',
                    '#64748b'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { padding: 15 }
                }
            }
        }
    });

    // District Chart
    new Chart(document.getElementById('districtChart'), {
        type: 'bar',
        data: {
            labels: ['Balige', 'Borbor', 'Lumban Julu', 'Nassau', 'Habinsaran', 'Siantar Narumonda'],
            datasets: [
                {
                    label: 'Laki-laki',
                    data: [25000, 12000, 15000, 8000, 10000, 9000],
                    backgroundColor: '#3b82f6'
                },
                {
                    label: 'Perempuan',
                    data: [24000, 11500, 14500, 7800, 9500, 8700],
                    backgroundColor: '#8b5cf6'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { padding: 15 }
                }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Services Chart
    new Chart(document.getElementById('servicesChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            datasets: [
                {
                    label: 'KTP',
                    data: [1234, 1456, 1678, 1523, 1689, 1734],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'KK',
                    data: [456, 523, 598, 512, 567, 589],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Akta',
                    data: [789, 867, 945, 823, 901, 934],
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
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
                    labels: { padding: 15 }
                }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Doughnut Charts for Service Rates
    function createDoughnut(canvasId, percentage, color) {
        new Chart(document.getElementById(canvasId), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [percentage, 100 - percentage],
                    backgroundColor: [color, '#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    }

    createDoughnut('ktpRate', 95, '#3b82f6');
    createDoughnut('kkRate', 92, '#10b981');
    createDoughnut('akteLahirRate', 88, '#8b5cf6');
    createDoughnut('akteKematianRate', 90, '#f97316');

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

    window.addEventListener('scroll', reveal);
    reveal();
</script>
@endsection
