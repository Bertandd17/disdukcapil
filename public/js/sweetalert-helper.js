/**
 * =====================================================
 * SWEETALERT HELPER - SISTEM NOTIFIKASI UTAMA
 * =====================================================
 * Disdukcapil Kabupaten Toba
 * 
 * File ini adalah definisi utama SwalHelper.
 * Semua sistem notifikasi delegation ke file ini.
 * 
 * @author Disdukcapil Toba
 * @version 2.0.0
 * @requires SweetAlert2 v11.x
 */

// === SELALU CEK APAKAH SwalHelper SUDAH ADA ===
// Jika sudah ada, extend saja (jangan replace)

(function(window) {
    'use strict';

    // =====================================================
    // KONFIGURASI DEFAULT
    // =====================================================

    const SwalConfig = {
        confirmButtonColor: 'var(--success-green)',
        cancelButtonColor: 'var(--neutral-600)',
        backdrop: true,
        customClass: {
            confirmButton: 'bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-3 rounded-xl transition-all duration-200',
            cancelButton: 'bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-6 py-3 rounded-xl transition-all duration-200',
            popup: 'rounded-2xl shadow-2xl',
            title: 'text-xl font-bold text-gray-800',
            htmlContainer: 'text-gray-600',
            actions: 'flex gap-3',
        }
    };

    // SweetAlert2 global config untuk toast - tanpa backdrop & blur
    Swal.mixin({
        toast: true,
        backdrop: false,
        showClass: {
            backdrop: ''
        },
        hideClass: {
            backdrop: ''
        }
    });

    // =====================================================
    // TOAST NOTIFICATIONS
    // =====================================================
    // Hanya 2 tipe: SUCCESS dan ERROR.
    // Error SELALU menampilkan "Masalah" + "Cara memperbaiki".
    // Solusi otomatis disusun dari pola pesan error (lihat solusiError()).
    // =====================================================

    function escapeHtmlSafe(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /**
     * Bangun solusi berdasarkan pola pesan error (masalah).
     * Dipakai ketika caller tidak menyertakan solusi eksplisit.
     */
    function solusiError(masalah) {
        var text = String(masalah || '').toLowerCase().trim();
        if (!text) return 'Periksa data atau aksi yang sedang dilakukan, lalu coba lagi.';

        if (/(wajib\s+diisi|tidak\s+boleh\s+kosong|isian\s+yang\s+kosong|field[^.]{0,40}wajib|harus\s+dipilih|harap\s+lengkapi|mohon\s+lengkapi|lengkapi\s+(semua|seluruh)\s+data|form\s+belum\s+lengkap|\brequired\b)/i.test(text)) {
            return 'Lengkapi kolom yang bertanda wajib, lalu lanjutkan kembali.';
        }
        if (/(pdf|format\s+file|berformat)/i.test(text)) {
            return 'Pilih ulang file dengan format yang sesuai (PDF, JPG, atau PNG).';
        }
        if (/(ukuran\s+file|2mb|5mb|maksimal\s+\d+\s*(mb|kilobyte|kb))/i.test(text)) {
            return 'Kompres file atau pilih file dengan ukuran di bawah batas maksimal.';
        }
        if (/(nomor\s+antrian|antrian\s+anda)/i.test(text)) {
            return 'Periksa kembali nomor antrian dan pastikan layanan yang dipilih sesuai.';
        }
        if (/(nik|nomor\s+kk|kartu\s+keluarga|16\s+digit)/i.test(text)) {
            return 'Masukkan angka yang benar sesuai dokumen kependudukan.';
        }
        if (/(koneksi|jaringan|network|fetch|http|timeout|tidak\s+dapat\s+terhubung|server\s+error|5\d\d)/i.test(text)) {
            return 'Periksa koneksi internet, lalu ulangi beberapa saat lagi.';
        }
        if (/(csrf|kadaluarsa|kedaluwarsa|419|session\s+habis|token\s+tidak\s+valid)/i.test(text)) {
            return 'Muat ulang halaman, lalu kirim formulir kembali.';
        }
        if (/(username|password|email|login|masuk|kredensial|akun)/i.test(text)) {
            return 'Pastikan username dan password benar, lalu coba lagi.';
        }
        if (/(username\s+sudah|email\s+sudah|sudah\s+terdaftar|already\s+taken|sudah\s+ada|terpakai)/i.test(text)) {
            return 'Gunakan username atau email lain yang belum terdaftar.';
        }
        if (/(jawaban\s+keamanan|security\s+answer)/i.test(text)) {
            return 'Pastikan jawaban sesuai dengan yang Anda daftarkan (huruf besar/kecil tidak berpengaruh).';
        }
        if (/(kesempatan\s+terbisa|percobaan|attempt)/i.test(text)) {
            return 'Periksa kembali jawaban Anda dengan teliti sebelum mencoba lagi.';
        }
        if (/(tidak\s+terbaca|ocr|gambar|kualitas|blur|kurang\s+jelas)/i.test(text)) {
            return 'Unggah foto dengan pencahayaan cukup dan pastikan seluruh teks pada dokumen terbaca jelas.';
        }
        if (/(captcha|verifikasi\s+bot)/i.test(text)) {
            return 'Selesaikan verifikasi captcha dengan benar, lalu coba lagi.';
        }
        if (/(hak\s+akses|forbidden|403|tidak\s+diizinkan|tidak\s+punya\s+akses)/i.test(text)) {
            return 'Hubungi administrator untuk mendapatkan akses yang sesuai.';
        }
        if (/(tidak\s+ditemukan|not\s+found|404|tidak\s+tersedia)/i.test(text)) {
            return 'Data yang Anda cari tidak ditemukan. Periksa kembali kata kunci pencarian Anda.';
        }
        if (/(registrasi\s+hanya\s+dapat\s+dilakukan\s+sekali|akun\s+admin\s+sudah\s+ada|registrasi\s+ditutup)/i.test(text)) {
            return 'Akun admin sudah ada. Silakan gunakan halaman login untuk masuk.';
        }
        return 'Periksa data atau aksi yang sedang dilakukan, lalu coba lagi.';
    }

    function buildProblemSolutionHtml(masalah, solusi) {
        var p = escapeHtmlSafe(masalah || 'Terjadi kesalahan saat memproses permintaan.');
        var s = escapeHtmlSafe(solusi || solusiError(masalah));
        return '<div class="swal-dd-error-detail">' +
            '<div class="swal-dd-error-block"><span class="swal-dd-error-label">Masalah</span><span class="swal-dd-error-text">' + p + '</span></div>' +
            '<div class="swal-dd-error-block"><span class="swal-dd-error-label">Cara memperbaiki</span><span class="swal-dd-error-text">' + s + '</span></div>' +
            '</div>';
    }

    function toastSuccess(message, title, duration) {
        var cfg = {
            type: 'success',
            icon: 'success',
            timer: 5000
        };

        if (typeof message === 'string' && arguments.length >= 2 && title) {
            cfg.title = String(title);
            cfg.html = String(message);
        } else {
            cfg.title = String(message || 'Berhasil');
        }

        if (typeof window.fireToast === 'function') {
            return fireToast(cfg);
        }

        return Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            backdrop: false,
            icon: 'success',
            iconColor: 'var(--success-green)',
            title: cfg.title,
            html: cfg.html || undefined,
            customClass: {
                popup: 'swal2-toast swal-dd-toast swal-dd-success'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    }

    function stripHtmlToText(value) {
        if (value == null) return '';
        var div = document.createElement('div');
        div.innerHTML = String(value);
        return (div.textContent || div.innerText || String(value)).replace(/\s+/g, ' ').trim();
    }

    /**
     * Error toast — selalu delegasi ke fireToast() dari sweetalert-disdukcapil.js
     * dengan format { problem, solution } (Masalah + Cara memperbaiki).
     */
    function toastError(masalah, solusi, duration) {
        var problemText = stripHtmlToText(masalah) || 'Terjadi kesalahan saat memproses permintaan.';
        var solutionText = stripHtmlToText(solusi) || solusiError(problemText);
        var timerMs = (typeof duration === 'number' && duration > 0) ? duration : 5000;

        if (typeof window.fireToast === 'function') {
            return fireToast({
                type: 'error',
                icon: 'error',
                title: 'Terjadi kesalahan',
                problem: problemText,
                solution: solutionText,
                timer: timerMs
            });
        }

        return Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: timerMs,
            timerProgressBar: true,
            backdrop: false,
            icon: 'error',
            iconColor: 'var(--danger-red)',
            title: 'Terjadi kesalahan',
            html: buildProblemSolutionHtml(problemText, solutionText),
            customClass: {
                popup: 'swal2-toast swal-dd-toast swal-dd-error'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    }

    // =====================================================
    // MODAL DIALOGS
    // =====================================================

    function modalSuccess(title, message, callback = null) {
        Swal.fire({
            icon: 'success',
            title: title,
            html: message,
            confirmButtonText: '<i class="fas fa-check mr-2"></i>OK',
            confirmButtonColor: 'var(--success-green)',
            showCancelButton: false,
            showDenyButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            zIndex: 9999
        }).then((result) => {
            if (result.isConfirmed && callback) callback();
        });
    }

    function modalError(title, message, callback = null) {
        Swal.fire({
            icon: 'error',
            title: title,
            html: message,
            confirmButtonText: '<i class="fas fa-times mr-2"></i>Tutup',
            confirmButtonColor: 'var(--danger-red)',
            showCancelButton: false,
            showDenyButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            zIndex: 9999
        }).then((result) => {
            if (result.isConfirmed && callback) callback();
        });
    }

    // =====================================================
    // CONFIRM DIALOGS
    // =====================================================

    function confirmDialog(title, text, callback) {
        Swal.fire({
            title: title,
            html: `<p>${text}</p>`,
            showCancelButton: true,
            showDenyButton: false,
            confirmButtonText: 'Konfirmasi',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            confirmButtonColor: 'var(--success-green)',
            cancelButtonColor: 'var(--neutral-600)',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed && callback) callback();
        });
    }

    function deleteConfirm(title, text, callback) {
        Swal.fire({
            title: title,
            html: `<p>${text || 'Data yang dihapus tidak dapat dikembalikan. Apakah Anda yakin ingin melanjutkan?'}</p>`,
            icon: false,
            showCancelButton: true,
            showDenyButton: false,
            confirmButtonText: 'Konfirmasi',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            confirmButtonColor: 'var(--danger-red)',
            cancelButtonColor: 'var(--neutral-600)',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed && callback) callback();
        });
    }

    function saveConfirm(title, text, callback) {
        Swal.fire({
            title: title,
            html: `<p>${text}</p>`,
            showCancelButton: true,
            showDenyButton: false,
            confirmButtonText: 'Konfirmasi',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            confirmButtonColor: 'var(--primary-blue-main)',
            cancelButtonColor: 'var(--neutral-600)',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed && callback) callback();
        });
    }

    // =====================================================
    // CUSTOM CONFIRM DENGAN ICON
    // =====================================================

    function customConfirm(options = {}) {
        const defaults = {
            title: 'Konfirmasi',
            message: 'Apakah Anda yakin?',
            subMessage: '',
            iconClass: 'fas fa-question-circle',
            iconColor: 'var(--success-green)',
            confirmText: 'Ya, Lanjutkan',
            confirmColor: 'var(--success-green)',
            cancelText: 'Batal',
            cancelColor: 'var(--neutral-600)',
            onConfirm: null,
            onCancel: null,
            loadingTitle: 'Memproses',
            loadingMessage: 'Mohon tunggu...',
            showLoadingAfterConfirm: true,
        };

        const config = Object.assign({}, defaults, options);

        let htmlContent = `
            <div class="text-center">
                <p class="text-gray-600 text-sm mb-2">${config.message}</p>
        `;

        if (config.subMessage) {
            htmlContent += `<p class="text-gray-500 text-sm">${config.subMessage}</p>`;
        }

        htmlContent += '</div>';

        Swal.fire({
            title: config.title,
            html: htmlContent,
            icon: false,
            showCancelButton: true,
            showDenyButton: false,
            confirmButtonColor: config.confirmColor,
            cancelButtonColor: config.cancelColor,
            confirmButtonText: 'Konfirmasi',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-lg px-6 py-3',
                cancelButton: 'rounded-lg px-6 py-3'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (config.showLoadingAfterConfirm) {
                    Swal.fire({
                        title: config.loadingTitle,
                        html: `
                            <div class="text-center">
                                <i class="fas fa-circle-notch fa-spin text-4xl text-green-500"></i>
                                <p class="mt-4 text-gray-600">${config.loadingMessage}</p>
                            </div>
                        `,
                        showConfirmButton: false,
                        showCancelButton: false,
                        showDenyButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    });
                }
                if (config.onConfirm && typeof config.onConfirm === 'function') {
                    config.onConfirm();
                }
            } else {
                if (config.onCancel && typeof config.onCancel === 'function') {
                    config.onCancel();
                }
            }
        });
    }

    // Helper khusus dengan auto-logout integration
    function confirmStart(title, message, subMessage, onConfirm, onCancel) {
        if (window.pauseAutoLogoutReset) window.pauseAutoLogoutReset();

        customConfirm({
            title: title,
            message: message,
            subMessage: subMessage,
            iconClass: 'fas fa-play-circle',
            iconColor: 'var(--success-green)',
            confirmText: 'Ya, Mulai',
            confirmColor: 'var(--success-green)',
            cancelText: 'Batal',
            cancelColor: 'var(--neutral-600)',
            onConfirm: onConfirm,
            onCancel: () => {
                if (window.resumeAutoLogoutReset && onCancel) onCancel();
                if (window.resumeAutoLogoutReset) window.resumeAutoLogoutReset();
            }
        });
    }

    function confirmDelete(title, message, subMessage, onConfirm, onCancel) {
        if (window.pauseAutoLogoutReset) window.pauseAutoLogoutReset();

        customConfirm({
            title: title,
            message: message,
            subMessage: subMessage,
            iconClass: 'fas fa-trash',
            iconColor: 'var(--danger-red)',
            confirmText: 'Ya, Hapus',
            confirmColor: 'var(--danger-red)',
            cancelText: 'Batal',
            cancelColor: 'var(--neutral-600)',
            onConfirm: onConfirm,
            onCancel: () => {
                if (window.resumeAutoLogoutReset && onCancel) onCancel();
                if (window.resumeAutoLogoutReset) window.resumeAutoLogoutReset();
            }
        });
    }

    function confirmSave(title, message, subMessage, onConfirm, onCancel) {
        if (window.pauseAutoLogoutReset) window.pauseAutoLogoutReset();

        customConfirm({
            title: title,
            message: message,
            subMessage: subMessage,
            iconClass: 'fas fa-save',
            iconColor: 'var(--success-green)',
            confirmText: 'Ya, Simpan',
            confirmColor: 'var(--success-green)',
            cancelText: 'Batal',
            cancelColor: 'var(--neutral-600)',
            onConfirm: onConfirm,
            onCancel: () => {
                if (window.resumeAutoLogoutReset && onCancel) onCancel();
                if (window.resumeAutoLogoutReset) window.resumeAutoLogoutReset();
            }
        });
    }

    function confirmUpdate(title, message, subMessage, onConfirm, onCancel) {
        if (window.pauseAutoLogoutReset) window.pauseAutoLogoutReset();

        customConfirm({
            title: title,
            message: message,
            subMessage: subMessage,
            iconClass: 'fas fa-sync',
            iconColor: 'var(--primary-blue-main)',
            confirmText: 'Ya, Update',
            confirmColor: 'var(--primary-blue-main)',
            cancelText: 'Batal',
            cancelColor: 'var(--neutral-600)',
            onConfirm: onConfirm,
            onCancel: () => {
                if (window.resumeAutoLogoutReset && onCancel) onCancel();
                if (window.resumeAutoLogoutReset) window.resumeAutoLogoutReset();
            }
        });
    }

    function confirmLogout(title, message, subMessage, onConfirm, onCancel) {
        if (window.pauseAutoLogoutReset) window.pauseAutoLogoutReset();

        customConfirm({
            title: title,
            message: message,
            subMessage: subMessage,
            iconClass: 'fas fa-sign-out-alt',
            iconColor: 'var(--danger-red)',
            confirmText: 'Ya, Keluar',
            confirmColor: 'var(--danger-red)',
            cancelText: 'Batal',
            cancelColor: 'var(--neutral-600)',
            onConfirm: onConfirm,
            onCancel: () => {
                if (window.resumeAutoLogoutReset && onCancel) onCancel();
                if (window.resumeAutoLogoutReset) window.resumeAutoLogoutReset();
            }
        });
    }

    // =====================================================
    // NOTIFIKASI SPESIAL
    // =====================================================

    function notifySuccess(title, message, subMessage, callback) {
        customConfirm({
            title: title,
            message: message,
            subMessage: subMessage,
            iconClass: 'fas fa-check-circle',
            iconColor: 'var(--success-green)',
            confirmText: 'OK',
            confirmColor: 'var(--success-green)',
            cancelText: 'Tutup',
            cancelColor: 'var(--neutral-600)',
            showLoadingAfterConfirm: false,
            onConfirm: callback,
            onCancel: callback
        });
    }

    function notifyError(title, message, subMessage, callback) {
        customConfirm({
            title: title,
            message: message,
            subMessage: subMessage,
            iconClass: 'fas fa-times-circle',
            iconColor: 'var(--danger-red)',
            confirmText: 'OK',
            confirmColor: 'var(--danger-red)',
            cancelText: 'Tutup',
            cancelColor: 'var(--neutral-600)',
            showLoadingAfterConfirm: false,
            onConfirm: callback,
            onCancel: callback
        });
    }

    // =====================================================
    // LOADING
    // =====================================================

    function showLoading(message = 'Memproses...') {
        return Swal.fire({
            title: message,
            html: `
                <div class="text-center">
                    <i class="fas fa-circle-notch fa-spin text-4xl text-green-500"></i>
                    <p class="mt-4 text-gray-600">Mohon tunggu sebentar...</p>
                </div>
            `,
            showConfirmButton: false,
            showCancelButton: false,
            showDenyButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        });
    }

    function closeLoading() {
        Swal.close();
    }

    // =====================================================
    // CLOSE
    // =====================================================

    function closeSwal() {
        Swal.close();
    }

    // =====================================================
    // EXPORT KE SwalHelper
    // =====================================================

    // Inisialisasi atau extend SwalHelper
    if (typeof window.SwalHelper === 'undefined') {
        window.SwalHelper = {};
    }

    // Assign semua fungsi
    Object.assign(window.SwalHelper, {
        // Toast — hanya Success dan Error
        toastSuccess,
        toastError,
        success: toastSuccess,
        error: toastError,

        // Modal
        modalSuccess,
        modalError,
        successModal: modalSuccess,

        // Confirm
        confirm: confirmDialog,
        deleteConfirm,
        saveConfirm,
        customConfirm,
        confirmStart,
        confirmDelete,
        confirmSave,
        confirmUpdate,
        confirmLogout,

        // Notifikasi
        notifySuccess,
        notifyError,

        // Loading
        loading: showLoading,
        close: closeSwal
    });

    window.toastSuccess = toastSuccess;
    window.toastError = toastError;

    console.log('✓ SweetAlert Helper loaded');

})(window);
