@extends('layouts.admin')

@section('content')
<div class="container-fluid p-6 bg-gray-50 min-h-screen">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Kelola Penerbitan Lahir Mati</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-4xl font-bold text-blue-700">{{ $jumlah }}</h3>
            <p class="text-gray-500 text-sm mt-1">Total Permohonan</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-4xl font-bold text-blue-700">{{ $menungguVerifikasi }}</h3>
            <p class="text-gray-500 text-sm mt-1">Menunggu Verifikasi</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-4xl font-bold text-blue-700">{{ $dalamProses }}</h3>
            <p class="text-gray-500 text-sm mt-1">Dalam Proses</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-4xl font-bold text-blue-700">{{ $selesai }}</h3>
            <p class="text-gray-500 text-sm mt-1">Selesai</p>
        </div>
    </div>

    <form method="GET" action="">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex items-center gap-4">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span class="font-semibold text-gray-700">Filter:</span>
        </div>
        <select name="status" class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
            <option value="">Semua Status</option>
            <option value="Verifikasi Data" {{ request('status') == 'Verifikasi Data' ? 'selected' : '' }}>Verifikasi Data</option>
            <option value="Proses Cetak" {{ request('status') == 'Proses Cetak' ? 'selected' : '' }}>Proses Cetak</option>
            <option value="Siap Pengambilan" {{ request('status') == 'Siap Pengambilan' ? 'selected' : '' }}>Siap Pengambilan</option>
            <option value="Tolak" {{ request('status') == 'Tolak' ? 'selected' : '' }}>Ditolak</option>
        </select>
        <button class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm">Terapkan</button>
    </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-blue-700 text-white">
                    <th class="p-4 font-semibold uppercase text-xs">No</th>
                    <th class="p-4 font-semibold uppercase text-xs">Nomor Antrian</th>
                    <th class="p-4 font-semibold uppercase text-xs">Nama Pemohon</th>
                    <th class="p-4 font-semibold uppercase text-xs">NIK Pemohon</th>
                    <th class="p-4 font-semibold uppercase text-xs">Tgl Pengajuan</th>
                    <th class="p-4 font-semibold uppercase text-xs text-center">Status</th>
                    <th class="p-4 font-semibold uppercase text-xs text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($dataLahirMati as $data)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="p-4 text-sm text-gray-700">{{ $loop->iteration }}</td>
                    <td class="p-4 text-sm text-gray-700">{{ $data->nomor_antrian }}</td>
                    <td class="p-4 text-sm font-bold text-gray-800">{{ $data->nama_pemohon }}</td>
                    <td class="p-4 text-sm text-gray-700">{{ $data->nik_pemohon }}</td>
                    <td class="p-4 text-sm text-gray-700">{{ $data->created_at->format('d M Y') }}</td>
                    <td class="p-4 text-center">
                        @php
                            $statusColor = match($data->status) {
                                'Dokumen Diterima' => 'bg-orange-50 text-orange-600 border-orange-100',
                                'Verifikasi Data' => 'bg-blue-50 text-blue-600 border-blue-100',
                                'Proses Cetak' => 'bg-yellow-50 text-yellow-600 border-yellow-100',
                                'Siap Pengambilan' => 'bg-green-50 text-green-600 border-green-100',
                                'Selesai' => 'bg-green-50 text-green-600 border-green-100',
                                'Tolak' => 'bg-red-50 text-red-600 border-red-100',
                                default => 'bg-gray-50 text-gray-600 border-gray-100',
                            };
                        @endphp
                        <span class="{{ $statusColor }} px-3 py-1 rounded-full text-xs font-bold border">
                            {{ $data->status }}
                        </span>
                    </td>
                    <td class="p-4">
                        <div class="flex flex-row gap-2 items-center justify-center flex-wrap">
                            <a href="{{ route('admin.lahir-mati.detail', $data->uuid) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all shadow-sm font-semibold text-sm h-[44px]">
                                <i class="fas fa-expand-alt text-xs"></i> <span>Detail</span>
                            </a>
                            @if($data->status == 'Proses Cetak')
                                <button type="button"
                                    onclick='openUploadModal("{{ $data->uuid }}", {!! json_encode($data->nama_pemohon) !!})'
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-all h-[44px]">
                                    <i class="fas fa-upload"></i> Upload Berkas
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-400">
                        <div class="text-4xl mb-2"><i class="fas fa-clipboard-list"></i></div>
                        <p class="font-semibold">Belum ada permohonan lahir mati</p>
                    </td>
                </tr>
                @endforelse
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
                    Upload Berkas — Surat Keterangan Lahir Mati
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
                    <span class="text-xs text-slate-500 mt-1 block">Format: PDF - Maksimal 5 MB</span>
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
@endsection

@push('scripts')
<script>
function openUploadModal(uuid, nama) {
    const form = document.getElementById('uploadBerkasForm');
    form.action = "{{ url('admin/penerbitan-lahir-mati') }}/" + uuid + "/upload-berkas";
    document.getElementById('namaPemohonModal').textContent = nama || '-';
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