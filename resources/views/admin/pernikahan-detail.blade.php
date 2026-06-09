@extends('layouts.admin')

@section('title', 'Detail Pernikahan')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Page Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.pernikahan.index') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-800">Detail Permohonan</h1>
                <p class="text-gray-500 text-sm">Nomor Antrian: <span class="font-mono font-semibold text-blue-600">{{ $pernikahan->nomor_antrian }}</span></p>
            </div>
            <div class="flex items-center gap-2">
                @switch($pernikahan->status)
                    @case(\App\Models\LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL)
                        <button onclick="approveTanggal()" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition-colors">
                            <i class="fas fa-check mr-2"></i>Setujui Tanggal
                        </button>
                        <button onclick="rejectTanggal()" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-medium hover:bg-red-700 transition-colors">
                            <i class="fas fa-times mr-2"></i>Tolak Tanggal
                        </button>
                        @break
                    @case(\App\Models\LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI)
                        <button onclick="verifikasiDokumen()" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition-colors">
                            <i class="fas fa-check-double mr-2"></i>Verifikasi Dokumen
                        </button>
                        @break
                    @case(\App\Models\LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI)
                        <button onclick="uploadBerkas()" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition-colors">
                            <i class="fas fa-upload mr-2"></i>Upload Berkas
                        </button>
                        @break
                @endswitch
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="p-6">
        {{-- Konten detail pernikahan --}}
    </div>
</div>

{{-- ======= SWEETALERT2 LOADING WITHOUT BUTTON ======= --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.SwalHelper = {
    loading: function(title = 'Memproses...', html = 'Mohon tunggu sebentar...') {
        return Swal.fire({
            title: title,
            html: html,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
    },
    close: function() {
        Swal.close();
    },
    success: function(message = 'Berhasil!') {
        Swal.fire({
            icon: 'success',
            title: message,
            showConfirmButton: false,
            timer: 2000,
            toast: true,
            position: 'top-end'
        });
    },
    error: function(message = 'Gagal!') {
        Swal.fire({
            icon: 'error',
            title: message,
            showConfirmButton: true,
            confirmButtonColor: '#dc2626'
        });
    },
    confirm: function(message, onYes, onNo, title = 'Konfirmasi') {
        return Swal.fire({
            title: title,
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0052CC',
            cancelButtonColor: '#6b7280',
            reverseButtons: true
        }).then(r => {
            if(r.isConfirmed && onYes) onYes();
            else if(r.isDismissed && onNo) onNo();
        });
    }
};

// Contoh penggunaan loading tanpa tombol untuk semua async action
async function approveTanggal() {
    SwalHelper.loading('Menyetujui tanggal...');
    try {
        const res = await fetch('{{ route('admin.pernikahan.approve-tanggal', $pernikahan->pernikahan_id) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json();
        SwalHelper.close();
        if(data.success) SwalHelper.success(data.message || 'Tanggal berhasil disetujui');
        else SwalHelper.error(data.message || 'Gagal menyetujui tanggal');
        setTimeout(()=>window.location.reload(), 1500);
    } catch (err) {
        SwalHelper.close();
        SwalHelper.error('Terjadi kesalahan');
    }
}

async function rejectTanggal() {
    SwalHelper.loading('Menolak tanggal...');
    try {
        const res = await fetch('{{ route('admin.pernikahan.reject-tanggal', $pernikahan->pernikahan_id) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json();
        SwalHelper.close();
        if(data.success) SwalHelper.success(data.message || 'Tanggal ditolak');
        else SwalHelper.error(data.message || 'Gagal menolak tanggal');
        setTimeout(()=>window.location.reload(), 1500);
    } catch (err) {
        SwalHelper.close();
        SwalHelper.error('Terjadi kesalahan');
    }
}

async function verifikasiDokumen() {
    SwalHelper.loading('Memverifikasi dokumen...');
    try {
        const res = await fetch('{{ route('admin.pernikahan.verify-doc', $pernikahan->pernikahan_id) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json();
        SwalHelper.close();
        if(data.success) SwalHelper.success(data.message || 'Dokumen diverifikasi');
        else SwalHelper.error(data.message || 'Verifikasi gagal');
        setTimeout(()=>window.location.reload(), 1500);
    } catch(err) {
        SwalHelper.close();
        SwalHelper.error('Terjadi kesalahan');
    }
}

async function uploadBerkas() {
    SwalHelper.loading('Mengupload berkas...');
    // kode upload file sesuai implementasi asli
    // setelah selesai:
    setTimeout(()=>SwalHelper.close(), 1000); // simulasi upload
}
</script>

{{-- swal-final-fix sudah di-include oleh layout utama (admin.blade.php) --}}
@endsection