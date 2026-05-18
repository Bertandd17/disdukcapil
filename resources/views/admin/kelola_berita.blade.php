@extends('layouts.admin')

@section('content')
@php
    $page_title = 'Kelola Berita';
@endphp

<div class="container-fluid p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-newspaper mr-2"></i>Kelola Berita</h1>
            <p class="text-gray-600 mt-1">Tambah, ubah, dan hapus berita yang tampil di beranda publik</p>
        </div>
        <button type="button" onclick="openBeritaModal('create')" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl font-semibold hover:from-green-700 hover:to-green-800 transition shadow-sm">
            <i class="fas fa-plus"></i>
            Tambah Berita
        </button>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
            <p class="font-semibold mb-2">Periksa kembali input:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-left">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Judul</th>
                        <th class="px-4 py-3 font-semibold">Tanggal</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($beritas as $item)
                        <tr class="hover:bg-gray-50/80">
                            <td class="px-4 py-3 font-medium text-gray-900 max-w-xs truncate">{{ $item->judul }}</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ ($item->published_at ?? $item->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button" class="berita-edit-btn inline-flex items-center gap-1 px-3 py-1.5 text-blue-600 hover:bg-blue-50 rounded-lg font-medium transition" data-berita-id="{{ $item->id }}">
                                    <i class="fas fa-edit"></i> Ubah
                                </button>
                                <form action="{{ route('admin.berita.destroy', $item) }}" method="post" class="inline-block delete-berita-form" data-title="{{ $item->judul }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-red-600 hover:bg-red-50 rounded-lg font-medium transition">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-16 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 text-gray-300 block"></i>
                                Belum ada berita. Klik &quot;Tambah Berita&quot; untuk membuat yang pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach ($beritas as $item)
    <script type="application/json" id="berita-payload-{{ $item->id }}">{!! json_encode([
        'id' => $item->id,
        'judul' => $item->judul,
        'konten' => $item->konten,
        'published_at' => optional($item->published_at)->toIso8601String(),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach

{{-- Modal form --}}
<div id="beritaModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white rounded-t-2xl">
            <h2 id="beritaModalTitle" class="text-lg font-bold text-gray-800">Tambah Berita</h2>
            <button type="button" onclick="closeBeritaModal()" class="w-10 h-10 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="beritaForm" method="post" class="p-6 space-y-4">
            @csrf
            <div id="beritaMethod"></div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label>
                <input type="text" name="judul" id="field_judul" required maxlength="255" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ old('judul') }}">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Isi lengkap (HTML diperbolehkan) <span class="text-red-500">*</span></label>
                <textarea name="konten" id="field_konten" required rows="8" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm">{{ old('konten') }}</textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Waktu terbit</label>
                    <input type="datetime-local" name="published_at" id="field_published_at" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex items-end">
                    <p class="text-sm text-gray-600 pb-2">Berita otomatis tampil berdasarkan tanggal terbit.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeBeritaModal()" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold hover:from-green-700 hover:to-green-800 transition shadow-sm">
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
    const modal = document.getElementById('beritaModal');
    const form = document.getElementById('beritaForm');
    const methodEl = document.getElementById('beritaMethod');
    const titleEl = document.getElementById('beritaModalTitle');

    window.openBeritaModal = function (mode, item) {
        form.reset();
        methodEl.innerHTML = '';

        if (mode === 'create') {
            titleEl.textContent = 'Tambah Berita';
            form.action = @json(route('admin.berita.store'));
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('field_published_at').value = now.toISOString().slice(0, 16);
        } else if (item) {
            titleEl.textContent = 'Ubah Berita';
            form.action = @json(url('/admin/berita')) + '/' + item.id;
            methodEl.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('field_judul').value = item.judul || '';
            document.getElementById('field_konten').value = item.konten || '';
            if (item.published_at) {
                const d = new Date(item.published_at);
                d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
                document.getElementById('field_published_at').value = d.toISOString().slice(0, 16);
            } else {
                document.getElementById('field_published_at').value = '';
            }
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    window.closeBeritaModal = function () {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    };

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeBeritaModal();
    });

    document.querySelectorAll('.berita-edit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = btn.getAttribute('data-berita-id');
            const el = document.getElementById('berita-payload-' + id);
            if (!el) {
                SwalHelper.error('Error!', 'Data berita tidak ditemukan');
                return;
            }
            try {
                const item = JSON.parse(el.textContent);
                openBeritaModal('edit', item);
            } catch (err) {
                SwalHelper.error('Gagal memuat data berita.');
            }
        });
    });

    document.querySelectorAll('.delete-berita-form').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            e.preventDefault();
            const t = f.getAttribute('data-title') || 'berita ini';
            SwalHelper.deleteConfirm(
                'Hapus Berita',
                'Apakah Anda yakin ingin menghapus ' + t + '?',
                function () {
                    f.submit();
                }
            );
        });
    });

    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            titleEl.textContent = 'Tambah Berita';
            form.action = @json(route('admin.berita.store'));
            methodEl.innerHTML = '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        });
    @endif
})();
</script>
@endpush
