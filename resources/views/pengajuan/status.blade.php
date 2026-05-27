@extends('layouts.user')

@section('content')
<div class="w-full bg-gray-50 min-h-screen py-10 px-4">
    <div class="max-w-2xl mx-auto">

        {{-- Success Alert --}}
        @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start gap-3 animate-fade-in-up">
            <i class="fa-solid fa-check-circle text-green-600 text-xl mt-0.5"></i>
            <div>
                <h3 class="font-semibold text-green-800">{{ session('success') }}</h3>
                <p class="text-sm text-green-700 mt-1">Silakan catat nomor pengajuan untuk melacak status berkas Anda.</p>
            </div>
        </div>
        @endif

        {{-- Error Alert --}}
        @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3 animate-fade-in-up">
            <i class="fa-solid fa-circle-xmark text-red-600 text-xl mt-0.5"></i>
            <div>
                <h3 class="font-semibold text-red-800">{{ is_array(session('error')) ? (session('error')['title'] ?? 'Terjadi kesalahan') : 'Terjadi kesalahan' }}</h3>
                <p class="text-sm text-red-700 mt-1">{{ is_array(session('error')) ? (session('error')['message'] ?? '') : session('error') }}</p>
            </div>
        </div>
        @endif

        {{-- Card: Status Pengajuan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6 animate-fade-in-up">
            <div class="bg-blue-600 px-6 py-4">
                <h2 class="text-white font-bold text-lg">Status Pengajuan</h2>
                <p class="text-blue-100 text-sm mt-0.5">Akta Kematian</p>
            </div>

            <div class="p-6 space-y-5">

                {{-- Nomor Pengajuan --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                    <span class="text-sm font-medium text-gray-500 min-w-[140px]">Nomor Pengajuan</span>
                    <span class="text-base font-bold text-gray-900">
                        {{ $pengajuan->nomor_antrian ?? ($pengajuan->uuid ?? '-') }}
                    </span>
                </div>

                {{-- Status Badge --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                    <span class="text-sm font-medium text-gray-500 min-w-[140px]">Status</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold
                        {{ $pengajuan->status === 'Siap Pengambilan' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        <i class="fa-solid fa-clock text-yellow-500"></i>
                        Menunggu Verifikasi
                    </span>
                </div>

                {{-- Nama Pemohon --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                    <span class="text-sm font-medium text-gray-500 min-w-[140px]">Nama Pemohon</span>
                    <span class="text-base text-gray-900">{{ $pengajuan->nama_pemohon ?? '-' }}</span>
                </div>

                {{-- NIK Pemohon (masked) --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                    <span class="text-sm font-medium text-gray-500 min-w-[140px]">NIK</span>
                    <span class="text-base text-gray-900">
                        @if($pengajuan->nik_pemohon)
                            {{ substr($pengajuan->nik_pemohon, 0, 6) }}********{{ substr($pengajuan->nik_pemohon, -4) }}
                        @else
                            -
                        @endif
                    </span>
                </div>

                {{-- Alamat --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                    <span class="text-sm font-medium text-gray-500 min-w-[140px]">Alamat</span>
                    <span class="text-base text-gray-900">{{ $pengajuan->alamat_pemohon ?? '-' }}</span>
                </div>

                {{-- Tanggal Pengajuan --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                    <span class="text-sm font-medium text-gray-500 min-w-[140px]">Tanggal</span>
                    <span class="text-base text-gray-900">{{ $pengajuan->created_at ? $pengajuan->created_at->format('d-m-Y H:i') : '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Card: Berkas ter-upload --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6 animate-fade-in-up">
            <div class="bg-gray-100 px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-700 text-sm">Berkas Ter-upload</h3>
            </div>
            <div class="p-6">
                @php
                    $berkasFields = [
                        'ktp_pemohon' => 'KTP Pemohon',
                        'kartu_keluarga_pemohon' => 'Kartu Keluarga',
                        'formulir_f201' => 'Formulir F-2.01',
                        'surat_keterangan_kematian' => 'Surat Keterangan Kematian',
                        'ktp_almarhum' => 'KTP Almarhum',
                        'ktp_saksi1' => 'KTP Saksi 1',
                        'ktp_saksi2' => 'KTP Saksi 2',
                    ];
                @endphp

                <ul class="space-y-3">
                    @foreach($berkasFields as $field => $label)
                        @if(!empty($pengajuan->{$field}))
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-file-pdf text-red-500"></i>
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                            <span class="ml-auto text-xs text-green-600 font-medium flex items-center gap-1">
                                <i class="fa-solid fa-check-circle"></i> Terupload
                            </span>
                        </li>
                        @else
                        <li class="flex items-center gap-3 opacity-50">
                            <i class="fa-regular fa-file text-gray-400"></i>
                            <span class="text-sm text-gray-400">{{ $label }}</span>
                            <span class="ml-auto text-xs text-gray-400">Belum diupload</span>
                        </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Card: Tombol Aksi --}}
        <div class="flex flex-col sm:flex-row justify-center gap-3 animate-fade-in-up">
            <a href="{{ route('lacak.berkas') }}"
               class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-center rounded-xl font-semibold text-sm transition-colors flex items-center justify-center gap-2">
                <i class="fa-solid fa-search"></i>
                Lacak Berkas
            </a>
            <a href="{{ route('home') }}"
               class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-center rounded-xl font-semibold text-sm transition-colors flex items-center justify-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Dashboard
            </a>
        </div>

    </div>
</div>
@endsection