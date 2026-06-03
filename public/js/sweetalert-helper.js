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
            confirmButtonText: '<i class="fas fa-check mr-2"></i>Konfirmasi',
            confirmButtonColor: '#43A047',
            cancelButtonColor: '#e5e7eb',
            reverseButtons: true,
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
            confirmButtonText: '<i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi',
            confirmButtonColor: '#43A047',
            cancelButtonColor: '#e5e7eb',
            reverseButtons: true,
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
            confirmButtonText: '<i class="fas fa-info-circle mr-2"></i>Konfirmasi',
            confirmButtonColor: '#43A047',
            cancelButtonColor: '#e5e7eb',
            reverseButtons: true,
            zIndex: 9999
        }).then((result) => {
            if (result.isConfirmed && callback) callback();
        });
    }

    // =====================================================
    // CONFIRM DIALOGS
    // =====================================================

    function confirmDialog(title, text, callback) {
        konfirmasiDisdukcapil({
            judul: title,
            pesan: text || 'Apakah Anda yakin ingin melanjutkan?',
            tipe: 'konfirmasi',
            labelOk: 'Konfirmasi',
            onKonfirmasi: () => { if (callback) callback(); }
        });
    }

    function deleteConfirm(title, text, callback) {
        konfirmasiDisdukcapil({
            judul: title,
            pesan: text || 'Data yang dihapus tidak dapat dikembalikan. Apakah Anda yakin ingin melanjutkan?',
            tipe: 'hapus',
            labelOk: 'Hapus',
            onKonfirmasi: () => { if (callback) callback(); }
        });
    }

    function saveConfirm(title, text, callback) {
        konfirmasiDisdukcapil({
            judul: title,
            pesan: text || 'Pastikan data sudah benar sebelum disimpan.',
            tipe: 'konfirmasi',
            labelOk: 'Simpan',
            onKonfirmasi: () => { if (callback) callback(); }
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

        let pesan = `<p class="text-gray-600 text-sm mb-2">${config.message}</p>`;
        if (config.subMessage) {
            pesan += `<p class="text-gray-500 text-sm">${config.subMessage}</p>`;
        }

        let tipe = 'konfirmasi';
        if (/E53935|var\(--danger-red\)|fa-trash/i.test(config.confirmColor + ' ' + config.iconClass)) {
            tipe = 'hapus';
        } else if (/FB8C00|var\(--warning-orange\)/i.test(config.confirmColor + ' ' + config.iconClass)) {
            tipe = 'warning';
        }

        konfirmasiDisdukcapil({
            judul: config.title,
            pesan: pesan,
            tipe: tipe,
            labelOk: config.confirmText,
            labelBatal: config.cancelText,
            onKonfirmasi: () => {
                if (config.showLoadingAfterConfirm) {
                    Swal.fire({
                        title: config.loadingTitle,
                        html: `
                            <div class="text-center">
                                <i class="fas fa-circle-notch fa-spin text-4xl text-green-500"></i>
                                <p class="mt-4 text-gray-600">${config.loadingMessage}</p>
                            </div>
                        `,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        showDenyButton: false,
                        showCancelButton: false
                    });
                }
                if (config.onConfirm && typeof config.onConfirm === 'function') {
                    config.onConfirm();
                }
            },
            onBatal: () => {
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
    // TEMPLATE KONFIRMASI STANDAR DISDUKCAPIL
    // =====================================================
    // Selalu 2 tombol: Batal (kiri) + Konfirmasi (kanan).
    // Konteks judul & pesan menyesuaikan pemanggil.
    // =====================================================

    function konfirmasiDisdukcapil({
        judul        = 'Konfirmasi Aksi',
        pesan        = 'Apakah Anda yakin ingin melanjutkan?',
        html         = null,
        tipe         = 'konfirmasi',
        labelOk      = 'Konfirmasi',
        labelBatal   = 'Batal',
        onKonfirmasi = () => {},
        onBatal      = () => {},
        confirmButtonColor = null,
        cancelButtonColor  = null,
        allowOutsideClick  = false,
        allowEscapeKey     = true
    } = {}) {

        const config = {
            konfirmasi: {
                icon      : 'question',
                iconColor : '#43A047',
                iconBg    : '#e8f5e9',
                okColor   : '#43A047',
                okHover   : '#388E3C'
            },
            hapus: {
                icon      : 'warning',
                iconColor : '#E53935',
                iconBg    : '#ffebee',
                okColor   : '#E53935',
                okHover   : '#d32f2f'
            },
            warning: {
                icon      : 'warning',
                iconColor : '#FB8C00',
                iconBg    : '#fff3e0',
                okColor   : '#FB8C00',
                okHover   : '#ef6c00'
            }
        };

        const c = config[tipe] || config.konfirmasi;
        const bodyHtml = html !== null && html !== undefined
            ? html
            : `<span style="font-size:0.875rem;color:#6b7280;line-height:1.6">${pesan}</span>`;

        return Swal.fire({
            title              : `<span style="font-size:1.1rem;font-weight:700;color:#1f2937">${judul}</span>`,
            html               : bodyHtml,

            icon               : c.icon,
            iconColor          : c.iconColor,

            customClass        : {
                popup        : 'swal-disdukcapil-popup',
                title        : 'swal-disdukcapil-title',
                actions      : 'swal-disdukcapil-actions',
                confirmButton: 'swal-btn-konfirmasi',
                cancelButton : 'swal-btn-batal'
            },

            showConfirmButton  : true,
            confirmButtonText  : labelOk,
            confirmButtonColor : confirmButtonColor || c.okColor,

            showCancelButton   : true,
            cancelButtonText   : labelBatal,
            cancelButtonColor  : cancelButtonColor || '#e5e7eb',

            showDenyButton     : false,

            reverseButtons     : true,

            allowOutsideClick  : allowOutsideClick,
            allowEscapeKey     : allowEscapeKey,
            focusCancel        : true
        }).then((result) => {
            if (result.isConfirmed) onKonfirmasi(result);
            else                    onBatal(result);
            return result;
        });
    }

    // CSS inline untuk override SweetAlert default (disuntik sekali)
    const swalStyle = document.createElement('style');
    swalStyle.textContent = `
    .swal-disdukcapil-popup {
        border-radius : 14px !important;
        padding       : 1.5rem !important;
        font-family   : 'Plus Jakarta Sans', sans-serif !important;
        box-shadow    : 0 20px 40px rgba(0,0,0,0.12) !important;
        max-width     : 400px !important;
    }
    .swal-disdukcapil-actions {
        gap           : 0.75rem !important;
        margin-top    : 1.25rem !important;
        width         : 100% !important;
    }
    .swal-btn-konfirmasi,
    .swal-btn-batal {
        flex          : 1 !important;
        border-radius : 10px !important;
        font-weight   : 600 !important;
        font-size     : 0.875rem !important;
        padding       : 0.75rem 1.5rem !important;
        border        : none !important;
        transition    : all 0.2s ease !important;
        min-width     : 120px !important;
    }
    .swal-btn-batal {
        color         : #374151 !important;
    }
    .swal-btn-konfirmasi:hover { filter: brightness(0.9) !important; }
    .swal-btn-batal:hover      { background: #d1d5db !important; }
    `;
    if (!document.getElementById('swal-disdukcapil-style')) {
        swalStyle.id = 'swal-disdukcapil-style';
        document.head.appendChild(swalStyle);
    }

    // =====================================================
    // NOTIFIKASI SPESIAL
    // =====================================================

    function notifySuccess(title, message, subMessage, callback) {
        Swal.fire({
            icon: 'success',
            title: title,
            html: `<div class="text-center">
                <p class="text-gray-600 text-sm mb-2">${message}</p>
                ${subMessage ? `<p class="text-gray-500 text-sm">${subMessage}</p>` : ''}
            </div>`,
            confirmButtonText: 'OK',
            confirmButtonColor: 'var(--success-green)',
            allowOutsideClick: false,
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-lg px-6 py-3'
            }
        }).then((result) => {
            if (callback && typeof callback === 'function') callback(result);
        });
    }

    function notifyError(title, message, subMessage, callback) {
        Swal.fire({
            icon: 'error',
            title: title,
            html: `<div class="text-center">
                <p class="text-gray-600 text-sm mb-2">${message}</p>
                ${subMessage ? `<p class="text-gray-500 text-sm">${subMessage}</p>` : ''}
            </div>`,
            confirmButtonText: 'OK',
            confirmButtonColor: 'var(--danger-red)',
            allowOutsideClick: false,
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-lg px-6 py-3'
            }
        }).then((result) => {
            if (callback && typeof callback === 'function') callback(result);
        });
    }

    function notifyWarning(title, message, subMessage, callback) {
        Swal.fire({
            icon: 'warning',
            title: title,
            html: `<div class="text-center">
                <p class="text-gray-600 text-sm mb-2">${message}</p>
                ${subMessage ? `<p class="text-gray-500 text-sm">${subMessage}</p>` : ''}
            </div>`,
            confirmButtonText: 'OK',
            confirmButtonColor: 'var(--warning-orange)',
            allowOutsideClick: false,
            customClass: {
           popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-lg px-6 py-3'
            }
        }).then((result) => {
            if (callback && typeof callback === 'function') callback(result);
        });
    }

    // =====================================================
    // LOADING
    // =====================================================

    function showLoading(message = 'Memproses...') {
        return Swal.fire({
            title: message,
            text: 'Mohon tunggu sebentar...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            showDenyButton: false,
            showCancelButton: false,
            didOpen: () => Swal.showLoading()
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
        konfirmasiDisdukcapil,

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
