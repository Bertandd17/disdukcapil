@extends('layouts.admin')

@section('content')

<div class="mb-6 reveal">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Visi Misi</h1>
            <p class="text-gray-600 mt-1 text-sm">Visi dan Misi Disdukcapil Kabupaten Toba</p>
        </div>
        <button type="button" onclick="openVisiMisiModal('create')"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 active:scale-95 transition-all">
            <i class="fas fa-plus"></i>
            <span>Tambah Visi Misi</span>
        </button>
    </div>
</div>
<div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm reveal">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <h3 class="text-base font-bold text-gray-800">Visi Misi</h3>
    </div>

    <div id="visimisiList" class="space-y-4">
        @forelse ($data as $item)
            <div class="visimisi-item border border-gray-200 rounded-xl p-4 sm:p-5 hover:shadow-md transition">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-gray-600 mb-1">Visi</h4>
                                <p class="text-sm sm:text-base font-bold text-gray-800 leading-snug">{{ $item->visi }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-gray-600 mb-1">Misi</h4>
                                <p class="text-sm sm:text-base text-gray-700 leading-snug">{{ $item->misi }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 mt-4 pt-4 border-t border-gray-100">
                            <button type="button"
                                class="visimisi-edit-btn inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-700 font-medium transition"
                                data-id="{{ $item->id }}">
                                <i class="fas fa-edit"></i> Ubah
                            </button>
                            <span class="text-gray-200 hidden sm:inline">|</span>
                            <form action="{{ route('admin.visimisi.destroy', $item->id) }}" method="post" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                    class="visimisi-delete-btn inline-flex items-center gap-1.5 text-xs text-red-500 hover:text-red-600 font-medium transition"
                                    data-title="Visi Misi">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lightbulb text-gray-300 text-2xl"></i>
                </div>
                <p class="text-gray-500 text-sm">Belum ada Visi Misi.<br>Klik <span class="font-semibold text-gray-700">"Tambah Visi Misi"</span> untuk membuat yang pertama.</p>
            </div>
        @endforelse
    </div>
</div>
@foreach ($data as $item)
    <script type="application/json" id="visimisi-payload-{{ $item->id }}">{!! json_encode([
        'id'   => $item->id,
        'visi' => $item->visi,
        'misi' => $item->misi,
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach

<div id="visimisiModal"
    class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white rounded-t-2xl">
            <h2 id="visimisiModalTitle" class="text-lg font-bold text-gray-800">Tambah Visi Misi</h2>
            <button type="button" onclick="closeVisimisiModal()"
                class="w-10 h-10 rounded-xl hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="visimisiForm" method="post" class="p-6 space-y-4">
            @csrf
            <div id="visimisiMethod"></div>
            <div>   
                <label class="block text-sm font-semibold text-gray-700 mb-1">Visi <span class="text-red-500">*</span></label>
                <textarea name="visi" id="field_visi" data-wajib="true" rows="3" maxlength="200"
                    placeholder="Tuliskan visi organisasi..."
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"></textarea>
                <p class="text-xs text-gray-400 mt-1">Maksimal 200 karakter</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Misi <span class="text-red-500">*</span></label>
                <textarea name="misi" id="field_misi" data-wajib="true" rows="3" maxlength="200"
                    placeholder="Tuliskan misi organisasi..."
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"></textarea>
                <p class="text-xs text-gray-400 mt-1">Maksimal 200 karakter</p>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeVisimisiModal()"
                    class="px-5 py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700 active:scale-95 transition-all shadow-sm">
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
    const modal    = document.getElementById('visimisiModal');
    const form     = document.getElementById('visimisiForm');
    const methodEl = document.getElementById('visimisiMethod');
    const titleEl  = document.getElementById('visimisiModalTitle');

    window.openVisiMisiModal = function (mode, item) {
        form.reset();
        methodEl.innerHTML = '';
        if (mode === 'create') {
            titleEl.textContent = 'Tambah Visi Misi';
            form.action = @json(route('admin.visimisi.store'));
        } else if (item) {
            titleEl.textContent = 'Ubah Visi Misi';
            form.action = @json(url('/admin/visimisi')) + '/' + item.id;
            methodEl.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('field_visi').value = item.visi || '';
            document.getElementById('field_misi').value = item.misi || '';
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    window.closeVisimisiModal = function () {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    };

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeVisimisiModal();
    });

    document.querySelectorAll('.visimisi-edit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = btn.getAttribute('data-id');
            const el = document.getElementById('visimisi-payload-' + id);
            if (!el) return;
            try {
                const item = JSON.parse(el.textContent);
                openVisiMisiModal('edit', item);
            } catch (err) {
                SwalHelper.toastError('Gagal memuat data visi misi.', 'Muat ulang halaman, lalu coba lagi.');
            }
        });
    });

    document.querySelectorAll('.visimisi-delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form  = btn.closest('form');
            const title = btn.getAttribute('data-title') || 'visi misi ini';
            if (window.pauseAutoLogoutReset) window.pauseAutoLogoutReset();
            SwalHelper.deleteConfirm(
                'Hapus Visi Misi?',
                'Yakin ingin menghapus: ' + title + '?',
                function () {
                    if (window.resumeAutoLogoutReset) window.resumeAutoLogoutReset();
                    form.submit();
                }
            );
        });
    });

    function reveal() {
        document.querySelectorAll('.reveal').forEach(function (el) {
            if (el.getBoundingClientRect().top < window.innerHeight - 100) {
                el.classList.add('active');
            }
        });
    }
    window.addEventListener('scroll', reveal);
    reveal();
    @if(session('success'))
        SwalHelper.toastSuccess(@json(session('success')));
    @endif
    @if(session('error'))
        SwalHelper.toastError(@json(session('error')), @json(session('error_solution') ?? 'Periksa data visi misi yang dimasukkan, lalu coba lagi.'));
    @endif
})();
</script>
@endpush
