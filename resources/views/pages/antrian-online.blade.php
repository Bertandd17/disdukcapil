@extends('layouts.user')

@section('content')
@php
 use App\Models\Layanan_Model;
 $data_layanan = Layanan_Model::all();

 $jam_kerja = $jam_kerja ?? [
 'senin_kamis' => '08.00 - 16.00 WIB',
 'jumat' => '08.00 - 14.00 WIB',
 'sabtu_minggu' => 'Tutup',
 ];
@endphp

<main class="pt-0">
 {{-- Hero Section --}}
 <section class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-cyan-800 text-white py-20">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center max-w-3xl mx-auto">
 <h1 class="text-4xl md:text-5xl font-extrabold mb-6">
 Ambil Nomor Antrian dari Rumah
 </h1>
 <p class="text-lg text-green-100 mb-8">
 Tidak perlu datang lebih awal untuk antri. Ambil nomor antrian secara online dan datang sesuai jadwal.
 </p>
 </div>
 </div>

 <div class="absolute bottom-0 left-0 right-0">
 <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
 <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#f9fafb"/>
 </svg>
 </div>
 </section>

 {{-- Jam Operasional Layanan --}}
 <section class="py-8 bg-white">
 <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6 border border-green-100">
 <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-0">
 <div class="flex-shrink-0">
 <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
 <i class="fas fa-clock text-white text-xl"></i>
 </div>
 </div>
 <div class="sm:ml-4 flex-1 w-full">
 <h3 class="text-lg font-bold text-gray-800 mb-3">Jam Operasional Layanan</h3>
 <div class="grid md:grid-cols-3 gap-4">
 <div class="bg-white rounded-lg p-4 shadow-sm">
 <div class="flex items-center mb-2">
 <i class="fas fa-calendar-day text-green-600 mr-2"></i>
 <span class="font-semibold text-gray-800">Senin - Kamis</span>
 </div>
 <p class="text-lg font-bold text-green-600">{{ $jam_kerja['senin_kamis'] }}</p>
 </div>
 <div class="bg-white rounded-lg p-4 shadow-sm">
 <div class="flex items-center mb-2">
 <i class="fas fa-calendar-day text-yellow-600 mr-2"></i>
 <span class="font-semibold text-gray-800">Jumat</span>
 </div>
 <p class="text-lg font-bold text-yellow-600">{{ $jam_kerja['jumat'] }}</p>
 </div>
 <div class="bg-white rounded-lg p-4 shadow-sm">
 <div class="flex items-center mb-2">
 <i class="fas fa-calendar-times text-red-600 mr-2"></i>
 <span class="font-semibold text-gray-800">Sabtu - Minggu</span>
 </div>
 <p class="text-lg font-bold text-red-600">{{ $jam_kerja['sabtu_minggu'] }}</p>
 </div>
 </div>
 <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
 <div class="flex items-start gap-2">
 <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
 <div>
 <p class="font-semibold text-yellow-800">Penting:</p>
 <p class="text-sm text-yellow-700">Antrian online hanya dapat dibuat pada jam operasional. Di luar jam kerja, sistem tidak akan menerima permohonan antrian baru.</p>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </section>

 {{-- Alur Pendaftaran Online Section (Revisi 6 Langkah) --}}
 <section id="alur-layanan" class="py-16 bg-white">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-16 reveal">
 <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider">Panduan Masyarakat</span>
 <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Alur Pendaftaran Online</h2>
 <p class="text-gray-600 mt-3 max-w-2xl mx-auto">
 Langkah-langkah mudah mengurus dokumen kependudukan melalui layanan mandiri Disdukcapil Kabupaten
 Toba
 </p>
 </div>

 <div class="relative reveal">
 <div
 class="hidden lg:block absolute top-[45px] left-[8%] right-[8%] h-1 bg-gradient-to-r from-blue-100 via-blue-400 to-blue-100 z-0">
 </div>

 <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-10 lg:gap-4 relative z-10">

 <div class="flex flex-col items-center text-center group">
 <div
 class="w-24 h-24 bg-white rounded-full border-4 border-blue-50 shadow-lg flex items-center justify-center mb-6 relative group-hover:border-blue-500 transition-colors duration-300">
 <div
 class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-700 text-white font-bold rounded-full flex items-center justify-center border-2 border-white shadow-sm">
 1</div>
 <i
 class="fas fa-ticket-alt text-3xl text-blue-600 group-hover:scale-110 transition-transform duration-300"></i>
 </div>
 <h3 class="font-bold text-gray-800 text-base mb-2">Ambil Antrean</h3>
 <p class="text-xs text-gray-600 px-1 leading-relaxed">Dapatkan nomor antrean virtual Anda
 sebelum memulai pengajuan.</p>
 </div>

 <div class="flex flex-col items-center text-center group">
 <div
 class="w-24 h-24 bg-white rounded-full border-4 border-blue-50 shadow-lg flex items-center justify-center mb-6 relative group-hover:border-teal-500 transition-colors duration-300">
 <div
 class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-teal-500 to-teal-700 text-white font-bold rounded-full flex items-center justify-center border-2 border-white shadow-sm">
 2</div>
 <i
 class="fas fa-mouse-pointer text-3xl text-teal-600 group-hover:scale-110 transition-transform duration-300"></i>
 </div>
 <h3 class="font-bold text-gray-800 text-base mb-2">Pilih Layanan</h3>
 <p class="text-xs text-gray-600 px-1 leading-relaxed">Pilih jenis dokumen kependudukan yang
 ingin Anda urus di portal.</p>
 </div>

 <div class="flex flex-col items-center text-center group">
 <div
 class="w-24 h-24 bg-white rounded-full border-4 border-blue-50 shadow-lg flex items-center justify-center mb-6 relative group-hover:border-amber-500 transition-colors duration-300">
 <div
 class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-amber-500 to-amber-700 text-white font-bold rounded-full flex items-center justify-center border-2 border-white shadow-sm">
 3</div>
 <i
 class="fas fa-file-upload text-3xl text-amber-600 group-hover:scale-110 transition-transform duration-300"></i>
 </div>
 <h3 class="font-bold text-gray-800 text-base mb-2">Unggah Berkas</h3>
 <p class="text-xs text-gray-600 px-1 leading-relaxed">Isi formulir elektronik dan unggah
 foto/scan dokumen persyaratan.</p>
 </div>

 <div class="flex flex-col items-center text-center group">
 <div
 class="w-24 h-24 bg-white rounded-full border-4 border-blue-50 shadow-lg flex items-center justify-center mb-6 relative group-hover:border-purple-500 transition-colors duration-300">
 <div
 class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-700 text-white font-bold rounded-full flex items-center justify-center border-2 border-white shadow-sm">
 4</div>
 <i
 class="fas fa-user-check text-3xl text-purple-600 group-hover:scale-110 transition-transform duration-300"></i>
 </div>
 <h3 class="font-bold text-gray-800 text-base mb-2">Verifikasi Admin</h3>
 <p class="text-xs text-gray-600 px-1 leading-relaxed">Petugas kami akan memvalidasi kebenaran
 dan kelengkapan data Anda.</p>
 </div>

 <div class="flex flex-col items-center text-center group">
 <div
 class="w-24 h-24 bg-white rounded-full border-4 border-blue-50 shadow-lg flex items-center justify-center mb-6 relative group-hover:border-indigo-500 transition-colors duration-300">
 <div
 class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-indigo-500 to-indigo-700 text-white font-bold rounded-full flex items-center justify-center border-2 border-white shadow-sm">
 5</div>
 <i
 class="fas fa-search text-3xl text-indigo-600 group-hover:scale-110 transition-transform duration-300"></i>
 </div>
 <h3 class="font-bold text-gray-800 text-base mb-2">Cek Status</h3>
 <p class="text-xs text-gray-600 px-1 leading-relaxed">Pantau terus status pengajuan Anda secara
 berkala menggunakan nomor antrean.</p>
 </div>

 <div class="flex flex-col items-center text-center group">
 <div
 class="w-24 h-24 bg-white rounded-full border-4 border-blue-50 shadow-lg flex items-center justify-center mb-6 relative group-hover:border-rose-500 transition-colors duration-300">
 <div
 class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-rose-500 to-rose-700 text-white font-bold rounded-full flex items-center justify-center border-2 border-white shadow-sm">
 6</div>
 <i
 class="fas fa-cloud-download-alt text-3xl text-rose-600 group-hover:scale-110 transition-transform duration-300"></i>
 </div>
 <h3 class="font-bold text-gray-800 text-base mb-2">Unduh Berkas</h3>
 <p class="text-xs text-gray-600 px-1 leading-relaxed">
 Berkas selesai dikirim ke nomor antrean. Segera unduh sebelum <span
 class="font-bold text-rose-600">batas waktu 1x24 jam</span>.
 </p>
 </div>

 </div>
 </div>
 </div>
 </section>
 
 {{-- Booking Form Section --}}
 <section class="py-16 bg-gray-50" id="formSection">
 <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-12">
 <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Ambil Nomor Antrian</h2>
 <p class="text-gray-600 mt-3 max-w-2xl mx-auto">
 Lengkapi data diri Anda untuk mengambil nomor antrian
 </p>
 </div>

 <div class="bg-white rounded-2xl shadow-lg p-5 sm:p-8">
 {{-- Indikator langkah --}}
 <div class="flex flex-wrap items-center justify-center gap-2 mb-10 text-sm">
 <div class="flex items-center gap-2">
 <span id="step1Indicator" class="step-indicator active flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-bold">1</span>
 <span id="step1Label" class="font-semibold text-blue-600">Upload KTP</span>
 </div>
 <div id="line1" class="w-16 h-1 bg-gray-300 mx-2 rounded hidden sm:block"></div>
 <div class="flex items-center gap-2">
 <span id="step2Indicator" class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-bold">2</span>
 <span id="step2Label" class="font-semibold text-gray-400">Verifikasi Data</span>
 </div>
 <div id="line2" class="w-16 h-1 bg-gray-300 mx-2 rounded hidden sm:block"></div>
 <div class="flex items-center gap-3">
 <span id="step3Indicator" class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-bold">3</span>
 <span id="step3Label" class="font-semibold text-gray-400">Konfirmasi</span>
 </div>
 </div>

 <form id="antrianForm" class="space-y-6" autocomplete="off">
 @csrf

 {{-- STEP 1: Layanan + unggah KTP --}}
 <div id="step1" class="step-content space-y-6">
 <div>
 <label class="block text-lg font-semibold text-gray-700 mb-2">
 Jenis Layanan <span class="text-red-500">*</span>
 </label>
 <select name="layanan_id" id="layanan_id"
 class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-base bg-white">
 <option value="" disabled selected>Pilih jenis layanan...</option>
 @foreach($data_layanan as $layanan)
 <option value="{{ $layanan->layanan_id }}">{{ $layanan->nama_layanan }}</option>
 @endforeach
 </select>
 </div>

 <input type="file" id="ktpFileInput" accept="image/jpeg,image/jpg,image/png,image/pjpeg" class="hidden" aria-hidden="true">

 <div>
 <label class="block text-lg font-semibold text-gray-700 mb-2">
 Foto e-KTP <span class="text-red-500">*</span>
 </label>
 <div id="uploadArea" class="relative border-2 border-dashed border-gray-300 rounded-2xl p-5 sm:p-8 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50/40 transition-colors">
 <div id="uploadPlaceholder">
 <i class="fas fa-cloud-upload-alt text-5xl text-gray-400 mb-4"></i>
 <p class="text-gray-700 font-medium mb-1">Klik atau seret foto KTP ke sini</p>
 <p class="text-sm text-gray-500">JPG, JPEG, atau PNG, maks. 5 MB</p>
 <p id="uploadDebug" class="text-xs text-gray-400 mt-3">Status: <span id="uploadDebugValue">memuat...</span></p>
 </div>
 <div id="previewContainer" class="hidden">
 <img id="imagePreview" src="" alt="Pratinjau KTP" class="max-h-56 mx-auto rounded-lg shadow-md object-contain">
 <p id="fileName" class="text-sm text-gray-600 mt-3 font-medium"></p>
 <button type="button" id="changeImageBtn" class="mt-4 text-sm text-blue-600 hover:text-blue-800 font-semibold underline">
 Ganti foto
 </button>
 </div>
 </div>
 </div>

