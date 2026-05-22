@extends('layouts.keagamaan')

@section('title', 'Request Tanggal ke Disdukcapil')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Page Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Request Tanggal ke Disdukcapil</h1>
                <p class="text-gray-500 text-sm">Kirim permintaan tanggal dan upload berkas persyaratan</p>
            </div>
            <div class="flex gap-3 items-center">
                <button onclick="openRequestModal()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Request Tanggal Baru
                </button>
                <a href="{{ route('keagamaan.pernikahan.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl text-sm font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="px-6 pb-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content - List Request Tanggal --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Filter Tabs --}}
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button onclick="switchTab('all')" id="tab-all" class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-blue-600 text-blue-600">
                                Semua
                            </button>
                            <button onclick="switchTab('pending')" id="tab-pending" class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                                Menunggu
                            </button>
                            <button onclick="switchTab('approved')" id="tab-approved" class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                                Disetujui
                            </button>
                            <button onclick="switchTab('rejected')" id="tab-rejected" class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                                Ditolak
                            </button>
                        </nav>
                    </div>

                    {{-- Table All --}}
                    <div id="content-all" class="tab-content p-4">
                        @if($requestTanggal->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($requestTanggal as $item)
                                <div class="request-item p-4 border border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50/30 transition-all"
                                     data-status="{{ $item->request_status ?? 'pending' }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <span class="font-mono text-sm font-bold text-blue-600">{{ $item->nomor_antrian }}</span>
                                                @if($item->request_status === 'approved')
                                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                        <i class="fas fa-check mr-1"></i>Disetujui
                                                    </span>
                                                @elseif($item->request_status === 'rejected')
                                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                        <i class="fas fa-times mr-1"></i>Ditolak
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                                        <i class="fas fa-clock mr-1"></i>Menunggu
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="font-medium text-gray-800">{{ $item->nama_mempelai_pria }} {{ $item->nama_mempelai_wanita ? '& ' . $item->nama_mempelai_wanita : '' }}</p>
                                            <p class="text-sm text-gray-500"><i class="fas fa-calendar mr-1"></i>{{ $item->tanggal_perkawinan?->format('d F Y') ?? 'Belum ditetapkan' }}</p>
                                            <p class="text-sm text-gray-500"><i class="fas fa-church mr-1"></i>{{ $item->nama_gereja }}</p>
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            @if($item->request_status === 'approved')
                                                @if(!$item->file_berkas_acara || $item->dokumen->isEmpty())
                                                    <a href="{{ route('keagamaan.pernikahan.upload-berkas') }}"
                                                       class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 text-center">
                                                        <i class="fas fa-upload mr-1"></i>Upload
                                                    </a>
                                                @else
                                                    <button onclick="printBerkas('{{ $item->pernikahan_id }}')"
                                                            class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700">
                                                        <i class="fas fa-print mr-1"></i>Print
                                                    </button>
                                                @endif
                                            @endif
                                            <button onclick="showRequestDetail('{{ $item->pernikahan_id }}')"
                                                    class="px-3 py-1.5 bg-gray-200 text-gray-800 rounded-lg text-xs font-medium hover:bg-gray-300">
                                                <i class="fas fa-eye mr-1"></i>Detail
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-500">
                                <i class="fas fa-inbox text-5xl mb-4 block mx-auto text-gray-300"></i>
                                <p class="text-lg font-medium">Belum ada request tanggal</p>
                                <p class="text-sm">Request tanggal akan muncul setelah tanggal perkawinan disetujui</p>
                            </div>
                        @endif
                    </div>

                    {{-- Table Pending --}}
                    <div id="content-pending" class="tab-content p-4 hidden">
                        @php
                            $pendingItems = $requestTanggal->where('request_status', 'pending');
                        @endphp
                        @if($pendingItems->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($pendingItems as $item)
                                <div class="request-item p-4 border border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50/30 transition-all"
                                     data-status="pending">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <span class="font-mono text-sm font-bold text-blue-600">{{ $item->nomor_antrian }}</span>
                                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                                    <i class="fas fa-clock mr-1"></i>Menunggu
                                                </span>
                                            </div>
                                            <p class="font-medium text-gray-800">{{ $item->nama_mempelai_pria }} {{ $item->nama_mempelai_wanita ? '& ' . $item->nama_mempelai_wanita : '' }}</p>
                                            <p class="text-sm text-gray-500"><i class="fas fa-calendar mr-1"></i>{{ $item->tanggal_perkawinan?->format('d F Y') ?? 'Belum ditetapkan' }}</p>
                                            <p class="text-sm text-gray-500"><i class="fas fa-church mr-1"></i>{{ $item->nama_gereja }}</p>
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <button onclick="showRequestDetail('{{ $item->pernikahan_id }}')"
                                                    class="px-3 py-1.5 bg-gray-200 text-gray-800 rounded-lg text-xs font-medium hover:bg-gray-300">
                                                <i class="fas fa-eye mr-1"></i>Detail
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-500">
                                <i class="fas fa-clock text-5xl mb-4 block mx-auto text-yellow-300"></i>
                                <p class="text-lg font-medium">Tidak ada request menunggu</p>
                                <p class="text-sm">Semua request sudah diproses</p>
                            </div>
                        @endif
                    </div>

                    {{-- Table Approved --}}
                    <div id="content-approved" class="tab-content p-4 hidden">
                        @php
                            $approvedItems = $requestTanggal->where('request_status', 'approved');
                        @endphp
                        @if($approvedItems->isNotEmpty())
                            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                                    <div>
                                        <p class="font-medium text-green-800">{{ $approvedItems->count() }} Request Tanggal Disetujui</p>
                                        <p class="text-sm text-green-600">Silakan upload berkas persyaratan di bawah ini</p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                @foreach($approvedItems as $item)
                                <div class="request-item p-4 border border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50/30 transition-all"
                                     data-status="approved">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <span class="font-mono text-sm font-bold text-blue-600">{{ $item->nomor_antrian }}</span>
                                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                    <i class="fas fa-check mr-1"></i>Disetujui
                                                </span>
                                            </div>
                                            <p class="font-medium text-gray-800">{{ $item->nama_mempelai_pria }} {{ $item->nama_mempelai_wanita ? '& ' . $item->nama_mempelai_wanita : '' }}</p>
                                            <p class="text-sm text-gray-500"><i class="fas fa-calendar mr-1"></i>{{ $item->tanggal_perkawinan?->format('d F Y') ?? 'Belum ditetapkan' }}</p>
                                            <p class="text-sm text-gray-500"><i class="fas fa-church mr-1"></i>{{ $item->nama_gereja }}</p>
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            @if(!$item->file_berkas_acara || $item->dokumen->isEmpty())
                                                <a href="{{ route('keagamaan.pernikahan.upload-berkas') }}"
                                                   class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 text-center">
                                                    <i class="fas fa-upload mr-1"></i>Upload
                                                </a>
                                            @else
                                                <button onclick="printBerkas('{{ $item->pernikahan_id }}')"
                                                        class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700">
                                                    <i class="fas fa-print mr-1"></i>Print
                                                </button>
                                            @endif
                                            <button onclick="showRequestDetail('{{ $item->pernikahan_id }}')"
                                                    class="px-3 py-1.5 bg-gray-200 text-gray-800 rounded-lg text-xs font-medium hover:bg-gray-300">
                                                <i class="fas fa-eye mr-1"></i>Detail
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-500">
                                <i class="fas fa-inbox text-5xl mb-4 block mx-auto text-gray-300"></i>
                                <p class="text-lg font-medium">Belum ada request disetujui</p>
                                <p class="text-sm">Request yang disetujui akan muncul di sini</p>
                            </div>
                        @endif
                    </div>

                    {{-- Table Rejected --}}
                    <div id="content-rejected" class="tab-content p-4 hidden">
                        @php
                            $rejectedItems = $requestTanggal->where('request_status', 'rejected');
                        @endphp
                        @if($rejectedItems->isNotEmpty())
                            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                                    <div>
                                        <p class="font-medium text-red-800">{{ $rejectedItems->count() }} Request Tanggal Ditolak</p>
                                        <p class="text-sm text-red-600">Periksa alasan penolakan dan perbaiki data</p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                @foreach($rejectedItems as $item)
                                <div class="request-item p-4 border border-red-200 rounded-xl hover:border-red-400 hover:bg-red-50/30 transition-all"
                                     data-status="rejected">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <span class="font-mono text-sm font-bold text-red-600">{{ $item->nomor_antrian }}</span>
                                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                    <i class="fas fa-times mr-1"></i>Ditolak
                                                </span>
                                            </div>
                                            <p class="font-medium text-gray-800">{{ $item->nama_mempelai_pria }} {{ $item->nama_mempelai_wanita ? '& ' . $item->nama_mempelai_wanita : '' }}</p>
                                            <p class="text-sm text-gray-500"><i class="fas fa-calendar mr-1"></i>{{ $item->tanggal_perkawinan?->format('d F Y') ?? 'Belum ditetapkan' }}</p>
                                            <p class="text-sm text-gray-500"><i class="fas fa-church mr-1"></i>{{ $item->nama_gereja }}</p>
                                            @if($item->alasan_ditolak)
                                                <p class="text-sm text-red-600 mt-2"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $item->alasan_ditolak }}</p>
                                            @endif
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <button onclick="showRequestDetail('{{ $item->pernikahan_id }}')"
                                                    class="px-3 py-1.5 bg-gray-200 text-gray-800 rounded-lg text-xs font-medium hover:bg-gray-300">
                                                <i class="fas fa-eye mr-1"></i>Detail
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-500">
                                <i class="fas fa-check-circle text-5xl mb-4 block mx-auto text-green-300"></i>
                                <p class="text-lg font-medium">Tidak ada request ditolak</p>
                                <p class="text-sm">Semua request diproses dengan baik</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar - Info & Quick Actions --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Info Card --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>Informasi
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                            <p class="text-gray-600">Pastikan tanggal perkawinan sudah disetujui oleh admin</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                            <p class="text-gray-600">Request tanggal akan diverifikasi oleh Disdukcapil</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                            <p class="text-gray-600">Setelah disetujui, upload semua berkas persyaratan</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                            <p class="text-gray-600">Berkas akan diverifikasi sebelum diterbitkan</p>
                        </div>
                    </div>
                </div>

                {{-- Statistik --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Statistik Request</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Total Request</span>
                            <span class="font-bold text-gray-800">{{ $requestTanggal->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Menunggu</span>
                            <span class="font-bold text-yellow-600">{{ $requestTanggal->where('request_status', 'pending')->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Disetujui</span>
                            <span class="font-bold text-green-600">{{ $requestTanggal->where('request_status', 'approved')->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Ditolak</span>
                            <span class="font-bold text-red-600">{{ $requestTanggal->where('request_status', 'rejected')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Multi-Step Request Tanggal Modal --}}
<div id="requestModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
        {{-- Modal Header with Progress --}}
        <div class="p-4 border-b border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Request Tanggal Baru</h3>
                <button onclick="closeRequestModal()" class="p-2 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
            {{-- Progress Steps --}}
            <div class="flex items-center justify-between">
                <div class="flex flex-col items-center">
                    <div id="step1-indicator" class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-medium">1</div>
                    <span class="text-xs mt-1 text-gray-600">Pilih Jemaat</span>
                </div>
                <div class="flex-1 h-1 bg-gray-200 mx-2">
                    <div id="progress1" class="h-full bg-blue-600 transition-all duration-300" style="width: 0%"></div>
                </div>
                <div class="flex flex-col items-center">
                    <div id="step2-indicator" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-medium">2</div>
                    <span class="text-xs mt-1 text-gray-600">Konfirmasi</span>
                </div>
                <div class="flex-1 h-1 bg-gray-200 mx-2">
                    <div id="progress2" class="h-full bg-blue-600 transition-all duration-300" style="width: 0%"></div>
                </div>
                <div class="flex flex-col items-center">
                    <div id="step3-indicator" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-medium">3</div>
                    <span class="text-xs mt-1 text-gray-600">Selesai</span>
                </div>
            </div>
        </div>

        {{-- Step 1: Pilih Jemaat --}}
        <div id="step1" class="step-content p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Jemaat</label>
                <p class="text-xs text-gray-500 mb-3">Daftar jemaat yang tanggal perkawinannya sudah disetujui</p>
                <select id="selectJemaat" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="onJemaatSelected()">
                    <option value="">-- Pilih Jemaat --</option>
                    @if($availableJemaat->isNotEmpty())
                        @foreach($availableJemaat as $jemaat)
                            <option value="{{ $jemaat['pernikahan_id'] }}"
                                    data-nama="{{ $jemaat['nama_mempelai_pria'] }}"
                                    data-tanggal="{{ $jemaat['tanggal_perkawinan'] }}"
                                    data-gereja="{{ $jemaat['nama_gereja'] ?? '-' }}"
                                    data-antrian="{{ $jemaat['nomor_antrian'] }}">
                                {{ $jemaat['nama_mempelai_pria'] }} ({{ $jemaat['nomor_antrian'] }})
                            </option>
                        @endforeach
                    @else
                        <option value="" disabled>-- Tidak ada jemaat tersedia --</option>
                    @endif
                </select>
                @if($availableJemaat->isEmpty())
                    <p class="text-sm text-yellow-600 mt-2">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Tidak ada jemaat dengan status Tanggal Disetujui
                    </p>
                @endif
            </div>

            {{-- Detail Jemaat Terpilih --}}
            <div id="jemaatDetail" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <p id="detailNama" class="font-medium text-gray-800"></p>
                        <p id="detailTanggal" class="text-sm text-gray-600">
                            <i class="fas fa-calendar mr-1"></i><span></span>
                        </p>
                        <p id="detailGereja" class="text-sm text-gray-600">
                            <i class="fas fa-church mr-1"></i><span></span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button onclick="closeRequestModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-xl font-medium hover:bg-gray-300">
                    Batal
                </button>
                <button id="btnStep1Next" onclick="goToStep(2)" disabled class="flex-1 px-4 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed disabled:pointer-events-none disabled:opacity-50">
                    Lanjut <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>

        {{-- Step 2: Terms & Condition --}}
        <div id="step2" class="step-content hidden p-6">
            <div class="mb-4">
                <h4 class="font-medium text-gray-800 mb-3">Pernyataan Keagamaan</h4>
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Pastikan data yang dipilih sudah benar dan sesuai dengan jemaat yang akan melaksanakan perkawinan.
                    </p>
                </div>
                <div class="space-y-3 text-sm text-gray-600">
                    <p>Dengan ini saya menyatakan bahwa:</p>
                    <ul class="space-y-2 ml-4">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                            <span>Jemaat tersebut terdaftar dan terverifikasi di gereja/jemaat kami</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                            <span>Tanggal perkawinan yang tertera sudah disetujui dan sesuai</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                            <span>Request tanggal ini akan dikirim ke Disdukcapil untuk verifikasi</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mb-4">
                <label class="flex items-start gap-3 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                    <input type="checkbox" id="ackJemaat" class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 mt-0.5" onchange="checkStep2Form()">
                    <span class="text-sm text-gray-700">
                        Saya menyatakan bahwa jemaat di atas adalah benar jemaat dari gereja/jemaat kami dan bertanggung jawab atas kebenaran data ini.
                    </span>
                </label>
            </div>

            <div class="flex gap-3">
                <button onclick="goToStep(1)" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-xl font-medium hover:bg-gray-300">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </button>
                <button id="btnStep2Next" onclick="goToStep(3)" disabled class="flex-1 px-4 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed disabled:pointer-events-none disabled:opacity-50">
                    Lanjut <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>

        {{-- Step 3: Konfirmasi --}}
        <div id="step3" class="step-content hidden p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-2xl text-green-600"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-2">Konfirmasi Request Tanggal</h4>
                <p class="text-sm text-gray-600">Periksa kembali data sebelum mengirim request</p>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 mb-4 space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Nama Jemaat</span>
                    <span id="confirmNama" class="text-sm font-medium text-gray-800"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Tanggal Pernikahan</span>
                    <span id="confirmTanggal" class="text-sm font-medium text-gray-800"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Gereja</span>
                    <span id="confirmGereja" class="text-sm font-medium text-gray-800"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Nomor Antrian</span>
                    <span id="confirmAntrian" class="text-sm font-medium text-blue-600"></span>
                </div>
            </div>

            <div class="flex gap-3">
                <button onclick="goToStep(2)" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-xl font-medium hover:bg-gray-300">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </button>
                <button id="btnSubmit" onclick="submitRequest()" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700">
                    <i class="fas fa-paper-plane mr-2"></i>Kirim Request
                </button>
            </div>
        </div>

        {{-- Step 4: Sukses --}}
        <div id="step4" class="step-content hidden p-6">
            <div class="text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-3xl text-green-600"></i>
                </div>
                <h4 class="font-semibold text-gray-800 text-lg mb-2">Request Berhasil Dikirim!</h4>
                <p class="text-sm text-gray-600 mb-6">Request tanggal telah dikirim ke Disdukcapil untuk verifikasi. Anda dapat melihat status request pada halaman ini.</p>
                <button onclick="closeRequestModal(); location.reload();" class="w-full px-4 py-2 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700">
                    <i class="fas fa-check mr-2"></i>Selesai
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Detail Dokumen Modal --}}
<div id="detailModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white">
            <h3 class="font-semibold text-gray-800">Detail Dokumen</h3>
            <button onclick="closeDetailModal()" class="p-2 hover:bg-gray-100 rounded-lg transition">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]" id="detailModalContent">
            {{-- Content will be loaded dynamically --}}
        </div>
    </div>
</div>

<script>
// Data jemaat yang tersedia (langsung dari PHP, tidak perlu AJAX)
let selectedJemaat = null;
let currentStep = 1;

// Buka modal request
function openRequestModal() {
    document.getElementById('requestModal').classList.remove('hidden');
    resetForm();
}

// Tutup modal request
function closeRequestModal() {
    document.getElementById('requestModal').classList.add('hidden');
    resetForm();
}

// Reset form
function resetForm() {
    currentStep = 1;
    selectedJemaat = null;
    document.getElementById('selectJemaat').value = '';
    document.getElementById('ackJemaat').checked = false;
    document.getElementById('jemaatDetail').classList.add('hidden');
    document.getElementById('btnStep1Next').disabled = true;
    document.getElementById('btnStep2Next').disabled = true;
    updateStepUI();
}

// Update tampilan step
function updateStepUI() {
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));

    // Show current step
    document.getElementById(`step${currentStep}`).classList.remove('hidden');

    // Update indicators
    for (let i = 1; i <= 3; i++) {
        const indicator = document.getElementById(`step${i}-indicator`);
        if (i < currentStep || currentStep === 4) {
            // Completed steps
            indicator.className = 'w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-sm font-medium';
            indicator.innerHTML = '<i class="fas fa-check"></i>';
        } else if (i === currentStep) {
            // Current step
            indicator.className = 'w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-medium';
            indicator.textContent = i;
        } else {
            // Future steps
            indicator.className = 'w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-medium';
            indicator.textContent = i;
        }
    }

    // Update progress bars
    const progress1 = document.getElementById('progress1');
    const progress2 = document.getElementById('progress2');

    if (currentStep >= 2) {
        progress1.style.width = '100%';
    } else {
        progress1.style.width = '0%';
    }

    if (currentStep >= 3) {
        progress2.style.width = '100%';
    } else {
        progress2.style.width = '0%';
    }
}

// Pindah ke step tertentu
function goToStep(step) {
    if (step === 2) {
        const btn = document.getElementById('btnStep1Next');
        if (btn.disabled || !selectedJemaat) {
            return;
        }
    }
    if (step === 3 && !document.getElementById('ackJemaat').checked) return;

    currentStep = step;

    if (step === 3) {
        // Isi data konfirmasi
        document.getElementById('confirmNama').textContent = selectedJemaat.nama_mempelai_pria;
        document.getElementById('confirmTanggal').textContent = selectedJemaat.tanggal_perkawinan;
        document.getElementById('confirmGereja').textContent = selectedJemaat.nama_gereja;
        document.getElementById('confirmAntrian').textContent = selectedJemaat.nomor_antrian;
    }

    updateStepUI();
}

// Ketika jemaat dipilih
function onJemaatSelected() {
    const selectEl = document.getElementById('selectJemaat');
    const detailEl = document.getElementById('jemaatDetail');
    const btnNext = document.getElementById('btnStep1Next');

    const pernikahanId = selectEl.value;
    const selectedOption = selectEl.options[selectEl.selectedIndex];

    if (pernikahanId && selectedOption) {
        // Ambil data dari atribut data-* pada option
        selectedJemaat = {
            pernikahan_id: pernikahanId,
            nama_mempelai_pria: selectedOption.getAttribute('data-nama'),
            tanggal_perkawinan: selectedOption.getAttribute('data-tanggal'),
            nama_gereja: selectedOption.getAttribute('data-gereja'),
            nomor_antrian: selectedOption.getAttribute('data-antrian')
        };

        if (selectedJemaat) {
            document.getElementById('detailNama').textContent = selectedJemaat.nama_mempelai_pria;
            document.getElementById('detailTanggal').querySelector('span').textContent = selectedJemaat.tanggal_perkawinan;
            document.getElementById('detailGereja').querySelector('span').textContent = selectedJemaat.nama_gereja || '-';
            detailEl.classList.remove('hidden');
            btnNext.disabled = false;
        }
    } else {
        selectedJemaat = null;
        detailEl.classList.add('hidden');
        btnNext.disabled = true;
    }
}

// Check step 2 form
function checkStep2Form() {
    const checked = document.getElementById('ackJemaat').checked;
    document.getElementById('btnStep2Next').disabled = !checked;
}

// Submit request
function submitRequest() {
    const btnSubmit = document.getElementById('btnSubmit');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...';

    SwalHelper.loading('Mengirim request tanggal...');

    fetch(`{{ route('keagamaan.pernikahan.submit-request-tanggal') }}`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            pernikahan_id: selectedJemaat.pernikahan_id,
            ack_jemaat: true
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        SwalHelper.close();

        if (data.success) {
            currentStep = 4;
            updateStepUI();
            SwalHelper.success(data.message);
        } else {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Kirim Request';
            SwalHelper.error(data.message || 'Gagal mengirim request');
        }
    })
    .catch(error => {
        SwalHelper.close();
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Kirim Request';
        SwalHelper.error('Terjadi kesalahan saat mengirim request');
        console.error('Error:', error);
    });
}

function switchTab(tab) {
    // Hide all contents
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    // Remove active state from all tabs
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('border-blue-600', 'text-blue-600');
        el.classList.add('border-transparent', 'text-gray-500');
    });

    // Show selected content
    document.getElementById('content-' + tab).classList.remove('hidden');
    // Add active state to selected tab
    const activeTab = document.getElementById('tab-' + tab);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-blue-600', 'text-blue-600');
}

