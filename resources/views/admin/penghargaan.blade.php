@extends('layouts.admin')

@section('content')
@php
    $page_title = 'Penghargaan - Admin';
    $total     = $data->count();
    $nasional  = $data->where('tingkat', 'Nasional')->count();
    $provinsi  = $data->where('tingkat', 'Provinsi')->count();
@endphp

<div class="mb-6 reveal">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Penghargaan</h1>
            <p class="text-gray-600 mt-1 text-sm">Daftar penghargaan yang telah diraih Disdukcapil Kabupaten Toba</p>
        </div>
        <button type="button" onclick="openPenghargaanModal('create')"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 active:scale-95 transition-all">
            <i class="fas fa-plus"></i>
            <span>Tambah Penghargaan</span>
        </button>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 reveal">
    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-trophy text-xl text-yellow-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $total }}</p>
            <p class="text-sm text-gray-500">Total Penghargaan</p>
        </div>
    </div>
    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-star text-xl text-emerald-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $nasional }}</p>
            <p class="text-sm text-gray-500">Tingkat Nasional</p>
        </div>
    </div>
    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-medal text-xl text-blue-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $provinsi }}</p>
            <p class="text-sm text-gray-500">Tingkat Provinsi</p>
        </div>
    </div>
</div>
<div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm reveal">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <h3 class="text-base font-bold text-gray-800">Daftar Penghargaan</h3>
        <div class="flex gap-2">
            <select id="filterTingkat" onchange="filterData()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Tingkat</option>
                <option value="Nasional">Nasional</option>
                <option value="Provinsi">Provinsi</option>
                <option value="Kabupaten">Kabupaten</option>
            </select>
            <select id="filterUrut" onchange="filterData()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="terbaru">Terbaru</option>
                <option value="terlama">Terlama</option>
            </select>
        </div>
    </div>

    <div id="penghargaanList" class="space-y-4">
        @forelse ($data as $item)
            @php
                $iconMap = [
                    'Nasional'  => ['icon' => 'fa-trophy',  'bg' => 'bg-yellow-500',  'badge' => 'bg-red-100 text-red-700'],
                    'Provinsi'  => ['icon' => 'fa-medal',   'bg' => 'bg-blue-500',    'badge' => 'bg-blue-100 text-blue-700'],
                    'Kabupaten' => ['icon' => 'fa-award',   'bg' => 'bg-emerald-500', 'badge' => 'bg-green-100 text-green-700'],
                ];
                $style = $iconMap[$item->tingkat] ?? ['icon' => 'fa-star', 'bg' => 'bg-gray-500', 'badge' => 'bg-gray-100 text-gray-700'];
            @endphp
            <div class="penghargaan-item border border-gray-200 rounded-xl p-4 sm:p-5 hover:shadow-md transition"
                 data-tingkat="{{ $item->tingkat }}"
                 data-tahun="{{ $item->tahun ?? 0 }}">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 {{ $style['bg'] }} rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas {{ $style['icon'] }} text-xl text-white"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-start justify-between gap-2 mb-1">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm sm:text-base font-bold text-gray-800 leading-snug">{{ $item->nama }}</h4>
                                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ $item->instansi ?? '-' }}</p>
                            </div>
                            <span class="px-2.5 py-1 {{ $style['badge'] }} rounded-full text-xs font-semibold flex-shrink-0">
                                {{ $item->tingkat }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-gray-600 mt-1 line-clamp-2">{{ $item->deskripsi_singkat }}</p>
                        <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-gray-400">
                            @if($item->tahun)
                                <span><i class="fas fa-calendar mr-1"></i>{{ $item->tahun }}</span>
                            @endif
                            @if($item->lokasi)
                                <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $item->lokasi }}</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-3 mt-3 pt-3 border-t border-gray-100">
                            @if($item->file)
                                <a href="{{ asset('storage/' . $item->file) }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1.5 text-xs text-emerald-600 hover:text-emerald-700 font-medium transition">
                                    <i class="fas fa-eye"></i> Lihat File
                                </a>
                                <span class="text-gray-200 hidden sm:inline">|</span>
                            @endif
                            <button type="button"
                                class="penghargaan-edit-btn inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-700 font-medium transition"
                                data-id="{{ $item->id }}">
                                <i class="fas fa-edit"></i> Ubah
                            </button>
                            <span class="text-gray-200 hidden sm:inline">|</span>
                            <form action="{{ route('admin.penghargaan.destroy', $item->id) }}" method="post" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                    class="penghargaan-delete-btn inline-flex items-center gap-1.5 text-xs text-red-500 hover:text-red-600 font-medium transition"
                                    data-title="{{ $item->nama }}">
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
                    <i class="fas fa-trophy text-gray-300 text-2xl"></i>
                </div>
                <p class="text-gray-500 text-sm">Belum ada penghargaan.<br>Klik <span class="font-semibold text-gray-700">"Tambah Penghargaan"</span> untuk membuat yang pertama.</p>
            </div>
        @endforelse
    </div>
