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

    function toastSuccess(message, duration = 5000) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            backdrop: false,
            customClass: {
                popup: 'swal2-toast-success'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        Toast.fire({
            icon: 'success',
            title: message,
            iconColor: 'var(--success-green)',
            zIndex: 99999
        });
    }

    function toastError(message, duration = 5000) {
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
            icon: 'error',
            title: message,
            iconColor: 'var(--danger-red)',
            zIndex: 99999
        });
    }

    function toastWarning(message, duration = 5000) {
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
            title: message,
            iconColor: 'var(--warning-orange)',
            zIndex: 99999
        });
    }

    function toastInfo(message, duration = 5000) {
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
            icon: 'info',
            title: message,
            iconColor: 'var(--info-blue)',
            zIndex: 99999
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
            zIndex: 9999
        }).then((result) => {
            if (result.isConfirmed && callback) callback();
        });
    }

    function modalWarning(title, message, callback = null) {
        Swal.fire({
            icon: 'warning',
            title: title,
            html: message,
            confirmButtonText: '<i class="fas fa-exclamation-triangle mr-2"></i>OK',
            confirmButtonColor: 'var(--warning-orange)',
            zIndex: 9999
        }).then((result) => {
            if (result.isConfirmed && callback) callback();
        });
    }

    function modalInfo(title, message, callback = null) {
        Swal.fire({
            icon: 'info',
            title: title,
            html: message,
            confirmButtonText: '<i class="fas fa-info-circle mr-2"></i>OK',
            confirmButtonColor: 'var(--info-blue)',
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
            showConfirmButton: true,
            showDenyButton: false,
            confirmButtonText: 'Konfirmasi',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            confirmButtonColor: 'var(--success-green)',
            cancelButtonColor: 'var(--neutral-600)'
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
            showConfirmButton: true,
            showDenyButton: false,
            confirmButtonText: 'Konfirmasi',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            confirmButtonColor: 'var(--danger-red)',
            cancelButtonColor: 'var(--neutral-600)'
        }).then((result) => {
            if (result.isConfirmed && callback) callback();
        });
    }

    function saveConfirm(title, text, callback) {
        Swal.fire({
            title: title,
            html: `<p>${text}</p>`,
            showCancelButton: true,
            showConfirmButton: true,
            showDenyButton: false,
            confirmButtonText: 'Konfirmasi',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            confirmButtonColor: 'var(--primary-blue-main)',
            cancelButtonColor: 'var(--neutral-600)'
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
            showConfirmButton: true,
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

    function notifyWarning(title, message, subMessage, callback) {
        customConfirm({
            title: title,
            message: message,
            subMessage: subMessage,
            iconClass: 'fas fa-exclamation-triangle',
            iconColor: 'var(--warning-orange)',
            confirmText: 'OK',
            confirmColor: 'var(--warning-orange)',
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
        // Toast
        toastSuccess,
        toastError,
        toastWarning,
        toastInfo,
        success: toastSuccess,
        error: toastError,
        warning: toastWarning,
        info: toastInfo,

        // Modal
        modalSuccess,
        modalError,
        modalWarning,
        modalInfo,
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
        notifyWarning,

        // Loading
        loading: showLoading,
        close: closeSwal
    });

    console.log('✓ SweetAlert Helper loaded');

})(window);
