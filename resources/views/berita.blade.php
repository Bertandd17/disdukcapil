@extends('layouts.app')

@section('title', 'Berita - Disdukcapil Kabupaten Toba')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-50 to-white py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-slate-800">Berita & Pengumuman</h1>
            <p class="mt-2 text-slate-600">Informasi terbaru dari Disdukcapil Kabupaten Toba</p>
        </div>

        <div class="bg-white shadow-sm rounded-xl p-8 border border-slate-200">
            <div class="text-center py-12">
                <i class="fas fa-newspaper text-5xl text-slate-300 mb-4"></i>
                <h2 class="text-xl font-semibold text-slate-700">Belum ada berita</h2>
                <p class="text-slate-500 mt-2">Berita dan pengumuman terbaru akan ditampilkan di sini.</p>
                <a href="{{ route('home') }}" class="inline-block mt-6 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
