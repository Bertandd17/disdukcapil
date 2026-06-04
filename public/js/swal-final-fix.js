(function(window, document) {
    'use strict';

    if (!window.Swal || typeof window.Swal.fire !== 'function') return;

    if (window.Swal.__finalDenyFixApplied) return;
    window.Swal.__finalDenyFixApplied = true;

    var originalFire = window.Swal.fire.bind(window.Swal);
    var originalMixin = typeof window.Swal.mixin === 'function'
        ? window.Swal.mixin.bind(window.Swal)
        : null;

    function sanitizeSwalConfig(config) {
        if (!config || typeof config !== 'object') return config;

        config.showDenyButton = false;
        delete config.denyButtonText;
        delete config.denyButtonColor;
        delete config.denyButtonAriaLabel;

        // jika modal konfirmasi
        if (config.showCancelButton === true) {
            config.showConfirmButton = true;
            config.showCancelButton = true;
            config.reverseButtons = true; // Batal kiri, Konfirmasi kanan
            config.focusCancel = true;
            config.cancelButtonText = config.cancelButtonText || 'Batal';
            config.confirmButtonText = config.confirmButtonText || 'Konfirmasi';
        }

        // jika modal loading
        if (
            config.showConfirmButton === false &&
            config.showCancelButton === false &&
            config.showDenyButton === false &&
            config.allowOutsideClick === false
        ) {
            config.showConfirmButton = false;
            config.showCancelButton = false;
            config.showDenyButton = false;
            config.allowOutsideClick = false;
            if (typeof config.allowEscapeKey === 'undefined') config.allowEscapeKey = false;

            if (!config.customClass) config.customClass = {};
            config.customClass.popup = (config.customClass.popup || '') + ' swal-loading-clean';
        }

        return config;
    }

    window.Swal.fire = function() {
        var args = Array.prototype.slice.call(arguments);
        if (args.length === 1 && typeof args[0] === 'object') {
            args[0] = sanitizeSwalConfig(args[0]);
        }
        var result = originalFire.apply(window.Swal, args);
        setTimeout(hideDenyButton, 0);
        return result;
    };

    function hideDenyButton() {
        var denyButtons = document.querySelectorAll('.swal2-deny');
        denyButtons.forEach(function(btn) {
            btn.style.setProperty('display', 'none', 'important');
            btn.style.setProperty('visibility', 'hidden', 'important');
            btn.style.setProperty('width', '0', 'important');
            btn.style.setProperty('height', '0', 'important');
            btn.style.setProperty('margin', '0', 'important');
            btn.style.setProperty('padding', '0', 'important');
            btn.style.setProperty('opacity', '0', 'important');
            btn.style.setProperty('pointer-events', 'none', 'important');
        });
    }

    if (originalMixin) {
        window.Swal.mixin = function(options) {
            options = sanitizeSwalConfig(options || {});
            var mixed = originalMixin(options);
            var mixedOriginalFire = mixed.fire.bind(mixed);
            mixed.fire = function() {
                var args = Array.prototype.slice.call(arguments);
                if (args.length === 1 && typeof args[0] === 'object') {
                    args[0] = sanitizeSwalConfig(args[0]);
                }
                var result = mixedOriginalFire.apply(mixed, args);
                setTimeout(hideDenyButton, 0);
                return result;
            };
            return mixed;
        };
    }

    document.addEventListener('DOMContentLoaded', hideDenyButton);
    var observer = new MutationObserver(hideDenyButton);
    observer.observe(document.documentElement, { childList: true, subtree: true });

})(window, document);