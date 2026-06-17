@extends('layouts.admin')

@section('content')
@php
    use Illuminate\Support\Str;

    $page_title = 'Kelola Berita';
    $totalBerita = $beritas->count();
    $terbitPublik = $beritas->filter(function ($b) {
        return ! $b->published_at || $b->published_at <= now();
    })->count();
    $bulanIni = $beritas->filter(function ($b) {
        $at = $b->published_at ?? $b->created_at;
        return $at && $at->isCurrentMonth();
    })->count();
@endphp

{{-- Welcome Banner --}}
<div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-2xl p-6 md:p-8 text-white mb-6 reveal shadow-lg">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold mb-2">Kelola Berita</h2>
            <p class="text-blue-100 text-base md:text-lg">Tambah, ubah, dan hapus berita yang tampil di beranda publik Disdukcapil Kabupaten Toba.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2">
                <p class="text-xs text-blue-100">Total Berita</p>
                <p class="text-2xl font-bold">{{ $totalBerita }}</p>
            </div>
        </div>
    </div>
</div>

<div class="mb-6 reveal">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-4">
        <button type="button" data-style-guide-skip onclick="openBeritaModal('create')"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 active:scale-95 transition-all">
            <i class="fas fa-plus"></i>
            <span>Tambah Berita</span>
        </button>
    </div>
</div>

