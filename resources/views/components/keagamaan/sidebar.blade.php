{{-- Keagamaan Sidebar --}}
<aside class="sidebar fixed left-0 top-0 h-full w-64 bg-white border-r border-gray-200 z-50 shadow-lg">
    {{-- Logo --}}
    <div class="h-16 flex items-center px-6 border-b border-gray-100">
        <div class="w-10 h-10 rounded-xl overflow-hidden flex-shrink-0">
            <img src="{{ asset('images/logo_toba.jpeg') }}" alt="Logo Kabupaten Toba" class="w-full h-full object-contain">
        </div>
        <span class="sidebar-text logo-text ml-3 font-bold text-lg text-gray-800">Disdukcapil</span>
    </div>

    {{-- Navigation --}}
    <nav class="p-4 space-y-1 overflow-y-auto h-[calc(100vh-8rem)]">
        {{-- Dashboard --}}
        <a href="{{ route('keagamaan.dashboard') }}"
            class="sidebar-link {{ request()->routeIs('keagamaan.dashboard') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-home w-5"></i>
            <span class="sidebar-text font-medium">Dashboard</span>
        </a>

        <div class="pt-4 pb-2">
            <p class="sidebar-text px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Layanan Pernikahan</p>
        </div>

        {{-- Permintaan Nikah (Calendar) --}}
        <a href="{{ route('keagamaan.pernikahan.index') }}"
            class="sidebar-link {{ request()->routeIs('keagamaan.pernikahan.index') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-calendar-check w-5"></i>
            <span class="sidebar-text font-medium">Permintaan Nikah</span>
        </a>

        {{-- Request Tanggal ke Disdukcapil --}}
        <a href="{{ route('keagamaan.pernikahan.request-tanggal') }}"
            class="sidebar-link {{ request()->routeIs('keagamaan.pernikahan.request-tanggal') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-paper-plane w-5"></i>
            <span class="sidebar-text font-medium">Request Tanggal</span>
        </a>

        {{-- Upload Berkas --}}
        <a href="{{ route('keagamaan.pernikahan.upload-berkas') }}"
            class="sidebar-link {{ request()->routeIs('keagamaan.pernikahan.upload-berkas') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700">
            <i class="fas fa-file-upload w-5"></i>
            <span class="sidebar-text font-medium">Upload Berkas</span>
        </a>

        <div class="pt-4 pb-2">
            <p class="sidebar-text px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Akun</p>
        </div>

        {{-- Logout --}}
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
    document.addEventListener('DOMContentLoaded', function() {
        var logoutBtn = document.getElementById('sidebarLogoutBtn');
        if (!logoutBtn) return;

        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            if (window.pauseAutoLogoutReset) {
                window.pauseAutoLogoutReset();
            }

            if (typeof Swal === 'undefined') {
                if (typeof fireToast !== 'undefined') {
                    fireToast({
                        type: 'warning',
                        icon: 'warning',
                        title: 'Konfirmasi keluar sistem',
                        problem: 'Library SweetAlert tidak termuat di halaman ini.',
                        solution: 'Muat ulang halaman (F5) untuk menyegarkan script, atau lanjutkan logout dengan konfirmasi browser.'
                    });
                } else {
                    document.getElementById('logoutForm').submit();
                }
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Konfirmasi Logout',
                html: 'Sesi Anda akan diakhiri dan Anda akan kembali ke halaman login. Apakah Anda yakin ingin melanjutkan?',
                showCancelButton: true,
                showConfirmButton: true,
                showDenyButton: false,
                denyButtonText: null,
                confirmButtonText: 'Konfirmasi',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                allowOutsideClick: false,
                allowEscapeKey: false,
                buttonsStyling: true,
                didOpen: function() {
                    var denyBtn = document.querySelector('.swal2-deny');
                    if (denyBtn) denyBtn.remove();
                    var denyContainer = document.querySelector('.swal2-deny-container');
                    if (denyContainer) denyContainer.remove();
                }
            }).then(function(result) {
                if (!result.isConfirmed) {
                    if (window.resumeAutoLogoutReset) {
                        window.resumeAutoLogoutReset();
                    }
                    return;
                }

                logoutBtn.disabled = true;

                Swal.close();

                window.setTimeout(function() {
                    if (typeof window.showRegisterStyleLoading === 'function') {
                        window.showRegisterStyleLoading('Memproses Logout', 'Sedang mengakhiri session...');
                    } else {
                        Swal.fire({
                            title: 'Memproses Logout',
                            html: '<div class="flex flex-col items-center gap-3 py-2"><i class="fas fa-circle-notch fa-spin text-4xl text-green-500"></i><p class="text-gray-600 text-sm">Sedang mengakhiri session...</p></div>',
                            showConfirmButton: false,
                            showCancelButton: false,
                            showDenyButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            customClass: { popup: 'swal-register-loading swal-dd-modal' }
                        });
                    }

                    window.setTimeout(function() {
                        if (window.PageLoading && typeof window.PageLoading.show === 'function') {
                            window.PageLoading.show('Memproses logout...');
                        }
                        document.getElementById('logoutForm').submit();
                    }, 350);
                }, 100);
            });
        }, { passive: false });
    });
</script>
