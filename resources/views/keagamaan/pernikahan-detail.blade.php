@extends('layouts.keagamaan')

@section('title', 'Detail Pernikahan')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Page Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('keagamaan.pernikahan.index') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-800">Detail Permohonan</h1>
                <p class="text-gray-500 text-sm">Nomor Antrian: <span class="font-mono font-semibold text-blue-600">{{ $pernikahan->nomor_antrian }}</span></p>
            </div>
            <div class="flex items-center gap-2">
                @switch($pernikahan->status)
                    @case(\App\Models\LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN)
                        <button onclick="konfirmasiJemaat(true)" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition-colors">
                            <i class="fas fa-check mr-2"></i>
                            Konfirmasi
                        </button>
                        <button onclick="konfirmasiJemaat(false)" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-medium hover:bg-red-700 transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Tolak
                        </button>
                        @break
                    @case(\App\Models\LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL)
                        <button onclick="setTanggal()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors">
                            <i class="fas fa-calendar mr-2"></i>
                            Tetapkan Tanggal
                        </button>
                        @break
                @endswitch
            </div>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Progress --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Status Progress</h2>
                    <div class="flex items-center justify-between">
                        @foreach([1 => 'Konfirmasi', 2 => 'Tanggal', 3 => 'Dokumen', 4 => 'Selesai'] as $step => $label)
                            <div class="flex items-center @if($loop->last) '' else 'flex-1' @end">
                                <div class="flex flex-col items-center @if($loop->last) '' else 'w-full' @end">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold
                                        @if($pernikahan->step >= $step) bg-gradient-to-br from-blue-600 to-blue-700 text-white
                                        @else bg-gray-200 text-gray-500 @endif">
                                        @if($pernikahan->step > $step)
                                            <i class="fas fa-check"></i>
                                        @else
                                            {{ $step }}
                                        @endif
                                    </div>
                                    <span class="text-xs mt-2 font-medium @if($pernikahan->step >= $step) text-blue-600 @else text-gray-500 @endif">{{ $label }}</span>
                                </div>
                                @if(!$loop->last)
                                    <div class="flex-1 h-1 mx-2 @if($pernikahan->step > $step) bg-blue-600 @else bg-gray-200 @endif top-5"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Data Mempelai --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Data Mempelai</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Mempelai Pria --}}
                        <div class="bg-blue-50 rounded-xl p-4">
                            <h3 class="font-medium text-blue-700 mb-3">Mempelai Pria</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex">
                                    <dt class="w-28 text-gray-500">Nama</dt>
                                    <dd class="font-medium text-gray-800">{{ $pernikahan->nama_mempelai_pria }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-28 text-gray-500">NIK</dt>
                                    <dd class="font-mono text-gray-800">{{ $pernikahan->nik_mempelai_pria }}</dd>
                                </div>
                                @if($pernikahan->tempat_lahir_mempelai_pria)
                                    <div class="flex">
                                        <dt class="w-28 text-gray-500">TTL</dt>
                                        <dd class="text-gray-800">{{ $pernikahan->tempat_lahir_mempelai_pria }}, {{ $pernikahan->tanggal_lahir_mempelai_pria?->format('d M Y') }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        {{-- Mempelai Wanita --}}
                        <div class="bg-pink-50 rounded-xl p-4">
                            <h3 class="font-medium text-pink-700 mb-3">Mempelai Wanita</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex">
                                    <dt class="w-28 text-gray-500">Nama</dt>
                                    <dd class="font-medium text-gray-800">{{ $pernikahan->nama_mempelai_wanita }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-28 text-gray-500">NIK</dt>
                                    <dd class="font-mono text-gray-800">{{ $pernikahan->nik_mempelai_wanita }}</dd>
                                </div>
                                @if($pernikahan->tempat_lahir_mempelai_wanita)
                                    <div class="flex">
                                        <dt class="w-28 text-gray-500">TTL</dt>
                                        <dd class="text-gray-800">{{ $pernikahan->tempat_lahir_mempelai_wanita }}, {{ $pernikahan->tanggal_lahir_mempelai_wanita?->format('d M Y') }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- Waktu & Tempat --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Waktu & Tempat</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">Tanggal</span>
                            <p class="font-medium text-gray-800">{{ $pernikahan->tanggal_perkawinan?->format('d F Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Gereja</span>
                            <p class="font-medium text-gray-800">{{ $pernikahan->nama_gereja }}</p>
                        </div>
                    </div>
                </div>

                {{-- Dokumen --}}
                @if($pernikahan->dokumen->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Dokumen</h2>
                    <div class="space-y-3">
                        @foreach($pernikahan->dokumen as $doc)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm">
                                        <i class="fas @if($doc->isImage()) fa-image @elseif($doc->isPdf()) fa-file-pdf @else fa-file @endif text-gray-500"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800 text-sm">{{ $doc->jenis_dokumen_label }}</p>
                                        <p class="text-xs text-gray-500">{{ $doc->original_filename }}</p>
                                    </div>
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    @if($doc->status === \App\Models\DokumenPernikahan::STATUS_UPLOADED) bg-yellow-100 text-yellow-700
                                    @elseif($doc->status === \App\Models\DokumenPernikahan::STATUS_DIVERIFIKASI) bg-green-100 text-green-700
                                    @else bg-red-100 text-red-700 @endif">
                                    {{ $doc->status_label }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Status Card --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Status</h3>
                    <div class="text-center py-4">
                        @switch($pernikahan->status)
                            @case(\App\Models\LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN)
                                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                                </div>
                                <p class="font-medium text-yellow-700">{{ $pernikahan->status_label }}</p>
                                @break
                            @case(\App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI)
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                                </div>
                                <p class="font-medium text-green-700">{{ $pernikahan->status_label }}</p>
                                @break
                            @default
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-file-alt text-gray-600 text-2xl"></i>
                                </div>
                                <p class="font-medium text-gray-700">{{ $pernikahan->status_label }}</p>
                        @endswitch
                    </div>
                </div>

                {{-- Catatan --}}
                @if($pernikahan->catatan_keagamaan || $pernikahan->catatan_admin || $pernikahan->alasan_ditolak)
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Catatan</h3>

                    @if($pernikahan->catatan_keagamaan)
                        <div class="mb-3">
                            <p class="text-xs text-gray-500 mb-1">Catatan Anda:</p>
                            <p class="text-sm text-gray-800">{{ $pernikahan->catatan_keagamaan }}</p>
                        </div>
                    @endif

                    @if($pernikahan->catatan_admin)
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Dari Admin:</p>
                            <p class="text-sm text-gray-800">{{ $pernikahan->catatan_admin }}</p>
                        </div>
                    @endif
                </div>
                @endif

                {{-- History --}}
                @if($pernikahan->history->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Riwayat</h3>
                    <div class="space-y-3">
                        @foreach($pernikahan->history->take(5) as $h)
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 mt-2 rounded-full bg-blue-500"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ \App\Models\LayananPernikahan::STATUS_TO_LABEL[$h->status_setelah] ?? $h->status_setelah }}</p>
                                <p class="text-xs text-gray-500">{{ $h->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
{{-- Konfirmasi Jemaat Modal --}}
<div id="konfirmasiModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="p-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Konfirmasi Jemaat</h3>
        </div>
        <form id="konfirmasiForm" method="POST" class="p-4">
            @csrf
            <input type="hidden" name="status" id="konfirmasiStatus" value="">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                <textarea name="catatan" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="Tambahkan catatan..."></textarea>
            </div>
            <div class="mb-4" id="tanggalContainer" style="display: none;">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Perkawinan</label>
                <input type="date" name="tanggal_perkawinan" value="{{ $pernikahan->tanggal_perkawinan?->format('Y-m-d') }}"
                       min="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl">
                <p class="text-xs text-gray-500 mt-1">Minimal 7 hari dari hari ini</p>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeKonfirmasiModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-300">Batal</button>
                <button type="submit" id="konfirmasiSubmitBtn" class="flex-1 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl font-medium hover:from-green-700 hover:to-green-800">Konfirmasi</button>
            </div>
        </form>
    </div>
</div>

{{-- Set Tanggal Modal --}}
<div id="tanggalModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="p-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Tetapkan Tanggal Pernikahan</h3>
        </div>
        <form id="tanggalForm" method="POST" action="{{ route('keagamaan.pernikahan.set-tanggal', $pernikahan->pernikahan_id) }}" class="p-4">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Perkawinan <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_perkawinan" required value="{{ $pernikahan->tanggal_perkawinan?->format('Y-m-d') }}"
                       min="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl">
                <p class="text-xs text-gray-500 mt-1">Minimal 7 hari dari hari ini</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                <textarea name="catatan" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-xl" placeholder="Catatan tambahan..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeTanggalModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-300">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function konfirmasiJemaat(isAccepted) {
    const modal = document.getElementById('konfirmasiModal');
    const statusInput = document.getElementById('konfirmasiStatus');
    const submitBtn = document.getElementById('konfirmasiSubmitBtn');
    const tanggalContainer = document.getElementById('tanggalContainer');

    statusInput.value = isAccepted ? 'diterima' : 'ditolak';

    if (isAccepted) {
        submitBtn.textContent = 'Konfirmasi';
        submitBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
        submitBtn.classList.add('bg-green-600', 'hover:bg-green-700');
        tanggalContainer.style.display = 'block';
    } else {
        submitBtn.textContent = 'Tolak';
        submitBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
        submitBtn.classList.add('bg-red-600', 'hover:bg-red-700');
        tanggalContainer.style.display = 'none';
    }

    // Set form action
    const form = document.getElementById('konfirmasiForm');
    form.action = '{{ route('keagamaan.pernikahan.konfirmasi-jemaat', $pernikahan->pernikahan_id) }}';

    modal.classList.remove('hidden');
}

function closeKonfirmasiModal() {
    document.getElementById('konfirmasiModal').classList.add('hidden');
}

function setTanggal() {
    document.getElementById('tanggalModal').classList.remove('hidden');
}

function closeTanggalModal() {
    document.getElementById('tanggalModal').classList.add('hidden');
}

// Close modals on outside click
document.getElementById('konfirmasiModal').addEventListener('click', function(e) {
    if (e.target === this) closeKonfirmasiModal();
});
document.getElementById('tanggalModal').addEventListener('click', function(e) {
    if (e.target === this) closeTanggalModal();
});
</script>
@endsection
