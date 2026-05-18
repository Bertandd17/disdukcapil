<div class="page-loading is-hidden is-removed" data-page-loading role="status" aria-live="polite" aria-label="Memuat halaman" aria-hidden="true">
    <div class="page-loading__logo">
        <img src="{{ asset('images/logo_toba.jpeg') }}" alt="Logo Kabupaten Toba">
    </div>
    <div class="page-loading__spinner" aria-hidden="true"></div>
    <p class="page-loading__title">Disdukcapil Kabupaten Toba</p>
    <p class="page-loading__message" data-page-loading-message>Memuat halaman...</p>
</div>
<script>
    (function () {
        var loader = document.currentScript.previousElementSibling;
        if (!loader || !loader.matches('[data-page-loading]')) return;

        function hideLoader() {
            loader.classList.add('is-hidden');
            loader.setAttribute('aria-hidden', 'true');
            window.setTimeout(function () {
                if (loader.classList.contains('is-hidden')) {
                    loader.classList.add('is-removed');
                }
            }, 260);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', hideLoader, { once: true });
        } else {
            hideLoader();
        }

        window.setTimeout(hideLoader, 900);
    })();
</script>
