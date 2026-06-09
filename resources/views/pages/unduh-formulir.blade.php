@extends('layouts.user')

@section('content')
<main class="pt-0 bg-gray-50 min-h-screen pb-16">
    {{-- Hero Section --}}
    <section class="relative bg-gradient-to-br from-blue-700 via-blue-800 to-cyan-900 text-white py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/20 backdrop-blur-sm text-sm font-medium mb-6 shadow-sm">
                    <i class="fas fa-file-download text-yellow-300"></i>
                    Pusat Unduhan Disdukcapil
                </div>
                <h1 class="text-3xl md:text-5xl font-extrabold mb-6 tracking-tight">Unduh Formulir Pendaftaran</h1>
                <p class="text-base md:text-lg text-blue-100 leading-relaxed">
                    Unduh, cetak, dan isi formulir di bawah ini sebelum Anda mengajukan permohonan melalui Layanan Mandiri. Pastikan data diisi dengan benar dan ditandatangani.
                </p>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#F9FAFB"/>
            </svg>
        </div>
    </section>

    {{-- Content Section (Diperlebar menjadi max-w-7xl dan margin atas diperbaiki) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20 -mt-6 pt-4">
        
        {{-- BUNGKUS DENGAN GRID 3 KOLOM UNTUK KATEGORI --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            
            {{-- KOLOM 1: Pendaftaran Penduduk (KK, Pindah) --}}
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-blue-100 text-blue-700 shadow-sm flex-shrink-0">
                        <i class="fas fa-id-card text-xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 leading-tight">Bidang Pendaftaran Penduduk</h2>
                </div>
                
                <div class="flex flex-col gap-5">
                    <div class="bg-white rounded-2xl p-6 border-2 border-gray-100 hover:border-blue-400 hover:shadow-xl transition-all group flex flex-col h-full">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <i class="fas fa-file-alt text-xl"></i>
                            </div>
                            <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full">PDF</span>
                        </div>
                        <h3 class="font-bold text-gray-800 text-lg mb-2">Formulir F-1.02</h3>
                        <p class="text-sm text-gray-500 mb-6">Formulir Pendaftaran Peristiwa Kependudukan (Digunakan untuk pembuatan KK Baru, Pindah Datang, Pisah KK).</p>
                        
                        <div class="mt-auto pt-4 border-t border-gray-50">
                            <a href="{{ asset('downloads/formulir/F-1.02.pdf') }}" download class="flex justify-center items-center w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-2.5 rounded-xl transition-all shadow-sm">
                                <i class="fas fa-download mr-2"></i> Unduh File
                            </a>
                        </div>
                    </div>

                    {{-- FORMULIR F-1.06 (COMMENTED) --}}
                    <div class="bg-white rounded-2xl p-6 border-2 border-gray-100 hover:border-blue-400 hover:shadow-xl transition-all group flex flex-col h-full">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <i class="fas fa-user-edit text-xl"></i>
                            </div>
                            <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full">PDF</span>
                        </div>
                        <h3 class="font-bold text-gray-800 text-lg mb-2">Formulir F-1.06</h3>
                        <p class="text-sm text-gray-500 mb-6">Formulir Perubahan Elemen Data Kependudukan (Digunakan jika ada perubahan nama, pendidikan, pekerjaan di KK).</p>
                        
                        <div class="mt-auto pt-4 border-t border-gray-50">
                            <a href="{{ asset('downloads/formulir/F-1.06.pdf') }}" download class="flex justify-center items-center w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-2.5 rounded-xl transition-all shadow-sm">
                                <i class="fas fa-download mr-2"></i> Unduh File
                            </a>
                        </div>
                    </div> 
                   
                </div>
            </div>

            {{-- KOLOM 2: Pencatatan Sipil (Akte) --}}
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-green-100 text-green-700 shadow-sm flex-shrink-0">
                        <i class="fas fa-baby-carriage text-xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 leading-tight">Bidang Pencatatan Sipil</h2>
                </div>
                
                <div class="flex flex-col gap-5">
                    <div class="bg-white rounded-2xl p-6 border-2 border-gray-100 hover:border-green-400 hover:shadow-xl transition-all group flex flex-col h-full">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors">
                                <i class="fas fa-file-signature text-xl"></i>
                            </div>
                            <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full">PDF</span>
                        </div>
                        <h3 class="font-bold text-gray-800 text-lg mb-2">Formulir F-2.01</h3>
                        <p class="text-sm text-gray-500 mb-6">Formulir Pelaporan Pencatatan Sipil di dalam Wilayah NKRI (Wajib untuk Akte Kelahiran, Akte Kematian, dan Lahir Mati).</p>
                        
                        <div class="mt-auto pt-4 border-t border-gray-50">
                            <a href="{{ asset('downloads/formulir/F-2.01.pdf') }}" download class="flex justify-center items-center w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-2.5 rounded-xl transition-all shadow-sm">
                                <i class="fas fa-download mr-2"></i> Unduh File
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM 3: Surat Pernyataan (SPTJM) --}}
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-orange-100 text-orange-700 shadow-sm flex-shrink-0">
                        <i class="fas fa-stamp text-xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 leading-tight">Surat Pernyataan (SPTJM)</h2>
                </div>
                
                <div class="flex flex-col gap-5">
                    <div class="bg-white rounded-2xl p-6 border-2 border-gray-100 hover:border-orange-400 hover:shadow-xl transition-all group flex flex-col h-full">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-orange-600 group-hover:bg-orange-600 group-hover:text-white transition-colors">
                                <i class="fas fa-file-contract text-xl"></i>
                            </div>
                            <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full">PDF</span>
                        </div>
                        <h3 class="font-bold text-gray-800 text-lg mb-2">SPTJM F-2.03</h3>
                        <p class="text-sm text-gray-500 mb-6">Surat Pernyataan Tanggung Jawab Mutlak (SPTJM) Kebenaran Data Kelahiran. Dipakai jika tidak memiliki Surat Ket. Lahir dari Bidan/RS.</p>
                        
                        <div class="mt-auto pt-4 border-t border-gray-50">
                            <a href="{{ asset('downloads/formulir/F-2.03.pdf') }}" download class="flex justify-center items-center w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-2.5 rounded-xl transition-all shadow-sm">
                                <i class="fas fa-download mr-2"></i> Unduh File
                            </a>
                        </div>
                    </div>

                    {{-- SPTJM SUAMI ISTRI (COMMENTED)
                    <div class="bg-white rounded-2xl p-6 border-2 border-gray-100 hover:border-orange-400 hover:shadow-xl transition-all group flex flex-col h-full">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-orange-600 group-hover:bg-orange-600 group-hover:text-white transition-colors">
                                <i class="fas fa-user-friends text-xl"></i>
                            </div>
                            <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full">PDF</span>
                        </div>
                        <h3 class="font-bold text-gray-800 text-lg mb-2">SPTJM F-2.04</h3>
                        <p class="text-sm text-gray-500 mb-6">Surat Pernyataan Tanggung Jawab Mutlak (SPTJM) Kebenaran Sebagai Pasangan Suami Istri. Dipakai jika tidak memiliki Buku Nikah/Akte.</p>
                        
                        <div class="mt-auto pt-4 border-t border-gray-50">
                            <a href="{{ asset('downloads/formulir/F-2.04.pdf') }}" download class="flex justify-center items-center w-full bg-orange-50 text-orange-700 hover:bg-orange-600 hover:text-white font-semibold py-2.5 rounded-xl transition-colors">
                                <i class="fas fa-download mr-2"></i> Unduh File
                            </a>
                        </div>
                    </div> 
                    --}}
                </div>
            </div>

        </div>

        {{-- Bantuan / Panduan (Full Width) --}}
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 md:p-8 flex flex-col md:flex-row items-center gap-6 shadow-sm">
            <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0 text-white shadow-lg shadow-blue-200">
                <i class="fas fa-question text-2xl"></i>
            </div>
            <div class="flex-1 text-center md:text-left">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Sudah Selesai Mengisi Formulir?</h3>
                <p class="text-gray-600 text-sm md:text-base mb-4">Jika formulir sudah dicetak, diisi dengan lengkap, dan ditandatangani, silakan scan atau foto formulir tersebut. Kemudian ajukan permohonan Anda melalui menu Layanan Mandiri.</p>
                <a href="{{ route('layanan-mandiri') }}" class="inline-flex items-center bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold px-6 py-3 rounded-xl text-sm transition-all shadow-lg">
                    Pergi ke Layanan Mandiri <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

    </section>
</main>
@endsection