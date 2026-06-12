{{--
    =====================================================================
    PANDUAN NOTIFIKASI DISDUKCAPIL — DESAIN BARU (TOP-END GRADIENT)
    =====================================================================
    Semua notifikasi terpusat di:
        public/js/sweetalert-disdukcapil.js

    Sudah di-load otomatis di seluruh layouts:
        - resources/views/layouts/admin.blade.php
        - resources/views/layouts/user.blade.php
        - resources/views/layouts/keagamaan.blade.php
        - resources/views/layouts/app.blade.php

    Posisi toast  : top-end
    Animasi       : slide-in dari kanan + timer progress bar
    Pause         : otomatis saat mouseenter, lanjut saat mouseleave
    Tema warna    : gradient (success/error/warning/info) — putih utk modal

    =====================================================================
    DAFTAR FUNGSI (langsung dipanggil sebagai window global)
    =====================================================================
--}}

{{-- 1. SIMPAN / UPDATE DATA --}}
<button onclick="notifSimpanBerhasil('#REG-2024-001234')">Toast Simpan Berhasil</button>
<button onclick="notifSimpanGagal('NIK sudah terdaftar dalam sistem')">Toast Simpan Gagal</button>

{{-- 2. VALIDASI FORM --}}
<button onclick="notifValidasiGagal(['NIK wajib diisi (16 digit)', 'Format tanggal tidak valid', 'KTP scan wajib diunggah'])">
    Validasi Gagal
</button>
<button onclick="notifFormBelumLengkap()">Form Belum Lengkap</button>

{{-- 3. UPLOAD DOKUMEN --}}
<button onclick="
    notifUploadProses('KTP_Scan.pdf',
        function(){ return fetch('/api/upload', { method: 'POST', body: new FormData() }); },
        function(){ console.log('Coba upload lagi'); }
    )
">Upload Dokumen</button>

{{-- 4. PROSES PENGAJUAN --}}
<button onclick="
    notifPengajuanProses('Akta Kelahiran',
        function(noReg){ window.location='/lacak-berkas?no=' + encodeURIComponent(noReg); },
        function(){ document.getElementById('formAjuan')?.reset(); }
    )
">Proses Pengajuan</button>
<button onclick="notifPengajuanDitolak('Foto KTP tidak terbaca dengan jelas.')">Pengajuan Ditolak</button>

{{-- 5. HAPUS DATA --}}
<button onclick="
    notifKonfirmasiHapus('Budi Santoso', function(){
        fetch('/admin/penduduk/1', { method: 'DELETE' })
            .then(function(){ notifSimpanBerhasil('Data terhapus'); });
    })
">Hapus Data</button>

{{-- 6. ANTRIAN & PENCARIAN --}}
<button onclick="notifNomorAntrian('A-015', 'KTP Elektronik', '± 15 menit')">Nomor Antrian</button>
<button onclick="notifCariBerhasil(5, 'Sitorus')">Pencarian Berhasil</button>
<button onclick="notifCariKosong('Sitoruss')">Pencarian Kosong</button>

{{-- 7. KONFIRMASI AKSI UMUM --}}
<button onclick="
    notifKonfirmasi('Setujui pengajuan ini?',
        function(){ /* on setuju */ },
        function(){ /* on batal  */ }
    )
">Konfirmasi Umum</button>
<button onclick="notifDisetujui('Budi Santoso')">Disetujui (APPROVED)</button>

{{--
    =====================================================================
    CONTOH INTEGRASI DENGAN FETCH/AJAX KE CONTROLLER LARAVEL
    =====================================================================
    @push('scripts')
    <script>
    async function submitForm(e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        try {
            const r = await fetch('{{ route("admin.penduduk.store") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: fd
            });
            const data = await r.json();
            if (data.status === 'validasi') return notifValidasiGagal(data.errors);
            if (data.status === 'success')  return notifSimpanBerhasil(data.noReg);
            notifSimpanGagal(data.message || 'Gagal menyimpan data');
        } catch (err) {
            notifSimpanGagal('Koneksi gagal. Periksa jaringan Anda.');
        }
    }
    </script>
    @endpush

    =====================================================================
    KOMPATIBILITAS — fungsi lama tetap bekerja & mengikuti desain baru:
    =====================================================================
       SwalHelper.success('...')     → toast gradient hijau
       SwalHelper.error('...')       → toast gradient merah
       SwalHelper.warning('...')     → toast gradient kuning
       SwalHelper.info('...')        → toast gradient biru
       notifToast('success','...','pesan',4000)
       showToast('success','pesan')
       Notifikasi.success('pesan'), Notifikasi.error('pesan'), dst.
       DToast.show('success','pesan')
       notifSuksesRegistrasi('#REG-...')   → alias notifSimpanBerhasil
       notifError('pesan')                 → alias notifSimpanGagal
       notifValidasiError([...])           → alias notifValidasiGagal
       notifCariDitemukan(n,'kw')          → alias notifCariBerhasil
       notifCariTidakDitemukan('kw')       → alias notifCariKosong
       notifKonfirmasiAksi(msg, yes, no)   → alias notifKonfirmasi
       notifUploadFile(nm, ok, err)        → alias notifUploadProses
       notifHapusBerhasil('Nama')          → toast sukses hapus
       showLoading('...')                  → modal loading

    =====================================================================
    WARNA TEMA
    =====================================================================
       success → linear-gradient(135deg, #22c55e, #16a34a)
       error   → linear-gradient(135deg, #ef4444, #dc2626)
       warning → linear-gradient(135deg, #f59e0b, #d97706)
       info    → linear-gradient(135deg, #3b82f6, #2563eb)
       modal   → #ffffff (teks #1f2937)
--}}