<button type="button" id="step1NextBtn" disabled
 class="w-full py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
 <i class="fas fa-arrow-right mr-2"></i>
 Lanjut dan Kirim ke OCR
</button>
 </div>

 {{-- STEP 2: Hasil OCR + koreksi --}}
 <div id="step2" class="step-content hidden space-y-6">
 <div id="ocrConfidence" class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
 <div class="flex gap-3">
 <div id="ocrStatusIcon" class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
 <i id="ocrStatusFa" class="fas fa-check-circle text-blue-600"></i>
 </div>
 <div class="flex-1 min-w-0">
 <div class="flex flex-wrap items-center gap-2 mb-1">
 <span id="ocrStatusTitle" class="font-semibold text-blue-800">Data berhasil diekstrak</span>
 <span id="ocrTrustBadge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-200 text-blue-800">Auto-fill</span>
 </div>
 <p id="ocrStatusMessage" class="text-sm text-blue-900">Data dari foto KTP sudah diisi otomatis. Silakan periksa dan koreksi jika perlu.</p>
 </div>
 </div>
 </div>

 {{-- Data Wajib --}}
 <div class="grid md:grid-cols-2 gap-4">
 <div>
 <label class="block font-semibold text-gray-700 mb-2">NIK <span class="text-red-500">*</span></label>
 <input type="text" name="nik" id="nik" inputmode="numeric" maxlength="16" placeholder="16 digit"
 class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono text-base">
 </div>
 <div>
 <label class="block font-semibold text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
 <input type="text" name="nama_lengkap" id="nama_lengkap" placeholder="Sesuai KTP"
 class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-base">
 </div>
 </div>

 <div>
 <label class="block font-semibold text-gray-700 mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
 <textarea name="alamat" id="alamat" rows="3" placeholder="Alamat pada KTP"
 class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none text-base"></textarea>
 </div>

 <div class="flex flex-col sm:flex-row gap-3">
 <button type="button" id="step2PrevBtn"
 class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-bold transition">
 <i class="fas fa-arrow-left mr-2"></i> Kembali
 </button>
 <button type="button" id="step2NextBtn"
 class="flex-1 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg">
 Lanjut ke konfirmasi <i class="fas fa-arrow-right ml-2"></i>
 </button>
 </div>
 </div>

 {{-- STEP 3: Ringkasan --}}
 <div id="step3" class="step-content hidden space-y-6">
 <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 space-y-4 text-left">
 <h3 class="font-bold text-gray-800 text-lg border-b pb-2">Ringkasan data</h3>
 <dl class="grid gap-3 text-sm">
 <div class="flex justify-between gap-4"><dt class="text-gray-500">NIK</dt><dd id="summaryNik" class="font-mono font-semibold text-gray-900 text-right break-all">-</dd></div>
 <div class="flex justify-between gap-4"><dt class="text-gray-500">Nama</dt><dd id="summaryNama" class="font-semibold text-gray-900 text-right">-</dd></div>
 <div class="flex justify-between gap-4 items-start"><dt class="text-gray-500 shrink-0">Alamat</dt><dd id="summaryAlamat" class="text-gray-900 text-right">-</dd></div>
 <div class="flex justify-between gap-4"><dt class="text-gray-500">Layanan</dt><dd id="summaryLayanan" class="font-semibold text-gray-900 text-right">-</dd></div>
 <div class="flex justify-between gap-4"><dt class="text-gray-500">Nomor antrian (sementara)</dt><dd id="summaryNomor" class="font-mono font-bold text-green-700 text-right">-</dd></div>
 </dl>
 </div>
 <div class="flex flex-col sm:flex-row gap-3">
 <button type="button" id="step3PrevBtn"
 class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-bold transition">
 <i class="fas fa-arrow-left mr-2"></i> Ubah data
 </button>
 <button type="submit" id="submitBtn"
 class="flex-1 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg">
 <i class="fas fa-check-circle mr-2"></i>
 Konfirmasi dan dapatkan nomor antrian
 </button>
 </div>
 </div>
 </form>
 </div>
 </div>
 </section>

 {{-- Ticket Result Section dengan Animasi --}}
 <section id="ticketResult" class="py-16 bg-gray-50 hidden">
 <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
 <!-- Confetti Container -->
 <div id="confetti-container" class="fixed inset-0 pointer-events-none z-50"></div>

 <div class="bg-white rounded-2xl shadow-2xl overflow-hidden ticket-wrapper">
 <!-- Header Tiket -->
 <div class="bg-gradient-to-r from-green-600 via-emerald-600 to-green-700 text-white p-8 text-center relative overflow-hidden">
 <!-- Animated Background -->
 <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent animate-shimmer"></div>

 <div class="relative z-10">
 <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-4 animate-bounce-slow">
 <i class="fas fa-ticket-alt text-5xl"></i>
 </div>
 <h3 class="text-3xl font-bold mb-2">Nomor Antrian Anda</h3>
 <p class="text-green-100">Simpan nomor ini untuk mengecek status</p>
 </div>
 </div>

 <!-- Body Tiket -->
 <div class="p-5 sm:p-8 text-center relative">
 <!-- Nomor Antrian dengan Counter Animation -->
 <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-5 sm:p-8 mb-6 relative overflow-hidden">
 <div class="absolute inset-0 bg-gradient-to-r from-green-600/5 to-emerald-600/5 animate-pulse-slow"></div>
 <div class="relative z-10">
 <p class="text-sm text-gray-500 mb-2 font-medium">NOMOR ANTRIAN</p>
 <div class="text-7xl font-black bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent mb-4 counter-animate" id="ticketNumber">ABC-123</div>
 <div class="flex items-center justify-center gap-2 text-sm text-gray-500">
 <i class="fas fa-clock"></i>
 <span id="ticketTime">-</span>
 </div>
 </div>
 </div>

 <!-- Info Grid -->
 <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left mb-6">
 <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-100 info-card">
 <div class="flex items-center gap-2 mb-2">
 <i class="fas fa-user text-green-600"></i>
 <p class="text-xs font-semibold text-gray-500 uppercase">Nama</p>
 </div>
 <p class="font-bold text-gray-800 text-lg" id="ticketName">-</p>
 </div>
 <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4 border border-purple-100 info-card">
 <div class="flex items-center gap-2 mb-2">
 <i class="fas fa-file-alt text-purple-600"></i>
 <p class="text-xs font-semibold text-gray-500 uppercase">Layanan</p>
 </div>
 <p class="font-bold text-gray-800 text-lg" id="ticketService">-</p>
 </div>
 </div>

 <!-- Action Buttons -->
 <div class="flex flex-col sm:flex-row gap-3">
 <button onclick="copyTicketNumber()" id="copyBtn" class="flex-1 py-3 bg-gray-200 text-gray-800 rounded-xl font-bold hover:bg-gray-300 transition-all shadow-md action-btn no-print">
 <i class="fas fa-copy mr-2"></i>
 Salin Nomor Antrian
 </button>
 <button onclick="resetForm()" class="flex-1 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg action-btn no-print">
 <i class="fas fa-plus mr-2"></i>
 Ambil Lagi
 </button>
 </div>
 </div>

 <!-- Decorative Elements -->
 <div class="absolute top-0 left-0 w-32 h-32 bg-green-500/10 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
 <div class="absolute bottom-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full translate-x-1/2 translate-y-1/2"></div>
 </div>
 </div>
 </section>

 {{-- Cari Antrian Section --}}
 <section class="py-16 bg-white">
 <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-12">
 <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Lupa Nomor Antrian?</h2>
 <p class="text-gray-600 mt-3">Cari nomor antrian Anda dengan memasukkan nama atau nomor antrian</p>
 </div>

 <div class="bg-gradient-to-br from-gray-50 to-emerald-50 rounded-2xl shadow-lg p-5 sm:p-8 border border-gray-100">
 <div class="grid md:grid-cols-3 gap-4 mb-6">
 <div class="md:col-span-2">
 <input type="text" id="searchInput" placeholder="Masukkan nama atau nomor antrian"
 class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
 </div>
 <div>
 <select id="searchLayanan" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white">
 <option value="">Semua Layanan</option>
 @foreach($data_layanan as $layanan)
 <option value="{{ $layanan->layanan_id }}">{{ $layanan->nama_layanan }}</option>
 @endforeach
 </select>
 </div>
 </div>
 <button type="button" id="btnCariAntrian" class="w-full py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg">
 <i class="fas fa-search mr-2"></i>
 Cari Antrian
 </button>
 </div>

 <!-- Search Results dengan Staggered Animation -->
 <div id="searchResults" class="mt-8 space-y-4"></div>
 </div>
 </section>
