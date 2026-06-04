@extends('layouts.keagamaan')

@section('title', 'Dashboard Keagamaan')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Welcome Banner --}}
    <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-2xl p-6 md:p-8 text-white mb-6 reveal shadow-lg">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold mb-2">Selamat Datang, {{ auth()->user()->name }}!</h2>
                <p class="text-blue-100 text-lg">Dashboard Petugas Keagamaan</p>
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
        // Ambil keagamaan milik user yang sedang login
        $keagamaanUser = \Illuminate\Support\Facades\DB::table('keagamaan')
            ->where('user_id', auth()->id())
            ->first();

        $keagamaanId = $keagamaanUser?->keagamaan_id;

        $statistics = [
            'total' => \App\Models\LayananPernikahan::where('keagamaan_id', $keagamaanId)->count(),
            'pending' => \App\Models\LayananPernikahan::where('keagamaan_id', $keagamaanId)
                ->menungguKonfirmasiKeagamaan()->count(),
            'proses' => \App\Models\LayananPernikahan::where('keagamaan_id', $keagamaanId)
                ->whereIn('status', [
                    \App\Models\LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
                    \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                    \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
                ])->count(),
            'selesai' => \App\Models\LayananPernikahan::where('keagamaan_id', $keagamaanId)
                ->where('status', \App\Models\LayananPernikahan::STATUS_SELESAI)->count(),
        ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-ring text-xl text-blue-600"></i>
                </div>
                <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Total</span>
            </div>
            <h3 class="text-3xl font-extrabold text-gray-800 mb-1">{{ $statistics['total'] }}</h3>
            <p class="text-sm text-gray-600 font-medium">Total Permohonan</p>
        </div>

        <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-xl text-yellow-600"></i>
                </div>
                <span class="text-xs font-medium text-yellow-600 bg-yellow-50 px-2 py-1 rounded-full">Pending</span>
            </div>
            <h3 class="text-3xl font-extrabold text-gray-800 mb-1">{{ $statistics['pending'] }}</h3>
            <p class="text-sm text-gray-600 font-medium">Menunggu Konfirmasi</p>
        </div>

        <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-spinner text-xl text-blue-600"></i>
                </div>
                <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Proses</span>
            </div>
            <h3 class="text-3xl font-extrabold text-gray-800 mb-1">{{ $statistics['proses'] }}</h3>
            <p class="text-sm text-gray-600 font-medium">Dalam Proses</p>
        </div>

        <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-xl text-green-600"></i>
                </div>
                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">Selesai</span>
            </div>
            <h3 class="text-3xl font-extrabold text-gray-800 mb-1">{{ $statistics['selesai'] }}</h3>
            <p class="text-sm text-gray-600 font-medium">Sudah Selesai</p>
        </div>
    </div>

    {{-- Quick Actions & Recent Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">Aksi Cepat</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4">
                    <a href="{{ route('keagamaan.pernikahan.index') }}" data-style-guide-skip class="flex items-center gap-4 p-4 rounded-xl border border-blue-100 bg-blue-50 text-blue-700 hover:border-blue-500 hover:bg-blue-100 transition group">
                        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center transition">
                            <i class="fas fa-calendar-check text-white transition"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-blue-800">Permintaan Nikah</p>
                            <p class="text-sm text-blue-600">Kelola dan konfirmasi permohonan</p>
                        </div>
                        <i class="fas fa-chevron-right text-blue-600 transition"></i>
                    </a>

                    <a href="{{ route('keagamaan.pernikahan.request-tanggal') }}" data-style-guide-skip class="flex items-center gap-4 p-4 rounded-xl border border-blue-100 bg-blue-50 text-blue-700 hover:border-blue-500 hover:bg-blue-100 transition group">
                        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center transition">
                            <i class="fas fa-paper-plane text-white transition"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-blue-800">Request Tanggal</p>
                            <p class="text-sm text-blue-600">Kirim request ke Disdukcapil</p>
                        </div>
                        <i class="fas fa-chevron-right text-blue-600 transition"></i>
                    </a>

                    <a href="{{ route('keagamaan.pernikahan.upload-berkas') }}" data-style-guide-skip class="flex items-center gap-4 p-4 rounded-xl border border-blue-100 bg-blue-50 text-blue-700 hover:border-blue-500 hover:bg-blue-100 transition group">
                        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center transition">
                            <i class="fas fa-file-upload text-white transition"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-blue-800">Upload Berkas</p>
                            <p class="text-sm text-blue-600">Upload berkas persyaratan</p>
                        </div>
                        <i class="fas fa-chevron-right text-blue-600 transition"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Aktivitas Terbaru</h3>
                <a href="{{ route('keagamaan.pernikahan.index') }}" class="text-sm text-blue-600 hover:text-blue-700">Lihat Semua</a>
            </div>
            <div class="p-6">
                @php
                    // $keagamaanId sudah didefinisikan di @php block atas, langsung dipakai
                    $recentPernikahan = \App\Models\LayananPernikahan::with(['dokumen'])
                        ->where('keagamaan_id', $keagamaanId)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp

                @if($recentPernikahan->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($recentPernikahan as $item)
                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                @if($item->status === \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI) bg-green-100
                                @elseif($item->status === \App\Models\LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN) bg-red-100
                                @elseif($item->status === \App\Models\LayananPernikahan::STATUS_SELESAI) bg-blue-100
                                @else bg-yellow-100 @endif">
                                <i class="fas
                                    @if($item->status === \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI) fa-check text-green-600
                                    @elseif($item->status === \App\Models\LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN) fa-times text-red-600
                                    @elseif($item->status === \App\Models\LayananPernikahan::STATUS_SELESAI) fa-flag-checkered text-blue-600
                                    @else fa-clock text-yellow-600 @endif text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800 text-sm">{{ $item->nama_mempelai_pria }}</p>
                                <p class="text-xs text-gray-500">{{ $item->nomor_antrian }}</p>
                                <p class="text-xs text-gray-400">{{ $item->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full
                                @if($item->status === \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI) bg-green-100 text-green-700
                                @elseif($item->status === \App\Models\LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN) bg-red-100 text-red-700
                                @elseif($item->status === \App\Models\LayananPernikahan::STATUS_SELESAI) bg-blue-100 text-blue-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                @if($item->status === \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI) Disetujui
                                @elseif($item->status === \App\Models\LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN) Ditolak
                                @elseif($item->status === \App\Models\LayananPernikahan::STATUS_SELESAI) Selesai
                                @else Pending @endif
                            </span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                        <p>Belum ada aktivitas</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Update time every second
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
});
</script>
@endsection