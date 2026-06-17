(function (window) {
    'use strict';

    function safeHtml(value) {
        if (value == null) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function setInputValue(field, value) {
        if (!field) return;
        field.value = value;
    }

    function showLoadingModal(title, message) {
        Swal.fire({
            title: title || 'Memproses Verifikasi',
            html: message || '<div class="flex flex-col items-center gap-3 py-2"><i class="fas fa-circle-notch fa-spin text-4xl text-green-500"></i><p class="text-gray-600 text-sm">Sedang memproses data...</p></div>',
            showConfirmButton: false,
            showCancelButton: false,
            showDenyButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false
        });
    }

    function bindAcceptButton(config) {
        if (!config) return;

        var button = document.getElementById(config.buttonId || 'btnTerima');
        var form = document.getElementById(config.formId || 'formUpdateStatus');
        var statusInput = document.getElementById(config.statusInputId || 'inputStatus');
        var reasonInput = document.getElementById(config.reasonInputId || 'inputAlasan');
        var nextStatus = config.nextStatus;
        var confirmTitle = config.confirmTitle || 'Konfirmasi Penerimaan';
        var confirmHtml = config.confirmHtml || ('Lanjutkan permohonan ke tahap <strong>' + safeHtml(nextStatus) + '</strong>?');
        var loadingTitle = config.loadingTitle || 'Memproses Verifikasi';
        var loadingHtml = config.loadingHtml || '<div class="flex flex-col items-center gap-3 py-2"><i class="fas fa-circle-notch fa-spin text-4xl text-green-500"></i><p class="text-gray-600 text-sm">Sedang memproses data...</p></div>';
        var successText = config.successText || 'Perubahan status berhasil diproses.';

        if (!button || !form || !statusInput) return;

        button.addEventListener('click', function () {
            if (!nextStatus) {
                if (window.SwalHelper && SwalHelper.toastError) {
                    SwalHelper.toastError(
                        'Permohonan ini sudah berada di tahap akhir.',
                        'Tidak ada langkah berikutnya yang dapat diproses.'
                    );
                }
                return;
            }

            Swal.fire({
                icon: 'question',
                title: confirmTitle,
                html: confirmHtml,
                showCancelButton: true,
                showDenyButton: false,
                confirmButtonText: config.confirmText || 'Konfirmasi',
                cancelButtonText: config.cancelText || 'Batal',
                confirmButtonColor: config.confirmButtonColor || '#16a34a',
                cancelButtonColor: config.cancelButtonColor || '#6b7280',
                reverseButtons: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                buttonsStyling: true
            }).then(function (result) {
                if (!result.isConfirmed) return;

                button.disabled = true;
                button.dataset.originalHtml = button.dataset.originalHtml || button.innerHTML;
                button.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Memproses...';

                setInputValue(statusInput, nextStatus);
                setInputValue(reasonInput, '');

                showLoadingModal(loadingTitle, loadingHtml);

                window.setTimeout(function () {
                    if (window.PageLoading && typeof window.PageLoading.show === 'function') {
                        window.PageLoading.show('Memproses data...');
                    }
                    form.submit();
                }, config.submitDelay || 350);
            });
        });
    }

    function bindUploadForm(config) {
        config = config || {};
        var form = document.getElementById(config.formId || 'uploadBerkasForm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var fileInput = form.querySelector('input[type="file"][name="file_berkas"]');
            if (fileInput && (!fileInput.files || !fileInput.files.length)) {
                if (window.SwalHelper && SwalHelper.toastError) {
                    SwalHelper.toastError(
                        'File berkas wajib dipilih.',
                        'Pilih file PDF terlebih dahulu, lalu coba lagi.'
                    );
                }
                return;
            }

            var submitBtn = form.querySelector('button[type="submit"]');
            var pemohonEl = document.getElementById(config.pemohonElementId || 'namaPemohonModal');
            var pemohonName = pemohonEl ? pemohonEl.textContent.trim() : '-';
            var docLabel = config.docLabel ? safeHtml(config.docLabel) : 'berkas';
            var confirmHtml = config.confirmHtml || (
                'Upload berkas <strong>' + docLabel + '</strong> untuk pemohon <strong>' + safeHtml(pemohonName) + '</strong>? ' +
                'Berkas akan tersedia bagi pemohon di halaman <strong>Lacak Berkas</strong>.'
            );

            Swal.fire({
                icon: 'question',
                title: config.confirmTitle || 'Konfirmasi Upload Berkas',
                html: confirmHtml,
                showCancelButton: true,
                showConfirmButton: true,
                showDenyButton: false,
                denyButtonText: null,
                confirmButtonText: config.confirmText || 'Konfirmasi',
                cancelButtonText: config.cancelText || 'Batal',
                reverseButtons: true,
                confirmButtonColor: config.confirmButtonColor || '#16a34a',
                cancelButtonColor: config.cancelButtonColor || '#6b7280',
                allowOutsideClick: false,
                allowEscapeKey: false,
                buttonsStyling: true,
                didOpen: function () {
                    var denyBtn = document.querySelector('.swal2-deny');
                    if (denyBtn) denyBtn.remove();
                    var denyContainer = document.querySelector('.swal2-deny-container');
                    if (denyContainer) denyContainer.remove();
                }
            }).then(function (result) {
                if (!result.isConfirmed) return;

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-1"></i> Mengupload...';
                }

                Swal.fire({
                    title: config.loadingTitle || 'Mengunggah Berkas',
                    html: config.loadingHtml || (
                        '<div class="flex flex-col items-center gap-3 py-2">' +
                        '<i class="fas fa-circle-notch fa-spin text-4xl text-green-500"></i>' +
                        '<p class="text-gray-600 text-sm">Sedang mengunggah berkas...</p>' +
                        '</div>'
                    ),
                    showConfirmButton: false,
                    showCancelButton: false,
                    showDenyButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });

                window.setTimeout(function () {
                    if (window.PageLoading && typeof window.PageLoading.show === 'function') {
                        window.PageLoading.show('Mengunggah berkas...');
                    }
                    form.submit();
                }, config.submitDelay || 500);
            });
        });
    }

    function bindRejectButton(config) {
        config = config || {};
        var button = document.getElementById(config.buttonId || 'btnTolak');
        var form = document.getElementById(config.formId || 'formUpdateStatus');
        var statusInput = document.getElementById(config.statusInputId || 'inputStatus');
        var reasonInput = document.getElementById(config.reasonInputId || 'inputAlasan');
        if (!button || !form || !statusInput || !reasonInput) return;

        button.addEventListener('click', function () {
            Swal.fire({
                icon: false,
                title: config.confirmTitle || 'Tolak Permohonan',
                html: config.confirmHtml || 'Masukkan <strong>alasan penolakan</strong>. Alasan ini akan ditampilkan pada halaman lacak berkas pengguna.',
                input: 'textarea',
                inputPlaceholder: config.inputPlaceholder || 'Tulis alasan penolakan di sini...',
                inputAttributes: { 'aria-label': 'Alasan penolakan', 'maxlength': '500' },
                showCancelButton: true,
                showDenyButton: false,
                confirmButtonText: config.confirmText || 'Konfirmasi',
                cancelButtonText: config.cancelText || 'Batal',
                confirmButtonColor: config.confirmButtonColor || '#dc2626',
                cancelButtonColor: config.cancelButtonColor || '#6b7280',
                reverseButtons: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                inputValidator: function (value) {
                    if (!value || value.trim().length < 5) {
                        return config.inputError || 'Alasan penolakan wajib diisi (minimal 5 karakter).';
                    }
                }
            }).then(function (res) {
                if (!res.isConfirmed) return;

                statusInput.value = 'Tolak';
                reasonInput.value = res.value.trim();
                button.disabled = true;
                button.dataset.originalHtml = button.dataset.originalHtml || button.innerHTML;
                button.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Memproses...';

                showLoadingModal(
                    config.loadingTitle || 'Memproses Penolakan',
                    config.loadingHtml || '<div class="flex flex-col items-center gap-3 py-2"><i class="fas fa-circle-notch fa-spin text-4xl text-red-500"></i><p class="text-gray-600 text-sm">Sedang memproses penolakan permohonan...</p></div>'
                );

                window.setTimeout(function () {
                    if (window.PageLoading && typeof window.PageLoading.show === 'function') {
                        window.PageLoading.show('Memproses penolakan...');
                    }
                    form.submit();
                }, config.submitDelay || 350);
            });
        });
    }

    window.AdminVerificationConfirm = {
        bindAcceptButton: bindAcceptButton,
        bindRejectButton: bindRejectButton,
        bindUploadForm: bindUploadForm,
        showLoadingModal: showLoadingModal
    };
})(window);
