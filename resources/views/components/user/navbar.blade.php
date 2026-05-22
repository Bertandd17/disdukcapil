@php
    $adminBelumAda = ! \App\Models\User::whereHas('roles', function ($q) { $q->where('name', 'Admin'); })->exists();
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
                <a href="{{ route('home') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('home') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                    <i class="fas fa-home mr-2"></i>Beranda
                </a>
                <a href="{{ route('antrian-online') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('antrian-online*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                    <i class="fas fa-ticket-alt mr-2"></i>Antrian Online
                </a>
                <a href="{{ route('layanan-mandiri') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('layanan-mandiri*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                    <i class="fas fa-rocket mr-2"></i>Layanan Mandiri
                </a>
                <a href="{{ route('statistik') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('statistik') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                    <i class="fas fa-chart-line mr-2"></i>Statistik
                </a>
                @auth
                    <a href="{{ route('logout') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 transition" onclick="event.preventDefault(); handleUserLogout('logoutForm');">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                    <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="hidden">
                        @csrf
                    </form>
                @else
                    @if($adminBelumAda)
                        <a href="{{ route('admin.register') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.register') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} transition">
                            <i class="fas fa-user-shield mr-2"></i>Register Admin
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('login*') ? 'text-green-600 bg-green-50' : 'text-gray-600 hover:text-green-600 hover:bg-green-50' }} transition">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    @endif
                @endauth
            </nav>

            {{-- Mobile Menu Button --}}
            <button id="mobileMenuBtn" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-bars text-gray-600"></i>
            </button>
        </div>
    </div>

    {{-- Mobile Navigation --}}
    <div id="mobileMenu" class="md:hidden hidden bg-white border-t">
        <nav class="px-4 py-3 space-y-1">
            <a href="{{ route('home') }}" class="block px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('home') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }}">
                <i class="fas fa-home mr-2"></i>Beranda
            </a>
            <a href="{{ route('antrian-online') }}" class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50">
                <i class="fas fa-ticket-alt mr-2"></i>Antrian Online
            </a>
            <a href="{{ route('layanan-mandiri') }}" class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50">
                <i class="fas fa-rocket mr-2"></i>Layanan Mandiri
            </a>
            <a href="{{ route('statistik') }}" class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50">
                <i class="fas fa-chart-line mr-2"></i>Statistik
            </a>
            @auth
                <a href="{{ route('logout') }}" class="block px-4 py-2 rounded-lg text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50" onclick="event.preventDefault(); handleUserLogout('logoutFormMobile');">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
                <form method="POST" action="{{ route('logout') }}" id="logoutFormMobile" class="hidden">
                    @csrf
                </form>
            @else
                @if($adminBelumAda)
                    <a href="{{ route('admin.register') }}" class="block px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.register') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }}">
                        <i class="fas fa-user-shield mr-2"></i>Register Admin
                    </a>
                @else
                    <a href="{{ route('login') }}" class="block px-4 py-2 rounded-lg text-sm font-medium text-green-600 hover:bg-green-50">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                @endif
            @endauth
        </nav>
    </div>
</header>

<div class="h-16"></div>

<script>
    // Mobile Menu Toggle
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
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
                confirmButtonText: 'Konfirmasi',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626',
                reverseButtons: true
            }).then(function (r) { if (r.isConfirmed) doSubmit(); });
        } else {
            doSubmit();
        }
    }
</script>
