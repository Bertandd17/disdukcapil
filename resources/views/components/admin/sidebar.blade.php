{{-- Admin Sidebar --}}
<aside class="sidebar fixed left-0 top-0 h-full w-64 bg-white border-r border-gray-200 z-50 shadow-lg">
    {{-- Logo --}}
    <div class="h-16 flex items-center px-6 border-b border-gray-100">
        <div class="w-10 h-10 rounded-xl overflow-hidden flex-shrink-0">
            <img src="{{ asset('images/logo_toba.jpeg') }}" alt="Logo Kabupaten Toba" class="w-full h-full object-contain">
        </div>
        <span class="sidebar-text logo-text ml-3 font-bold text-lg text-gray-800">Disdukcapil</span>
    </div>

    {{-- Navigation --}}
    <nav class="p-4 space-y-1 overflow-y-auto h-[calc(100vh-4rem)]">
        <a href="{{ route('admin.dashboard') }}"
            class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-home w-5"></i>
            <span class="sidebar-text font-medium">Dashboard</span>
        </a>

        <div class="pt-4 pb-2">
            <p class="sidebar-text px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Manajemen</p>
        </div>

        <a href="{{ route('admin.berita') }}"
            class="sidebar-link {{ request()->routeIs('admin.berita*') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-newspaper w-5"></i>
            <span class="sidebar-text font-medium">Kelola Berita</span>
        </a>
        <a href="{{ route('admin.organisasi.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.organisasi*') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-sitemap w-5"></i>
            <span class="sidebar-text font-medium">Organisasi</span>
        </a>
        <a href="{{ route('admin.penghargaan') }}"
            class="sidebar-link {{ request()->routeIs('admin.penghargaan') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-trophy w-5"></i>
            <span class="sidebar-text font-medium">Penghargaan</span>
        </a>
        <a href="{{ route('admin.dasar-hukum') }}"
            class="sidebar-link {{ request()->routeIs('admin.dasar-hukum') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-balance-scale w-5"></i>
            <span class="sidebar-text font-medium">Dasar Hukum</span>
        </a>
        {{-- Statistik Dropdown --}}
        <div class="statistik-dropdown">
            <a href="#"
                class="sidebar-link dropdown-toggle flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 {{ request()->routeIs('admin.statistik*') ? 'active' : '' }}">
                <div class="flex items-center gap-3">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span class="sidebar-text font-medium">Statistik</span>
                </div>
                <i class="fas fa-chevron-down text-xs"></i>
            </a>
            <div class="dropdown-menu {{ request()->routeIs('admin.statistik*') ? 'active' : '' }}">
                <a href="{{ route('admin.statistik-penduduk.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.statistik-penduduk.*') ? 'active' : '' }} flex items-center gap-3 px-4 py-2 rounded-lg text-gray-700 text-sm">
                    <i class="fas fa-users w-4"></i>
                    <span class="sidebar-text">Statistik Penduduk</span>
                </a>
                <a href="{{ route('admin.statistik-dokumen.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.statistik-dokumen.*') ? 'active' : '' }} flex items-center gap-3 px-4 py-2 rounded-lg text-gray-700 text-sm">
                    <i class="fas fa-file-alt w-4"></i>
                    <span class="sidebar-text">Statistik Dokumen</span>
                </a>
                <a href="{{ route('admin.statistik-layanan.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.statistik-layanan.*') ? 'active' : '' }} flex items-center gap-3 px-4 py-2 rounded-lg text-gray-700 text-sm">
                    <i class="fas fa-clipboard-list w-4"></i>
                    <span class="sidebar-text">Statistik Layanan</span>
                </a>
            </div>
        </div>

        <div class="pt-4 pb-2">
            <p class="sidebar-text px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Layanan</p>
        </div>

        <a href="{{ route('admin.antrian-online') }}"
            class="sidebar-link {{ request()->routeIs('admin.antrian-online') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-ticket-alt w-5"></i>
            <span class="sidebar-text font-medium">Antrian Online</span>
        </a>

        {{-- Kelola Layanan Dropdown --}}
        <div class="layanan-dropdown">
            <a href="#"
                class="sidebar-link dropdown-toggle flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
                <div class="flex items-center gap-3">
                    <i class="fas fa-file-alt w-5"></i>
                    <span class="sidebar-text font-medium">Kelola Layanan</span>
                </div>
                <i class="fas fa-chevron-down text-xs"></i>
            </a>
            <div class="dropdown-menu">
                <a href="{{ route('admin.penerbitan-kk') }}"
                    class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg text-gray-700 text-sm">
                    <i class="fas fa-address-card w-4"></i>
                    <span class="sidebar-text">Kartu Keluarga</span>
                </a>
                <a href="{{ route('admin.penerbitan-akte-lahir') }}"
                    class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg text-gray-700 text-sm">
                    <i class="fas fa-baby w-4"></i>
                    <span class="sidebar-text">Akta Kelahiran</span>
                </a>
                <a href="{{ route('admin.penerbitan-akte-kematian') }}"
                    class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg text-gray-700 text-sm">
                    <i class="fas fa-user-times w-4"></i>
                    <span class="sidebar-text">Akta Kematian</span>
                </a>
                <a href="{{ route('admin.penerbitan-lahir-mati') }}"
                    class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg text-gray-700 text-sm">
                    <i class="fas fa-exchange-alt w-4"></i>
                    <span class="sidebar-text">Lahir Mati</span>
                </a>
                <a href="{{ route('admin.penerbitan-pernikahan') }}"
                    class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg text-gray-700 text-sm">
                    <i class="fas fa-ring w-4"></i>
                    <span class="sidebar-text">Akta Pernikahan</span>
                </a>
            </div>
        </div>

        <div class="pt-4 pb-2">
            <p class="sidebar-text px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Akun</p>
        </div>

        <a href="{{ route('admin.manajemen-akun') }}"
            class="sidebar-link {{ request()->routeIs('admin.manajemen-akun') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-users-cog w-5"></i>
            <span class="sidebar-text font-medium">Manajemen Akun</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="inline">
            @csrf
            <button type="button" id="sidebarLogoutBtn"
                class="sidebar-link w-full flex items-center gap-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition-all">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span class="sidebar-text font-medium">Logout</span>
            </button>
        </form>
    </nav>
