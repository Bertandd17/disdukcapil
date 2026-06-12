@extends('layouts.admin')

@section('title', 'Statistik Penduduk - Admin Disdukcapil Toba')

@section('content')
<div class="container-fluid p-6 bg-gray-50 min-h-screen">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="bi bi-people me-2"></i>Statistik Penduduk
            </h1>
            <p class="text-gray-600 mt-1">Data statistik penduduk per kecamatan</p>
        </div>
        @if($canCreate)
        <button data-style-guide-skip type="button" onclick="openModal('create')" 
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 active:scale-95 transition-all">
            <i class="bi bi-plus-circle"></i>
            Tambah Data
        </button>
        @endif
    </div>

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
                                <button data-style-guide-skip type="button" onclick='openModal("edit", {{ json_encode(["id" => $row->statistik_penduduk_id, "kecamatan_id" => $row->kecamatan_id, "tahun" => $row->tahun, "total_penduduk" => $row->total_penduduk, "nama_kecamatan" => $row->kecamatan->nama_kecamatan ?? ""]) }})' 
                                        class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-700 font-medium transition">
                                    <i class="bi bi-pencil"></i> Ubah
                                </button>
                                @endif
                                @if($canDelete)
                                <form action="{{ route('admin.statistik-penduduk.destroy', $row->statistik_penduduk_id) }}" method="POST" class="inline-block delete-form" data-title="{{ $row->kecamatan->nama_kecamatan ?? '' }} {{ $row->tahun }}">
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
            <button data-style-guide-skip type="button" onclick="closeModal()" class="w-10 h-10 rounded-xl hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="formData" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kecamatan <span class="text-red-500">*</span></label>
                <select name="kecamatan_id" id="field_kecamatan" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih Kecamatan --</option>
                    @foreach($kecamatan as $k)
                        <option value="{{ $k->kecamatan_id }}">{{ $k->nama_kecamatan }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                <select name="tahun" id="field_tahun" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @for($y = date('Y'); $y >= date('Y') - 10; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Total Penduduk <span class="text-red-500">*</span></label>
                <input type="number" name="total_penduduk" id="field_total" required min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Masukkan jumlah penduduk">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button data-style-guide-skip type="button" onclick="closeModal()" class="px-5 py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold transition">Batal</button>
                <button data-style-guide-skip type="button" id="btnBukaKonfirmasi" onclick="showKonfirmasiSimpan()" class="px-5 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700 active:scale-95 transition-all shadow-sm">
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Simpan --}}
<div id="modalKonfirmasi" class="fixed inset-0 z-[110] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center">
        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-question-octagon-fill text-yellow-500 text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-800 mb-2">Konfirmasi Penyimpanan</h3>
        <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin menyimpan data ini?</p>
        <div class="flex gap-3">
            <button data-style-guide-skip type="button" onclick="closeKonfirmasiModal()" class="flex-1 px-4 py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold transition">Batal</button>
            <button data-style-guide-skip type="button" id="btnKonfirmasiSimpan" class="flex-1 px-4 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700 active:scale-95 transition-all shadow-sm">
                <span id="btnKonfirmasiText">Ya, Simpan</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const modal = document.getElementById('modalForm');
    const konfirmasiModal = document.getElementById('modalKonfirmasi');
    const form = document.getElementById('formData');
    const titleEl = document.getElementById('modalTitle');
    const methodEl = document.getElementById('formMethod');
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
            form.action = storeUrl;
        } else if (item) {
            titleEl.textContent = 'Ubah Data';
            methodEl.value = 'PUT';
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

    window.closeKonfirmasiModal = function () {
        konfirmasiModal.classList.add('hidden');
        konfirmasiModal.classList.remove('flex');
    };

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    // E5 FIX: showKonfirmasiSimpan hanya tampilkan modal, TANPA submit langsung
    window.showKonfirmasiSimpan = function () {
        // Validasi form dulu
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Tampilkan modal konfirmasi
        konfirmasiModal.classList.remove('hidden');
        konfirmasiModal.classList.add('flex');
    };

    // E5 FIX: Submit HANYA terjadi saat tombol konfirmasi diklik
    document.getElementById('btnKonfirmasiSimpan').addEventListener('click', function() {
        closeKonfirmasiModal();

        const btnTextEl = document.getElementById('btnKonfirmasiText');
        btnTextEl.textContent = 'Menyimpan...';

        // Submit form secara langsung (bukan via event listener)
        form.submit();
    });

    // E5 FIX: Hapus event listener submit yang auto-submit tanpa konfirmasi
    // Form submit handler - TETAP ada tapi hanya dipanggil dari tombol konfirmasi
    form.addEventListener('submit', function(e) {
        const btnTextEl = document.getElementById('btnKonfirmasiText');
        btnTextEl.textContent = 'Menyimpan...';

        // Biarkan form submit secara natural (tidak e.preventDefault())
        // sehingga redirect ke server dan session flash muncul
    });

    document.querySelectorAll('.delete-form').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            e.preventDefault();
            const t = f.getAttribute('data-title') || 'data ini';
            Swal.fire({
                title: 'Hapus Data?',
                html: 'Apakah Anda yakin ingin menghapus <strong>' + t + '</strong>?',
                icon: false,
                showCancelButton: true,
                showDenyButton: false,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#e5e7eb',
                confirmButtonText: 'Konfirmasi',
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
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: r.message, toast: true, position: 'top-end', timer: 5000 });
                            setTimeout(() => { window.location.reload(); }, 1000);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal!', text: r.message });
                        }
                    });
                }
            });
        });
    });

    // E5 FIX: SweetAlert sukses HANYA dari session flash (server-side)
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        toast: true,
        position: 'top-end',
        timer: 5000
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session('error') }}'
    });
    @endif
})();
</script>
@endpush
