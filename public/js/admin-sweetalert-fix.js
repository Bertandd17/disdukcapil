/**
 * SweetAlert Global Fix untuk Admin
 * Versi yang lebih sederhana dan tidak mengganggu SweetAlert native
 */

document.addEventListener('DOMContentLoaded', function() {
    // Fix untuk semua tombol dengan class btn-status dan btn-tolak
    setTimeout(function() {
        // Fix untuk tombol status
        const statusButtons = document.querySelectorAll('.btn-status');
        statusButtons.forEach(function(btn) {
            // Hapus event listener yang lama jika ada
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);

            // Tambah event listener baru dengan fix
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                const form = this.closest('form');
                const statusBaru = form.querySelector('input[name="status"]').value;

                // Panggil fungsi SweetAlert
                if (typeof SwalHelper !== 'undefined' && SwalHelper.confirmUpdate) {
                    SwalHelper.confirmUpdate(
                        'Ubah Status',
                        'Apakah Anda yakin ingin mengubah status?',
                        'Status akan diperbarui ke: ' + statusBaru,
                        function() {
                            form.submit();
                        }
                    );
                }
            }, { passive: false });
        });

        // Fix untuk tombol tolak
        const tolakButtons = document.querySelectorAll('.btn-tolak');
        tolakButtons.forEach(function(btn) {
            // Hapus event listener yang lama jika ada
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);

            // Tambah event listener baru dengan fix
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                const form = this.closest('form');
                const alasan = form.querySelector('.input-alasan');

                // Panggil fungsi SweetAlert
                if (typeof SwalHelper !== 'undefined' && SwalHelper.confirmDelete) {
                    SwalHelper.confirmDelete(
                        'Tolak Permohonan',
                        'Apakah Anda yakin ingin menolak permohonan ini?',
                        'Permohonan yang ditolak tidak dapat dikembalikan.',
                        function() {
                            Swal.fire({
                                title: 'Alasan Penolakan',
                                input: 'textarea',
                                inputPlaceholder: 'Masukkan alasan penolakan...',
                                showCancelButton: true,
                                confirmButtonColor: '#ef4444',
                                cancelButtonColor: '#64748b',
                                confirmButtonText: 'Ya, Tolak',
                                cancelButtonText: 'Batal',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                returnFocus: false,
                                inputValidator: function(value) {
                                    if (!value) return 'Alasan wajib diisi!';
                                }
                            }).then(function(result) {
                                if (result.isConfirmed) {
                                    alasan.value = result.value;
                                    form.submit();
                                }
                            });
                        }
                    );
                }
            }, { passive: false });
        });
    }, 100);

    // HAPUS: SetInterval yang mengganggu
    // HAPUS: Event listener mousemove yang menyebabkan popup hilang saat cursor bergerak
    // HAPUS: Event listener click dan keydown yang override native SweetAlert behavior

    // Override Swal.fire untuk menyimpan referensi
    if (typeof Swal !== 'undefined') {
        const originalFire = Swal.fire;
        Swal.fire = function() {
            const result = originalFire.apply(this, arguments);
            window.SwalInstance = result;
            return result;
        };
    }
});

// Tambahkan CSS fix secara dinamis - VERSI SEDERHANA
const style = document.createElement('style');
style.textContent = `
    /* SweetAlert Container - TANPA !IMPORTANT yang berlebihan */
    .swal2-container {
        pointer-events: auto;
        position: fixed;
        z-index: 99999;
    }

    .swal2-popup {
        pointer-events: auto;
        border-radius: 1rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    /* Button styling */
    .swal2-confirm,
    .swal2-cancel {
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .swal2-confirm:hover,
    .swal2-cancel:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Custom scrollbar */
    .swal2-html-container::-webkit-scrollbar {
        width: 6px;
    }

    .swal2-html-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .swal2-html-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
`;

document.head.appendChild(style);
