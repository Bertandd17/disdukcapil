/**
 * =====================================================================
 * NOTIFIKASI DISDUKCAPIL TOBA — FILE FINAL TUNGGAL
 * =====================================================================
 * Satu file untuk semua notifikasi SweetAlert2 di project.
 *
 * ATURAN KETAT (T27):
 *   - Sukses  → background HIJAU, ikon ✓
 *   - Error   → background MERAH,  ikon ✗
 *   - Posisi  → top-end (pojok kanan atas)
 *   - TANPA backdrop / overlay gelap
 *   - TIDAK menampilkan teks literal "success" / "error"
 *
 * @author  Disdukcapil Toba
 * @version 3.0.0 (Final Tunggal)
 * @requires SweetAlert2 v11.x
 * =====================================================================
 */
(function (window, document) {
    'use strict';

    if (!window.Swal) {
        console.warn('[Notifikasi] SweetAlert2 belum dimuat. Pastikan <script src="sweetalert2@11"> ada sebelum file ini.');
        return;
    }

    // =====================================================================
    // 1. INJECT STYLE — Toast top-end: HIJAU (sukses) / MERAH (error)
    // =====================================================================
    var STYLE_ID = 'notifikasi-disdukcapil-styles';
    if (!document.getElementById(STYLE_ID)) {
        var T = '.swal2-popup.swal2-toast.swal-dd-toast';
        var css =
            // Hilangkan backdrop/overlay gelap pada toast & top-end
            '.swal2-container.swal2-toast,' +
            '.swal2-container.swal2-top,' +
            '.swal2-container.swal2-top-end,' +
            '.swal2-container.swal2-top-right{' +
                'background:transparent!important;' +
                'background-color:transparent!important;' +
                'background-image:none!important;' +
                'backdrop-filter:none!important;' +
                '-webkit-backdrop-filter:none!important;' +
                'filter:none!important;' +
                'pointer-events:none!important;' +
            '}' +
            '.swal2-container.swal2-toast .swal2-backdrop,' +
            '.swal2-container.swal2-top-end .swal2-backdrop{display:none!important;background:transparent!important;}' +

            // Basis toast: kartu putih, rounded, shadow
            T + '{' +
                'font-family:"Plus Jakarta Sans",sans-serif!important;' +
                'color:#0f172a!important;' +
                'background:#ffffff!important;' +
                'background-color:#ffffff!important;' +
                'border-radius:14px!important;' +
                'box-shadow:0 12px 32px rgba(15,23,42,0.18)!important;' +
                'padding:14px 18px!important;' +
                'min-width:320px!important;' +
                'max-width:420px!important;' +
                'border:0!important;' +
                'display:grid!important;' +
                'grid-template-columns:44px minmax(0,1fr) auto!important;' +
                'column-gap:14px!important;' +
                'align-items:center!important;' +
                'backdrop-filter:none!important;' +
                '-webkit-backdrop-filter:none!important;' +
            '}' +

            // === SUKSES → background HIJAU + border hijau + ikon putih ✓
            T + '.swal-dd-success{' +
                'background:linear-gradient(135deg,#22c55e 0%,#16a34a 100%)!important;' +
                'background-color:#16a34a!important;' +
                'border:0!important;' +
                'border-left:5px solid #15803d!important;' +
            '}' +
            T + '.swal-dd-success .swal2-title{color:#ffffff!important;font-weight:700!important;font-size:14.5px!important;}' +
            T + '.swal-dd-success .swal2-html-container{color:#f0fdf4!important;font-size:12.5px!important;line-height:1.5!important;margin-top:3px!important;}' +

            // === ERROR → background MERAH + border merah + ikon putih ✗
            T + '.swal-dd-error{' +
                'background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%)!important;' +
                'background-color:#dc2626!important;' +
                'border:0!important;' +
                'border-left:5px solid #b91c1c!important;' +
            '}' +
            T + '.swal-dd-error .swal2-title{color:#ffffff!important;font-weight:700!important;font-size:14.5px!important;}' +
            T + '.swal-dd-error .swal2-html-container{color:#fef2f2!important;font-size:12.5px!important;line-height:1.5!important;margin-top:3px!important;}' +
            T + '.swal-dd-error-detail{display:grid;gap:8px;margin-top:4px;}' +
            T + '.swal-dd-error-block{padding:8px 10px;border-radius:8px;background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.28);}' +
            T + '.swal-dd-error-label{display:block;margin-bottom:3px;color:#ffffff;font-size:10.5px!important;font-weight:800!important;letter-spacing:0.5px!important;text-transform:uppercase!important;}' +
            T + '.swal-dd-error-text{display:block;color:#fef2f2!important;font-size:12px!important;font-weight:500!important;line-height:1.5!important;}' +

            // Title & html container umum
            T + ' .swal2-title{font-size:14.5px!important;font-weight:600!important;line-height:1.4!important;margin:0!important;padding:0!important;text-align:left!important;grid-column:2!important;min-width:0!important;}' +
            T + ' .swal2-html-container{font-size:12.5px!important;font-weight:400!important;line-height:1.5!important;margin:3px 0 0 0!important;padding:0!important;text-align:left!important;display:block!important;grid-column:2!important;min-width:0!important;}' +

            // Ikon: lingkaran 38px, tengah, dan override icon bawaan SweetAlert
            T + ' .swal2-icon{grid-column:1!important;grid-row:1 / span 2!important;align-self:center!important;justify-self:center!important;position:relative!important;margin:0!important;width:38px!important;height:38px!important;min-width:38px!important;flex:0 0 38px!important;border-width:2px!important;border-style:solid!important;border-radius:999px!important;box-sizing:border-box!important;display:flex!important;align-items:center!important;justify-content:center!important;line-height:1!important;overflow:hidden!important;}' +
            T + '.swal-dd-success .swal2-icon{background:#ffffff!important;border-color:#bbf7d0!important;color:#16a34a!important;}' +
            T + '.swal-dd-error .swal2-icon{background:#ffffff!important;border-color:#fecaca!important;color:#dc2626!important;}' +

            // Sembunyikan icon SVGs bawaan SWAL (cincin, garis centang, x-mark) — kita gambar sendiri via ::before
            T + ' .swal2-icon .swal2-icon-content,' +
            T + ' .swal2-icon .swal2-success-ring,' +
            T + ' .swal2-icon .swal2-success-fix,' +
            T + ' .swal2-icon .swal2-success-circular-line-left,' +
            T + ' .swal2-icon .swal2-success-circular-line-right,' +
            T + ' .swal2-icon .swal2-success-line-tip,' +
            T + ' .swal2-icon .swal2-success-line-long,' +
            T + ' .swal2-icon .swal2-x-mark,' +
            T + ' .swal2-icon .swal2-x-mark-line-left,' +
            T + ' .swal2-icon .swal2-x-mark-line-right{display:none!important;}' +

            // Ikon kustom via ::before — karakter Unicode
            T + ' .swal2-icon::before{position:absolute!important;inset:0!important;display:flex!important;align-items:center!important;justify-content:center!important;width:100%!important;height:100%!important;font-size:22px!important;font-weight:800!important;line-height:1!important;text-align:center!important;transform:translateY(-1px)!important;color:currentColor!important;}' +
            T + ' .swal2-icon::after{display:none!important;}' +
            // ✓ untuk sukses, ✗ untuk error
            T + '.swal-dd-success .swal2-icon::before{content:"\\2713"!important;}' +
            T + '.swal-dd-error .swal2-icon::before{content:"\\2715"!important;}' +

            // Close button
            T + ' .swal2-close{grid-column:3!important;grid-row:1 / span 2!important;align-self:start!important;color:#ffffff!important;font-size:20px!important;width:22px!important;height:22px!important;margin:-2px -4px 0 4px!important;opacity:0.85!important;}' +
            T + '.swal-dd-success .swal2-close{color:#ffffff!important;}' +
            T + '.swal-dd-error .swal2-close{color:#ffffff!important;}' +
            T + ' .swal2-close:hover{opacity:1!important;}' +

            // Progress bar
            T + ' .swal2-timer-progress-bar-container{display:block!important;height:3px!important;background:rgba(255,255,255,0.25)!important;}' +
            T + ' .swal2-timer-progress-bar{background:#ffffff!important;height:3px!important;}' +

            // === MODAL: kartu putih bersih (tanpa backdrop gelap) ===
            '.swal-dd-modal{' +
                'font-family:"Plus Jakarta Sans",sans-serif!important;' +
                'background:#ffffff!important;' +
                'background-color:#ffffff!important;' +
                'color:#0f172a!important;' +
                'border-radius:16px!important;' +
                'padding:28px!important;' +
                'box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)!important;' +
            '}' +
            '.swal-dd-modal .swal2-title{color:#0f172a!important;font-weight:700!important;font-size:18px!important;}' +
            '.swal-dd-modal .swal2-html-container{color:#475569!important;font-size:14px!important;line-height:1.6!important;}' +
            // Tombol modal
            '.swal-dd-btn{padding:11px 20px!important;border-radius:10px!important;font-weight:600!important;font-size:13.5px!important;border:none!important;box-shadow:none!important;}' +
            '.swal-dd-btn-primary{background:#2563eb!important;color:#ffffff!important;}' +
            '.swal-dd-btn-primary:hover{background:#1d4ed8!important;}' +
            '.swal-dd-btn-success{background:#16a34a!important;color:#ffffff!important;}' +
            '.swal-dd-btn-success:hover{background:#15803d!important;}' +
            '.swal-dd-btn-danger{background:#dc2626!important;color:#ffffff!important;}' +
            '.swal-dd-btn-danger:hover{background:#b91c1c!important;}' +
            '.swal-dd-btn-warning{background:#ea580c!important;color:#ffffff!important;}' +
            '.swal-dd-btn-cancel{background:#e5e7eb!important;color:#1f2937!important;}' +
            '.swal-dd-btn-cancel:hover{background:#d1d5db!important;}' +
            '.swal-dd-modal .swal2-actions{gap:10px!important;margin-top:20px!important;}' +
            // Tombol Deny (3-tombol) selalu sembunyi — sesuai standar
            '.swal2-deny{display:none!important;}' +

            // Animasi slide masuk/keluar
            '@keyframes notifikasi-slidein{from{opacity:0;transform:translateX(120%);}to{opacity:1;transform:translateX(0);}}' +
            '@keyframes notifikasi-slideout{from{opacity:1;transform:translateX(0);}to{opacity:0;transform:translateX(120%);}}' +
            T + '.swal2-show{animation:notifikasi-slidein .28s ease-out!important;}' +
            T + '.swal2-hide{animation:notifikasi-slideout .25s ease-in!important;}';

        var styleEl = document.createElement('style');
        styleEl.id = STYLE_ID;
        styleEl.appendChild(document.createTextNode(css));
        (document.head || document.documentElement).appendChild(styleEl);
    }

    // =====================================================================
    // 2. UTILITIES
    // =====================================================================
    function escapeHtml(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function normalizeWhitespace(s) {
        return String(s || '').replace(/\s+/g, ' ').trim();
    }

    function htmlToText(s) {
        if (!s) return '';
        var div = document.createElement('div');
        div.innerHTML = String(s);
        return normalizeWhitespace(div.textContent || div.innerText || String(s).replace(/<[^>]*>/g, ' '));
    }

    function generateNoReg() {
        var year = new Date().getFullYear();
        var rand = Math.floor(Math.random() * 900000) + 100000;
        return '#REG-' + year + '-' + rand;
    }

    // Default solution per kategori error (dipakai bila caller tidak kasih)
    function defaultErrorSolution(problem) {
        var text = normalizeWhitespace(problem).toLowerCase();
        if (!text) return 'Periksa data yang Anda masukkan, lalu coba lagi.';
        if (/(wajib diisi|tidak boleh kosong|isian yang kosong|harus dipilih|harap lengkapi|mohon lengkapi|lengkapi (semua|seluruh) data|form belum lengkap|required)/i.test(text))
            return 'Lengkapi kolom yang bertanda wajib, lalu lanjutkan kembali.';
        if (/(pdf|format file|berformat)/i.test(text))
            return 'Pilih ulang file dengan format PDF sesuai ketentuan.';
        if (/(ukuran file|2mb|5mb|maksimal)/i.test(text))
            return 'Kompres file atau pilih file yang ukurannya sesuai batas maksimal.';
        if (/(nik|nomor kk|16 digit)/i.test(text))
            return 'Masukkan angka yang benar sesuai dokumen kependudukan.';
        if (/(koneksi|jaringan|network|fetch|http)/i.test(text))
            return 'Periksa koneksi internet, lalu ulangi beberapa saat lagi.';
        if (/(csrf|kadaluarsa|kedaluwarsa|419|session)/i.test(text))
            return 'Muat ulang halaman, lalu kirim formulir kembali.';
        if (/(nomor antrian|antrian)/i.test(text))
            return 'Periksa kembali nomor antrian dan pastikan layanan yang dipilih sesuai.';
        return 'Periksa data atau aksi yang sedang dilakukan, lalu coba lagi.';
    }

    function buildErrorHtml(problem, solution) {
        return '<div class="swal-dd-error-detail">' +
            '<div class="swal-dd-error-block"><span class="swal-dd-error-label">Masalah</span><span class="swal-dd-error-text">' + escapeHtml(problem) + '</span></div>' +
            '<div class="swal-dd-error-block"><span class="swal-dd-error-label">Cara memperbaiki</span><span class="swal-dd-error-text">' + escapeHtml(solution) + '</span></div>' +
            '</div>';
    }

    // =====================================================================
    // 3. CORE: fireToast + fireModal
    // =====================================================================

    /**
     * Menampilkan toast top-end.
     * @param {Object} opts { type: 'success'|'error'|'warning'|'info', title, html, timer }
     * Tidak menampilkan teks literal "success" / "error".
     */
    function fireToast(opts) {
        opts = opts || {};
        var type = (opts.type || opts.icon || 'info');
        if (type !== 'success' && type !== 'error' && type !== 'warning' && type !== 'info') {
            type = 'info';
        }
        var timer = typeof opts.timer === 'number' ? opts.timer : 5000;

        // Untuk error: normalisasi problem + solution
        var html = opts.html;
        if (type === 'error' && (!html || String(html).indexOf('swal-dd-error-detail') === -1)) {
            var problem = opts.problem || opts.text || htmlToText(html) || 'Terjadi kesalahan saat memproses permintaan.';
            var solution = opts.solution || defaultErrorSolution(problem);
            html = buildErrorHtml(problem, solution);
        }

        var cfg = {
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            showCloseButton: true,
            timer: timer,
            timerProgressBar: true,
            backdrop: false,
            icon: type,
            iconColor: '#ffffff',
            customClass: {
                popup: 'swal-dd-toast swal-dd-' + type
            },
            didOpen: function (toast) {
                toast.addEventListener('mouseenter', window.Swal.stopTimer);
                toast.addEventListener('mouseleave', window.Swal.resumeTimer);
            }
        };
        if (opts.title) cfg.title = opts.title;
        if (html) cfg.html = html;
        if (opts.text && !html) cfg.text = opts.text;

        return window.Swal.fire(cfg);
    }

    /**
     * Menampilkan modal (tanpa backdrop gelap, kartu putih bersih).
     */
    function fireModal(opts) {
        var base = {
            backdrop: false,
            allowOutsideClick: opts.allowOutsideClick === true,
            allowEscapeKey: opts.allowEscapeKey === true,
            showCloseButton: opts.showCloseButton === true,
            showDenyButton: false,
            customClass: Object.assign({
                popup: 'swal-dd-modal',
                confirmButton: 'swal-dd-btn swal-dd-btn-primary',
                cancelButton: 'swal-dd-btn swal-dd-btn-cancel'
            }, opts.customClass || {})
        };
        delete opts.customClass;
        delete opts.showDenyButton;
        return window.Swal.fire(Object.assign(base, opts));
    }

    // =====================================================================
    // 4. FUNGSI PUBLIK — SIMPAN / UPDATE
    // =====================================================================

    /** Toast hijau sukses simpan. */
    function notifSimpanBerhasil(nomorRegistrasi) {
        var no = escapeHtml(nomorRegistrasi || generateNoReg());
        return fireToast({
            type: 'success',
            title: 'Data berhasil disimpan',
            html: 'No. Registrasi: <strong>' + no + '</strong>',
            timer: 5000
        });
    }

    /** Toast merah gagal simpan (otomatis: Masalah + Cara memperbaiki). */
    function notifSimpanGagal(pesanError) {
        return fireToast({
            type: 'error',
            problem: pesanError || 'Gagal menyimpan data ke server.',
            solution: defaultErrorSolution(pesanError || ''),
            timer: 6000
        });
    }

    // =====================================================================
    // 5. FUNGSI PUBLIK — VALIDASI FORM
    // =====================================================================

    function notifValidasiGagal(arrayKesalahan) {
        var errs = Array.isArray(arrayKesalahan) ? arrayKesalahan : [String(arrayKesalahan || 'Data tidak valid')];
        var problem = errs.length + ' kolom tidak valid: ' + errs.join('; ');
        return fireToast({
            type: 'error',
            problem: problem,
            solution: 'Perbaiki kolom yang ditandai, lalu kirim ulang formulir.',
            timer: 6000
        });
    }

    function notifFormBelumLengkap() {
        return fireToast({
            type: 'error',
            problem: 'Ada kolom wajib yang belum diisi.',
            solution: 'Lengkapi semua kolom yang bertanda wajib, lalu lanjutkan kembali.',
            timer: 6000
        });
    }

    // =====================================================================
    // 6. FUNGSI PUBLIK — UPLOAD DOKUMEN
    // =====================================================================

    function notifUploadProses(namaFile, onSuccess, onError) {
        var nf = escapeHtml(namaFile || 'file');

        window.Swal.fire({
            title: 'Mengunggah ' + nf + '...',
            html: '<div style="display:flex;justify-content:center;margin-top:8px;">' +
                  '<i class="fas fa-cloud-upload-alt" style="font-size:42px;color:#2563eb;"></i>' +
                  '</div>' +
                  '<p style="margin-top:14px;color:#64748b;font-size:13.5px;">Mohon tunggu, file sedang diunggah ke server.</p>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            backdrop: false,
            customClass: { popup: 'swal-dd-modal' },
            didOpen: function () { window.Swal.showLoading(); }
        });

        var task;
        try {
            task = typeof onSuccess === 'function' ? onSuccess() : Promise.resolve();
        } catch (err) {
            task = Promise.reject(err);
        }
        if (!task || typeof task.then !== 'function') task = Promise.resolve(task);

        return task.then(function (res) {
            window.Swal.close();
            fireToast({
                type: 'success',
                title: 'Berhasil diunggah',
                html: '<strong>' + nf + '</strong> berhasil disimpan.',
                timer: 5000
            });
            return res;
        }).catch(function (err) {
            return window.Swal.fire({
                icon: 'error',
                title: 'File gagal diunggah',
                html: '<div style="text-align:left;color:#475569;font-size:13.5px;line-height:1.6;">' +
                      '<p style="margin:0 0 10px;">File <strong>' + nf + '</strong> tidak berhasil diunggah.</p>' +
                      buildErrorHtml(
                          (err && err.message) ? err.message : 'Upload file gagal diproses server.',
                          'Pastikan format file PDF/JPG/PNG, ukuran ≤ 5MB, dan koneksi internet stabil.'
                      ) +
                      '</div>',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-redo mr-2"></i>Coba Lagi',
                cancelButtonText: 'Tutup',
                reverseButtons: true,
                backdrop: false,
                customClass: {
                    popup: 'swal-dd-modal',
                    confirmButton: 'swal-dd-btn swal-dd-btn-danger',
                    cancelButton: 'swal-dd-btn swal-dd-btn-cancel'
                }
            }).then(function (r) {
                if (r.isConfirmed && typeof onError === 'function') onError();
            });
        });
    }

    // =====================================================================
    // 7. FUNGSI PUBLIK — PENGAJUAN
    // =====================================================================

    function notifPengajuanProses(jenisDokumen, onSelesai, onTambah) {
        var jd = escapeHtml(jenisDokumen || 'dokumen');

        window.Swal.fire({
            title: 'Memproses pengajuan ' + jd + '...',
            html: '<p style="color:#64748b;font-size:13.5px;margin-top:10px;">Mohon tunggu, sistem sedang memvalidasi data.</p>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            backdrop: false,
            customClass: { popup: 'swal-dd-modal' },
            didOpen: function () { window.Swal.showLoading(); }
        });

        setTimeout(function () {
            var noReg = generateNoReg();
            window.Swal.fire({
                icon: 'success',
                title: 'Pengajuan Berhasil',
                html: '<div style="text-align:center;">' +
                      '<p style="color:#475569;font-size:14px;margin:6px 0 12px;">Pengajuan <strong>' + jd + '</strong> telah diterima.</p>' +
                      '<div style="background:#f0fdf4;border:1px solid #16a34a;border-radius:12px;padding:14px 16px;display:inline-block;">' +
                      '<p style="margin:0;color:#15803d;font-size:12px;font-weight:600;letter-spacing:0;">NOMOR REGISTRASI</p>' +
                      '<p style="margin:6px 0 0;color:#15803d;font-size:22px;font-weight:800;letter-spacing:0;">' + noReg + '</p>' +
                      '</div>' +
                      '<p style="color:#64748b;font-size:13px;margin-top:14px;">Simpan nomor ini untuk melacak status pengajuan Anda.</p>' +
                      '</div>',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check mr-2"></i>Selesai',
                cancelButtonText: '<i class="fas fa-plus mr-2"></i>Tambah Pengajuan Lagi',
                reverseButtons: true,
                backdrop: false,
                customClass: {
                    popup: 'swal-dd-modal',
                    confirmButton: 'swal-dd-btn swal-dd-btn-success',
                    cancelButton: 'swal-dd-btn swal-dd-btn-primary'
                }
            }).then(function (r) {
                if (r.isConfirmed && typeof onSelesai === 'function') onSelesai(noReg);
                else if (r.dismiss === window.Swal.DismissReason.cancel && typeof onTambah === 'function') onTambah(noReg);
            });
        }, 800);
    }

    function notifPengajuanDitolak(alasan) {
        var al = escapeHtml(alasan || 'Data tidak memenuhi syarat.');
        return window.Swal.fire({
            icon: 'warning',
            title: 'Pengajuan Ditolak',
            html: '<div style="text-align:left;color:#475569;font-size:13.5px;line-height:1.6;">' +
                  '<p style="margin:0 0 10px;">Pengajuan Anda tidak dapat diproses dengan alasan berikut:</p>' +
                  '<div style="background:#fff7ed;border:1px solid #ea580c;border-radius:10px;padding:10px 12px;color:#9a3412;">' + al + '</div>' +
                  '</div>',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-edit mr-2"></i>Edit Data',
            cancelButtonText: '<i class="fas fa-headset mr-2"></i>Hubungi Admin',
            reverseButtons: true,
            backdrop: false,
            customClass: {
                popup: 'swal-dd-modal',
                confirmButton: 'swal-dd-btn swal-dd-btn-warning',
                cancelButton: 'swal-dd-btn swal-dd-btn-primary'
            }
        });
    }

    // =====================================================================
    // 8. FUNGSI PUBLIK — HAPUS DATA
    // =====================================================================

    function notifKonfirmasiHapus(namaPenduduk, onHapus) {
        var nm = escapeHtml(namaPenduduk || 'data ini');
        return window.Swal.fire({
            title: 'Konfirmasi Hapus Data',
            html: '<p style="color:#475569;font-size:14px;margin:6px 0 0;">Data <strong>' + nm + '</strong> akan dihapus dan tidak dapat dikembalikan. Apakah Anda yakin ingin melanjutkan?</p>',
            showCancelButton: true,
            confirmButtonText: 'Konfirmasi',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            allowOutsideClick: false,
            backdrop: false,
            customClass: {
                popup: 'swal-dd-modal',
                confirmButton: 'swal-dd-btn swal-dd-btn-danger',
                cancelButton: 'swal-dd-btn swal-dd-btn-cancel'
            }
        }).then(function (r) {
            if (r.isConfirmed && typeof onHapus === 'function') onHapus();
        });
    }

    // =====================================================================
    // 9. FUNGSI PUBLIK — ANTRIAN & PENCARIAN
    // =====================================================================

    function notifNomorAntrian(nomorAntrian, jenisLayanan, estimasiWaktu) {
        var no = escapeHtml(nomorAntrian || '-');
        var jl = escapeHtml(jenisLayanan || '-');
        var et = escapeHtml(estimasiWaktu || '-');
        return fireToast({
            type: 'info',
            title: 'Nomor Antrian Anda',
            html: '<div style="display:flex;align-items:center;gap:14px;padding:4px 0;">' +
                  '<div style="font-size:30px;font-weight:800;line-height:1;color:#ffffff;">' + no + '</div>' +
                  '<div style="font-size:12px;color:rgba(255,255,255,0.95);line-height:1.45;">' +
                  '<strong style="color:#ffffff;">' + jl + '</strong><br>' +
                  '<i class="far fa-clock mr-1"></i>Estimasi: ' + et +
                  '</div></div>',
            timer: 8000
        });
    }

    function notifCariBerhasil(jumlahHasil, keyword) {
        var jml = parseInt(jumlahHasil, 10) || 0;
        var kw = escapeHtml(keyword || '');
        return fireToast({
            type: 'success',
            title: jml + ' data ditemukan',
            html: 'Hasil pencarian untuk "<strong>' + kw + '</strong>".',
            timer: 5000
        });
    }

    function notifCariKosong(keyword) {
        var kw = escapeHtml(keyword || '');
        return fireToast({
            type: 'warning',
            title: 'Data tidak ditemukan',
            html: 'Tidak ada data untuk "<strong>' + kw + '</strong>".',
            timer: 5000
        });
    }

    // =====================================================================
    // 10. FUNGSI PUBLIK — KONFIRMASI & DISETUJUI
    // =====================================================================

    function notifKonfirmasi(pesan, onSetuju, onBatal) {
        return window.Swal.fire({
            title: 'Konfirmasi',
            html: '<p style="color:#475569;font-size:14px;margin:6px 0 0;">' + escapeHtml(pesan || 'Apakah Anda yakin?') + '</p>',
            showCancelButton: true,
            confirmButtonText: 'Konfirmasi',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            allowOutsideClick: false,
            backdrop: false,
            customClass: {
                popup: 'swal-dd-modal',
                confirmButton: 'swal-dd-btn swal-dd-btn-success',
                cancelButton: 'swal-dd-btn swal-dd-btn-cancel'
            }
        }).then(function (r) {
            if (r.isConfirmed) { if (typeof onSetuju === 'function') onSetuju(); }
            else { if (typeof onBatal === 'function') onBatal(); }
        });
    }

    function notifDisetujui(namaPemohon) {
        var nm = escapeHtml(namaPemohon || 'Pemohon');
        return fireToast({
            type: 'success',
            title: 'Pengajuan Disetujui',
            html: 'Pengajuan <strong>' + nm + '</strong> telah disetujui.',
            timer: 5000
        });
    }

    // =====================================================================
    // 11. SHORTCUT — TIDAK menampilkan teks literal "success"/"error"
    // =====================================================================

    function showSuccess(message) {
        return fireToast({ type: 'success', title: message || 'Berhasil', timer: 5000 });
    }
    function showError(message, problem, solution) {
        return fireToast({
            type: 'error',
            problem: problem || message || 'Terjadi kesalahan.',
            solution: solution || defaultErrorSolution(message || problem || ''),
            timer: 6000
        });
    }
    function showInfo(message) {
        return fireToast({ type: 'info', title: message || 'Informasi', timer: 5000 });
    }
    function showWarning(message) {
        return fireToast({ type: 'warning', title: message || 'Perhatian', timer: 5000 });
    }

    // =====================================================================
    // 12. PUBLIK API
    // =====================================================================

    var api = {
        notifSimpanBerhasil: notifSimpanBerhasil,
        notifSimpanGagal: notifSimpanGagal,
        notifValidasiGagal: notifValidasiGagal,
        notifFormBelumLengkap: notifFormBelumLengkap,
        notifUploadProses: notifUploadProses,
        notifPengajuanProses: notifPengajuanProses,
        notifPengajuanDitolak: notifPengajuanDitolak,
        notifKonfirmasiHapus: notifKonfirmasiHapus,
        notifNomorAntrian: notifNomorAntrian,
        notifCariBerhasil: notifCariBerhasil,
        notifCariKosong: notifCariKosong,
        notifKonfirmasi: notifKonfirmasi,
        notifDisetujui: notifDisetujui,
        showSuccess: showSuccess,
        showError: showError,
        showInfo: showInfo,
        showWarning: showWarning,
        fireToast: fireToast,
        fireModal: fireModal,
        generateNoReg: generateNoReg
    };

    // Expose ke window
    Object.keys(api).forEach(function (k) { window[k] = api[k]; });
    window.Notifikasi = window.Notifikasi || {};
    window.Notifikasi.berhasil = showSuccess;
    window.Notifikasi.gagal    = showError;
    window.Notifikasi.info     = showInfo;
    window.Notifikasi.perhatian= showWarning;
    window.Notifikasi.konfirmasi = notifKonfirmasi;
    window.Notifikasi.simpanBerhasil = notifSimpanBerhasil;
    window.Notifikasi.simpanGagal    = notifSimpanGagal;
    window.Notifikasi.validasiGagal  = notifValidasiGagal;
    window.Notifikasi.formBelumLengkap = notifFormBelumLengkap;
    window.Notifikasi.uploadProses   = notifUploadProses;
    window.Notifikasi.pengajuanProses = notifPengajuanProses;
    window.Notifikasi.pengajuanDitolak = notifPengajuanDitolak;
    window.Notifikasi.konfirmasiHapus = notifKonfirmasiHapus;
    window.Notifikasi.nomorAntrian  = notifNomorAntrian;
    window.Notifikasi.cariBerhasil  = notifCariBerhasil;
    window.Notifikasi.cariKosong    = notifCariKosong;
    window.Notifikasi.disetujui     = notifDisetujui;

    // =====================================================================
    // 13. GLOBAL Swal.fire INTERCEPTOR
    //     Menjamin SEMUA panggilan Swal.fire({toast:true}) di project
    //     otomatis memakai desain top-end HIJAU/MERAH ini.
    // =====================================================================
    if (window.Swal && !window.Swal.__ddIntercepted) {
        var __ddOrigFire = window.Swal.fire.bind(window.Swal);
        var __ddOrigMixin = window.Swal.mixin ? window.Swal.mixin.bind(window.Swal) : null;

        function decorateToastOpts(opt) {
            if (!opt || typeof opt !== 'object' || opt.toast !== true) return opt;

            // Tipe berdasar icon (default 'info')
            var type = (opt.icon === 'success' || opt.icon === 'error' ||
                        opt.icon === 'warning' || opt.icon === 'info') ? opt.icon : 'info';

            // Untuk error: bungkus jadi {Masalah + Cara memperbaiki}
            if (type === 'error' && (!opt.html || String(opt.html).indexOf('swal-dd-error-detail') === -1)) {
                var problem = opt.problem || opt.text || htmlToText(opt.html) || 'Terjadi kesalahan.';
                var solution = opt.solution || defaultErrorSolution(problem);
                opt.html = buildErrorHtml(problem, solution);
                delete opt.text;
            }

            // Pastikan icon & class pakai style baru
            opt.icon = type;
            opt.iconColor = '#ffffff';
            if (!opt.customClass) opt.customClass = {};
            if (typeof opt.customClass === 'string') {
                opt.customClass = (opt.customClass.indexOf('swal-dd-toast') === -1)
                    ? (opt.customClass + ' swal-dd-toast swal-dd-' + type)
                    : opt.customClass;
            } else {
                var existing = opt.customClass.popup || '';
                opt.customClass.popup = (existing.indexOf('swal-dd-toast') === -1)
                    ? (existing ? existing + ' ' : '') + 'swal-dd-toast swal-dd-' + type
                    : existing;
            }

            // Position & opsi standar
            if (typeof opt.position === 'undefined') opt.position = 'top-end';
            if (typeof opt.showConfirmButton === 'undefined') opt.showConfirmButton = false;
            if (typeof opt.showCloseButton === 'undefined') opt.showCloseButton = true;
            if (typeof opt.timerProgressBar === 'undefined') opt.timerProgressBar = true;
            opt.backdrop = false;
            if (typeof opt.timer !== 'number') opt.timer = 5000;

            // Pause-on-hover
            if (!opt.__ddPauseInjected) {
                var prev = opt.didOpen;
                opt.didOpen = function (toast) {
                    if (window.Swal && toast && toast.addEventListener) {
                        toast.addEventListener('mouseenter', window.Swal.stopTimer);
                        toast.addEventListener('mouseleave', window.Swal.resumeTimer);
                    }
                    if (typeof prev === 'function') try { prev(toast); } catch (e) {}
                };
                opt.__ddPauseInjected = true;
            }
            return opt;
        }

        window.Swal.fire = function () {
            try {
                if (arguments[0] && typeof arguments[0] === 'object') {
                    arguments[0] = decorateToastOpts(arguments[0]);
                }
            } catch (e) { /* fail-safe */ }
            return __ddOrigFire.apply(window.Swal, arguments);
        };

        if (__ddOrigMixin) {
            window.Swal.mixin = function (mixinOpts) {
                try { mixinOpts = decorateToastOpts(mixinOpts || {}); } catch (e) {}
                var instance = __ddOrigMixin(mixinOpts);
                if (instance && typeof instance.fire === 'function' && !instance.__ddPatched) {
                    var __origInstFire = instance.fire.bind(instance);
                    instance.fire = function () {
                        try {
                            if (arguments[0] && typeof arguments[0] === 'object') {
                                if (mixinOpts && mixinOpts.toast === true && typeof arguments[0].toast === 'undefined') {
                                    arguments[0].toast = true;
                                }
                                arguments[0] = decorateToastOpts(arguments[0]);
                            }
                        } catch (e) {}
                        return __origInstFire.apply(instance, arguments);
                    };
                    instance.__ddPatched = true;
                }
                return instance;
            };
        }

        window.Swal.__ddIntercepted = true;
    }

    // =====================================================================
    // 14. FORM-LEVEL AUTO HANDLER — tangkap required-field invalid
    // =====================================================================
    var lastRequiredAt = 0;
    document.addEventListener('invalid', function (event) {
        var el = event.target;
        if (!el || !el.matches || !el.matches('input, select, textarea')) return;
        event.preventDefault();
        if (el.setCustomValidity) el.setCustomValidity('');
        var now = Date.now();
        if (now - lastRequiredAt > 800) {
            lastRequiredAt = now;
            notifFormBelumLengkap();
        }
        try { el.focus({ preventScroll: false }); } catch (e) { el.focus(); }
    }, true);

})(window, document);
