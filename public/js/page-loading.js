(function () {
    'use strict';

    var overlay;
    var hideTimer;

    function getOverlay() {
        if (!overlay) {
            overlay = document.querySelector('[data-page-loading]');
        }
        return overlay;
    }

    function setMessage(message) {
        var el = getOverlay();
        if (!el || !message) return;
        var messageEl = el.querySelector('[data-page-loading-message]');
        if (messageEl) messageEl.textContent = message;
    }

    function hide() {
        var el = getOverlay();
        if (!el) return;
        window.clearTimeout(hideTimer);
        hideTimer = window.setTimeout(function () {
            el.classList.add('is-hidden');
            el.setAttribute('aria-hidden', 'true');
            window.setTimeout(function () {
                if (el.classList.contains('is-hidden')) {
                    el.classList.add('is-removed');
                }
            }, 220);
        }, 30);
    }

    function show(message) {
        var el = getOverlay();
        if (!el) return;
        window.clearTimeout(hideTimer);
        setMessage(message || 'Memuat halaman...');
        el.classList.remove('hidden', 'is-hidden', 'is-removed');
        el.setAttribute('aria-hidden', 'false');
    }

    function isSamePageHash(url) {
        return url.origin === window.location.origin
            && url.pathname === window.location.pathname
            && url.search === window.location.search
            && url.hash;
    }

    function shouldShowForLink(link, event) {
        if (!link || event.defaultPrevented || event.button !== 0) return false;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
        if (link.closest('[data-no-page-loading]')) return false;
        if (link.target && link.target !== '_self') return false;
        if (link.hasAttribute('download')) return false;

        var href = link.getAttribute('href');
        if (!href || href.charAt(0) === '#') return false;
        if (/^(javascript:|mailto:|tel:)/i.test(href)) return false;

        try {
            var url = new URL(href, window.location.href);
            if (url.origin !== window.location.origin) return false;
            if (isSamePageHash(url)) return false;
        } catch (e) {
            return false;
        }

        return true;
    }

    function bindNavigation() {
        document.addEventListener('click', function (event) {
            var link = event.target.closest ? event.target.closest('a[href]') : null;
            if (shouldShowForLink(link, event)) {
                show('Membuka halaman...');
            }
        });

        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (!form || event.defaultPrevented || form.matches('[data-no-page-loading]')) return;
            show('Memproses data...');
        });

        window.addEventListener('pageshow', hide);
        window.addEventListener('load', hide);

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', hide, { once: true });
        } else {
            hide();
        }
    }

    window.PageLoading = {
        show: show,
        hide: hide
    };

    bindNavigation();
})();
