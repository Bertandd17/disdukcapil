/* =================================================================
 * Antrian Online — Auto-OCR multi-step form dengan EasyOCR
 * Menggunakan EasyOCR untuk extract data KTP
 * ================================================================= */
(function () {
    'use strict';

    var cfg = typeof window.ANTRIAN_OCR_CONFIG === 'object' && window.ANTRIAN_OCR_CONFIG !== null
        ? window.ANTRIAN_OCR_CONFIG
        : {};

    // EasyOCR API URL
    function easyOcrUrl() {
        return cfg.easyOcrUploadUrl || '/api/ocr/process';
    }

    // Store URL untuk simpan antrian
    function storeUrl() {
        return cfg.storeUrl || '/antrian-online';
    }

    // ---------- helpers ----------
    var $ = function (id) { return document.getElementById(id); };
    var csrfToken = function () {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    };
    function ocrExtractFailCopy() {
        if (window.ANTRIAN_OCR_MESSAGES && window.ANTRIAN_OCR_MESSAGES.extractFail) {
            return window.ANTRIAN_OCR_MESSAGES.extractFail;
        }
        return {
            problem: 'Gagal Melakukan Ekstrak data',
            solution: 'Pastikan yang anda upload adalah KTP atau perbaiki cara pengambilan gambar anda agar lebih baik lagi.'
        };
    }

    function friendlyOcrError(msg) {
        if (typeof window.normalizeOcrExtractError === 'function') {
            var normalized = window.normalizeOcrExtractError(msg);
            if (normalized && normalized.solution) return normalized;
        }
        var text = String(msg || '').toLowerCase();
        if (/ocr\.?space|easyocr|ocr gagal|tidak menemukan teks|fallback|service ocr/i.test(text)
            || (text.indexOf('ocr') !== -1 && (text.indexOf('gagal') !== -1 || text.indexOf('fallback') !== -1))) {
            return ocrExtractFailCopy();
        }
        return { problem: msg, solution: null };
    }

    var toastError = function (msg) {
        var copy = friendlyOcrError(msg);
        var problem = copy.problem || msg;
        var solution = copy.solution;
        if (window.SwalHelper && typeof window.SwalHelper.toastError === 'function') {
            window.SwalHelper.toastError(problem, solution || undefined);
        } else if (typeof fireToast === 'function') {
            fireToast({
                type: 'error',
                icon: 'error',
                title: 'Terjadi kesalahan',
                problem: problem,
                solution: solution || 'Periksa data yang dimasukkan dan coba lagi.',
                timer: 5000
            });
        } else if (window.__nativeAlert) {
            window.__nativeAlert(problem);
        } else if (window.console && typeof window.console.error === 'function') {
            window.console.error(problem);
        }
    };

    var toastSuccess = function (msg) {
        if (window.SwalHelper && typeof window.SwalHelper.toastSuccess === 'function') {
            window.SwalHelper.toastSuccess(msg);
        }
    };

    // ---------- state ----------
    var state = {
        antrianId: null,
        nomor    : null,
        layananTxt: null,
        layananId: null,
        file     : null,
    };

    function boot() {
        console.log('[OCR] Boot - EasyOCR Mode');
        console.log('[OCR] Config:', cfg);

        var fileInput   = $('ktpFileInput');
        var uploadArea  = $('uploadArea');
        var placeholder = $('uploadPlaceholder');
        var preview     = $('previewContainer');
        var previewImg  = $('imagePreview');
        var fileName    = $('fileName');
        var changeBtn   = $('changeImageBtn');
        var nextBtn     = $('step1NextBtn');
        var layananSel  = $('layanan_id');
        var debug       = $('uploadDebugValue');

        if (!fileInput || !uploadArea || !nextBtn || !layananSel) {
            console.error('[OCR] Elemen form tidak ditemukan');
            return;
        }

        function say(txt) {
            if (debug) debug.textContent = txt;
            console.log('[OCR] ' + txt);
        }

        console.log('[OCR] Menggunakan EasyOCR untuk extract data KTP');

        say('Siap - upload KTP untuk auto-fill data');

        function syncNextBtn() {
            nextBtn.disabled = !(state.file && layananSel.value);
        }

        if (layananSel) {
            layananSel.addEventListener('change', function() {
                say('layanan: ' + (layananSel.options[layananSel.selectedIndex] ? layananSel.options[layananSel.selectedIndex].text : '-'));
                state.layananId = layananSel.value;
                syncNextBtn();
            });
        }

        uploadArea.addEventListener('click', function (e) {
            if (changeBtn && (e.target === changeBtn || changeBtn.contains(e.target))) return;
            say('buka file picker');
            fileInput.value = '';
            fileInput.click();
        });
        if (changeBtn) {
            changeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                say('buka file picker (ganti)');
                fileInput.value = '';
                fileInput.click();
            });
        }

        uploadArea.addEventListener('dragover', function (e) {
            e.preventDefault();
            uploadArea.classList.add('border-blue-500', 'bg-blue-50');
        });
        uploadArea.addEventListener('dragleave', function (e) {
            e.preventDefault();
            uploadArea.classList.remove('border-blue-500', 'bg-blue-50');
        });
        uploadArea.addEventListener('drop', function (e) {
            e.preventDefault();
            uploadArea.classList.remove('border-blue-500', 'bg-blue-50');
            var f = e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files[0] : null;
            if (f) { say('drop: ' + f.name); processFile(f); }
        });

        fileInput.addEventListener('change', function () {
            say('change event, files=' + (fileInput.files ? fileInput.files.length : 0));
            if (fileInput.files && fileInput.files.length > 0) {
                processFile(fileInput.files[0]);
            } else {
                say('dialog ditutup tanpa file');
            }
        });

        function processFile(file) {
            say('file: ' + file.name + ' (' + Math.round(file.size / 1024) + ' KB, ' + (file.type || '?') + ')');
            var ext = (file.name || '').toLowerCase().split('.').pop();
            var okExt  = ['png','jpg','jpeg','jfif'];
            var okMime = ['image/png','image/jpeg','image/jpg','image/jfif','image/pjpeg'];
            if (!okMime.includes((file.type || '').toLowerCase()) && !okExt.includes(ext)) {
                say('format ditolak');
                toastError('Format harus PNG atau JPG/JPEG');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                say('ukuran > 5 MB');
                toastError('Ukuran file maksimal 5 MB');
                return;
            }
            state.file = file;
            say('memuat preview…');
            var reader = new FileReader();
            reader.onload = function (ev) {
                say('preview siap — silakan klik Lanjut');
                if (previewImg)  previewImg.src = ev.target.result;
                if (fileName)    fileName.textContent = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
                if (placeholder) placeholder.classList.add('hidden');
                if (preview)     preview.classList.remove('hidden');
                syncNextBtn();
            };
            reader.onerror = function () {
                say('FileReader error');
                toastError('Gagal membaca file');
                state.file = null;
                syncNextBtn();
            };
            reader.readAsDataURL(file);
        }

        // ---------- step nav ----------
        function goToStep(step) {
            document.querySelectorAll('.step-content').forEach(function (e) { e.classList.add('hidden'); });
            var t = $('step' + step);
            if (t) t.classList.remove('hidden');
            for (var i = 1; i <= 3; i++) {
                var ind = $('step' + i + 'Indicator');
                var lbl = $('step' + i + 'Label');
                if (!ind || !lbl) continue;
                if (i < step) {
                    ind.className = 'step-indicator completed flex items-center justify-center w-10 h-10 rounded-full bg-green-500 text-white font-bold';
                    ind.innerHTML = '<i class="fas fa-check"></i>';
                    lbl.className = 'font-semibold text-green-600';
                } else if (i === step) {
                    ind.className = 'step-indicator active flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-bold';
                    ind.textContent = i;
                    lbl.className = 'font-semibold text-blue-600';
                } else {
                    ind.className = 'step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-bold';
                    ind.textContent = i;
                    lbl.className = 'font-semibold text-gray-400';
                }
            }
            var l1 = $('line1'); var l2 = $('line2');
            if (l1) l1.className = step > 1 ? 'w-16 h-1 bg-green-500 mx-2' : 'w-16 h-1 bg-gray-300 mx-2';
            if (l2) l2.className = step > 2 ? 'w-16 h-1 bg-green-500 mx-2' : 'w-16 h-1 bg-gray-300 mx-2';
            var fs = $('formSection');
            if (fs) fs.scrollIntoView({ behavior: 'smooth' });
        }

        // ---------- OCR panel ----------
        function setOcrPanel(mode, msg, titleOverride) {
            var panel = $('ocrConfidence');
            var wrap  = $('ocrStatusIcon');
            var icon  = $('ocrStatusFa');
            var title = $('ocrStatusTitle');
            var txt   = $('ocrStatusMessage');
            var badge = $('ocrTrustBadge');
            if (!panel) return;
            var P = {
                processing:{p:'bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4', w:'w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0',i:'fas fa-spinner fa-spin text-blue-600',t:'font-semibold text-blue-800',b:'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-200 text-blue-800',bt:'Memproses',tt:'OCR sedang berjalan…',m:'Sistem sedang membaca data dari foto KTP. Mohon tunggu sebentar.'},
                ready     :{p:'bg-green-50 border border-green-200 rounded-xl p-4 mb-4',w:'w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0',i:'fas fa-check-circle text-green-600',t:'font-semibold text-green-800',b:'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-200 text-green-800',bt:'Auto-fill',tt:'Data berhasil diekstrak',m:'Data dari foto KTP sudah diisi otomatis. Silakan periksa dan koreksi jika perlu.'},
                timeout   :{p:'bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4',w:'w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0',i:'fas fa-exclamation-triangle text-amber-600',t:'font-semibold text-amber-800',b:'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-200 text-amber-800',bt:'Isi manual',tt:'OCR belum selesai',m:'OCR butuh waktu lebih lama. Silakan isi manual.'},
                error     :{p:'bg-red-50 border border-red-200 rounded-xl p-4 mb-4',w:'w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0',i:'fas fa-times-circle text-red-600',t:'font-semibold text-red-800',b:'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-200 text-red-800',bt:'Isi manual',tt:'OCR gagal',m:'Terjadi kesalahan. Silakan isi manual.'},
            };
            var s = P[mode] || P.error;
            panel.className = s.p;
            if (wrap)  wrap.className  = s.w;
            if (icon)  icon.className  = s.i;
            if (title) {
                title.className = s.t;
                title.textContent = titleOverride || s.tt;
            }
            if (txt)   txt.textContent = msg || s.m;
            if (badge) { badge.className = s.b; badge.textContent = s.bt; }
        }

        function showOcrExtractError(rawMsg) {
            var copy = friendlyOcrError(rawMsg);
            toastError(rawMsg);
            setOcrPanel('error', copy.solution || copy.problem, copy.problem);
        }

        // ---------- step 1 next → OCR dengan EasyOCR ----------
        nextBtn.addEventListener('click', function () {
            if (!state.file)          { toastError('Pilih foto KTP dulu'); return; }
            if (!layananSel.value)    { toastError('Pilih jenis layanan dulu'); return; }

            clearStep2Fields();
            setOcrPanel('processing');
            state.layananTxt = layananSel.options[layananSel.selectedIndex].text;

            var ori = nextBtn.innerHTML;
            nextBtn.disabled = true;
            nextBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Mengekstrak Data…';

            // Kirim ke EasyOCR endpoint
            var fd = new FormData();
            fd.append('ktp_image', state.file, state.file.name);

            fetch(easyOcrUrl(), {
                method : 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                body   : fd,
                credentials: 'same-origin',
            })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, st: r.status, d: d }; }); })
            .then(function (res) {
                nextBtn.innerHTML = ori;
                
                if (!res.ok || !res.d) {
                    var errMsg = (res.d && res.d.message) ? res.d.message : 'OCR gagal diproses';
                    showOcrExtractError(errMsg);
                    syncNextBtn();
                    return;
                }

                if (res.d.success) {
                    // OCR berhasil - langsung ke step 2 dengan data
                    var data = res.d.data || {};
                    
                    console.log('[OCR] Data berhasil diekstrak:', data);
                    
                    goToStep(2);
                    applyOcrLocal(data);
                    setOcrPanel('ready');
                    toastSuccess('Data berhasil diekstrak dari KTP!');
                } else {
                    // OCR gagal
                    var errMsg = (res.d.message) ? res.d.message : 'OCR tidak dapat membaca KTP';
                    showOcrExtractError(errMsg);
                    syncNextBtn();
                }
            })
            .catch(function (e) {
                console.error('[OCR] Error:', e);
                toastError('Terjadi kesalahan koneksi: ' + e.message);
                nextBtn.innerHTML = ori;
                setOcrPanel('error', 'Gagal terhubung ke server OCR');
                syncNextBtn();
            });
        });

        // ---------- step 2 ----------
        var s2p = $('step2PrevBtn'); if (s2p) s2p.addEventListener('click', function () { goToStep(1); });
        var s2n = $('step2NextBtn'); if (s2n) s2n.addEventListener('click', function () {
            var nik    = ($('nik').value || '').trim();
            var nama   = ($('nama_lengkap').value || '').trim();
            var alamat = ($('alamat').value || '').trim();
            
            if (!nik || !nama || !alamat) { toastError('Ada kolom yang wajib diisi'); return; }
            if (nama.toUpperCase() === 'MENUNGGU OCR') { toastError('Isi nama lengkap sesuai KTP (bukan teks placeholder)'); return; }
            if (!/^\d{16}$/.test(nik))   { toastError('NIK harus 16 digit angka'); return; }
            
            $('summaryNik').textContent     = nik;
            $('summaryNama').textContent    = nama;
            $('summaryAlamat').textContent  = alamat;
            $('summaryLayanan').textContent = state.layananTxt || '-';
            $('summaryNomor').textContent   = state.nomor || '-';
            
            goToStep(3);
        });

        // ---------- step 3 ----------
        var s3p = $('step3PrevBtn'); if (s3p) s3p.addEventListener('click', function () { goToStep(2); });
        var form = $('antrianForm'); if (form) form.addEventListener('submit', function (e) { e.preventDefault(); finalize(); });

        // ---------- Helper functions ----------
        function fmtScore(v) {
            if (v === null || v === undefined || v === '') return '—';
            var n = Number(v);
            if (isNaN(n)) return '—';
            return (Math.round(n * 1000) / 1000).toString();
        }

        // Apply OCR result - hanya NIK, Nama, Alamat
        function applyOcrLocal(d) {
            var n = $('nik'); 
            var na = $('nama_lengkap'); 
            var al = $('alamat');
            
            // Isi field utama jika ada data
            if (n && d.nik) n.value = d.nik;
            if (na && d.nama_lengkap) na.value = d.nama_lengkap;
            if (al && d.alamat) al.value = d.alamat;
        }

        // ---------- finalize (buat antrian) ----------
        function finalize() {
            var nik    = ($('nik').value || '').trim();
            var nama   = ($('nama_lengkap').value || '').trim();
            var alamat = ($('alamat').value || '').trim();
            if (!/^\d{16}$/.test(nik)) { toastError('NIK harus 16 digit angka'); return; }
            if (!nama || !alamat)      { toastError('Ada kolom yang wajib diisi'); return; }

            var btn = $('submitBtn'); var ori = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Membuat antrian…';
            
            // Buat antrian baru
            fetch(storeUrl(), {
                method : 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body   : JSON.stringify({ 
                    nik: nik, 
                    nama_lengkap: nama, 
                    alamat: alamat,
                    layanan_id: state.layananId
                }),
                credentials: 'same-origin',
            })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, st: r.status, d: d }; }); })
            .then(function (res) {
                if (!res.ok || !res.d || res.d.success !== true) {
                    toastError((res.d && res.d.message) ? res.d.message : 'Gagal membuat antrian');
                    return;
                }
                state.antrianId = null;
                showTicket(res.d.data);
                if (typeof window.loadStatistics === 'function') {
                    window.loadStatistics();
                }
            })
            .catch(function () { toastError('Kesalahan jaringan'); })
            .finally(function () { btn.disabled = false; btn.innerHTML = ori; });
        }

        function showTicket(data) {
            var fs = $('formSection'); var tr = $('ticketResult');
            if (!fs || !tr) return;
            fs.classList.add('hidden');
            tr.classList.remove('hidden');
            var t = $('ticketNumber'); var n = $('ticketName'); var s = $('ticketService'); var tm = $('ticketTime');
            if (t) t.textContent  = data.nomor_antrian  || '';
            if (n) n.textContent  = data.nama_lengkap   || '';
            if (s) s.textContent  = data.layanan        || '';
            if (tm) tm.textContent = new Date().toLocaleString('id-ID');
            makeConfetti();
        }

        function makeConfetti() {
            var c = $('confetti-container');
            if (!c) return;
            var cols = ['#0052CC','#00B8D9','#10B981','#F59E0B','#EF4444'];
            for (var i = 0; i < 100; i++) {
                var el = document.createElement('div');
                el.className = 'confetti';
                el.style.left = (Math.random() * 100) + 'vw';
                el.style.backgroundColor = cols[Math.floor(Math.random() * cols.length)];
                el.style.animationDelay = (Math.random() * 2) + 's';
                el.style.animationDuration = (Math.random() * 2 + 2) + 's';
                c.appendChild(el);
                (function (node) { setTimeout(function () { node.remove(); }, 5000); })(el);
            }
        }

        function clearStep2Fields() {
            // Hanya clear field yang diperlukan
            var fields = ['nik', 'nama_lengkap', 'alamat'];
            fields.forEach(function (id) { var e = $(id); if (e) e.value = ''; });
        }

        // ---------- reset ----------
        function performResetUi() {
            state.antrianId = null;
            state.nomor     = null;
            state.layananTxt= null;
            state.layananId = null;
            state.file      = null;
            var f = $('antrianForm');
            if (f) f.reset();
            clearStep2Fields();
            if (placeholder) placeholder.classList.remove('hidden');
            if (preview)     preview.classList.add('hidden');
            if (previewImg)  previewImg.removeAttribute('src');
            if (fileName)    fileName.textContent = '';
            if (nextBtn)     nextBtn.disabled = true;
            setOcrPanel('processing', 'Upload KTP untuk auto-fill data.');
            var fs = $('formSection'); var tr = $('ticketResult'); var cc = $('confetti-container');
            if (fs) fs.classList.remove('hidden');
            if (tr) tr.classList.add('hidden');
            if (cc) cc.innerHTML = '';
            var ofs = $('ocrFieldScores');
            if (ofs) ofs.classList.add('hidden');
            goToStep(1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        window.resetForm = function () {
            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    title: 'Konfirmasi Reset',
                    text: 'Nomor antrian saat ini akan hilang. Lanjutkan?',
                    icon: false,
                    showCancelButton: true,
                    showDenyButton: false,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#e5e7eb',
                    confirmButtonText: 'Konfirmasi',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    performResetUi();
                    toastSuccess('Form Direset', 'Silakan ambil nomor antrian baru', 1500);
                });
            } else if (typeof notifKonfirmasi === 'function') {
                notifKonfirmasi('Ambil antrian baru? Nomor saat ini akan hilang.', function() {
                    performResetUi();
                });
            } else if (typeof fireToast !== 'undefined') {
                fireToast({
                    type: 'error', icon: 'error',
                    title: 'Ambil antrian baru?',
                    problem: 'Tindakan ini akan menghapus nomor antrian yang sedang diambil.',
                    solution: 'Pilih tombol "Ambil" pada langkah berikutnya untuk konfirmasi ulang.'
                });
            }
        };

        if (window.PageLoading && typeof window.PageLoading.hide === 'function') {
            window.PageLoading.hide();
        }

        console.log('[OCR] siap');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