@if ($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm reveal">
        <p class="font-semibold mb-2">Periksa kembali input:</p>
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 reveal">
    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-newspaper text-xl text-blue-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalBerita }}</p>
            <p class="text-sm text-gray-500">Total Berita</p>
        </div>
    </div>
    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-check-circle text-xl text-emerald-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $terbitPublik }}</p>
            <p class="text-sm text-gray-500">Terbit Publik</p>
        </div>
    </div>
    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-calendar-alt text-xl text-indigo-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $bulanIni }}</p>
            <p class="text-sm text-gray-500">Bulan Ini</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm reveal">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <h3 class="text-base font-bold text-gray-800">Daftar Berita</h3>
        <div class="flex gap-2">
            <select id="filterStatus" onchange="filterBeritaData()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="terbit">Terbit</option>
                <option value="terjadwal">Terjadwal</option>
            </select>
            <select id="filterUrut" onchange="filterBeritaData()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="terbaru">Terbaru</option>
                <option value="terlama">Terlama</option>
            </select>
        </div>
    </div>

    <div id="beritaList" class="space-y-4">
        @forelse ($beritas as $item)
            @php
                $publishedAt = $item->published_at ?? $item->created_at;
                $isTerjadwal = $item->published_at && $item->published_at > now();
                $status = $isTerjadwal ? 'terjadwal' : 'terbit';
                $badgeClass = $isTerjadwal ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700';
                $badgeLabel = $isTerjadwal ? 'Terjadwal' : 'Terbit';
                $excerpt = Str::limit(strip_tags($item->konten), 120);
                $timestamp = $publishedAt ? $publishedAt->timestamp : 0;
            @endphp
            <div class="berita-item border border-gray-200 rounded-xl p-4 sm:p-5 hover:shadow-md transition"
                 data-status="{{ $status }}"
                 data-timestamp="{{ $timestamp }}">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 bg-blue-500 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-newspaper text-xl text-white"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm sm:text-base font-bold text-gray-800 leading-snug">{{ $item->judul }}</h4>
                                @if($excerpt)
                                    <p class="text-xs sm:text-sm text-gray-600 mt-1 line-clamp-2">{{ $excerpt }}</p>
                                @endif
                                <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-gray-400">
                                    <span><i class="fas fa-calendar mr-1"></i>{{ $publishedAt->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                <span class="px-2.5 py-1 {{ $badgeClass }} rounded-full text-xs font-semibold">
                                    {{ $badgeLabel }}
                                </span>
                                <div class="flex flex-wrap items-center justify-end gap-x-3 gap-y-1">
                                    <button type="button"
                                        data-style-guide-skip
                                        class="berita-edit-btn inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-700 font-medium transition"
                                        data-berita-id="{{ $item->id }}">
                                        <i class="fas fa-edit"></i> Ubah
                                    </button>
                                    <span class="text-gray-200">|</span>
                                    <form action="{{ route('admin.berita.destroy', $item) }}" method="post" class="inline delete-berita-form" data-title="{{ $item->judul }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            data-style-guide-skip
                                            class="berita-delete-btn inline-flex items-center gap-1.5 text-xs text-red-500 hover:text-red-600 font-medium transition">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-newspaper text-gray-300 text-2xl"></i>
                </div>
                <p class="text-gray-500 text-sm">Belum ada berita.<br>Klik <span class="font-semibold text-gray-700">"Tambah Berita"</span> untuk membuat yang pertama.</p>
            </div>
        @endforelse
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
            <button type="button" data-style-guide-skip onclick="closeBeritaModal()"
                class="w-10 h-10 rounded-xl hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="beritaForm" method="post" class="p-6 space-y-4">
            @csrf
            <div id="beritaMethod"></div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label>
                <input type="text" name="judul" id="field_judul" data-wajib="true" maxlength="255"
                    placeholder="Contoh: Disdukcapil Laksanakan Jemput Bola Layanan Adminduk"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    value="{{ old('judul') }}">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Isi lengkap (HTML diperbolehkan) <span class="text-red-500">*</span></label>
                <textarea name="konten" id="field_konten" data-wajib="true" rows="8"
                    placeholder="Tuliskan isi berita lengkap..."
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono outline-none transition resize-none">{{ old('konten') }}</textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Waktu terbit</label>
                    <input type="datetime-local" name="published_at" id="field_published_at"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>
                <div class="flex items-end">
                    <p class="text-sm text-gray-600 pb-2">Berita otomatis tampil berdasarkan tanggal terbit.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" data-style-guide-skip onclick="closeBeritaModal()"
                    class="px-5 py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold transition">
                    Batal
                </button>
                <button type="submit" data-style-guide-skip
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
                SwalHelper.toastError('Data berita tidak ditemukan.', 'Muat ulang halaman, lalu coba edit kembali.');
                return;
            }
            try {
                const item = JSON.parse(el.textContent);
                openBeritaModal('edit', item);
            } catch (err) {
                SwalHelper.toastError('Gagal memuat data berita.', 'Muat ulang halaman, lalu coba lagi.');
            }
        });
    });

    document.querySelectorAll('.berita-delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const formEl = btn.closest('form');
            const title = formEl.getAttribute('data-title') || 'berita ini';
            if (window.pauseAutoLogoutReset) window.pauseAutoLogoutReset();
            SwalHelper.deleteConfirm(
                'Hapus Berita?',
                'Yakin ingin menghapus: ' + title + '?',
                function () {
                    if (window.resumeAutoLogoutReset) window.resumeAutoLogoutReset();
                    formEl.submit();
                }
            );
        });
    });

    window.filterBeritaData = function () {
        const status = document.getElementById('filterStatus').value;
        const urut = document.getElementById('filterUrut').value;
        const items = Array.from(document.querySelectorAll('.berita-item'));

        items.forEach(function (item) {
            const s = item.getAttribute('data-status');
            item.style.display = (!status || s === status) ? '' : 'none';
        });

        const visible = items.filter(function (i) { return i.style.display !== 'none'; });
        visible.sort(function (a, b) {
            const ta = parseInt(a.getAttribute('data-timestamp')) || 0;
            const tb = parseInt(b.getAttribute('data-timestamp')) || 0;
            return urut === 'terlama' ? ta - tb : tb - ta;
        });

        const list = document.getElementById('beritaList');
        visible.forEach(function (item) { list.appendChild(item); });
    };

    function reveal() {
        document.querySelectorAll('.reveal').forEach(function (el) {
            if (el.getBoundingClientRect().top < window.innerHeight - 100) {
                el.classList.add('active');
            }
        });
    }
    window.addEventListener('scroll', reveal);
    reveal();

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

    @if(session('success'))
        SwalHelper.toastSuccess(@json(session('success')));
    @endif
    @if(session('error'))
        SwalHelper.toastError(@json(session('error')), @json(session('error_solution') ?? 'Periksa data berita yang dimasukkan, lalu coba lagi.'));
    @endif
})();
</script>
@endpush