</aside>

<script>
    // Setup logout button event listener
    document.addEventListener('DOMContentLoaded', function() {
        const logoutBtn = document.getElementById('sidebarLogoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                console.log('Logout button clicked'); // Debug

                // Pause auto-logout timer saat modal konfirmasi terbuka
                if (window.pauseAutoLogoutReset) {
                    window.pauseAutoLogoutReset();
                }

                if (typeof SwalHelper !== 'undefined' && window.SwalHelper && typeof window.SwalHelper.konfirmasiDisdukcapil === 'function') {
                    window.SwalHelper.konfirmasiDisdukcapil({
                        judul: 'Konfirmasi Logout',
                        pesan: 'Sesi Anda akan diakhiri dan Anda akan kembali ke halaman login. Apakah Anda yakin ingin melanjutkan?',
                        tipe: 'warning',
                        labelOk: 'Konfirmasi',
                        onKonfirmasi: function() {
                            Swal.fire({
                                title: 'Memproses Logout',
                                html: '<div class="loading-icon"><i class="fas fa-circle-notch fa-spin"></i></div>' +
                                    '<p class="text-gray-600 mt-4">Sedang mengakhiri session...</p>',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                showDenyButton: false,
                                showCancelButton: false,
                                customClass: { popup: 'swal-dd-modal', htmlContainer: 'swal2-html-container' }
                            });
                            document.getElementById('logoutForm').submit();
                        },
                        onBatal: function() {
                            if (window.resumeAutoLogoutReset) {
                                window.resumeAutoLogoutReset();
                            }
                        }
                    });
                } else {
                    console.warn('SwalHelper dan Swal tidak tersedia, menggunakan konfirmasi default browser');
                    if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
                        document.getElementById('logoutForm').submit();
                    } else {
                        // Resume auto-logout timer jika user batal
                        if (window.resumeAutoLogoutReset) {
                            window.resumeAutoLogoutReset();
                        }
                    }
                }
            }, { passive: false });
        }
    });
</script>
