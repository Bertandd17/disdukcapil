@extends('layouts.admin')

@section('title', 'Manajemen Organisasi')

@section('content')
@php
    $total           = $allOrganisasi->count();
    $totalTerisi     = $allOrganisasi->whereNotNull('nama_pejabat')->where('nama_pejabat', '!=', '')->count();
    $totalKosong     = $total - $totalTerisi;
    $levelLabels     = \App\Models\Organisasi_Model::getLevels();
    $levelBadgeStyle = [
        'pimpinan_utama'      => 'bg-blue-100 text-blue-700',
        'bidang'              => 'bg-emerald-100 text-emerald-700',
        'sub_bagian'          => 'bg-amber-100 text-amber-700',
        'koordinator'         => 'bg-violet-100 text-violet-700',
        'kelompok_fungsional' => 'bg-pink-100 text-pink-700',
    ];
@endphp

{{-- Welcome Banner --}}
<div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-2xl p-6 md:p-8 text-white mb-6 reveal shadow-lg">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold mb-2">Manajemen Organisasi</h2>
            <p class="text-blue-100 text-base md:text-lg">Kelola nama pejabat untuk setiap jabatan dalam struktur organisasi Disdukcapil Kabupaten Toba.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2">
                <p class="text-xs text-blue-100">Total Jabatan</p>
                <p class="text-2xl font-bold">{{ $total }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Statistics Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 reveal">
    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-sitemap text-xl text-indigo-600"></i>
            </div>
            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Total</span>
        </div>
        <h3 class="text-3xl font-extrabold text-gray-800 mb-1">{{ $total }}</h3>
        <p class="text-sm text-gray-600 font-medium">Total Jabatan</p>
    </div>

    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-user-check text-xl text-green-600"></i>
            </div>
            <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">Terisi</span>
        </div>
        <h3 class="text-3xl font-extrabold text-green-600 mb-1">{{ $totalTerisi }}</h3>
        <p class="text-sm text-gray-600 font-medium">Pejabat Terisi</p>
    </div>

    <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-user-slash text-xl text-red-600"></i>
            </div>
            <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full">Kosong</span>
        </div>
        <h3 class="text-3xl font-extrabold text-red-600 mb-1">{{ $totalKosong }}</h3>
        <p class="text-sm text-gray-600 font-medium">Belum Diisi</p>
    </div>
</div>

{{-- Info Panel --}}
<div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 mb-6 reveal">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-info-circle text-blue-600"></i>
        </div>
        <div>
            <h3 class="font-bold text-gray-800 mb-1">Panduan</h3>
            <p class="text-sm text-gray-600">Klik tombol <strong class="text-blue-700">Edit</strong> untuk mengubah nama pejabat. Perubahan akan langsung tampil di halaman publik <em>Struktur Organisasi</em>.</p>
        </div>
    </div>
</div>

{{-- Main Card: Tabel Organisasi --}}
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden reveal">
    <div class="p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-gray-100">
        <div>
            <h3 class="text-base font-bold text-gray-800">Daftar Jabatan & Pejabat</h3>
            <p class="text-xs text-gray-500 mt-1">Diurutkan berdasarkan struktur organisasi.</p>
        </div>
        <div class="flex gap-2 w-full md:w-auto">
            <div class="relative flex-1 md:flex-none">
                <input type="text" id="searchBox" onkeyup="filterTable()"
                    placeholder="Cari jabatan atau pejabat..."
                    class="w-full md:w-72 pl-11 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            </div>
            <select id="filterLevel" onchange="filterTable()" class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Level</option>
                @foreach($levelLabels as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left" id="organisasiTable">
            <thead class="bg-gray-50 text-[11px] uppercase font-bold text-gray-400 tracking-widest">
                <tr>
                    <th class="px-6 py-4 w-14 text-center">No</th>
                    <th class="px-6 py-4">Jabatan</th>
                    <th class="px-6 py-4">Level</th>
                    <th class="px-6 py-4 text-center">Eselon</th>
                    <th class="px-6 py-4">Pejabat</th>
                    <th class="px-6 py-4 text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($allOrganisasi->sortBy('urutan') as $index => $item)
                    <tr class="hover:bg-gray-50/70 transition-colors" data-level="{{ $item->level }}" data-search="{{ strtolower($item->nama_jabatan . ' ' . $item->nama_pejabat) }}">
                        <td class="px-6 py-4 text-center text-sm text-gray-500 font-medium">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-gray-800 leading-snug">{{ $item->nama_jabatan }}</p>
                            @if($item->kode_posisi)
                                <p class="text-[11px] text-gray-400 mt-0.5 uppercase tracking-wide">{{ $item->kode_posisi }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($item->level)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $levelBadgeStyle[$item->level] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $item->level_label }}
                                </span>
                            @else
                                <span class="text-gray-300 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($item->eselon)
                                <span class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-[11px] font-bold">{{ $item->eselon }}</span>
                            @else
                                <span class="text-gray-300 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($item->nama_pejabat)
                                <p class="text-sm text-gray-700 font-medium">{{ $item->nama_pejabat }}</p>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full font-medium">
                                    <i class="fas fa-exclamation-circle"></i> Belum diisi
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button type="button"
                                onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->nama_jabatan) }}', '{{ addslashes($item->nama_pejabat ?? '') }}', '{{ $item->level ?? '' }}', '{{ $item->eselon ?? '' }}', {{ $item->urutan }}, {{ $item->parent_id ?? 'null' }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center text-gray-400">
                                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                                    <i class="fas fa-inbox text-3xl text-gray-300"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-500">Data organisasi belum tersedia.</p>
                                <p class="text-xs text-gray-400 mt-1">Silakan jalankan <code class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-600">php artisan db:seed --class=OrganisasiSeeder</code></p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Edit Pejabat (konsisten dengan modal admin lainnya) --}}
