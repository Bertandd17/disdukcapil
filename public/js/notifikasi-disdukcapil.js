/**
 * =====================================================================
 * notifikasi-disdukcapil.js â€” LAPISAN KOMPATIBILITAS
 * =====================================================================
 * Seluruh implementasi toast/notifikasi sekarang berada di
 * public/js/sweetalert-disdukcapil.js (desain top-end gradient baru).
 *
 * File ini hanya menyediakan alias untuk nama-nama LAMA yang masih
 * dipakai di view, controller flash message handler, dan modul lain.
 * Semua alias mendelegasikan ke API baru.
 * =====================================================================
 */
(function (window) {
    'use strict';

    function S() { return window.SwalDisdukcapil || null; }
    function toastBy(type, message, timer) {
        if (type === 'warning' || type === 'info') type = 'error';
        if (!S()) {
            if (window.Swal) window.Swal.fire({ toast: true, position: 'top-end', icon: type, title: message, timer: 5000, showConfirmButton: false });
            return;
        }
        S().fireToast({ type: type, icon: type, title: message || '', timer: 5000 });
    }

    // --- Toast singkat lama ---
    window.showSuccess = function (m) { toastBy('success', m, 4000); };
    window.showError   = function (m) { toastBy('error',   m, 4000); };
    window.showInfo    = function (m) { toastBy('error',   m, 4000); };
    window.showWarning = function (m) { toastBy('error',   m, 4000); };

    // --- Konfirmasi & loading lama ---
    window.showConfirm = function (title, text, onConfirm) {
        if (window.notifKonfirmasi) return window.notifKonfirmasi(text || title, onConfirm);
        if (window.SwalHelper) return window.SwalHelper.confirm(title, text, onConfirm);
    };
    window.showDeleteConfirm = function (title, text, onConfirm) {
        if (window.notifKonfirmasiHapus) return window.notifKonfirmasiHapus(text || title, onConfirm);
        if (window.SwalHelper) return window.SwalHelper.deleteConfirm(title, text, onConfirm);
    };
    window.showLoading = function (message) {
        if (window.SwalHelper && typeof window.SwalHelper.loading === 'function') return window.SwalHelper.loading(message);
        if (window.Swal) window.Swal.fire({
            title: message || 'Memproses...',
            html: '<i class="fas fa-circle-notch fa-spin" style="font-size:32px;color:#0052CC"></i>',
            showConfirmButton: false,
            allowOutsideClick: false,
            customClass: { popup: 'swal-dd-modal' }
        });
    };

    // --- Alias nama LAMA â†’ fungsi BARU ---
    window.notifSuksesRegistrasi   = function (noReg, cb) { var p = window.notifSimpanBerhasil(noReg); if (cb) Promise.resolve(p).then(cb); };
    window.notifError              = function (pesan, cb) { var p = window.notifSimpanGagal(pesan);   if (cb) Promise.resolve(p).then(cb); };
    window.notifValidasiError      = function (errs, cb)  { var p = window.notifValidasiGagal(errs);  if (cb) Promise.resolve(p).then(cb); };
    window.notifCariDitemukan      = function (jml, kw)   { return window.notifCariBerhasil(jml, kw); };
    window.notifCariTidakDitemukan = function (kw)        { return window.notifCariKosong(kw); };
    window.notifHapusBerhasil      = function (nama)      { return toastBy('success', 'Data "' + (nama || '') + '" berhasil dihapus', 3500); };
    window.notifKonfirmasiAksi     = function (pesan, onYes, onNo) { return window.notifKonfirmasi(pesan, onYes, onNo); };
    window.notifLoading            = function (pesan)     { return window.showLoading(pesan); };
    window.notifUploadFile         = function (nama, onOk, onErr) { return window.notifUploadProses(nama, onOk, onErr); };
    // notifDisetujui & notifFormBelumLengkap sudah didefinisikan di sweetalert-disdukcapil.js

    // Namespace `Notifikasi` (backward-compat luas)
    window.Notifikasi = window.Notifikasi || {};
    window.Notifikasi.success       = window.showSuccess;
    window.Notifikasi.error         = window.showError;
    window.Notifikasi.info          = window.showError;
    window.Notifikasi.warning       = window.showError;
    window.Notifikasi.confirm       = function (msg, onYes, onNo) { return window.notifKonfirmasi(msg, onYes, onNo); };
    window.Notifikasi.deleteConfirm = window.showDeleteConfirm;
    window.Notifikasi.loading       = window.showLoading;
    window.Notifikasi.sukses        = window.notifSuksesRegistrasi;
    window.Notifikasi.validasi      = window.notifValidasiError;
    window.Notifikasi.cariDitemukan = window.notifCariDitemukan;
    window.Notifikasi.cariTidakDitemukan = window.notifCariTidakDitemukan;
    window.Notifikasi.hapusBerhasil = window.notifHapusBerhasil;
    window.Notifikasi.disetujui     = function (nama) { return window.notifDisetujui(nama); };

})(window);
