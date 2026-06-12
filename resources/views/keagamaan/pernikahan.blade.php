@extends('layouts.keagamaan')

@section('title', 'Permintaan Nikah')

{{-- =====================================================================
 PUSH: CSS & JS dependencies (FullCalendar + SweetAlert2 jika belum)
 Tambahkan di @push agar tidak bentrok dengan layout
 ===================================================================== --}}
@push('styles')
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
@endpush

@push('scripts')
 <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
 {{-- SweetAlert2 sebagai fallback jika belum di-load layout --}}
 <script>
 if (typeof Swal === 'undefined') {
 var script = document.createElement('script');
 script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
 document.head.appendChild(script);
 }
 </script>
@endpush

@section('content')

@php
// Persiapan data kalender untuk JavaScript
$calendarEventsData = \App\Models\LayananPernikahan::whereIn('status', [
 \App\Models\LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
 \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
 \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
 \App\Models\LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
 \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
 \App\Models\LayananPernikahan::STATUS_SELESAI,
])
->whereNotNull('tanggal_perkawinan')
->get()
->map(function($p) {
 $colorMap = [
 \App\Models\LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL => '#f59e0b',
 \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI => '#3b82f6',
 \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI => '#8b5cf6',
 \App\Models\LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN => '#ef4444',
 \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI => '#14b8a6',
 \App\Models\LayananPernikahan::STATUS_SELESAI => '#22c55e',
 ];
 return [
 'title' => $p->nama_mempelai_pria,
 'start' => $p->tanggal_perkawinan->format('Y-m-d'),
 'backgroundColor' => $colorMap[$p->status] ?? '#3b82f6',
 'borderColor' => 'transparent',
 'extendedProps' => ['pernikahan_id' => $p->pernikahan_id],
 ];
})
->values()
->toArray();
@endphp

<?php
 // ------------------------------------------------------------------ //
 // Helper: badge status
 // ------------------------------------------------------------------ //
 $getStatusBadge = function($item) {
 $status = $item->status;
 if ($status === \App\Models\LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN) {
 return '<span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700"><i class="fas fa-clock mr-1"></i>Menunggu Konfirmasi</span>';
 } elseif ($status === \App\Models\LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL) {
 return '<span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700"><i class="fas fa-calendar-check mr-1"></i>Menunggu Tanggal</span>';
 } elseif ($status === \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI) {
 return '<span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700"><i class="fas fa-check mr-1"></i>Disetujui</span>';
 } elseif ($status === \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI) {
 return '<span class="px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700"><i class="fas fa-file-upload mr-1"></i>Verifikasi Dokumen</span>';
 } elseif ($status === \App\Models\LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN) {
 return '<span class="px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700"><i class="fas fa-exclamation-triangle mr-1"></i>Perlu Perbaikan</span>';
 } elseif ($status === \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI) {
 return '<span class="px-2 py-1 rounded-full text-xs font-medium bg-teal-100 text-teal-700"><i class="fas fa-check-double mr-1"></i>Dokumen OK</span>';
 } elseif ($status === \App\Models\LayananPernikahan::STATUS_SELESAI) {
 return '<span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700"><i class="fas fa-flag-checkered mr-1"></i>Selesai</span>';
 } elseif (in_array($status, [
 \App\Models\LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN,
 \App\Models\LayananPernikahan::STATUS_TANGGAL_DITOLAK,
 ])) {
 return '<span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700"><i class="fas fa-times mr-1"></i>Ditolak</span>';
 }
 return '<span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700"><i class="fas fa-clock mr-1"></i>Pending</span>';
 };
?>

