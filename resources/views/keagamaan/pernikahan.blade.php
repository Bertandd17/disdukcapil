@extends('layouts.admin')

@section('title', 'Penerbitan Akta Pernikahan')


@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Page Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-2xl p-6 md:p-8 text-white mb-6 reveal shadow-lg">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-2">Penerbitan Akta Pernikahan</h1>
                <p class="text-blue-100 text-lg">Kelola permohonan pencatatan perkawinan</p>
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

    {{-- Quick Stats --}}
    @php
        $statistics = [
            'total' => \App\Models\LayananPernikahan::count(),
            'pending' => \App\Models\LayananPernikahan::menungguApproveTanggal()->count(),
            'upload' => \App\Models\LayananPernikahan::where('status', \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI)->count(),
            'verifikasi' => \App\Models\LayananPernikahan::menungguVerifikasiDokumen()->count(),
            'selesai' => \App\Models\LayananPernikahan::where('status', \App\Models\LayananPernikahan::STATUS_SELESAI)->count(),
            'ditolak' => \App\Models\LayananPernikahan::whereIn('status', [
                \App\Models\LayananPernikahan::STATUS_TANGGAL_DITOLAK,
                \App\Models\LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN,
                \App\Models\LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
            ])->count(),
        ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="stat-card bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="filterByStatus('')">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-alt text-gray-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold text-gray-800">{{ $statistics['total'] }}</h3>
            <p class="text-xs text-gray-600 font-medium">Total</p>
        </div>

        <div class="stat-card bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="filterByStatus('MENUNGGU_APPROVE_TANGGAL')">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-blue-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold text-blue-600">{{ $statistics['pending'] }}</h3>
            <p class="text-xs text-gray-600 font-medium">Pending</p>
        </div>

        <div class="stat-card bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="filterByStatus('TANGGAL_DISETUJUI')">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-upload text-cyan-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold text-cyan-600">{{ $statistics['upload'] }}</h3>
            <p class="text-xs text-gray-600 font-medium">Upload</p>
        </div>

        <div class="stat-card bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="filterByStatus('DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI')">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-search text-purple-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold text-purple-600">{{ $statistics['verifikasi'] }}</h3>
            <p class="text-xs text-gray-600 font-medium">Verifikasi</p>
        </div>

        <div class="stat-card bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="filterByStatus('SELESAI')">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold text-green-600">{{ $statistics['selesai'] }}</h3>
            <p class="text-xs text-gray-600 font-medium">Selesai</p>
        </div>

        <div class="stat-card bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="filterByStatus('ditolak')">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold text-red-600">{{ $statistics['ditolak'] }}</h3>
            <p class="text-xs text-gray-600 font-medium">Ditolak</p>
        </div>
    </div>

    {{-- Main Content: Calendar & List --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Calendar View --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800">Kalender Pernikahan</h3>
                    <div class="flex items-center gap-2">
                        <button onclick="changeMonth(-1)" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-chevron-left text-gray-600 text-sm"></i>
                        </button>
                        <span id="currentMonth" class="text-sm font-medium text-gray-700 min-w-32 text-center"></span>
                        <button onclick="changeMonth(1)" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-chevron-right text-gray-600 text-sm"></i>
                        </button>
                    </div>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-7 gap-1 text-center mb-2">
                        <div class="text-xs font-semibold text-gray-500">Min</div>
                        <div class="text-xs font-semibold text-gray-500">Sen</div>
                        <div class="text-xs font-semibold text-gray-500">Sel</div>
                        <div class="text-xs font-semibold text-gray-500">Rab</div>
                        <div class="text-xs font-semibold text-gray-500">Kam</div>
                        <div class="text-xs font-semibold text-gray-500">Jum</div>
                        <div class="text-xs font-semibold text-gray-500">Sab</div>
                    </div>
                    <div id="calendarGrid" class="grid grid-cols-7 gap-1"></div>
                </div>
            </div>
        </div>

        {{-- List View --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="p-4 border-b border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <h3 class="font-bold text-gray-800">Daftar Permohonan</h3>
                        <div class="flex items-center gap-2">
                            <input type="text" id="searchInput" placeholder="Cari nomor antrian atau nama..."
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                {{-- Tabs --}}
                <div class="flex border-b border-gray-100 overflow-x-auto">
                    <button class="tab-btn active px-4 py-3 text-sm font-medium border-b-2 border-blue-600 text-blue-600 whitespace-nowrap" data-status="">
                        Semua
                    </button>
                    <button class="tab-btn px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap" data-status="MENUNGGU_APPROVE_TANGGAL">
                        <i class="fas fa-clock mr-1"></i>Pending
                    </button>
                    <button class="tab-btn px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap" data-status="TANGGAL_DISETUJUI">
                        <i class="fas fa-upload mr-1"></i>Upload
                    </button>
                    <button class="tab-btn px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap" data-status="DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI">
                        <i class="fas fa-search mr-1"></i>Verifikasi
                    </button>
                    <button class="tab-btn px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap" data-status="SELESAI">
                        <i class="fas fa-check mr-1"></i>Selesai
                    </button>
                    <button class="tab-btn px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap" data-status="ditolak">
                        <i class="fas fa-times mr-1"></i>Ditolak
                    </button>
                </div>

                {{-- List --}}
                <div id="pernikahanList" class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                    @php
                        $currentStatus = request('status', '');
                        $query = \App\Models\LayananPernikahan::with(['dokumen', 'user'])
                            ->orderBy('created_at', 'desc');

                        if ($currentStatus === 'ditolak') {
                            $query->whereIn('status', [
                                \App\Models\LayananPernikahan::STATUS_TANGGAL_DITOLAK,
                                \App\Models\LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN,
                                \App\Models\LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
                            ]);
                        } elseif ($currentStatus) {
                            $query->where('status', $currentStatus);
                        }

                        $pernikahan = $query->limit(50)->get();
                    @endphp

                    @if($pernikahan->isNotEmpty())
                        @foreach($pernikahan as $item)
                            <div class="p-4 hover:bg-gray-50 transition-colors cursor-pointer" onclick="showDetail('{{ $item->pernikahan_id }}')">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0
                                        @if($item->status === \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI) bg-cyan-100
                                        @elseif($item->status === \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI) bg-purple-100
                                        @elseif($item->status === \App\Models\LayananPernikahan::STATUS_SELESAI) bg-green-100
                                        @elseif(in_array($item->status, [\App\Models\LayananPernikahan::STATUS_TANGGAL_DITOLAK, \App\Models\LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN, \App\Models\LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN])) bg-red-100
                                        @else bg-yellow-100 @endif">
                                        <i class="fas @if($item->status === \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI) fa-upload text-cyan-600
                                            @elseif($item->status === \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI) fa-search text-purple-600
                                            @elseif($item->status === \App\Models\LayananPernikahan::STATUS_SELESAI) fa-check text-green-600
                                            @elseif(in_array($item->status, [\App\Models\LayananPernikahan::STATUS_TANGGAL_DITOLAK, \App\Models\LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN, \App\Models\LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN])) fa-times text-red-600
                                            @else fa-clock text-yellow-600 @endif text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="font-medium text-gray-800 truncate">{{ $item->nama_mempelai_pria }}</p>
                                            <span class="text-xs px-2 py-1 rounded-full flex-shrink-0
                                                @if($item->status === \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI) bg-cyan-100 text-cyan-700
                                                @elseif($item->status === \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI) bg-purple-100 text-purple-700
                                                @elseif($item->status === \App\Models\LayananPernikahan::STATUS_SELESAI) bg-green-100 text-green-700
                                                @elseif(in_array($item->status, [\App\Models\LayananPernikahan::STATUS_TANGGAL_DITOLAK, \App\Models\LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN, \App\Models\LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN])) bg-red-100 text-red-700
                                                @else bg-yellow-100 text-yellow-700 @endif">
                                                {{ $item->status_label }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500 font-mono">{{ $item->nomor_antrian }}</p>
                                        <div class="flex items-center gap-4 mt-1 text-xs text-gray-400">
                                            @if($item->tanggal_perkawinan)
                                                <span><i class="fas fa-calendar mr-1"></i>{{ $item->tanggal_perkawinan->format('d M Y') }}</span>
                                            @endif
                                            @if($item->nama_gereja)
                                                <span><i class="fas fa-church mr-1"></i>{{ $item->nama_gereja }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-400 flex-shrink-0"></i>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                            <p>Tidak ada data</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail --}}
<div id="detailModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Detail Pernikahan</h3>
                <p id="modalNomorAntrian" class="text-sm text-gray-500 font-mono"></p>
            </div>
            <button onclick="closeModal()" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                <i class="fas fa-times text-gray-600"></i>
            </button>
        </div>
        <div id="modalContent" class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
            {{-- Content will be loaded dynamically --}}
        </div>
    </div>
</div>

{{-- Modal Konfirmasi --}}
<div id="confirmModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="p-6 text-center">
            <div id="confirmIcon" class="w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-4"></div>
            <h3 id="confirmTitle" class="text-xl font-bold text-gray-800 mb-2"></h3>
            <p id="confirmMessage" class="text-gray-600 mb-4"></p>

            <div id="confirmReasonWrapper" class="text-left mb-4 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan <span class="text-red-500">*</span></label>
                <textarea id="confirmReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Tuliskan alasan..."></textarea>
            </div>

            <div class="flex gap-3">
                <button onclick="closeConfirmModal()" class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 rounded-xl font-medium text-gray-800 transition-colors">
                    Batal
                </button>
                <button id="confirmActionBtn" class="flex-1 px-4 py-3 rounded-xl font-medium text-white transition-colors">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="currentPernikahanId">
<input type="hidden" id="currentAction">

{{-- ==== SWEETALERT2 ASSETS — khusus view ini, jangan ubah urutan ==== --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/sweetalert-helper.js') }}"></script>
<script src="{{ asset('js/sweetalert-disdukcapil.js') }}"></script>
<link rel="stylesheet" href="{{ asset('css/swal-final-fix.css') }}">
<script src="{{ asset('js/swal-final-fix.js') }}"></script>

{{-- ==== SWEETALERT HELPER FALLBACK (defensive) ==== --}}
<script>
(function installSweetAlertHelper() {
    'use strict';

    if (typeof window.Swal === 'undefined') {
        console.error('SweetAlert2 belum termuat. Pastikan CDN sweetalert2@11 dapat diakses.');
        return;
    }

    if (!window.Swal.__loadingInterceptorInstalled) {
        const originalFire = window.Swal.fire.bind(window.Swal);

        window.Swal.fire = function(config) {
            if (config && typeof config === 'object') {
                const title = String(config.title || '').toLowerCase();
                const isLoading =
                    config.showLoading === true ||
                    (typeof config.didOpen === 'function' && config.didOpen.toString().includes('showLoading')) ||
                    ['memproses', 'memuat', 'menyimpan', 'mengirim', 'memeriksa', 'mengupload', 'loading', 'tunggu'].some(k => title.includes(k));

                if (isLoading) {
                    config.showConfirmButton = false;
                    config.showDenyButton = false;
                    config.showCancelButton = false;
                    config.allowOutsideClick = false;
                    config.allowEscapeKey = false;
                }
            }

            return originalFire(config);
        };

        window.Swal.__loadingInterceptorInstalled = true;
    }

    if (typeof window.SwalHelper === 'undefined') {
        window.SwalHelper = {};
    }

    window.SwalHelper.loading = window.SwalHelper.loading || function(title, html) {
        return Swal.fire({
            title: title || 'Memproses...',
            html : html  || 'Mohon tunggu sebentar...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            showDenyButton: false,
            showCancelButton: false,
            didOpen: function() {
                Swal.showLoading();
            }
        });
    };

    window.SwalHelper.close = window.SwalHelper.close || function() {
        Swal.close();
    };

    window.SwalHelper.success = window.SwalHelper.success || function(message, title) {
        return Swal.fire({
            icon: 'success',
            title: title || 'Berhasil!',
            text: message || 'Operasi berhasil dilakukan.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            customClass: {
                popup: 'swal-toast-success'
            }
        });
    };

    window.SwalHelper.error = window.SwalHelper.error || function(message, title) {
        return Swal.fire({
            icon: 'error',
            title: title || 'Gagal!',
            text: message || 'Operasi gagal dilakukan.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc2626'
        });
    };

    window.SwalHelper.warning = window.SwalHelper.warning || function(message, title) {
        return Swal.fire({
            icon: 'warning',
            title: title || 'Peringatan!',
            text: message || 'Periksa kembali data Anda.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#d97706'
        });
    };

    window.SwalHelper.info = window.SwalHelper.info || function(message, title) {
        return Swal.fire({
            icon: 'info',
            title: title || 'Informasi',
            text: message || '',
            confirmButtonText: 'OK',
            confirmButtonColor: '#2563eb'
        });
    };

    window.SwalHelper.confirm = window.SwalHelper.confirm || function(message, onYes, onNo, title) {
        return Swal.fire({
            title: title || 'Konfirmasi',
            text: message || 'Apakah Anda yakin ingin melanjutkan?',
            icon: 'question',
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0052CC',
            cancelButtonColor: '#6b7280',
            reverseButtons: true
        }).then(function(result) {
            if (result.isConfirmed && typeof onYes === 'function') onYes();
            if (result.isDismissed && typeof onNo === 'function') onNo();
            return result;
        });
    };

    window.SwalHelper.confirmReason = window.SwalHelper.confirmReason || function(options) {
        options = options || {};

        return Swal.fire({
            title: options.title || 'Konfirmasi',
            html: options.html || options.message || 'Tuliskan alasan untuk melanjutkan.',
            icon: options.icon || 'warning',
            input: 'textarea',
            inputPlaceholder: options.placeholder || 'Tuliskan alasan...',
            inputAttributes: {
                'aria-label': 'Alasan'
            },
            inputValidator: function(value) {
                if (!value || !value.trim()) {
                    return options.requiredMessage || 'Alasan harus diisi.';
                }
                return null;
            },
            showCancelButton: true,
            confirmButtonText: options.confirmText || 'Ya, Lanjutkan',
            cancelButtonText: options.cancelText || 'Batal',
            confirmButtonColor: options.confirmColor || '#dc2626',
            cancelButtonColor: '#6b7280',
            reverseButtons: true
        });
    };
})();
</script>

<style>
.tab-btn.active {
    border-color: #0052CC;
    color: #0052CC;
}
.calendar-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}
.calendar-day:hover {
    background: #f0f5ff;
}
.calendar-day.today {
    background: #0052CC;
    color: white;
}
.calendar-day.has-event {
    background: #10b981;
    color: white;
    font-weight: 600;
}
.calendar-day.other-month {
    color: #d1d5db;
}
.calendar-day.empty {
    cursor: default;
}
.calendar-day.empty:hover {
    background: transparent;
}
</style>

<script>
// Global state untuk auto-refresh dan kalender
let autoRefreshInterval = null;
let isChecking = false;
let lastCheckTime = new Date().toISOString();
let knownStatuses = new Map();

// Calendar
let currentDate = new Date();
let calendarData = {};

function renderCalendar() {
    const grid = document.getElementById('calendarGrid');
    const monthLabel = document.getElementById('currentMonth');

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                       'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    monthLabel.textContent = `${monthNames[month]} ${year}`;

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();

    const today = new Date();

    let html = '';

    // Days from previous month
    for (let i = firstDay - 1; i >= 0; i--) {
        html += `<div class="calendar-day other-month">${daysInPrevMonth - i}</div>`;
    }

    // Days in current month
    for (let day = 1; day <= daysInMonth; day++) {
        const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = today.getDate() === day &&
                       today.getMonth() === month &&
                       today.getFullYear() === year;
        const hasEvent = calendarData[dateKey] && calendarData[dateKey].length > 0;

        let classes = 'calendar-day';
        if (isToday) classes += ' today';
        else if (hasEvent) classes += ' has-event';

        html += `<div class="${classes}" onclick="${hasEvent ? `showDateEvents('${dateKey}')` : ''}">${day}</div>`;
    }

    // Days from next month
    const remainingCells = 42 - (firstDay + daysInMonth);
    for (let i = 1; i <= remainingCells; i++) {
        html += `<div class="calendar-day other-month">${i}</div>`;
    }

    grid.innerHTML = html;
}

function changeMonth(delta) {
    currentDate.setMonth(currentDate.getMonth() + delta);
    loadCalendarData();
}

async function loadCalendarData() {
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, '0');

    try {
        const response = await fetch(`{{ route('api.pernikahan.keagamaan.calendar') }}?year=${year}&month=${month}`);
        const data = await response.json();
        calendarData = data.data || {};
        renderCalendar();
    } catch (error) {
        console.error('Error loading calendar:', error);
        renderCalendar();
    }
}

function showDateEvents(dateKey) {
    const events = calendarData[dateKey] || [];
    if (events.length === 1) {
        showDetail(events[0].id);
    } else if (events.length > 1) {
        // Show list of events for this date
        const modal = document.getElementById('detailModal');
        const content = document.getElementById('modalContent');

        content.innerHTML = `
            <h4 class="font-bold text-gray-800 mb-4">Pernikahan pada ${dateKey}</h4>
            <div class="space-y-3">
                ${events.map(e => `
                    <div class="p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100" onclick="showDetail('${e.id}')">
                        <p class="font-medium text-gray-800">${e.nama_pria}</p>
                        <p class="text-sm text-gray-500">${e.nomor_antrian}</p>
                        <p class="text-sm text-gray-500">${e.gereja}</p>
                    </div>
                `).join('')}
            </div>
        `;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

// Tab filtering
function filterByStatus(status) {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.status === status) {
            btn.classList.add('active');
        }
    });

    const url = new URL(window.location);
    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    window.location.href = url.toString();
}

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => filterByStatus(btn.dataset.status));
});

// Search
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#pernikahanList > div').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(query) ? '' : 'none';
        });
    }, 300);
});

