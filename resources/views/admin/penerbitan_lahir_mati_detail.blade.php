@extends('layouts.admin')
@section('content')
<div class="container-fluid p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Detail Permohonan Lahir Mati
        </h1>
        <a href="{{ route('admin.penerbitan-lahir-mati') }}"
        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg text-sm">
            Kembali
        </a>
    </div>
    
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700 font-semibold">
                    Semua rincian jenazah bayi, orang tua, dan saksi tersedia secara lengkap pada dokumen "Formulir F-2.01" yang diunggah oleh pemohon.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Kolom Kiri: Informasi Pemohon & Status --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- Informasi Pemohon --}}
            <div class="bg-white p-6 rounded-xl shadow border">
                <h2 class="text-lg font-semibold mb-6 text-gray-700">
                    Informasi Pemohon
                </h2>
                <div class="space-y-4 text-sm">
                    <div class="border-b pb-2">
                        <p class="text-gray-500 text-xs mb-1">Nama Pemohon</p>
                        <p class="font-semibold text-base text-gray-800">{{ $berkas->nama_pemohon }}</p>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">NIK Pemohon</span>
                        <span class="font-semibold">{{ $berkas->nik_pemohon }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">No. KK Pemohon</span>
                        <span class="font-semibold">{{ $berkas->nomor_kk_pemohon ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Hubungan dg Bayi</span>
                        <span class="font-semibold">{{ $berkas->hubungan_pemohon }}</span>
                    </div>
                    <div class="border-b pb-2">
                        <p class="text-gray-500 text-xs mb-1">Alamat Pemohon</p>
                        <p class="font-semibold">{{ $berkas->alamat_pemohon }}</p>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">No. Antrian</span>
                        <span class="font-semibold text-blue-600">{{ $berkas->nomor_antrian ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tgl Pengajuan</span>
                        <span class="font-semibold">{{ $berkas->created_at->format('d M Y - H:i') }}</span>
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

                <form id="formUpdateStatus" action="{{ route('admin.lahir-mati.status', $berkas->uuid) }}" method="POST" class="hidden">
                    @csrf
                    <input type="hidden" name="status" id="inputStatus">
                    <input type="hidden" name="alasan_penolakan" id="inputAlasan">
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

            {{-- Status --}}
            <div class="bg-white p-6 rounded-xl shadow border">
                <h2 class="text-lg font-semibold mb-4 text-gray-700">
                    Status Pengajuan
                </h2>
                <div class="text-center p-3 rounded-lg border 
                    {{ $berkas->status == 'Tolak' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-blue-50 border-blue-200 text-blue-700' }}">
                    <p class="text-xs uppercase tracking-wider mb-1">Status Terkini</p>
                    <p class="text-lg font-bold">{{ $berkas->status }}</p>
                </div>
                @if($berkas->status == 'Tolak')
                <div class="mt-4 p-3 bg-red-100 rounded-lg text-sm text-red-800">
                    <strong>Alasan Penolakan:</strong><br>
                    {{ $berkas->alasan_penolakan }}
                </div>
                @endif
            </div>

        </div>

        {{-- Kolom Kanan: Dokumen Persyaratan --}}
        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-xl shadow border h-full">
                <h2 class="text-lg font-semibold mb-6 text-gray-700">
                    Dokumen Persyaratan (Klik untuk Buka di Tab Baru)
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php
                        $dokumen = [
                            ['label' => 'Formulir F-2.01 (Wajib)', 'field' => 'formulir_f201', 'icon' => 'fa-file-signature', 'color' => 'blue'],
                            ['label' => 'Suket Lahir Mati (Wajib)', 'field' => 'surat_keterangan_lahir_mati', 'icon' => 'fa-file-medical', 'color' => 'red'],
                            ['label' => 'KTP Pemohon (Wajib)', 'field' => 'ktp_pemohon', 'icon' => 'fa-id-card', 'color' => 'green'],
                            ['label' => 'KK Pemohon (Wajib)', 'field' => 'kartu_keluarga_pemohon', 'icon' => 'fa-users', 'color' => 'green'],
                            ['label' => 'KTP Saksi 1 (Wajib) ', 'field' => 'ktp_saksi1', 'icon' => 'fa-user-check', 'color' => 'gray'],
                            ['label' => 'KTP Saksi 2 (Wajib) ', 'field' => 'ktp_saksi2', 'icon' => 'fa-user-check', 'color' => 'gray'],
                        ];
                    @endphp
                    
                    @foreach($dokumen as $dok)
                    <div class="border rounded-xl p-4 flex flex-col justify-between hover:shadow-md transition bg-gray-50">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-{{ $dok['color'] }}-100 text-{{ $dok['color'] }}-600 flex-shrink-0">
                                <i class="fas {{ $dok['icon'] }}"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ $dok['label'] }}</p>
                                @if($berkas->{$dok['field']})
                                    <p class="text-xs text-green-600 mt-1"><i class="fas fa-check-circle mr-1"></i> Terunggah</p>
                                @else
                                    <p class="text-xs text-red-500 mt-1"><i class="fas fa-times-circle mr-1"></i> Tidak Ada</p>
                                @endif
                            </div>
                        </div>
                        
                        @if($berkas->{$dok['field']})
                            <a href="{{ asset('storage/'.$berkas->{$dok['field']}) }}" target="_blank"
                            class="w-full bg-green-600 text-white hover:bg-green-700 py-2 rounded-lg text-sm font-semibold transition text-center flex items-center justify-center">
                                <i class="fas fa-external-link-alt mr-2"></i> Buka Dokumen
                            </a>
                        @else
                            <button disabled class="w-full bg-gray-200 text-gray-400 py-2 rounded-lg text-sm font-semibold cursor-not-allowed">
                                Berkas Kosong
                            </button>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
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
            Swal.fire({
                icon: false,
                title: 'Konfirmasi Penerimaan',
                html: 'Lanjutkan permohonan ke tahap <strong>' + statusBerikut + '</strong>?',
                showCancelButton: true,
                confirmButtonText: 'Konfirmasi',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#e5e7eb',
                reverseButtons: true
            }).then((res) => {
                if (res.isConfirmed) {
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