</main>
@endsection

@push('styles')
<style>
 /* Ticket Animation */
 .ticket-wrapper {
 animation: ticketAppear 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
 }

 @keyframes ticketAppear {
 0% {
 transform: scale(0.3) rotate(-10deg);
 opacity: 0;
 }
 50% {
 transform: scale(1.05) rotate(2deg);
 }
 100% {
 transform: scale(1) rotate(0deg);
 opacity: 1;
 }
 }

 /* Counter Animation untuk Nomor Antrian */
 .counter-animate {
 animation: counterPop 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) 0.3s both;
 }

 @keyframes counterPop {
 0% {
 transform: scale(0);
 opacity: 0;
 }
 50% {
 transform: scale(1.2);
 }
 100% {
 transform: scale(1);
 opacity: 1;
 }
 }

 /* Info Cards Staggered Animation */
 .info-card {
 animation: slideUp 0.5s ease-out 0.4s both;
 }

 .info-card:nth-child(1) {
 animation-delay: 0.4s;
 }

 .info-card:nth-child(2) {
 animation-delay: 0.5s;
 }

 @keyframes slideUp {
 from {
 transform: translateY(20px);
 opacity: 0;
 }
 to {
 transform: translateY(0);
 opacity: 1;
 }
 }

 /* Action Buttons */
 .action-btn {
 animation: buttonSlide 0.5s ease-out 0.6s both;
 }

 @keyframes buttonSlide {
 from {
 transform: translateY(10px);
 opacity: 0;
 }
 to {
 transform: translateY(0);
 opacity: 1;
 }
 }

 /* Shimmer Effect */
 @keyframes shimmer {
 0% {
 transform: translateX(-100%);
 }
 100% {
 transform: translateX(100%);
 }
 }

 .animate-shimmer > div {
 animation: shimmer 2s infinite;
 }

 /* Bounce Animation */
 @keyframes bounce-slow {
 0%, 100% {
 transform: translateY(0);
 }
 50% {
 transform: translateY(-10px);
 }
 }

 .animate-bounce-slow {
 animation: bounce-slow 2s ease-in-out infinite;
 }

 /* Pulse Slow Animation */
 @keyframes pulse-slow {
 0%, 100% {
 opacity: 1;
 }
 50% {
 opacity: 0.7;
 }
 }

 .animate-pulse-slow {
 animation: pulse-slow 2s ease-in-out infinite;
 }

 /* Search Result Card Animation */
 .search-result-card {
 animation: cardSlideIn 0.5s ease-out both;
 }

 @keyframes cardSlideIn {
 from {
 transform: translateX(-30px);
 opacity: 0;
 }
 to {
 transform: translateX(0);
 opacity: 1;
 }
 }

 /* Lacak Card Animation */
 .lacak-card {
 animation: lacakAppear 0.7s cubic-bezier(0.68, -0.55, 0.265, 1.55);
 }

 @keyframes lacakAppear {
 0% {
 transform: scale(0.8) translateY(20px);
 opacity: 0;
 }
 100% {
 transform: scale(1) translateY(0);
 opacity: 1;
 }
 }

 /* Timeline Animation */
 .timeline-item {
 animation: timelineFade 0.5s ease-out both;
 }

 @keyframes timelineFade {
 from {
 transform: translateX(-20px);
 opacity: 0;
 }
 to {
 transform: translateX(0);
 opacity: 1;
 }
 }

 /* Timeline Progress Line Animation */
 .timeline-progress {
 animation: progressLine 1.5s ease-out forwards;
 }

 @keyframes progressLine {
 from {
 height: 0;
 }
 to {
 height: 100%;
 }
 }

 /* Status Badge Glow */
 .status-glow {
 animation: glow 2s ease-in-out infinite;
 }

 @keyframes glow {
 0%, 100% {
 box-shadow: 0 0 5px currentColor;
 }
 50% {
 box-shadow: 0 0 20px currentColor, 0 0 30px currentColor;
 }
 }

 /* Confetti Animation */
 .confetti {
 position: fixed;
 width: 10px;
 height: 10px;
 top: -10px;
 animation: confettiFall 3s linear forwards;
 }

 @keyframes confettiFall {
 0% {
 transform: translateY(0) rotate(0deg);
 opacity: 1;
 }
 100% {
 transform: translateY(100vh) rotate(720deg);
 opacity: 0;
 }
 }

 /* Stat Cards */
 .stat-card {
 transition: all 0.3s ease;
 }

 .stat-card:hover {
 transform: translateY(-5px);
 box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
 }

 @media (max-width: 640px) {
 #step1Indicator,
 #step2Indicator,
 #step3Indicator {
 width: 2rem;
 height: 2rem;
 font-size: 0.8rem;
 }

 #step1Label,
 #step2Label,
 #step3Label {
 font-size: 0.75rem;
 }

 #ticketNumber {
 font-size: clamp(2.5rem, 14vw, 4rem);
 line-height: 1;
 overflow-wrap: anywhere;
 }

 .search-result-card > div:first-child,
 .search-result-card .flex.items-center.gap-2.mb-3 {
 align-items: flex-start;
 }

 .search-result-card .flex.items-center.gap-2.px-3.py-2 {
 align-self: flex-start;
 max-width: 100%;
 }

 .search-result-card .grid.grid-cols-2 {
 grid-template-columns: minmax(0, 1fr) !important;
 }

 .swal2-popup .flex.justify-between {
 gap: 0.75rem;
 }
 }

 /* Print Styles */
 @media print {
 /* Sembunyikan semua elemen kecuali tiket */
 body > *:not(#ticketResult):not(#lacakResult) {
 display: none !important;
 }

 /* Tampilkan section yang relevan */
 #ticketResult,
 #lacakResult {
 display: block !important;
 position: absolute;
 left: 0;
 top: 0;
 width: 100%;
 margin: 0 !important;
 padding: 20px !important;
 }

 /* Hilangkan elemen dekoratif dan tombol */
 .no-print,
 #confetti-container,
 .action-btn,
 #ticketResult .absolute:not(.bg-gradient-to-r):not(.inset-0) {
 display: none !important;
 }

 /* Style untuk cetak tiket */
 .ticket-wrapper {
 box-shadow: none !important;
 border: 2px solid #000 !important;
 page-break-inside: avoid;
 max-width: 100% !important;
 }

 /* Style untuk cetak lacak result */
 .lacak-card {
 box-shadow: none !important;
 border: 1px solid #000 !important;
 page-break-inside: avoid;
 }

 .bg-gradient-to-r {
 background: #28A745 !important;
 -webkit-print-color-adjust: exact;
 print-color-adjust: exact;
 }

 /* Pastikan warna tercetak dengan benar */
 * {
 -webkit-print-color-adjust: exact !important;
 print-color-adjust: exact !important;
 }

 /* Hentikan semua animasi saat print */
 * {
 animation: none !important;
 transition: none !important;
 transform: none !important;
 }

 /* Atur ukuran kertas */
 @page {
 size: A4;
 margin: 15mm;
 }

 body {
 margin: 0;
 padding: 0;
 background: white !important;
 }

 /* Pastikan text tetap terbaca */
 .text-transparent {
 background-clip: border-box !important;
 -webkit-background-clip: border-box !important;
 color: #28A745 !important;
 }
 }
</style>
@endpush

@push('scripts')
<script>
 window.ANTRIAN_OCR_CONFIG = @json($ocrClientConfig ?? []);
</script>

