<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $page_title ?? 'Keagamaan Dashboard - Disdukcapil Kabupaten Toba' }}</title>

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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- SweetAlert global styles (toast transparan, tanpa backdrop abu-abu) --}}
    @include('admin.partials.sweetalert-styles')

    {{-- Notifikasi Disdukcapil (file final tunggal: toast top-end HIJAU/MERAH, modal putih, tanpa backdrop) --}}
    <script src="{{ asset('js/notifikasi-disdukcapil.js') }}?v={{ filemtime(public_path('js/notifikasi-disdukcapil.js')) }}"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- FullCalendar untuk Kalender -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

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
        * { font-family: 'Plus Jakarta Sans', 'sans-serif'; }

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

        .main-content { transition: all 0.3s ease; }
        .main-content.expanded { margin-left: 80px; }

        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .reveal { opacity: 0; transform: translateY(20px); transition: all 0.6s ease-out; }
        .reveal.active { opacity: 1; transform: translateY(0); }

        /* SweetAlert2 custom styles */
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

        .swal2-toast { font-family: 'Plus Jakarta Sans', sans-serif !important; border-radius: var(--radius-lg) !important; box-shadow: var(--shadow-xl) !important; background: var(--neutral-white) !important; }
        .swal2-toast .swal2-title,
        .swal2-toast .swal2-html-container { font-family: 'Plus Jakarta Sans', sans-serif !important; font-size: var(--font-size-sm) !important; font-weight: 400 !important; color: var(--neutral-900) !important; }

        .loading-icon { font-size: var(--font-size-5xl) !important; color: var(--primary-blue-main) !important; animation: pulse 1.5s ease-in-out infinite !important; }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.1); }
        }

        /* FullCalendar Custom Styles */
        .fc {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .fc .fc-toolbar-title {
            font-size: var(--font-size-xl) !important;
            font-weight: 700 !important;
            color: var(--neutral-800) !important;
        }
        .fc .fc-button {
            background-color: var(--primary-blue-main) !important;
            border-color: var(--primary-blue-main) !important;
            border-radius: var(--radius-md) !important;
            font-weight: 500 !important;
        }
        .fc .fc-button:hover {
            background-color: var(--primary-blue-dark) !important;
            border-color: var(--primary-blue-dark) !important;
        }
        .fc .fc-daygrid-day-number {
            color: var(--neutral-700) !important;
            font-weight: 500 !important;
        }
        .fc-event {
            border-radius: var(--radius-sm) !important;
            border: none !important;
            cursor: pointer !important;
        }
        .fc-event.pernikahan-event {
            background-color: var(--primary-blue-main) !important;
        }
        .fc-event.approved-event {
            background-color: var(--success-green) !important;
        }
        .fc-event.pending-event {
            background-color: var(--warning-orange) !important;
        }
        .fc-event.rejected-event {
            background-color: var(--danger-red) !important;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/page-loading.css') }}?v={{ filemtime(public_path('css/page-loading.css')) }}">

    {{-- SweetAlert Final Fix --}}
    <link rel="stylesheet" href="{{ asset('css/swal-final-fix.css') }}?v={{ filemtime(public_path('css/swal-final-fix.css')) }}">

    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    @include('components.page-loading')

    @include('components.keagamaan.sidebar')

    <main class="main-content ml-64 min-h-screen flex flex-col">
        @include('components.keagamaan.navbar')

        <div class="p-6 flex-1 min-h-0">
            {{-- Flash messages ditampilkan sebagai toast SweetAlert top-right pada DOMContentLoaded --}}
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
                    timer: timerMs,
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
                window.SwalHelper.konfirmasiDisdukcapil({
                    judul: title,
                    pesan: text,
                    tipe: 'konfirmasi',
                    labelOk: 'Konfirmasi',
                    onKonfirmasi: function() {
                        if (callback) callback();
                    }
                });
            },

            deleteConfirm: function(title, text, callback) {
                window.SwalHelper.konfirmasiDisdukcapil({
                    judul: title,
                    pesan: text,
                    tipe: 'hapus',
                    labelOk: 'Hapus',
                    onKonfirmasi: function() {
                        if (callback) callback();
                    }
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

                var pesanHtml = '<p class="text-gray-600 text-sm mb-2">' + cfg.message + '</p>';
                if (cfg.subMessage) pesanHtml += '<p class="text-gray-500 text-sm">' + cfg.subMessage + '</p>';

                var tipe = 'konfirmasi';
                if (cfg.confirmColor === 'var(--danger-red)' || cfg.iconColor === 'var(--danger-red)' || /fa-trash/.test(cfg.iconClass || '')) {
                    tipe = 'hapus';
                } else if (cfg.confirmColor === 'var(--warning-orange)' || cfg.iconColor === 'var(--warning-orange)') {
                    tipe = 'warning';
                }

                window.SwalHelper.konfirmasiDisdukcapil({
                    judul: cfg.title,
                    pesan: pesanHtml,
                    tipe: tipe,
                    labelOk: cfg.confirmText || 'Konfirmasi',
                    labelBatal: cfg.cancelText || 'Batal',
                    onKonfirmasi: function() {
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
                                customClass: { popup: 'swal2-popup swal2-modal', htmlContainer: 'swal2-html-container' }
                            });
                        }
                        if (typeof cfg.onConfirm === 'function') cfg.onConfirm();
                    },
                    onBatal: function() {
                        if (typeof cfg.onCancel === 'function') cfg.onCancel();
                    }
                });
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
                    customClass: { popup: 'swal2-popup swal2-modal', htmlContainer: 'swal2-html-container' }
                });
            },

            close: function() { Swal.close(); }
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('login_success'))
            if (typeof notifToast === 'function') {
                notifToast('success', 'Login Berhasil', @json(session('login_success')), 4000);
            } else {
                SwalHelper.success(@json(session('login_success')));
            }
        @endif

        @if(session('success'))
            SwalHelper.success(@json(session('success')));
        @endif

        @if(session('error'))
            @php($flashError = session('error'))
            SwalHelper.error(
                @json(is_array($flashError) ? ($flashError['title'] ?? 'Terjadi kesalahan') : 'Terjadi kesalahan'),
                @json(is_array($flashError) ? ($flashError['message'] ?? 'Periksa data yang Anda masukkan, lalu coba lagi.') : $flashError)
            );
        @endif

        @if(session('info'))
            SwalHelper.info(@json(session('info')));
        @endif

        @if(session('warning'))
            SwalHelper.warning(@json(session('warning')));
        @endif
    });
    </script>

    {{-- Toast API (window.Toast: sukses/error/warning/info) --}}
    <script src="{{ asset('js/toast-disdukcapil.js') }}?v={{ filemtime(public_path('js/toast-disdukcapil.js')) }}"></script>

    <script src="{{ asset('js/page-loading.js') }}?v={{ filemtime(public_path('js/page-loading.js')) }}"></script>
    <script src="{{ asset('js/style-guide-enhancer.js') }}?v={{ filemtime(public_path('js/style-guide-enhancer.js')) }}"></script>

    @stack('scripts')

    {{-- SweetAlert Final Fix (PALING AKHIR - setelah semua Swal dimuat) --}}
    <script src="{{ asset('js/swal-final-fix.js') }}?v={{ filemtime(public_path('js/swal-final-fix.js')) }}"></script>

</body>
</html>
