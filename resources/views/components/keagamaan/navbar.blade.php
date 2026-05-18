{{-- Keagamaan Header --}}
<header class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <div class="flex items-center justify-between px-6 py-4">
        {{-- Left --}}
        <div class="flex items-center gap-4">
            <button id="sidebarToggle" class="p-2 hover:bg-gray-100 rounded-lg transition">
                <i class="fas fa-bars text-gray-600"></i>
            </button>
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $page_title ?? 'Dashboard Keagamaan' }}</h1>
                <p class="text-sm text-gray-500">Selamat datang kembali, {{ auth()->user()->name }}</p>
            </div>
        </div>

        {{-- Right --}}
        <div class="flex items-center gap-4">
            {{-- Notifications --}}
            @if(auth()->user()->unreadNotifications->isNotEmpty())
            <button class="relative p-2 hover:bg-gray-100 rounded-lg transition" onclick="toggleNotifications()">
                <i class="fas fa-bell text-gray-600"></i>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>
            @endif

            {{-- Profile --}}
            <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-tie text-blue-600"></i>
                </div>
                <div class="hidden md:block">
                    <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500">Petugas Keagamaan</p>
                </div>
            </div>
        </div>
    </div>
</header>
