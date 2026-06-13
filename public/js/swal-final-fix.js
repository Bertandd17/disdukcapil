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

    // ── Penapis tombol deny visual secara global ──────────────────────
    function stripDenyButton() {
        try {
            var deny = Swal.getDenyButton && Swal.getDenyButton();
            if (deny) {
                deny.style.setProperty('display', 'none', 'important');
                deny.style.setProperty('visibility', 'hidden', 'important');
                deny.setAttribute('aria-hidden', 'true');
                deny.setAttribute('tabindex', '-1');
            }
            document.querySelectorAll('.swal2-deny').forEach(function(el) {
                el.style.setProperty('display', 'none', 'important');
                el.style.setProperty('visibility', 'hidden', 'important');
                el.setAttribute('aria-hidden', 'true');
            });
        } catch (e) { /* ignore */ }
    }

    Swal.fire = function(params) {
        if (!params || typeof params !== 'object') {
            return _originalFire(params);
        }

        // DEFAULT GLOBAL: paksa showDenyButton = false di SEMUA modal.
        // Project ini tidak pernah butuh tombol deny ke-3.
        // Kalau ada yang benar-benar butuh, set _allowDeny: true.
        if (!params._allowDeny) {
            params.showDenyButton = false;
            delete params.denyButtonText;
        }

        var title = (params.title || '').toLowerCase();
        var isToast = params.toast === true;
        var isConfirmModal = !isToast && params.showCancelButton === true;
        var isLoadingModal = !isToast && [
            'memproses', 'memuat', 'loading', 'mohon tunggu',
            'harap tunggu', 'menyimpan', 'mengirim', 'menghapus',
            'sedang memproses', 'sedang mengakhiri', 'memeriksa',
            'mengunggah', 'sedang membuat', 'sedang mengupload'
        ].some(function(keyword) { return title.indexOf(keyword) !== -1; });

        // Modal konfirmasi 2-tombol: standarisasi Batal + Konfirmasi
        if (isConfirmModal && !params._allowDeny) {
            params.showDenyButton = false;
            delete params.denyButtonText;
            if (params.allowOutsideClick !== true) params.allowOutsideClick = false;
            if (params.allowEscapeKey !== true) params.allowEscapeKey = false;
            if (params.reverseButtons === undefined) params.reverseButtons = true;
        }

        // Paksa: modal loading TIDAK BOLEH punya tombol
        if (isLoadingModal || (params.showConfirmButton === false && !params.showCancelButton && !params.showDenyButton && params.didOpen)) {
            params.showConfirmButton = false;
            params.showCancelButton = false;
            params.showDenyButton = false;
            if (params.allowOutsideClick !== true) params.allowOutsideClick = false;
            if (params.allowEscapeKey !== true) params.allowEscapeKey = false;
        }

        // Untuk SEMUA modal (apapun tipenya), pastikan deny button di-strip dari DOM.
        // SweetAlert2 v11 tetap me-render .swal2-deny di DOM walau showDenyButton=false.
        if (!params._allowDeny) {
            var origDidOpen = params.didOpen || function() {};
            params.didOpen = function(toast) {
                origDidOpen(toast);
                setTimeout(stripDenyButton, 0);
                setTimeout(stripDenyButton, 50);
            };
            var origWillOpen = params.willOpen || function() {};
            params.willOpen = function(toast) {
                origWillOpen(toast);
                setTimeout(stripDenyButton, 50);
                setTimeout(stripDenyButton, 150);
            };
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

    // ── MutationObserver untuk menangkap deny button yang ditambahkan setelahnya ──
    if (typeof MutationObserver !== 'undefined') {
        var _denyObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(m) {
                m.addedNodes.forEach(function(node) {
                    if (node && node.nodeType === 1) {
                        if (node.classList && node.classList.contains('swal2-deny')) {
                            node.style.setProperty('display', 'none', 'important');
                            node.style.setProperty('visibility', 'hidden', 'important');
                        }
                        if (node.querySelectorAll) {
                            node.querySelectorAll('.swal2-deny').forEach(function(el) {
                                el.style.setProperty('display', 'none', 'important');
                                el.style.setProperty('visibility', 'hidden', 'important');
                            });
                        }
                    }
                });
            });
        });
        _denyObserver.observe(document.documentElement || document.body, {
            childList: true,
            subtree: true
        });
    }

    console.log('[SwalFix] Loaded — Disdukcapil Toba v1.0');
})();