<div id="modalEditPejabat" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white rounded-t-2xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Edit Nama Pejabat</h2>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-10 h-10 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-500 transition" aria-label="Tutup">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="editForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="nama_jabatan" id="editNamaJabatan">
            <input type="hidden" name="level" id="editLevel">
            <input type="hidden" name="eselon" id="editEselon">
            <input type="hidden" name="urutan" id="editUrutan">
            <input type="hidden" name="parent_id" id="editParentId">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Jabatan</label>
                <input type="text" id="editJabatanDisplay" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 text-gray-600" readonly>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Pejabat <span class="text-red-500">*</span></label>
                <input type="text" name="nama_pejabat" id="editNamaPejabat" data-wajib="true" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Masukkan nama lengkap pejabat">
                <p class="text-xs text-gray-500 mt-1">Contoh: Budi Santoso, S.Kom</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-green-600 text-white font-semibold hover:bg-green-700 transition shadow-sm">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const editModalEl = document.getElementById('modalEditPejabat');

    function openEditModal(id, jabatan, pejabat, level, eselon, urutan, parentId) {
        const form = document.getElementById('editForm');
        form.action = `{{ route('admin.organisasi.update', ['id' => '__ID__']) }}`.replace('__ID__', id);

        document.getElementById('editJabatanDisplay').value = jabatan;
        document.getElementById('editNamaJabatan').value = jabatan;
        document.getElementById('editNamaPejabat').value = pejabat || '';
        document.getElementById('editLevel').value = level || '';
        document.getElementById('editEselon').value = eselon || '';
        document.getElementById('editUrutan').value = urutan;
        document.getElementById('editParentId').value = parentId || '';

        editModalEl.classList.remove('hidden');
        editModalEl.classList.add('flex');
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.getElementById('editNamaPejabat').focus(), 50);
    }

    function closeEditModal() {
        editModalEl.classList.add('hidden');
        editModalEl.classList.remove('flex');
        document.body.style.overflow = '';
    }

    // Tutup modal saat klik backdrop / tekan Escape
    editModalEl.addEventListener('click', (e) => {
        if (e.target === editModalEl) closeEditModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !editModalEl.classList.contains('hidden')) closeEditModal();
    });

    function filterTable() {
        const q = (document.getElementById('searchBox').value || '').toLowerCase();
        const level = document.getElementById('filterLevel').value;
        document.querySelectorAll('#organisasiTable tbody tr[data-search]').forEach(row => {
            const matchSearch = !q || row.dataset.search.includes(q);
            const matchLevel = !level || row.dataset.level === level;
            row.style.display = (matchSearch && matchLevel) ? '' : 'none';
        });
    }

    // Aktifkan animasi reveal (admin layout hanya menyediakan CSS-nya)
    (function activateReveal() {
        const reveals = document.querySelectorAll('.reveal');
        const reveal = () => {
            const windowHeight = window.innerHeight;
            reveals.forEach(el => {
                if (el.getBoundingClientRect().top < windowHeight - 50) {
                    el.classList.add('active');
                }
            });
        };
        window.addEventListener('scroll', reveal);
        window.addEventListener('load', reveal);
        document.addEventListener('DOMContentLoaded', reveal);
        reveal();
    })();

    @if(session('success'))
        SwalHelper.toastSuccess(@json(session('success')));
    @endif

    @if(session('error'))
        SwalHelper.toastError(
            @json(session('error')),
            @json(session('error_solution') ?? 'Periksa data organisasi yang dimasukkan, lalu coba lagi.')
        );
    @endif
</script>
@endsection
