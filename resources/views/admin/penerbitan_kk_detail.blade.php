@extends('layouts.admin')

@section('content')
<div class="container-fluid p-6">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Detail Permohonan Kartu Keluarga
        </h1>
        <a href="{{ route('admin.penerbitan-kk') }}"
        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg text-sm">
            Kembali
        </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-xl shadow border">
            <h2 class="text-lg font-semibold mb-6 text-gray-700">
                Informasi Pemohon
            </h2>
            <div class="space-y-4 text-sm">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">Nama Pemohon</span>
                    <span class="font-semibold">{{ $berkas->nama_pemohon }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">Nomor Antrian</span>
                    <span class="font-semibold">{{ $berkas->nomor_antrian }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">Alamat</span>
                    <span class="font-semibold">{{ $berkas->alamat_pemohon }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">Jenis Layanan</span>
                    <span class="font-semibold">{{ $jenis }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">Tanggal Pengajuan</span>
                    <span class="font-semibold">
                        {{ $berkas->created_at->format('d M Y') }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    <span class="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-700 font-semibold">
                        {{ $berkas->status }}
                    </span>
                </div>
            </div>

            @php
                $alurStatus = ['Verifikasi Data', 'Proses Cetak', 'Siap Pengambilan'];
                $statusSekarang = ($berkas->status === 'Dokumen Diterima') ? 'Verifikasi Data' : $berkas->status;
                $idxSekarang = array_search($statusSekarang, $alurStatus);
                $statusBerikutnya = ($idxSekarang !== false && $idxSekarang < count($alurStatus) - 1)
                    ? $alurStatus[$idxSekarang + 1]
                    : null;
                $sudahFinal = in_array($statusSekarang, ['Siap Pengambilan', 'Tolak']);
            @endphp

            @if($berkas->status == 'Tolak' && $berkas->alasan_penolakan)
            <div class="mt-4 p-3 bg-red-100 rounded-lg text-sm text-red-800">
                <strong>Alasan Penolakan:</strong><br>
                {{ $berkas->alasan_penolakan }}
            </div>
            @endif

            @if($statusSekarang === 'Verifikasi Data')
            <div class="mt-6 pt-4 border-t grid grid-cols-2 gap-3">
                <button type="button" id="btnTolak"
                    class="w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center transition">
                    <i class="fas fa-times-circle mr-2"></i> Tolak
                </button>
                <button type="button" id="btnTerima"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center transition">
                    <i class="fas fa-check-circle mr-2"></i> Terima
                </button>
            </div>

            <form id="formUpdateStatus" action="{{ route('admin.status', [$berkas->uuid, $jenis]) }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="status" id="inputStatus">
                <input type="hidden" name="alasan" id="inputAlasan">
            </form>
            @elseif($berkas->status === 'Tolak')
            <div class="mt-6 pt-4 border-t">
                <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm font-semibold text-center">
                    <i class="fas fa-times-circle mr-2"></i> Berkas memiliki kekurangan dan sudah ditolak.
                </div>
            </div>
            @else
            <div class="mt-6 pt-4 border-t">
                <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm font-semibold text-center">
                    <i class="fas fa-check-circle mr-2"></i> Berkas sudah disetujui, dilanjut dengan proses cetak berkas.
                </div>
            </div>
            @endif
        </div>
        <div class="bg-white p-6 rounded-xl shadow border">
            <h2 class="text-lg font-semibold mb-6 text-gray-700">
                Dokumen Persyaratan
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($jenis == 'Perubahan Data')
                    @include('admin.partials.dokumen', [
                        'label' => 'Formulir F-1.02',
                        'field' => 'formulir_f102'
                    ])
                    @include('admin.partials.dokumen', [
                        'label' => 'KTP Pemohon',
                        'field' => 'ktp_pemohon'
                    ])
                    @include('admin.partials.dokumen', [
                        'label' => 'KK Pemohon',
                        'field' => 'kk_pemohon'
                    ])
                    @include('admin.partials.dokumen', [
                        'label' => 'Formulir F-1.06',
                        'field' => 'formulir_f106'
                    ])
                    @include('admin.partials.dokumen', [
                        'label' => 'Suket Perubahan',
                        'field' => 'surat_keterangan_perubahan'
                    ])

                    @if($berkas->pernyataan_pindah_kk)
                        @include('admin.partials.dokumen', [
                            'label' => 'Pernyataan Pindah KK',
                            'field' => 'pernyataan_pindah_kk'
                        ])
                    @endif
                @endif

                @if($jenis == 'Ganti Kepala')
                    @include('admin.partials.dokumen', [
                        'label' => 'Formulir F-1.02',
                        'field' => 'formulir_f102'
                    ])
                    @include('admin.partials.dokumen', [
                        'label' => 'KTP Pemohon',
                        'field' => 'ktp_pemohon'
                    ])
                    @include('admin.partials.dokumen', [
                        'label' => 'KK Pemohon',
                        'field' => 'kk_pemohon'
                    ])
                    @include('admin.partials.dokumen', [
                        'label' => 'Akta Kematian',
                        'field' => 'fotokopi_akta_kematian'
                    ])

                    @if($berkas->surat_pernyataan_wali)
                        @include('admin.partials.dokumen', [
                            'label' => 'Surat Pernyataan Wali',
                            'field' => 'surat_pernyataan_wali'
                        ])
                    @endif
                @endif

                @if($jenis == 'Hilang Rusak')
                    @include('admin.partials.dokumen', [
                        'label' => 'Formulir F-1.02',
                        'field' => 'formulir_f102'
                    ])
                    @include('admin.partials.dokumen', [
                        'label' => 'KTP Pemohon',
                        'field' => 'ktp_pemohon'
                    ])
                    @include('admin.partials.dokumen', [
                        'label' => 'Surat Kehilangan/Rusak',
                        'field' => 'suket_hilang_rusak'
                    ])
                @endif

                @if($jenis == 'Pisah KK')
                    @include('admin.partials.dokumen', [
                        'label' => 'Formulir F-1.02',
                        'field' => 'formulir_f102'
                    ])
                    @include('admin.partials.dokumen', [
                        'label' => 'KTP Pemohon',
                        'field' => 'ktp_pemohon'
                    ])
                    @include('admin.partials.dokumen', [
                        'label' => 'KK Pemohon',
                        'field' => 'kk_pemohon'
                    ])
                    @include('admin.partials.dokumen', [
                        'label' => 'Buku Nikah',
                        'field' => 'fotokopi_buku_nikah'
                    ])
                    @include('admin.partials.dokumen', [
                        'label' => 'KK Lama',
                        'field' => 'kk_lama'
                    ])
                @endif
            </div>
            @if($berkas->foto_wajah)
            <div class="bg-white p-6 rounded-xl shadow border mt-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-700">
                    Foto Verifikasi Wajah
                </h2>
                <div class="flex items-center gap-6">
                    <img 
                        src="{{ route('admin.lihat-berkas', [$berkas->uuid, $jenis, 'foto_wajah']) }}"
                        alt="Foto Wajah Pemohon"
                        class="w-40 h-40 rounded-xl object-cover border-2 border-gray-200 shadow"
                    >
                    <div class="text-sm text-gray-500">
                        <p class="font-semibold text-gray-700 mb-1">Foto Verifikasi Liveness</p>
                        <p class="text-xs text-gray-400 mt-1">Diambil otomatis saat kedipan mata ke-2 terdeteksi.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const formStatus    = document.getElementById('formUpdateStatus');
    const inputStatus   = document.getElementById('inputStatus');
    const inputAlasan   = document.getElementById('inputAlasan');
    const btnTerima     = document.getElementById('btnTerima');
    const btnTolak      = document.getElementById('btnTolak');
    const statusBerikut = @json($statusBerikutnya ?? null);

    if (btnTerima) {
        btnTerima.addEventListener('click', function () {
            if (!statusBerikut) {
                Swal.fire({ icon: 'info', title: 'Tidak ada langkah berikutnya', text: 'Permohonan ini sudah berada di tahap akhir.', confirmButtonColor: '#2563eb' });
                return;
            }
            window.SwalHelper.konfirmasiDisdukcapil({
                judul: 'Konfirmasi Penerimaan',
                pesan: 'Lanjutkan permohonan ke tahap <strong>' + statusBerikut + '</strong>?',
                tipe: 'konfirmasi',
                labelOk: 'Konfirmasi',
                onKonfirmasi: function () {
                    inputStatus.value = statusBerikut;
                    inputAlasan.value = '';
                    formStatus.submit();
                }
            });
        });
    }

    if (btnTolak) {
        btnTolak.addEventListener('click', function () {
            Swal.fire({
                icon: false,
                title: 'Tolak Permohonan',
                html: 'Masukkan <strong>alasan penolakan</strong>. Alasan ini akan ditampilkan pada halaman lacak berkas pengguna.',
                input: 'textarea',
                inputPlaceholder: 'Tulis alasan penolakan di sini...',
                inputAttributes: { 'aria-label': 'Alasan penolakan', 'maxlength': '500' },
                showCancelButton: true,
                confirmButtonText: 'Konfirmasi',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#e5e7eb',
                reverseButtons: true,
                inputValidator: (value) => {
                    if (!value || value.trim().length < 5) {
                        return 'Alasan penolakan wajib diisi (minimal 5 karakter).';
                    }
                }
            }).then((res) => {
                if (res.isConfirmed) {
                    inputStatus.value = 'Tolak';
                    inputAlasan.value = res.value.trim();
                    formStatus.submit();
                }
            });
        });
    }
});
</script>
@endpush