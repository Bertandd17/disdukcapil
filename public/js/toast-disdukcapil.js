/**
 * ============================================================
 *  TOAST GLOBAL — Sistem Informasi Disdukcapil Kabupaten Toba
 *  File   : public/js/toast-disdukcapil.js
 *  Load   : SETELAH SweetAlert2, SEBELUM @stack('scripts')
 *  Versi  : 1.0.0
 * ============================================================
 *
 *  API PUBLIK:
 *    Toast.sukses(pesan)
 *    Toast.error({ masalah, solusi })
 *    Toast.warning({ masalah, solusi })
 *    Toast.info(pesan)
 *    Toast.dari_session()   ← otomatis baca window.__flashData
 *
 *  LARANGAN:
 *    - TIDAK BOLEH menampilkan teks literal "success" / "error"
 *    - TIDAK BOLEH punya backdrop atau overlay
 *    - TIDAK BOLEH muncul di tengah layar
 *    - Selalu muncul di pojok kanan atas (top-end)
 * ============================================================
 */

(function (window, document) {
    'use strict';

    /* ── Guard: jangan load dua kali ─────────────────────── */
    if (window.Toast && window.Toast.__loaded) return;

    /* ── Pastikan SweetAlert2 tersedia ───────────────────── */
    if (!window.Swal || typeof window.Swal.fire !== 'function') {
        console.warn('[Toast] SweetAlert2 belum tersedia. Toast tidak aktif.');
        return;
    }

    /* ══════════════════════════════════════════════════════
       CSS OVERRIDE — inject sekali, tidak bisa di-override
       ══════════════════════════════════════════════════════ */
    if (!document.getElementById('toast-disdukcapil-style')) {
        var style = document.createElement('style');
        style.id  = 'toast-disdukcapil-style';
        style.textContent = [
            /* Container — WAJIB transparent, BUKAN 100vw/100vh */
            '.swal2-container.swal2-top-end,',
            '.swal2-container.swal2-top-right {',
            '    background    : transparent !important;',
            '    pointer-events: none         !important;',
            '    width         : fit-content  !important;',
            '    height        : fit-content  !important;',
            '}',
            /* Toast individual */
            '.swal2-popup.swal2-toast {',
            '    pointer-events: all           !important;',
            '    border-radius : 12px          !important;',
            '    box-shadow    : 0 8px 24px rgba(0,0,0,0.12) !important;',
            '    font-family   : "Plus Jakarta Sans", sans-serif !important;',
            '    min-width     : 300px         !important;',
            '    max-width     : 420px         !important;',
            '    padding       : 14px 18px     !important;',
            '}',
            /* Sukses — border kiri hijau */
            '.toast-sukses {',
            '    border-left: 4px solid #43A047 !important;',
            '    background : #ffffff          !important;',
            '}',
            '.toast-sukses .swal2-icon { color: #43A047 !important; border-color: #43A047 !important; }',
            '.toast-sukses .swal2-title { color: #1f2937 !important; font-size: 0.9rem !important; font-weight: 600 !important; }',
            /* Error — border kiri merah */
            '.toast-error {',
            '    border-left: 4px solid #E53935 !important;',
            '    background : #ffffff          !important;',
            '}',
            '.toast-error .swal2-icon { color: #E53935 !important; border-color: #E53935 !important; }',
            '.toast-error .swal2-title { color: #1f2937 !important; font-size: 0.9rem !important; font-weight: 600 !important; }',
            '.toast-error .swal2-html-container { font-size: 0.8rem !important; color: #6b7280 !important; margin-top: 4px !important; }',
            /* Warning — border kiri orange */
            '.toast-warning {',
            '    border-left: 4px solid #FB8C00 !important;',
            '    background : #ffffff          !important;',
            '}',
            '.toast-warning .swal2-icon { color: #FB8C00 !important; border-color: #FB8C00 !important; }',
            '.toast-warning .swal2-title { color: #1f2937 !important; font-size: 0.9rem !important; font-weight: 600 !important; }',
            '.toast-warning .swal2-html-container { font-size: 0.8rem !important; color: #6b7280 !important; margin-top: 4px !important; }',
            /* Info — border kiri biru */
            '.toast-info {',
            '    border-left: 4px solid #1976D2 !important;',
            '    background : #ffffff          !important;',
            '}',
            '.toast-info .swal2-icon { color: #1976D2 !important; border-color: #1976D2 !important; }',
            '.toast-info .swal2-title { color: #1f2937 !important; font-size: 0.9rem !important; font-weight: 600 !important; }',
            /* Progress bar warna per tipe */
            '.toast-sukses  .swal2-timer-progress-bar { background: #43A047 !important; }',
            '.toast-error   .swal2-timer-progress-bar { background: #E53935 !important; }',
            '.toast-warning .swal2-timer-progress-bar { background: #FB8C00 !important; }',
            '.toast-info    .swal2-timer-progress-bar { background: #1976D2 !important; }',
        ].join('\n');
        document.head.appendChild(style);
    }

    /* ══════════════════════════════════════════════════════
       BASE MIXIN — konfigurasi dasar semua toast
       ══════════════════════════════════════════════════════ */
    var _base = window.Swal.mixin({
        toast            : true,
        position         : 'top-end',   /* pojok kanan atas — WAJIB */
        showConfirmButton: false,
        showDenyButton   : false,
        showCancelButton : false,
        timerProgressBar : true,
        didOpen: function (t) {
            t.addEventListener('mouseenter', window.Swal.stopTimer);
            t.addEventListener('mouseleave', window.Swal.resumeTimer);
        }
    });

    /* ══════════════════════════════════════════════════════
       HELPER INTERNAL
       ══════════════════════════════════════════════════════ */

    /**
     * Validasi: judul tidak boleh literal "success" atau "error"
     */
    function _sanitizeJudul(judul, fallback) {
        var blacklist = ['success', 'error', 'warning', 'info',
                         'sukses', 'berhasil ok', 'failed'];
        if (!judul) return fallback;
        var lower = String(judul).toLowerCase().trim();
        return blacklist.indexOf(lower) !== -1 ? fallback : judul;
    }

    /**
     * Buat HTML detail error (masalah + solusi)
     */
    function _buatHtmlDetail(masalah, solusi) {
        var html = '';
        if (masalah) {
            html += '<span style="display:block;font-size:0.8rem;'
                  + 'color:#6b7280;margin-top:2px">'
                  + '<strong style="color:#374151">Masalah:</strong> '
                  + masalah + '</span>';
        }
        if (solusi) {
            html += '<span style="display:block;font-size:0.8rem;'
                  + 'color:#6b7280;margin-top:2px">'
                  + '<strong style="color:#374151">Solusi:</strong> '
                  + solusi + '</span>';
        }
        return html;
    }

    /* ══════════════════════════════════════════════════════
       API PUBLIK — window.Toast
       ══════════════════════════════════════════════════════ */
    var Toast = {

        __loaded: true,

        /**
         * Toast SUKSES — background putih, border kiri hijau, ikon ✓
         *
         * @param {string} pesan   Teks singkat (maks 80 karakter)
         * @param {number} [timer] Default 4000ms
         *
         * Contoh:
         *   Toast.sukses('Data antrian berhasil disimpan.');
         */
        sukses: function (pesan, timer) {
            var judul = _sanitizeJudul(pesan, 'Data berhasil disimpan.');
            _base.fire({
                icon       : 'success',
                title      : judul,
                timer      : timer || 4000,
                customClass: { popup: 'toast-sukses' }
            });
        },

        /**
         * Toast ERROR — background putih, border kiri merah, ikon ✗
         * Wajib berisi: masalah DAN solusi agar user tahu harus apa.
         *
         * @param {object|string} opsi
         *   opsi.judul   — judul singkat (default: 'Terjadi Kesalahan')
         *   opsi.masalah — apa yang salah
         *   opsi.solusi  — langkah yang harus dilakukan user
         *   opsi.timer   — default 6000ms
         *
         * Contoh:
         *   Toast.error({
         *     judul  : 'Gagal Menyimpan Data',
         *     masalah: 'Nomor KK yang dimasukkan sudah terdaftar.',
         *     solusi : 'Periksa kembali nomor KK atau hubungi petugas.'
         *   });
         *
         * Atau singkat (hanya string):
         *   Toast.error('Koneksi bermasalah. Coba lagi.');
         */
        error: function (opsi, timer) {
            var judul, masalah, solusi, html;

            if (typeof opsi === 'string') {
                judul   = _sanitizeJudul(opsi, 'Terjadi Kesalahan');
                masalah = null;
                solusi  = 'Muat ulang halaman dan coba lagi.';
            } else {
                opsi    = opsi || {};
                judul   = _sanitizeJudul(
                    opsi.judul   || opsi.title,
                    'Terjadi Kesalahan'
                );
                masalah = opsi.masalah || opsi.message || null;
                solusi  = opsi.solusi  || opsi.solution
                        || 'Coba lagi atau hubungi petugas Disdukcapil.';
            }

            html = _buatHtmlDetail(masalah, solusi);

            _base.fire({
                icon            : 'error',
                title           : judul,
                html            : html || undefined,
                timer           : timer || opsi.timer || 6000,
                customClass     : { popup: 'toast-error' }
            });
        },

        /**
         * Toast WARNING — background putih, border kiri orange, ikon !
         *
         * @param {object|string} opsi
         *   opsi.judul   — judul peringatan
         *   opsi.masalah — apa yang perlu diperhatikan
         *   opsi.solusi  — apa yang harus dilakukan
         *
         * Contoh:
         *   Toast.warning({
         *     judul  : 'Ada Dokumen yang Belum Dilengkapi',
         *     masalah: 'KTP Pemohon belum diunggah.',
         *     solusi : 'Unggah KTP Pemohon sebelum melanjutkan.'
         *   });
         */
        warning: function (opsi, timer) {
            var judul, masalah, solusi, html;

            if (typeof opsi === 'string') {
                judul   = _sanitizeJudul(opsi, 'Perhatian');
                masalah = null;
                solusi  = null;
            } else {
                opsi    = opsi || {};
                judul   = _sanitizeJudul(
                    opsi.judul || opsi.title,
                    'Perhatian'
                );
                masalah = opsi.masalah || opsi.message || null;
                solusi  = opsi.solusi  || opsi.solution || null;
            }

            html = _buatHtmlDetail(masalah, solusi);

            _base.fire({
                icon       : 'warning',
                title      : judul,
                html       : html || undefined,
                timer      : timer || opsi.timer || 5000,
                customClass: { popup: 'toast-warning' }
            });
        },

        /**
         * Toast INFO — background putih, border kiri biru
         *
         * @param {string} pesan
         *
         * Contoh:
         *   Toast.info('Data sedang diproses oleh petugas.');
         */
        info: function (pesan, timer) {
            var judul = _sanitizeJudul(pesan, 'Informasi');
            _base.fire({
                icon       : 'info',
                title      : judul,
                timer      : timer || 4000,
                customClass: { popup: 'toast-info' }
            });
        },

        /**
         * Otomatis tampilkan toast dari session flash Laravel.
         * Dipanggil oleh layout blade via window.__flashData.
         * JANGAN panggil manual — sudah dipanggil otomatis.
         */
        dari_session: function () {
            var flash = window.__flashData;
            if (!flash) return;

            if (flash.success) {
                Toast.sukses(flash.success);
            }

            if (flash.error) {
                if (typeof flash.error === 'object') {
                    Toast.error({
                        judul  : flash.error.title   || 'Terjadi Kesalahan',
                        masalah: flash.error.message || null,
                        solusi : flash.error.solution
                               || 'Coba lagi atau hubungi petugas.'
                    });
                } else {
                    Toast.error(flash.error);
                }
            }

            if (flash.warning) {
                if (typeof flash.warning === 'object') {
                    Toast.warning({
                        judul  : flash.warning.title   || 'Perhatian',
                        masalah: flash.warning.message || null,
                        solusi : flash.warning.solution || null
                    });
                } else {
                    Toast.warning(flash.warning);
                }
            }

            if (flash.info) {
                Toast.info(flash.info);
            }

            // Bersihkan setelah ditampilkan
            window.__flashData = null;
        }
    };

    /* ── Expose ke window ───────────────────────────────── */
    window.Toast = Toast;

    /* ── Alias backward-compatible ──────────────────────── */
    window.showToast = function (pesan, tipe, timer) {
        tipe = (tipe || 'info').toLowerCase();
        if (tipe === 'success' || tipe === 'sukses') return Toast.sukses(pesan, timer);
        if (tipe === 'error'   || tipe === 'danger') return Toast.error(pesan, timer);
        if (tipe === 'warning' || tipe === 'warn')   return Toast.warning(pesan, timer);
        return Toast.info(pesan, timer);
    };

    /* ── Auto-trigger dari session saat DOM siap ─────────── */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', Toast.dari_session);
    } else {
        Toast.dari_session();
    }

})(window, document);