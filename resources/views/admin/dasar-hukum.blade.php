@extends('layouts.admin')

@section('styles')
<style>
    .law-card {
        border-left: 4px solid #3b82f6;
        transition: all 0.3s ease;
    }
    .law-card:hover {
        border-left-color: #1d4ed8;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">⚖️ Dasar Hukum</h1>
        <button class="ml-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-emerald-500 to-green-600 text-white shadow-lg hover:shadow-xl hover:from-emerald-600 hover:to-green-700 transition-all transform hover:scale-105 border-2 border-emerald-300">
            <i class="fas fa-plus mr-2"></i>Tambah Dasar Hukum
        </button>
    </div>

    {{-- Daftar Dasar Hukum --}}
    <div class="space-y-4">
        {{-- UUD 1945 --}}
        <div class="law-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">NASIONAL</span>
                        <h3 class="font-bold text-gray-800">Undang-Undang Dasar 1945</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">Pasal 26 ayat (1), (2), dan (3) tentang Pencatatan Sipil</p>
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <span><i class="far fa-calendar mr-1"></i> 18 Agustus 1945</span>
                        <span><i class="far fa-file-alt mr-1"></i> Lembaran Negara</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- UU No. 24 Tahun 2013 --}}
        <div class="law-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">NASIONAL</span>
                        <h3 class="font-bold text-gray-800">UU No. 24 Tahun 2013</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">Perubahan atas UU No. 23 Tahun 2006 tentang Administrasi Kependudukan</p>
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <span><i class="far fa-calendar mr-1"></i> 2013</span>
                        <span><i class="far fa-file-alt mr-1"></i> Lembaran Negara</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Perda Kabupaten Toba --}}
        <div class="law-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">DAERAH</span>
                        <h3 class="font-bold text-gray-800">Perda Kabupaten Toba No. 5 Tahun 2020</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">Penyelenggaraan Administrasi Kependudukan di Kabupaten Toba</p>
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <span><i class="far fa-calendar mr-1"></i> 2020</span>
                        <span><i class="far fa-file-alt mr-1"></i> Lembaran Daerah</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
