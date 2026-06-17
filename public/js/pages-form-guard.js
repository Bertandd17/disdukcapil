/**
 * Pages Form Guard — validasi keamanan input pada halaman publik (resources/views/pages/).
 * Memerlukan: sweetalert-helper.js, sweetalert-disdukcapil.js, input-security-validator.js
 */
(function (global) {
    'use strict';

    var TOAST_MESSAGES = {
        xss: {
            judul: 'Input Tidak Valid',
            masalah: 'Karakter yang Anda masukkan tidak diizinkan pada field ini.',
            solusi: 'Periksa kembali input Anda dan hindari penggunaan karakter seperti < > " \' / atau kode skrip.'
        },
        sqli: {
            judul: 'Input Tidak Valid',
            masalah: 'Format input yang Anda masukkan terdeteksi sebagai pola yang tidak diizinkan.',
            solusi: 'Gunakan teks biasa tanpa tanda kutip, tanda hubung ganda (--), atau ekspresi query basis data.'
        },
        generic: {
            judul: 'Input Tidak Dapat Diproses',
            masalah: 'Sistem mendeteksi konten yang berpotensi membahayakan keamanan pada field yang Anda isi.',
            solusi: 'Silakan isi ulang form dengan data yang sesuai dan hindari penggunaan karakter atau simbol khusus.'
        }
    };

    if (!global.Toast) {
        global.Toast = {
            error: function (opts) {
                opts = opts || {};
                var judul = opts.judul || 'Input Tidak Valid';
                var masalah = opts.masalah || '';
                var solusi = opts.solusi || '';

                if (typeof global.fireToast === 'function') {
                    return global.fireToast({
                        type: 'error',
                        icon: 'error',
                        title: judul,
                        problem: masalah,
                        solution: solusi,
                        timer: 5000
                    });
                }
                if (global.SwalHelper && typeof global.SwalHelper.toastError === 'function') {
                    return global.SwalHelper.toastError(masalah, solusi);
                }
            }
        };
    }

    function shouldSkipField(field) {
        if (!field || !field.getAttribute) return true;
        var attr = field.getAttribute('data-validate-security');
        if (attr === 'skip' || attr === 'false') return true;
        if (attr !== 'true') return true;

        var type = (field.type || '').toLowerCase();
        if (type === 'hidden' || type === 'file' || type === 'submit' || type === 'button' || type === 'reset') {
            return true;
        }
        if (field.readOnly || field.disabled) return true;
        if (field.name === '_token' || field.name === 'csrf_token') return true;

        return false;
    }

    function markSecurityInvalid(field, invalid) {
        if (!field) return;
        if (invalid) {
            field.classList.add('border-red-500', 'security-invalid');
            field.setAttribute('data-security-flagged', 'true');
        } else {
            field.classList.remove('border-red-500', 'security-invalid');
            field.removeAttribute('data-security-flagged');
        }
    }

    function showThreatToast(type) {
        var msg = TOAST_MESSAGES[type] || TOAST_MESSAGES.generic;
        global.Toast.error(msg);
    }

    function scanValue(value) {
        if (!global.InputSecurityValidator) {
            return { safe: true, type: null };
        }
        var xss = global.InputSecurityValidator.detectXSS(value);
        if (!xss.safe) return xss;
        return global.InputSecurityValidator.detectSQLi(value);
    }

    function validateField(field, options) {
        options = options || {};
        if (shouldSkipField(field)) return true;

        var result = scanValue(field.value);
        if (result.safe) {
            markSecurityInvalid(field, false);
            return true;
        }

        if (!options.silent) {
            showThreatToast(result.type);
        }

        field.value = '';
        markSecurityInvalid(field, true);

        if (typeof field.dispatchEvent === 'function') {
            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
        }

        return false;
    }

    function validateForm(form, options) {
        options = options || {};
        var fields = form.querySelectorAll('[data-validate-security="true"]');
        var firstThreat = null;
        var firstField = null;

        fields.forEach(function (field) {
            if (shouldSkipField(field)) return;
            var result = scanValue(field.value);
            if (!result.safe) {
                if (!firstThreat) {
                    firstThreat = result.type;
                    firstField = field;
                }
                markSecurityInvalid(field, true);
            } else {
                markSecurityInvalid(field, false);
            }
        });

        if (!firstThreat) return true;

        if (!options.silent) {
            showThreatToast(firstThreat);
        }
        if (firstField) {
            firstField.value = '';
            firstField.dispatchEvent(new Event('input', { bubbles: true }));
            firstField.dispatchEvent(new Event('change', { bubbles: true }));
        }

        return false;
    }

    function onFieldEvent(e) {
        validateField(e.target);
    }

    function attachField(field) {
        if (shouldSkipField(field)) return;
        if (field._pagesFormGuardAttached) return;
        field._pagesFormGuardAttached = true;
        field.addEventListener('input', onFieldEvent);
        field.addEventListener('paste', function (e) {
            var field = e.target;
            setTimeout(function () {
                validateField(field);
            }, 0);
        });
    }

    function onFormSubmit(e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM') return;

        var hasGuarded = form.querySelector('[data-validate-security="true"]');
        if (!hasGuarded) return;

        if (!validateForm(form)) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }

    function scanAllFields() {
        document.querySelectorAll('[data-validate-security="true"]').forEach(attachField);
    }

    function initMutationObserver() {
        if (!global.MutationObserver) return;

        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (!node || node.nodeType !== 1) return;
                    if (node.matches && node.matches('[data-validate-security="true"]')) {
                        attachField(node);
                    }
                    if (node.querySelectorAll) {
                        node.querySelectorAll('[data-validate-security="true"]').forEach(attachField);
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    function init() {
        scanAllFields();
        document.addEventListener('submit', onFormSubmit, true);
        initMutationObserver();
    }

    global.PagesFormGuard = {
        attachField: attachField,
        validateField: validateField,
        validateForm: validateForm,
        scanAllFields: scanAllFields
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window);
