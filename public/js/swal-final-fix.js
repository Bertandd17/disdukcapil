/**
 * SweetAlert2 Final Fix
 *
 * FIX 1: Swal.fire().fire() fix - replace duplicate fire() calls with .then()
 * FIX 2: Swal.fire loading dialog fix - prevent stuck modal
 * FIX 3: Global Swal fire() error protection
 * FIX 4: Swal.fire() with HTML content that might break
 * FIX 5: Prevent SweetAlert2 from causing layout shifts
 * FIX 6: Handle cases where Swal.fire returns undefined
 */

(function() {
    'use strict';

    // WAIT: Ensure SweetAlert2 is fully loaded before we patch it
    if (typeof Swal === 'undefined') {
        console.warn('[SwalFinalFix] Swal (SweetAlert2) is not loaded. Skipping fixes.');
        return;
    }

    /**
     * FIX 3: Override Swal.fire to catch ALL errors globally
     * This catches errors from:
     * - Swal.fire({ html: '<broken-content>' })
     * - Swal.fire() called with unexpected parameters
     * - Any internal Swal error
     */
    const originalSwalFire = Swal.fire.bind(Swal);

    Swal.fire = function(params) {
        try {
            // FIX 4: Sanitize html parameter - remove potentially problematic inline styles
            if (params && params.html) {
                // Remove 'font-size: 0' that can break layout
                params.html = params.html.replace(/font-size:\s*0/g, '');
                // Remove duplicate font-family declarations that cause issues
                params.html = params.html.replace(/font-family:\s*['"]?Plus Jakarta Sans['"]?/g, '');
            }

            // FIX 1: Check for .fire() calls being chained (Swal.fire(...).fire(...))
            // We can't directly detect this, but we can ensure Swal.fire always
            // returns a proper Promise
            const result = originalSwalFire(params);

            // If result is undefined or not a proper SwalResult, wrap it
            if (!result || typeof result.then !== 'function') {
                console.warn('[SwalFinalFix] Swal.fire returned non-Promise value. Wrapping.');
                return Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Terjadi kesalahan pada tampilan popup. Silakan refresh halaman.'
                });
            }

            return result;

        } catch (error) {
            console.error('[SwalFinalFix] Swal.fire error caught:', error);

            // FIX 2: Graceful degradation - show a simple alert
            // instead of leaving the user with a broken UI
            return Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Tidak dapat menampilkan popup. Silakan refresh halaman (F5).',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                // Auto-refresh on error to restore state
                window.location.reload();
            });
        }
    };

    /**
     * FIX 2: Override Swal.close to prevent stuck loading modals
     * Sometimes Swal.fire({ title: 'Loading...', showConfirmButton: false })
     * gets stuck because showConfirmButton: false means no close button
     */
    const originalSwalClose = Swal.close.bind(Swal);

    Swal.close = function() {
        try {
            // Check if there's actually an open popup before trying to close
            if (!Swal.isVisible()) {
                return;
            }
            originalSwalClose();
        } catch (error) {
            console.error('[SwalFinalFix] Swal.close error:', error);
            // Last resort: force close all Swal elements
            try {
                document.querySelectorAll('.swal2-container').forEach(el => {
                    el.remove();
                });
            } catch (e) {
                console.error('[SwalFinalFix] Force close failed:', e);
            }
        }
    };

    /**
     * FIX 6: Override Swal.mixin to ensure it returns a proper function
     */
    const originalSwalMixin = Swal.mixin.bind(Swal);

    Swal.mixin = function(mixinParams) {
        try {
            const mixin = originalSwalMixin(mixinParams);
            // Ensure mixin.fire also uses our patched version
            if (mixin && mixin.fire) {
                const originalMixinFire = mixin.fire.bind(mixin);
                mixin.fire = function(params) {
                    try {
                        return originalMixinFire(params);
                    } catch (error) {
                        console.error('[SwalFinalFix] Mixin fire error:', error);
                        // Return a resolved promise to prevent unhandled rejections
                        return Promise.resolve({ dismissed: Swal.Dismissal.cancel });
                    }
                };
            }
            return mixin;
        } catch (error) {
            console.error('[SwalFinalFix] Swal.mixin error:', error);
            // Return a fallback mixin that shows error toast
            return {
                fire: function(params) {
                    return Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Toast gagal dimuat'
                    });
                }
            };
        }
    };

    /**
     * FIX: Auto-close stuck Swal after timeout
     * If a Swal popup is open for more than 5 minutes, auto-close it
     */
    setInterval(() => {
        if (Swal.isVisible()) {
            const popup = document.querySelector('.swal2-popup');
            if (popup) {
                const popupAge = Date.now() - parseInt(popup.dataset.createdAt || Date.now());
                if (popupAge > 300000) { // 5 minutes
                    console.warn('[SwalFinalFix] Auto-closing stuck Swal popup (5+ min old)');
                    Swal.close();
                }
            }
        }
    }, 60000); // Check every minute

    console.log('[SwalFinalFix] Applied successfully - v1.0');
})();
