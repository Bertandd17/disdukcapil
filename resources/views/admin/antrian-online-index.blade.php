@extends('layouts.admin')

@section('content')
@php
    $page_title = 'Manajemen Antrian Digital';
@endphp

@push('styles')
<style>
    /* Fix untuk tombol di list antrian */
    .btn-mulai-antrian {
        pointer-events: auto !important;
    }

    .btn-show-detail {
        pointer-events: auto !important;
    }
</style>
@endpush

<div class="container mx-auto px-4 py-8">

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @php
            $stats = [
                ['id' => 'waitingCount', 'label' => 'Menunggu', 'color' => 'amber', 'icon' => 'fa-clock'],
                ['id' => 'processingCount', 'label' => 'Diproses', 'color' => 'blue', 'icon' => 'fa-spinner'],
                ['id' => 'completedCount', 'label' => 'Selesai', 'color' => 'emerald', 'icon' => 'fa-check-double'],
                ['id' => 'totalCount', 'label' => 'Total Hari Ini', 'color' => 'indigo', 'icon' => 'fa-users'],
            ];
        @endphp

        @foreach($stats as $stat)
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm transition-transform hover:scale-[1.02]">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-{{ $stat['color'] }}-50 text-{{ $stat['color'] }}-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fas {{ $stat['icon'] }}"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $stat['label'] }}</p>
                    <h3 class="text-2xl font-black text-slate-800" id="{{ $stat['id'] }}">0</h3>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <h2 class="font-bold text-slate-800 flex items-center gap-2">
                <span class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-list text-blue-600"></i>
                </span>
                <span>Semua Antrian Online</span>
            </h2>
            <div class="flex items-center gap-3">
                <button onclick="refreshData()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold flex items-center gap-2" id="refreshBtn">
                    <i class="fas fa-sync-alt"></i>
                    <span>Refresh</span>
                </button>
                <span id="totalRecords" class="text-xs font-bold py-1 px-3 bg-white border border-slate-200 text-slate-500 rounded-full shadow-sm uppercase">Total: 0</span>
            </div>
        </div>

        <div id="queueList" class="divide-y divide-slate-50">
            <div class="p-20 text-center" id="initialLoading">
                <div class="animate-spin inline-block w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full mb-4"></div>
                <p class="text-slate-600 font-bold">Memuat data antrian...</p>
                <p class="text-slate-400 text-sm mt-2">Mohon tunggu sebentar</p>
            </div>
        </div>
    </div>
</div>