</div>
@foreach ($data as $item)
    <script type="application/json" id="penghargaan-payload-{{ $item->id }}">{!! json_encode([
        'id'                => $item->id,
        'nama'              => $item->nama,
        'instansi'          => $item->instansi,
        'deskripsi_singkat' => $item->deskripsi_singkat,
        'tingkat'           => $item->tingkat,
        'tahun'             => $item->tahun,
        'lokasi'            => $item->lokasi,
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach

<div id="penghargaanModal"
    class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white rounded-t-2xl">
            <h2 id="penghargaanModalTitle" class="text-lg font-bold text-gray-800">Tambah Penghargaan</h2>
            <button type="button" onclick="closePenghargaanModal()"
                class="w-10 h-10 rounded-xl hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="penghargaanForm" method="post" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div id="penghargaanMethod"></div>
            <div>   
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Penghargaan <span class="text-red-500">*</span></label>
                <input type="text" name="nama" id="field_nama" required maxlength="200"
                    placeholder="Contoh: Penghargaan Kualitas Pelayanan Publik"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi Pemberi <span class="text-red-500">*</span></label>
                <input type="text" name="instansi" id="field_instansi" required maxlength="200"
                    placeholder="Contoh: Kementerian PANRB"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi <span class="text-red-500">*</span></label>
                <textarea name="deskripsi_singkat" id="field_deskripsi_singkat" required rows="3"
                    placeholder="Tuliskan deskripsi singkat penghargaan..."
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tingkat <span class="text-red-500">*</span></label>
                    <select name="tingkat" id="field_tingkat" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
                        <option value="">Pilih Tingkat</option>
                        <option value="Nasional">Nasional</option>
                        <option value="Provinsi">Provinsi</option>
                        <option value="Kabupaten">Kabupaten</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                    <input type="number" name="tahun" id="field_tahun" required
                        min="2000" max="{{ date('Y') }}" placeholder="{{ date('Y') }}"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi</label>
                    <input type="text" name="lokasi" id="field_lokasi" maxlength="100"
                        placeholder="Contoh: Jakarta"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">File PDF</label>
                <input type="file" name="file" id="field_file" accept=".pdf"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
                <p class="text-xs text-gray-400 mt-1">Format: PDF &bull; Maksimal: 512 KB. Kosongkan jika tidak ingin mengganti file.</p>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closePenghargaanModal()"
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
    const modal    = document.getElementById('penghargaanModal');
    const form     = document.getElementById('penghargaanForm');
    const methodEl = document.getElementById('penghargaanMethod');
    const titleEl  = document.getElementById('penghargaanModalTitle');

    window.openPenghargaanModal = function (mode, item) {
        form.reset();
        methodEl.innerHTML = '';
        if (mode === 'create') {
            titleEl.textContent = 'Tambah Penghargaan';
            form.action = @json(route('admin.penghargaan.store'));
        } else if (item) {
            titleEl.textContent = 'Ubah Penghargaan';
            form.action = @json(url('/admin/penghargaan')) + '/' + item.id;
            methodEl.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('field_nama').value              = item.nama              || '';
            document.getElementById('field_instansi').value          = item.instansi          || '';
            document.getElementById('field_deskripsi_singkat').value = item.deskripsi_singkat || '';
            document.getElementById('field_tingkat').value           = item.tingkat           || '';
            document.getElementById('field_tahun').value             = item.tahun             || '';
            document.getElementById('field_lokasi').value            = item.lokasi            || '';
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    window.closePenghargaanModal = function () {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    };

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closePenghargaanModal();
    });

    document.querySelectorAll('.penghargaan-edit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = btn.getAttribute('data-id');
            const el = document.getElementById('penghargaan-payload-' + id);
            if (!el) return;
            try {
                const item = JSON.parse(el.textContent);
                openPenghargaanModal('edit', item);
            } catch (err) {
                SwalHelper.error('Gagal memuat data penghargaan.');
            }
        });
    });

    document.querySelectorAll('.penghargaan-delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form  = btn.closest('form');
            const title = btn.getAttribute('data-title') || 'penghargaan ini';
            if (window.pauseAutoLogoutReset) window.pauseAutoLogoutReset();
            SwalHelper.deleteConfirm(
                'Hapus Penghargaan?',
                'Yakin ingin menghapus: ' + title + '?',
                function () {
                    if (window.resumeAutoLogoutReset) window.resumeAutoLogoutReset();
                    form.submit();
                }
            );
        });
    });

    window.filterData = function () {
        const tingkat = document.getElementById('filterTingkat').value;
        const urut    = document.getElementById('filterUrut').value;
        const items   = Array.from(document.querySelectorAll('.penghargaan-item'));
        items.forEach(function (item) {
            const t = item.getAttribute('data-tingkat');
            item.style.display = (!tingkat || t === tingkat) ? '' : 'none';
        });
        const visible = items.filter(i => i.style.display !== 'none');
        visible.sort(function (a, b) {
            const ta = parseInt(a.getAttribute('data-tahun')) || 0;
            const tb = parseInt(b.getAttribute('data-tahun')) || 0;
            return urut === 'terlama' ? ta - tb : tb - ta;
        });
        const list = document.getElementById('penghargaanList');
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
    @if(session('success'))
        SwalHelper.success("{{ session('success') }}");
    @endif
    @if(session('error'))
        SwalHelper.error("{{ session('error') }}");
    @endif
})();
</script>
@endpush