// Detail Modal
async function showDetail(id) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('modalContent');
    const nomorAntrian = document.getElementById('modalNomorAntrian');

    content.innerHTML = '<div class="flex items-center justify-center py-12"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i></div>';
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    try {
        const response = await fetch(`{{ route('api.pernikahan.keagamaan.detail', ':id') }}`.replace(':id', id));
        const result = await response.json();

        if (result.success) {
            const data = result.data;
            nomorAntrian.textContent = data.nomor_antrian;

            let actionsHtml = '';

            // Actions based on status
            if (data.status === 'MENUNGGU_APPROVE_TANGGAL') {
                actionsHtml = `
                    <div class="flex gap-3 mt-6 pt-6 border-t border-gray-100">
                        <button onclick="showConfirm('approve', '${data.pernikahan_id}')" class="flex-1 px-4 py-3 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition-colors">
                            <i class="fas fa-check mr-2"></i>Setujui Tanggal
                        </button>
                        <button onclick="showConfirm('reject', '${data.pernikahan_id}')" class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition-colors">
                            <i class="fas fa-times mr-2"></i>Tolak
                        </button>
                    </div>
                `;
            } else if (data.status === 'DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI') {
                actionsHtml = `
                    <div class="flex gap-3 mt-6 pt-6 border-t border-gray-100">
                        <button onclick="verifyAllDocuments('${data.pernikahan_id}')" class="flex-1 px-4 py-3 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition-colors">
                            <i class="fas fa-check mr-2"></i>Verifikasi Semua
                        </button>
                        <button onclick="showConfirm('reject_doc', '${data.pernikahan_id}')" class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition-colors">
                            <i class="fas fa-times mr-2"></i>Perlu Perbaikan
                        </button>
                    </div>
                `;
            } else if (data.status === 'DOKUMEN_DIVERIFIKASI') {
                actionsHtml = `
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h4 class="font-semibold text-gray-800 mb-3">Upload Berkas Pernikahan</h4>
                        <div class="space-y-3">
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center">
                                <input type="file" id="fileBerkasAcara" accept=".pdf" class="hidden" onchange="handleFileUpload(this, '${data.pernikahan_id}', 'berkas_acara')">
                                <label for="fileBerkasAcara" class="cursor-pointer">
                                    <i class="fas fa-file-pdf text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600">Berkas Acara</p>
                                    <p class="text-xs text-gray-400">PDF, maks 5MB</p>
                                </label>
                            </div>
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center">
                                <input type="file" id="fileSuratKeterangan" accept=".pdf" class="hidden" onchange="handleFileUpload(this, '${data.pernikahan_id}', 'surat_keterangan')">
                                <label for="fileSuratKeterangan" class="cursor-pointer">
                                    <i class="fas fa-file-alt text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600">Surat Keterangan</p>
                                    <p class="text-xs text-gray-400">PDF, maks 5MB</p>
                                </label>
                            </div>
                        </div>
                    </div>
                `;
            }

            let dokumenHtml = '';
            if (data.dokumen && data.dokumen.length > 0) {
                dokumenHtml = `
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h4 class="font-semibold text-gray-800 mb-3">Dokumen yang Diupload</h4>
                        <div class="grid grid-cols-2 gap-3">
                            ${data.dokumen.map(d => `
                                <div class="p-3 bg-gray-50 rounded-xl">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-800">${d.jenis_dokumen_label}</span>
                                        <span class="text-xs px-2 py-1 rounded-full ${d.status === 'VERIFIED' ? 'bg-green-100 text-green-700' : d.status === 'DITOLAK' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'}">
                                            ${d.status_label}
                                        </span>
                                    </div>
                                    ${d.file_path ? `<a href="${d.file_url}" target="_blank" class="text-xs text-blue-600 hover:underline"><i class="fas fa-eye mr-1"></i>Lihat</a>` : '-'}
                                    ${d.catatan_verifikasi ? `<p class="text-xs text-gray-500 mt-1">${d.catatan_verifikasi}</p>` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            content.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-3">Informasi Mempelai Pria</h4>
                        <div class="space-y-2 text-sm">
                            <div><span class="text-gray-500">Nama:</span> <span class="font-medium">${data.nama_mempelai_pria}</span></div>
                            <div><span class="text-gray-500">NIK:</span> <span class="font-mono">${data.nik_mempelai_pria}</span></div>
                            <div><span class="text-gray-500">TTL:</span> ${data.tempat_lahir_mempelai_pria}, ${data.tanggal_lahir_mempelai_pria}</div>
                            <div><span class="text-gray-500">Agama:</span> ${data.agama_mempelai_pria}</div>
                            <div><span class="text-gray-500">Pekerjaan:</span> ${data.pekerjaan_mempelai_pria || '-'}</div>
                            <div><span class="text-gray-500">Alamat:</span> ${data.alamat_mempelai_pria}</div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-3">Informasi Mempelai Wanita</h4>
                        <div class="space-y-2 text-sm">
                            <div><span class="text-gray-500">Nama:</span> <span class="font-medium">${data.nama_mempelai_wanita}</span></div>
                            <div><span class="text-gray-500">NIK:</span> <span class="font-mono">${data.nik_mempelai_wanita}</span></div>
                            <div><span class="text-gray-500">TTL:</span> ${data.tempat_lahir_mempelai_wanita}, ${data.tanggal_lahir_mempelai_wanita}</div>
                            <div><span class="text-gray-500">Agama:</span> ${data.agama_mempelai_wanita}</div>
                            <div><span class="text-gray-500">Pekerjaan:</span> ${data.pekerjaan_mempelai_wanita || '-'}</div>
                            <div><span class="text-gray-500">Alamat:</span> ${data.alamat_mempelai_wanita}</div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-100">
                    <h4 class="font-semibold text-gray-800 mb-3">Informasi Pernikahan</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                        <div><span class="text-gray-500">Tanggal:</span> <span class="font-medium">${data.tanggal_perkawinan || '-'}</span></div>
                        <div><span class="text-gray-500">Gereja:</span> <span class="font-medium">${data.nama_gereja || '-'}</span></div>
                        <div><span class="text-gray-500">Status:</span> <span class="font-medium">${data.status_label}</span></div>
                    </div>
                    ${data.alasan_ditolak ? `<div class="mt-2 p-3 bg-red-50 rounded-lg text-sm text-red-700"><i class="fas fa-info-circle mr-2"></i>${data.alasan_ditolak}</div>` : ''}
                </div>

                ${dokumenHtml}
                ${actionsHtml}
            `;
        } else {
            content.innerHTML = '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-circle text-2xl mb-2"></i><p>Gagal memuat data</p></div>';
        }
    } catch (error) {
        content.innerHTML = '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-circle text-2xl mb-2"></i><p>Gagal memuat data</p></div>';
    }
}

function closeModal() {
    const modal = document.getElementById('detailModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Confirm Modal
function showConfirm(action, id) {
    document.getElementById('currentPernikahanId').value = id;
    document.getElementById('currentAction').value = action;

    if (action === 'approve') {
        return Swal.fire({
            title: 'Setujui Tanggal Pernikahan?',
            text: 'Tanggal perkawinan akan disetujui dan keagamaan dapat mengupload dokumen.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#6b7280',
            reverseButtons: true
        }).then(function(result) {
            if (result.isConfirmed) {
                executeConfirm();
            }
        });
    }

    if (action === 'reject' || action === 'reject_doc') {
        const isRejectDoc = action === 'reject_doc';

        return SwalHelper.confirmReason({
            title: isRejectDoc ? 'Tolak Dokumen?' : 'Tolak Tanggal Pernikahan?',
            html: isRejectDoc
                ? 'Dokumen ditolak dan memerlukan perbaikan dari keagamaan.'
                : 'Tanggal perkawinan akan ditolak. Keagamaan perlu mengajukan tanggal baru.',
            icon: 'warning',
            placeholder: 'Tuliskan alasan penolakan...',
            confirmText: isRejectDoc ? 'Ya, Tolak Dokumen' : 'Ya, Tolak Tanggal',
            confirmColor: '#dc2626'
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById('confirmReason').value = result.value || '';
                executeConfirm();
            }
        });
    }
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    const reason = document.getElementById('confirmReason');
    if (reason) reason.value = '';
}

async function executeConfirm() {
    const id = document.getElementById('currentPernikahanId').value;
    const action = document.getElementById('currentAction').value;
    const reasonEl = document.getElementById('confirmReason');
    const reason = reasonEl ? reasonEl.value : '';

    if ((action === 'reject' || action === 'reject_doc') && !reason.trim()) {
        SwalHelper.warning('Alasan harus diisi');
        return;
    }

    try {
        let url, method, body, loadingTitle;

        if (action === 'approve') {
            url = '{{ route('api.pernikahan.keagamaan.approve', ':id') }}'.replace(':id', id);
            method = 'POST';
            body = JSON.stringify({});
            loadingTitle = 'Menyetujui tanggal...';
        } else if (action === 'reject') {
            url = '{{ route('api.pernikahan.keagamaan.reject', ':id') }}'.replace(':id', id);
            method = 'POST';
            body = JSON.stringify({ alasan: reason });
            loadingTitle = 'Menolak tanggal...';
        } else if (action === 'reject_doc') {
            url = '{{ route('api.pernikahan.keagamaan.reject-doc', ':id') }}'.replace(':id', id);
            method = 'POST';
            body = JSON.stringify({ alasan: reason });
            loadingTitle = 'Menolak dokumen...';
        } else {
            SwalHelper.error('Aksi tidak valid.');
            return;
        }

        SwalHelper.loading(loadingTitle, 'Mohon tunggu, sistem sedang memproses data.');

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: body
        });

        const result = await response.json().catch(function() {
            return {
                success: false,
                message: 'Response server tidak valid.'
            };
        });

        SwalHelper.close();

        if (response.ok && result.success) {
            closeConfirmModal();
            closeModal();

            await Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.message || 'Operasi berhasil diproses.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#16a34a'
            });

            window.location.reload();
        } else {
            SwalHelper.error(result.message || 'Operasi gagal.');
        }
    } catch (error) {
        SwalHelper.close();
        SwalHelper.error('Terjadi kesalahan saat memproses permintaan.');
    }
}

async function verifyAllDocuments(id) {
    const confirmation = await Swal.fire({
        title: 'Verifikasi Semua Dokumen?',
        text: 'Semua dokumen yang diupload akan ditandai valid.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Verifikasi',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        reverseButtons: true
    });

    if (!confirmation.isConfirmed) return;

    try {
        SwalHelper.loading('Memverifikasi dokumen...', 'Mohon tunggu, sistem sedang memproses data.');

        const response = await fetch('{{ route('api.pernikahan.keagamaan.verify-all', ':id') }}'.replace(':id', id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const result = await response.json().catch(function() {
            return {
                success: false,
                message: 'Response server tidak valid.'
            };
        });

        SwalHelper.close();

        if (response.ok && result.success) {
            closeModal();

            await Swal.fire({
                icon: 'success',
                title: 'Dokumen Diverifikasi',
                text: result.message || 'Semua dokumen berhasil diverifikasi.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#16a34a'
            });

            window.location.reload();
        } else {
            SwalHelper.error(result.message || 'Verifikasi gagal.');
        }
    } catch (error) {
        SwalHelper.close();
        SwalHelper.error('Terjadi kesalahan saat memverifikasi dokumen.');
    }
}

async function handleFileUpload(input, id, type) {
    const file = input.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        SwalHelper.error('Ukuran file maksimal 5MB');
        input.value = '';
        return;
    }

    if (file.type !== 'application/pdf') {
        SwalHelper.error('File harus berformat PDF');
        input.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', type);

    try {
        SwalHelper.loading('Mengupload berkas...');

        const response = await fetch('{{ route('api.pernikahan.keagamaan.upload-berkas', ':id') }}'.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json().catch(function() {
            return {
                success: false,
                message: 'Response server tidak valid.'
            };
        });
        SwalHelper.close();

        if (response.ok && result.success) {
            SwalHelper.success('Berkas berhasil diupload');
            showDetail(id);
        } else {
            SwalHelper.error(result.message || 'Upload gagal');
        }
    } catch (error) {
        SwalHelper.close();
        SwalHelper.error('Terjadi kesalahan');
    }
}

// Update time
function updateDateTime() {
    const now = new Date();
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
        timeElement.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
    }
}
updateDateTime();
setInterval(updateDateTime, 1000);

// Reveal animation
document.addEventListener('DOMContentLoaded', function() {
    const reveals = document.querySelectorAll('.reveal');
    reveals.forEach(function(reveal) {
        reveal.classList.add('active');
    });

    loadCalendarData();
});

// Close modal on backdrop click
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Fungsi check update dari server
async function checkStatusUpdates() {
 if (isChecking) return;
 isChecking = true;

 try {
 const response = await fetch(`{{ route('keagamaan.pernikahan.check-updates') }}?last_check=${encodeURIComponent(lastCheckTime)}`, {
 method: 'GET',
 headers: {
 'Accept': 'application/json',
 'X-Requested-With': 'XMLHttpRequest'
 },
 credentials: 'same-origin'
 });

 const data = await response.json();

 if (data.timestamp) {
 lastCheckTime = data.timestamp;
 }

 if (data.success && data.has_updates && data.updates.length > 0) {
 console.log(' Updates detected:', data.updates);

 // Cek perubahan status
 data.updates.forEach(update => {
 const oldStatus = knownStatuses.get(update.pernikahan_id);
 const newStatus = update.status;

 // Jika status berubah
 if (oldStatus && oldStatus !== newStatus) {
 console.log(`Status changed for ${(update.nama_mempelai_pria || '') + (update.nama_mempelai_wanita ? ' & ' + update.nama_mempelai_wanita : '')}: ${oldStatus} -> ${newStatus}`);

 // Update knownStatuses
 knownStatuses.set(update.pernikahan_id, newStatus);

 // Jika berubah ke Disetujui
 if (newStatus === 'TANGGAL_DISETUJUI') {
 const Toast = Swal.mixin({
 toast: true,
 position: 'top-end',
 showConfirmButton: true,
 confirmButtonText: 'Lihat',
 confirmButtonColor: '#16a34a',
 timer: 0,
 backdrop: false,
 });

 Toast.fire({
 icon: 'success',
 title: 'Tanggal Disetujui!',
 html: `
 <div class="text-left">
 <p class="font-medium">${(update.nama_mempelai_pria || '') + (update.nama_mempelai_wanita ? ' & ' + update.nama_mempelai_wanita : '')}</p>
 <p class="text-sm text-gray-600">${update.nomor_antrian}</p>
 <p class="text-sm text-green-600 mt-1"><i class="fas fa-check-circle mr-1"></i>${update.tanggal_perkawinan}</p>
 </div>
 `,
 }).then((result) => {
 if (result.isConfirmed) {
 location.reload();
 }
 });
 }
 }
 });

 // Update UI jika ada perubahan
 if (data.updates.length > 0) {
 await refreshUIFromServer();
 }
 }

 } catch (error) {
 console.error('Error checking updates:', error);
 } finally {
 isChecking = false;
 }
}

// Refresh UI dari server
async function refreshUIFromServer() {
 try {
 const response = await fetch(window.location.href);
 const html = await response.text();
 const parser = new DOMParser();
 const newDoc = parser.parseFromString(html, 'text/html');

 // Update list permohonan
 const newList = newDoc.getElementById('permohonanList');
 if (newList) {
 document.getElementById('permohonanList').innerHTML = newList.innerHTML;
 // Re-apply filter
 const currentFilter = document.getElementById('filterStatus')?.value || 'all';
 filterList(currentFilter);
 }

 // Update statistics jika ada
 const newStats = newDoc.querySelectorAll('.grid.grid-cols-2.md\\:grid-cols-4 .text-2xl');
 if (newStats.length > 0) {
 const currentStats = document.querySelectorAll('.grid.grid-cols-2.md\\:grid-cols-4 .text-2xl');
 newStats.forEach((stat, i) => {
 if (currentStats[i]) {
 currentStats[i].textContent = stat.textContent;
 }
 });
 }

 } catch (error) {
 console.error('Error refreshing UI:', error);
 }
}

// Mulai auto-refresh
function startAutoRefresh(intervalSeconds = 10) {
 stopAutoRefresh();
 console.log(` Auto-refresh started (every ${intervalSeconds}s)`);

 setTimeout(() => {
 checkStatusUpdates();
 autoRefreshInterval = setInterval(() => {
 checkStatusUpdates();
 }, intervalSeconds * 1000);
 }, 2000);
}

// Stop auto-refresh
function stopAutoRefresh() {
 if (autoRefreshInterval) {
 clearInterval(autoRefreshInterval);
 autoRefreshInterval = null;
 console.log(' Auto-refresh stopped');
 }
}

// Mulai auto-refresh saat page load
startAutoRefresh(10);

// Stop auto-refresh saat page akan unload
window.addEventListener('beforeunload', function() {
 stopAutoRefresh();
});
</script>

<script>
(function () {
    'use strict';

    if (window.__KOM_TOAST_INTERCEPTOR__) return;
    window.__KOM_TOAST_INTERCEPTOR__ = true;

    function normalize(text) {
        if (text == null) return '';
        if (Array.isArray(text)) text = text.join(' ');
        return String(text).toLowerCase();
    }

    const KEYWORD_TO_ICON = [
        { icon: 'success', kw: ['berhasil', 'sukses', 'tersimpan', 'telah dikirim', 'terkirim', 'disimpan'] },
        { icon: 'error',   kw: ['gagal', 'error', 'tidak dapat', 'tidak bisa', 'ditolak', 'gagal disimpan'] },
        { icon: 'warning', kw: ['perlu', 'belum', 'lengkapi', 'perhatian', 'warning', 'kurang'] },
        { icon: 'info',    kw: ['info', 'informasi', 'catatan', 'pemberitahuan', 'sedang'] }
    ];

    function classify(text) {
        const t = normalize(text);
        if (!t) return null;
        for (const rule of KEYWORD_TO_ICON) {
            if (rule.kw.some(k => t.includes(k))) return rule.icon;
        }
        return null;
    }

    const COLOR_MAP = {
        success: '#16a34a',
        error:   '#dc2626',
        warning: '#d97706',
        info:    '#2563eb',
        question:'#8b5cf6'
    };

    function patchToastConfig(cfg) {
        if (!cfg || typeof cfg !== 'object') return cfg;
        if (cfg.toast !== true) return cfg;
        const text = normalize(cfg.title) + ' ' + normalize(cfg.html) + ' ' + normalize(cfg.text);
        const detected = classify(text);
        if (detected && !cfg.icon) cfg.icon = detected;
        if (cfg.icon && COLOR_MAP[cfg.icon] && !cfg.background) {
            cfg.background = '#ffffff';
        }
        return cfg;
    }

    const origFire = window.Swal && window.Swal.fire;
    if (origFire) {
        window.Swal.fire = function (...args) {
            if (args.length === 1 && typeof args[0] === 'object') {
                args[0] = patchToastConfig(Object.assign({}, args[0]));
            }
            return origFire.apply(this, args);
        };
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('button, a');
        if (!btn) return;
        const onclick = btn.getAttribute('onclick') || '';
        if (!/Swal\.fire|\.fire\(|showToast|notifToast/i.test(onclick)) return;
        if (/confirm|question|hapus|delete|logout|keluar|reset|batal/i.test(onclick)) return;
    }, true);
})();
</script>

<style>
/* === TOAST WARNA BERDASARKAN TIPE === */
.swal2-popup.swal2-toast { border-left: 4px solid transparent !important; }
.swal2-popup.swal2-toast.swal2-icon-success { border-left-color: #16a34a !important; }
.swal2-popup.swal2-toast.swal2-icon-error   { border-left-color: #dc2626 !important; }
.swal2-popup.swal2-toast.swal2-icon-warning { border-left-color: #d97706 !important; }
.swal2-popup.swal2-toast.swal2-icon-info    { border-left-color: #2563eb !important; }
.swal2-popup.swal2-toast .swal2-icon {
    margin: 0 !important;
    width: 28px !important;
    height: 28px !important;
    border-width: 3px !important;
}

/* === NUCLEAR OVERRIDE: TOAST SUCCESS = HIJAU === */
.swal2-popup.swal2-toast.swal2-icon-success,
.swal2-popup.swal2-toast.swal2-success {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%) !important;
    color: #14532d !important;
    border-left: 5px solid #16a34a !important;
    box-shadow: 0 10px 25px rgba(22, 163, 74, 0.25) !important;
}
.swal2-popup.swal2-toast.swal2-icon-success .swal2-title,
.swal2-popup.swal2-toast.swal2-icon-success .swal2-html-container,
.swal2-popup.swal2-toast.swal2-icon-success .swal2-content,
.swal2-popup.swal2-toast.swal2-icon-success p,
.swal2-popup.swal2-toast.swal2-icon-success span,
.swal2-popup.swal2-toast.swal2-icon-success div {
    color: #14532d !important;
}
.swal2-popup.swal2-toast.swal2-icon-success .swal2-icon {
    color: #16a34a !important;
    border-color: #16a34a !important;
}
.swal2-popup.swal2-toast.swal2-icon-success .swal2-timer-progress-bar {
    background: #16a34a !important;
}

/* === NUCLEAR OVERRIDE: TOAST ERROR = MERAH === */
.swal2-popup.swal2-toast.swal2-icon-error,
.swal2-popup.swal2-toast.swal2-error {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%) !important;
    color: #7f1d1d !important;
    border-left: 5px solid #dc2626 !important;
    box-shadow: 0 10px 25px rgba(220, 38, 38, 0.25) !important;
}
.swal2-popup.swal2-toast.swal2-icon-error .swal2-title,
.swal2-popup.swal2-toast.swal2-icon-error .swal2-html-container,
.swal2-popup.swal2-toast.swal2-icon-error p,
.swal2-popup.swal2-toast.swal2-icon-error span,
.swal2-popup.swal2-toast.swal2-icon-error div {
    color: #7f1d1d !important;
}
.swal2-popup.swal2-toast.swal2-icon-error .swal2-icon {
    color: #dc2626 !important;
    border-color: #dc2626 !important;
}
.swal2-popup.swal2-toast.swal2-icon-error .swal2-timer-progress-bar {
    background: #dc2626 !important;
}

/* === NUCLEAR OVERRIDE: TOAST WARNING = KUNING/ORANGE === */
.swal2-popup.swal2-toast.swal2-icon-warning,
.swal2-popup.swal2-toast.swal2-warning {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%) !important;
    color: #78350f !important;
    border-left: 5px solid #d97706 !important;
    box-shadow: 0 10px 25px rgba(217, 119, 6, 0.25) !important;
}
.swal2-popup.swal2-toast.swal2-icon-warning .swal2-title,
.swal2-popup.swal2-toast.swal2-icon-warning .swal2-html-container,
.swal2-popup.swal2-toast.swal2-icon-warning p,
.swal2-popup.swal2-toast.swal2-icon-warning span,
.swal2-popup.swal2-toast.swal2-icon-warning div {
    color: #78350f !important;
}
.swal2-popup.swal2-toast.swal2-icon-warning .swal2-icon {
    color: #d97706 !important;
    border-color: #d97706 !important;
}
.swal2-popup.swal2-toast.swal2-icon-warning .swal2-timer-progress-bar {
    background: #d97706 !important;
}

/* === NUCLEAR OVERRIDE: TOAST INFO = BIRU === */
.swal2-popup.swal2-toast.swal2-icon-info,
.swal2-popup.swal2-toast.swal2-info {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%) !important;
    color: #1e3a8a !important;
    border-left: 5px solid #2563eb !important;
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.25) !important;
}
.swal2-popup.swal2-toast.swal2-icon-info .swal2-title,
.swal2-popup.swal2-toast.swal2-icon-info .swal2-html-container,
.swal2-popup.swal2-toast.swal2-icon-info p,
.swal2-popup.swal2-toast.swal2-icon-info span,
.swal2-popup.swal2-toast.swal2-icon-info div {
    color: #1e3a8a !important;
}
.swal2-popup.swal2-toast.swal2-icon-info .swal2-icon {
    color: #2563eb !important;
    border-color: #2563eb !important;
}
.swal2-popup.swal2-toast.swal2-icon-info .swal2-timer-progress-bar {
    background: #2563eb !important;
}
</style>
@endsection
