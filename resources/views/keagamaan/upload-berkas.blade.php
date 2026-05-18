@extends('layouts.keagamaan')

@section('title', 'Upload Berkas Pernikahan')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Page Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Upload Berkas Pernikahan</h1>
                <p class="text-gray-500 text-sm">Kelola berkas persyaratan pernikahan</p>
            </div>
            <a href="{{ route('keagamaan.pernikahan.index') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="px-6 pb-6">
        @if($uploadBerkas->isNotEmpty())
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($uploadBerkas as $item)
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    {{-- Header --}}
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="font-mono text-sm font-bold text-blue-600">{{ $item->nomor_antrian }}</p>
                            <p class="text-sm text-gray-800">{{ $item->nama_mempelai_pria }} {{ $item->nama_mempelai_wanita ? '& ' . $item->nama_mempelai_wanita : '' }}</p>
                        </div>
                        @if($item->isDokumenLengkap())
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            <i class="fas fa-check mr-1"></i>Berkas Lengkap
                        </span>
                        @else
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                            <i class="fas fa-clock mr-1"></i>Belum Lengkap
                        </span>
                        @endif
                    </div>

                    {{-- Body --}}
                    <div class="p-4">
                        <div class="space-y-3">
                            {{-- List Dokumen --}}
                            <div class="space-y-2">
                                <p class="text-sm font-medium text-gray-700 mb-2">Dokumen yang diupload:</p>

                                @foreach($item->dokumen as $doc)
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <i class="fas @if($doc->isPdf()) fa-file-pdf text-red-500 @elseif($doc->isImage()) fa-file-image text-blue-500 @else fa-file text-gray-500 @endif"></i>
                                        <span class="text-sm text-gray-700">{{ $doc->jenis_dokumen_label }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($doc->status === \App\Models\DokumenPernikahan::STATUS_DIVERIFIKASI)
                                        <span class="text-xs text-green-600"><i class="fas fa-check-circle"></i></span>
                                        @elseif($doc->status === \App\Models\DokumenPernikahan::STATUS_DITOLAK)
                                        <span class="text-xs text-red-600"><i class="fas fa-times-circle"></i></span>
                                        @endif
                                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                           class="text-xs text-blue-600 hover:text-blue-700">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                @endforeach

                                @if($item->dokumen->isEmpty())
                                <p class="text-sm text-gray-500 italic">Belum ada dokumen diupload</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="p-4 bg-gray-50 border-t border-gray-100 flex gap-3">
                        @if(!$item->isDokumenLengkap())
                        <button onclick="openUploadModal('{{ $item->pernikahan_id }}')"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">
                            <i class="fas fa-upload mr-2"></i>Upload Berkas
                        </button>
                        @else
                        <button onclick="printBerkas('{{ $item->pernikahan_id }}')"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">
                            <i class="fas fa-print mr-2"></i>Print Berkas
                        </button>
                        @endif
                        <a href="{{ route('keagamaan.pernikahan.show', $item->pernikahan_id) }}"
                           class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-300">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
                <i class="fas fa-folder-open text-6xl mb-4 text-gray-300 block mx-auto"></i>
                <p class="text-lg font-medium text-gray-800 mb-2">Belum Ada Berkas</p>
                <p class="text-gray-500">Berkas persyaratan akan muncul setelah request tanggal disetujui</p>
                <a href="{{ route('keagamaan.pernikahan.index') }}" data-style-guide-skip
                   class="inline-flex items-center justify-center gap-2 mt-6 px-6 py-2 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-colors shadow-sm">
                    <i class="fas fa-calendar-check mr-2"></i>Lihat Permintaan Nikah
                </a>
            </div>
        @endif
    </div>
</div>

{{-- Upload Modal --}}
<div id="uploadModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white">
            <h3 class="font-semibold text-gray-800">Upload Berkas</h3>
            <button onclick="closeUploadModal()" class="p-2 hover:bg-gray-100 rounded-lg transition">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pernikahan_id" id="uploadPernikahanId">

                <div class="space-y-4">
                    {{-- Surat Keterangan Agama --}}
                    <div class="border border-gray-200 rounded-xl p-4">
                        <label class="block">
                            <p class="font-medium text-gray-800 mb-2">Surat Keterangan Perkawinan Agama</p>
                            <p class="text-xs text-gray-500 mb-2">Dari Pimpinan Gereja</p>
                            <input type="file" name="surat_keterangan_agama" accept=".pdf"
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </label>
                    </div>

                    {{-- KTP Mempelai --}}
                    <div class="border border-gray-200 rounded-xl p-4">
                        <label class="block">
                            <p class="font-medium text-gray-800 mb-2">KTP-el Kedua Mempelai</p>
                            <p class="text-xs text-gray-500 mb-2">Asli dan Fotokopi</p>
                            <input type="file" name="ktp_mempelai" accept=".pdf,image/*" multiple
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </label>
                    </div>

                    {{-- KK Orang Tua --}}
                    <div class="border border-gray-200 rounded-xl p-4">
                        <label class="block">
                            <p class="font-medium text-gray-800 mb-2">Kartu Keluarga Orang Tua</p>
                            <p class="text-xs text-gray-500 mb-2">Asli dari kedua orang tua mempelai</p>
                            <input type="file" name="kk_orang_tua" accept=".pdf,image/*" multiple
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </label>
                    </div>

                    {{-- KTP Saksi --}}
                    <div class="border border-gray-200 rounded-xl p-4">
                        <label class="block">
                            <p class="font-medium text-gray-800 mb-2">KTP-el Saksi Perkawinan</p>
                            <p class="text-xs text-gray-500 mb-2">Fotokopi 2 orang saksi</p>
                            <input type="file" name="ktp_saksi" accept=".pdf,image/*" multiple
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </label>
                    </div>

                    {{-- Akta Kematian (Opsional) --}}
                    <div class="border border-gray-200 rounded-xl p-4">
                        <label class="block">
                            <p class="font-medium text-gray-800 mb-2">Akta Kematian Pasangan <span class="text-gray-400">(Opsional)</span></p>
                            <p class="text-xs text-gray-500 mb-2">Jika janda/duda karena cerai mati</p>
                            <input type="file" name="akta_kematian" accept=".pdf,image/*"
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </label>
                    </div>

                    {{-- Akta Perceraian (Opsional) --}}
                    <div class="border border-gray-200 rounded-xl p-4">
                        <label class="block">
                            <p class="font-medium text-gray-800 mb-2">Akta Perceraian <span class="text-gray-400">(Opsional)</span></p>
                            <p class="text-xs text-gray-500 mb-2">Jika janda/duda karena cerai hidup</p>
                            <input type="file" name="akta_perceraian" accept=".pdf,image/*"
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="closeUploadModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700">
                        <i class="fas fa-upload mr-2"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openUploadModal(pernikahanId) {
    document.getElementById('uploadPernikahanId').value = pernikahanId;
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
}

function printBerkas(pernikahanId) {
    window.open('/keagamaan/pernikahan/print-berkas/' + pernikahanId, '_blank');
}

// Form submit
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    SwalHelper.loading('Mengupload berkas...');

    fetch(`{{ route('keagamaan.pernikahan.upload-berkas-post') }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        SwalHelper.close();
        if (data.success) {
            SwalHelper.success(data.message);
            closeUploadModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            SwalHelper.error(data.message || 'Gagal mengupload berkas');
        }
    })
    .catch(error => {
        SwalHelper.close();
        SwalHelper.error('Terjadi kesalahan saat mengupload berkas');
    });
});

// Close modal on outside click
document.getElementById('uploadModal').addEventListener('click', function(e) {
    if (e.target === this) closeUploadModal();
});
</script>
@endsection
