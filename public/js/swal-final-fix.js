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
            params.showDenyButton = false; // Matikan deny button
            delete params.denyButtonText;  // Hapus teks deny button

            // Opsi: Jika ingin tombol Batal tetap tampil, biarkan saja.
            // Jika ingin hanya ada 1 tombol (Konfirmasi), set showCancelButton = false.
            // Untuk kasus ini, kita biarkan Batal tetap ada tapi pastikan No hilang.

            // STRIP: sembunyikan deny button dari DOM agar tidak tampil
            var origDidOpen = params.didOpen || function() {};
            params.didOpen = function(toast) {
                origDidOpen(toast);
                setTimeout(stripDenyButton, 0);
            };
            // Juga pasang di willOpen untuk kasus modal tanpa didOpen
            var origWillOpen = params.willOpen || function() {};
            params.willOpen = function(toast) {
                origWillOpen(toast);
                setTimeout(stripDenyButton, 50);
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
