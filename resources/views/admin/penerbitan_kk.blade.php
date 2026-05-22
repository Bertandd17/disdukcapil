@extends('layouts.admin')

@section('content')
<div class="container-fluid p-6 bg-gray-50 min-h-screen">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Kelola Penerbitan Kartu Keluarga</h1>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-3xl font-bold text-blue-700">{{ $jumlahkk }}</h3>
            <p class="text-gray-500 text-sm">Total Permohonan</p>
        </div>
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-3xl font-bold text-blue-700">{{ $menungguVerifikasi }}</h3>
            <p class="text-gray-500 text-sm">Menunggu Verifikasi</p>
        </div>
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-3xl font-bold text-blue-700">{{ $dalamProses }}</h3>
            <p class="text-gray-500 text-sm">Dalam Proses</p>
        </div>
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-3xl font-bold text-blue-700">{{ $selesai }}</h3>
            <p class="text-gray-500 text-sm">Selesai</p>
        </div>
    </div>
    <form method="GET" class="mb-6">
        <div class="bg-white p-4 rounded-xl border flex gap-3 items-center">
            <span class="font-semibold text-gray-700">Filter:</span>
            <select name="status" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                <option value="Verifikasi Data">Verifikasi Data</option>
                <option value="Proses Cetak">Proses Cetak</option>
                <option value="Siap Pengambilan">Siap Pengambilan</option>
            </select>
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition">
                Terapkan
            </button>
        </div>
    </form>
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-blue-700 text-white">
                <tr>
                    <th class="p-4 text-left">No</th>
                    <th class="p-4 text-left">Nama</th>
                    <th class="p-4 text-left">Jenis</th>
                    <th class="p-4 text-left">Alamat</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($datakk as $data)
                <tr class="hover:bg-gray-50">
                    <td class="p-4">{{ $loop->iteration }}</td>
                    <td class="p-4 font-semibold">{{ $data->nama_pemohon }}</td>
                    <td class="p-4">{{ $data->jenis }}</td>
                    <td class="p-4 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                            @if($data->status == 'Dokumen Diterima') bg-gray-100 text-gray-700
                            @elseif($data->status == 'Verifikasi Data') bg-blue-100 text-blue-700
                            @elseif($data->status == 'Proses Cetak') bg-yellow-100 text-yellow-700
                            @elseif($data->status == 'Siap Pengambilan') bg-green-100 text-green-700
                            @elseif($data->status == 'Selesai') bg-green-100 text-green-700
                            @elseif($data->status == 'Tolak') bg-red-100 text-red-700
                            @endif">
                            {{ $data->status }}
                        </span>
                    </td>
                    <td class="p-4 text-center">
                        <div class="flex flex-row gap-2 items-center justify-center flex-wrap">
                            <a href="{{ route('admin.detail', ['uuid' => $data->uuid, 'jenis' => $data->jenis]) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all shadow-sm font-semibold text-sm h-[44px]">
                                <i class="fas fa-expand-alt text-xs"></i> <span>Detail</span>
                            </a>
                            @if($data->status == 'Proses Cetak')
                                <button type="button"
                                    onclick='openUploadModal("{{ $data->uuid }}", {!! json_encode($data->jenis) !!}, {!! json_encode($data->nama_pemohon) !!})'
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                    <i class="fas fa-upload"></i> Upload Berkas
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Upload Berkas Modal --}}
<div id="uploadBerkasModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <form id="uploadBerkasForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-slate-800 text-base">
                    <i class="fas fa-upload text-emerald-600 mr-2"></i>
                    Upload Berkas â€” Kartu Keluarga <span id="jenisKKLabel" class="text-emerald-700"></span>
                </h3>
                <button type="button" onclick="closeUploadModal()" class="text-slate-400 hover:text-red-500 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-700">
                    <i class="fas fa-info-circle mr-1"></i>
                    Berkas yang diunggah akan tersedia bagi pemohon di halaman <strong>Lacak Berkas</strong>.
                </div>
                <p class="text-sm text-slate-600">Pemohon: <strong id="namaPemohonModal" class="text-slate-900">-</strong></p>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">File Berkas <span class="text-red-500">*</span></span>
                    <input type="file" name="file_berkas" required accept="application/pdf,.pdf"
                        class="mt-2 block w-full text-sm border border-slate-300 rounded-lg p-2 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <span class="text-xs text-slate-500 mt-1 block">Format: PDF â€¢ Maksimal 5 MB</span>
                </label>
            </div>
            <div class="p-4 bg-slate-50 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeUploadModal()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-sm font-semibold transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                    <i class="fas fa-upload mr-1"></i> Upload
                </button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
function openUploadModal(uuid, jenis, nama) {
    const form = document.getElementById('uploadBerkasForm');
    form.action = "{{ url('admin/penerbitan-kk') }}/" + uuid + "/" + encodeURIComponent(jenis) + "/upload-berkas";
    document.getElementById('namaPemohonModal').textContent = nama || '-';
    document.getElementById('jenisKKLabel').textContent = '(' + jenis + ')';
    const modal = document.getElementById('uploadBerkasModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeUploadModal() {
    const modal = document.getElementById('uploadBerkasModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('uploadBerkasForm').reset();
}
document.getElementById('uploadBerkasModal').addEventListener('click', function(e){
    if (e.target === this) closeUploadModal();
});
@if(session('success'))
    SwalHelper.success("{{ session('success') }}");
@endif
@if(session('upload_error'))
    SwalHelper.error("{{ session('upload_error') }}");
@endif
@if($errors->any())
    SwalHelper.error("{{ $errors->first() }}");
@endif
</script>
@endpush
@endsection