<div id="detailModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all scale-95" id="modalContainer">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Informasi Detail</h3>
                <button onclick="closeDetailModal()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white transition-colors text-slate-400 hover:text-red-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="detailModalContent" class="p-6"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let allQueueData = [];

    // Base URL for API calls
    const BASE_URL = '{{ url("/") }}';

    // Route paths for antrian actions (UUID will be appended)
    const ROUTES = {
        terima: '/admin/antrian-online/terima/',
        verifikasi: '/admin/antrian-online/verifikasi/',
        cetak: '/admin/antrian-online/cetak/',
        selesai: '/admin/antrian-online/selesai/',
        riwayat: '{{ route("admin.antrian-online.riwayat", ":id") }}'.replace(':id', '')
    };

    // CSRF Token
    const CSRF_TOKEN = '{{ csrf_token() }}';

    document.addEventListener('DOMContentLoaded', () => {
        console.log('DOM Content Loaded - Starting setup...');
        refreshData();
        setupEventListeners();
    });

    function setupEventListeners() {
        // Modal Backdrop click
        document.getElementById('detailModal').addEventListener('click', (e) => {
            if (e.target.id === 'detailModal') closeDetailModal();
        });

        // Event delegation untuk tombol "Dimulai" dan "Detail" yang dibuat secara dinamis
        document.getElementById('queueList').addEventListener('click', function(e) {
            const target = e.target.closest('button');

            if (!target) return;

            // Mencegah event bubbling
            e.preventDefault();
            e.stopPropagation();

            // Cek apakah tombol "Dimulai"
            if (target.dataset.action === 'mulai-antrian') {
                const id = target.dataset.id;
                if (id) {
                    console.log('Mulai antrian clicked for ID:', id);
                    mulaiAntrian(id);
                }
            }

            // Cek apakah tombol "Detail"
            if (target.dataset.action === 'show-detail') {
                const id = target.dataset.id;
                if (id) {
                    console.log('Show detail clicked for ID:', id);
                    showDetail(id);
                }
            }
        }, { passive: false });
    }

    async function refreshData() {
        console.log('=== REFRESH DATA STARTED ===');
        const startTime = performance.now();

        try {
            await Promise.all([loadStatistics(), loadAntrian()]);

            const endTime = performance.now();
            const duration = (endTime - startTime).toFixed(2);
            console.log(`=== REFRESH DATA COMPLETED in ${duration}ms ===`);
        } catch (error) {
            const endTime = performance.now();
            const duration = (endTime - startTime).toFixed(2);
            console.error(`=== REFRESH DATA FAILED after ${duration}ms ===`, error);
        }
    }

    async function loadStatistics() {
        const startTime = performance.now();

        try {
            console.log('loadStatistics: Starting request...');

            // Panggil endpoint statistics yang mengambil SEMUA data
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 detik timeout

            const res = await fetch('{{ route('admin.antrian-online.statistics') }}', {
                signal: controller.signal,
                cache: 'no-store'
            });
            clearTimeout(timeoutId);

            const fetchTime = performance.now();
            console.log(`loadStatistics: Fetch completed in ${(fetchTime - startTime).toFixed(2)}ms`);

            if (!res.ok) {
                // Coba parse error response
                let errorMessage = `HTTP error! status: ${res.status}`;
                try {
                    const errorData = await res.json();
                    if (errorData.message) {
                        errorMessage = errorData.message;
                    }
                    if (errorData.debug) {
                        errorMessage += ` (Debug: ${errorData.debug})`;
                    }
                } catch (e) {
                    // Ignore parse error
                }
                throw new Error(errorMessage);
            }

            const stats = await res.json();

            const parseTime = performance.now();
            console.log(`loadStatistics: Parse completed in ${(parseTime - fetchTime).toFixed(2)}ms`);

            console.log('=== LOAD STATISTIK ===');
            console.log('Statistics:', stats);

            // Gunakan data statistik dari endpoint
            const menungguCount = stats.menunggu || 0;
            const processingCount = stats.processing || 0;
            const completedCount = stats.completed || 0;
            const totalCount = stats.total || 0;

            console.log('Menunggu:', menungguCount);
            console.log('Diproses:', processingCount);
            console.log('Selesai:', completedCount);
            console.log('Total:', totalCount);

            document.getElementById('waitingCount').innerText = menungguCount;
            document.getElementById('processingCount').innerText = processingCount;
            document.getElementById('completedCount').innerText = completedCount;
            document.getElementById('totalCount').innerText = totalCount;

            const endTime = performance.now();
            console.log(`loadStatistics: Total time: ${(endTime - startTime).toFixed(2)}ms`);
        } catch (e) {
            console.error("Stats Error:", e);

            if (e.name === 'AbortError') {
                console.error('Request timeout - loading statistics took too long');
            }

            // Set default values on error
            document.getElementById('waitingCount').innerText = '0';
            document.getElementById('processingCount').innerText = '0';
            document.getElementById('completedCount').innerText = '0';
            document.getElementById('totalCount').innerText = '0';
        }
    }

    async function loadAntrian() {
        const container = document.getElementById('queueList');
        const startTime = performance.now();

        try {
            console.log('loadAntrian: Starting request...');

            // Muat SEMUA data tanpa filter layanan dengan timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => {
                console.error('loadAntrian: Request timeout after 30 seconds');
                controller.abort();
            }, 30000); // 30 detik timeout

            const res = await fetch('{{ route('admin.antrian-online.data') }}', {
                signal: controller.signal,
                cache: 'no-store'
            });
            clearTimeout(timeoutId);

            const fetchTime = performance.now();
            console.log(`loadAntrian: Fetch completed in ${(fetchTime - startTime).toFixed(2)}ms`);

            if (!res.ok) {
                // Coba parse error response
                let errorMessage = `HTTP error! status: ${res.status}`;
                try {
                    const errorData = await res.json();
                    if (errorData.message) {
                        errorMessage = errorData.message;
                    }
                    if (errorData.debug) {
                        errorMessage += ` (Debug: ${errorData.debug})`;
                    }
                } catch (e) {
                    // Ignore parse error
                }
                throw new Error(errorMessage);
            }

            const response = await res.json();

            const parseTime = performance.now();
            console.log(`loadAntrian: Parse completed in ${(parseTime - fetchTime).toFixed(2)}ms`);

            console.log('=== DEBUG LOAD ANTRIAN ===');
            console.log('API Response:', response);
            console.log('API Response success:', response.success);

            const data = response.data || [];

            console.log('Total data loaded:', data.length);

            if (data.length > 0) {
                console.log('Sample data pertama:', data[0]);
                console.log('Sample layanan_id:', data[0].layanan_id);
                console.log('Sample layanan nama:', data[0].layanan?.nama_layanan);
            }

            // Sort data ASCENDING berdasarkan waktu request (created_at)
            // Antrian yang request lebih awal akan muncul pertama
            const sortStart = performance.now();
            data.sort((a, b) => {
                const dateA = new Date(a.created_at);
                const dateB = new Date(b.created_at);
                return dateA - dateB;
            });
            const sortEnd = performance.now();
            console.log(`loadAntrian: Sort completed in ${(sortEnd - sortStart).toFixed(2)}ms`);

            allQueueData = data;
            console.log('All Queue Data saved:', allQueueData.length);

            // DOUBLE SAFETY: Filter HANYA status "Menunggu" untuk ditampilkan
            // Meskipun controller sudah filter, ini untuk keamanan tambahan
            const onlyMenunggu = allQueueData.filter(q => q.status_antrian === 'Menunggu');
            console.log('Filtered Menunggu:', onlyMenunggu.length);

            renderQueueList(onlyMenunggu);

            const endTime = performance.now();
            console.log(`loadAntrian: Total time: ${(endTime - startTime).toFixed(2)}ms`);
        } catch (e) {
            console.error("Error loading antrian:", e);

            if (e.name === 'AbortError') {
                console.error('Request timeout - loading antrian took too long');
                container.innerHTML = `
                    <div class="py-20 text-center">
                        <div class="w-20 h-20 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
                        </div>
                        <p class="text-red-500 font-bold">Timeout - Server terlalu lama merespons</p>
                        <p class="text-slate-400 text-sm mt-2">Silakan refresh halaman atau coba lagi nanti</p>
                        <button onclick="location.reload()" class="mt-4 px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i>Refresh Halaman
                        </button>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div class="py-20 text-center">
                        <div class="w-20 h-20 mx-auto mb-4 bg-rose-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-circle text-3xl text-rose-500"></i>
                        </div>
                        <p class="text-rose-500 font-bold">Gagal memuat data</p>
                        <p class="text-slate-400 text-sm mt-2">${e.message}</p>
                        <button onclick="location.reload()" class="mt-4 px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i>Refresh Halaman
                        </button>
                    </div>
                `;
            }
        }
    }

    function getStatusConfig(status) {
        const configs = {
            'Menunggu': { class: 'bg-amber-50 text-amber-600 border-amber-100', icon: 'fa-hourglass' },
            'Dokumen Diterima': { class: 'bg-blue-50 text-blue-600 border-blue-100', icon: 'fa-file-check' },
            'Verifikasi Data': { class: 'bg-indigo-50 text-indigo-600 border-indigo-100', icon: 'fa-search' },
            'Proses Cetak': { class: 'bg-purple-50 text-purple-600 border-purple-100', icon: 'fa-print' },
            'Siap Pengambilan': { class: 'bg-emerald-50 text-emerald-600 border-emerald-100', icon: 'fa-box-open' },
            'Ditolak': { class: 'bg-red-50 text-red-600 border-red-100', icon: 'fa-ban' },
            'Dibatalkan': { class: 'bg-rose-50 text-rose-600 border-rose-100', icon: 'fa-times' }
        };
        return configs[status] || { class: 'bg-slate-100 text-slate-500', icon: 'fa-info-circle' };
    }

    function renderQueueList(data) {
        const container = document.getElementById('queueList');

        // TRIPLE SAFETY: Filter HANYA status "Menunggu"
        // Ini untuk memastikan bahwa benar-benar hanya yang menunggu yang ditampilkan
        const filteredData = data.filter(q => q.status_antrian === 'Menunggu');

        document.getElementById('totalRecords').innerText = `Total: ${filteredData.length}`;

        if (filteredData.length === 0) {
            container.innerHTML = `
                <div class="py-20 text-center">
                    <div class="w-20 h-20 mx-auto mb-4 bg-slate-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-inbox text-3xl text-slate-300"></i>
                    </div>
                    <p class="text-slate-400 font-bold">Tidak ada antrian yang menunggu</p>
                    <p class="text-slate-300 text-sm mt-2">Semua antrian telah diproses</p>
                </div>`;
            return;
        }

        container.innerHTML = filteredData.map(q => {
            const cfg = getStatusConfig(q.status_antrian);
            const initial = q.nama_lengkap.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);

            return `
            <div class="antrian-item group p-5 sm:p-6 hover:bg-slate-50 transition-all" data-antrian-id="${q.antrian_online_id}">
                <div class="flex items-center gap-6">
                    <!-- Avatar -->
                    <div class="relative flex-shrink-0">
                        <div class="w-16 h-16 bg-gradient-to-tr from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white shadow-md group-hover:scale-105 transition-transform border-4 border-white">
                            <span class="text-xl font-black tracking-tighter">${initial}</span>
                        </div>
                    </div>

                    <!-- Info Section -->
                    <div class="flex-1 min-w-0">
                        <!-- Name & Status -->
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <h4 class="text-lg font-bold text-slate-800">${q.nama_lengkap}</h4>
                            <span class="px-3 py-1 border rounded-full text-[10px] font-black uppercase ${cfg.class} flex-shrink-0">
                                <i class="fas ${cfg.icon} mr-1"></i> ${q.status_antrian}
                            </span>
                        </div>

                        <!-- Service & Date - Aligned in one row -->
                        <div class="flex flex-wrap items-center gap-6 text-sm text-slate-500 font-medium">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-id-card text-blue-400 w-4 text-xs flex-shrink-0"></i>
                                <span class="text-slate-600">${q.layanan ? q.layanan.nama_layanan : 'Layanan Umum'}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-blue-400 w-4 text-xs flex-shrink-0"></i>
                                <span>${new Date(q.created_at).toLocaleString('id-ID', {day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit'})}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-2 items-center flex-shrink-0">
                        <button data-action="show-detail" data-id="${q.antrian_online_id}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all shadow-sm font-semibold text-sm flex items-center gap-2 h-[44px] btn-show-detail">
                            <i class="fas fa-expand-alt text-xs"></i>
                            <span>Detail</span>
                        </button>
                        ${renderActions(q)}
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    function renderActions(q) {
        const safeId = q.antrian_online_id.replace(/'/g, "\\'");
        let buttons = '';

        // E10: Only show "Dimulai" button if user has uploaded files
        // Hide button if berkas_count is 0 (no files uploaded yet)
        if (q.status_antrian === 'Menunggu') {
            if (q.has_uploads || q.berkas_count > 0) {
                buttons = `
                    <button data-action="mulai-antrian" data-id="${safeId}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-bold hover:bg-green-700 shadow-md shadow-green-100 transition-all h-[44px] btn-mulai-antrian">
                        <i class="fas fa-play mr-1"></i> Dimulai
                    </button>
                `;
            } else {
                // Show disabled button with tooltip explaining why it's disabled
                buttons = `
                    <button disabled class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-500 rounded-xl text-sm font-bold cursor-not-allowed h-[44px] btn-disabled" title="Pengguna belum mengunggah berkas">
                        <i class="fas fa-clock mr-1"></i> Menunggu Upload
                    </button>
                `;
            }
        }

        return buttons;
    }

    /**
     * Hapus antrian dari daftar dengan animasi smooth
     * @param {string} antrianId - ID antrian yang akan dihapus
     */
    function removeAntrianFromList(antrianId) {
        // 1. Hapus dari array allQueueData
        const index = allQueueData.findIndex(q => q.antrian_online_id === antrianId);
        if (index !== -1) {
            allQueueData.splice(index, 1);
        }

        // 2. Cari elemen DOM yang sesuai dan hapus dengan animasi
        const item = document.querySelector(`[data-antrian-id="${antrianId}"]`);

        if (item) {
            // Tambahkan animasi fade out
            item.style.transition = 'all 0.5s ease';
            item.style.opacity = '0';
            item.style.transform = 'translateX(-100%)';
            item.style.maxHeight = '0';
            item.style.padding = '0';
            item.style.margin = '0';
            item.style.overflow = 'hidden';

            // Hapus elemen setelah animasi selesai
            setTimeout(() => {
                item.remove();

                // Update total records
                const newTotal = allQueueData.length;
                document.getElementById('totalRecords').innerText = `Total: ${newTotal}`;

                // Update statistik
                updateStatistics();

                // Tampilkan pesan jika tidak ada antrian tersisa
                if (newTotal === 0) {
                    const container = document.getElementById('queueList');
                    container.innerHTML = `
                        <div class="py-20 text-center">
                            <div class="w-20 h-20 mx-auto mb-4 bg-slate-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-inbox text-3xl text-slate-300"></i>
                            </div>
                            <p class="text-slate-400 font-bold">Tidak ada data ditemukan</p>
                        </div>
                    `;
                }
            }, 500);
        }
    }

    /**
     * Update statistik setelah antrian dihapus
     * Memanggil ulang loadStatistics() untuk mendapatkan data terbaru
     */
    function updateStatistics() {
        // Panggil ulang loadStatistics untuk mendapatkan statistik terbaru dari server
        loadStatistics();
    }

    async function mulaiAntrian(id) {
        console.log('Mulai antrian clicked for ID:', id);
        const route = BASE_URL + ROUTES.terima + id;

        // E10: Find queue data to show file upload status
        const queueItem = allQueueData.find(q => q.antrian_online_id === id);
        const hasUploads = queueItem && (queueItem.has_uploads || queueItem.berkas_count > 0);

        if (typeof Swal !== 'undefined') {
            // E10: Show different confirmation based on upload status
            const confirmationText = hasUploads
                ? `Antrian ini memiliki ${queueItem.berkas_count} berkas yang sudah diunggah. Apakah Anda yakin ingin memulai proses antrian ini?`
                : 'Pengguna belum mengunggah berkas apapun. Apakah Anda yakin ingin melanjutkan?';

            const result = await Swal.fire({
                title: hasUploads ? 'Mulai Antrian' : 'Konfirmasi - Belum Ada Upload',
                text: confirmationText,
                icon: hasUploads ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: hasUploads ? '#16a34a' : '#f59e0b',
                cancelButtonColor: '#e5e7eb',
                confirmButtonText: 'Konfirmasi',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                allowOutsideClick: false,
                allowEscapeKey: false
            });

            if (!result.isConfirmed) {
                return;
            }

            // Loading SEDERHANA
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            });

            try {
                console.log('Fetching:', route);

                const res = await fetch(route, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                console.log('Response status:', res.status);

                const data = await res.json();
                console.log('Response data:', data);

                // Close loading first
                Swal.close();

                if (data.success) {
                    // Cari data antrian untuk redirect ke halaman layanan yang sesuai
                    const queueItem = allQueueData.find(q => q.antrian_online_id === id);

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'Antrian berhasil dimulai! Antrian akan pindah ke halaman layanan yang sesuai.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#16a34a',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        // HAPUS antrian dari daftar sebelum redirect
                        removeAntrianFromList(id);

                        // Redirect ke halaman layanan yang sesuai
                        if (queueItem && queueItem.layanan_id) {
                            const layananRoutes = {
                                '1': '/admin/penerbitan-kk',           // Kartu Keluarga
                                '2': '/admin/penerbitan-akte-lahir',   // Akte Kelahiran
                                '3': '/admin/penerbitan-akte-kematian', // Akte Kematian
                                '4': '/admin/penerbitan-lahir-mati'    // Lahir Mati
                            };

                            const targetRoute = layananRoutes[queueItem.layanan_id];
                            if (targetRoute) {
                                // Tampilkan loading sebentar sebelum redirect
                                setTimeout(() => {
                                    window.location.href = targetRoute;
                                }, 500);
                            } else {
                                refreshData();
                            }
                        } else {
                            refreshData();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message || 'Terjadi kesalahan saat memulai antrian. Silakan coba lagi atau hubungi administrator.',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#dc2626',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    });
                }
            } catch (e) {
                console.error('Error during fetch:', e);
                Swal.close();

                Swal.fire({
                    icon: 'error',
                    title: 'Error Sistem!',
                    text: 'Terjadi kesalahan sistem. Mohon coba kembali atau hubungi administrator.',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#dc2626',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });
            }
        } else {
            // Fallback if SweetAlert is not available
            console.error('SweetAlert not available!');
            alert('Error: SweetAlert tidak tersedia. Silakan refresh halaman.');
        }
    }

    // OPTIMIZED: Tampilkan detail langsung dari memory, fetch riwayat asynchronously
    function showDetail(id) {
        const modal = document.getElementById('detailModal');
        const content = document.getElementById('detailModalContent');
        const container = document.getElementById('modalContainer');

        // Cari data antrian dari memory (sangat cepat)
        const queueData = allQueueData.find(q => q.antrian_online_id == id);
        if (!queueData) {
            if (typeof SwalHelper !== 'undefined') {
                SwalHelper.modalError('Error', 'Data antrian tidak ditemukan');
            } else {
                alert('Data antrian tidak ditemukan');
            }
            return;
        }

        // Tampilkan modal dengan loading singkat
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            container.classList.remove('scale-95');
            container.classList.add('scale-100');
        }, 10);

        // Render data dasar langsung dari memory (INSTANT)
        const cfg = getStatusConfig(queueData.status_antrian);
        const initial = queueData.nama_lengkap.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);

        content.innerHTML = `
            <div class="space-y-6">
                <!-- Header dengan loading kecil untuk riwayat -->
                <div class="bg-slate-900 rounded-2xl p-5 text-white flex justify-between items-center">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase">Token Antrian</p>
                        <p class="text-3xl font-black font-mono">${queueData.nomor_antrian}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-400 uppercase">Status</p>
                        <span class="inline-flex items-center gap-2 px-3 py-1 ${cfg.class.replace('text-', 'text-white bg-').replace('border-', 'bg-opacity-20')} rounded-full text-xs font-bold">
                            <i class="fas ${cfg.icon}"></i>
                            ${queueData.status_antrian}
                        </span>
                    </div>
                </div>

                <!-- Info Dasar -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 rounded-xl">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Lengkap</p>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-gradient-to-tr from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-bold">${initial}</div>
                            <p class="font-bold text-slate-800 text-sm">${queueData.nama_lengkap}</p>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-xl">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Layanan</p>
                        <p class="font-bold text-slate-800 text-sm">${queueData.layanan?.nama_layanan || '-'}</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-xl">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Dibuat</p>
                        <p class="font-bold text-slate-800 text-sm">${new Date(queueData.created_at).toLocaleString('id-ID')}</p>
                    </div>
                </div>

                <!-- Riwayat dengan loading indicator -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-black text-slate-400 uppercase px-1">Log Aktivitas</h4>
                        <div id="riwayatLoading" class="flex items-center gap-2 text-xs text-blue-500">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Memuat riwayat...</span>
                        </div>
                    </div>
                    <div id="riwayatContainer" class="space-y-3">
                        <div class="p-4 bg-slate-50 rounded-xl text-center text-slate-400 text-sm">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Memuat log aktivitas...
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Fetch riwayat secara asynchronous (di background)
        fetch(`{{ route('admin.antrian-online.riwayat', ':id') }}`.replace(':id', id))
            .then(r => r.json())
            .then(resp => {
                // Hide loading
                const loadingEl = document.getElementById('riwayatLoading');
                if (loadingEl) loadingEl.style.display = 'none';

                console.log('Riwayat response:', resp);

                if (resp.success && resp.data && resp.data.riwayat) {
                    const riwayatContainer = document.getElementById('riwayatContainer');
                    riwayatContainer.innerHTML = resp.data.riwayat.map(function(r, index) {
                        // Tentukan warna berdasarkan status
                        let dotColor = 'bg-blue-500';
                        if (r.status === 'Ditolak') dotColor = 'bg-red-500';
                        else if (r.status === 'Siap Pengambilan') dotColor = 'bg-emerald-500';
                        else if (r.status === 'Dokumen Diterima') dotColor = 'bg-blue-500';
                        else if (r.status === 'Verifikasi Data') dotColor = 'bg-indigo-500';
                        else if (r.status === 'Proses Cetak') dotColor = 'bg-purple-500';
                        else if (r.status === 'Menunggu') dotColor = 'bg-amber-500';

                        return `
                        <div class="flex items-start gap-3 p-3 border border-slate-100 rounded-xl hover:bg-slate-50 transition">
                            <div class="mt-1 w-2 h-2 rounded-full ${dotColor} shadow-[0_0_8px_rgba(59,130,246,0.5)]"></div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-slate-800">${r.status}</p>
                                <p class="text-[11px] text-slate-400">${new Date(r.tanggal).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'})}</p>
                                ${r.keterangan ? `<p class="text-xs text-slate-500 mt-1">${r.keterangan}</p>` : ''}
                                ${r.alasan_penolakan ? `<p class="text-xs text-red-600 mt-1 font-semibold"><i class="fas fa-exclamation-circle mr-1"></i>Alasan: ${r.alasan_penolakan}</p>` : ''}
                            </div>
                        </div>
                    `;
                    }).join('');
                } else {
                    document.getElementById('riwayatContainer').innerHTML = `
                        <div class="p-4 bg-slate-50 rounded-xl text-center">
                            <p class="text-slate-400 text-sm">Tidak ada riwayat aktivitas</p>
                        </div>
                    `;
                }
            })
            .catch(e => {
                console.error('Error loading riwayat:', e);
                const loadingEl = document.getElementById('riwayatLoading');
                if (loadingEl) loadingEl.style.display = 'none';

                document.getElementById('riwayatContainer').innerHTML = `
                    <div class="p-4 bg-rose-50 rounded-xl text-center">
                        <p class="text-rose-500 text-sm">Gagal memuat riwayat</p>
                    </div>
                `;
            });
    }

    function closeDetailModal() {
        const modal = document.getElementById('detailModal');
        const container = document.getElementById('modalContainer');

        modal.classList.add('opacity-0');
        container.classList.remove('scale-100');
        container.classList.add('scale-95');

        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    // Close modal on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDetailModal();
    });
</script>
@endpush

<style>
    /* Custom Scrollbar for better UX */
    #detailModalContent::-webkit-scrollbar { width: 6px; }
    #detailModalContent::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

    /* Animation for revealing cards */
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    #queueList > div {
        animation: slideUp 0.4s ease forwards;
    }

    /* Animation for removing cards */
    @keyframes slideOut {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(-100%); }
    }

    /* Modal transition */
    #modalContainer {
        transition: transform 0.3s ease, opacity 0.3s ease;
    }

    /* Antrian item transition for smooth removal */
    .antrian-item {
        transition: all 0.5s ease;
    }
</style>

@endsection
