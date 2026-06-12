/**
 * SweetAlert2 Final Fix — Disdukcapil Toba
 * Mencegah tombol berlebihan & loading modal yang stuck
 */
(function() {
    'use strict';

    if (typeof Swal === 'undefined') {
        console.warn('[SwalFix] SweetAlert2 belum dimuat.');
        return;
    }

    var _originalFire = Swal.fire.bind(Swal);

    Swal.fire = function(params) {
        if (!params || typeof params !== 'object') {
            return _originalFire(params);
        }

        var title = (params.title || '').toLowerCase();
        var isLoadingModal = [
            'memproses', 'memuat', 'loading', 'mohon tunggu',
            'harap tunggu', 'menyimpan', 'mengirim', 'menghapus',
            'sedang memproses', 'sedang mengakhiri'
        ].some(function(keyword) { return title.indexOf(keyword) !== -1; });

        // Paksa: modal loading TIDAK BOLEH punya tombol
        if (isLoadingModal || (params.showConfirmButton === false && !params.showCancelButton && !params.showDenyButton && params.didOpen)) {
            params.showConfirmButton = false;
            params.showCancelButton = false;
            params.showDenyButton = false;
        }

        // Paksa: hapus deny button kecuali benar-benar dibutuhkan
        if (params.showDenyButton === true && !params._allowDeny) {
            params.showDenyButton = false;
            if (params.denyButtonText && !params.cancelButtonText) {
                params.cancelButtonText = params.denyButtonText;
                params.showCancelButton = true;
            }
            delete params.denyButtonText;
        }

        // Pastikan konfirmasi 2 tombol tidak punya deny (FIX BUG "No" BUTTON)
        if (params.showCancelButton === true && params.showConfirmButton === true && !params._allowDeny) {
            params.showDenyButton = false;
            if (params.cancelButtonText && /No\b/i.test(params.cancelButtonText)) {
                params.cancelButtonText = 'Batal';
            }
        }

        try {
            var result = _originalFire(params);
            if (!result || typeof result.then !== 'function') {
                console.warn('[SwalFix] fire() mengembalikan non-Promise.');
                return Promise.resolve({ isConfirmed: false, isDismissed: true });
            }
            return result;
        } catch (error) {
            console.error('[SwalFix] Error pada Swal.fire():', error);
            return Promise.resolve({ isConfirmed: false, isDismissed: true });
        }
    };

    // Auto-close loading modal stuck > 30 detik
    var _loadingTimer = null;
    var _originalShowLoading = Swal.showLoading.bind(Swal);
    Swal.showLoading = function() {
        if (_loadingTimer) clearTimeout(_loadingTimer);
        _loadingTimer = setTimeout(function() {
            if (Swal.isVisible()) {
                console.warn('[SwalFix] Auto-close: loading modal stuck 30 detik.');
                Swal.close();
            }
        }, 30000);
        return _originalShowLoading();
    };

    var _originalClose = Swal.close.bind(Swal);
    Swal.close = function() {
        if (_loadingTimer) clearTimeout(_loadingTimer);
        if (!Swal.isVisible()) return;
        try {
            _originalClose();
        } catch (e) {
            document.querySelectorAll('.swal2-container').forEach(function(el) { el.remove(); });
        }
    };

    console.log('[SwalFix] Loaded — Disdukcapil Toba v1.0');
})();
