{{-- SweetAlert Global Styles untuk Admin - Mencegah popup hilang saat cursor digerakkan --}}
<style>
    /* ===== SWEETALERT GLOBAL FIXES ===== */
    /* Mencegah popup SweetAlert hilang saat cursor digerakkan */

    /* Container fixes */
    .swal2-container {
        pointer-events: auto !important;
    }

    /* Backdrop untuk modal saja, BUKAN untuk toast */
    .swal2-container.swal2-backdrop-show:not(.swal2-toast),
    .swal2-container.swal2-noanimation:not(.swal2-toast) {
        background: color-mix(in srgb, var(--neutral-900) 60%, transparent) !important;
    }

    /* Toast tidak perlu backdrop - transparan (semua posisi kanan atas) */
    .swal2-container.swal2-backdrop-show.swal2-toast,
    .swal2-container.swal2-backdrop-show.swal2-top,
    .swal2-container.swal2-backdrop-show.swal2-top-start,
    .swal2-container.swal2-backdrop-show.swal2-top-end,
    .swal2-container.swal2-backdrop-show.swal2-top-left,
    .swal2-container.swal2-backdrop-show.swal2-top-right,
    .swal2-container.swal2-noanimation.swal2-toast,
    .swal2-container.swal2-noanimation.swal2-top,
    .swal2-container.swal2-noanimation.swal2-top-start,
    .swal2-container.swal2-noanimation.swal2-top-end,
    .swal2-container.swal2-noanimation.swal2-top-left,
    .swal2-container.swal2-noanimation.swal2-top-right,
    .swal2-container.swal2-toast,
    .swal2-container.swal2-top,
    .swal2-container.swal2-top-start,
    .swal2-container.swal2-top-end,
    .swal2-container.swal2-top-left,
    .swal2-container.swal2-top-right,
    .swal2-container.swal2-bottom,
    .swal2-container.swal2-bottom-start,
    .swal2-container.swal2-bottom-end,
    .swal2-container.swal2-bottom-left,
    .swal2-container.swal2-bottom-right {
        background: transparent !important;
        background-color: transparent !important;
        background-image: none !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        filter: none !important;
        pointer-events: none !important;
    }

    /* Popup pada container toast tetap dapat di-klik */
    .swal2-container.swal2-toast .swal2-popup,
    .swal2-container.swal2-top .swal2-popup,
    .swal2-container.swal2-top-start .swal2-popup,
    .swal2-container.swal2-top-end .swal2-popup,
    .swal2-container.swal2-bottom .swal2-popup,
    .swal2-container.swal2-bottom-start .swal2-popup,
    .swal2-container.swal2-bottom-end .swal2-popup {
        pointer-events: auto !important;
    }

    /* Toast popup: kartu putih tanpa overlay */
    .swal2-popup.swal2-toast {
        font-family: "Plus Jakarta Sans", sans-serif !important;
        background: var(--neutral-white) !important;
        background-color: var(--neutral-white) !important;
        background-image: none !important;
        color: var(--neutral-900) !important;
        border-radius: var(--radius-lg) !important;
        box-shadow: var(--shadow-xl) !important;
        border: 0 !important;
        border-left: 4px solid var(--info-blue) !important;
        padding: var(--spacing-md) var(--spacing-lg) !important;
        min-width: 300px !important;
        max-width: 380px !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        filter: none !important;
    }

    .swal2-popup.swal2-toast .swal2-title {
        font-family: "Plus Jakarta Sans", sans-serif !important;
        color: var(--neutral-900) !important;
        font-size: var(--font-size-sm) !important;
        font-weight: 400 !important;
        text-align: left !important;
    }

    .swal2-popup.swal2-toast .swal2-html-container {
        font-family: "Plus Jakarta Sans", sans-serif !important;
        font-weight: 400 !important;
    }

    .swal2-popup.swal2-toast .swal2-timer-progress-bar-container { display: block !important; }

    .swal2-popup.swal2-toast .swal2-timer-progress-bar {
        background: var(--primary-blue-main) !important;
    }

    /* Hapus backdrop dari toast container */
    .swal2-container.swal2-toast .swal2-backdrop {
        display: none !important;
    }

    /* Popup fixes - MOST IMPORTANT */
    .swal2-popup {
        pointer-events: auto !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .swal2-popup.swal2-modal {
        pointer-events: auto !important;
    }

    .swal2-shown {
        overflow-y: auto !important;
        pointer-events: auto !important;
    }

    /* Content fixes */
    .swal2-header,
    .swal2-content,
    .swal2-actions {
        pointer-events: auto !important;
    }

    /* Button fixes - Enhanced hover effects */
    .swal2-confirm,
    .swal2-cancel,
    .swal2-styled {
        pointer-events: auto !important;
        transition: all 0.2s ease !important;
        cursor: pointer !important;
    }

    .swal2-confirm:hover,
    .swal2-cancel:hover,
    .swal2-styled:hover {
        transform: translateY(-1px) !important;
        box-shadow: var(--shadow-md) !important;
    }

    .swal2-confirm:active,
    .swal2-cancel:active,
    .swal2-styled:active {
        transform: translateY(0) !important;
    }

    /* Icon fixes */
    .swal2-icon {
        pointer-events: none !important;
    }

    /* Input fixes */
    .swal2-input,
    .swal2-textarea,
    .swal2-select {
        pointer-events: auto !important;
    }

    /* Close button fix */
    .swal2-close {
        pointer-events: auto !important;
        transition: all 0.2s ease !important;
    }

    .swal2-close:hover {
        transform: rotate(90deg) !important;
    }

    /* Progress steps */
    .swal2-progress-steps {
        pointer-events: auto !important;
    }

    /* Validation message */
    .swal2-validation-message {
        pointer-events: none !important;
    }

    /* Queue steps */
    .swal2-queue-step {
        pointer-events: auto !important;
    }

    /* IMPORTANT: Prevent backdrop from blocking pointer events */
    body.swal2-shown > .swal2-container {
        pointer-events: auto !important;
    }

    body.swal2-shown > [aria-hidden='true'] {
        pointer-events: none !important;
    }

    /* Fix for z-index issues */
    .swal2-container {
        z-index: 99999 !important;
    }

    /* Fix for mobile responsiveness */
    @media (max-width: 768px) {
        .swal2-popup {
            width: 90% !important;
            max-width: 400px !important;
        }
    }

    /* Animation fixes */
    .swal2-show {
        animation: swal2-show 0.3s forwards !important;
    }

    .swal2-hide {
        animation: swal2-hide 0.3s forwards !important;
    }

    @keyframes swal2-show {
        0% {
            transform: scale(0.8);
            opacity: 0;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    @keyframes swal2-hide {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        100% {
            transform: scale(0.8);
            opacity: 0;
        }
    }

    /* Enhanced styling for better UX */
    .swal2-popup {
        border-radius: 1rem !important;
        box-shadow: var(--shadow-xl) !important;
    }

    /* Custom scrollbar for SweetAlert content */
    .swal2-html-container::-webkit-scrollbar {
        width: 6px;
    }

    .swal2-html-container::-webkit-scrollbar-thumb {
        background: var(--neutral-300);
        border-radius: 3px;
    }

    .swal2-html-container::-webkit-scrollbar-thumb:hover {
        background: var(--neutral-400);
    }
</style>

<script>
    // SweetAlert Configuration untuk Admin
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swal !== 'undefined') {
            // Set default configuration untuk semua SweetAlert di admin
            Swal.mixin({
                allowOutsideClick: false,
                allowEscapeKey: false,
                showClass: {
                    popup: 'swal2-show',
                    backdrop: 'swal2-backdrop-show',
                    icon: 'swal2-icon-show'
                },
                hideClass: {
                    popup: 'swal2-hide',
                    backdrop: 'swal2-backdrop-hide',
                    icon: 'swal2-icon-hide'
                },
                customClass: {
                    popup: 'swal2-popup',
                    backdrop: 'swal2-backdrop'
                }
            });

            // Fix untuk mencegah popup hilang saat cursor digerakkan
            document.addEventListener('mousemove', function(e) {
                const swalPopup = document.querySelector('.swal2-container.swal2-shown');
                if (swalPopup) {
                    // Pastikan popup tetap visible saat cursor digerakkan
                    swalPopup.style.pointerEvents = 'auto';
                }
            });

            // Fix untuk klik di luar popup
            document.addEventListener('click', function(e) {
                const swalPopup = document.querySelector('.swal2-popup');
                const swalContainer = document.querySelector('.swal2-container');

                if (swalContainer && swalContainer.classList.contains('swal2-shown')) {
                    // Jika klik di luar popup dan allowOutsideClick adalah false
                    if (swalPopup && !swalPopup.contains(e.target) && !e.target.classList.contains('swal2-confirm')) {
                        // Cek apakah SweetAlert saat ini mengizinkan outside click
                        const currentSwal = window.SwalInstance;
                        if (currentSwal && currentSwal.params && !currentSwal.params.allowOutsideClick) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                    }
                }
            }, true);

            // Simpan referensi ke SweetAlert instance
            const originalFire = Swal.fire;
            Swal.fire = function() {
                const result = originalFire.apply(this, arguments);
                window.SwalInstance = result;
                return result;
            };
        }
    });
</script>
