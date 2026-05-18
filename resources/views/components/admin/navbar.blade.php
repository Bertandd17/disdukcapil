{{-- Admin Header --}}
<header class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <div class="flex items-center justify-between px-6 py-4">
        {{-- Left --}}
        <div class="flex items-center gap-4">
            <button id="sidebarToggle" class="p-2 hover:bg-gray-100 rounded-lg transition">
                <i class="fas fa-bars text-gray-600"></i>
            </button>
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $page_title ?? 'Dashboard Admin' }}</h1>
                <p class="text-sm text-gray-500">Selamat datang kembali, {{ auth()->user()->name }}</p>
            </div>
        </div>

        {{-- Profile --}}
        <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user text-blue-600"></i>
            </div>
            <div class="hidden md:block">
                <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500">Administrator</p>
            </div>
        </div>
    </div>
</header>
