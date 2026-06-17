(function (window) {
    'use strict';

    function escapeHtml(value) {
        if (value == null) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /** Hapus semua tombol & container actions — sama seperti register / admin logout */
    function stripSwalActionButtons() {
        try {
            document.querySelectorAll(
                '.swal2-deny, .swal2-cancel, .swal2-confirm, .swal2-styled, .swal-dd-btn, button.swal2-deny, button.swal2-cancel, button.swal2-confirm'
            ).forEach(function (el) {
                el.remove();
            });
            document.querySelectorAll(
                '.swal2-actions, .swal2-deny-container, .swal2-cancel-container, .swal2-confirm-container'
            ).forEach(function (el) {
                el.remove();
            });
        } catch (e) { /* ignore */ }
    }

    /**
     * Modal loading konsisten dengan halaman register:
     * spinner Font Awesome, tanpa tombol (DOM di-strip), tidak bisa ditutup.
     */
    function showRegisterStyleLoading(title, message, options) {
        options = options || {};
        var spinnerColor = options.spinnerColor || 'text-green-500';
        var msg = message || options.defaultMessage || 'Mohon tunggu sebentar...';

        Swal.fire({
            title: title || 'Memproses',
            html:
                '<div class="flex flex-col items-center gap-3 py-2">' +
                '<i class="fas fa-circle-notch fa-spin text-4xl ' + spinnerColor + '"></i>' +
                '<p class="text-gray-600 text-sm">' + escapeHtml(msg) + '</p>' +
                '</div>',
            showConfirmButton: false,
            showCancelButton: false,
            showDenyButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            customClass: {
                popup: 'swal-register-loading swal-dd-modal',
                htmlContainer: 'swal2-html-container'
            },
            didOpen: function () {
                stripSwalActionButtons();
                window.setTimeout(stripSwalActionButtons, 0);
                window.setTimeout(stripSwalActionButtons, 50);
                window.setTimeout(stripSwalActionButtons, 150);
            }
        });
    }

    window.showRegisterStyleLoading = showRegisterStyleLoading;
    window.stripSwalActionButtons = stripSwalActionButtons;
})(window);
