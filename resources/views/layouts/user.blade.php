<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page_title ?? 'Disdukcapil Kabupaten Toba' }}</title>
    <meta name="description" content="{{ $page_description ?? 'Layanan Kependudukan dan Pencatatan Sipil Kabupaten Toba' }}">

    <!-- User Authenticated Meta Tag -->
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_toba.jpeg') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('head')

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

    <!-- SweetAlert Helper -->
    <script src="{{ asset('js/sweetalert-helper.js') }}"></script>

    <!-- SweetAlert2 Disdukcapil Notification System -->
    <script src="{{ asset('js/sweetalert-disdukcapil.js') }}?v={{ filemtime(public_path('js/sweetalert-disdukcapil.js')) }}"></script>

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
                    },
                    colors: {
                        blue: {
                            50: 'var(--primary-blue-50)',
                            100: 'var(--primary-blue-100)',
                            200: 'var(--primary-blue-100)',
                            300: 'var(--primary-blue-light)',
                            400: 'var(--primary-blue-light)',
                            500: 'var(--primary-blue-main)',
                            600: 'var(--primary-blue-main)',
                            700: 'var(--primary-blue-dark)',
                            800: 'var(--primary-blue-dark)',
                            900: 'var(--primary-blue-dark)',
                        },
                        teal: {
                            500: 'var(--info-blue)',
                            600: 'var(--primary-blue-main)',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
            scroll-behavior: smooth;
        }

        body {
            overflow-x: hidden;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--neutral-100);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--primary-blue-main);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-blue-dark);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease-out;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Skeleton Loading */
        .skeleton {
            background: linear-gradient(90deg, var(--neutral-200) 25%, var(--neutral-100) 50%, var(--neutral-200) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 8px;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }

        /* Tabs */
        .tabs::-webkit-scrollbar {
            display: none;
        }
        .tabs {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .tab-btn.active {
            background-color: var(--primary-blue-main);
            color: white;
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/page-loading.css') }}?v={{ filemtime(public_path('css/page-loading.css')) }}">

    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    @include('components.page-loading')

    {{-- Navbar --}}
    @include('components.user.navbar')

    {{-- Flash messages ditampilkan sebagai toast SweetAlert top-right pada DOMContentLoaded --}}

    {{-- Content --}}
    <main class="flex-1 flex flex-col">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('components.user.footer')

    {{-- Scripts --}}
    @stack('scripts')

    {{-- Auto-Logout System --}}
    @if(auth()->check())
        <script src="{{ asset('js/auto-logout.js') }}"></script>
    @endif

    <script>
        // Scroll Reveal Animation
        function reveal() {
            const reveals = document.querySelectorAll('.reveal');
            reveals.forEach(element => {
                const windowHeight = window.innerHeight;
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;

                if (elementTop < windowHeight - elementVisible) {
                    element.classList.add('active');
                }
            });
        }

        window.addEventListener('scroll', reveal);
        reveal(); // Initial call

        // Header Scroll Effect
        let lastScroll = 0;
        const header = document.getElementById('mainHeader');

        if (header) {
            window.addEventListener('scroll', () => {
                const currentScroll = window.pageYOffset;

                if (currentScroll > 50) {
                    header.classList.add('shadow-lg');
                } else {
                    header.classList.remove('shadow-lg');
                }

                lastScroll = currentScroll;
            });
        }

        // SwalHelper sudah didefinisikan di sweetalert-helper.js
        // Jangan replace jika sudah ada
        if (typeof window.SwalHelper === 'undefined') {
            window.SwalHelper = {
            _toast: function(icon, title, message) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    backdrop: false,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
                Toast.fire({
                    icon: icon,
                    title: title,
                    html: message ? '<p class="text-sm text-gray-600 mt-1">' + message + '</p>' : undefined
                });
            },

            // Success Toast
            success: function(message) {
                this._toast('success', message, null);
            },

            // Error Toast
            error: function(title, message) {
                this._toast('error', title || 'Terjadi kesalahan', message || 'Periksa data yang Anda masukkan, lalu coba lagi.');
            },

            // Info Toast
            info: function(message) {
                this._toast('info', message, null);
            },

            // Warning Toast
            warning: function(message) {
                this._toast('warning', message, null);
            },

            // Confirm Dialog
            confirm: function(title, text, callback) {
                Swal.fire({
                    title: title,
                    html: '<p class="text-gray-600 text-sm">' + text + '</p>',
                    showCancelButton: true,
                    confirmButtonColor: 'var(--success-green)',
                    cancelButtonColor: 'var(--neutral-600)',
                    confirmButtonText: 'Konfirmasi',
                    cancelButtonText: 'Batal',
                    showClass: {
                        popup: 'swal2-show',
                        backdrop: 'swal2-backdrop-show',
                        icon: 'swal2-icon-show'
                    },
                    hideClass: {
                        popup: 'swal2-hide',
                        backdrop: 'swal2-backdrop-hide',
                        icon: 'swal2-icon-hide'
                    }
                }).then((result) => {
                    if (result.isConfirmed && callback) {
                        callback();
                    }
                });
            },

            // Delete Confirm
            deleteConfirm: function(title, text, callback) {
                Swal.fire({
                    title: title,
                    html: '<p class="text-gray-600 text-sm">' + text + '</p>',
                    icon: false,
                    showCancelButton: true,
                    confirmButtonColor: 'var(--danger-red)',
                    cancelButtonColor: 'var(--neutral-600)',
                    confirmButtonText: 'Konfirmasi',
                    cancelButtonText: 'Batal',
                    showClass: {
                        popup: 'swal2-show',
                        backdrop: 'swal2-backdrop-show',
                        icon: 'swal2-icon-show'
                    },
                    hideClass: {
                        popup: 'swal2-hide',
                        backdrop: 'swal2-backdrop-hide',
                        icon: 'swal2-icon-hide'
                    }
                }).then((result) => {
                    if (result.isConfirmed && callback) {
                        callback();
                    }
                });
            },

            // Loading
            loading: function(message = 'Memuat...') {
                Swal.fire({
                    title: message,
                    html: '<div class="loading-icon"><i class="fas fa-circle-notch fa-spin"></i></div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    showDenyButton: false,
                    showCancelButton: false,
                    didOpen: function() { Swal.showLoading(); },
                    customClass: {
                        popup: 'swal2-modal-popup',
                        htmlContainer: 'swal2-html-container'
                    }
                });
            },

            // Close Loading
            close: function() {
                Swal.close();
            }
        };
        } // end if SwalHelper undefined

        // Show SweetAlert for session messages on page load
        document.addEventListener('DOMContentLoaded', function() {
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

    {{-- Custom toast (override SwalHelper toast methods) --}}
    <script src="{{ asset('js/disdukcapil-toast.js') }}"></script>
    <script src="{{ asset('js/page-loading.js') }}?v={{ filemtime(public_path('js/page-loading.js')) }}"></script>
    <script src="{{ asset('js/style-guide-enhancer.js') }}?v={{ filemtime(public_path('js/style-guide-enhancer.js')) }}"></script>
</body>
</html>