{{-- Search Antrian Functions - didefinisikan sebelum antrian-ocr.js agar selalu tersedia --}}
<script>
 // Helper: deteksi format nomor antrian (ABC-123-456 atau ABC123)
 // Hanya true jika: 3 huruf di awal DAN ada angka setelahnya
 window.isQueueNumberFormat = function(input) {
 if (!input || typeof input !== 'string') return false;
 var cleaned = input.replace(/[-\s]/g, '').toUpperCase();
 // Format nomor antrian: 3 huruf + minimal 1 angka
 // Contoh: ABC123, ABC-123-456, ABC1
 var queuePattern = /^[A-Z]{3,}\d+$/;
 // Atau format dengan dash: ABC-123-456
 var dashPattern = /^[A-Z]{3,}-\d+(-\d+)*$/;
 return queuePattern.test(cleaned) || dashPattern.test(input.toUpperCase());
 };

 // Helper: format nomor antrian ke standar ABC-123-456
 window.formatQueueNumber = function(input) {
 if (!input || typeof input !== 'string') return null;
 var cleaned = input.toUpperCase().replace(/[^A-Z0-9]/g, '');
 if (cleaned.length < 3) return null;
 var letters = cleaned.substring(0, 3);
 var numbers = cleaned.substring(3);
 if (numbers.length < 6) {
 numbers = numbers.padEnd(6, '0');
 }
 var part1 = numbers.substring(0, 3);
 var part2 = numbers.substring(3, 6);
 return letters + '-' + part1 + '-' + part2;
 };

 // Fungsi pencarian antrian - global scope
 // ==== Auto-refresh status (polling) ====
 window.__lacakPollState = window.__lacakPollState || { interval: null, lastSearch: '', lastLayanan: '', lastStatuses: {} };

 window.stopLacakPolling = function() {
 if (window.__lacakPollState.interval) {
 clearInterval(window.__lacakPollState.interval);
 window.__lacakPollState.interval = null;
 console.log('[Lacak Polling] Stopped');
 }
 };

 window.startLacakPolling = function(searchValue, layananId) {
 window.stopLacakPolling();
 window.__lacakPollState.lastSearch = searchValue;
 window.__lacakPollState.lastLayanan = layananId || '';
 console.log('[Lacak Polling] Started for', searchValue);
 window.__lacakPollState.interval = setInterval(window.pollLacakStatus, 10000);
 };

 window.pollLacakStatus = function() {
 var s = window.__lacakPollState.lastSearch;
 if (!s) { window.stopLacakPolling(); return; }
 if (document.hidden) return;

 var params = new URLSearchParams();
 var isQueueNumber = window.isQueueNumberFormat ? window.isQueueNumberFormat(s) : false;
 if (isQueueNumber) {
 var formatted = window.formatQueueNumber ? window.formatQueueNumber(s) : null;
 params.append('nomor_antrian', formatted || s.toUpperCase());
 } else {
 params.append('nama_lengkap', s);
 }
 if (window.__lacakPollState.lastLayanan) {
 params.append('layanan_id', window.__lacakPollState.lastLayanan);
 }

 fetch('{{ route('antrian.search') }}?' + params.toString(), {
 method: 'GET',
 headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
 })
 .then(function(r) { return r.ok ? r.json() : null; })
 .then(function(data) {
 if (!data || !data.success || !data.data || !data.data.length) return;
 var container = document.getElementById('searchResults');
 if (!container) return;

 // Detect status changes for toast notification
 var changed = false;
 data.data.forEach(function(item) {
 var key = item.nomor_antrian || item.antrian_online_id;
 var newStatus = item.status_antrian || item.status;
 var oldStatus = window.__lacakPollState.lastStatuses[key];
 if (oldStatus && newStatus && oldStatus !== newStatus) {
 changed = true;
 if (window.SwalHelper && window.SwalHelper.info) {
 window.SwalHelper.info('Status Diperbarui', key + ': ' + oldStatus + ' ? ' + newStatus);
 }
 }
 if (newStatus) window.__lacakPollState.lastStatuses[key] = newStatus;
 });

 if (window.renderSearchResults) {
 window.renderSearchResults(data.data);
 }
 })
 .catch(function(e) { console.warn('[Lacak Polling] error', e); });
 };

 // Stop polling when page unloads
 window.addEventListener('beforeunload', function() { window.stopLacakPolling(); });

 window.searchAntrian = function() {
 try {
 console.log('=== SEARCH ANTRIAN DIPANGGIL ===');

 var searchInput = document.getElementById('searchInput');
 var searchLayanan = document.getElementById('searchLayanan');
 var resultsContainer = document.getElementById('searchResults');

 if (!searchInput) {
 console.error('searchInput element not found');
 SwalHelper.error('Error!', 'Elemen input tidak ditemukan');
 return;
 }

 var searchValue = searchInput.value.trim();
 var layananId = searchLayanan ? searchLayanan.value : '';

 console.log('Search value:', searchValue);
 console.log('Layanan ID:', layananId);

 if (!searchValue) {
 SwalHelper.warning('Peringatan!', 'Masukkan kata kunci pencarian');
 return;
 }

 // Tampilkan loading di results container
 if (resultsContainer) {
 resultsContainer.innerHTML = '<div class="text-center py-8"><div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-green-500 border-t-transparent mb-4"></div><p class="text-gray-500 font-medium">Mencari data antrian...</p></div>';
 }

 // Deteksi apakah ini adalah nomor antrian pernikahan (PNK-)
 var isPernikahan = searchValue.toUpperCase().startsWith('PNK-');

 if (isPernikahan) {
 // Cari di tabel pernikahan
 window.searchPernikahan(searchValue, resultsContainer);
 return;
 }

 // Build query params untuk antrian biasa
 var params = new URLSearchParams();

 // Deteksi apakah input adalah format nomor antrian
 var isQueueNumber = window.isQueueNumberFormat(searchValue);

 if (isQueueNumber) {
 var formattedNomor = window.formatQueueNumber(searchValue);
 if (formattedNomor) {
 params.append('nomor_antrian', formattedNomor);
 console.log('Searching queue number:', formattedNomor);
 } else {
 params.append('nomor_antrian', searchValue.toUpperCase());
 }
 } else {
 params.append('nama_lengkap', searchValue);
 console.log('Searching by name:', searchValue);
 }

 if (layananId) {
 params.append('layanan_id', layananId);
 }

 console.log('Searching with params:', params.toString());

 var searchUrl = '{{ route('antrian.search') }}?' + params.toString();
 console.log('Search URL:', searchUrl);

 fetch(searchUrl, {
 method: 'GET',
 headers: {
 'Accept': 'application/json',
 'X-Requested-With': 'XMLHttpRequest'
 }
 })
 .then(function(response) {
 console.log('Response status:', response.status);
 if (!response.ok) {
 throw new Error('HTTP error! status: ' + response.status);
 }
 return response.json();
 })
 .then(function(data) {
 console.log('Search response:', data);
 console.log('Response success:', data.success);
 console.log('Response data:', data.data);
 console.log('Response data length:', data.data ? data.data.length : 0);

 if (!resultsContainer) {
 console.error('resultsContainer not found');
 return;
 }

 if (data.success && data.data && data.data.length > 0) {
 console.log('Rendering ' + data.data.length + ' results');
 window.renderSearchResults(data.data);

 // Seed status cache & start auto-refresh polling (skip pernikahan-only results)
 window.__lacakPollState.lastStatuses = {};
 var hasNonPernikahan = false;
 data.data.forEach(function(item) {
 var key = item.nomor_antrian || item.antrian_online_id;
 var st = item.status_antrian || item.status;
 if (key && st) window.__lacakPollState.lastStatuses[key] = st;
 if (!(item.nomor_antrian && String(item.nomor_antrian).indexOf('PNK-') === 0)) {
 hasNonPernikahan = true;
 }
 });
 if (hasNonPernikahan) {
 window.startLacakPolling(searchValue, layananId);
 } else {
 window.stopLacakPolling();
 }

 // Notifikasi cari berhasil
 SwalHelper.success('Ditemukan!', data.data.length + ' data ditemukan untuk "' + searchValue + '"');
 } else {
 console.log('No results found. Message:', data.message || 'No message');
 window.stopLacakPolling();
 var debugInfo = data.debug ? '<br><small class="text-gray-400">Debug: Mencari ' + data.debug.search_type + ' = ' + (data.debug.params.nama_lengkap || data.debug.params.nomor_antrian || data.debug.params.search || 'kosong') + '</small>' : '';
 resultsContainer.innerHTML = '<div class="text-center py-8 animate-fade-in"><div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-search text-3xl text-gray-400"></i></div><p class="text-gray-500 font-medium">Data antrian tidak ditemukan.</p><p class="text-sm text-gray-400 mt-1">Pastikan nama atau nomor antrian yang dimasukkan benar.</p><div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg inline-block"><p class="text-sm text-yellow-700"><i class="fas fa-lightbulb mr-1"></i><strong>Tips:</strong> Gunakan nama lengkap sesuai KTP. Coba juga dengan nama lain yang mirip.</p></div>' + debugInfo + '</div>';

 // Tampilkan notifikasi cari kosong
 SwalHelper.info('Tidak Ditemukan', 'Data untuk "' + searchValue + '" tidak ditemukan dalam sistem');
 }
 })
 .catch(function(err) {
 console.error('Search Error:', err);
 if (resultsContainer) {
 resultsContainer.innerHTML = '<div class="text-center py-8"><div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-exclamation-triangle text-3xl text-red-500"></i></div><p class="text-gray-500 font-medium">Gagal mencari data.</p><p class="text-sm text-gray-400 mt-1">' + (err.message || 'Terjadi kesalahan koneksi') + '</p></div>';
 }
 // Gunakan notifikasi error
 SwalHelper.error('Gagal Mencari Data!', 'Gagal mencari data: ' + (err.message || 'Terjadi kesalahan koneksi'));
 });
 } catch (err) {
 console.error('Unexpected error in searchAntrian:', err);
 SwalHelper.error('Terjadi kesalahan!', 'Terjadi kesalahan: ' + err.message);
 }
 };

 // Fungsi pencarian pernikahan
 window.searchPernikahan = function(nomorAntrian, container) {
 var apiUrl = '{{ url('/api/pernikahan/status/') }}' + nomorAntrian;

 fetch(apiUrl, {
 method: 'GET',
 headers: {
 'Accept': 'application/json',
 'X-Requested-With': 'XMLHttpRequest'
 }
 })
 .then(function(response) {
 if (!response.ok) {
 if (response.status === 404) {
 return { success: false, message: 'Nomor antrian pernikahan tidak ditemukan' };
 }
 throw new Error('HTTP error! status: ' + response.status);
 }
 return response.json();
 })
 .then(function(data) {
 if (data.success && data.data) {
 window.renderPernikahanResult([data.data], container);
 SwalHelper.success('Ditemukan!', 'Data pernikahan ditemukan untuk "' + nomorAntrian + '"');
 } else {
 container.innerHTML = '<div class="text-center py-8 animate-fade-in"><div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-search text-3xl text-gray-400"></i></div><p class="text-gray-500 font-medium">Data pernikahan tidak ditemukan.</p><p class="text-sm text-gray-400 mt-1">Pastikan nomor antrian pernikahan yang dimasukkan benar (format: PNK-XXXXXXXX).</p></div>';
 SwalHelper.info('Tidak Ditemukan', 'Data pernikahan untuk "' + nomorAntrian + '" tidak ditemukan');
 }
 })
 .catch(function(err) {
 console.error('Search Pernikahan Error:', err);
 container.innerHTML = '<div class="text-center py-8"><div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-exclamation-triangle text-3xl text-red-500"></i></div><p class="text-gray-500 font-medium">Gagal mencari data pernikahan.</p><p class="text-sm text-gray-400 mt-1">' + (err.message || 'Terjadi kesalahan koneksi') + '</p></div>';
 SwalHelper.error('Gagal Mencari Data!', 'Gagal mencari data pernikahan: ' + (err.message || 'Terjadi kesalahan koneksi'));
 });
 };

 // Render hasil pencarian pernikahan
 window.renderPernikahanResult = function(results, container) {
 var statusPernikahanConfig = {
 'MENUNGGU_KONFIRMASI_KEAGAMAAN': { bg: 'bg-yellow-100', text: 'text-yellow-700', border: 'border-yellow-200', hex: '#f59e0b', label: 'Menunggu Konfirmasi Keagamaan', icon: 'fa-clock' },
 'DITOLAK_KEAGAMAAN': { bg: 'bg-red-100', text: 'text-red-700', border: 'border-red-200', hex: '#ef4444', label: 'Ditolak', icon: 'fa-times-circle' },
 'MENUNGGU_APPROVE_TANGGAL': { bg: 'bg-blue-100', text: 'text-blue-700', border: 'border-blue-200', hex: '#3b82f6', label: 'Menunggu Persetujuan Tanggal', icon: 'fa-calendar-check' },
 'TANGGAL_DITOLAK': { bg: 'bg-orange-100', text: 'text-orange-700', border: 'border-orange-200', hex: '#f97316', label: 'Tanggal Ditolak', icon: 'fa-calendar-times' },
 'TANGGAL_DISETUJUI': { bg: 'bg-green-100', text: 'text-green-700', border: 'border-green-200', hex: '#22c55e', label: 'Tanggal Disetujui', icon: 'fa-check-circle' },
 'DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI': { bg: 'bg-purple-100', text: 'text-purple-700', border: 'border-purple-200', hex: '#a855f7', label: 'Menunggu Verifikasi Dokumen', icon: 'fa-file-search' },
 'DOKUMEN_PERLU_PERBAIKAN': { bg: 'bg-orange-100', text: 'text-orange-700', border: 'border-orange-200', hex: '#f97316', label: 'Dokumen Perlu Perbaikan', icon: 'fa-exclamation-triangle' },
 'DOKUMEN_DIVERIFIKASI': { bg: 'bg-teal-100', text: 'text-teal-700', border: 'border-teal-200', hex: '#14b8a6', label: 'Dokumen Diverifikasi', icon: 'fa-file-check' },
 'SELESAI': { bg: 'bg-emerald-100', text: 'text-emerald-700', border: 'border-emerald-200', hex: '#10b981', label: 'Selesai', icon: 'fa-check-double' }
 };

 var html = results.map(function(item) {
 var statusConfig = statusPernikahanConfig[item.status] || statusPernikahanConfig['MENUNGGU_KONFIRMASI_KEAGAMAAN'];
 var nomorAntrian = item.nomor_antrian || '-';
 var namaPemohon = item.nama_pemohon || '-';
 var statusLabel = statusConfig.label;
 var statusIcon = statusConfig.icon;

 // Progress bar untuk step
 var stepWidth = (item.step / 5) * 100;
 var progressHtml = '<div class="mt-3">' +
 '<div class="flex justify-between text-xs text-gray-500 mb-1">' +
 '<span>Progress</span>' +
 '<span>Step ' + item.step + ' dari 5</span>' +
 '</div>' +
 '<div class="w-full bg-gray-200 rounded-full h-2">' +
 '<div class="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full transition-all duration-500" style="width: ' + stepWidth + '%"></div>' +
 '</div>' +
 '</div>';

 return '<div class="search-result-card bg-white border-2 ' + statusConfig.border + ' rounded-xl p-5 shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer" style="animation-delay: 0s" onclick="window.showPernikahanDetail(' + JSON.stringify(item).replace(/"/g, '&quot;') + ')">' +
 '<div class="flex items-center gap-2 mb-3">' +
 '<div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center text-white shadow-lg">' +
 '<i class="fas fa-ring text-xl"></i>' +
 '</div>' +
 '<div class="flex-1">' +
 '<div class="flex items-center gap-2 mb-1">' +
 '<span class="text-xs font-semibold text-purple-600 bg-purple-100 px-2 py-0.5 rounded">PERNIKAHAN</span>' +
 '</div>' +
 '<h3 class="font-bold text-xl text-purple-600">' + nomorAntrian + '</h3>' +
 '<p class="text-gray-800 font-semibold">' + namaPemohon + '</p>' +
 '</div>' +
 '<div class="flex items-center gap-2 px-3 py-2 rounded-full ' + statusConfig.bg + ' ' + statusConfig.text + ' border ' + statusConfig.border + ' font-bold text-xs shadow-sm">' +
 '<i class="fas ' + statusIcon + '"></i>' +
 '<span>' + statusLabel + '</span>' +
 '</div>' +
 '</div>' +
 progressHtml +
 '<div class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs text-gray-600">' +
 '<div><i class="fas fa-calendar mr-1 text-purple-500"></i>' + (item.tanggal_perkawinan || '-') + '</div>' +
 '<div><i class="fas fa-church mr-1 text-purple-500"></i>' + (item.nama_gereja || '-') + '</div>' +
 '</div>' +
 '</div>';
 }).join('');

 container.innerHTML = html;
 };

 // Tampilkan detail pernikahan
 window.showPernikahanDetail = function(pernikahan) {
 var statusPernikahanConfig = {
 'MENUNGGU_KONFIRMASI_KEAGAMAAN': { hex: '#f59e0b', label: 'Menunggu Konfirmasi Keagamaan', icon: 'fa-clock' },
 'DITOLAK_KEAGAMAAN': { hex: '#ef4444', label: 'Ditolak', icon: 'fa-times-circle' },
 'MENUNGGU_APPROVE_TANGGAL': { hex: '#3b82f6', label: 'Menunggu Persetujuan Tanggal', icon: 'fa-calendar-check' },
 'TANGGAL_DITOLAK': { hex: '#f97316', label: 'Tanggal Ditolak', icon: 'fa-calendar-times' },
 'TANGGAL_DISETUJUI': { hex: '#22c55e', label: 'Tanggal Disetujui', icon: 'fa-check-circle' },
 'DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI': { hex: '#a855f7', label: 'Menunggu Verifikasi Dokumen', icon: 'fa-file-search' },
 'DOKUMEN_PERLU_PERBAIKAN': { hex: '#f97316', label: 'Dokumen Perlu Perbaikan', icon: 'fa-exclamation-triangle' },
 'DOKUMEN_DIVERIFIKASI': { hex: '#14b8a6', label: 'Dokumen Diverifikasi', icon: 'fa-file-check' },
 'SELESAI': { hex: '#10b981', label: 'Selesai', icon: 'fa-check-double' }
 };

 var statusConfig = statusPernikahanConfig[pernikahan.status] || statusPernikahanConfig['MENUNGGU_KONFIRMASI_KEAGAMAAN'];
 var stepWidth = (pernikahan.step / 5) * 100;

 var modalContent = `
 <div class="p-6">
 <div class="flex items-center justify-between mb-4">
 <div class="flex items-center gap-3">
 <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center text-white">
 <i class="fas fa-ring text-xl"></i>
 </div>
 <div>
 <span class="text-xs font-semibold text-purple-600 bg-purple-100 px-2 py-0.5 rounded">PERNIKAHAN</span>
 <h3 class="font-bold text-xl text-gray-800">${pernikahan.nomor_antrian}</h3>
 </div>
 </div>
 <button onclick="Swal.close()" class="text-gray-400 hover:text-gray-600">
 <i class="fas fa-times text-xl"></i>
 </button>
 </div>

 <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-4 mb-4">
 <div class="flex items-center gap-3">
 <div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-2xl" style="background-color: ${statusConfig.hex}">
 <i class="fas ${statusConfig.icon}"></i>
 </div>
 <div>
 <p class="font-bold text-lg" style="color: ${statusConfig.hex}">${statusConfig.label}</p>
 <p class="text-xs text-gray-500">Status saat ini</p>
 </div>
 </div>
 </div>

 <div class="mb-4">
 <div class="flex justify-between text-xs text-gray-500 mb-1">
 <span>Progress Pengajuan</span>
 <span>Step ${pernikahan.step} dari 5</span>
 </div>
 <div class="w-full bg-gray-200 rounded-full h-3">
 <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-3 rounded-full transition-all" style="width: ${stepWidth}%"></div>
 </div>
 </div>

 <div class="bg-gray-50 rounded-xl p-4 space-y-3 mb-4">
 <div class="flex justify-between">
 <span class="text-xs text-gray-500">Nama Pemohon</span>
 <span class="font-semibold text-gray-800 text-sm">${pernikahan.nama_pemohon || '-'}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-xs text-gray-500">Mempelai Pria</span>
 <span class="font-semibold text-gray-800 text-sm">${pernikahan.nama_mempelai_pria || '-'}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-xs text-gray-500">Mempelai Wanita</span>
 <span class="font-semibold text-gray-800 text-sm">${pernikahan.nama_mempelai_wanita || '-'}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-xs text-gray-500">Tanggal Perkawinan</span>
 <span class="font-semibold text-gray-800 text-sm">${pernikahan.tanggal_perkawinan || '-'}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-xs text-gray-500">Gereja/Lembaga</span>
 <span class="font-semibold text-gray-800 text-sm">${pernikahan.nama_gereja || '-'}</span>
 </div>
 </div>

 ${pernikahan.catatan_keagamaan || pernikahan.catatan_admin || pernikahan.alasan_ditolak ? `
 <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
 <p class="text-xs font-semibold text-yellow-800 mb-2"><i class="fas fa-info-circle mr-1"></i>Catatan</p>
 ${pernikahan.catatan_keagamaan ? '<p class="text-xs text-gray-700 mb-1"><strong>Keagamaan:</strong> ' + pernikahan.catatan_keagamaan + '</p>' : ''}
 ${pernikahan.catatan_admin ? '<p class="text-xs text-gray-700 mb-1"><strong>Admin:</strong> ' + pernikahan.catatan_admin + '</p>' : ''}
 ${pernikahan.alasan_ditolak ? '<p class="text-xs text-red-700"><strong>Alasan Ditolak:</strong> ' + pernikahan.alasan_ditolak + '</p>' : ''}
 </div>
 ` : ''}

 <div class="flex gap-2">
 <button onclick="window.copyNomorAntrianToClipboard('${pernikahan.nomor_antrian}'); Swal.close();" class="flex-1 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700">
 <i class="fas fa-copy mr-1"></i> Salin Nomor
 </button>
 <button onclick="Swal.close()" class="flex-1 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold text-sm hover:bg-gray-300">
 Tutup
 </button>
 </div>
 </div>
 `;

 Swal.fire({
 html: modalContent,
 showConfirmButton: false,
 customClass: {
 popup: 'rounded-2xl'
 }
 });
 };

 // Render Search Results
 window.__antrianRegistry = window.__antrianRegistry || {};

 // Helper: cari URL dokumen PDF final yang sudah diupload admin
 // Berlaku untuk semua layanan KECUALI pernikahan (pernikahan tetap pakai modal detail)
 window.findDokumenFinalUrl = function(data) {
 if (!data) return null;
 // Skip pernikahan: jangan auto-open PDF, biarkan modal detail yang menangani
 if (data.pernikahan && data.pernikahan.status) return null;
 // Cek lacak_berkas: ambil yang punya download_url, prioritaskan paling baru
 if (Array.isArray(data.lacak_berkas) && data.lacak_berkas.length > 0) {
 var withFile = data.lacak_berkas.filter(function(lb) { return lb && lb.download_url; });
 if (withFile.length > 0) {
 withFile.sort(function(a, b) {
 var da = new Date(a.created_at || a.tanggal || 0).getTime();
 var db = new Date(b.created_at || b.tanggal || 0).getTime();
 return db - da;
 });
 return withFile[0].download_url;
 }
 }
 return null;
 };

 window.showAntrianDetailById = function(key) {
 var data = window.__antrianRegistry[key];
 console.log('[Lihat] key=', key, 'data=', data);
 if (!data) { console.warn('Antrian tidak ditemukan di registry:', key, 'available:', Object.keys(window.__antrianRegistry)); return; }

 // Jika sudah ada dokumen PDF final yang diupload admin, buka langsung di tab baru
 var pdfUrl = window.findDokumenFinalUrl(data);
 if (pdfUrl) {
 var viewUrl = pdfUrl + (pdfUrl.indexOf('?') === -1 ? '?' : '&') + 'inline=1';
 console.log('[Lihat] Membuka dokumen final:', viewUrl);
 window.open(viewUrl, '_blank', 'noopener');
 return;
 }

 // Fallback: belum ada dokumen final ? tampilkan modal detail
 if (typeof window.showAntrianDetail === 'function') {
 window.showAntrianDetail(data);
 } else {
 console.error('window.showAntrianDetail tidak terdefinisi');
 }
 };

 // Event delegation: tangani klik tombol Lihat & kartu hasil
 if (!window.__lacakClickBound) {
 window.__lacakClickBound = true;
 document.addEventListener('click', function(e) {
 var btn = e.target.closest('[data-action="lihat-antrian"]');
 if (btn) {
 e.preventDefault();
 e.stopPropagation();
 var key = btn.getAttribute('data-antrian-key');
 window.showAntrianDetailById(key);
 return;
 }
 var card = e.target.closest('[data-card-antrian-key]');
 if (card) {
 var k = card.getAttribute('data-card-antrian-key');
 window.showAntrianDetailById(k);
 }
 });
 }

 window.renderSearchResults = function(results) {
 console.log('Rendering results:', results);
 
 if (!results || results.length === 0) {
 document.getElementById('searchResults').innerHTML = 
 '<div class="text-center py-8 animate-fade-in"><div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-inbox text-3xl text-gray-400"></i></div><p class="text-gray-500 font-medium">Tidak ada data antrian.</p></div>'; return;
 }
 
 var html = results.map(function(antrian, index) {
 // Daftarkan ke registry agar tombol Lihat dapat membuka detail
 var regKey = antrian.nomor_antrian || ('idx-' + index);
 window.__antrianRegistry[regKey] = antrian;
 var statusColors = {
 'Menunggu': { bg: 'bg-amber-100', text: 'text-amber-700', border: 'border-amber-200', hex: '#f59e0b' },
 'Dokumen Diterima': { bg: 'bg-green-100', text: 'text-green-700', border: 'border-green-200', hex: '#22c55e' },
 'Verifikasi Data': { bg: 'bg-indigo-100', text: 'text-indigo-700', border: 'border-indigo-200', hex: '#6366f1' },
 'Proses Cetak': { bg: 'bg-purple-100', text: 'text-purple-700', border: 'border-purple-200', hex: '#a855f7' },
 'Siap Pengambilan': { bg: 'bg-emerald-100', text: 'text-emerald-700', border: 'border-emerald-200', hex: '#10b981' },
 'Selesai': { bg: 'bg-emerald-100', text: 'text-emerald-700', border: 'border-emerald-200', hex: '#10b981' },
 'Ditolak': { bg: 'bg-red-100', text: 'text-red-700', border: 'border-red-200', hex: '#ef4444' },
 'Dibatalkan': { bg: 'bg-rose-100', text: 'text-rose-700', border: 'border-rose-200', hex: '#f43f5e' }
 };
 var statusIcons = {
 'Menunggu': 'fa-clock',
 'Dokumen Diterima': 'fa-file-check',
 'Verifikasi Data': 'fa-search',
 'Proses Cetak': 'fa-print',
 'Siap Pengambilan': 'fa-box-open',
 'Selesai': 'fa-check-circle',
 'Ditolak': 'fa-ban',
 'Dibatalkan': 'fa-times'
 };

 // Konfigurasi khusus untuk status LayananPernikahan
 var statusPernikahanConfig = {
 'MENUNGGU_KONFIRMASI_KEAGAMAAN': { bg: 'bg-yellow-100', text: 'text-yellow-700', border: 'border-yellow-200', hex: '#f59e0b', label: 'Menunggu Konfirmasi Keagamaan', icon: 'fa-clock' },
 'DITOLAK_KEAGAMAAN': { bg: 'bg-red-100', text: 'text-red-700', border: 'border-red-200', hex: '#ef4444', label: 'Ditolak', icon: 'fa-times-circle' },
 'MENUNGGU_APPROVE_TANGGAL': { bg: 'bg-blue-100', text: 'text-blue-700', border: 'border-blue-200', hex: '#3b82f6', label: 'Menunggu Persetujuan Tanggal', icon: 'fa-calendar-check' },
 'TANGGAL_DITOLAK': { bg: 'bg-orange-100', text: 'text-orange-700', border: 'border-orange-200', hex: '#f97316', label: 'Tanggal Ditolak', icon: 'fa-calendar-times' },
 'TANGGAL_DISETUJUI': { bg: 'bg-green-100', text: 'text-green-700', border: 'border-green-200', hex: '#22c55e', label: 'Tanggal Disetujui', icon: 'fa-check-circle' },
 'DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI': { bg: 'bg-purple-100', text: 'text-purple-700', border: 'border-purple-200', hex: '#a855f7', label: 'Menunggu Verifikasi Dokumen', icon: 'fa-search' },
 'DOKUMEN_PERLU_PERBAIKAN': { bg: 'bg-orange-100', text: 'text-orange-700', border: 'border-orange-200', hex: '#f97316', label: 'Dokumen Perlu Perbaikan', icon: 'fa-exclamation-triangle' },
 'DOKUMEN_DIVERIFIKASI': { bg: 'bg-teal-100', text: 'text-teal-700', border: 'border-teal-200', hex: '#14b8a6', label: 'Dokumen Diverifikasi', icon: 'fa-clipboard-check' },
 'SELESAI': { bg: 'bg-emerald-100', text: 'text-emerald-700', border: 'border-emerald-200', hex: '#10b981', label: 'Selesai', icon: 'fa-check-double' }
 };

 var nomorAntrian = antrian.nomor_antrian || '-';
 var namaLengkap = antrian.nama_lengkap || '-';
 var namaLayanan = (antrian.layanan && antrian.layanan.nama_layanan) ? antrian.layanan.nama_layanan : 'Layanan Umum';

 // Jika ada data pernikahan, prioritaskan status pernikahan agar real-time
 var hasPernikahan = !!(antrian.pernikahan && antrian.pernikahan.status);
 var statusAntrian, statusStyle, icon;

 if (hasPernikahan) {
 var pCfg = statusPernikahanConfig[antrian.pernikahan.status] || statusPernikahanConfig['MENUNGGU_KONFIRMASI_KEAGAMAAN'];
 statusAntrian = antrian.pernikahan.status_label || pCfg.label;
 statusStyle = { bg: pCfg.bg, text: pCfg.text, border: pCfg.border, hex: pCfg.hex };
 icon = pCfg.icon;
 } else {
 statusAntrian = antrian.status_antrian || 'Menunggu';
 statusStyle = statusColors[statusAntrian] || statusColors['Menunggu'];
 icon = statusIcons[statusAntrian] || 'fa-info-circle';
 }

 var prefixText = nomorAntrian.substring(0, 2);

 // Progress step (1-5) berdasarkan status antrian (untuk layanan non-pernikahan)
 var stepMap = {
 'Menunggu': 1,
 'Dokumen Diterima': 2,
 'Verifikasi Data': 3,
 'Proses Cetak': 4,
 'Siap Pengambilan': 5,
 'Selesai': 5,
 'Ditolak': 5,
 'Dibatalkan': 5
 };
//  var currentStep = hasPernikahan ? (antrian.pernikahan.step || 1) : (stepMap[statusAntrian] || 1);
var currentStep = hasPernikahan 
    ? (antrian.pernikahan.step || stepMap[statusAntrian] || 1) 
    : (stepMap[statusAntrian] || 1);
 var stepWidth = (currentStep / 5) * 100;
 var progressHtml = '<div class="mt-3">' +
 '<div class="flex justify-between text-xs text-gray-500 mb-1">' +
 '<span>Progress</span>' +
 '<span>Step ' + currentStep + ' dari 5</span>' +
 '</div>' +
 '<div class="w-full bg-gray-200 rounded-full h-2">' +
 '<div class="bg-gradient-to-r from-green-500 to-emerald-500 h-2 rounded-full transition-all duration-500" style="width: ' + stepWidth + '%"></div>' +
 '</div>' +
 '</div>';

 // Info grid (NIK + Tanggal Pengajuan)
 var nikText = antrian.nik || '-';
 var tglPengajuan = '-';
 if (antrian.created_at) {
 try {
 tglPengajuan = new Date(antrian.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
 } catch (e) { tglPengajuan = antrian.created_at; }
 }
 var infoGridHtml = '<div class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs text-gray-600">' +
 '<div><i class="fas fa-id-card mr-1 text-green-500"></i>' + nikText + '</div>' +
 '<div><i class="fas fa-calendar mr-1 text-green-500"></i>' + tglPengajuan + '</div>' +
 '</div>';

 var timelineHtml = '';
 if (antrian.lacak_berkas && antrian.lacak_berkas.length > 0) {
 var dots = antrian.lacak_berkas.slice(-5).map(function(lb) {
 var lbStatus = lb.status || '-';
 var lbColor = statusColors[lbStatus] ? statusColors[lbStatus].hex : '#6b7280';
 return '<div class="w-3 h-3 rounded-full border-2 border-white shadow" style="background-color: ' + lbColor + '"></div>';
 }).join('');
 timelineHtml = '<div class="mt-4 pt-4 border-t border-gray-100"><p class="text-xs text-gray-500 mb-2"><i class="fas fa-history mr-1"></i>Riwayat: ' + antrian.lacak_berkas.length + ' update status</p><div class="flex gap-1">' + dots + '</div></div>';
 } else if (hasPernikahan && antrian.pernikahan.history_count > 0) {
 timelineHtml = '<div class="mt-4 pt-4 border-t border-gray-100"><p class="text-xs text-gray-500"><i class="fas fa-history mr-1"></i>Riwayat: ' + antrian.pernikahan.history_count + ' update status</p></div>';
 }

 // Dokumen final dari pernikahan (Akta + 3 KK)
 var dokumenFinalHtml = '';
 if (hasPernikahan && antrian.pernikahan.dokumen_final) {
 var df = antrian.pernikahan.dokumen_final;
 var dfItems = [
 { key: 'akta_pernikahan', label: 'Akta Pernikahan' },
 { key: 'kk_pasangan', label: 'KK Baru  -  Pasangan' },
 { key: 'kk_ortu_pria', label: 'KK Baru  -  Ortu Pria' },
 { key: 'kk_ortu_wanita', label: 'KK Baru  -  Ortu Wanita' }
 ];
 var anyUploaded = dfItems.some(function(it) { return !!df[it.key]; });

 if (anyUploaded) {
 var rows = dfItems.map(function(it) {
    var url = df[it.key];
    if (url) {
        var downloadUrl = url + (url.indexOf('?') === -1 ? '?' : '&') + 'download=1';
        return '<div style="display:flex;align-items:center;justify-content:space-between;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:8px 12px;margin-bottom:6px;">' +
            '<span style="font-size:12px;color:#374151;font-weight:500;display:flex;align-items:center;gap:6px;">' +
            '<i class="fas fa-file-pdf" style="color:#059669;"></i>' + it.label + '</span>' +
            '<a href="' + downloadUrl + '" onclick="event.stopPropagation()" ' +
            'style="display:inline-flex;align-items:center;gap:6px;background:#2563eb;color:#ffffff;font-size:12px;font-weight:600;padding:5px 12px;border-radius:6px;text-decoration:none;white-space:nowrap;">' +
            '<i class="fas fa-download"></i> Download</a>' +
            '</div>';
    }
    return '<div style="display:flex;align-items:center;justify-content:space-between;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:8px 12px;margin-bottom:6px;">' +
        '<span style="font-size:12px;color:#9ca3af;display:flex;align-items:center;gap:6px;">' +
        '<i class="fas fa-file-pdf" style="color:#d1d5db;"></i>' + it.label + '</span>' +
        '<span style="font-size:12px;color:#9ca3af;font-style:italic;">Belum tersedia</span>' +
        '</div>';
}).join('');

 var uploadedAt = antrian.pernikahan.dokumen_final_uploaded_at
 ? '<p class="text-[10px] text-gray-400 mt-2"><i class="fas fa-clock mr-1"></i>Diupload: ' + antrian.pernikahan.dokumen_final_uploaded_at + '</p>'
 : '';

 dokumenFinalHtml = '<div class="mt-4 pt-4 border-t border-gray-100">' +
 '<p class="text-xs font-semibold text-gray-700 mb-2"><i class="fas fa-folder-open text-emerald-600 mr-1"></i>Dokumen Hasil Penerbitan Disdukcapil</p>' +
 '<div class="space-y-2">' + rows + '</div>' +
 uploadedAt +
 '</div>';
 }
 }

 return '<div class="search-result-card bg-white border-2 ' + statusStyle.border + ' rounded-xl p-5 shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer" style="animation-delay: ' + (index * 0.1) + 's" data-card-antrian-key="' + regKey + '">' +
 '<div class="flex items-center gap-2 mb-3">' +
 '<div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">' +
 '<i class="fas fa-file-alt text-xl"></i>' +
 '</div>' +
 '<div class="flex-1 min-w-0">' +
 '<div class="flex items-center gap-2 mb-1">' +
 '<span class="text-xs font-semibold text-green-600 bg-green-100 px-2 py-0.5 rounded uppercase tracking-wide">' + namaLayanan + '</span>' +
 '</div>' +
 '<h3 class="font-bold text-xl text-green-600 truncate">' + nomorAntrian + '</h3>' +
 '<p class="text-gray-800 font-semibold truncate">' + namaLengkap + '</p>' +
 '</div>' +
 '<div class="flex items-center gap-2 px-3 py-2 rounded-full ' + statusStyle.bg + ' ' + statusStyle.text + ' border ' + statusStyle.border + ' font-bold text-xs shadow-sm whitespace-nowrap">' +
 '<i class="fas ' + icon + '"></i>' +
 '<span>' + statusAntrian + '</span>' +
 '</div>' +
 '</div>' +
 progressHtml +
 infoGridHtml +
 timelineHtml +
 dokumenFinalHtml +
 '<div class="mt-4 pt-3 border-t border-gray-100 flex justify-end">' +
 '<button type="button" data-action="lihat-antrian" data-antrian-key="' + regKey + '" class="px-4 py-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-sm inline-flex items-center gap-2 transition-all">' +
 '<i class="fas fa-eye"></i><span>Lihat</span>' +
 '</button>' +
 '</div>' +
 '</div>';
 }).join('');
 document.getElementById('searchResults').innerHTML = html;
 };
</script>

<script src="{{ asset('js/antrian-ocr.js') }}?v={{ time() }}" defer></script>
<script>
 // Load Statistics on Page Load
 function loadStatistics() {
 fetch('{{ route('antrian.statistik') }}')
 .then(response => response.json())
 .then(data => {
 if (data.success) {
 animateCounter('totalToday', data.data.total_antrian);
 animateCounter('waitingToday', data.data.antrian_menunggu);
 animateCounter('processingToday', data.data.antrian_diproses);
 animateCounter('completedToday', data.data.antrian_selesai);
 }
 })
 .catch(err => console.error('Gagal memuat statistik:', err));
 }

 // Counter Animation
 function animateCounter(elementId, target) {
 const element = document.getElementById(elementId);
 const duration = 1000;
 const steps = 30;
 const increment = target / steps;
 let current = 0;

 const timer = setInterval(() => {
 current += increment;
 if (current >= target) {
 element.textContent = target;
 clearInterval(timer);
 } else {
 element.textContent = Math.floor(current);
 }
 }, duration / steps);
 }

 // Pengiriman form & alur tiket: dihandle oleh public/js/antrian-ocr.js (draft ? OCR ? finalize).

 // Confetti Animation
 function createConfetti() {
 const container = document.getElementById('confetti-container');
 const colors = ['#28A745', '#22c55e', '#36B37E', '#FFAB00', '#FF5630', '#6554C0'];

 for (let i = 0; i < 50; i++) {
 const confetti = document.createElement('div');
 confetti.className = 'confetti';
 confetti.style.left = Math.random() * 100 + 'vw';
 confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
 confetti.style.animationDelay = Math.random() * 2 + 's';
 confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
 container.appendChild(confetti);

 setTimeout(() => confetti.remove(), 4000);
 }
 }

 // Salin Nomor Antrian ke Clipboard
 function copyTicketNumber() {
 const ticketNumber = document.getElementById('ticketNumber').textContent;
 const copyBtn = document.getElementById('copyBtn');
 
 if (!ticketNumber || ticketNumber === '-') {
 Swal.fire({
 icon: 'error',
 title: 'Gagal Menyalin',
 text: 'Nomor antrian tidak ditemukan',
 confirmButtonColor: '#16a34a',
 });
 return;
 }
 
 navigator.clipboard.writeText(ticketNumber).then(function() {
 const originalText = copyBtn.innerHTML;
 copyBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Tersalin!';
 copyBtn.classList.remove('from-gray-100', 'to-gray-200', 'hover:from-gray-200', 'hover:to-gray-300');
 copyBtn.classList.add('from-green-500', 'to-green-600', 'text-white');
 
 Swal.fire({
 icon: 'success',
 title: 'Berhasil Disalin!',
 text: 'Nomor antrian ' + ticketNumber + ' telah disalin',
 timer: 2000,
 showConfirmButton: false,
 toast: true,
 position: 'top-end',
 });
 
 setTimeout(function() {
 copyBtn.innerHTML = originalText;
 copyBtn.classList.remove('from-green-500', 'to-green-600', 'text-white');
 copyBtn.classList.add('from-gray-100', 'to-gray-200', 'hover:from-gray-200', 'hover:to-gray-300');
 }, 2000);
 }).catch(function() {
 // Fallback
 const textarea = document.createElement('textarea');
 textarea.value = ticketNumber;
 textarea.style.position = 'fixed';
 textarea.style.opacity = '0';
 document.body.appendChild(textarea);
 textarea.select();
 try {
 document.execCommand('copy');
 Swal.fire({
 icon: 'success',
 title: 'Berhasil Disalin!',
 text: 'Nomor antrian ' + ticketNumber + ' telah disalin',
 timer: 2000,
 showConfirmButton: false,
 toast: true,
 position: 'top-end',
 });
 } catch (err) {
 Swal.fire({
 icon: 'error',
 title: 'Gagal Menyalin',
 text: 'Tidak dapat menyalin nomor antrian',
 confirmButtonColor: '#16a34a',
 });
 }
 document.body.removeChild(textarea);
 });
 }

 // resetForm: didefinisikan di antrian-ocr.js (konfirmasi Swal + reset multi-step).
 
 // Show Antrian Detail dengan SweetAlert - desain mengikuti modal pernikahan
 window.showAntrianDetail = function(antrian) {
 console.log('Showing antrian detail:', antrian);

 var statusConfigMap = {
 'Menunggu': { hex: '#f59e0b', label: 'Menunggu', icon: 'fa-clock', step: 1 },
 'Dokumen Diterima': { hex: '#3b82f6', label: 'Dokumen Diterima', icon: 'fa-file-check', step: 2 },
 'Verifikasi Data': { hex: '#6366f1', label: 'Verifikasi Data', icon: 'fa-search', step: 3 },
 'Proses Cetak': { hex: '#a855f7', label: 'Proses Cetak', icon: 'fa-print', step: 4 },
 'Siap Pengambilan': { hex: '#10b981', label: 'Siap Pengambilan', icon: 'fa-box-open', step: 5 },
 'Selesai': { hex: '#22c55e', label: 'Selesai', icon: 'fa-check-circle', step: 5 },
 'Ditolak': { hex: '#ef4444', label: 'Ditolak', icon: 'fa-ban', step: 5 },
 'Dibatalkan': { hex: '#f43f5e', label: 'Dibatalkan', icon: 'fa-times', step: 5 }
 };

 var nomorAntrian = antrian.nomor_antrian || '-';
 var namaLengkap = antrian.nama_lengkap || '-';
 var nik = antrian.nik || '-';
 var namaLayanan = (antrian.layanan && antrian.layanan.nama_layanan) ? antrian.layanan.nama_layanan : 'Layanan Umum';
 var statusAntrian = antrian.status_antrian || 'Menunggu';
 var statusConfig = statusConfigMap[statusAntrian] || statusConfigMap['Menunggu'];
 var stepVal = statusConfig.step;
 var stepWidth = (statusConfig.step / 5) * 100;

 // Format tanggal pembuatan
 var createdDate = '-';
 if (antrian.created_at) {
 try {
 createdDate = new Date(antrian.created_at).toLocaleString('id-ID', {
 day: 'numeric', month: 'long', year: 'numeric',
 hour: '2-digit', minute: '2-digit'
 });
 } catch (e) { createdDate = antrian.created_at; }
 }

 // Dokumen final: ambil semua lacak_berkas yang punya file/download_url
 var dokumenItems = [];
 if (Array.isArray(antrian.lacak_berkas)) {
 antrian.lacak_berkas.forEach(function(lb) {
 if (lb && lb.download_url) {
 var tgl = '-';
 if (lb.tanggal) {
 try {
 tgl = new Date(lb.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
 } catch (e) { tgl = lb.tanggal; }
 } else if (lb.created_at) {
 try {
 tgl = new Date(lb.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
 } catch (e) { tgl = lb.created_at; }
 }
 dokumenItems.push({
 label: lb.status || 'Dokumen',
 tanggal: tgl,
 url: lb.download_url
 });
 }
 });
 }

 var dokumenHtml = '';
 if (dokumenItems.length > 0) {
 var rows = dokumenItems.map(function(it) {
 var viewUrl = it.url + (it.url.indexOf('?') === -1 ? '?' : '&') + 'inline=1';
 return '<div class="flex items-center justify-between bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2 text-xs">' +
 '<div class="flex items-center gap-2">' +
 '<i class="fas fa-file-pdf text-emerald-600"></i>' +
 '<div>' +
 '<p class="text-gray-700 font-semibold">' + it.label + '</p>' +
 '<p class="text-[10px] text-gray-400">' + it.tanggal + '</p>' +
 '</div>' +
 '</div>' +
 '<a href="' + viewUrl + '" target="_blank" rel="noopener" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-semibold shadow-sm inline-flex items-center gap-1 transition">' +
 '<i class="fas fa-eye"></i> Lihat Dokumen' +
 '</a>' +
 '</div>';
 }).join('');

 dokumenHtml =
 '<div class="mb-4">' +
 '<p class="text-xs font-semibold text-gray-700 mb-2"><i class="fas fa-folder-open text-emerald-600 mr-1"></i>Dokumen Hasil Penerbitan Disdukcapil</p>' +
 '<div class="space-y-2">' + rows + '</div>' +
 '</div>';
 }

 var modalContent =
 '<div class="p-6">' +
 '<div class="flex items-center justify-between mb-4">' +
 '<div class="flex items-center gap-3">' +
 '<div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-white">' +
 '<i class="fas fa-file-alt text-xl"></i>' +
 '</div>' +
 '<div>' +
 '<span class="text-xs font-semibold text-green-600 bg-green-100 px-2 py-0.5 rounded">ANTRIAN ONLINE</span>' +
 '<h3 class="font-bold text-xl text-gray-800">' + nomorAntrian + '</h3>' +
 '</div>' +
 '</div>' +
 '<button onclick="Swal.close()" class="text-gray-400 hover:text-gray-600">' +
 '<i class="fas fa-times text-xl"></i>' +
 '</button>' +
 '</div>' +

 '<div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 mb-4">' +
 '<div class="flex items-center gap-3">' +
 '<div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-2xl" style="background-color: ' + statusConfig.hex + '">' +
 '<i class="fas ' + statusConfig.icon + '"></i>' +
 '</div>' +
 '<div>' +
 '<p class="font-bold text-lg" style="color: ' + statusConfig.hex + '">' + statusConfig.label + '</p>' +
 '<p class="text-xs text-gray-500">Status saat ini</p>' +
 '</div>' +
 '</div>' +
 '</div>' +

 '<div class="mb-4">' +
 '<div class="flex justify-between text-xs text-gray-500 mb-1">' +
 '<span>Progress Pengajuan</span>' +
 '<span>Step ' + statusConfig.step + ' dari 5</span>' +
 '</div>' +
 '<div class="w-full bg-gray-200 rounded-full h-3">' +
 '<div class="bg-gradient-to-r from-green-500 to-emerald-500 h-3 rounded-full transition-all" style="width: ' + stepWidth + '%"></div>' +
 '</div>' +
 '</div>' +

 '<div class="bg-gray-50 rounded-xl p-4 space-y-3 mb-4">' +
 '<div class="flex justify-between">' +
 '<span class="text-xs text-gray-500">Nama Pemohon</span>' +
 '<span class="font-semibold text-gray-800 text-sm">' + namaLengkap + '</span>' +
 '</div>' +
 '<div class="flex justify-between">' +
 '<span class="text-xs text-gray-500">NIK</span>' +
 '<span class="font-mono text-gray-800 text-sm">' + nik + '</span>' +
 '</div>' +
 '<div class="flex justify-between">' +
 '<span class="text-xs text-gray-500">Layanan</span>' +
 '<span class="font-semibold text-gray-800 text-sm">' + namaLayanan + '</span>' +
 '</div>' +
 '<div class="flex justify-between">' +
 '<span class="text-xs text-gray-500">Tanggal Pengajuan</span>' +
 '<span class="font-semibold text-gray-800 text-sm">' + createdDate + '</span>' +
 '</div>' +
 '</div>' +

 dokumenHtml +

 '<div class="flex gap-2">' +
 '<button onclick="window.copyNomorAntrianToClipboard(\'' + nomorAntrian + '\'); Swal.close();" class="flex-1 py-2 bg-green-600 text-white rounded-lg font-semibold text-sm hover:bg-green-700">' +
 '<i class="fas fa-copy mr-1"></i> Salin Nomor' +
 '</button>' +
 '<button onclick="Swal.close()" class="flex-1 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold text-sm hover:bg-gray-300">' +
 'Tutup' +
 '</button>' +
 '</div>' +
 '</div>';

 Swal.fire({
 html: modalContent,
 showConfirmButton: false,
 width: '500px',
 customClass: { popup: 'rounded-2xl' }
 });
 }

 // Copy Nomor Antrian to Clipboard
 window.copyNomorAntrianToClipboard = function(text) {
 if (navigator.clipboard && navigator.clipboard.writeText) {
 navigator.clipboard.writeText(text).then(() => {
 Swal.fire({
 icon: 'success',
 title: 'Berhasil Disalin!',
 text: `Nomor antrian ${text} telah disalin`,
 timer: 2000,
 showConfirmButton: false,
 toast: true,
 position: 'top-end'
 });
 }).catch(() => {
 fallbackCopyNomor(text);
 });
 } else {
 fallbackCopyNomor(text);
 }
 }

 function fallbackCopyNomor(text) {
 const textarea = document.createElement('textarea');
 textarea.value = text;
 textarea.style.position = 'fixed';
 textarea.style.opacity = '0';
 document.body.appendChild(textarea);
 textarea.select();
 try {
 document.execCommand('copy');
 Swal.fire({
 icon: 'success',
 title: 'Berhasil Disalin!',
 text: `Nomor antrian ${text} telah disalin`,
 timer: 2000,
 showConfirmButton: false,
 toast: true,
 position: 'top-end'
 });
 } catch (err) {
 Swal.fire({
 icon: 'error',
 title: 'Gagal Menyalin',
 text: 'Tidak dapat menyalin nomor antrian',
 confirmButtonColor: '#16a34a',
 });
 }
 document.body.removeChild(textarea);
 }
 
 // Enter key support for search
 document.addEventListener('DOMContentLoaded', function() {
 var searchInput = document.getElementById('searchInput');
 if (searchInput) {
 searchInput.addEventListener('keypress', function(e) {
 if (e.key === 'Enter') {
 e.preventDefault();
 searchAntrian();
 }
 });
 }

 // Button click handler untuk Cari Antrian
 var btnCari = document.getElementById('btnCariAntrian');
 if (btnCari) {
 btnCari.addEventListener('click', function(e) {
 e.preventDefault();
 e.stopPropagation();
 console.log('Button Cari Antrian clicked (addEventListener)');
 searchAntrian();
 });
 console.log('Button event listener attached to btnCariAntrian');
 } else {
 console.error('Button btnCariAntrian not found');
 }

 // Load statistics on page load
 loadStatistics();

 // Debug: Check if searchAntrian function exists
 console.log('searchAntrian function exists:', typeof searchAntrian === 'function');
 });

 // Global error handler untuk debugging
 window.addEventListener('error', function(e) {
 console.error('Global error:', e.message, 'at', e.filename, 'line', e.lineno);
 });
</script>
@endpush
