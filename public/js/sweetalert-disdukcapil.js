/**
 * =====================================================================
 * SWEETALERT2 NOTIFIKASI DISDUKCAPIL — DESAIN BARU (TOP-END GRADIENT)
 * =====================================================================
 * Sistem notifikasi terpusat untuk Sistem Informasi Disdukcapil Kab. Toba.
 *
 * Desain:
 *   - Toast  : position top-end, timerProgressBar, pause-on-hover,
 *              kartu putih tanpa backdrop/blur abu-abu
 *   - Modal  : background putih, teks netral, tombol mengikuti style guide.
 *
 * Warna tema mengikuti CSS variable di public/css/style-guide.css.
 *
 * Dependensi: SweetAlert2 v11.x
 *
 * Catatan kompatibilitas:
 *   - File ini juga mem-patch `window.SwalHelper.success/error/warning/info`,
 *     `window.notifToast`, `window.showToast`, `window.DToast`, dan
 *     `window.Notifikasi` agar tampilan toast lama otomatis ikut desain baru.
 * =====================================================================
 */
(function (window, document) {
    'use strict';

    if (!window.Swal) {
        console.warn('[SwalDisdukcapil] SweetAlert2 belum dimuat. Pastikan <script src="sweetalert2@11"> ada sebelum file ini.');
    }

    // =====================================================================
    // STYLE INJECTION
    // =====================================================================
    var STYLE_ID = 'swal-disdukcapil-styles';
    if (!document.getElementById(STYLE_ID)) {
        // Selektor menggunakan .swal2-popup.swal2-toast.swal-dd-toast (specificity 0,3,0)
        // agar mengalahkan rule global di admin/partials/sweetalert-styles.blade.php.
        var T = '.swal2-popup.swal2-toast.swal-dd-toast';
        var css = '' +
            '.swal2-container.swal2-backdrop-show.swal2-toast,.swal2-container.swal2-backdrop-show.swal2-top,.swal2-container.swal2-backdrop-show.swal2-top-start,.swal2-container.swal2-backdrop-show.swal2-top-end,.swal2-container.swal2-backdrop-show.swal2-top-left,.swal2-container.swal2-backdrop-show.swal2-top-right,.swal2-container.swal2-noanimation.swal2-toast,.swal2-container.swal2-noanimation.swal2-top,.swal2-container.swal2-noanimation.swal2-top-start,.swal2-container.swal2-noanimation.swal2-top-end,.swal2-container.swal2-noanimation.swal2-top-left,.swal2-container.swal2-noanimation.swal2-top-right,.swal2-container.swal2-toast,.swal2-container.swal2-top,.swal2-container.swal2-top-start,.swal2-container.swal2-top-end,.swal2-container.swal2-top-left,.swal2-container.swal2-top-right{background:transparent!important;background-color:transparent!important;background-image:none!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important;filter:none!important;pointer-events:none!important;}' +
            '.swal2-container.swal2-toast .swal2-backdrop,.swal2-container.swal2-top .swal2-backdrop,.swal2-container.swal2-top-end .swal2-backdrop,.swal2-container.swal2-top-right .swal2-backdrop{display:none!important;background:transparent!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important;}' +
            T + '{font-family:"Plus Jakarta Sans",sans-serif!important;color:var(--neutral-900)!important;background:var(--neutral-white)!important;background-color:var(--neutral-white)!important;background-image:none!important;border-radius:var(--radius-lg)!important;box-shadow:var(--shadow-xl)!important;padding:14px 18px!important;min-width:320px!important;max-width:420px!important;border:0!important;border-left:4px solid var(--info-blue)!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important;filter:none!important;display:grid!important;grid-template-columns:44px minmax(0,1fr) auto!important;column-gap:14px!important;align-items:center!important;}' +
            T + '.swal-dd-success{border-left-color:var(--success-green)!important;}' +
            T + '.swal-dd-error{border-left-color:var(--danger-red)!important;}' +
            T + '.swal-dd-warning{border-left-color:var(--warning-orange)!important;}' +
            T + '.swal-dd-info{border-left-color:var(--info-blue)!important;}' +
            T + ' .swal2-title{font-family:"Plus Jakarta Sans",sans-serif!important;color:var(--neutral-900)!important;font-size:var(--font-size-sm)!important;font-weight:500!important;line-height:1.4!important;margin:0!important;padding:0!important;text-align:left!important;grid-column:2!important;min-width:0!important;}' +
            T + ' .swal2-html-container{font-family:"Plus Jakarta Sans",sans-serif!important;color:var(--neutral-600)!important;font-size:var(--font-size-xs)!important;font-weight:400!important;line-height:1.5!important;margin:4px 0 0 0!important;padding:0!important;text-align:left!important;display:block!important;grid-column:2!important;min-width:0!important;}' +
            T + ' .swal2-icon{grid-column:1!important;grid-row:1 / span 2!important;align-self:center!important;justify-self:center!important;position:relative!important;margin:0!important;width:38px!important;height:38px!important;min-width:38px!important;flex:0 0 38px!important;border-width:2px!important;border-style:solid!important;border-radius:999px!important;box-sizing:border-box!important;display:flex!important;align-items:center!important;justify-content:center!important;line-height:1!important;overflow:hidden!important;}' +
            T + '.swal-dd-success .swal2-icon{background:#ecfdf5!important;border-color:#bbf7d0!important;color:var(--success-green)!important;}' +
            T + '.swal-dd-error .swal2-icon,' + T + '.swal-dd-danger .swal2-icon{background:#fef2f2!important;border-color:#fecaca!important;color:var(--danger-red)!important;}' +
            T + '.swal-dd-warning .swal2-icon{background:#fffbeb!important;border-color:#fde68a!important;color:var(--warning-orange)!important;}' +
            T + '.swal-dd-info .swal2-icon{background:#eff6ff!important;border-color:#bfdbfe!important;color:var(--info-blue)!important;}' +
            T + ' .swal2-icon .swal2-icon-content,' + T + ' .swal2-icon .swal2-success-ring,' + T + ' .swal2-icon .swal2-success-fix,' + T + ' .swal2-icon .swal2-success-circular-line-left,' + T + ' .swal2-icon .swal2-success-circular-line-right,' + T + ' .swal2-icon .swal2-success-line-tip,' + T + ' .swal2-icon .swal2-success-line-long,' + T + ' .swal2-icon .swal2-x-mark,' + T + ' .swal2-icon .swal2-x-mark-line-left,' + T + ' .swal2-icon .swal2-x-mark-line-right{display:none!important;}' +
            T + ' .swal2-icon::before{position:absolute!important;inset:0!important;display:flex!important;align-items:center!important;justify-content:center!important;width:100%!important;height:100%!important;font-size:21px!important;font-weight:800!important;line-height:1!important;text-align:center!important;transform:translateY(-1px)!important;color:currentColor!important;}' +
            T + ' .swal2-icon::after{display:none!important;}' +
            T + '.swal-dd-success .swal2-icon::before{content:"\\2713"!important;}' +
            T + '.swal-dd-error .swal2-icon::before,' + T + '.swal-dd-danger .swal2-icon::before{content:"\\00d7"!important;}' +
            T + '.swal-dd-warning .swal2-icon::before{content:"!"!important;}' +
            T + '.swal-dd-info .swal2-icon::before{content:"i"!important;font-family:Georgia,serif!important;font-style:italic!important;}' +
            T + ' .swal2-success-circular-line-left,' + T + ' .swal2-success-circular-line-right,' + T + ' .swal2-success-fix{display:none!important;background:transparent!important;}' +
            T + ' .swal2-success-ring{border-color:transparent!important;}' +
            T + ' .swal2-success-line-tip,' + T + ' .swal2-success-line-long{display:none!important;}' +
            T + ' .swal2-x-mark{width:20px!important;height:20px!important;}' +
            T + ' .swal2-x-mark-line-left,' + T + ' .swal2-x-mark-line-right{display:none!important;}' +
            T + ' .swal2-timer-progress-bar-container{display:block!important;height:3px!important;}' +
            T + ' .swal2-timer-progress-bar{background:var(--primary-blue-main)!important;height:3px!important;}' +
            T + ' .swal2-close{grid-column:3!important;grid-row:1 / span 2!important;align-self:start!important;color:var(--neutral-500)!important;font-size:22px!important;width:24px!important;height:24px!important;margin:-4px -6px 0 4px!important;}' +
            T + ' .swal2-close:hover{color:var(--neutral-900)!important;}' +
            T + ' .swal-dd-list{margin:6px 0 0 18px;padding:0;color:var(--neutral-700);font-size:12.5px;line-height:1.5;}' +
            T + ' .swal-dd-list li{margin:2px 0;color:var(--neutral-700);}' +
            T + ' .swal-dd-antrian{display:flex;align-items:center;gap:14px;padding:4px 0;}' +
            T + ' .swal-dd-antrian-no{font-size:30px;font-weight:800;letter-spacing:0;line-height:1;color:var(--primary-blue-main);}' +
            T + ' .swal-dd-antrian-meta{font-size:12px;color:var(--neutral-600);line-height:1.45;}' +
            T + ' .swal-dd-antrian-meta strong{color:var(--neutral-900);font-weight:700;}' +
            T + ' .swal-dd-action{margin-top:8px;background:var(--primary-blue-main);color:var(--neutral-white);border:1px solid var(--primary-blue-main);padding:6px 14px;border-radius:var(--radius-md);font-size:12px;font-weight:600;cursor:pointer;transition:background var(--transition-base);}' +
            T + ' .swal-dd-action:hover{background:var(--primary-blue-dark);}' +
            T + ' .swal-dd-pill{display:inline-block;padding:2px 10px;border-radius:999px;background:var(--success-green-light);color:var(--secondary-green);font-size:11px;font-weight:700;letter-spacing:0;margin-left:6px;vertical-align:middle;}' +
            '.swal-dd-modal{font-family:"Plus Jakarta Sans",sans-serif!important;background:var(--neutral-white)!important;color:var(--neutral-900)!important;border-radius:var(--radius-lg)!important;padding:28px!important;box-shadow:var(--shadow-xl)!important;}' +
            '.swal-dd-modal .swal2-title{color:var(--neutral-900)!important;font-weight:700!important;font-size:var(--font-size-xl)!important;}' +
            '.swal-dd-modal .swal2-html-container{color:var(--neutral-600)!important;font-size:var(--font-size-sm)!important;line-height:1.6!important;}' +
            '.swal-dd-btn{padding:0.75rem 1.5rem!important;border-radius:var(--radius-md)!important;font-weight:600!important;font-size:var(--font-size-sm)!important;border:none!important;box-shadow:none!important;}' +
            '.swal-dd-btn-primary{background:var(--primary-blue-main)!important;color:var(--neutral-white)!important;}' +
            '.swal-dd-btn-success{background-color:#16a34a!important;background-image:linear-gradient(to right,#16a34a,#15803d)!important;color:var(--neutral-white)!important;border-color:#16a34a!important;border-radius:.75rem!important;font-weight:700!important;box-shadow:0 10px 15px -3px rgba(22,163,74,.22),0 4px 6px -4px rgba(22,163,74,.22)!important;}' +
            '.swal-dd-btn-success:hover{background-color:#15803d!important;background-image:linear-gradient(to right,#15803d,#166534)!important;border-color:#15803d!important;box-shadow:0 12px 18px -5px rgba(22,101,52,.28),0 6px 8px -6px rgba(22,101,52,.28)!important;}' +
            '.swal-dd-btn-danger{background:var(--danger-red)!important;color:var(--neutral-white)!important;}' +
            '.swal-dd-btn-warning{background:var(--warning-orange)!important;color:var(--neutral-white)!important;}' +
            '.swal-dd-btn-cancel{background:var(--neutral-200)!important;color:var(--neutral-900)!important;}' +
            '.swal-dd-btn-cancel:hover{background:var(--neutral-300)!important;}' +
            '.swal-dd-modal .swal2-actions{gap:8px!important;margin-top:18px!important;}' +
            '.swal-dd-pillbar{display:inline-flex;align-items:center;gap:6px;background:var(--primary-blue-100);border:1px solid var(--primary-blue-100);color:var(--primary-blue-dark);padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600;}' +
            '@keyframes swal-dd-slidein{from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);}}' +
            '@keyframes swal-dd-slideout{from{opacity:1;transform:translateX(0);}to{opacity:0;transform:translateX(110%);}}' +
            T + '.swal2-show{animation:swal-dd-slidein .3s ease-in-out!important;}' +
            T + '.swal2-hide{animation:swal-dd-slideout .3s ease-in-out!important;}';
        var styleEl = document.createElement('style');
        styleEl.id = STYLE_ID;
        styleEl.appendChild(document.createTextNode(css));
        (document.head || document.documentElement).appendChild(styleEl);
    }

    // =====================================================================
    // HELPERS
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

    var EMPTY_REQUIRED_MESSAGE = 'Ada kolom yang wajib diisi';
    var EMPTY_REQUIRED_PATTERN = /(wajib\s+diisi|tidak\s+boleh\s+kosong|isian\s+yang\s+kosong|field[^.]{0,40}wajib|harus\s+dipilih|harap\s+lengkapi|mohon\s+lengkapi|lengkapi\s+(semua|seluruh)\s+data|form\s+belum\s+lengkap|\brequired\b)/i;

    function stripTags(s) {
        return String(s || '').replace(/<[^>]*>/g, ' ');
    }

    function normalizeEmptyRequiredToast(opts) {
        if (!opts || typeof opts !== 'object') return opts;
        var type = opts.type || opts.icon || '';
        if (type !== 'error' && type !== 'warning') return opts;

        var combined = [
            opts.title,
            opts.text,
            stripTags(opts.html)
        ].filter(Boolean).join(' ');

        if (!EMPTY_REQUIRED_PATTERN.test(combined)) return opts;

        opts.title = EMPTY_REQUIRED_MESSAGE;
        delete opts.text;
        delete opts.html;
        opts.icon = 'error';
        opts.type = 'error';
        return opts;
    }

    function generateNoReg() {
        var year = new Date().getFullYear();
        var rand = Math.floor(Math.random() * 900000) + 100000; // 6 digit
        return '#REG-' + year + '-' + rand;
    }

    /**
     * Toast generik bertema. Tidak dipublikasikan langsung; dipakai oleh
     * fungsi notif* di bawah.
     */
    function toastIconColor(type) {
        if (type === 'success') return 'var(--success-green)';
        if (type === 'error') return 'var(--danger-red)';
        if (type === 'warning') return 'var(--warning-orange)';
        return 'var(--info-blue)';
    }

    function isPositiveActionText(text) {
        return /\b(login|masuk|lanjut|lanjutkan|selanjutnya|next|konfirmasi|setuju|setujui|ya,\s*(lanjutkan|mulai|setujui|konfirmasi|verifikasi|terima)|tambah|verifikasi|approve|terima|mulai)\b/i.test(String(text || '')) &&
            !/\b(hapus|delete|tolak|batalkan|keluar|logout)\b/i.test(String(text || ''));
    }

    function fireToast(opts) {
        opts = normalizeEmptyRequiredToast(opts || {});
        var type = opts.type || 'info';
        var timer = typeof opts.timer === 'number' ? opts.timer : 4000;
        var cfg = {
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            showCloseButton: opts.showCloseButton !== false,
            timer: timer,
            timerProgressBar: true,
            backdrop: false,
            iconColor: toastIconColor(type),
            customClass: {
                popup: 'swal-dd-toast swal-dd-' + type
            },
            didOpen: function (toastEl) {
                toastEl.addEventListener('mouseenter', window.Swal.stopTimer);
                toastEl.addEventListener('mouseleave', window.Swal.resumeTimer);
                if (typeof opts.didOpen === 'function') opts.didOpen(toastEl);
            }
        };

        if (opts.title) cfg.title = opts.title;
        if (opts.html) cfg.html = opts.html;
        if (opts.icon) cfg.icon = opts.icon;

        return window.Swal.fire(cfg);
    }

    function fireModal(opts) {
        var base = {
            allowOutsideClick: opts.allowOutsideClick === true,
            allowEscapeKey: opts.allowEscapeKey === true,
            showCloseButton: opts.showCloseButton === true,
            customClass: Object.assign({
                popup: 'swal-dd-modal',
                confirmButton: 'swal-dd-btn swal-dd-btn-primary',
                cancelButton: 'swal-dd-btn swal-dd-btn-cancel',
                denyButton: 'swal-dd-btn swal-dd-btn-cancel'
            }, opts.customClass || {})
        };
        delete opts.customClass;
        return window.Swal.fire(Object.assign(base, opts));
    }

    // =====================================================================
    // 1. SIMPAN / UPDATE DATA
    // =====================================================================

    /**
     * Toast sukses simpan data dengan nomor registrasi dinamis.
     * @param {string} nomorRegistrasi Contoh: '#REG-2024-001234'
     * @example
     *   <button onclick="notifSimpanBerhasil('#REG-2024-001234')">Simpan</button>
     */
    function notifSimpanBerhasil(nomorRegistrasi) {
        var no = escapeHtml(nomorRegistrasi || generateNoReg());
        return fireToast({
            type: 'success',
            icon: 'success',
            title: 'Data berhasil disimpan',
            html: 'No. Registrasi: <strong>' + no + '</strong>',
            timer: 4000
        });
    }

    /**
     * Toast error saat penyimpanan gagal.
     * @param {string} pesanError Pesan error dari server.
     * @example
     *   notifSimpanGagal('NIK sudah terdaftar dalam sistem');
     */
    function notifSimpanGagal(pesanError) {
        return fireToast({
            type: 'error',
            icon: 'error',
            title: 'Gagal menyimpan data',
            html: escapeHtml(pesanError || 'Terjadi kesalahan saat menyimpan data.'),
            timer: 4000
        });
    }

    // =====================================================================
    // 2. VALIDASI FORM
    // =====================================================================

    /**
     * Toast validasi gagal — menampilkan daftar field bermasalah.
     * @param {string[]} arrayKesalahan Contoh: ['NIK wajib diisi', 'Format tanggal tidak valid']
     * @example
     *   notifValidasiGagal(['NIK wajib diisi (16 digit)', 'Format tanggal tidak valid']);
     */
    function notifValidasiGagal(arrayKesalahan) {
        var errs = Array.isArray(arrayKesalahan) ? arrayKesalahan : [String(arrayKesalahan || 'Data tidak valid')];
        var lis = errs.map(function (e) { return '<li>' + escapeHtml(e) + '</li>'; }).join('');
        return fireToast({
            type: 'error',
            icon: 'error',
            title: 'Validasi gagal',
            html: '<ul class="swal-dd-list">' + lis + '</ul>',
            timer: 5000
        });
    }

    /**
     * Toast warning saat form belum lengkap, dengan tombol aksi "Lengkapi Data".
     * @example
     *   notifFormBelumLengkap();
     */
    function notifFormBelumLengkap() {
        return fireToast({
            type: 'error',
            icon: 'error',
            title: EMPTY_REQUIRED_MESSAGE,
            timer: 4000,
            showCloseButton: true
        });
    }

    // =====================================================================
    // 3. UPLOAD DOKUMEN
    // =====================================================================

    /**
     * Modal loading saat upload, otomatis menampilkan toast/modal sesuai hasil.
     * @param {string} namaFile Nama file yang diunggah.
     * @param {Function} onSuccess Promise/fungsi async yang dijalankan untuk upload.
     *                             Harus return Promise yang resolve jika berhasil.
     * @param {Function} onError Callback "Coba Lagi" saat upload gagal.
     * @example
     *   notifUploadProses('KTP.pdf',
     *       () => fetch('/api/upload', {method:'POST', body: fd}),
     *       () => coba_upload_lagi()
     *   );
     */
    function notifUploadProses(namaFile, onSuccess, onError) {
        var nf = escapeHtml(namaFile || 'file');

        window.Swal.fire({
            title: 'Mengunggah ' + nf + '...',
            html: '<div style="display:flex;justify-content:center;margin-top:8px;">' +
                  '<i class="fas fa-cloud-upload-alt" style="font-size:42px;color:var(--primary-blue-main);"></i>' +
                  '</div>' +
                  '<p style="margin-top:14px;color:var(--neutral-500);font-size:13.5px;">Mohon tunggu, file sedang diunggah ke server.</p>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
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
                icon: 'success',
                title: 'Berhasil diunggah',
                html: '<strong>' + nf + '</strong> berhasil disimpan.',
                timer: 3500
            });
            return res;
        }).catch(function (err) {
            return window.Swal.fire({
                icon: 'error',
                title: 'File gagal diunggah',
                html: '<div style="text-align:left;color:var(--neutral-600);font-size:13.5px;line-height:1.6;">' +
                      '<p style="margin:0 0 10px;">File <strong>' + nf + '</strong> tidak berhasil diunggah.</p>' +
                      '<div style="background:var(--danger-red-light);border:1px solid var(--danger-red);border-radius:10px;padding:10px 12px;margin-bottom:10px;">' +
                      '<p style="margin:0 0 4px;font-weight:600;color:var(--danger-red);font-size:12.5px;">Saran:</p>' +
                      '<ul style="margin:0;padding-left:18px;color:var(--danger-red);font-size:12.5px;">' +
                      '<li>Pastikan format file: PDF, JPG, atau PNG</li>' +
                      '<li>Ukuran maksimum 5MB</li>' +
                      '<li>Periksa kembali koneksi jaringan Anda</li>' +
                      '</ul></div>' +
                      (err && err.message ? '<p style="margin:0;color:var(--neutral-400);font-size:12px;">Detail: ' + escapeHtml(err.message) + '</p>' : '') +
                      '</div>',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-redo mr-2"></i>Coba Lagi',
                cancelButtonText: 'Tutup',
                reverseButtons: true,
                allowOutsideClick: false,
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
    // 4. PROSES PENGAJUAN
    // =====================================================================

    /**
     * Modal loading proses pengajuan dokumen, otomatis menampilkan modal
     * sukses dengan nomor registrasi setelah selesai.
     * @param {string} jenisDokumen Contoh: 'Akta Kelahiran'
     * @param {Function} onSelesai Callback tombol "Selesai".
     * @param {Function} onTambah Callback tombol "Tambah Pengajuan Lagi".
     * @example
     *   notifPengajuanProses('Akta Kelahiran',
     *      () => window.location='/dashboard',
     *      () => formReset()
     *   );
     */
    function notifPengajuanProses(jenisDokumen, onSelesai, onTambah) {
        var jd = escapeHtml(jenisDokumen || 'dokumen');

        window.Swal.fire({
            title: 'Memproses pengajuan ' + jd + '...',
            html: '<p style="color:var(--neutral-500);font-size:13.5px;margin-top:10px;">Mohon tunggu, sistem sedang memvalidasi data.</p>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            customClass: { popup: 'swal-dd-modal' },
            didOpen: function () { window.Swal.showLoading(); }
        });

        setTimeout(function () {
            var noReg = generateNoReg();
            window.Swal.fire({
                icon: 'success',
                title: 'Pengajuan Berhasil',
                html: '<div style="text-align:center;">' +
                      '<p style="color:var(--neutral-600);font-size:14px;margin:6px 0 12px;">Pengajuan <strong>' + jd + '</strong> telah diterima.</p>' +
                      '<div style="background:var(--success-green-light);border:1px solid var(--success-green);border-radius:12px;padding:14px 16px;display:inline-block;">' +
                      '<p style="margin:0;color:var(--secondary-green);font-size:12px;font-weight:600;letter-spacing:0;">NOMOR REGISTRASI</p>' +
                      '<p style="margin:6px 0 0;color:var(--secondary-green);font-size:22px;font-weight:800;letter-spacing:0;">' + noReg + '</p>' +
                      '</div>' +
                      '<p style="color:var(--neutral-500);font-size:12.5px;margin-top:14px;">Simpan nomor ini untuk melacak status pengajuan Anda.</p>' +
                      '</div>',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check mr-2"></i>Selesai',
                cancelButtonText: '<i class="fas fa-plus mr-2"></i>Tambah Pengajuan Lagi',
                reverseButtons: true,
                allowOutsideClick: false,
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

    /**
     * Modal warning saat pengajuan ditolak.
     * @param {string} alasan Alasan penolakan dinamis dari sistem.
     * @example
     *   notifPengajuanDitolak('Foto KTP tidak terbaca dengan jelas.');
     */
    function notifPengajuanDitolak(alasan) {
        var al = escapeHtml(alasan || 'Data tidak memenuhi syarat.');
        return window.Swal.fire({
            icon: 'warning',
            title: 'Pengajuan Ditolak',
            html: '<div style="text-align:left;color:var(--neutral-600);font-size:13.5px;line-height:1.6;">' +
                  '<p style="margin:0 0 10px;">Pengajuan Anda tidak dapat diproses dengan alasan berikut:</p>' +
                  '<div style="background:var(--warning-orange-light);border:1px solid var(--warning-orange);border-radius:10px;padding:10px 12px;color:var(--warning-orange);">' + al + '</div>' +
                  '</div>',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-edit mr-2"></i>Edit Data',
            cancelButtonText: '<i class="fas fa-headset mr-2"></i>Hubungi Admin',
            reverseButtons: true,
            allowOutsideClick: false,
            customClass: {
                popup: 'swal-dd-modal',
                confirmButton: 'swal-dd-btn swal-dd-btn-warning',
                cancelButton: 'swal-dd-btn swal-dd-btn-primary'
            }
        });
    }

    // =====================================================================
    // 5. HAPUS DATA
    // =====================================================================

    /**
     * Modal konfirmasi hapus dengan nama penduduk dinamis.
     * @param {string} namaPenduduk
     * @param {Function} onHapus Dipanggil jika user menekan "Ya, Hapus".
     * @example
     *   notifKonfirmasiHapus('Budi Santoso', () => fetch('/hapus/1', {method:'DELETE'}));
     */
    function notifKonfirmasiHapus(namaPenduduk, onHapus) {
        var nm = escapeHtml(namaPenduduk || 'data ini');
        return window.Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi Hapus Data',
            html: '<div style="text-align:left;color:var(--neutral-600);font-size:13.5px;line-height:1.6;">' +
                  '<p style="margin:0 0 10px;">Anda akan menghapus data <strong>' + nm + '</strong>.</p>' +
                  '<div style="background:var(--danger-red-light);border:1px solid var(--danger-red);border-radius:10px;padding:10px 12px;color:var(--danger-red);font-size:12.5px;">' +
                  '<i class="fas fa-exclamation-triangle mr-1"></i> Tindakan ini <strong>tidak dapat dibatalkan</strong>.' +
                  '</div></div>',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash mr-2"></i>Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
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
    // 6. ANTRIAN & PENCARIAN
    // =====================================================================

    /**
     * Toast info biru besar untuk menampilkan nomor antrian.
     * @param {string} nomorAntrian   Contoh: 'A-015'
     * @param {string} jenisLayanan   Contoh: 'KTP Elektronik'
     * @param {string} estimasiWaktu  Contoh: '± 15 menit'
     * @example
     *   notifNomorAntrian('A-015', 'KTP Elektronik', '± 15 menit');
     */
    function notifNomorAntrian(nomorAntrian, jenisLayanan, estimasiWaktu) {
        var no = escapeHtml(nomorAntrian || '-');
        var jl = escapeHtml(jenisLayanan || '-');
        var et = escapeHtml(estimasiWaktu || '-');
        return fireToast({
            type: 'info',
            icon: 'info',
            title: 'Nomor Antrian Anda',
            html: '<div class="swal-dd-antrian">' +
                  '<div class="swal-dd-antrian-no">' + no + '</div>' +
                  '<div class="swal-dd-antrian-meta">' +
                  '<strong>' + jl + '</strong><br>' +
                  '<i class="far fa-clock mr-1"></i>Estimasi: ' + et +
                  '</div></div>',
            timer: 8000
        });
    }

    /**
     * Toast hasil pencarian ditemukan.
     * @param {number} jumlahHasil
     * @param {string} keyword
     */
    function notifCariBerhasil(jumlahHasil, keyword) {
        var jml = parseInt(jumlahHasil, 10) || 0;
        var kw = escapeHtml(keyword || '');
        return fireToast({
            type: 'success',
            icon: 'success',
            title: jml + ' data ditemukan',
            html: 'Hasil pencarian untuk "<strong>' + kw + '</strong>".',
            timer: 3000
        });
    }

    /**
     * Toast hasil pencarian kosong.
     * @param {string} keyword
     */
    function notifCariKosong(keyword) {
        var kw = escapeHtml(keyword || '');
        return fireToast({
            type: 'warning',
            icon: 'warning',
            title: 'Data tidak ditemukan',
            html: 'Tidak ada data untuk "<strong>' + kw + '</strong>".',
            timer: 3500
        });
    }

    // =====================================================================
    // 7. KONFIRMASI AKSI UMUM
    // =====================================================================

    /**
     * Modal konfirmasi umum.
     * @param {string} pesan
     * @param {Function} onSetuju
     * @param {Function} [onBatal]
     */
    function notifKonfirmasi(pesan, onSetuju, onBatal) {
        return window.Swal.fire({
            icon: 'question',
            title: 'Konfirmasi',
            html: '<p style="color:var(--neutral-600);font-size:14px;margin:6px 0 0;">' + escapeHtml(pesan || 'Apakah Anda yakin?') + '</p>',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check mr-2"></i>Ya, Lanjutkan',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
            reverseButtons: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
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

    /**
     * Toast pengajuan disetujui dengan badge "APPROVED".
     * @param {string} namaPemohon
     */
    function notifDisetujui(namaPemohon) {
        var nm = escapeHtml(namaPemohon || 'Pemohon');
        return fireToast({
            type: 'success',
            icon: 'success',
            title: 'Pengajuan Disetujui <span class="swal-dd-pill">APPROVED</span>',
            html: 'Pengajuan <strong>' + nm + '</strong> telah disetujui.',
            timer: 4000
        });
    }

    // =====================================================================
    // PUBLIC API
    // =====================================================================
    var api = {
        // 1. Simpan/Update
        notifSimpanBerhasil: notifSimpanBerhasil,
        notifSimpanGagal: notifSimpanGagal,
        // 2. Validasi
        notifValidasiGagal: notifValidasiGagal,
        notifFormBelumLengkap: notifFormBelumLengkap,
        // 3. Upload
        notifUploadProses: notifUploadProses,
        // 4. Pengajuan
        notifPengajuanProses: notifPengajuanProses,
        notifPengajuanDitolak: notifPengajuanDitolak,
        // 5. Hapus
        notifKonfirmasiHapus: notifKonfirmasiHapus,
        // 6. Antrian & pencarian
        notifNomorAntrian: notifNomorAntrian,
        notifCariBerhasil: notifCariBerhasil,
        notifCariKosong: notifCariKosong,
        // 7. Konfirmasi umum
        notifKonfirmasi: notifKonfirmasi,
        notifDisetujui: notifDisetujui,
        // Helpers
        fireToast: fireToast,
        generateNoReg: generateNoReg
    };

    // Expose semua fungsi sebagai global
    Object.keys(api).forEach(function (k) { window[k] = api[k]; });
    window.SwalDisdukcapil = api;

    // =====================================================================
    // KOMPATIBILITAS — pasang patch agar semua toast lama mengikuti desain baru
    // =====================================================================
    function patchLegacy() {
        // SwalHelper.success/error/warning/info + toastSuccess/Error/Warning/Info
        // + modalSuccess/Error/Warning/Info → toast & modal gradient baru
        if (window.SwalHelper) {
            var ts = function (m, d) { return fireToast({ type: 'success', icon: 'success', title: m || 'Berhasil', timer: d || 4000 }); };
            var te = function (m, d) { return fireToast({ type: 'error',   icon: 'error',   title: m || 'Terjadi kesalahan', timer: d || 4000 }); };
            var tw = function (m, d) { return fireToast({ type: 'warning', icon: 'warning', title: m || 'Perhatian', timer: d || 4000 }); };
            var ti = function (m, d) { return fireToast({ type: 'info',    icon: 'info',    title: m || 'Informasi', timer: d || 4000 }); };

            window.SwalHelper.success      = ts;
            window.SwalHelper.error        = te;
            window.SwalHelper.warning      = tw;
            window.SwalHelper.info         = ti;
            window.SwalHelper.toastSuccess = ts;
            window.SwalHelper.toastError   = te;
            window.SwalHelper.toastWarning = tw;
            window.SwalHelper.toastInfo    = ti;

            // Modal helpers — putih dengan tombol gradient
            var modalFactory = function (icon, btnClass) {
                return function (title, message, callback) {
                    return window.Swal.fire({
                        icon: icon,
                        title: title || '',
                        html: message ? ('<p style="color:var(--neutral-600);font-size:14px;margin:6px 0 0;">' + escapeHtml(message) + '</p>') : undefined,
                        confirmButtonText: 'OK',
                        allowOutsideClick: false,
                        customClass: { popup: 'swal-dd-modal', confirmButton: 'swal-dd-btn ' + btnClass }
                    }).then(function (r) { if (typeof callback === 'function') callback(r); });
                };
            };
            window.SwalHelper.modalSuccess = modalFactory('success', 'swal-dd-btn-success');
            window.SwalHelper.modalError   = modalFactory('error',   'swal-dd-btn-danger');
            window.SwalHelper.modalWarning = modalFactory('warning', 'swal-dd-btn-warning');
            window.SwalHelper.modalInfo    = modalFactory('info',    'swal-dd-btn-primary');
            window.SwalHelper.successModal = window.SwalHelper.modalSuccess;
            window.SwalHelper.errorModal   = window.SwalHelper.modalError;
            window.SwalHelper.warningModal = window.SwalHelper.modalWarning;
            window.SwalHelper.infoModal    = window.SwalHelper.modalInfo;
        }

        // notifToast(icon, judul, pesan, durasi) — signature lama
        window.notifToast = function (icon, judul, pesan, durasi) {
            var t = (icon === 'success' || icon === 'error' || icon === 'warning' || icon === 'info') ? icon : 'info';
            return fireToast({
                type: t,
                icon: t,
                title: judul || '',
                html: pesan ? escapeHtml(pesan) : undefined,
                timer: typeof durasi === 'number' ? durasi : 4000
            });
        };
        window.showToast = function (type, message, duration) {
            var t = (type === 'success' || type === 'error' || type === 'warning' || type === 'info') ? type : 'info';
            return fireToast({ type: t, icon: t, title: message || '', timer: typeof duration === 'number' ? duration : 4000 });
        };

        // DToast.show(type, message, duration)
        window.DToast = {
            show: function (type, message, duration) {
                var t = (type === 'success' || type === 'error' || type === 'warning' || type === 'info') ? type : 'info';
                return fireToast({ type: t, icon: t, title: message || '', timer: typeof duration === 'number' ? duration : 4000 });
            },
            dismiss: function () { if (window.Swal) window.Swal.close(); }
        };

        // window.Notifikasi wrapper
        window.Notifikasi = window.Notifikasi || {};
        window.Notifikasi.success = function (m) { return fireToast({ type: 'success', icon: 'success', title: m, timer: 4000 }); };
        window.Notifikasi.error = function (m) { return fireToast({ type: 'error', icon: 'error', title: m, timer: 4000 }); };
        window.Notifikasi.warning = function (m) { return fireToast({ type: 'warning', icon: 'warning', title: m, timer: 4000 }); };
        window.Notifikasi.info = function (m) { return fireToast({ type: 'info', icon: 'info', title: m, timer: 4000 }); };
        window.Notifikasi.confirm = function (msg, onYes, onNo) { return notifKonfirmasi(msg, onYes, onNo); };
    }

    // =====================================================================
    // GLOBAL Swal.fire INTERCEPTOR
    // ---------------------------------------------------------------------
    // Otomatis menyuntik customClass gradient + opsi standar (timerProgressBar,
    // showCloseButton, pause-on-hover, position top-end) untuk SEMUA pemanggilan
    // `Swal.fire({ toast:true, ... })` di seluruh project — termasuk OCR
    // (antrian-ocr.js), halaman antrian-online, statistik, layanan-mandiri,
    // auto-logout, dan inline script lainnya. Tanpa edit blade/JS lain.
    // =====================================================================
    if (window.Swal && !window.Swal.__ddIntercepted) {
        // Util: tempel customClass swal-dd-toast pada konfigurasi toast.
        // mode='mixin' → hanya kelas dasar (tanpa variant ikon), agar fire-time menambah variant.
        function __ddDecorateToastOpts(opt, mode) {
            if (!opt || typeof opt !== 'object') return opt;

            // Cabang MODAL — bila bukan toast, suntik swal-dd-modal untuk konsistensi putih.
            if (opt.toast !== true) {
                // Skip jika sudah ditandai eksplisit oleh helper internal.
                var existingPopup = '';
                if (opt.customClass) {
                    if (typeof opt.customClass === 'string') existingPopup = opt.customClass;
                    else if (typeof opt.customClass === 'object') existingPopup = opt.customClass.popup || '';
                }
                if (existingPopup.indexOf('swal-dd-modal') === -1 && existingPopup.indexOf('swal-dd-toast') === -1) {
                    if (!opt.customClass) opt.customClass = {};
                    if (typeof opt.customClass === 'string') {
                        opt.customClass = { popup: opt.customClass + ' swal-dd-modal' };
                    } else {
                        opt.customClass.popup = (existingPopup ? existingPopup + ' ' : '') + 'swal-dd-modal';
                    }
                    // Tombol gradient otomatis bila belum dispesifikkan
                    if (typeof opt.customClass === 'object') {
                        if (!opt.customClass.confirmButton) {
                            var btn = 'swal-dd-btn swal-dd-btn-primary';
                            if (opt.icon === 'success') btn = 'swal-dd-btn swal-dd-btn-success';
                            else if (opt.icon === 'error') btn = 'swal-dd-btn swal-dd-btn-danger';
                            else if (opt.icon === 'warning') btn = 'swal-dd-btn swal-dd-btn-warning';
                            opt.customClass.confirmButton = btn;
                        }
                        if (opt.showCancelButton && !opt.customClass.cancelButton) {
                            opt.customClass.cancelButton = 'swal-dd-btn swal-dd-btn-cancel';
                        }
                    }
                }
                if (typeof opt.customClass === 'object') {
                    var confirmText = opt.confirmButtonText || '';
                    if (isPositiveActionText(confirmText)) {
                        var currentConfirm = opt.customClass.confirmButton || 'swal-dd-btn swal-dd-btn-primary';
                        currentConfirm = String(currentConfirm).replace(/\bswal-dd-btn-primary\b/g, '').replace(/\bswal-btn-primary\b/g, '').trim();
                        opt.customClass.confirmButton = (currentConfirm ? currentConfirm + ' ' : '') + 'swal-dd-btn swal-dd-btn-success style-guide-positive-action';
                    }
                }
                return opt;
            }

            // Cabang TOAST
            opt = normalizeEmptyRequiredToast(opt) || opt;
            var hasIcon = (opt.icon === 'success' || opt.icon === 'error' || opt.icon === 'warning' || opt.icon === 'info');
            var iconClass = hasIcon ? (' swal-dd-' + opt.icon) : '';
            var ddClass = 'swal-dd-toast' + (mode === 'mixin' ? '' : iconClass);
            if (!opt.customClass) {
                opt.customClass = { popup: ddClass };
            } else if (typeof opt.customClass === 'string') {
                if (opt.customClass.indexOf('swal-dd-toast') === -1) opt.customClass = opt.customClass + ' ' + ddClass;
                else if (iconClass && opt.customClass.indexOf(iconClass.trim()) === -1) opt.customClass += iconClass;
            } else if (typeof opt.customClass === 'object') {
                var existing = opt.customClass.popup || '';
                if (existing.indexOf('swal-dd-toast') === -1) {
                    opt.customClass.popup = (existing ? existing + ' ' : '') + ddClass;
                } else if (iconClass && existing.indexOf(iconClass.trim()) === -1) {
                    opt.customClass.popup = existing + iconClass;
                }
            }
            if (typeof opt.position === 'undefined') opt.position = 'top-end';
            if (typeof opt.showConfirmButton === 'undefined') opt.showConfirmButton = false;
            if (typeof opt.timerProgressBar === 'undefined') opt.timerProgressBar = true;
            if (typeof opt.showCloseButton === 'undefined') opt.showCloseButton = true;
            if (typeof opt.timer === 'undefined') opt.timer = 4000;
            opt.backdrop = false;
            if (!opt.showClass) opt.showClass = {};
            if (!opt.hideClass) opt.hideClass = {};
            opt.showClass.backdrop = '';
            opt.hideClass.backdrop = '';
            opt.iconColor = toastIconColor(opt.icon);
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
        window.__ddDecorateToastOpts = __ddDecorateToastOpts;

        // Patch Swal.fire — menangkap pemanggilan langsung
        var __ddOrigFire = window.Swal.fire.bind(window.Swal);
        window.Swal.fire = function () {
            try {
                if (arguments[0] && typeof arguments[0] === 'object') {
                    arguments[0] = __ddDecorateToastOpts(arguments[0], 'fire');
                }
            } catch (e) { /* fail-safe */ }
            return __ddOrigFire.apply(window.Swal, arguments);
        };

        // Patch Swal.mixin — menangkap Toast = Swal.mixin({toast:true}); Toast.fire(...)
        var __ddOrigMixin = window.Swal.mixin.bind(window.Swal);
        window.Swal.mixin = function (mixinOpts) {
            try { mixinOpts = __ddDecorateToastOpts(mixinOpts, 'mixin'); } catch (e) {}
            var instance = __ddOrigMixin(mixinOpts);
            if (instance && typeof instance.fire === 'function' && !instance.__ddPatched) {
                var __origInstFire = instance.fire.bind(instance);
                instance.fire = function () {
                    try {
                        if (mixinOpts && mixinOpts.toast === true) {
                            var arg = arguments[0];
                            if (arg && typeof arg === 'object') {
                                if (typeof arg.toast === 'undefined') arg.toast = true;
                                arguments[0] = __ddDecorateToastOpts(arg, 'fire');
                            }
                        } else if (arguments[0] && typeof arguments[0] === 'object') {
                            arguments[0] = __ddDecorateToastOpts(arguments[0], 'fire');
                        }
                    } catch (e) {}
                    return __origInstFire.apply(instance, arguments);
                };
                instance.__ddPatched = true;
            }
            return instance;
        };

        window.Swal.__ddIntercepted = true;
    }

    // Patch sekarang dan ulangi sebentar agar menangani SwalHelper yang
    // didefinisikan inline di layout (urutan eksekusi bisa berbeda).
    patchLegacy();
    var tries = 0;
    var iv = setInterval(function () {
        patchLegacy();
        if (++tries > 40) clearInterval(iv);
    }, 50);

    var lastRequiredToastAt = 0;
    document.addEventListener('invalid', function (event) {
        var el = event.target;
        if (!el || !el.matches || !el.matches('input, select, textarea')) return;

        event.preventDefault();
        if (el.setCustomValidity) el.setCustomValidity('');

        var now = Date.now();
        if (now - lastRequiredToastAt > 800) {
            lastRequiredToastAt = now;
            fireToast({
                type: 'error',
                icon: 'error',
                title: EMPTY_REQUIRED_MESSAGE,
                timer: 4000
            });
        }

        if (typeof el.focus === 'function') {
            try { el.focus({ preventScroll: false }); }
            catch (e) { el.focus(); }
        }
    }, true);

    // =====================================================================
    // CONTOH PEMAKAIAN (untuk dokumentasi — tidak dijalankan otomatis)
    // =====================================================================
    /*
    // Dari tombol PHP/Blade:
    // <button onclick="notifSimpanBerhasil('#REG-2024-001234')">Simpan</button>
    // <button onclick="notifValidasiGagal(['NIK wajib diisi', 'Format tanggal tidak valid'])">Validasi</button>
    // <button onclick="notifNomorAntrian('A-015', 'KTP', '± 15 menit')">Antrian</button>

    // Dengan fetch/AJAX ke endpoint Laravel:
    async function simpanData(formData) {
        try {
            const res  = await fetch('/admin/penduduk', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.status === 'validasi') return notifValidasiGagal(data.errors);
            if (data.status === 'success')  return notifSimpanBerhasil(data.noReg);
            notifSimpanGagal(data.message || 'Gagal menyimpan');
        } catch (e) { notifSimpanGagal('Koneksi gagal. Periksa jaringan Anda.'); }
    }

    // Upload dokumen:
    function upload(file) {
        const fd = new FormData(); fd.append('file', file);
        notifUploadProses(file.name,
            () => fetch('/api/upload', { method: 'POST', body: fd }).then(r => r.ok ? r.json() : Promise.reject(new Error('HTTP ' + r.status))),
            () => upload(file)
        );
    }

    // Konfirmasi hapus:
    // <button onclick="notifKonfirmasiHapus('Budi Santoso', () => fetch('/penduduk/1', {method:'DELETE'}).then(() => location.reload()))">Hapus</button>

    // Pengajuan dokumen:
    // notifPengajuanProses('Akta Kelahiran',
    //     (noReg) => location.href = '/lacak/' + noReg,
    //     ()      => document.getElementById('form-ajuan').reset()
    // );
    */
})(window, document);
