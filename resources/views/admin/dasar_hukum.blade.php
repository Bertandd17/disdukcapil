@extends('layouts.admin')

@section('content')
@php
    $page_title = 'Kelola Dasar Hukum';
@endphp

<div class="container-fluid p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dasar Hukum</h1>
            <p class="text-gray-500 mt-1 text-sm">Daftar dasar hukum yang tampil di beranda publik</p>
        </div>
        <button type="button" onclick="openDasarHukumModal('create')"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-xl font-semibold text-sm hover:bg-emerald-700 active:scale-95 transition-all shadow-sm">
            <i class="fas fa-plus"></i>
            Tambah Dasar Hukum
        </button>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
            <p class="font-semibold mb-2">Periksa kembali input:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-3">
        @forelse ($data as $item)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5 flex items-start gap-3 sm:gap-4 hover:shadow-md transition-shadow">

                {{-- Icon --}}
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-scroll text-white text-base sm:text-lg"></i>
                </div>

                <div class="flex-1 min-w-0">
                    {{-- Nama & Deskripsi --}}
                    <p class="font-bold text-gray-900 text-sm sm:text-base leading-snug">{{ $item->nama }}</p>
                    <p class="text-gray-500 text-xs sm:text-sm mt-1 leading-relaxed line-clamp-2">{{ $item->deskripsi_singkat }}</p>

                    {{-- Action buttons --}}
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2 mt-3 pt-3 border-t border-gray-100">

                        {{-- Lihat File --}}
                        @if ($item->file)
                            <a href="{{ asset('storage/' . $item->file) }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-1.5 text-xs sm:text-sm text-emerald-600 hover:text-emerald-700 font-medium transition py-1">
                                <i class="fas fa-eye text-xs"></i>
                                <span>Lihat File</span>
                            </a>
                            <span class="text-gray-200 hidden sm:inline">|</span>
                        @endif

                        {{-- Tombol Ubah --}}
                        <button type="button"
                            class="dasar-hukum-edit-btn inline-flex items-center gap-1.5 text-xs sm:text-sm text-blue-600 hover:text-blue-700 font-medium transition py-1"
                            data-dasar-hukum-id="{{ $item->id }}">
                            <i class="fas fa-edit text-xs"></i>
                            <span>Ubah</span>
                        </button>
                        <span class="text-gray-200 hidden sm:inline">|</span>

                        {{-- Form + tombol hapus --}}
                        <form action="{{ route('admin.dasar-hukum.destroy', $item->id) }}" method="post" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                class="dasar-hukum-delete-btn inline-flex items-center gap-1.5 text-xs sm:text-sm text-red-500 hover:text-red-600 font-medium transition py-1"
                                data-title="{{ $item->nama }}">
                                <i class="fas fa-trash-alt text-xs"></i>
                                <span>Hapus</span>
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-gray-300 text-2xl"></i>
                </div>
                <p class="text-gray-500 text-sm">Belum ada dasar hukum.<br>Klik <span class="font-semibold text-gray-700">"Tambah Dasar Hukum"</span> untuk membuat yang pertama.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- JSON payloads --}}
@foreach ($data as $item)
    <script type="application/json" id="dasar-hukum-payload-{{ $item->id }}">{!! json_encode([
        'id'                => $item->id,
        'nama'              => $item->nama,
        'deskripsi_singkat' => $item->deskripsi_singkat,
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach

{{-- Modal --}}
<div id="dasarHukumModal"
    class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white rounded-t-2xl">
            <h2 id="dasarHukumModalTitle" class="text-lg font-bold text-gray-800">Tambah Dasar Hukum</h2>
            <button type="button" onclick="closeDasarHukumModal()"
                class="w-10 h-10 rounded-xl hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="dasarHukumForm" method="post" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div id="dasarHukumMethod"></div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Nama <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" id="field_nama" required maxlength="100"
                    placeholder="Contoh: Undang-Undang No. 24 Tahun 2013"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    value="{{ old('nama') }}">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Deskripsi Singkat <span class="text-red-500">*</span>
                </label>
                <textarea name="deskripsi_singkat" id="field_deskripsi_singkat" required rows="4"
                    placeholder="Tuliskan deskripsi singkat mengenai dasar hukum ini..."
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none">{{ old('deskripsi_singkat') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    File PDF
                </label>
                <input type="file" name="file" id="field_file" accept=".pdf"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                <p class="text-xs text-gray-400 mt-1">Format: PDF &bull; Maksimal: 500 KB. Kosongkan jika tidak ingin mengganti file.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeDasarHukumModal()"
                    class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:scale-95 transition-all shadow-sm">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const modal    = document.getElementById('dasarHukumModal');
    const form     = document.getElementById('dasarHukumForm');
    const methodEl = document.getElementById('dasarHukumMethod');
    const titleEl  = document.getElementById('dasarHukumModalTitle');

    /* ── MODAL ── */
    window.openDasarHukumModal = function (mode, item) {
        form.reset();
        methodEl.innerHTML = '';

        if (mode === 'create') {
            titleEl.textContent = 'Tambah Dasar Hukum';
            form.action = @json(route('admin.dasar-hukum.store'));
        } else if (item) {
            titleEl.textContent = 'Ubah Dasar Hukum';
            form.action = @json(url('/admin/dasar-hukum')) + '/' + item.id;
            methodEl.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('field_nama').value              = item.nama              || '';
            document.getElementById('field_deskripsi_singkat').value = item.deskripsi_singkat || '';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    window.closeDasarHukumModal = function () {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    };

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeDasarHukumModal();
    });

    /* ── TOMBOL UBAH ── */
    document.querySelectorAll('.dasar-hukum-edit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = btn.getAttribute('data-dasar-hukum-id');
            const el = document.getElementById('dasar-hukum-payload-' + id);
            if (!el) return;
            try {
                const item = JSON.parse(el.textContent);
                openDasarHukumModal('edit', item);
            } catch (err) {
                SwalHelper.error('Gagal memuat data dasar hukum.');
            }
        });
    });

    /* ── TOMBOL HAPUS ──
       Tombol berada di dalam <form> langsung (tiru pola akta lahir).
       Pakai click biasa — closest('form') langsung dapat formnya.
    */
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.dasar-hukum-delete-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const form  = btn.closest('form');
                const title = btn.getAttribute('data-title') || 'dasar hukum ini';

                if (window.pauseAutoLogoutReset) window.pauseAutoLogoutReset();
                SwalHelper.deleteConfirm(
                    'Hapus dasar hukum?',
                    'Yakin ingin menghapus: ' + title + '?',
                    function () {
                        if (window.resumeAutoLogoutReset) window.resumeAutoLogoutReset();
                        form.submit();
                    }
                );
            });
        });
    });

    /* ── AUTO-BUKA MODAL SAAT ADA ERROR VALIDASI ── */
    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            titleEl.textContent = 'Tambah Dasar Hukum';
            form.action         = @json(route('admin.dasar-hukum.store'));
            methodEl.innerHTML  = '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        });
    @endif
})();
</script>
@endpush