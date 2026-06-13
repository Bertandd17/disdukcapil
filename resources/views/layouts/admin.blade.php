<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $page_title ?? 'Admin Dashboard - Disdukcapil Kabupaten Toba' }}</title>

    <!-- User Authenticated Meta Tag -->
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_toba.jpeg') }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Disdukcapil Style Guide -->
    <link rel="stylesheet" href="{{ asset('css/style-guide.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- SweetAlert2 — hanya dimuat SEKALI di sini -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- SweetAlert2 Disdukcapil Notification System -->
    <script src="{{ asset_v('js/sweetalert-disdukcapil.js') }}"></script>
    <script src="{{ asset_v('js/sweetalert-helper.js') }}"></script>

    <!-- Notifikasi Disdukcapil Helper -->
    <script src="{{ asset('js/notifikasi-disdukcapil.js') }}"></script>

    <!-- SweetAlert2 Final Fix — paksa 3 flag false pada loading modal & auto-strip deny button -->
    <link rel="stylesheet" href="{{ asset('css/swal-final-fix.css') }}">
    <script src="{{ asset('js/swal-final-fix.js') }}"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--neutral-100); }
        ::-webkit-scrollbar-thumb { background: var(--primary-blue-main); border-radius: var(--radius-sm); }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary-blue-dark); }

        .sidebar { transition: all 0.3s ease; }
        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .sidebar-text,
        .sidebar.collapsed .logo-text { display: none; }

        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link:hover,
        .sidebar-link.active { background: var(--primary-blue-50); color: var(--primary-blue-main); }
        .sidebar-link.active { border-left: 3px solid var(--primary-blue-main); }

        .dropdown-menu { display: none; padding-left: 2rem; }
        .dropdown-menu.active { display: block; }
        .dropdown-toggle { justify-content: space-between; }
        .dropdown-toggle .fa-chevron-down { transition: transform 0.3s ease; }
        .dropdown-toggle.active .fa-chevron-down { transform: rotate(180deg); }

        .main-content { transition: all 0.3s ease; }
        .main-content.expanded { margin-left: 80px; }

        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .reveal { opacity: 0; transform: translateY(20px); transition: all 0.6s ease-out; }
        .reveal.active { opacity: 1; transform: translateY(0); }

        /* ── SweetAlert2 custom styles ── */
        .swal2-popup.swal2-modal {
            border-radius: var(--radius-lg) !important;
            padding: var(--spacing-lg) !important;
            box-shadow: var(--shadow-xl) !important;
        }

        .swal2-confirm.swal-btn-primary {
            background: var(--primary-blue-main) !important;
            border-radius: var(--radius-md) !important;
            padding: 0.75rem 1.5rem !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            box-shadow: var(--shadow-sm) !important;
            transition: all var(--transition-base) !important;
        }
        .swal2-confirm.swal-btn-primary:hover {
            transform: translateY(-1px) !important;
            background: var(--primary-blue-dark) !important;
            box-shadow: var(--shadow-md) !important;
        }

        .swal2-cancel.swal-btn-cancel {
            background: var(--neutral-200) !important;
            color: var(--neutral-900) !important;
            border-radius: var(--radius-md) !important;
            padding: 0.75rem 1.5rem !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            transition: all var(--transition-base) !important;
        }
        .swal2-cancel.swal-btn-cancel:hover {
            background: var(--neutral-300) !important;
            transform: translateY(-1px) !important;
        }

        .swal2-confirm.swal-btn-delete {
            background: var(--danger-red) !important;
            border-radius: var(--radius-md) !important;
            padding: 0.75rem 1.5rem !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            box-shadow: var(--shadow-sm) !important;
            transition: all var(--transition-base) !important;
        }
        .swal2-confirm.swal-btn-delete:hover {
            transform: translateY(-1px) !important;
            background: var(--secondary-red) !important;
            box-shadow: var(--shadow-md) !important;
        }

        .swal2-confirm.swal-btn-success {
            background: var(--success-green) !important;
            border-radius: var(--radius-md) !important;
            padding: 0.75rem 1.5rem !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            box-shadow: var(--shadow-sm) !important;
            transition: all var(--transition-base) !important;
        }
        .swal2-confirm.swal-btn-success:hover {
            transform: translateY(-1px) !important;
            background: var(--secondary-green) !important;
            box-shadow: var(--shadow-md) !important;
        }

        .swal2-toast { font-family: 'Plus Jakarta Sans', sans-serif !important; border-radius: var(--radius-lg) !important; box-shadow: var(--shadow-xl) !important; background: var(--neutral-white) !important; }
        .swal2-toast .swal2-title,
        .swal2-toast .swal2-html-container { font-family: 'Plus Jakarta Sans', sans-serif !important; font-size: var(--font-size-sm) !important; font-weight: 400 !important; color: var(--neutral-900) !important; }

        .swal2-html-container {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            padding: 20px !important;
        }
        .swal2-html-container p { margin: 0 !important; padding: 0 !important; text-align: center !important; }
        .swal2-popup.swal2-show { display: flex !important; flex-direction: column !important; align-items: center !important; }
        .swal2-title { text-align: center !important; }

        .loading-icon { font-size: var(--font-size-5xl) !important; color: var(--primary-blue-main) !important; animation: pulse 1.5s ease-in-out infinite !important; }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.1); }
        }
    </style>

    <link rel="stylesheet" href="{{ asset_v('css/page-loading.css') }}">
    <link rel="stylesheet" href="{{ asset_v('css/site-footer.css') }}">

    @stack('styles')

    {{-- SweetAlert Global Styles untuk Admin --}}
    @include('admin.partials.sweetalert-styles')
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    @include('components.page-loading')

    @include('components.admin.sidebar')

    <main class="main-content ml-64 min-h-screen flex flex-col">
        @include('components.admin.navbar')

        <div class="p-6 flex-1 min-h-0 has-fixed-site-footer--admin">
            {{-- Success message displayed as toast via SwalHelper.success() in DOMContentLoaded below --}}

            {{-- Info message displayed as toast via SwalHelper.info() in DOMContentLoaded below --}}

            {{-- Warning message displayed as toast via SwalHelper.warning() in DOMContentLoaded below --}}

            @yield('content')
        </div>

        @include('components.admin.footer')
    </main>

    @if(auth()->check())
        <script src="{{ asset('js/auto-logout.js') }}"></script>
    @endif

    {{-- Sidebar Toggle Script --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const sidebarToggle = document.getElementById('sidebarToggle');

        if (sidebarToggle && sidebar && mainContent) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');

                // Toggle icon
                const icon = sidebarToggle.querySelector('i');
                if (sidebar.classList.contains('collapsed')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-chevron-right');
                } else {
                    icon.classList.remove('fa-chevron-right');
                    icon.classList.add('fa-bars');
                }
            });
        }

        // Handle dropdown menus in sidebar
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        dropdownToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Close other dropdowns
                dropdownToggles.forEach(function(otherToggle) {
                    if (otherToggle !== toggle) {
                        otherToggle.classList.remove('active');
                        const menu = otherToggle.nextElementSibling;
                        if (menu && menu.classList.contains('dropdown-menu')) {
                            menu.classList.remove('active');
                        }
                    }
                });

                // Toggle current dropdown
                this.classList.toggle('active');
                const menu = this.nextElementSibling;
                if (menu && menu.classList.contains('dropdown-menu')) {
                    menu.classList.toggle('active');
                }
            });
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const navbar = document.querySelector('header');

            if (window.innerWidth < 1024 && sidebar && !sidebar.contains(e.target) && !sidebarToggle?.contains(e.target) && !navbar?.contains(e.target)) {
                // Optional: Close sidebar on mobile when clicking outside
            }
        });
    });
    </script>

    <script>
    if (typeof window.SwalHelper === 'undefined') {
        window.SwalHelper = {

            _toast: function(icon, iconColor, timerMs, title, customClass, message) {
                Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    backdrop: false,
                    background: 'var(--neutral-white)',
                    customClass: { popup: customClass, title: 'swal2-toast-title' },
                    didOpen: function(toast) {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                }).fire({
                    icon: icon,
                    iconColor: iconColor,
                    title: title,
                    html: message ? '<p class="text-gray-600 text-sm mt-1">' + message + '</p>' : undefined
                });
            },

            success: function(message) {
                this._toast('success', 'var(--success-green)', 5000, message, 'swal2-toast');
            },

            error: function(title, message) {
                this._toast('error', 'var(--danger-red)', 5000, title || 'Terjadi kesalahan', 'swal2-toast', message || 'Periksa data yang Anda masukkan, lalu coba lagi.');
            },

            info: function(message) {
                this._toast('info', 'var(--info-blue)', 5000, message, 'swal2-toast');
            },

            warning: function(message) {
                this._toast('warning', 'var(--warning-orange)', 5000, message, 'swal2-toast');
            },

            confirm: function(title, text, callback) {
                Swal.fire({
                    title: title,
                    html: '<p class="text-gray-600">' + text + '</p>',
                    showCancelButton: true,
                    showDenyButton: false,
                    confirmButtonText: 'Konfirmasi',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        popup: 'swal2-popup swal2-modal',
                        confirmButton: 'swal-btn-primary',
                        cancelButton: 'swal-btn-cancel'
                    }
                }).then(function(result) {
                    if (result.isConfirmed && callback) callback();
                });
            },

            deleteConfirm: function(title, text, callback) {
                Swal.fire({
                    title: title,
                    html: '<p class="text-gray-600">' + text + '</p>',
                    icon: false,
                    showCancelButton: true,
                    showDenyButton: false,
                    confirmButtonText: 'Konfirmasi',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        popup: 'swal2-popup swal2-modal',
                        confirmButton: 'swal-btn-delete',
                        cancelButton: 'swal-btn-cancel'
                    }
                }).then(function(result) {
                    if (result.isConfirmed && callback) callback();
                });
            },

            customConfirm: function(options) {
                var defaults = {
                    title: 'Konfirmasi',
                    message: 'Apakah Anda yakin ingin melanjutkan?',
                    subMessage: '',
                    iconClass: 'fas fa-question-circle',
                    iconColor: 'var(--success-green)',
                    confirmText: 'Ya, Lanjutkan',
                    confirmColor: 'var(--success-green)',
                    cancelText: 'Batal',
                    cancelColor: 'var(--neutral-600)',
                    reverseButtons: true,
                    onConfirm: null,
                    onCancel: null,
                    loadingTitle: 'Memproses',
                    loadingMessage: 'Mohon tunggu...',
                    showLoadingAfterConfirm: true
                };
                var cfg = Object.assign({}, defaults, options);
                var confirmButtonClass = 'swal-btn-primary';
                if (cfg.confirmColor === 'var(--danger-red)' || cfg.iconColor === 'var(--danger-red)') {
                    confirmButtonClass = 'swal-btn-delete';
                } else if (cfg.confirmColor === 'var(--success-green)' || cfg.iconColor === 'var(--success-green)') {
                    confirmButtonClass = 'swal-btn-success';
                }

                var html = '<div class="text-center">'
                    + '<p class="text-gray-600 text-sm mb-2">' + cfg.message + '</p>';
                if (cfg.subMessage) html += '<p class="text-gray-500 text-sm">' + cfg.subMessage + '</p>';
                html += '</div>';

                Swal.fire({
                    title: cfg.title,
                    html: html,
                    icon: false,
                    showCancelButton: true,
                    showDenyButton: false,
                    confirmButtonText: 'Konfirmasi',
                    cancelButtonText: 'Batal',
                    reverseButtons: cfg.reverseButtons,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        popup: 'swal2-popup swal2-modal',
                        confirmButton: confirmButtonClass,
                        cancelButton: 'swal-btn-cancel'
                    }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        if (cfg.showLoadingAfterConfirm) {
                            Swal.fire({
                                title: cfg.loadingTitle,
                                html: '<div class="loading-icon"><i class="fas fa-circle-notch fa-spin"></i></div>'
                                    + '<p class="text-gray-600 mt-4">' + cfg.loadingMessage + '</p>',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                showDenyButton: false,
                                showCancelButton: false,
                                didOpen: function() { Swal.showLoading(); },
                                customClass: { popup: 'swal2-popup swal2-modal', htmlContainer: 'swal2-html-container' }
                            });
                        }
                        if (typeof cfg.onConfirm === 'function') cfg.onConfirm();
                    } else {
                        if (typeof cfg.onCancel === 'function') cfg.onCancel();
                    }
                });
            },

            _pauseResume: function(pause) {
                if (pause && window.pauseAutoLogoutReset) window.pauseAutoLogoutReset();
                if (!pause && window.resumeAutoLogoutReset) window.resumeAutoLogoutReset();
            },

            confirmStart: function(title, message, subMessage, onConfirm, onCancel) {
                this._pauseResume(true);
                var self = this;
                this.customConfirm({
                    title: title, message: message, subMessage: subMessage,
                    iconClass: 'fas fa-play-circle', iconColor: 'var(--success-green)',
                    confirmText: 'Ya, Mulai', confirmColor: 'var(--success-green)',
                    loadingTitle: 'Memproses', loadingMessage: 'Sedang memproses permintaan...',
                    onConfirm: onConfirm,
                    onCancel: function() { self._pauseResume(false); if (onCancel) onCancel(); }
                });
            },

            confirmDelete: function(title, message, subMessage, onConfirm, onCancel) {
                this._pauseResume(true);
                var self = this;
                this.customConfirm({
                    title: title, message: message, subMessage: subMessage,
                    iconClass: 'fas fa-trash', iconColor: 'var(--danger-red)',
                    confirmText: 'Ya, Hapus', confirmColor: 'var(--danger-red)',
                    loadingTitle: 'Menghapus', loadingMessage: 'Sedang menghapus data...',
                    onConfirm: onConfirm,
                    onCancel: function() { self._pauseResume(false); if (onCancel) onCancel(); }
                });
            },

            confirmSave: function(title, message, subMessage, onConfirm, onCancel) {
                this._pauseResume(true);
                var self = this;
                this.customConfirm({
                    title: title, message: message, subMessage: subMessage,
                    iconClass: 'fas fa-save', iconColor: 'var(--success-green)',
                    confirmText: 'Ya, Simpan', confirmColor: 'var(--success-green)',
                    loadingTitle: 'Menyimpan', loadingMessage: 'Sedang menyimpan data...',
                    onConfirm: onConfirm,
                    onCancel: function() { self._pauseResume(false); if (onCancel) onCancel(); }
                });
            },

            confirmUpdate: function(title, message, subMessage, onConfirm, onCancel) {
                this._pauseResume(true);
                var self = this;
                this.customConfirm({
                    title: title, message: message, subMessage: subMessage,
                    iconClass: 'fas fa-sync', iconColor: 'var(--primary-blue-main)',
                    confirmText: 'Ya, Update', confirmColor: 'var(--primary-blue-main)',
                    loadingTitle: 'Memperbarui', loadingMessage: 'Sedang memperbarui data...',
                    onConfirm: onConfirm,
                    onCancel: function() { self._pauseResume(false); if (onCancel) onCancel(); }
                });
            },

            confirmLogout: function(title, message, subMessage, onConfirm, onCancel) {
                this._pauseResume(true);
                var self = this;
                this.customConfirm({
                    title: title, message: message, subMessage: subMessage,
                    iconClass: 'fas fa-sign-out-alt', iconColor: 'var(--danger-red)',
                    confirmText: 'Ya, Keluar', confirmColor: 'var(--danger-red)',
                    loadingTitle: 'Memproses Logout', loadingMessage: 'Sedang mengakhiri session...',
                    onConfirm: onConfirm,
                    onCancel: function() { self._pauseResume(false); if (onCancel) onCancel(); }
                });
            },

            notifySuccess: function(title, message, subMessage, callback) {
                this.customConfirm({
                    title: title, message: message, subMessage: subMessage,
                    iconClass: 'fas fa-check-circle', iconColor: 'var(--success-green)',
                    confirmText: 'OK', confirmColor: 'var(--success-green)',
                    cancelText: 'Tutup', showLoadingAfterConfirm: false,
                    onConfirm: callback, onCancel: callback
                });
            },

            notifyError: function(title, message, subMessage, callback) {
                this.customConfirm({
                    title: title, message: message, subMessage: subMessage,
                    iconClass: 'fas fa-times-circle', iconColor: 'var(--danger-red)',
                    confirmText: 'OK', confirmColor: 'var(--danger-red)',
                    cancelText: 'Tutup', showLoadingAfterConfirm: false,
                    onConfirm: callback, onCancel: callback
                });
            },

            notifyWarning: function(title, message, subMessage, callback) {
                this.customConfirm({
                    title: title, message: message, subMessage: subMessage,
                    iconClass: 'fas fa-exclamation-triangle', iconColor: 'var(--warning-orange)',
                    confirmText: 'OK', confirmColor: 'var(--warning-orange)',
                    cancelText: 'Tutup', showLoadingAfterConfirm: false,
                    onConfirm: callback, onCancel: callback
                });
            },

            _modal: function(bgColor, iconClass, iconColor, btnClass, title, message, callback) {
                Swal.fire({
                    title: title,
                    html: '<div class="text-center">'
                        + '<div class="mb-4"><div class="w-20 h-20 mx-auto ' + bgColor + ' rounded-full flex items-center justify-center">'
                        + '<i class="' + iconClass + ' text-4xl ' + iconColor + '"></i></div></div>'
                        + '<p class="text-gray-600 text-lg">' + message + '</p></div>',
                    icon: false,
                    confirmButtonText: '<i class="fas fa-check mr-2"></i>OK',
                    customClass: {
                        popup: 'swal2-popup swal2-modal',
                        confirmButton: btnClass
                    }
                }).then(function(result) {
                    if (result.isConfirmed && callback) callback();
                });
            },

            modalSuccess: function(title, message, callback) {
                this._modal('bg-green-100', 'fas fa-check', 'text-green-500', 'swal-btn-success', title, message, callback);
            },

            modalError: function(title, message, callback) {
                this._modal('bg-red-100', 'fas fa-times', 'text-red-500', 'swal-btn-delete', title, message, callback);
            },

            modalWarning: function(title, message, callback) {
                this._modal('bg-yellow-100', 'fas fa-exclamation-triangle', 'text-yellow-500', 'swal-btn-primary', title, message, callback);
            },
            loading: function(message) {
                Swal.fire({
                    title: message || 'Memuat...',
                    html: '<div class="loading-icon"><i class="fas fa-circle-notch fa-spin"></i></div>'
                        + '<p class="text-gray-600 mt-4">Mohon tunggu sebentar...</p>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    showDenyButton: false,
                    showCancelButton: false,
                    didOpen: function() { Swal.showLoading(); },
                    customClass: { popup: 'swal2-popup swal2-modal', htmlContainer: 'swal2-html-container' }
                });
            },

            close: function() { Swal.close(); },

            successModal: function(title, message, callback) { this.modalSuccess(title, message, callback); },
            actionConfirm: function(options) { this.customConfirm(options); }
        };
    }
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('login_success'))
            SwalHelper.toastSuccess(@json(session('login_success')));
        @endif

        @if(session('success'))
            SwalHelper.toastSuccess(@json(session('success')));
        @endif

        @if($errors->any())
            SwalHelper.toastError(@json($errors->first()), 'Pastikan semua field wajib telah diisi dengan benar.');
        @endif

        @if(session('error'))
            @php($flashError = session('error'))
            SwalHelper.toastError(
                @json(is_array($flashError) ? ($flashError['message'] ?? $flashError['title'] ?? 'Terjadi kesalahan') : $flashError),
                @json(session('error_solution') ?? 'Periksa data yang Anda masukkan, lalu coba lagi.')
            );
        @endif

        @if(session('warning'))
            SwalHelper.toastError(@json(session('warning')), 'Periksa kembali data atau aksi yang dilakukan, lalu coba lagi.');
        @endif

        @if(session('info'))
            SwalHelper.toastError(@json(session('info')), 'Periksa kembali informasi yang ditampilkan, lalu lanjutkan.');
        @endif
    });
    </script>

    {{-- Custom toast (override SwalHelper toast methods) --}}
    <script src="{{ asset('js/disdukcapil-toast.js') }}"></script>
    <script src="{{ asset_v('js/page-loading.js') }}"></script>
    <script src="{{ asset_v('js/style-guide-enhancer.js') }}"></script>

    {{-- @stack dipanggil SETELAH SwalHelper didefinisikan --}}
    @stack('scripts')

    <script>
        (function() {
            if (typeof window.__nativeAlert === 'function') return;
            window.__nativeAlert = window.alert.bind(window);
            window.__nativeConfirm = window.confirm.bind(window);
            var classify = function(msg) {
                var s = String(msg || '');
                if (/berhasil|sukses|success|tersimpan|terkirim/i.test(s)) return 'success';
                return 'error';
            };
            var toToast = function(type, msg) {
                if (typeof fireToast !== 'function') return false;
                if (type === 'success') {
                    fireToast({ type: 'success', icon: 'success', title: String(msg || 'Berhasil'), timer: 5000 });
                    return true;
                }
                fireToast({
                    type: 'error', icon: 'error', title: 'Terjadi kesalahan',
                    problem: String(msg || 'Terjadi kesalahan saat memproses permintaan.'),
                    solution: 'Periksa data yang dimasukkan dan coba lagi.',
                    timer: 5000
                });
                return true;
            };
            window.alert = function(msg) {
                if (toToast(classify(msg), msg)) return;
                window.__nativeAlert(msg);
            };
            window.__nativeConfirm = window.confirm;
            window.confirm = function(msg) {
                if (typeof notifKonfirmasi === 'function') {
                    return new Promise(function(resolve) {
                        notifKonfirmasi(String(msg || 'Apakah Anda yakin?'), function() { resolve(true); }, function() { resolve(false); });
                    });
                }
                return window.__nativeConfirm(msg);
            };
        })();
    </script>

</body>
</html>
