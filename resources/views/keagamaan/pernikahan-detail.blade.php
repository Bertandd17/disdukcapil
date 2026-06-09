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
                <p class="text-gray-500 text-sm">
                    Nomor Antrian: <span class="font-mono font-semibold text-blue-600">{{ $pernikahan->nomor_antrian }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                @switch($pernikahan->status)
                    @case(\App\Models\LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN)
                        <button onclick="konfirmasiJemaat(true)" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition-colors">
                            <i class="fas fa-check mr-2"></i>Konfirmasi
                        </button>
                        <button onclick="konfirmasiJemaat(false)" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-medium hover:bg-red-700 transition-colors">
                            <i class="fas fa-times mr-2"></i>Tolak
                        </button>
                        @break
                    @case(\App\Models\LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL)
                        <button onclick="setTanggal()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors">
                            <i class="fas fa-calendar mr-2"></i>Tetapkan Tanggal
                        </button>
                        @break
                @endswitch
            </div>
        </div>
    </div>

    {{-- Konten utama --}}
    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- Progress, Data Mempelai, Waktu & Tempat, Dokumen --}}
            </div>
            <div class="space-y-6">
                {{-- Status, Catatan, Riwayat --}}
            </div>
        </div>
    </div>
</div>

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
                <button type="button" onclick="closeKonfirmasiModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-xl font-medium hover:bg-gray-300">Batal</button>
                <button type="submit" id="konfirmasiSubmitBtn" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700">Konfirmasi</button>
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
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                <textarea name="catatan" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-xl" placeholder="Catatan tambahan..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeTanggalModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-xl font-medium hover:bg-gray-300">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- SweetAlert2 Loading Modal Tanpa Tombol --}}
<script>
function showLoading(message = 'Sedang memproses...') {
    Swal.fire({
        title: message,
        html: '<p class="text-gray-600 text-sm">Mohon tunggu sebentar.</p>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        showCancelButton: false,
        showDenyButton: false,
        didOpen: () => Swal.showLoading(),
        customClass: {
            popup: 'swal-loading-modal'
        }
    });
}

function konfirmasiJemaat(isAccepted) {
    const modal = document.getElementById('konfirmasiModal');
    const statusInput = document.getElementById('konfirmasiStatus');
    const submitBtn = document.getElementById('konfirmasiSubmitBtn');
    const tanggalContainer = document.getElementById('tanggalContainer');

    statusInput.value = isAccepted ? 'diterima' : 'ditolak';
    submitBtn.textContent = isAccepted ? 'Konfirmasi' : 'Tolak';
    submitBtn.classList.toggle('bg-green-600', isAccepted);
    submitBtn.classList.toggle('bg-red-600', !isAccepted);
    tanggalContainer.style.display = isAccepted ? 'block' : 'none';

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