<div class="min-h-screen bg-gray-50">

 {{--  --  --  Page Header  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --}}
 <div class="bg-white border-b border-gray-200 px-6 py-4 mb-6">
 <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
 <div>
 <h1 class="text-2xl font-bold text-gray-800">Permintaan Nikah</h1>
 <p class="text-gray-500 text-sm">Kelola konfirmasi dan jadwal perkawinan</p>
 </div>
 <div class="flex items-center gap-3">
 <button onclick="refreshPage()"
 class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl text-sm font-medium transition-colors">
 <i class="fas fa-sync-alt mr-2"></i>Refresh
 </button>
 </div>
 </div>
 </div>

 <div class="px-6 pb-6">

 {{--  --  --  Statistics  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --}}
 <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
 <div class="bg-white rounded-xl shadow-sm p-5 flex items-center justify-between">
 <div>
 <p class="text-sm text-gray-500">Menunggu Konfirmasi</p>
 <p class="text-2xl font-bold text-yellow-600">{{ $statistics['menunggu_konfirmasi'] }}</p>
 </div>
 <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
 <i class="fas fa-clock text-yellow-600"></i>
 </div>
 </div>
 <div class="bg-white rounded-xl shadow-sm p-5 flex items-center justify-between">
 <div>
 <p class="text-sm text-gray-500">Dalam Proses</p>
 <p class="text-2xl font-bold text-blue-600">{{ $statistics['dalam_proses'] }}</p>
 </div>
 <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
 <i class="fas fa-spinner text-blue-600"></i>
 </div>
 </div>
 <div class="bg-white rounded-xl shadow-sm p-5 flex items-center justify-between">
 <div>
 <p class="text-sm text-gray-500">Tanggal Disetujui</p>
 <p class="text-2xl font-bold text-green-600">{{ $statistics['tanggal_disetujui'] ?? 0 }}</p>
 </div>
 <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
 <i class="fas fa-calendar-check text-green-600"></i>
 </div>
 </div>
 <div class="bg-white rounded-xl shadow-sm p-5 flex items-center justify-between">
 <div>
 <p class="text-sm text-gray-500">Selesai</p>
 <p class="text-2xl font-bold text-teal-600">{{ $statistics['selesai'] }}</p>
 </div>
 <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
 <i class="fas fa-check-circle text-teal-600"></i>
 </div>
 </div>
 </div>

 {{--  --  --  Calendar + List  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --}}
 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

 {{-- Calendar --}}
 <div class="lg:col-span-2">
 <div class="bg-white rounded-2xl shadow-sm p-6">
 <h2 class="text-lg font-semibold text-gray-800 mb-4">Kalender Pernikahan</h2>
 {{-- FIX 1: pastikan div ini punya tinggi eksplisit agar FullCalendar render --}}
 <div id="calendar" style="min-height:520px"></div>
 </div>
 </div>

 {{-- List --}}
 <div class="lg:col-span-1">
 <div class="bg-white rounded-2xl shadow-sm p-6">
 <div class="flex items-center justify-between mb-4">
 <h2 class="text-lg font-semibold text-gray-800">Daftar Permohonan</h2>
 {{-- FIX 3: gunakan onchange yang terbukti terpanggil --}}
 <select id="filterStatus"
 onchange="filterList(this.value)"
 class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500">
 <option value="all">Semua</option>
 <option value="pending">Pending</option>
 <option value="approved">Disetujui</option>
 <option value="rejected">Ditolak</option>
 </select>
 </div>

 <div id="permohonanList" class="space-y-3 max-h-[600px] overflow-y-auto pr-1">
 @forelse($pernikahan as $item)
 @php
 // Tentukan kategori untuk filter
 $statusCategory = 'pending';
 if (in_array($item->status, [
 \App\Models\LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
 \App\Models\LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
 \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
 \App\Models\LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
 \App\Models\LayananPernikahan::STATUS_SELESAI,
 ])) {
 $statusCategory = 'approved';
 } elseif (in_array($item->status, [
 \App\Models\LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN,
 \App\Models\LayananPernikahan::STATUS_TANGGAL_DITOLAK,
 ])) {
 $statusCategory = 'rejected';
 }
 @endphp
 {{-- FIX 3: gunakan style="display:block" bukan Tailwind hidden --}}
 <div class="permohonan-item p-4 border border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50/50 transition-all cursor-pointer"
 data-status="{{ $statusCategory }}"
 style="display:block"
 onclick="showDetail('{{ $item->pernikahan_id }}')">
 <div class="flex items-start justify-between mb-2">
 <div class="flex-1 min-w-0 pr-2">
 <p class="font-medium text-gray-800 text-sm truncate">{{ $item->nama_mempelai_pria }} {{ $item->nama_mempelai_wanita ? '& ' . $item->nama_mempelai_wanita : '' }}</p>
 </div>
 <div class="shrink-0">{!! $getStatusBadge($item) !!}</div>
 </div>
 <div class="flex items-center justify-between text-xs text-gray-500 mt-1">
 <span><i class="fas fa-hashtag mr-1"></i>{{ $item->nomor_antrian }}</span>
 @if($item->tanggal_perkawinan)
 <span><i class="fas fa-calendar mr-1"></i>{{ $item->tanggal_perkawinan->format('d M Y') }}</span>
 @endif
 </div>
 </div>
 @empty
 <div class="text-center py-8 text-gray-500">
 <i class="fas fa-inbox text-4xl mb-3 block text-gray-300"></i>
 <p>Tidak ada permohonan</p>
 </div>
 @endforelse
 </div>
 </div>
 </div>

 </div>{{-- /grid --}}
 </div>{{-- /px-6 --}}
</div>{{-- /min-h-screen --}}


{{-- =====================================================================
 MODAL: Detail Permohonan
 ===================================================================== --}}
<div id="detailModal"
 class="fixed inset-0 bg-black/50 items-center justify-center z-50 p-4"
 style="display:none">
 <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden mx-auto">
 <div class="modal-header sticky top-0 z-10">
 <div class="flex items-center gap-3">
 <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
 <i class="fas fa-heart text-white text-sm"></i>
 </div>
 <h3>Detail Permohonan Pernikahan</h3>
 </div>
 <button onclick="closeDetailModal()">
 <i class="fas fa-times text-sm"></i>
 </button>
 </div>
 <div id="detailContent" class="p-5 overflow-y-auto max-h-[calc(90vh-64px)]">
 {{-- Diisi via JS --}}
 </div>
 </div>
</div>


{{-- =====================================================================
 MODAL: Konfirmasi
 ===================================================================== --}}
<div id="konfirmasiModal"
 class="fixed inset-0 bg-black/50 items-center justify-center z-50 p-4"
 style="display:none">
 <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto">
 <div class="p-4 border-b border-gray-100">
 <h3 class="font-semibold text-gray-800">Konfirmasi Pernikahan</h3>
 </div>
 <div class="p-4">
 @csrf
 <p class="text-sm text-gray-600 mb-4">Konfirmasi permohonan pernikahan ini dan tambahkan ke kalender?</p>

 <div class="mb-4">
 <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Perkawinan</label>
 <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-xl text-gray-800 font-medium">
 <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
 <span id="konfirmasiTanggalDisplay">-</span>
 </div>
 <p class="text-xs text-gray-500 mt-1">Tanggal dari permohonan user</p>
 </div>

 <div class="mb-4">
 <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
 <textarea id="konfirmasiCatatan" rows="3"
 class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
 placeholder="Tambahkan catatan..."></textarea>
 </div>

 <div class="flex gap-3">
 <button type="button" onclick="closeKonfirmasiModal()"
 class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-xl font-medium hover:bg-gray-300">
 Batal
 </button>
 <button type="button" onclick="submitKonfirmasi()"
 class="flex-1 px-4 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700">
 <i class="fas fa-check mr-2"></i>Konfirmasi
 </button>
 </div>
 </div>
 </div>
</div>


{{-- =====================================================================
 MODAL: Tolak
 ===================================================================== --}}
