@extends('layouts.admin')

@section('title', 'Statistik Penduduk - Admin Disdukcapil Toba')

@section('content')
<div class="container-fluid p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="bi bi-people me-2"></i>Statistik Penduduk
            </h1>
            <p class="text-gray-600 mt-1">Data statistik penduduk per kecamatan</p>
        </div>
        @if($canCreate)
        <button type="button" onclick="openModal('create')" 
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl font-semibold hover:from-green-700 hover:to-green-800 transition shadow-sm">
            <i class="bi bi-plus-circle"></i>
            Tambah Data
        </button>
        @endif
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
            <select name="kecamatan_id" class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                <option value="">Semua Kecamatan</option>
                @foreach($kecamatan as $k)
                    <option value="{{ $k->kecamatan_id }}" {{ $kecamatanId == $k->kecamatan_id ? 'selected' : '' }}>{{ $k->nama_kecamatan }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide">Total Penduduk</p>
            <h3 class="text-3xl font-bold text-blue-700 mt-1">{{ number_format($summary['total_penduduk'], 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-green-600 uppercase tracking-wide">Jumlah Kecamatan</p>
            <h3 class="text-3xl font-bold text-green-700 mt-1">{{ $summary['jumlah_kecamatan'] }}</h3>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-purple-600 uppercase tracking-wide">Rata-rata Penduduk</p>
            <h3 class="text-3xl font-bold text-purple-700 mt-1">{{ number_format($summary['rata_rata'], 0, ',', '.') }}</h3>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-blue-700 text-white">
                    <tr>
                        <th class="p-4 font-semibold uppercase text-xs text-left">No</th>
                        <th class="p-4 font-semibold uppercase text-xs text-left">Kecamatan</th>
                        <th class="p-4 font-semibold uppercase text-xs text-center">Tahun</th>
                        <th class="p-4 font-semibold uppercase text-xs text-right">Total Penduduk</th>
                        <th class="p-4 font-semibold uppercase text-xs text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $row)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 text-sm text-gray-700">{{ $loop->iteration }}</td>
                        <td class="p-4 text-sm font-semibold text-gray-800">{{ $row->kecamatan->nama_kecamatan ?? '-' }}</td>
                        <td class="p-4 text-sm text-gray-700 text-center">{{ $row->tahun }}</td>
                        <td class="p-4 text-sm font-bold text-gray-800 text-right">{{ number_format($row->total_penduduk, 0, ',', '.') }}</td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @if($canEdit)
                                <button type="button" onclick='openModal("edit", {{ json_encode(["id" => $row->statistik_penduduk_id, "kecamatan_id" => $row->kecamatan_id, "tahun" => $row->tahun, "total_penduduk" => $row->total_penduduk, "nama_kecamatan" => $row->kecamatan->nama_kecamatan ?? ""]) }})' 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-blue-600 hover:bg-blue-50 rounded-lg font-medium transition text-xs">
                                    <i class="bi bi-pencil"></i> Ubah
                                </button>
                                @endif
                                @if($canDelete)
                                <form action="{{ route('admin.statistik-penduduk.destroy', $row->statistik_penduduk_id) }}" method="POST" class="inline-block delete-form" data-title="{{ $row->kecamatan->nama_kecamatan ?? '' }} {{ $row->tahun }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-red-600 hover:bg-red-50 rounded-lg font-medium transition text-xs">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500">
                            <i class="bi bi-inbox text-4xl block mb-2"></i>
                            Tidak ada data statistik penduduk
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Form --}}
<div id="modalForm" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white rounded-t-2xl">
            <h2 id="modalTitle" class="text-lg font-bold text-gray-800">Tambah Data</h2>
            <button type="button" onclick="closeModal()" class="w-10 h-10 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="formData" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kecamatan <span class="text-red-500">*</span></label>
                <select name="kecamatan_id" id="field_kecamatan" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih Kecamatan --</option>
                    @foreach($kecamatan as $k)
                        <option value="{{ $k->kecamatan_id }}">{{ $k->nama_kecamatan }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                <select name="tahun" id="field_tahun" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @for($y = date('Y'); $y >= date('Y') - 10; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Total Penduduk <span class="text-red-500">*</span></label>
                <input type="number" name="total_penduduk" id="field_total" required min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Masukkan jumlah penduduk">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeModal()" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" id="btnSubmit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold hover:from-green-700 hover:to-green-800 transition shadow-sm">
                    <span id="btnText">Simpan</span>
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
    const baseUrl = '{{ url("/admin/statistik-penduduk") }}';
    const storeUrl = '{{ route("admin.statistik-penduduk.store") }}';

    window.openModal = function (mode, item) {
        form.reset();
        document.getElementById('field_kecamatan').value = '';
        document.getElementById('field_tahun').value = '{{ date("Y") }}';
        document.getElementById('field_total').value = '';

        if (mode === 'create') {
            titleEl.textContent = 'Tambah Data';
            methodEl.value = 'POST';
            btnTextEl.textContent = 'Simpan';
            form.action = storeUrl;
        } else if (item) {
            titleEl.textContent = 'Ubah Data';
            methodEl.value = 'PUT';
            btnTextEl.textContent = 'Update';
            form.action = baseUrl + '/' + item.id;
            document.getElementById('field_kecamatan').value = item.kecamatan_id;
            document.getElementById('field_tahun').value = item.tahun;
            document.getElementById('field_total').value = item.total_penduduk;
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

    // Form submit handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmit');
        const btnText = document.getElementById('btnText');
        btn.disabled = true;
        btnText.textContent = 'Menyimpan...';
        
        // Create FormData with proper method override
        const formData = new FormData(form);
        const isPut = methodEl.value === 'PUT';
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(r => {
            if (r.success) {
                closeModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: r.message,
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: r.message || 'Terjadi kesalahan'
                });
                btn.disabled = false;
                btnText.textContent = isPut ? 'Update' : 'Simpan';
            }
        })
        .catch(err => {
            console.error('Error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan koneksi'
            });
            btn.disabled = false;
            btnText.textContent = isPut ? 'Update' : 'Simpan';
        });
    });

    document.querySelectorAll('.delete-form').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            e.preventDefault();
            const t = f.getAttribute('data-title') || 'data ini';
            Swal.fire({
                title: 'Hapus Data?',
                html: 'Apakah Anda yakin ingin menghapus <strong>' + t + '</strong>?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(f.action, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(r => {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: r.message, toast: true, position: 'top-end', timer: 3000 });
                            setTimeout(() => { window.location.reload(); }, 1000);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal!', text: r.message });
                        }
                    });
                }
            });
        });
    });
})();
</script>
@endpush
