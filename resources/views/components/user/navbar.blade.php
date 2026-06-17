@php
    $adminBelumAda = ! \App\Models\User::whereHas('roles', function ($q) { $q->where('name', 'Admin'); })->exists();
@endphp
{{-- Header Navigation --}}
<header class="user-public-header relative z-50 bg-white/95 backdrop-blur-md shadow-sm shrink-0" id="mainHeader">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 gap-2">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 sm:gap-3 min-w-0 hover:scale-[1.02] transition-transform" data-style-guide-skip>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white rounded-xl flex items-center justify-center shadow-md overflow-hidden border-2 border-blue-200 shrink-0">
                    <img src="{{ asset('images/logo_toba.jpeg') }}" alt="Logo Kabupaten Toba" class="w-full h-full object-contain">
                </div>
                <div class="min-w-0">
                    <span class="block text-sm sm:text-lg font-bold text-gray-800 truncate">Disdukcapil Toba</span>
                    <p class="text-[11px] sm:text-xs text-gray-500 -mt-0.5 truncate">Kabupaten Toba</p>
                </div>
            </a>

            {{-- Desktop Navigation --}}
            <nav class="hidden md:flex items-center gap-1 user-public-nav" aria-label="Navigasi utama">
                <a href="{{ route('home') }}" data-style-guide-skip class="user-nav-link px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('home') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                    <i class="fas fa-home mr-2" aria-hidden="true"></i>Beranda
                </a>
                <a href="{{ route('antrian-online') }}" data-style-guide-skip class="user-nav-link px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('antrian-online*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                    <i class="fas fa-ticket-alt mr-2" aria-hidden="true"></i>Antrian Online
                </a>
                <a href="{{ route('layanan-mandiri') }}" data-style-guide-skip class="user-nav-link px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('layanan-mandiri*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                    <i class="fas fa-rocket mr-2" aria-hidden="true"></i>Layanan Mandiri
                </a>
                <a href="{{ route('statistik') }}" data-style-guide-skip class="user-nav-link px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('statistik') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                    <i class="fas fa-chart-line mr-2" aria-hidden="true"></i>Statistik
                </a>
                @auth
                    <a href="{{ route('logout') }}"
                        data-style-guide-skip
                        class="user-nav-link px-4 py-2 rounded-lg text-sm font-medium bg-red-600 text-white hover:bg-red-700 transition inline-flex items-center gap-2"
                        onclick="event.preventDefault(); handleUserLogout('logoutForm');">
                        <i class="fas fa-sign-out-alt" aria-hidden="true"></i><span>Logout</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="hidden">
                        @csrf
                    </form>
                @else
                    @if($adminBelumAda)
                        <a href="{{ route('admin.register') }}" data-style-guide-skip class="user-nav-link px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.register') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                            <i class="fas fa-user-shield mr-2" aria-hidden="true"></i>Register Admin
                        </a>
                    @else
                        <a href="{{ route('login') }}" data-style-guide-skip class="user-nav-link user-nav-link-login px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('login*') ? 'text-green-700 bg-green-50 border border-green-200' : 'text-green-700 bg-white border border-green-300 hover:bg-green-50' }} transition inline-flex items-center gap-2">
                            <i class="fas fa-sign-in-alt" aria-hidden="true"></i><span>Login</span>
                        </a>
                    @endif
                @endauth
            </nav>

            {{-- Mobile Menu Button --}}
            <button id="mobileMenuBtn" type="button" class="md:hidden p-2.5 rounded-lg hover:bg-gray-100 min-h-[44px] min-w-[44px] flex items-center justify-center shrink-0" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="mobileMenu">
                <i class="fas fa-bars text-gray-600 text-lg"></i>
            </button>
        </div>
    </div>

    {{-- Mobile Navigation --}}
    <div id="mobileMenu" class="md:hidden hidden bg-white border-t shadow-sm" role="navigation" aria-label="Menu mobile">
        <nav class="user-public-nav px-3 py-3 space-y-1 max-h-[70vh] overflow-y-auto">
            <a href="{{ route('home') }}" data-style-guide-skip class="user-nav-link flex items-center gap-2 px-4 py-3 rounded-lg text-sm font-medium min-h-[44px] {{ request()->routeIs('home') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }}">
                <i class="fas fa-home w-5 text-center" aria-hidden="true"></i><span>Beranda</span>
            </a>
            <a href="{{ route('antrian-online') }}" data-style-guide-skip class="user-nav-link flex items-center gap-2 px-4 py-3 rounded-lg text-sm font-medium min-h-[44px] {{ request()->routeIs('antrian-online*') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }}">
                <i class="fas fa-ticket-alt w-5 text-center" aria-hidden="true"></i><span>Antrian Online</span>
            </a>
            <a href="{{ route('layanan-mandiri') }}" data-style-guide-skip class="user-nav-link flex items-center gap-2 px-4 py-3 rounded-lg text-sm font-medium min-h-[44px] {{ request()->routeIs('layanan-mandiri*') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }}">
                <i class="fas fa-rocket w-5 text-center" aria-hidden="true"></i><span>Layanan Mandiri</span>
            </a>
            <a href="{{ route('statistik') }}" data-style-guide-skip class="user-nav-link flex items-center gap-2 px-4 py-3 rounded-lg text-sm font-medium min-h-[44px] {{ request()->routeIs('statistik') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }}">
                <i class="fas fa-chart-line w-5 text-center" aria-hidden="true"></i><span>Statistik</span>
            </a>
            @auth
                <a href="{{ route('logout') }}"
                    data-style-guide-skip
                    class="user-nav-link flex items-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold min-h-[44px] bg-red-600 text-white hover:bg-red-700"
                    onclick="event.preventDefault(); handleUserLogout('logoutFormMobile');">
                    <i class="fas fa-sign-out-alt w-5 text-center" aria-hidden="true"></i><span>Logout</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" id="logoutFormMobile" class="hidden">
                    @csrf
                </form>
            @else
                @if($adminBelumAda)
                    <a href="{{ route('admin.register') }}" data-style-guide-skip class="user-nav-link flex items-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold min-h-[44px] {{ request()->routeIs('admin.register') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }}">
                        <i class="fas fa-user-shield w-5 text-center" aria-hidden="true"></i><span>Register Admin</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" data-style-guide-skip class="user-nav-link user-nav-link-login flex items-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold min-h-[44px] text-green-700 bg-green-50 border border-green-200 hover:bg-green-100">
                        <i class="fas fa-sign-in-alt w-5 text-center" aria-hidden="true"></i><span>Login</span>
                    </a>
                @endif
            @endauth
        </nav>
    </div>
