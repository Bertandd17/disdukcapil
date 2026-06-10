/**
 * SweetAlert Helper — Stub global API
 *
 * Wrapper yang menyediakan API high-level di atas SweetAlert2 + swal-final-fix
 * agar konsistensi UX (loading, success, error, confirm) di seluruh project.
 *
 * Digunakan di blade: auth/register, auth/login, dan view lain yang butuh
 * konfirmasi / loading / notifikasi seragam.
 */
(function (global) {
    'use strict';

    if (typeof Swal === 'undefined') {
        console.warn('[SweetAlertHelper] SweetAlert2 (Swal) belum dimuat. Helper nonaktif.');
        return;
    }

    const SwalHelper = {
        loading: function (title, text) {
            return Swal.fire({
                title: title || 'Memproses...',
                text: text || 'Mohon tunggu sebentar.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });
        },

        success: function (message, title) {
            return Swal.fire({
                icon: 'success',
                title: title || 'Berhasil',
                text: message || '',
                confirmButtonText: 'OK',
                confirmButtonColor: '#10b981'
            });
        },

        error: function (message, title) {
            return Swal.fire({
                icon: 'error',
                title: title || 'Terjadi Kesalahan',
                text: message || 'Silakan coba lagi atau hubungi administrator.',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#ef4444'
            });
        },

        warning: function (message, title) {
            return Swal.fire({
                icon: 'warning',
                title: title || 'Peringatan',
                text: message || '',
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#f59e0b'
            });
        },

        confirm: function (title, text, onConfirm, onCancel) {
            return Swal.fire({
                icon: 'question',
                title: title || 'Konfirmasi',
                text: text || 'Apakah Anda yakin?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed && typeof onConfirm === 'function') {
                    onConfirm();
                } else if (result.dismiss === Swal.DismissReason.cancel && typeof onCancel === 'function') {
                    onCancel();
                }
                return result;
            });
        }
    };

    global.SwalHelper = SwalHelper;
})(typeof window !== 'undefined' ? window : this);