<div id="tolakModal"
 class="fixed inset-0 bg-black/50 items-center justify-center z-50 p-4"
 style="display:none">
 <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto">
 <div class="p-4 border-b border-gray-100">
 <h3 class="font-semibold text-gray-800">Tolak Permohonan</h3>
 </div>
 <div class="p-4">
 <p class="text-sm text-gray-600 mb-4">Masukkan alasan penolakan permohonan ini.</p>

 <div class="mb-4">
 <label class="block text-sm font-medium text-gray-700 mb-1">
 Alasan Penolakan <span class="text-red-500">*</span>
 </label>
 <textarea id="tolakAlasan" rows="4"
 class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500"
 placeholder="Jelaskan alasan penolakan..."></textarea>
 </div>

 <div class="flex gap-3">
 <button type="button" onclick="closeTolakModal()"
 class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-xl font-medium hover:bg-gray-300">
 Batal
 </button>
 <button type="button" onclick="submitTolak()"
 class="flex-1 px-4 py-2 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700">
 <i class="fas fa-times mr-2"></i>Tolak
 </button>
 </div>
 </div>
 </div>
</div>


{{-- =====================================================================
 STYLES
 ===================================================================== --}}
<style>
 /* =====================================================================
    Modal Base
 ===================================================================== */
 .modal-open { display: flex !important; }

 /* Backdrop blur untuk modal */
 #detailModal,
 #konfirmasiModal,
 #tolakModal {
 backdrop-filter: blur(4px);
 -webkit-backdrop-filter: blur(4px);
 background-color: rgba(0, 0, 0, 0.55);
 animation: fadeInBackdrop 0.2s ease;
 }

 @keyframes fadeInBackdrop {
 from { opacity: 0; }
 to   { opacity: 1; }
 }

 /* Modal card slide-in */
 #detailModal > div,
 #konfirmasiModal > div,
 #tolakModal > div {
 animation: slideUpModal 0.25s cubic-bezier(0.34, 1.3, 0.64, 1);
 }

 @keyframes slideUpModal {
 from { opacity: 0; transform: translateY(24px) scale(0.97); }
 to   { opacity: 1; transform: translateY(0)   scale(1);    }
 }

 /* =====================================================================
    Detail Modal — Header
 ===================================================================== */
 #detailModal .modal-header {
 background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
 color: white;
 padding: 16px 20px;
 border-radius: 16px 16px 0 0;
 display: flex;
 align-items: center;
 justify-content: space-between;
 }

 #detailModal .modal-header h3 {
 font-size: 15px;
 font-weight: 700;
 color: white;
 letter-spacing: 0.01em;
 }

 #detailModal .modal-header button {
 width: 32px;
 height: 32px;
 border-radius: 50%;
 background: rgba(255,255,255,0.18);
 display: flex;
 align-items: center;
 justify-content: center;
 transition: background 0.2s;
 color: white;
 }
 #detailModal .modal-header button:hover {
 background: rgba(255,255,255,0.32);
 }

 /* =====================================================================
    Detail Content — Info Rows
 ===================================================================== */
 .detail-section {
 padding: 14px 0;
 border-bottom: 1px solid #f1f5f9;
 }
 .detail-section:last-child {
 border-bottom: none;
 }

 .detail-label {
 font-size: 11px;
 font-weight: 600;
 text-transform: uppercase;
 letter-spacing: 0.06em;
 color: #94a3b8;
 margin-bottom: 4px;
 }

 .detail-value {
 font-size: 14px;
 font-weight: 600;
 color: #1e293b;
 }

 .detail-value.mono {
 font-family: 'Courier New', monospace;
 color: #2563eb;
 font-size: 13px;
 background: #eff6ff;
 display: inline-block;
 padding: 2px 8px;
 border-radius: 6px;
 border: 1px solid #bfdbfe;
 }

 /* =====================================================================
    KTP Dokumen List
 ===================================================================== */
 .ktp-doc-list {
 display: flex;
 flex-direction: column;
 gap: 8px;
 }

 .ktp-doc-row {
 display: flex;
 align-items: center;
 justify-content: space-between;
 padding: 10px 14px;
 background: #f8fafc;
 border: 1.5px solid #e2e8f0;
 border-radius: 10px;
 transition: border-color 0.2s, background 0.2s;
 }
 .ktp-doc-row:hover {
 background: #eff6ff;
 border-color: #bfdbfe;
 }

 .ktp-doc-info {
 display: flex;
 align-items: center;
 gap: 10px;
 font-size: 13px;
 font-weight: 500;
 color: #374151;
 }

 .ktp-doc-btn {
 display: inline-flex;
 align-items: center;
 gap: 6px;
 padding: 6px 14px;
 background: #16a34a;
 color: white;
 font-size: 12px;
 font-weight: 600;
 border-radius: 8px;
 text-decoration: none;
 transition: background 0.2s, transform 0.15s;
 white-space: nowrap;
 }
 .ktp-doc-btn:hover {
 background: #15803d;
 transform: translateY(-1px);
 }

 /* =====================================================================
    Status Badge di Detail
 ===================================================================== */
 .status-pill {
 display: inline-flex;
 align-items: center;
 gap: 5px;
 padding: 5px 12px;
 border-radius: 20px;
 font-size: 12px;
 font-weight: 600;
 }

 /* =====================================================================
    Info Row (Tanggal & Gereja)
 ===================================================================== */
 .info-row {
 display: flex;
 align-items: center;
 justify-content: space-between;
 padding: 8px 12px;
 background: #f8fafc;
 border-radius: 8px;
 margin-bottom: 6px;
 }
 .info-row:last-child { margin-bottom: 0; }
 .info-row-key {
 font-size: 12px;
 color: #64748b;
 display: flex;
 align-items: center;
 gap: 6px;
 }
 .info-row-val {
 font-size: 13px;
 font-weight: 600;
 color: #1e293b;
 }

 /* =====================================================================
    Action Buttons
 ===================================================================== */
 .modal-action-btn {
 flex: 1;
 padding: 11px 16px;
 border-radius: 12px;
 font-weight: 700;
 font-size: 13px;
 display: flex;
 align-items: center;
 justify-content: center;
 gap: 7px;
 transition: all 0.18s;
 cursor: pointer;
 border: none;
 }
 .modal-action-btn:active { transform: scale(0.97); }

 .btn-tolak {
 background: #fef2f2;
 color: #dc2626;
 border: 1.5px solid #fecaca;
 }
 .btn-tolak:hover {
 background: #dc2626;
 color: white;
 border-color: #dc2626;
 }

 .btn-konfirmasi {
 background: linear-gradient(135deg, #16a34a, #22c55e);
 color: white;
 box-shadow: 0 4px 12px rgba(34,197,94,0.35);
 }
 .btn-konfirmasi:hover {
 background: linear-gradient(135deg, #15803d, #16a34a);
 box-shadow: 0 4px 16px rgba(34,197,94,0.45);
 }

 /* =====================================================================
    Dokumen Final
 ===================================================================== */
 .dokumen-final-row {
 display: flex;
 align-items: center;
 justify-content: space-between;
 padding: 9px 12px;
 border-radius: 8px;
 margin-bottom: 5px;
 }
 .dokumen-final-row.available {
 background: #f0fdf4;
 border: 1px solid #bbf7d0;
 }
 .dokumen-final-row.unavailable {
 background: #f8fafc;
 border: 1px solid #e2e8f0;
 }

 /* =====================================================================
    Scrollbar Custom
 ===================================================================== */
 #detailContent::-webkit-scrollbar { width: 5px; }
 #detailContent::-webkit-scrollbar-track { background: transparent; }
 #detailContent::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
 #detailContent::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

 #permohonanList::-webkit-scrollbar { width: 4px; }
 #permohonanList::-webkit-scrollbar-track { background: transparent; }
 #permohonanList::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

 /* =====================================================================
    FullCalendar Tweaks
 ===================================================================== */
 .fc .fc-button-group { gap: 6px; }
 .fc .fc-button {
 margin: 0 !important;
 padding: 7px 14px !important;
 font-size: 13px !important;
 font-weight: 500 !important;
 border-radius: 8px !important;
 }
 .fc .fc-button-primary {
 background-color: #3b82f6 !important;
 border-color: #3b82f6 !important;
 }
 .fc .fc-button-primary:hover {
 background-color: #2563eb !important;
 border-color: #2563eb !important;
 }
 .fc .fc-button-active {
 background-color: #1d4ed8 !important;
 border-color: #1d4ed8 !important;
 }
 .fc .fc-toolbar-chunk { display: flex; align-items: center; gap: 8px; }

 .calendar-loading {
 display: flex;
 align-items: center;
 justify-content: center;
 min-height: 520px;
 color: #9ca3af;
 }

 .fc-daygrid-event {
 display: flex !important;
 align-items: center !important;
 justify-content: center !important;
 text-align: center !important;
 min-height: 32px !important;
 }
 .fc-event {
 display: flex !important;
 align-items: center !important;
 justify-content: center !important;
 text-align: center !important;
 }
 .fc-event-main {
 display: flex !important;
 align-items: center !important;
 justify-content: center !important;
 width: 100% !important;
 height: 100% !important;
 }
 .fc-event-title {
 width: 100% !important;
 text-align: center !important;
 }
</style>


{{-- =====================================================================
 SCRIPTS
 ===================================================================== --}}
<script>
// -----------------------------------------------------------------------
// Fallback SwalHelper  -  aman jika layout belum menyediakan SwalHelper
// -----------------------------------------------------------------------
if (typeof SwalHelper === 'undefined') {
 var SwalHelper = {
 loading: function(msg) {
 if (typeof Swal !== 'undefined') {
 Swal.fire({ title: msg || 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
 }
 },
 close: function() {
 if (typeof Swal !== 'undefined') Swal.close();
 },
 success: function(msg) {
 if (typeof Swal !== 'undefined') {
 Swal.fire({ icon: 'success', title: 'Berhasil', text: msg, timer: 2000, showConfirmButton: false });
 } else if (typeof fireToast !== 'undefined') {
 fireToast({ type: 'success', icon: 'success', title: msg || 'Berhasil', timer: 4000 });
 } else if (window.__nativeAlert) { window.__nativeAlert(msg); }
 },
 error: function(msg) {
 if (typeof Swal !== 'undefined') {
 Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
 } else if (typeof fireToast !== 'undefined') {
 fireToast({ type: 'error', icon: 'error', title: 'Gagal', problem: 'Terjadi kesalahan pada proses ini.', solution: 'Periksa data yang dimasukkan dan coba lagi.' });
 } else if (window.__nativeAlert) { window.__nativeAlert('Error: ' + msg); }
 }
 };
}

// -----------------------------------------------------------------------
// State
// -----------------------------------------------------------------------
let selectedPernikahanId = null;
let currentPernikahanData = null;
let calendarInitialized = false;
let calendarInstance = null; // Referensi ke FullCalendar instance

// -----------------------------------------------------------------------
// Data events untuk kalender (di-render dari PHP)
// -----------------------------------------------------------------------
const calendarEventsData = @json($calendarEventsData);

// -----------------------------------------------------------------------
// Inisialisasi FullCalendar
// -----------------------------------------------------------------------
function initCalendar() {
 const calendarEl = document.getElementById('calendar');
 if (!calendarEl) return;

 // Cek apakah FullCalendar sudah ter-load
 if (typeof FullCalendar === 'undefined') {
 console.error('FullCalendar belum ter-load. Periksa CDN di @@push scripts.');
 calendarEl.innerHTML = '<p class="text-center text-red-500 py-10">FullCalendar gagal dimuat. Refresh halaman.</p>';
 return;
 }

 try {
 const cal = new FullCalendar.Calendar(calendarEl, {
 initialView: 'dayGridMonth',
 locale: 'id',
 displayEventTime: false,
 headerToolbar: {
 left: 'prev,next today',
 center: 'title',
 right: 'dayGridMonth,timeGridWeek,timeGridDay',
 },
 buttonText: {
 today: 'Hari Ini',
 month: 'Bulan',
 week: 'Minggu',
 day: 'Hari',
 },
 events: calendarEventsData,
 eventClick: function(info) {
 showDetail(info.event.extendedProps.pernikahan_id);
 },
 eventDidMount: function(info) {
 info.el.title = info.event.title;
 },
 });

 cal.render();
 calendarInstance = cal; // Simpan referensi calendar
 calendarInitialized = true;
 console.log('FullCalendar berhasil diinisialisasi dengan ' + calendarEventsData.length + ' events');
 } catch (error) {
 console.error('Gagal inisialisasi FullCalendar:', error);
 calendarEl.innerHTML = '<p class="text-center text-red-500 py-10">Gagal memuat kalender: ' + error.message + '</p>';
 }
}

// Jalankan setelah halaman sepenuhnya dimuat
window.addEventListener('load', function() {
 // Tunggu sebentar untuk memastikan semua script ter-load
 setTimeout(function() {
 if (!calendarInitialized) {
 initCalendar();
 }
 }, 100);
});


// -----------------------------------------------------------------------
// FIX 2  --  Modal Detail
// Menggunakan style="display:flex/none" agar tidak bentrok Tailwind hidden
// -----------------------------------------------------------------------
function showDetail(pernikahanId) {
 if (!pernikahanId) return;
 selectedPernikahanId = pernikahanId;

 SwalHelper.loading('Memuat detail...');

 fetch(`{{ route('keagamaan.pernikahan.detail-ajax') }}`, {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': '{{ csrf_token() }}',
 },
 body: JSON.stringify({ pernikahan_id: pernikahanId }),
 })
 .then(res => {
 if (!res.ok) throw new Error('HTTP ' + res.status);
 return res.json();
 })
 .then(data => {
 SwalHelper.close();

 if (!data.success) {
 SwalHelper.error(data.message || 'Gagal memuat detail');
 return;
 }

 const p = data.data;
 currentPernikahanData = p;

 //  --  --  KTP files  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  //
 const ktpEntries = [
 { key: 'mempelai_pria', label: 'KTP Mempelai Pria' },
 { key: 'mempelai_wanita', label: 'KTP Mempelai Wanita' },
 { key: 'saksi_1', label: 'KTP Saksi 1' },
 { key: 'saksi_2', label: 'KTP Saksi 2' },
 ];
 const ktpHtml = ktpEntries
 .filter(e => p.ktp_files && p.ktp_files[e.key])
 .map(e => `
 <div class="ktp-doc-row">
 <div class="ktp-doc-info">
 <i class="fas fa-id-card text-blue-500"></i>
 <span>${e.label}</span>
 </div>
 <a href="${escHtml(p.ktp_files[e.key])}" target="_blank" rel="noopener" class="ktp-doc-btn">
 <i class="fas fa-external-link-alt"></i> Buka Dokumen
 </a>
 </div>
 `).join('');

 //  --  --  Dokumen Final hasil penerbitan Disdukcapil  --  --  --  --  --  --  --  --  --  --  --  --  --  --  //
 const dokumenFinalEntries = [
 { key: 'akta_pernikahan', label: 'Akta Pernikahan' },
 { key: 'kk_pasangan', label: 'KK Baru  -  Pasangan' },
 { key: 'kk_ortu_pria', label: 'KK Baru  -  Ortu Pria' },
 { key: 'kk_ortu_wanita', label: 'KK Baru  -  Ortu Wanita' },
 ];
 const df = p.dokumen_final || {};
 const anyDfUploaded = dokumenFinalEntries.some(e => df[e.key]);
 const dokumenFinalHtml = anyDfUploaded ? `
 <div class="detail-section">
 <p class="detail-label"><i class="fas fa-folder-open text-emerald-500 mr-1"></i>Dokumen Hasil Penerbitan Disdukcapil</p>
 <div class="mt-2 space-y-1">
 ${dokumenFinalEntries.map(e => df[e.key] ? `
 <div class="dokumen-final-row available">
 <span class="text-xs font-medium text-gray-700 flex items-center gap-2"><i class="fas fa-file-pdf text-emerald-600"></i>${e.label}</span>
 <a href="${escHtml(df[e.key])}" target="_blank" rel="noopener"
 class="text-xs text-blue-600 hover:text-blue-800 font-bold inline-flex items-center gap-1">
 <i class="fas fa-external-link-alt"></i> Lihat
 </a>
 </div>
 ` : `
 <div class="dokumen-final-row unavailable">
 <span class="text-xs text-gray-400 flex items-center gap-2"><i class="fas fa-file-pdf text-gray-300"></i>${e.label}</span>
 <span class="text-xs text-gray-400 italic">Belum tersedia</span>
 </div>
 `).join('')}
 </div>
 ${df.uploaded_at ? `<p class="text-xs text-gray-400 mt-2"><i class="fas fa-clock mr-1"></i>Diupload: ${escHtml(df.uploaded_at)}</p>` : ''}
 </div>
 ` : '';

 //  --  --  Build konten modal  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  --  //
 document.getElementById('detailContent').innerHTML = `
 <div>
 <!-- Status + Nomor Antrian -->
 <div class="flex items-center justify-between mb-4">
 <span class="status-pill ${escHtml(p.status_color)}">${escHtml(p.status_label)}</span>
 <span class="detail-value mono">${escHtml(p.nomor_antrian)}</span>
 </div>

 <!-- Mempelai -->
 <div class="detail-section">
 <p class="detail-label"><i class="fas fa-heart mr-1"></i>Mempelai</p>
 <p class="detail-value">${escHtml(p.nama_mempelai_pria)}${p.nama_mempelai_wanita ? ' <span class="text-gray-400 font-normal">&amp;</span> ' + escHtml(p.nama_mempelai_wanita) : ''}</p>
 </div>

 <!-- Pemohon -->
 <div class="detail-section">
 <p class="detail-label"><i class="fas fa-user mr-1"></i>Pemohon</p>
 <p class="detail-value">${escHtml(p.nama_pemohon)}</p>
 ${p.alamat_pemohon ? `<p class="text-xs text-gray-500 mt-1 flex items-center gap-1"><i class="fas fa-map-marker-alt text-blue-400"></i>${escHtml(p.alamat_pemohon)}</p>` : ''}
 </div>

 <!-- KTP -->
 ${ktpHtml ? `
 <div class="detail-section">
 <p class="detail-label"><i class="fas fa-id-card mr-1"></i>Berkas KTP</p>
 <div class="ktp-doc-list mt-2">${ktpHtml}</div>
 </div>` : ''}

 <!-- Tanggal & Gereja -->
 <div class="detail-section">
 <p class="detail-label"><i class="fas fa-calendar-alt mr-1"></i>Jadwal Pernikahan</p>
 <div class="mt-2 space-y-2">
 <div class="info-row">
 <span class="info-row-key"><i class="fas fa-calendar text-blue-400"></i>Tanggal</span>
 <span class="info-row-val">${escHtml(p.tanggal_perkawinan || '-')}</span>
 </div>
 <div class="info-row">
 <span class="info-row-key"><i class="fas fa-church text-blue-400"></i>Gereja</span>
 <span class="info-row-val">${escHtml(p.nama_gereja || '-')}</span>
 </div>
 </div>
 </div>

 ${p.catatan_keagamaan ? `
 <div class="detail-section">
 <p class="detail-label"><i class="fas fa-sticky-note mr-1"></i>Catatan Keagamaan</p>
 <p class="text-sm text-gray-700 mt-1 leading-relaxed">${escHtml(p.catatan_keagamaan)}</p>
 </div>` : ''}

 ${p.alasan_ditolak ? `
 <div class="detail-section">
 <p class="detail-label text-red-400"><i class="fas fa-ban mr-1"></i>Alasan Ditolak</p>
 <div class="mt-1 bg-red-50 border border-red-100 rounded-lg p-3">
 <p class="text-sm text-red-600">${escHtml(p.alasan_ditolak)}</p>
 </div>
 </div>` : ''}

 ${dokumenFinalHtml}

 ${p.can_konfirmasi ? `
 <div class="flex gap-3 pt-4 mt-2 border-t border-gray-100">
 <button onclick="openTolakModal('${escHtml(p.pernikahan_id)}')" class="modal-action-btn btn-tolak">
 <i class="fas fa-times"></i>Tolak
 </button>
 <button onclick="openKonfirmasiModal('${escHtml(p.pernikahan_id)}')" class="modal-action-btn btn-konfirmasi">
 <i class="fas fa-check"></i>Konfirmasi
 </button>
 </div>
 ` : ''}
 </div>
 `;

 modalShow('detailModal');
 })
 .catch(err => {
 SwalHelper.close();
 console.error('showDetail error:', err);
 SwalHelper.error('Terjadi kesalahan saat memuat detail');
 });
}

function closeDetailModal() { modalHide('detailModal'); }


// -----------------------------------------------------------------------
// Modal: Konfirmasi
// -----------------------------------------------------------------------
function openKonfirmasiModal(pernikahanId) {
 closeDetailModal();
 selectedPernikahanId = pernikahanId;

 if (currentPernikahanData) {
 document.getElementById('konfirmasiTanggalDisplay').textContent =
 currentPernikahanData.tanggal_perkawinan || '-';
 }
 document.getElementById('konfirmasiCatatan').value = '';
 modalShow('konfirmasiModal');
}

function closeKonfirmasiModal() {
 modalHide('konfirmasiModal');
 selectedPernikahanId = null;
}

function submitKonfirmasi() {
 if (!selectedPernikahanId) return;

 const formData = new FormData();
 formData.append('_token', '{{ csrf_token() }}');
 formData.append('pernikahan_id', selectedPernikahanId);
 formData.append('status', 'diterima');
 formData.append('catatan', document.getElementById('konfirmasiCatatan').value);

 if (currentPernikahanData && currentPernikahanData.tanggal_perkawinan_raw) {
 formData.append('tanggal_perkawinan', currentPernikahanData.tanggal_perkawinan_raw);
 }

 SwalHelper.loading('Memproses...');

 fetch('/keagamaan/pernikahan/' + selectedPernikahanId + '/konfirmasi-jemaat', {
 method: 'POST',
 headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
 body: formData,
 })
 .then(res => res.json())
 .then(data => {
 SwalHelper.close();
 if (data.success) {
 closeKonfirmasiModal();

 // Tampilkan toast tanpa backdrop blur
 const Toast = Swal.mixin({
 toast: true,
 position: 'top-end',
 showConfirmButton: false,
 timer: 5000,
 timerProgressBar: true,
 backdrop: false,
 didOpen: (toast) => {
 toast.addEventListener('mouseenter', Swal.stopTimer);
 toast.addEventListener('mouseleave', Swal.resumeTimer);
 }
 });
 Toast.fire({
 icon: 'success',
 title: data.message || 'Konfirmasi berhasil',
 iconColor: '#22c55e'
 });

 // Update UI tanpa reload
 updateUIAfterConfirm(selectedPernikahanId, 'approve');
 } else {
 SwalHelper.error(data.message || 'Gagal memproses konfirmasi');
 }
 })
 .catch(() => { SwalHelper.close(); SwalHelper.error('Terjadi kesalahan'); });
}


// -----------------------------------------------------------------------
// Modal: Tolak
// -----------------------------------------------------------------------
function openTolakModal(pernikahanId) {
 closeDetailModal();
 selectedPernikahanId = pernikahanId;
 document.getElementById('tolakAlasan').value = '';
 modalShow('tolakModal');
}

function closeTolakModal() {
 modalHide('tolakModal');
 selectedPernikahanId = null;
}

function submitTolak() {
 if (!selectedPernikahanId) return;

 const alasan = document.getElementById('tolakAlasan').value.trim();
 if (!alasan) {
 SwalHelper.error('Alasan penolakan wajib diisi');
 return;
 }

 SwalHelper.loading('Memproses...');

 fetch('/keagamaan/pernikahan/' + selectedPernikahanId + '/konfirmasi-jemaat', {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': '{{ csrf_token() }}',
 },
 body: JSON.stringify({
 _token: '{{ csrf_token() }}',
 status: 'ditolak',
 catatan: alasan,
 }),
 })
 .then(res => res.json())
 .then(data => {
 SwalHelper.close();
 if (data.success) {
 closeTolakModal();

 // Tampilkan toast tanpa backdrop blur
 const Toast = Swal.mixin({
 toast: true,
 position: 'top-end',
 showConfirmButton: false,
 timer: 5000,
 timerProgressBar: true,
 backdrop: false,
 didOpen: (toast) => {
 toast.addEventListener('mouseenter', Swal.stopTimer);
 toast.addEventListener('mouseleave', Swal.resumeTimer);
 }
 });
 Toast.fire({
 icon: 'warning',
 title: data.message || 'Permohonan ditolak',
 iconColor: '#eab308'
 });

 // Update UI tanpa reload
 updateUIAfterConfirm(selectedPernikahanId, 'reject');
 } else {
 SwalHelper.error(data.message || 'Gagal memproses penolakan');
 }
 })
 .catch(() => { SwalHelper.close(); SwalHelper.error('Terjadi kesalahan'); });
}


// -----------------------------------------------------------------------
// FIX 3  --  Filter daftar permohonan
// Menggunakan style.display (bukan Tailwind class toggle) agar konsisten
// -----------------------------------------------------------------------
function filterList(value) {
 // Ambil value dari parameter atau dari select jika tidak diberikan
 const filter = value !== undefined ? value : document.getElementById('filterStatus').value;

 document.querySelectorAll('.permohonan-item').forEach(function(item) {
 const status = item.getAttribute('data-status');
 item.style.display = (filter === 'all' || status === filter) ? 'block' : 'none';
 });
}


// -----------------------------------------------------------------------
// Update UI setelah confirm/reject tanpa reload
// -----------------------------------------------------------------------
async function updateUIAfterConfirm(pernikahanId, action) {
 try {
 // Jika approve dan ada tanggal, tambahkan event ke FullCalendar
 if (action === 'approve' && currentPernikahanData && currentPernikahanData.tanggal_perkawinan_raw && calendarInstance) {
 const tanggal = currentPernikahanData.tanggal_perkawinan_raw;
 // Tambahkan event baru ke kalender
 calendarInstance.addEvent({
 id: pernikahanId,
 title: (currentPernikahanData.nama_mempelai_pria || '') + (currentPernikahanData.nama_mempelai_wanita ? ' & ' + currentPernikahanData.nama_mempelai_wanita : '') || 'Pernikahan',
 start: tanggal,
 backgroundColor: '#3b82f6',
 borderColor: 'transparent',
 extendedProps: {
 pernikahan_id: pernikahanId
 }
 });
 }

 // Update/remove item dari list
 const listItem = document.querySelector(`.permohonan-item[onclick*="${pernikahanId}"]`);
 if (listItem) {
 if (action === 'approve') {
 // Update status badge
 const badge = listItem.querySelector('.bg-blue-100');
 if (badge) {
 badge.classList.remove('bg-blue-100', 'text-blue-700');
 badge.classList.add('bg-green-100', 'text-green-700');
 badge.innerHTML = '<i class="fas fa-check mr-1"></i>Disetujui';
 }
 } else if (action === 'reject') {
 // Update ke status rejected
 const badge = listItem.querySelector('.bg-blue-100, .bg-green-100');
 if (badge) {
 badge.classList.remove('bg-blue-100', 'text-blue-700', 'bg-green-100', 'text-green-700');
 badge.classList.add('bg-red-100', 'text-red-700');
 badge.innerHTML = '<i class="fas fa-times mr-1"></i>Ditolak';
 }
 // Update data-status attribute
 listItem.setAttribute('data-status', 'rejected');
 }
 }

 // Refresh list dari server untuk data terbaru
 const listResponse = await fetch(window.location.href);
 const listText = await listResponse.text();
 const parser = new DOMParser();
 const newDoc = parser.parseFromString(listText, 'text/html');
 const newList = newDoc.getElementById('permohonanList');
 if (newList) {
 document.getElementById('permohonanList').innerHTML = newList.innerHTML;
 // Re-attach filter jika ada
 const currentFilter = document.getElementById('filterStatus')?.value || 'all';
 filterList(currentFilter);
 }

 } catch (error) {
 console.error('Error updating UI:', error);
 }
}


// -----------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------

/** Tampilkan modal (flex agar konten center) */
function modalShow(id) {
 const el = document.getElementById(id);
 if (el) el.style.display = 'flex';
}

/** Sembunyikan modal */
function modalHide(id) {
 const el = document.getElementById(id);
 if (el) el.style.display = 'none';
}

/** Escape HTML untuk konten yang disisipkan ke innerHTML */
function escHtml(str) {
 if (str === null || str === undefined) return '';
 return String(str)
 .replace(/&/g, '&amp;')
 .replace(/</g, '&lt;')
 .replace(/>/g, '&gt;')
 .replace(/"/g, '&quot;')
 .replace(/'/g, '&#39;');
}

function refreshPage() { location.reload(); }

// -----------------------------------------------------------------------
// Tutup modal saat klik backdrop
// -----------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function() {
 ['detailModal', 'konfirmasiModal', 'tolakModal'].forEach(function(id) {
 const el = document.getElementById(id);
 if (!el) return;
 el.addEventListener('click', function(e) {
 if (e.target === el) modalHide(id);
 });
 });
});


// ====================================================================
// AUTO-REFRESH: Check update status pernikahan dari admin
// ====================================================================
let autoRefreshInterval = null;
let lastCheckTime = new Date().toISOString();
let isChecking = false;
let knownStatuses = new Map(); // Simpan status yang sudah diketahui

// Inisialisasi knownStatuses dari data awal
@php
 $initialStatuses = [];
 foreach($pernikahan as $item) {
 $initialStatuses[] = [
 'id' => $item->pernikahan_id,
 'status' => $item->status,
 'label' => $item->nama_mempelai_pria
 ];
 }
@endphp
const initialData = @json($initialStatuses);
initialData.forEach(item => {
 knownStatuses.set(item.id, item.status);
});

// Fungsi check update dari server
async function checkStatusUpdates() {
 if (isChecking) return;
 isChecking = true;

 try {
 const response = await fetch(`{{ route('keagamaan.pernikahan.check-updates') }}?last_check=${encodeURIComponent(lastCheckTime)}`, {
 method: 'GET',
 headers: {
 'Accept': 'application/json',
 'X-Requested-With': 'XMLHttpRequest'
 },
 credentials: 'same-origin'
 });

 const data = await response.json();

 if (data.timestamp) {
 lastCheckTime = data.timestamp;
 }

 if (data.success && data.has_updates && data.updates.length > 0) {
 console.log(' Updates detected:', data.updates);

 // Cek perubahan status
 data.updates.forEach(update => {
 const oldStatus = knownStatuses.get(update.pernikahan_id);
 const newStatus = update.status;

 // Jika status berubah
 if (oldStatus && oldStatus !== newStatus) {
 console.log(`Status changed for ${(update.nama_mempelai_pria || '') + (update.nama_mempelai_wanita ? ' & ' + update.nama_mempelai_wanita : '')}: ${oldStatus} -> ${newStatus}`);

 // Update knownStatuses
 knownStatuses.set(update.pernikahan_id, newStatus);

 // Jika berubah ke Disetujui
 if (newStatus === 'TANGGAL_DISETUJUI') {
 const Toast = Swal.mixin({
 toast: true,
 position: 'top-end',
 showConfirmButton: true,
 confirmButtonText: 'Lihat',
 confirmButtonColor: '#16a34a',
 timer: 0,
 backdrop: false,
 });

 Toast.fire({
 icon: 'success',
 title: 'Tanggal Disetujui!',
 html: `
 <div class="text-left">
 <p class="font-medium">${(update.nama_mempelai_pria || '') + (update.nama_mempelai_wanita ? ' & ' + update.nama_mempelai_wanita : '')}</p>
 <p class="text-sm text-gray-600">${update.nomor_antrian}</p>
 <p class="text-sm text-green-600 mt-1"><i class="fas fa-check-circle mr-1"></i>${update.tanggal_perkawinan}</p>
 </div>
 `,
 }).then((result) => {
 if (result.isConfirmed) {
 location.reload();
 }
 });
 }
 }
 });

 // Update UI jika ada perubahan
 if (data.updates.length > 0) {
 await refreshUIFromServer();
 }
 }

 } catch (error) {
 console.error('Error checking updates:', error);
 } finally {
 isChecking = false;
 }
}

// Refresh UI dari server
async function refreshUIFromServer() {
 try {
 const response = await fetch(window.location.href);
 const html = await response.text();
 const parser = new DOMParser();
 const newDoc = parser.parseFromString(html, 'text/html');

 // Update list permohonan
 const newList = newDoc.getElementById('permohonanList');
 if (newList) {
 document.getElementById('permohonanList').innerHTML = newList.innerHTML;
 // Re-apply filter
 const currentFilter = document.getElementById('filterStatus')?.value || 'all';
 filterList(currentFilter);
 }

 // Update statistics jika ada
 const newStats = newDoc.querySelectorAll('.grid.grid-cols-2.md\\:grid-cols-4 .text-2xl');
 if (newStats.length > 0) {
 const currentStats = document.querySelectorAll('.grid.grid-cols-2.md\\:grid-cols-4 .text-2xl');
 newStats.forEach((stat, i) => {
 if (currentStats[i]) {
 currentStats[i].textContent = stat.textContent;
 }
 });
 }

 } catch (error) {
 console.error('Error refreshing UI:', error);
 }
}

// Mulai auto-refresh
function startAutoRefresh(intervalSeconds = 10) {
 stopAutoRefresh();
 console.log(` Auto-refresh started (every ${intervalSeconds}s)`);

 setTimeout(() => {
 checkStatusUpdates();
 autoRefreshInterval = setInterval(() => {
 checkStatusUpdates();
 }, intervalSeconds * 1000);
 }, 2000);
}

// Stop auto-refresh
function stopAutoRefresh() {
 if (autoRefreshInterval) {
 clearInterval(autoRefreshInterval);
 autoRefreshInterval = null;
 console.log(' Auto-refresh stopped');
 }
}

// Mulai auto-refresh saat page load
startAutoRefresh(10);

// Stop auto-refresh saat page akan unload
window.addEventListener('beforeunload', function() {
 stopAutoRefresh();
});
</script>

@endsection