</header>

<style>
    /* Navigasi publik: jangan di-styling ulang sebagai tombol aksi oleh style guide */
    .user-public-header .user-nav-link,
    .user-public-header .user-nav-link span,
    .user-public-header .user-nav-link i {
        text-decoration: none !important;
    }
    .user-public-header .user-nav-link-login {
        background-color: #f0fdf4 !important;
        color: #15803d !important;
        border: 1px solid #86efac !important;
        box-shadow: none !important;
        transform: none !important;
    }
    .user-public-header .user-nav-link-login:hover {
        background-color: #dcfce7 !important;
        color: #166534 !important;
    }
</style>

<script>
    // Mobile Menu Toggle
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', function() {
                var isOpen = !mobileMenu.classList.contains('hidden');
                mobileMenu.classList.toggle('hidden');
                mobileMenuBtn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            });

            mobileMenu.querySelectorAll('a.user-nav-link').forEach(function(link) {
                link.addEventListener('click', function() {
                    mobileMenu.classList.add('hidden');
                    mobileMenuBtn.setAttribute('aria-expanded', 'false');
                });
            });
        }
    });

    // Logout konfirmasi (konsisten dengan admin/keagamaan/login)
    function handleUserLogout(formId) {
        var doSubmit = function () {
            var f = document.getElementById(formId);
            if (f) f.submit();
        };
        if (window.SwalHelper && typeof window.SwalHelper.customConfirm === 'function') {
            window.SwalHelper.customConfirm({
                title: 'Konfirmasi Logout',
                message: 'Sesi Anda akan diakhiri dan Anda akan kembali ke halaman login.',
                subMessage: 'Apakah Anda yakin ingin melanjutkan?',
                iconClass: 'fas fa-sign-out-alt',
                iconColor: '#dc2626',
                confirmText: 'Konfirmasi',
                confirmColor: '#dc2626',
                loadingTitle: 'Memproses Logout',
                loadingMessage: 'Sedang mengakhiri sesi...',
                onConfirm: function () { setTimeout(doSubmit, 600); }
            });
        } else if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                icon: false,
                title: 'Konfirmasi Logout',
                html: '<p class="text-gray-600 text-sm">Sesi Anda akan diakhiri dan Anda akan kembali ke halaman login. Apakah Anda yakin ingin melanjutkan?</p>',
                showCancelButton: true,
                showDenyButton: false,
                confirmButtonText: 'Konfirmasi',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(function (r) { if (r.isConfirmed) doSubmit(); });
        } else {
            doSubmit();
        }
    }
</script>
