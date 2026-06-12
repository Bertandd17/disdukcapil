/**
 * SweetAlert2 Final Fix — Disdukcapil Toba
 * VERSI 2.0 — Fix agresif untuk 3 bug kritis:
 *   BUG 1: Modal konfirmasi punya 3 tombol (Batal + No + Ya)
 *   BUG 2: Modal loading punya tombol OK/Cancel/No
 *   BUG 3: Konfirmasi pengajuan punya 3 tombol (Batalkan + No + Ya, Kirim)
 *
 * ROOT CAUSE: SweetAlert2 v11 tetap me-render <button class="swal2-deny">No
 * di DOM walaupun showDenyButton:false. Library quirk. Workaround:
 *   (a) JS: paksa _removeDenyButton() setelah Swal.fire()
 *   (b) JS: MutationObserver menyapu deny button yang lolos
 *   (c) CSS: .swal2-deny { display:none !important }
 * Ketiganya dipasang di sini untuk double/triple safety.
 */
(function() {
    'use strict';

    if (typeof Swal === 'undefined') {
        console.warn('[SwalFix] SweetAlert2 belum dimuat.');
        return;
    }

    var _originalFire = Swal.fire.bind(Swal);

    /**
     * Deteksi apakah Swal.fire() ini untuk modal loading.
     * Kriteria: title mengandung kata loading/proses, atau ada spinner HTML.
     */
    function _isLoadingModal(params) {
        var title = String(params.title || '').toLowerCase();
        var html = String(params.html || '').toLowerCase();
        var text = String(params.text || '').toLowerCase();

        var keywords = [
            'memproses', 'memuat', 'loading', 'mohon tunggu',
            'harap tunggu', 'menyimpan', 'mengirim', 'menghapus',
            'sedang memproses', 'sedang mengakhiri', 'sedang membuat',
            'sedang memuat', 'tunggu sebentar', 'memuat detail'
        ];

        var hasLoadingKeyword = keywords.some(function(k) {
            return title.indexOf(k) !== -1 ||
                   html.indexOf(k) !== -1 ||
                   text.indexOf(k) !== -1;
        });

        var hasSpinner = html.indexOf('fa-spin') !== -1 ||
                         html.indexOf('fa-circle-notch') !== -1 ||
                         html.indexOf('fa-spinner') !== -1 ||
                         html.indexOf('swal2-loader') !== -1;

        return hasLoadingKeyword || hasSpinner;
    }

    /**
     * Paksa sembunyikan tombol deny + cancel + confirm di popup yang visible.
     * Dipanggil setelah Swal.fire() resolve dan via MutationObserver.
     */
    function _purgeExtraButtons() {
        var popups = document.querySelectorAll('.swal2-popup:not(.swal2-toast)');
        popups.forEach(function(popup) {
            if (popup._swalDDAllowDeny === true) return;

            // Sembunyikan deny button
            var denyBtns = popup.querySelectorAll('.swal2-deny');
            denyBtns.forEach(function(btn) {
                btn.style.setProperty('display', 'none', 'important');
                btn.style.setProperty('visibility', 'hidden', 'important');
                btn.setAttribute('aria-hidden', 'true');
                btn.setAttribute('tabindex', '-1');
                btn.disabled = true;
            });

            // Tandai popup sebagai loading agar CSS safety net juga menyembunyikan
            if (popup._swalDDIsLoading) {
                popup.classList.add('swal-dd-loading');
            }
        });
    }

    /**
     * Override Swal.fire utama.
     * - Paksa showDenyButton:false di SEMUA popup (kecuali flag _allowDeny)
     * - Auto-detect loading modal, set 3 flag false
     * - Tandai popup untuk CSS safety net
     */
    Swal.fire = function(params) {
        if (!params || typeof params !== 'object') {
            return _originalFire(params);
        }

        // === BUG 1/3: paksa sembunyikan deny button di semua konfirmasi ===
        if (params._allowDeny !== true) {
            params.showDenyButton = false;
            if (params.denyButtonText) {
                // Promote denyButtonText jadi cancelButtonText jika belum ada
                if (!params.cancelButtonText) {
                    params.cancelButtonText = params.denyButtonText;
                    params.showCancelButton = true;
                }
                delete params.denyButtonText;
            }
        }

        // === BUG 2: deteksi loading modal, paksa 3 flag false ===
        var isLoading = _isLoadingModal(params);
        if (isLoading) {
            params.showConfirmButton = false;
            params.showCancelButton = false;
            params.showDenyButton = false;
            params.allowOutsideClick = false;
            params.allowEscapeKey = false;
        }

        // Tandai untuk MutationObserver
        params._swalDDFixed = true;

        try {
            var result = _originalFire(params);
            if (!result || typeof result.then !== 'function') {
                console.warn('[SwalFix] fire() mengembalikan non-Promise.');
                return Promise.resolve({ isConfirmed: false, isDismissed: true });
            }

            // Setelah fire, langsung purge deny button di DOM
            // (SweetAlert2 me-render tombol saat init, jadi kita sikat setelah)
            Promise.resolve().then(function() {
                var popup = document.querySelector('.swal2-popup.swal2-show:not(.swal2-toast)');
                if (popup) {
                    if (isLoading) {
                        popup.classList.add('swal-dd-loading');
                    }
                    _purgeExtraButtons();
                }
            });

            return result;
        } catch (error) {
            console.error('[SwalFix] Error pada Swal.fire():', error);
            return Promise.resolve({ isConfirmed: false, isDismissed: true });
        }
    };

    /**
     * Override Swal.showLoading() — auto-close stuck modal 30 detik.
     */
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

    /**
     * Override Swal.close() — bersihkan timer.
     */
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

    /**
     * MutationObserver: sikat deny button yang lolos hook awal.
     * Observasi subtree body untuk catch semua popup baru.
     */
    if (typeof MutationObserver !== 'undefined') {
        var _denyObserver = new MutationObserver(function(mutations) {
            var needsPurge = false;
            mutations.forEach(function(m) {
                m.addedNodes.forEach(function(node) {
                    if (node.nodeType !== 1) return;
                    if (node.classList && (
                        node.classList.contains('swal2-deny') ||
                        node.classList.contains('swal2-popup')
                    )) {
                        needsPurge = true;
                    }
                    if (node.querySelectorAll) {
                        if (node.querySelectorAll('.swal2-deny').length > 0) needsPurge = true;
                        if (node.querySelectorAll('.swal2-popup').length > 0) needsPurge = true;
                    }
                });
            });
            if (needsPurge) _purgeExtraButtons();
        });
        _denyObserver.observe(document.body, { childList: true, subtree: true });
    }

    /**
     * Periodic safety sweep setiap 500ms selama 5 detik pertama setelah
     * page load (menangkap race condition DOM).
     */
    var _sweepCount = 0;
    var _sweepInterval = setInterval(function() {
        _purgeExtraButtons();
        _sweepCount++;
        if (_sweepCount >= 10) clearInterval(_sweepInterval);
    }, 500);

    /**
     * Fallback untuk kasus yang benar-benar edge:
     * Patch Swal.mixin() — mixin biasanya cache config, override di sini
     * untuk menambah showDenyButton:false default.
     */
    if (Swal.mixin && !Swal.mixin._swalDDPatched) {
        var _originalMixin = Swal.mixin.bind(Swal);
        Swal.mixin = function(opts) {
            opts = opts || {};
            if (opts._allowDeny !== true) {
                opts.showDenyButton = false;
            }
            return _originalMixin(opts);
        };
        Swal.mixin._swalDDPatched = true;
    }

    console.log('[SwalFix] Loaded — Disdukcapil Toba v2.0 (3-bug fix)');
})();
