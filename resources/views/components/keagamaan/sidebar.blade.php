{{-- SweetAlert dimuat oleh layout --}}

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
    // Setup logout button event listener
    document.addEventListener('DOMContentLoaded', function() {
        const logoutBtn = document.getElementById('sidebarLogoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

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
                        }
                    });
                } else if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        title: 'Konfirmasi Logout',
                        text: 'Sesi Anda akan diakhiri dan Anda akan kembali ke halaman login. Apakah Anda yakin ingin melanjutkan?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Konfirmasi',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                        customClass: { popup: 'swal-dd-modal' }
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            window.Swal.fire({
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
                        }
                    });
                } else {
                    if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
                        document.getElementById('logoutForm').submit();
                    }
                }
            }, { passive: false });
        }
    });
</script>
