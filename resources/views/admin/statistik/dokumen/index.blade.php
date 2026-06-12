@extends('layouts.admin')

@section('title', 'Statistik Dokumen - Admin Disdukcapil Toba')

@section('content')
<div class="container-fluid p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="bi bi-file-earmark-text me-2"></i>Statistik Dokumen
            </h1>
            <p class="text-gray-600 mt-1">Data statistik penerbitan dokumen kependudukan</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($canCreate)
            <button data-style-guide-skip type="button" onclick="openModal('create')" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 active:scale-95 transition-all">
                <i class="bi bi-plus-circle"></i>
                Tambah Data
            </button>
            @endif
            @if($canGenerate)
            <button data-style-guide-skip type="button" onclick="openGenerateModal()" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 active:scale-95 transition-all">
                <i class="bi bi-arrow-clockwise"></i>
                Generate Otomatis
            </button>
            @endif
        </div>
    </div>

    <!-- Filter -->
    <form method="GET" class="mb-6">
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span class="font-semibold text-gray-700">Filter:</span>
            </div>
            <select name="tahun" class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                @foreach($tahunTersedia as $t)
                    <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide">Kartu Keluarga</p>
            <h3 class="text-2xl font-bold text-blue-700 mt-1">{{ number_format($summary['total_kk'], 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-green-600 uppercase tracking-wide">Akte Lahir</p>
            <h3 class="text-2xl font-bold text-green-700 mt-1">{{ number_format($summary['total_akte_lahir'], 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Akte Kematian</p>
            <h3 class="text-2xl font-bold text-red-700 mt-1">{{ number_format($summary['total_akte_kematian'], 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-purple-600 uppercase tracking-wide">KTP</p>
            <h3 class="text-2xl font-bold text-purple-700 mt-1">{{ number_format($summary['total_ktp'], 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-yellow-600 uppercase tracking-wide">KIA</p>
            <h3 class="text-2xl font-bold text-yellow-700 mt-1">{{ number_format($summary['total_kia'], 0, ',', '.') }}</h3>
        </div>
    </div>

    <!-- Total -->
    <div class="bg-blue-600 rounded-xl shadow-sm p-6 mb-6 text-white">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm opacity-80">Total Dokumen</p>
                <h3 class="text-3xl font-bold">{{ number_format($summary['total_dokumen'], 0, ',', '.') }}</h3>
            </div>
            <i class="bi bi-files text-5xl opacity-50"></i>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-blue-700 text-white">
                    <tr>
                        <th class="p-4 font-semibold uppercase text-xs text-left">No</th>
                        <th class="p-4 font-semibold uppercase text-xs text-left">Periode</th>
                        <th class="p-4 font-semibold uppercase text-xs text-center">KK</th>
                        <th class="p-4 font-semibold uppercase text-xs text-center">Akte Lahir</th>
                        <th class="p-4 font-semibold uppercase text-xs text-center">Akte Kematian</th>
                        <th class="p-4 font-semibold uppercase text-xs text-center">KTP</th>
                        <th class="p-4 font-semibold uppercase text-xs text-center">KIA</th>
                        <th class="p-4 font-semibold uppercase text-xs text-center">Total</th>
                        <th class="p-4 font-semibold uppercase text-xs text-center">Sumber</th>
                        <th class="p-4 font-semibold uppercase text-xs text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $row)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 text-sm text-gray-700">{{ $loop->iteration }}</td>
                        <td class="p-4 text-sm font-semibold text-gray-800">{{ $row->nama_bulan }} {{ $row->tahun }}</td>
                        <td class="p-4 text-sm text-gray-700 text-center">{{ number_format($row->jumlah_kk, 0, ',', '.') }}</td>
                        <td class="p-4 text-sm text-gray-700 text-center">{{ number_format($row->jumlah_akte_lahir, 0, ',', '.') }}</td>
                        <td class="p-4 text-sm text-gray-700 text-center">{{ number_format($row->jumlah_akte_kematian, 0, ',', '.') }}</td>
                        <td class="p-4 text-sm text-gray-700 text-center">{{ number_format($row->jumlah_ktp, 0, ',', '.') }}</td>
                        <td class="p-4 text-sm text-gray-700 text-center">{{ number_format($row->jumlah_kia, 0, ',', '.') }}</td>
                        <td class="p-4 text-sm font-bold text-gray-800 text-center">{{ number_format($row->total_dokumen, 0, ',', '.') }}</td>
                        <td class="p-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $row->is_auto_generated ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-gray-50 text-gray-600 border-gray-100' }}">
                                {{ $row->is_auto_generated ? 'Auto' : 'Manual' }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @if($canEdit)
                                <button data-style-guide-skip type="button" onclick='openModal("edit", {{ json_encode(["id" => $row->statistik_dokumen_id, "tahun" => $row->tahun, "bulan" => $row->bulan, "jumlah_kk" => $row->jumlah_kk, "jumlah_akte_lahir" => $row->jumlah_akte_lahir, "jumlah_akte_kematian" => $row->jumlah_akte_kematian, "jumlah_ktp" => $row->jumlah_ktp, "jumlah_kia" => $row->jumlah_kia]) }})' 
                                        class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-700 font-medium transition">
                                    <i class="bi bi-pencil"></i> Ubah
                                </button>
                                @endif
                                @if($canDelete)
                                <form action="{{ route('admin.statistik-dokumen.destroy', $row->statistik_dokumen_id) }}" method="POST" class="inline-block delete-form" data-title="{{ $row->nama_bulan }} {{ $row->tahun }}">
                                    @csrf
                                    @method('DELETE')
                                    <button data-style-guide-skip type="submit" class="inline-flex items-center gap-1.5 text-xs text-red-500 hover:text-red-600 font-medium transition">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="p-8 text-center text-gray-500">
                            <i class="bi bi-inbox text-4xl block mb-2"></i>
                            Tidak ada data statistik dokumen
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Form Tambah/Edit --}}
<div id="modalForm" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white rounded-t-2xl">
            <h2 id="modalTitle" class="text-lg font-bold text-gray-800">Tambah Data</h2>
            <button data-style-guide-skip type="button" onclick="closeModal()" class="w-10 h-10 rounded-xl hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="formData" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                    <select name="tahun" id="field_tahun" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bulan <span class="text-red-500">*</span></label>
                    <select name="bulan" id="field_bulan" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach(App\Models\StatistikDokumen::BULAN_INDONESIA as $key => $nama)
                            <option value="{{ $key }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">KK</label>
                    <input type="number" name="jumlah_kk" id="field_kk" value="0" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Akte Lahir</label>
                    <input type="number" name="jumlah_akte_lahir" id="field_akte_lahir" value="0" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Akte Kematian</label>
                    <input type="number" name="jumlah_akte_kematian" id="field_akte_kematian" value="0" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">KTP</label>
                    <input type="number" name="jumlah_ktp" id="field_ktp" value="0" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">KIA</label>
                    <input type="number" name="jumlah_kia" id="field_kia" value="0" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
            </div>

            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-700">Total Dokumen (Auto)</span>
                    <span id="totalDokumen" class="text-2xl font-bold text-blue-700">0</span>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button data-style-guide-skip type="button" onclick="closeModal()" class="px-5 py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold transition">Batal</button>
                <button data-style-guide-skip type="submit" class="px-5 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700 active:scale-95 transition-all shadow-sm">
                    <span id="btnText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Generate Otomatis --}}
<div id="modalGenerate" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white rounded-t-2xl">
            <h2 class="text-lg font-bold text-gray-800">Generate Statistik Dokumen</h2>
            <button data-style-guide-skip type="button" onclick="closeGenerateModal()" class="w-10 h-10 rounded-xl hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="formGenerate" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun</label>
                <select name="tahun" id="gen_tahun" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Periode</label>
                <select name="periode" id="gen_periode" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="tahun">Seluruh Tahun</option>
                    <option value="bulan">Bulan Tertentu</option>
                </select>
            </div>
            <div id="bulanRange" class="hidden grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bulan Awal</label>
                    <select name="bulan_awal" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach(App\Models\StatistikDokumen::BULAN_INDONESIA as $key => $nama)
                            <option value="{{ $key }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bulan Akhir</label>
                    <select name="bulan_akhir" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach(App\Models\StatistikDokumen::BULAN_INDONESIA as $key => $nama)
                            <option value="{{ $key }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button data-style-guide-skip type="button" onclick="closeGenerateModal()" class="px-5 py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold transition">Batal</button>
                <button data-style-guide-skip type="submit" class="px-5 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700 active:scale-95 transition-all shadow-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i> Generate
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const modal = document.getElementById('modalForm');
    const form = document.getElementById('formData');
    const titleEl = document.getElementById('modalTitle');
    const methodEl = document.getElementById('formMethod');
    const btnTextEl = document.getElementById('btnText');
    const baseUrl = '{{ url("/admin/statistik-dokumen") }}';
    const storeUrl = '{{ route("admin.statistik-dokumen.store") }}';

    function calculateTotal() {
        var total = 0;
        ['field_kk', 'field_akte_lahir', 'field_akte_kematian', 'field_ktp', 'field_kia'].forEach(function(id) {
            total += parseInt(document.getElementById(id).value) || 0;
        });
        document.getElementById('totalDokumen').textContent = total.toLocaleString('id-ID');
    }

    ['field_kk', 'field_akte_lahir', 'field_akte_kematian', 'field_ktp', 'field_kia'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', calculateTotal);
    });

    window.openModal = function (mode, item) {
        form.reset();
        document.getElementById('field_tahun').value = '{{ date("Y") }}';
        document.getElementById('field_bulan').value = '1';
        document.getElementById('field_kk').value = '0';
        document.getElementById('field_akte_lahir').value = '0';
        document.getElementById('field_akte_kematian').value = '0';
        document.getElementById('field_ktp').value = '0';
        document.getElementById('field_kia').value = '0';
        document.getElementById('totalDokumen').textContent = '0';

        if (mode === 'create') {
            titleEl.textContent = 'Tambah Data Manual';
            methodEl.value = 'POST';
            btnTextEl.textContent = 'Simpan';
            form.action = storeUrl;
        } else if (item) {
            titleEl.textContent = 'Ubah Data';
            methodEl.value = 'PUT';
            btnTextEl.textContent = 'Update';
            form.action = baseUrl + '/' + item.id;
            document.getElementById('field_tahun').value = item.tahun;
            document.getElementById('field_bulan').value = item.bulan;
            document.getElementById('field_kk').value = item.jumlah_kk;
            document.getElementById('field_akte_lahir').value = item.jumlah_akte_lahir;
            document.getElementById('field_akte_kematian').value = item.jumlah_akte_kematian;
            document.getElementById('field_ktp').value = item.jumlah_ktp;
            document.getElementById('field_kia').value = item.jumlah_kia;
            calculateTotal();
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    window.closeModal = function () {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    };

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = e.submitter || form.querySelector('button[type="submit"]');
        const isPut = methodEl.value === 'PUT';
        btn.disabled = true;
        btnTextEl.textContent = isPut ? 'Mengupdate...' : 'Menyimpan...';

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async response => {
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) throw payload;
            return payload;
        })
        .then(r => {
            if (r.success) {
                closeModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: r.message,
                    confirmButtonColor: '#16a34a'
                }).then(() => window.location.reload());
                return;
            }
            throw r;
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: err.message || 'Terjadi kesalahan saat menyimpan data'
            });
            btn.disabled = false;
            btnTextEl.textContent = isPut ? 'Update' : 'Simpan';
        });
    });

    // Generate Modal
    const genModal = document.getElementById('modalGenerate');
    window.openGenerateModal = function () {
        document.getElementById('gen_periode').value = 'tahun';
        document.getElementById('bulanRange').classList.add('hidden');
        genModal.classList.remove('hidden');
        genModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };
    window.closeGenerateModal = function () {
        genModal.classList.add('hidden');
        genModal.classList.remove('flex');
        document.body.style.overflow = '';
    };
    genModal.addEventListener('click', function (e) {
        if (e.target === genModal) closeGenerateModal();
    });
    document.getElementById('gen_periode').addEventListener('change', function() {
        document.getElementById('bulanRange').classList.toggle('hidden', this.value !== 'bulan');
    });
    document.getElementById('formGenerate').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = e.submitter;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generating...';
        
        var formData = new FormData(this);
        if (document.getElementById('gen_periode').value !== 'bulan') {
            formData.delete('bulan_awal');
            formData.delete('bulan_akhir');
        }
        
        fetch('{{ route("admin.statistik-dokumen.generate") }}', {
            method: 'POST',
            body: formData,
            headers: { 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(r => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Generate';
            if (r.success) {
                closeGenerateModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: r.message,
                    confirmButtonColor: '#16a34a'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Informasi',
                    text: r.message || 'Tidak ada data untuk periode ini'
                });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Generate';
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan koneksi'
            });
        });
    });

    // Delete forms
    document.querySelectorAll('.delete-form').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            e.preventDefault();
            const t = f.getAttribute('data-title') || 'data ini';
            Swal.fire({
                title: 'Hapus Data?',
                html: 'Apakah Anda yakin ingin menghapus <strong>' + t + '</strong>?',
                icon: false,
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#e5e7eb',
                confirmButtonText: 'Konfirmasi',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(f.action, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(r => {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: r.message, toast: true, position: 'top-end', timer: 5000 });
                            setTimeout(() => { window.location.reload(); }, 1000);
                        }
                    });
                }
            });
        });
    });
})();
</script>
@endpush
