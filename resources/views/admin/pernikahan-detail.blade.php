@extends('layouts.admin')

@section('title', 'Detail Pernikahan')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Page Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.pernikahan.index') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-800">Detail Permohonan</h1>
                <p class="text-gray-500 text-sm">Nomor Antrian: <span class="font-mono font-semibold text-blue-600">{{ $pernikahan->nomor_antrian }}</span></p>
            </div>
            <div class="flex items-center gap-2">
                @switch($pernikahan->status)
                    @case(\App\Models\LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL)
                        <button onclick="approveTanggal()" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition-colors">
                            <i class="fas fa-check mr-2"></i>
                            Setujui Tanggal
                        </button>
                        <button onclick="rejectTanggal()" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-medium hover:bg-red-700 transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Tolak Tanggal
                        </button>
                        @break
                    @case(\App\Models\LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI)
                        <button onclick="verifikasiDokumen()" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition-colors">
                            <i class="fas fa-check-double mr-2"></i>
                            Verifikasi Dokumen
                        </button>
                        @break
                    @case(\App\Models\LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI)
                        <button onclick="uploadBerkas()" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition-colors">
                            <i class="fas fa-upload mr-2"></i>
                            Upload Berkas
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

                {{-- Data Pemohon & Mempelai --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Data Pemohon & Mempelai</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Pemohon --}}
                        <div class="bg-gray-50 rounded-xl p-4">
                            <h3 class="font-medium text-gray-700 mb-3">Pemohon</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex">
                                    <dt class="w-28 text-gray-500">Nama</dt>
                                    <dd class="font-medium text-gray-800">{{ $pernikahan->nama_pemohon }}</dd>
                                </div>
                                @if($pernikahan->nik_pemohon)
                                    <div class="flex">
                                        <dt class="w-28 text-gray-500">NIK</dt>
                                        <dd class="font-mono text-gray-800">{{ $pernikahan->nik_pemohon }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

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
                        <div class="md:col-span-2 bg-pink-50 rounded-xl p-4">
                            <h3 class="font-medium text-pink-700 mb-3">Mempelai Wanita</h3>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
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
                                @if($pernikahan->agama_mempelai_wanita)
                                    <div class="flex">
                                        <dt class="w-28 text-gray-500">Agama</dt>
                                        <dd class="text-gray-800">{{ $pernikahan->agama_mempelai_wanita }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- Waktu & Tempat --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Waktu & Tempat Pernikahan</h2>
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
                                        <p class="text-xs text-gray-500">{{ $doc->original_filename }} ({{ number_format($doc->file_size / 1024 / 1024, 2) }} MB)</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    @switch($doc->status)
                                        @case(\App\Models\DokumenPernikahan::STATUS_UPLOADED)
                                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Diupload</span>
                                            @break
                                        @case(\App\Models\DokumenPernikahan::STATUS_DIVERIFIKASI)
                                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Diverifikasi</span>
                                            @break
                                        @case(\App\Models\DokumenPernikahan::STATUS_DITOLAK)
                                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Ditolak</span>
                                            @break
                                    @endswitch
                                    @if($doc->catatan_verifikasi)
                                        <p class="text-xs text-red-600">{{ $doc->catatan_verifikasi }}</p>
                                    @endif
                                </div>
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
                                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-upload text-blue-600 text-2xl"></i>
                                </div>
                                <p class="font-medium text-blue-700">{{ $pernikahan->status_label }}</p>
                                @break
                            @case(\App\Models\LayananPernikahan::STATUS_SELESAI)
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

                    @if($pernikahan->alasan_ditolak)
                        <div class="p-3 bg-red-50 rounded-xl mb-3">
                            <p class="text-sm font-medium text-red-800 mb-1">Alasan Ditolak:</p>
                            <p class="text-sm text-red-700">{{ $pernikahan->alasan_ditolak }}</p>
                        </div>
                    @endif

                    @if($pernikahan->catatan_keagamaan)
                        <div class="mb-3">
                            <p class="text-xs text-gray-500 mb-1">Dari Keagamaan:</p>
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
{{-- Reject Tanggal Modal --}}
<div id="rejectModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="p-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Tolak Tanggal Pernikahan</h3>
        </div>
        <form id="rejectForm" method="POST" action="{{ route('admin.pernikahan.reject-tanggal', $pernikahan->pernikahan_id) }}" class="p-4">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="alasan" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="Jelaskan alasan penolakan..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeRejectModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-xl font-medium hover:bg-gray-300">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700">Tolak</button>
            </div>
        </form>
    </div>
</div>

{{-- Verifikasi Dokumen Modal --}}
<div id="verifikasiModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white">
            <h3 class="font-semibold text-gray-800">Verifikasi Dokumen</h3>
            <button onclick="closeVerifikasiModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="verifikasiForm" method="POST" action="{{ route('admin.pernikahan.verifikasi', $pernikahan->pernikahan_id) }}" class="p-4">
            @csrf
            <div class="space-y-4">
                @foreach($pernikahan->dokumen as $doc)
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm">
                                    <i class="fas @if($doc->isImage()) fa-image @elseif($doc->isPdf()) fa-file-pdf @else fa-file @endif text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 text-sm">{{ $doc->jenis_dokumen_label }}</p>
                                    <p class="text-xs text-gray-500">{{ $doc->original_filename }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 p-2 bg-white rounded-lg cursor-pointer border border-gray-200 hover:border-blue-500">
                                <input type="radio" name="status[{{ $loop->index }}]" value="DIVERIFIKASI" checked class="text-blue-600">
                                <span class="text-sm">Verifikasi</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 bg-white rounded-lg cursor-pointer border border-gray-200 hover:border-red-500">
                                <input type="radio" name="status[{{ $loop->index }}]" value="DITOLAK" class="text-red-600">
                                <span class="text-sm">Tolak</span>
                            </label>
                        </div>
                        <input type="hidden" name="dokumen_id[{{ $loop->index }}]" value="{{ $doc->id }}">
                        <textarea name="catatan[{{ $loop->index }}]" rows="2" placeholder="Catatan (opsional)" class="w-full mt-2 px-3 py-2 border border-gray-300 rounded-xl text-sm"></textarea>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeVerifikasiModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-xl font-medium hover:bg-gray-300">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Upload Berkas Modal --}}
<div id="berkasModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="p-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Upload Berkas</h3>
        </div>
        <form id="berkasForm" method="POST" action="{{ route('admin.pernikahan.upload-berkas', $pernikahan->pernikahan_id) }}" enctype="multipart/form-data" class="p-4">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Berkas Acara</label>
                    <input type="file" name="file_berkas_acara" accept=".pdf" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                    <p class="text-xs text-gray-500 mt-1">Format PDF, maks 5MB</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Surat Keterangan</label>
                    <input type="file" name="file_surat_keterangan" accept=".pdf" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                    <p class="text-xs text-gray-500 mt-1">Format PDF, maks 5MB</p>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeBerkasModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-xl font-medium hover:bg-gray-300">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700">Upload</button>
            </div>
        </form>
    </div>
</div>

<script>
function approveTanggal() {
    const onConfirm = function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.pernikahan.approve-tanggal', $pernikahan->pernikahan_id) }}';
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    };

    if (typeof notifKonfirmasi === 'function') {
        notifKonfirmasi('Setujui tanggal perkawinan ini?', onConfirm);
    } else if (typeof fireToast !== 'undefined') {
        fireToast({
            type: 'error', icon: 'error',
            title: 'Konfirmasi diperlukan',
            problem: 'Tindakan ini akan menyetujui tanggal perkawinan yang diajukan.',
            solution: 'Pastikan data pemohon sudah diverifikasi, lalu lakukan konfirmasi melalui dialog.'
        });
    }
}

function rejectTanggal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

function verifikasiDokumen() {
    document.getElementById('verifikasiModal').classList.remove('hidden');
}

function closeVerifikasiModal() {
    document.getElementById('verifikasiModal').classList.add('hidden');
}

function uploadBerkas() {
    document.getElementById('berkasModal').classList.remove('hidden');
}

function closeBerkasModal() {
    document.getElementById('berkasModal').classList.add('hidden');
}

// Close modals on outside click
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});
document.getElementById('verifikasiModal').addEventListener('click', function(e) {
    if (e.target === this) closeVerifikasiModal();
});
document.getElementById('berkasModal').addEventListener('click', function(e) {
    if (e.target === this) closeBerkasModal();
});
</script>
@endsection