function printBerkas(pernikahanId) {
    window.open('/keagamaan/pernikahan/print-berkas/' + pernikahanId, '_blank');
}

function showRequestDetail(pernikahanId) {
    SwalHelper.loading('Memuat detail...');

    fetch(`/keagamaan/pernikahan/detail-dokumen/${pernikahanId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        SwalHelper.close();
        if (data.success) {
            const content = document.getElementById('detailModalContent');
            const dokumenList = data.dokumen && data.dokumen.length > 0
                ? data.dokumen.map(d => `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm">
                                <i class="fas ${d.is_image ? 'fa-image text-blue-500' : 'fa-file-pdf text-red-500'}"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 text-sm">${d.jenis_dokumen_label}</p>
                                <p class="text-xs text-gray-500">${d.original_filename || 'Dokumen'}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            ${d.status === 'DIVERIFIKASI' ? '<span class="text-xs text-green-600"><i class="fas fa-check-circle"></i></span>' : ''}
                            ${d.status === 'DITOLAK' ? '<span class="text-xs text-red-600"><i class="fas fa-times-circle"></i></span>' : ''}
                            <a href="${d.file_url}" target="_blank" class="text-xs text-blue-600 hover:text-blue-700">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                `).join('')
                : '<p class="text-center text-gray-500 py-8">Belum ada dokumen diupload</p>';

            content.innerHTML = `
                <div class="text-center mb-6">
                    <p class="font-mono text-sm font-bold text-blue-600 mb-1">${data.nomor_antrian || '-'}</p>
                    <p class="font-medium text-gray-800">${data.nama_mempelai_pria || ''} ${data.nama_mempelai_wanita ? '& ' + data.nama_mempelai_wanita : ''}</p>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-calendar mr-1"></i>${data.tanggal_perkawinan || 'Belum ditetapkan'}
                    </p>
                </div>
                <div class="mb-4">
                    <p class="text-sm font-medium text-gray-700 mb-3">Dokumen yang diupload:</p>
                    ${dokumenList}
                </div>
                ${data.dokumen && data.dokumen.length > 0 ? `
                    <div class="flex gap-3 mt-4 pt-4 border-t border-gray-200">
                        <a href="/keagamaan/pernikahan/upload-berkas"
                           class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 text-center">
                            <i class="fas fa-upload mr-1"></i>Upload Berkas
                        </a>
                    </div>
                ` : ''}
            `;
            document.getElementById('detailModal').classList.remove('hidden');
        } else {
            SwalHelper.error(data.message || 'Gagal memuat detail');
        }
    })
    .catch(error => {
        SwalHelper.close();
        SwalHelper.error('Terjadi kesalahan saat memuat detail');
    });
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

// Close request modal on outside click
document.getElementById('requestModal').addEventListener('click', function(e) {
    if (e.target === this) closeRequestModal();
});

// Close detail modal on outside click
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) closeDetailModal();
});

// ====================================================================
// SILENT AUTO-REFRESH: Check update status tanpa mengganggu UX
// ====================================================================
let lastCheckTime = new Date().toISOString();
let isChecking = false;
let checkInterval = null;

async function silentCheckUpdates() {
    if (isChecking) return;
    isChecking = true;

    try {
        const response = await fetch(`{{ route('keagamaan.pernikahan.check-updates') }}?last_check=${encodeURIComponent(lastCheckTime)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            cache: 'no-cache'
        });

        if (!response.ok) return;

        const data = await response.json();

        if (data.timestamp) {
            lastCheckTime = data.timestamp;
        }

        if (data.success && data.has_updates && data.updates.length > 0) {
            const approvedUpdates = data.updates.filter(u => u.status === 'TANGGAL_DISETUJUI');

            if (approvedUpdates.length > 0) {
                clearInterval(checkInterval);

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: true,
                    confirmButtonText: 'Muat Ulang',
                    confirmButtonColor: '#16a34a',
                    timer: 5000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Tanggal Telah Disetujui!',
                    html: `
                        <div class="text-left">
                            <p class="font-semibold">${approvedUpdates[0].nama_mempelai_pria}</p>
                            <p class="text-sm text-gray-600">${approvedUpdates[0].nomor_antrian}</p>
                            <p class="text-sm text-green-600 mt-1">
                                <i class="fas fa-calendar-check mr-1"></i>${approvedUpdates[0].tanggal_perkawinan}
                            </p>
                        </div>
                    `,
                }).then(() => {
                    location.reload();
                });
            }
        }
    } catch (error) {
        // Silent fail - tidak perlu ganggu user
    } finally {
        isChecking = false;
    }
}

// Mulai silent check 3 detik setelah load
setTimeout(() => {
    silentCheckUpdates();
    checkInterval = setInterval(silentCheckUpdates, 8000); // Check setiap 8 detik
}, 3000);

// Stop saat page unload
window.addEventListener('beforeunload', () => {
    if (checkInterval) clearInterval(checkInterval);
});
</script>
@endsection
