<!-- Load Dependencies - SweetAlert dimuat oleh layout -->

@php
    $adminBelumAda = ! \App\Models\User::whereHas('roles', function ($q) {
        $q->where('name', 'Admin');
    })->exists();
@endphp

{{-- Header Navigation --}}
<header class="fixed top-0 left-0 right-0 bg-white/95 backdrop-blur-md shadow-sm z-50 transition-all duration-300" id="mainHeader">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-3 hover:scale-105 transition-transform">
                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-md overflow-hidden border-2 border-blue-200">
                    <img src="{{ asset('images/logo_toba.jpeg') }}" alt="Logo Kabupaten Toba" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="text-lg font-bold text-gray-800">Disdukcapil Toba</span>
                    <p class="text-xs text-gray-500 -mt-1">Kabupaten Toba</p>
                </div>
            </a>

            {{-- Desktop Navigation --}}
            <nav class="hidden md:flex items-center gap-1">
                <a href="{{ route('home') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('home') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                    <i class="fas fa-home mr-2"></i>Beranda
                </a>

                <a href="{{ route('antrian-online') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('antrian-online*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                    <i class="fas fa-ticket-alt mr-2"></i>Antrian Online
                </a>

                <a href="{{ route('layanan-mandiri') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('layanan-mandiri*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                    <i class="fas fa-rocket mr-2"></i>Layanan Mandiri
                </a>

                <a href="{{ route('statistik') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('statistik') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                    <i class="fas fa-chart-line mr-2"></i>Statistik
                </a>

                @auth
                    {{-- Form Logout Desktop --}}
                    <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="hidden">
                        @csrf
                    </form>

                    <a href="#"
                       data-style-guide-skip
                       class="px-4 py-2 rounded-lg text-sm font-medium bg-red-600 text-white hover:bg-red-700 transition flex items-center gap-2"
                       onclick="event.preventDefault(); handleUserLogout('logoutForm');">
                        <i class="fas fa-sign-out-alt"></i>Logout
                    </a>
                @else
                    @if($adminBelumAda)
                        <a href="{{ route('admin.register') }}"
                           class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.register') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                            <i class="fas fa-user-shield mr-2"></i>Register Admin
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('login*') ? 'text-green-600 bg-green-50' : 'text-gray-600 hover:text-green-600 hover:bg-green-50' }} transition">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    @endif
                @endauth
            </nav>

            {{-- Mobile Menu Button --}}
            <button id="mobileMenuBtn" type="button" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-bars text-gray-600"></i>
            </button>
        </div>
    </div>

    {{-- Mobile Navigation --}}
    <div id="mobileMenu" class="md:hidden hidden bg-white border-t">
        <nav class="px-4 py-3 space-y-1">
            <a href="{{ route('home') }}"
               class="block px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('home') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }}">
                <i class="fas fa-home mr-2"></i>Beranda
            </a>

            <a href="{{ route('antrian-online') }}"
               class="block px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('antrian-online*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }}">
                <i class="fas fa-ticket-alt mr-2"></i>Antrian Online
            </a>

            <a href="{{ route('layanan-mandiri') }}"
               class="block px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('layanan-mandiri*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }}">
                <i class="fas fa-rocket mr-2"></i>Layanan Mandiri
            </a>

            <a href="{{ route('statistik') }}"
               class="block px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('statistik') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }}">
                <i class="fas fa-chart-line mr-2"></i>Statistik
            </a>

            @auth
                {{-- Form Logout Mobile --}}
                <form method="POST" action="{{ route('logout') }}" id="logoutFormMobile" class="hidden">
                    @csrf
                </form>

                <button type="button"
                        data-style-guide-skip
                        class="sidebar-link w-full flex items-center gap-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition-all"
                        onclick="handleUserLogout('logoutFormMobile');">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span class="sidebar-text font-medium">Logout</span>
                </button>
            @else
                @if($adminBelumAda)
                    <a href="{{ route('admin.register') }}"
                       class="block px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.register') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }}">
                        <i class="fas fa-user-shield mr-2"></i>Register Admin
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="block px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('login*') ? 'text-green-600 bg-green-50' : 'text-green-600 hover:bg-green-50' }}">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                @endif
            @endauth
        </nav>
    </div>
</header>

<div class="h-16"></div>

<style>
    /*
    |--------------------------------------------------------------------------
    | SWEETALERT LOADING FIX KHUSUS NAVBAR
    |--------------------------------------------------------------------------
    | Tujuan:
    | - Menghapus tombol apa pun pada modal loading logout.
    | - Tidak mengganggu modal konfirmasi logout.
    */
    .swal-navbar-loading .swal2-actions,
    .swal-navbar-loading .swal2-confirm,
    .swal-navbar-loading .swal2-deny,
    .swal-navbar-loading .swal2-cancel,
    .swal-navbar-loading .swal2-styled {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        width: 0 !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
        pointer-events: none !important;
    }

    .swal-navbar-loading .swal2-actions {
        min-height: 0 !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function () {
            mobileMenu.classList.toggle('hidden');
        });
    }
});

function handleUserLogout(formId) {
    const logoutForm = document.getElementById(formId);

    if (!logoutForm) {
        return;
    }

    const logoutMessage = 'Sesi Anda akan diakhiri dan Anda akan kembali ke halaman login. Apakah Anda yakin ingin melanjutkan?';

    if (window.Swal && typeof window.Swal.fire === 'function') {
        Swal.fire({
            title: 'Konfirmasi Logout',
            html: '<p class="text-gray-600 text-sm leading-relaxed">' + logoutMessage + '</p>',
            icon: 'question',

            showCancelButton: true,
            showConfirmButton: true,
            showDenyButton: false,

            confirmButtonText: 'Konfirmasi',
            cancelButtonText: 'Batal',

            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',

            reverseButtons: true,
            allowOutsideClick: true,
            allowEscapeKey: true
        }).then(function (result) {
            if (result.isConfirmed) {
                showNavbarLogoutLoading();

                setTimeout(function () {
                    logoutForm.submit();
                }, 600);
            }
        });
    } else {
        logoutForm.submit();
    }
}

function showNavbarLogoutLoading() {
    Swal.fire({
        title: 'Memproses Logout',
        html: '<p class="text-gray-600 text-sm">Sedang mengakhiri sesi...</p>',

        icon: false,

        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,

        showConfirmButton: false,
        showDenyButton: false,
        showCancelButton: false,

        buttonsStyling: false,
        focusConfirm: false,
        focusCancel: false,

        customClass: {
            popup: 'swal-navbar-loading'
        },

        didOpen: function () {
            Swal.showLoading();

            const popup = Swal.getPopup();
            if (popup) {
                const actions = popup.querySelector('.swal2-actions');
                const confirmBtn = popup.querySelector('.swal2-confirm');
                const denyBtn = popup.querySelector('.swal2-deny');
                const cancelBtn = popup.querySelector('.swal2-cancel');

                if (actions) {
                    actions.style.display = 'none';
                    actions.style.visibility = 'hidden';
                    actions.style.height = '0';
                    actions.style.margin = '0';
                    actions.style.padding = '0';
                }

                if (confirmBtn) confirmBtn.remove();
                if (denyBtn) denyBtn.remove();
                if (cancelBtn) cancelBtn.remove();
            }
        }
    });
}
</script>