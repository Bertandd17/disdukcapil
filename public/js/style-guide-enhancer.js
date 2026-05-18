(function () {
    'use strict';

    var successPattern = /\b(login|masuk|lanjut|lanjutkan|selanjutnya|next|konfirmasi|setuju|setujui|ya,\s*(lanjutkan|mulai|setujui|konfirmasi|verifikasi|terima)|tambah|simpan|submit|kirim|upload|cari\s+antrian|generate|verifikasi|approve|terima|mulai)\b/i;
    var primaryPattern = /\b(update|perbarui)\b/i;
    var outlinePattern = /\b(edit|ubah)\b/i;
    var secondaryPattern = /\b(batal|kembali|tutup|cancel)\b/i;
    var dangerPattern = /\b(hapus|delete|tolak|batalkan)\b/i;
    var ghostPattern = /\b(skip|info|detail|lihat)\b/i;

    function textOf(el) {
        return (el.textContent || el.value || el.getAttribute('aria-label') || '').replace(/\s+/g, ' ').trim();
    }

    function isButtonLike(el) {
        if (el.hasAttribute('data-style-guide-skip')) return false;
        if (el.classList.contains('sidebar-link')) return false;
        if (el.classList.contains('btn')) return true;
        if (el.tagName === 'BUTTON') return true;
        if (el.getAttribute('role') === 'button') return true;
        if (el.tagName === 'A') {
            var cls = el.className || '';
            return /\b(px-|py-|rounded|bg-|border-|shadow|inline-flex|flex)\b/.test(cls);
        }
        return false;
    }

    function removeVariants(el) {
        ['btn-primary', 'btn-secondary', 'btn-outline', 'btn-ghost', 'btn-danger', 'swal-btn-primary', 'swal-dd-btn-primary'].forEach(function (cls) {
            el.classList.remove(cls);
        });
    }

    function hasVariant(el) {
        return /\bbtn-(primary|secondary|outline|ghost|danger|success)\b/.test(el.className || '') ||
            /\bswal-(dd-)?btn-(primary|secondary|outline|ghost|danger|success|cancel|delete)\b/.test(el.className || '');
    }

    function applyVariant(el) {
        if (!isButtonLike(el)) return;

        var text = textOf(el);
        if (!text) return;

        var variant = '';
        if (dangerPattern.test(text)) {
            if (hasVariant(el)) return;
            variant = 'btn-danger';
        } else if (successPattern.test(text)) {
            if (
                el.classList.contains('style-guide-action') &&
                el.classList.contains('style-guide-positive-action') &&
                el.classList.contains('btn-success')
            ) {
                return;
            }
            removeVariants(el);
            variant = 'btn-success';
            el.classList.add('style-guide-positive-action');
        }
        else if (primaryPattern.test(text)) variant = 'btn-primary';
        else if (outlinePattern.test(text)) variant = 'btn-outline';
        else if (secondaryPattern.test(text)) variant = 'btn-secondary';
        else if (ghostPattern.test(text)) variant = 'btn-ghost';

        if (variant && (!hasVariant(el) || variant === 'btn-success')) {
            el.classList.add('style-guide-action', variant);
        }
    }

    function enhance(root) {
        var scope = root || document;
        scope.querySelectorAll('button, a[role="button"], a[class], input[type="submit"], input[type="button"]').forEach(applyVariant);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { enhance(document); });
    } else {
        enhance(document);
    }

    new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.type === 'characterData' && mutation.target.parentElement) {
                applyVariant(mutation.target.parentElement);
            }
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType !== 1) return;
                if (node.matches && node.matches('button, a[role="button"], a[class], input[type="submit"], input[type="button"]')) {
                    applyVariant(node);
                }
                if (node.querySelectorAll) enhance(node);
            });
        });
    }).observe(document.documentElement, { childList: true, subtree: true, characterData: true });
})();
