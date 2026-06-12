@extends('layouts.admin')

@section('title', 'Penerbitan Akta Pernikahan')

@section('content')
<div class="min-h-screen bg-gray-50">
 {{-- Page Header --}}
 <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-2xl p-6 md:p-8 text-white mb-6 reveal shadow-lg">
 <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
 <div>
 <h1 class="text-2xl md:text-3xl font-bold mb-2">Penerbitan Akta Pernikahan</h1>
 <p class="text-blue-100 text-lg">Kelola permohonan pencatatan perkawinan</p>
 </div>
 <div class="flex flex-col gap-2 text-sm">
 <div class="flex items-center gap-2">
 <i class="fas fa-calendar-alt"></i>
 <span id="currentDate">{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
 </div>     
 <div class="flex items-center gap-2">
 <i class="fas fa-clock"></i>
 <span id="currentTime">{{ now()->format('H:i') }} WIB</span>
 </div>
 </div>
 </div>
 </div>

 {{-- Quick Stats --}}
 @php
 $statistics = [
 'total' => \App\Models\LayananPernikahan::count(),
 'pending_keagamaan' => \App\Models\LayananPernikahan::where('status', \App\Models\LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN)->count(),
 'pending_admin' => \App\Models\LayananPernikahan::menungguApproveTanggal()->count(),
 'upload' => \App\Models\LayananPernikahan::where('status', \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI)->count(),
 'verifikasi' => \App\Models\LayananPernikahan::menungguVerifikasiDokumen()->count(),
 'selesai' => \App\Models\LayananPernikahan::where('status', \App\Models\LayananPernikahan::STATUS_SELESAI)->count(),
 'ditolak' => \App\Models\LayananPernikahan::whereIn('status', [
 \App\Models\LayananPernikahan::STATUS_TANGGAL_DITOLAK,
 \App\Models\LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN,
 \App\Models\LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
 ])->count(),
 ];

 // Persiapan data kalender untuk JavaScript (mirip keagamaan)
 $approvedPernikahan = \App\Models\LayananPernikahan::where('status', \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI)
 ->whereNotNull('tanggal_perkawinan')
 ->select(['pernikahan_id', 'nomor_antrian', 'nama_mempelai_pria', 'tanggal_perkawinan', 'nama_gereja'])
 ->get();

 $calendarData = [];
 foreach ($approvedPernikahan as $p) {
 $dateKey = $p->tanggal_perkawinan->format('Y-m-d');
 if (!isset($calendarData[$dateKey])) {
 $calendarData[$dateKey] = [];
 }
 $calendarData[$dateKey][] = [
 'id' => $p->pernikahan_id,
 'nomor_antrian' => $p->nomor_antrian,
 'nama_pria' => $p->nama_mempelai_pria,
 'gereja' => $p->nama_gereja,
 ];
 }
 @endphp

 <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
 <div class="stat-card bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="filterByStatus('', event)">
 <div class="flex items-center justify-between mb-2">
 <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
 <i class="fas fa-file-alt text-gray-600"></i>
 </div>
 </div>
 <h3 class="text-2xl font-extrabold text-gray-800">{{ $statistics['total'] }}</h3>
 <p class="text-xs text-gray-600 font-medium">Total</p>
 </div>

 <div class="stat-card bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="filterByStatus('MENUNGGU_KONFIRMASI_KEAGAMAAN', event)">
 <div class="flex items-center justify-between mb-2">
 <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
 <i class="fas fa-users text-orange-600"></i>
 </div>
 </div>
 <h3 class="text-2xl font-extrabold text-orange-600">{{ $statistics['pending_keagamaan'] }}</h3>
 <p class="text-xs text-gray-600 font-medium">Pending Keagamaan</p>
 </div>

 <div class="stat-card bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="filterByStatus('MENUNGGU_APPROVE_TANGGAL', event)">
 <div class="flex items-center justify-between mb-2">
 <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
 <i class="fas fa-user-clock text-blue-600"></i>
 </div>
 </div>
 <h3 class="text-2xl font-extrabold text-blue-600">{{ $statistics['pending_admin'] }}</h3>
 <p class="text-xs text-gray-600 font-medium">Pending Admin</p>
 </div>

 <div class="stat-card bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="filterByStatus('TANGGAL_DISETUJUI', event)">
 <div class="flex items-center justify-between mb-2">
 <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center">
 <i class="fas fa-upload text-cyan-600"></i>
 </div>
 </div>
 <h3 class="text-2xl font-extrabold text-cyan-600">{{ $statistics['upload'] }}</h3>
 <p class="text-xs text-gray-600 font-medium">Upload Dokumen</p>
 </div>

 <div class="stat-card bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="filterByStatus('DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI', event)">
 <div class="flex items-center justify-between mb-2">
 <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
 <i class="fas fa-search text-purple-600"></i>
 </div>
 </div>
 <h3 class="text-2xl font-extrabold text-purple-600">{{ $statistics['verifikasi'] }}</h3>
 <p class="text-xs text-gray-600 font-medium">Verifikasi</p>
 </div>

 <div class="stat-card bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="filterByStatus('SELESAI', event)">
 <div class="flex items-center justify-between mb-2">
 <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
 <i class="fas fa-check-circle text-green-600"></i>
 </div>
 </div>
 <h3 class="text-2xl font-extrabold text-green-600">{{ $statistics['selesai'] }}</h3>
 <p class="text-xs text-gray-600 font-medium">Selesai</p>
 </div>

 <div class="stat-card bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="filterByStatus('ditolak', event)">
 <div class="flex items-center justify-between mb-2">
 <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
 <i class="fas fa-times-circle text-red-600"></i>
 </div>
 </div>
 <h3 class="text-2xl font-extrabold text-red-600">{{ $statistics['ditolak'] }}</h3>
 <p class="text-xs text-gray-600 font-medium">Ditolak</p>
 </div>
 </div>

 {{-- Main Content: Calendar & List --}}
 <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
 {{-- Calendar View --}}
 <div class="lg:col-span-1">
 <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
 <div class="p-4 border-b border-gray-100 flex items-center justify-between">
 <h3 class="font-bold text-gray-800">Kalender Pernikahan</h3>
 <div class="flex items-center gap-2">
 <button onclick="changeMonth(-1)" class="w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
 <i class="fas fa-chevron-left text-gray-600 text-sm"></i>
 </button>
 <span id="currentMonth" class="text-sm font-bold text-gray-700 min-w-36 text-center"></span>
 <button onclick="changeMonth(1)" class="w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
 <i class="fas fa-chevron-right text-gray-600 text-sm"></i>
 </button>
 </div>
 </div>
 <div class="p-4">
 <div class="grid grid-cols-7 gap-2 text-center mb-3">
 <div class="text-sm font-bold text-gray-600">Min</div>
 <div class="text-sm font-bold text-gray-600">Sen</div>
 <div class="text-sm font-bold text-gray-600">Sel</div>
 <div class="text-sm font-bold text-gray-600">Rab</div>
 <div class="text-sm font-bold text-gray-600">Kam</div>
 <div class="text-sm font-bold text-gray-600">Jum</div>
 <div class="text-sm font-bold text-gray-600">Sab</div>
 </div>
 <div id="calendarGrid" class="grid grid-cols-7 gap-2"></div>
 </div>
 </div>
 </div>

 {{-- List View --}}
 <div class="lg:col-span-1">
 <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
 <div class="p-4 border-b border-gray-100">
 <div class="flex flex-col gap-3">
 <h3 class="font-bold text-gray-800">Daftar Permohonan</h3>
 <input type="text" id="searchInput" placeholder="Cari nomor atau nama..."
 class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
 </div>
 </div>

 {{-- List --}}
 <div id="pernikahanList" class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
 @php
 $currentStatus = request('status', '');
 $query = \App\Models\LayananPernikahan::with(['dokumen', 'user'])
 ->orderBy('created_at', 'desc');

 if ($currentStatus === 'ditolak') {
 $query->whereIn('status', [
 \App\Models\LayananPernikahan::STATUS_TANGGAL_DITOLAK,
 \App\Models\LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN,
 \App\Models\LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
 ]);
 } elseif ($currentStatus) {
 $query->where('status', $currentStatus);
 }

 $pernikahan = $query->limit(50)->get();
 @endphp

 @if($pernikahan->isNotEmpty())
 @foreach($pernikahan as $item)
 @php
 $isRejected = in_array($item->status, [
 \App\Models\LayananPernikahan::STATUS_TANGGAL_DITOLAK,
 \App\Models\LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN,
 \App\Models\LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
 ]);
 $canUploadFinal = in_array($item->status, [
 \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
 \App\Models\LayananPernikahan::STATUS_SELESAI,
 ]);
 @endphp
 <div class="p-3 hover:bg-gray-50 transition-colors cursor-pointer"
 data-status="{{ $item->status }}"
 data-is-rejected="{{ $isRejected ? '1' : '0' }}"
 onclick="showDetail('{{ $item->pernikahan_id }}')">
 <div class="flex items-start gap-3">
 <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
 @if($item->status === \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI) bg-cyan-100
 @elseif($item->status === \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI) bg-purple-100
 @elseif($item->status === \App\Models\LayananPernikahan::STATUS_SELESAI) bg-green-100
 @elseif($item->status === \App\Models\LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL) bg-blue-100
 @elseif($item->status === \App\Models\LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN) bg-orange-100
 @elseif($isRejected) bg-red-100
 @else bg-yellow-100 @endif">
 <i class="fas @if($item->status === \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI) fa-upload text-cyan-600
 @elseif($item->status === \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI) fa-search text-purple-600
 @elseif($item->status === \App\Models\LayananPernikahan::STATUS_SELESAI) fa-check text-green-600
 @elseif($item->status === \App\Models\LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL) fa-user-clock text-blue-600
 @elseif($item->status === \App\Models\LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN) fa-users text-orange-600
 @elseif($isRejected) fa-times text-red-600
 @else fa-clock text-yellow-600 @endif text-xs"></i>
 </div>
 <div class="flex-1 min-w-0">
 <p class="font-medium text-gray-800 text-sm truncate">{{ $item->nama_mempelai_pria }}</p>
 <p class="text-xs text-gray-500 font-mono">{{ $item->nomor_antrian }}</p>
 <p class="text-xs mt-1
 @if($item->status === \App\Models\LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL) text-blue-600 font-medium
 @elseif($item->status === \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI) text-cyan-600 font-medium
 @elseif($item->status === \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI) text-purple-600 font-medium
 @elseif($item->status === \App\Models\LayananPernikahan::STATUS_SELESAI) text-green-600 font-medium
 @elseif($item->status === \App\Models\LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN) text-orange-600 font-medium
 @else text-gray-500 @endif">
 {{ $item->status_label }}
 </p>
 @if($item->tanggal_perkawinan)
 <p class="text-xs text-gray-400 mt-1"><i class="fas fa-calendar mr-1"></i>{{ $item->tanggal_perkawinan->format('d M Y') }}</p>
 @endif
 </div>
 @if($canUploadFinal)
 <button type="button"
 onclick="event.stopPropagation(); openUploadDokumenFinalModal('{{ $item->pernikahan_id }}')"
 class="self-center flex-shrink-0 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold inline-flex items-center gap-2 shadow-sm transition-colors"
 title="Upload Dokumen Final (Akta Pernikahan & KK Baru)">
 <i class="fas fa-cloud-upload-alt text-sm"></i>
 <span>Upload Dokumen</span>
 </button>
 @endif
 </div>
 </div>
 @endforeach
 @else
 <div class="p-8 text-center text-gray-500">
 <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
 <p class="text-sm">Tidak ada data</p>
 </div>
 @endif
 </div>
 </div>
 </div>
 </div>
</div>

{{-- Modal Detail --}}
<div id="detailModal" class="fixed inset-0 bg-black/50 z-50 items-center justify-center p-4" style="display:none;">
 <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
 <div class="p-6 border-b border-gray-100 flex items-center justify-between">
 <div>
 <h3 class="text-xl font-bold text-gray-800">Detail Pernikahan</h3>
 <p id="modalNomorAntrian" class="text-sm text-gray-500 font-mono"></p>
 </div>
 <button onclick="closeModal()" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
 <i class="fas fa-times text-gray-600"></i>
 </button>
 </div>
 <div id="modalContent" class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
 {{-- Content will be loaded dynamically --}}
 </div>
 </div>
</div>

{{-- Modal Konfirmasi --}}
<div id="confirmModal" class="fixed inset-0 z-50 items-center justify-center p-4" style="display:none;">
 <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
 <div class="p-6 text-center">
 <div id="confirmIcon" class="w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-4"></div>
 <h3 id="confirmTitle" class="text-xl font-bold text-gray-800 mb-2"></h3>
 <p id="confirmMessage" class="text-gray-600 mb-4"></p>

 <div id="reasonContainer" class="text-left mb-4 hidden">
 <label class="block text-sm font-medium text-gray-700 mb-2">Alasan <span class="text-red-500">*</span></label>
 <textarea id="confirmReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Tuliskan alasan..."></textarea>
 </div>

 <div class="flex gap-3">
 <button onclick="closeConfirmModal()" class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 rounded-xl font-medium text-gray-800 transition-colors">
 Batal
 </button>
 <button id="confirmActionBtn" class="flex-1 px-4 py-3 rounded-xl font-medium text-white transition-colors">
 Ya, Lanjutkan
 </button>
 </div>
 </div>
 </div>
</div>

{{-- Modal Upload Dokumen Final --}}
<div id="uploadDokumenFinalModal" class="fixed inset-0 bg-black/50 z-50 items-center justify-center p-4" style="display:none;">
 <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
 <div class="p-6 border-b border-gray-100 flex items-center justify-between">
 <div>
 <h3 class="text-xl font-bold text-gray-800">Upload Dokumen Final</h3>
 <p class="text-sm text-gray-500">Akta Pernikahan & Kartu Keluarga baru hasil penerbitan Disdukcapil</p>
 </div>
 <button type="button" onclick="closeUploadDokumenFinalModal()" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
 <i class="fas fa-times text-gray-600"></i>
 </button>
 </div>
 <form id="uploadDokumenFinalForm" class="overflow-y-auto max-h-[calc(90vh-180px)]" enctype="multipart/form-data">
 @csrf
 <div class="p-6 space-y-4">
 <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-800">
 <i class="fas fa-info-circle mr-1"></i>
 Format yang didukung: <strong>PDF</strong> (maks. 5 MB per file). Anda dapat mengupload sebagian dokumen terlebih dahulu - status akan otomatis menjadi <strong>Selesai</strong> ketika ke-4 dokumen lengkap.
 </div>

 {{-- Akta Pernikahan --}}
 <div class="border border-gray-200 rounded-xl p-4">
 <label class="block text-sm font-semibold text-gray-800 mb-1">Akta Pernikahan</label>
 <p class="text-xs text-gray-500 mb-2">Akta resmi pencatatan perkawinan</p>
 <input type="file" name="file_akta_pernikahan" id="file_akta_pernikahan"
 accept="application/pdf,.pdf"
 class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
 <div id="preview_akta_pernikahan" class="mt-2 text-xs"></div>
 </div>

 {{-- KK Pasangan --}}
 <div class="border border-gray-200 rounded-xl p-4">
 <label class="block text-sm font-semibold text-gray-800 mb-1">Kartu Keluarga Baru - Pasangan Suami-Istri</label>
 <p class="text-xs text-gray-500 mb-2">KK baru untuk pasangan mempelai</p>
 <input type="file" name="file_kk_pasangan" id="file_kk_pasangan"
 accept="application/pdf,.pdf"
 class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
 <div id="preview_kk_pasangan" class="mt-2 text-xs"></div>
 </div>

 {{-- KK Ortu Pria --}}
 <div class="border border-gray-200 rounded-xl p-4">
 <label class="block text-sm font-semibold text-gray-800 mb-1">Kartu Keluarga Baru - Orang Tua Mempelai Pria</label>
 <p class="text-xs text-gray-500 mb-2">KK orang tua mempelai pria (setelah pengeluaran mempelai pria)</p>
 <input type="file" name="file_kk_ortu_pria" id="file_kk_ortu_pria"
 accept="application/pdf,.pdf"
 class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
 <div id="preview_kk_ortu_pria" class="mt-2 text-xs"></div>
 </div>

 {{-- KK Ortu Wanita --}}
 <div class="border border-gray-200 rounded-xl p-4">
 <label class="block text-sm font-semibold text-gray-800 mb-1">Kartu Keluarga Baru - Orang Tua Mempelai Wanita</label>
 <p class="text-xs text-gray-500 mb-2">KK orang tua mempelai wanita (setelah pengeluaran mempelai wanita)</p>
 <input type="file" name="file_kk_ortu_wanita" id="file_kk_ortu_wanita"
 accept="application/pdf,.pdf"
 class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
 <div id="preview_kk_ortu_wanita" class="mt-2 text-xs"></div>
 </div>
 </div>

 <div class="p-4 border-t border-gray-100 bg-gray-50 flex gap-3">
 <button type="button" onclick="closeUploadDokumenFinalModal()"
 class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 rounded-xl font-medium text-gray-800">
 Batal
 </button>
 <button type="submit" id="btnSubmitDokumenFinal"
 class="flex-1 px-4 py-3 rounded-xl font-semibold text-white bg-green-600 hover:bg-green-700 inline-flex items-center justify-center gap-2">
 <i class="fas fa-cloud-upload-alt"></i> Simpan Dokumen
 </button>
 </div>
 </form>
 </div>
</div>

<input type="hidden" id="currentPernikahanId">
<input type="hidden" id="currentAction">
<input type="hidden" id="currentStatus">

<style>
.tab-btn {
 position: relative;
 transition: all 0.2s;
}
.tab-btn.active {
 border-color: #0052CC;
 color: #0052CC;
 background: #f0f5ff;
}
.tab-btn:hover:not(.active) {
 background: #f9fafb;
}
.calendar-day {
 aspect-ratio: 1;
 min-height: 52px;
 display: flex;
 flex-direction: column;
 align-items: center;
 justify-content: center;
 font-size: 15px;
 font-weight: 600;
 border-radius: 12px;
 cursor: pointer;
 transition: all 0.2s;
 position: relative;
 background: #f9fafb;
}
.calendar-day:hover {
 background: #e5e7eb;
 transform: scale(1.05);
}
.calendar-day.today {
 background: #0052CC;
 color: white;
 font-weight: 700;
 box-shadow: 0 4px 12px rgba(0, 82, 204, 0.3);
}
.calendar-day.has-event {
 background: linear-gradient(135deg, #10b981 0%, #059669 100%);
 color: white;
 font-weight: 700;
 box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}
.calendar-day.has-event .event-icon {
 display: flex !important;
}
.calendar-day.today.has-event {
 background: linear-gradient(135deg, #10b981 0%, #059669 100%);
 color: white;
 font-weight: 700;
 box-shadow: 0 0 0 3px #0052CC, 0 4px 12px rgba(16, 185, 129, 0.5);
}
.calendar-day.has-event::after {
 content: '';
 position: absolute;
 bottom: 4px;
 width: 6px;
 height: 6px;
 background: rgba(255, 255, 255, 0.8);
 border-radius: 50%;
}
.calendar-day.other-month {
 color: #d1d5db;
 background: #f3f4f6;
}
.calendar-day.other-month:hover {
 background: #f3f4f6;
 transform: none;
}
.calendar-day.empty {
 cursor: default;
 background: transparent;
}
.calendar-day.empty:hover {
 background: transparent;
 transform: none;
}
.stat-card {
 transition: all 0.2s ease;
}
.stat-card:hover {
 transform: translateY(-2px);
}
.stat-card:active {
 transform: translateY(0);
}
</style>

<script>
const DOKUMEN_LABEL_MAP = {
 // Surat keterangan
 'surat_keterangan':             'Surat Keterangan Gereja',
 'surat_keterangan_gereja':      'Surat Keterangan Gereja',
 'surat_keterangan_belum_menikah': 'Surat Keterangan Belum Menikah',

 // KTP
 'ktp_mempelai':                 'KTP Mempelai',
 'ktp_mempelai_pria':            'KTP Mempelai Pria',
 'ktp_mempelai_wanita':          'KTP Mempelai Wanita',
 'ktp_saksi':                    'KTP Saksi',
 'ktp_saksi_1':                  'KTP Saksi 1',
 'ktp_saksi_2':                  'KTP Saksi 2',

 // Kartu Keluarga
 'kartu_keluarga':               'Kartu Keluarga',
 'kartu_keluarga_pria':          'Kartu Keluarga Mempelai Pria',
 'kartu_keluarga_wanita':        'Kartu Keluarga Mempelai Wanita',
 'kk':                           'Kartu Keluarga',
 'kk_pria':                      'Kartu Keluarga Mempelai Pria',
 'kk_wanita':                    'Kartu Keluarga Mempelai Wanita',

 // Surat baptis
 'surat_baptis':                 'Surat Baptis',
 'surat_baptis_pria':            'Surat Baptis Mempelai Pria',
 'surat_baptis_wanita':          'Surat Baptis Mempelai Wanita',

 // Dokumen lainnya
 'surat_izin_ortu':              'Surat Izin Orang Tua',
 'surat_izin_orang_tua':         'Surat Izin Orang Tua',
 'surat_cerai':                  'Surat Cerai',
 'akta_kematian':                'Akta Kematian Pasangan',
 'pas_foto':                     'Pas Foto',
 'ijazah':                       'Ijazah',
 'akta_kelahiran':               'Akta Kelahiran',
 'akta_kelahiran_pria':          'Akta Kelahiran Mempelai Pria',
 'akta_kelahiran_wanita':        'Akta Kelahiran Mempelai Wanita',
};

function getDokumenLabel(d) {
 const raw = d.jenis_dokumen_label || d.jenis_dokumen || '';
 if (DOKUMEN_LABEL_MAP[raw]) {
 return DOKUMEN_LABEL_MAP[raw];
 }
 // Fallback: ubah snake_case menjadi Title Case
 return raw
 .replace(/_/g, ' ')
 .replace(/\b\w/g, c => c.toUpperCase());
}

// Set active tab on page load
document.addEventListener('DOMContentLoaded', function() {
 const currentStatus = '{{ request('status', '') }}';

 document.querySelectorAll('.tab-btn').forEach(btn => {
 btn.classList.remove('active');
 btn.classList.remove('border-blue-600', 'text-blue-600');
 btn.classList.add('border-transparent', 'text-gray-500');
 });

 let activeTab = null;
 document.querySelectorAll('.tab-btn').forEach(btn => {
 if (btn.dataset.status === currentStatus) {
 activeTab = btn;
 }
 });

 if (!activeTab) {
 activeTab = document.querySelector('.tab-btn[data-status=""]');
 }

 if (activeTab) {
 activeTab.classList.add('active');
 activeTab.classList.remove('border-transparent', 'text-gray-500');
 activeTab.classList.add('border-blue-600', 'text-blue-600');
 }

 loadCalendarData();
});

// Calendar
let currentDate = new Date();
let calendarData = @json($calendarData);

function renderCalendar() {
 const grid = document.getElementById('calendarGrid');
 const monthLabel = document.getElementById('currentMonth');

 const year = currentDate.getFullYear();
 const month = currentDate.getMonth();

 const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
 monthLabel.textContent = `${monthNames[month]} ${year}`;

 const firstDay = new Date(year, month, 1).getDay();
 const daysInMonth = new Date(year, month + 1, 0).getDate();
 const daysInPrevMonth = new Date(year, month, 0).getDate();

 const today = new Date();

 let html = '';

 // Days from previous month
 for (let i = firstDay - 1; i >= 0; i--) {
 html += `<div class="calendar-day other-month">${daysInPrevMonth - i}</div>`;
 }

 // Days in current month
 for (let day = 1; day <= daysInMonth; day++) {
 const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
 const isToday = today.getDate() === day &&
 today.getMonth() === month &&
 today.getFullYear() === year;
 const hasEvent = calendarData[dateKey] && calendarData[dateKey].length > 0;
 const eventCount = hasEvent ? calendarData[dateKey].length : 0;

 let classes = 'calendar-day';
 if (isToday) classes += ' today';
 if (hasEvent) classes += ' has-event';

 const eventIcon = hasEvent
 ? `<div class="event-icon absolute bottom-1 right-1 w-5 h-5 bg-white/20 rounded-full flex items-center justify-center">
 <i class="fas fa-check text-white text-[8px]"></i>
 </div>`
 : '';

 const countBadge = eventCount > 1
 ? `<span class="absolute top-1 right-1 bg-white text-green-600 rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold">${eventCount}</span>`
 : '';

 html += `<div class="${classes}" ${hasEvent ? `onclick="showDateEvents('${dateKey}')"` : ''}>
 <span class="relative z-10">${day}</span>
 ${eventIcon}
 ${countBadge}
 </div>`;
 }

 // Days from next month
 const totalCells = firstDay + daysInMonth;
 const remainingCells = totalCells <= 35 ? 42 - totalCells : 49 - totalCells;
 for (let i = 1; i <= remainingCells; i++) {
 html += `<div class="calendar-day other-month">${i}</div>`;
 }

 grid.innerHTML = html;
}

function changeMonth(delta) {
 currentDate.setMonth(currentDate.getMonth() + delta);
 loadCalendarData();
}

async function loadCalendarData() {
 const year = currentDate.getFullYear();
 const month = String(currentDate.getMonth() + 1).padStart(2, '0');
 const currentMonth = currentDate.getMonth();
 const currentYear = currentDate.getFullYear();

 const now = new Date();
 const isCurrentMonth = now.getMonth() === currentMonth && now.getFullYear() === currentYear;

 if (!window.initialCalendarData) {
 window.initialCalendarData = @json($calendarData);
 window.initialCalendarMonth = now.getMonth();
 window.initialCalendarYear = now.getFullYear();
 }

 if (isCurrentMonth) {
 calendarData = window.initialCalendarData;
 renderCalendar();
 return;
 }

 try {
 const url = `{{ route('admin.pernikahan.calendar-data') }}?year=${year}&month=${month}`;
 const response = await fetch(url, {
 method: 'GET',
 headers: {
 'Accept': 'application/json',
 'X-Requested-With': 'XMLHttpRequest'
 },
 credentials: 'same-origin'
 });

 if (!response.ok) throw new Error('Failed to load: ' + response.status);

 const data = await response.json();

 if (data.success && data.data) {
 calendarData = data.data;
 } else {
 calendarData = {};
 }
 renderCalendar();
 } catch (error) {
 console.error('Error loading calendar:', error);
 calendarData = {};
 renderCalendar();
 }
}

function showDateEvents(dateKey) {
 const events = calendarData[dateKey] || [];
 if (events.length === 1) {
 showDetail(events[0].id);
 } else if (events.length > 1) {
 const content = document.getElementById('modalContent');
 content.innerHTML = `
 <h4 class="font-bold text-gray-800 mb-4">Pernikahan pada ${dateKey}</h4>
 <div class="space-y-3">
 ${events.map(e => `
 <div class="p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100" onclick="showDetail('${e.id}')">
 <p class="font-medium text-gray-800">${e.nama_pria}</p>
 <p class="text-sm text-gray-500">${e.nomor_antrian}</p>
 <p class="text-sm text-gray-500">${e.gereja}</p>
 </div>
 `).join('')}
 </div>
 `;
 modalShow('detailModal');
 }
}

// Tab filtering
function filterByStatus(status, event) {
 if (event) event.stopPropagation();

 document.querySelectorAll('.tab-btn').forEach(btn => {
 btn.classList.remove('active');
 btn.classList.remove('border-blue-600', 'text-blue-600');
 btn.classList.add('border-transparent', 'text-gray-500');

 if (btn.dataset.status === status) {
 btn.classList.add('active');
 btn.classList.remove('border-transparent', 'text-gray-500');
 btn.classList.add('border-blue-600', 'text-blue-600');
 }
 });

 const listItems = document.querySelectorAll('#pernikahanList > div');
 let visibleCount = 0;

 listItems.forEach(item => {
 if (item.classList.contains('text-center')) return;

 const itemStatus = item.dataset.status || '';
 const isRejected = item.dataset.isRejected === '1';
 let shouldShow = false;

 if (status === '') {
 shouldShow = true;
 } else if (status === 'ditolak') {
 shouldShow = isRejected;
 } else {
 shouldShow = itemStatus === status;
 }

 item.style.display = shouldShow ? '' : 'none';
 if (shouldShow) visibleCount++;
 });

 const noDataMsg = document.querySelector('#pernikahanList .text-center');
 if (noDataMsg) {
 noDataMsg.style.display = visibleCount === 0 ? '' : 'none';
 }
}

document.querySelectorAll('.tab-btn').forEach(btn => {
 btn.addEventListener('click', (e) => {
 e.preventDefault();
 filterByStatus(btn.dataset.status, e);
 });
});

// Search
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
 clearTimeout(searchTimeout);
 searchTimeout = setTimeout(() => {
 const query = this.value.toLowerCase();
 document.querySelectorAll('#pernikahanList > div').forEach(item => {
 const text = item.textContent.toLowerCase();
 item.style.display = text.includes(query) ? '' : 'none';
 });
 }, 300);
});

function escHtml(str) {
 if (!str) return '';
 return String(str)
 .replace(/&/g, '&amp;')
 .replace(/</g, '&lt;')
 .replace(/>/g, '&gt;')
 .replace(/"/g, '&quot;')
 .replace(/'/g, '&#039;');
}

function modalShow(modalId) {
 const modal = document.getElementById(modalId);
 if (modal) modal.style.display = 'flex';
}

function modalHide(modalId) {
 const modal = document.getElementById(modalId);
 if (modal) modal.style.display = 'none';
}

// ============================================================
// DETAIL MODAL
// ============================================================

let currentPernikahanData = null;

async function showDetail(pernikahanId) {
 if (!pernikahanId) return;

 SwalHelper.loading('Memuat detail...');

 try {
 const response = await fetch(`{{ route('admin.pernikahan.detail-ajax') }}`, {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': '{{ csrf_token() }}',
 },
 body: JSON.stringify({ pernikahan_id: pernikahanId }),
 });

 if (!response.ok) throw new Error('HTTP ' + response.status);

 const result = await response.json();

 SwalHelper.close();

 if (!result.success) {
 showToast('error', result.message || 'Gagal memuat detail');
 return;
 }

 const p = result.data;
 currentPernikahanData = p;

 let documentsHtml = '';
 const isMenungguTanggal = p.status === 'MENUNGGU_APPROVE_TANGGAL';
 const isVerifikasiDokumen = p.status === 'DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI' ||
 p.status === 'DOKUMEN_PERLU_PERBAIKAN' ||
 p.status === 'DOKUMEN_DIVERIFIKASI' ||
 p.status === 'SELESAI';

 if (isMenungguTanggal) {
 const ktpEntries = [
 { key: 'mempelai_pria', label: 'KTP Mempelai Pria' },
 { key: 'mempelai_wanita', label: 'KTP Mempelai Wanita' },
 { key: 'saksi_1', label: 'KTP Saksi 1' },
 { key: 'saksi_2', label: 'KTP Saksi 2' },
 ];

 const ktpHtml = ktpEntries
 .filter(e => p.ktp_files && p.ktp_files[e.key])
 .map(e => `
 <div class="border rounded-lg p-3 bg-gray-50">
 <p class="text-xs font-medium text-gray-700 mb-2">${e.label}</p>
 <img src="${escHtml(p.ktp_files[e.key])}" alt="${e.label}"
 class="w-full max-h-64 object-contain rounded border cursor-pointer hover:opacity-90"
 onclick="window.open('${escHtml(p.ktp_files[e.key])}','_blank')">
 </div>
 `).join('');

 if (ktpHtml) {
 documentsHtml = `
 <div class="border-t pt-3">
 <p class="text-xs text-gray-500 mb-3">Berkas KTP</p>
 <div class="space-y-3">${ktpHtml}</div>
 </div>
 `;
 }

 } else if (isVerifikasiDokumen && p.dokumen_keagamaan && p.dokumen_keagamaan.length > 0) {
 const dokumenHtml = p.dokumen_keagamaan.map(d => {
 const label = getDokumenLabel(d);
 return `
 <div class="border rounded-lg p-3 bg-gray-50">
 <div class="flex items-center justify-between">
 <p class="text-sm font-medium text-gray-700">${escHtml(label)}</p>
 ${d.file_url ? `
 <a href="${escHtml(d.file_url)}" target="_blank"
 class="px-2 py-1 bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 text-xs inline-flex items-center">
 <i class="fas fa-eye mr-1"></i>Lihat
 </a>
 ` : '<span class="text-xs text-gray-400">Belum ada file</span>'}
 </div>
 ${d.catatan_verifikasi ? `<p class="text-xs text-red-600 mt-2">${escHtml(d.catatan_verifikasi)}</p>` : ''}
 </div>
 `;
 }).join('');

 documentsHtml = `
 <div class="border-t pt-3">
 <p class="text-xs text-gray-500 mb-3">Dokumen Keagamaan</p>
 <div class="space-y-3">${dokumenHtml}</div>
 </div>
 `;
 }

 const contentDiv = document.getElementById('modalContent');
 const nomorAntrianEl = document.getElementById('modalNomorAntrian');

 nomorAntrianEl.textContent = p.nomor_antrian || '-';

 contentDiv.innerHTML = `
 <div class="space-y-4">
 <div class="flex items-center justify-between">
 <span class="text-sm text-gray-500">Status</span>
 <span class="px-3 py-1 rounded-full text-xs font-medium ${escHtml(p.status_color)}">${escHtml(p.status_label)}</span>
 </div>

 <div class="bg-gray-50 rounded-xl p-3">
 <p class="text-xs text-gray-500 mb-1">Nomor Antrian</p>
 <p class="font-mono font-bold text-blue-600">${escHtml(p.nomor_antrian)}</p>
 </div>

 ${p.nama_pemohon ? `
 <div class="border-t pt-3">
 <p class="text-xs text-gray-500 mb-1">Pemohon</p>
 <p class="text-sm font-medium text-gray-800">${escHtml(p.nama_pemohon)}</p>
 ${p.alamat_pemohon ? `<p class="text-xs text-gray-500 mt-1"><i class="fas fa-map-marker-alt mr-1"></i>${escHtml(p.alamat_pemohon)}</p>` : ''}
 </div>
 ` : ''}

 ${documentsHtml}

 <div class="border-t pt-3 space-y-2">
 <div class="flex justify-between items-center">
 <span class="text-sm text-gray-500">Tanggal Pernikahan</span>
 <span class="text-sm font-medium text-gray-800">${escHtml(p.tanggal_perkawinan || '-')}</span>
 </div>
 <div class="flex justify-between items-center">
 <span class="text-sm text-gray-500">Gereja</span>
 <span class="text-sm font-medium text-gray-800">${escHtml(p.nama_gereja || '-')}</span>
 </div>
 </div>

 ${p.catatan_keagamaan ? `
 <div class="border-t pt-3">
 <p class="text-xs text-gray-500 mb-1">Catatan Keagamaan</p>
 <p class="text-sm text-gray-800">${escHtml(p.catatan_keagamaan)}</p>
 </div>
 ` : ''}

 ${p.alasan_ditolak ? `
 <div class="border-t pt-3">
 <p class="text-xs text-gray-500 mb-1">Alasan Ditolak</p>
 <p class="text-sm text-red-600">${escHtml(p.alasan_ditolak)}</p>
 </div>
 ` : ''}
 </div>

 ${p.can_konfirmasi ? `
 <div class="flex gap-3 mt-6">
 <button onclick="openTolakModal('${escHtml(p.pernikahan_id)}', '${escHtml(p.status)}')"
 class="flex-1 px-4 py-2 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700">
 <i class="fas fa-times mr-2"></i>Tolak
 </button>
 <button onclick="openKonfirmasiModal('${escHtml(p.pernikahan_id)}', '${escHtml(p.status)}')"
 class="flex-1 px-4 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700">
 <i class="fas fa-check mr-2"></i>Konfirmasi
 </button>
 </div>
 ` : ''}
 `;

 modalShow('detailModal');

 } catch (err) {
 SwalHelper.close();
 console.error('showDetail error:', err);
 showToast('error', 'Terjadi kesalahan saat memuat detail');
 }
}

// Modal Tolak
function openTolakModal(pernikahanId, status) {
 modalHide('detailModal');

 const icon = document.getElementById('confirmIcon');
 const title = document.getElementById('confirmTitle');
 const message = document.getElementById('confirmMessage');
 const btn = document.getElementById('confirmActionBtn');
 const reasonContainer = document.getElementById('reasonContainer');
 const reasonInput = document.getElementById('confirmReason');

 document.getElementById('currentPernikahanId').value = pernikahanId;
 document.getElementById('currentAction').value = 'reject';
 document.getElementById('currentStatus').value = status || '';

 icon.className = 'w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-4 bg-red-100';
 icon.innerHTML = '<i class="fas fa-times text-3xl text-red-600"></i>';

 if (status === 'MENUNGGU_APPROVE_TANGGAL') {
 title.textContent = 'Tolak Tanggal Pernikahan?';
 message.textContent = 'Tanggal perkawinan akan ditolak. Keagamaan perlu mengajukan tanggal baru.';
 } else if (status === 'DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI') {
 title.textContent = 'Tolak Dokumen?';
 message.textContent = 'Dokumen akan ditolak dan keagamaan perlu mengupload ulang dokumen yang diperbaiki.';
 } else {
 title.textContent = 'Tolak?';
 message.textContent = 'Apakah Anda yakin ingin menolak permintaan ini?';
 }

 btn.textContent = 'Ya, Tolak';
 btn.className = 'flex-1 px-4 py-3 rounded-xl font-medium text-white bg-red-600 hover:bg-red-700 transition-colors';
 reasonContainer.classList.remove('hidden');
 reasonInput.required = true;
 reasonInput.value = '';

 btn.onclick = () => executeConfirm();
 modalShow('confirmModal');
}

// Modal Konfirmasi
function openKonfirmasiModal(pernikahanId, status) {
 modalHide('detailModal');

 const icon = document.getElementById('confirmIcon');
 const title = document.getElementById('confirmTitle');
 const message = document.getElementById('confirmMessage');
 const btn = document.getElementById('confirmActionBtn');
 const reasonContainer = document.getElementById('reasonContainer');

 document.getElementById('currentPernikahanId').value = pernikahanId;
 document.getElementById('currentAction').value = 'approve';
 document.getElementById('currentStatus').value = status || '';

 icon.className = 'w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-4 bg-green-100';
 icon.innerHTML = '<i class="fas fa-check text-3xl text-green-600"></i>';

 if (status === 'MENUNGGU_APPROVE_TANGGAL') {
 title.textContent = 'Setujui Tanggal Pernikahan?';
 message.textContent = 'Tanggal perkawinan akan disetujui dan keagamaan dapat mengupload dokumen.';
 btn.textContent = 'Ya, Setujui';
 } else if (status === 'DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI') {
 title.textContent = 'Verifikasi Dokumen?';
 message.textContent = 'Dokumen akan diverifikasi dan status akan berubah menjadi "Dokumen Diverifikasi".';
 btn.textContent = 'Ya, Verifikasi';
 } else {
 title.textContent = 'Konfirmasi?';
 message.textContent = 'Apakah Anda yakin ingin melanjutkan aksi ini?';
 btn.textContent = 'Ya, Lanjutkan';
 }

 btn.className = 'flex-1 px-4 py-3 rounded-xl font-medium text-white bg-green-600 hover:bg-green-700 transition-colors';
 reasonContainer.classList.add('hidden');

 btn.onclick = () => executeConfirm();
 modalShow('confirmModal');
}

function closeModal() {
 modalHide('detailModal');
}

function closeConfirmModal() {
 modalHide('confirmModal');
 document.getElementById('confirmReason').value = '';
}

// Confirm Modal (legacy)
function showConfirm(action, id) {
 const icon = document.getElementById('confirmIcon');
 const title = document.getElementById('confirmTitle');
 const message = document.getElementById('confirmMessage');
 const btn = document.getElementById('confirmActionBtn');
 const reasonContainer = document.getElementById('reasonContainer');
 const reasonInput = document.getElementById('confirmReason');

 document.getElementById('currentPernikahanId').value = id;
 document.getElementById('currentAction').value = action;

 if (action === 'approve') {
 icon.className = 'w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-4 bg-green-100';
 icon.innerHTML = '<i class="fas fa-check text-3xl text-green-600"></i>';
 title.textContent = 'Setujui Tanggal Pernikahan?';
 message.textContent = 'Tanggal perkawinan akan disetujui dan keagamaan dapat mengupload dokumen.';
 btn.className = 'flex-1 px-4 py-3 rounded-xl font-medium text-white bg-green-600 hover:bg-green-700 transition-colors';
 btn.textContent = 'Ya, Setujui';
 reasonContainer.classList.add('hidden');
 } else if (action === 'reject' || action === 'reject_doc') {
 icon.className = 'w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-4 bg-red-100';
 icon.innerHTML = '<i class="fas fa-times text-3xl text-red-600"></i>';
 title.textContent = action === 'reject' ? 'Tolak Tanggal Pernikahan?' : 'Tolak Dokumen?';
 message.textContent = action === 'reject'
 ? 'Tanggal perkawinan akan ditolak. Keagamaan perlu mengajukan tanggal baru.'
 : 'Dokumen ditolak dan memerlukan perbaikan dari keagamaan.';
 btn.className = 'flex-1 px-4 py-3 rounded-xl font-medium text-white bg-red-600 hover:bg-red-700 transition-colors';
 btn.textContent = 'Ya, Tolak';
 reasonContainer.classList.remove('hidden');
 reasonInput.required = true;
 }

 btn.onclick = () => executeConfirm();
 modalShow('confirmModal');
}

async function executeConfirm() {
 const id = document.getElementById('currentPernikahanId').value;
 const action = document.getElementById('currentAction').value;
 const status = document.getElementById('currentStatus').value;
 const reason = document.getElementById('confirmReason').value;

 if ((action === 'reject' || action === 'reject_doc') && !reason.trim()) {
 if (typeof SwalHelper !== 'undefined' && SwalHelper.warning) {
 SwalHelper.warning('Alasan harus diisi');
 } else if (typeof showToast === 'function') {
 showToast('error', 'Alasan harus diisi');
 }
 return;
 }

 try {
 let url, body;

 if (action === 'approve') {
 if (status === 'DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI') {
 const dokumenData = currentPernikahanData?.dokumen_keagamaan || [];
 if (dokumenData.length === 0) {
 showToast('error', 'Tidak ada dokumen untuk diverifikasi');
 return;
 }
 const dokumenIds = dokumenData.map(d => d.id);
 const statuses = dokumenData.map(() => 'DIVERIFIKASI');

 url = '{{ url('/admin/pernikahan') }}/' + id + '/verifikasi';
 body = JSON.stringify({ dokumen_id: dokumenIds, status: statuses, catatan: [] });
 } else {
 url = '{{ url('/admin/pernikahan') }}/' + id + '/approve-tanggal';
 body = JSON.stringify({});
 }
 } else if (action === 'reject') {
 url = '{{ url('/admin/pernikahan') }}/' + id + '/reject-tanggal';
 body = JSON.stringify({ alasan: reason });
 } else if (action === 'reject_doc') {
 url = '{{ url('/admin/pernikahan') }}/' + id + '/reject-tanggal';
 body = JSON.stringify({ alasan: reason });
 }

 const response = await fetch(url, {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': '{{ csrf_token() }}'
 },
 body: body
 });

 const result = await response.json();

 if (result.success) {
 closeConfirmModal();
 closeModal();
 showToast('success', result.message || 'Operasi berhasil');
 await updateUIAfterAction(id, action);
 } else {
 showToast('error', result.message || 'Operasi gagal');
 }
 } catch (error) {
 console.error('Error:', error);
 showToast('error', 'Terjadi kesalahan');
 }
}

function showToast(type, message) {
 const Toast = Swal.mixin({
 toast: true,
 position: 'top-end',
 showConfirmButton: false,
 timer: 5000,
 timerProgressBar: true,
 background: '#ffffff',
 backdrop: false,
 showClass: { popup: 'swal2-show', backdrop: '' },
 hideClass: { popup: 'swal2-hide', backdrop: '' }
 });

 Toast.fire({
 icon: type === 'success' ? 'success' : 'error',
 title: message,
 iconColor: type === 'success' ? '#22c55e' : '#ef4444'
 });
}

async function updateUIAfterAction(pernikahanId, action) {
 try {
 await loadCalendarData();

 if (action === 'approve' && currentPernikahanData && currentPernikahanData.tanggal_perkawinan_raw) {
 const tanggal = currentPernikahanData.tanggal_perkawinan_raw;
 if (tanggal && calendarData) {
 const eventExists = calendarData[tanggal] && calendarData[tanggal].some(e => e.id === pernikahanId);
 if (!eventExists) {
 if (!calendarData[tanggal]) calendarData[tanggal] = [];
 const newEvent = {
 id: pernikahanId,
 nama_pria: currentPernikahanData.nama_mempelai_pria,
 nomor_antrian: currentPernikahanData.nomor_antrian,
 gereja: currentPernikahanData.nama_gereja
 };
 calendarData[tanggal].push(newEvent);

 if (window.initialCalendarData) {
 if (!window.initialCalendarData[tanggal]) window.initialCalendarData[tanggal] = [];
 const existsInInitial = window.initialCalendarData[tanggal].some(e => e.id === pernikahanId);
 if (!existsInInitial) window.initialCalendarData[tanggal].push(newEvent);
 }
 }
 renderCalendar();
 }
 }

 const listItem = document.querySelector(`[onclick="showDetail('${pernikahanId}')"]`);
 if (listItem && action === 'approve') {
 const statusLabel = listItem.querySelector('.text-blue-600, .text-cyan-600');
 if (statusLabel) {
 statusLabel.classList.remove('text-blue-600');
 statusLabel.classList.add('text-cyan-600');
 statusLabel.textContent = 'Tanggal Disetujui';
 }
 const iconContainer = listItem.querySelector('.bg-blue-100');
 if (iconContainer) {
 iconContainer.classList.remove('bg-blue-100');
 iconContainer.classList.add('bg-cyan-100');
 const icon = iconContainer.querySelector('i');
 if (icon) {
 icon.classList.remove('fa-user-clock', 'text-blue-600');
 icon.classList.add('fa-upload', 'text-cyan-600');
 }
 }
 } else if (listItem && action === 'reject') {
 listItem.setAttribute('data-is-rejected', '1');
 const statusLabel = listItem.querySelector('.text-blue-600');
 if (statusLabel) {
 statusLabel.classList.remove('text-blue-600');
 statusLabel.classList.add('text-red-600');
 statusLabel.textContent = 'Ditolak';
 }
 const iconContainer = listItem.querySelector('.bg-blue-100');
 if (iconContainer) {
 iconContainer.classList.remove('bg-blue-100');
 iconContainer.classList.add('bg-red-100');
 const icon = iconContainer.querySelector('i');
 if (icon) {
 icon.classList.remove('fa-user-clock', 'text-blue-600');
 icon.classList.add('fa-times', 'text-red-600');
 }
 }
 }

 const listResponse = await fetch(window.location.href);
 const listText = await listResponse.text();
 const parser = new DOMParser();
 const newDoc = parser.parseFromString(listText, 'text/html');
 const newList = newDoc.getElementById('pernikahanList');
 if (newList) {
 document.getElementById('pernikahanList').innerHTML = newList.innerHTML;
 document.querySelectorAll('.tab-btn').forEach(btn => {
 btn.addEventListener('click', (e) => {
 e.preventDefault();
 filterByStatus(btn.dataset.status, e);
 });
 });
 }

 } catch (error) {
 console.error('Error updating UI:', error);
 }
}

function renderDokumenFinalList(df) {
 if (!df) return '';
 const items = [
 { key: 'akta_pernikahan', label: 'Akta Pernikahan', icon: 'fa-certificate', color: 'blue' },
 { key: 'kk_pasangan', label: 'KK Pasangan Suami-Istri', icon: 'fa-users', color: 'green' },
 { key: 'kk_ortu_pria', label: 'KK Orang Tua Mempelai Pria', icon: 'fa-male', color: 'cyan' },
 { key: 'kk_ortu_wanita', label: 'KK Orang Tua Mempelai Wanita', icon: 'fa-female', color: 'pink' },
 ];

 const rows = items.map(it => {
 const url = df[it.key];
 if (url) {
 return `
 <div class="flex items-center justify-between bg-gray-50 rounded-lg p-2 text-xs">
 <div class="flex items-center gap-2 min-w-0">
 <i class="fas ${it.icon} text-${it.color}-600"></i>
 <span class="text-gray-700 truncate">${escHtml(it.label)}</span>
 </div>
 <a href="${escHtml(url)}" target="_blank"
 class="text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center gap-1 flex-shrink-0">
 <i class="fas fa-eye"></i> Lihat
 </a>
 </div>
 `;
 }
 return `
 <div class="flex items-center justify-between bg-gray-50 rounded-lg p-2 text-xs">
 <div class="flex items-center gap-2 min-w-0">
 <i class="fas ${it.icon} text-gray-400"></i>
 <span class="text-gray-500 truncate">${escHtml(it.label)}</span>
 </div>
 <span class="text-gray-400 italic flex-shrink-0">Belum diupload</span>
 </div>
 `;
 }).join('');

 const uploadedAt = df.uploaded_at
 ? `<p class="text-[10px] text-gray-400 mt-2"><i class="fas fa-clock mr-1"></i>Update terakhir: ${escHtml(df.uploaded_at)}</p>`
 : '';

 return `<div class="space-y-2">${rows}</div>${uploadedAt}`;
}

function openUploadDokumenFinalModal(pernikahanId) {
 document.getElementById('currentPernikahanId').value = pernikahanId;

 const form = document.getElementById('uploadDokumenFinalForm');
 form.reset();
 ['akta_pernikahan', 'kk_pasangan', 'kk_ortu_pria', 'kk_ortu_wanita'].forEach(k => {
 const prev = document.getElementById('preview_' + k);
 if (prev) prev.innerHTML = '';
 });

 modalHide('detailModal');
 modalShow('uploadDokumenFinalModal');
}

function closeUploadDokumenFinalModal() {
 modalHide('uploadDokumenFinalModal');
}

['file_akta_pernikahan', 'file_kk_pasangan', 'file_kk_ortu_pria', 'file_kk_ortu_wanita'].forEach(inputId => {
 document.addEventListener('change', function (e) {
 if (e.target && e.target.id === inputId) {
 const file = e.target.files[0];
 const previewId = 'preview_' + inputId.replace('file_', '');
 const preview = document.getElementById(previewId);
 if (!preview) return;
 if (file) {
 const sizeMB = (file.size / 1024 / 1024).toFixed(2);
 preview.innerHTML = `<span class="text-green-700"><i class="fas fa-check-circle mr-1"></i>${escHtml(file.name)} (${sizeMB} MB)</span>`;
 } else {
 preview.innerHTML = '';
 }
 }
 });
});

document.getElementById('uploadDokumenFinalForm').addEventListener('submit', async function (e) {
 e.preventDefault();

 const pernikahanId = document.getElementById('currentPernikahanId').value;
 if (!pernikahanId) {
 showToast('error', 'ID permohonan tidak ditemukan');
 return;
 }

 const formData = new FormData(this);

 const fields = ['file_akta_pernikahan', 'file_kk_pasangan', 'file_kk_ortu_pria', 'file_kk_ortu_wanita'];
 const hasAnyFile = fields.some(f => {
 const input = document.getElementById(f);
 return input && input.files.length > 0;
 });

 if (!hasAnyFile) {
 showToast('error', 'Pilih minimal satu file untuk diupload');
 return;
 }

 const btn = document.getElementById('btnSubmitDokumenFinal');
 const originalHtml = btn.innerHTML;
 btn.disabled = true;
 btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupload...';

 try {
 const url = `{{ url('/admin/pernikahan') }}/${pernikahanId}/upload-dokumen-final`;
 const response = await fetch(url, {
 method: 'POST',
 headers: {
 'X-CSRF-TOKEN': '{{ csrf_token() }}',
 'Accept': 'application/json',
 'X-Requested-With': 'XMLHttpRequest',
 },
 body: formData,
 });

 let result;
 try {
 result = await response.json();
 } catch (_) {
 throw new Error('Respon server tidak valid');
 }

 if (!response.ok || !result.success) {
 const msg = result?.message || `Upload gagal (HTTP ${response.status})`;
 if (result?.errors) {
 const firstErr = Object.values(result.errors)[0];
 showToast('error', Array.isArray(firstErr) ? firstErr[0] : msg);
 } else {
 showToast('error', msg);
 }
 return;
 }

 showToast('success', result.message || 'Dokumen final tersimpan');
 closeUploadDokumenFinalModal();

 if (typeof updateUIAfterAction === 'function') {
 await updateUIAfterAction(pernikahanId, 'upload_final');
 }
 } catch (err) {
 console.error('Upload dokumen final error:', err);
 showToast('error', err.message || 'Terjadi kesalahan saat upload');
 } finally {
 btn.disabled = false;
 btn.innerHTML = originalHtml;
 }
});

document.getElementById('uploadDokumenFinalModal').addEventListener('click', function (e) {
 if (e.target === this) closeUploadDokumenFinalModal();
});

// Update time
function updateDateTime() {
 const now = new Date();
 const timeElement = document.getElementById('currentTime');
 if (timeElement) {
 timeElement.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
 }
}
updateDateTime();
setInterval(updateDateTime, 1000);

// Reveal animation
document.querySelectorAll('.reveal').forEach(function(reveal) {
 reveal.classList.add('active');
});

// Close modal on backdrop click
document.getElementById('detailModal').addEventListener('click', function(e) {
 if (e.target === this) closeModal();
});
document.getElementById('confirmModal').addEventListener('click', function(e) {
 if (e.target === this) closeConfirmModal();
});
</script>
@endsection