@extends('layouts.admin')

@section('content')
    {{-- 1. Welcome Banner --}}
        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-2xl p-6 md:p-8 text-white mb-6 reveal shadow-lg">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold mb-2">Manajemen Akun</h2>
                    <p class="text-blue-100 text-lg">Kelola akun keagamaan dan petugas di sistem Disdukcapil</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2">
                        <p class="text-xs text-blue-100">Total Akun</p>
                        <p class="text-2xl font-bold">{{ $users->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 reveal">
            <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-xl text-indigo-600"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Total</span>
                </div>
                <h3 class="text-3xl font-extrabold text-gray-800 mb-1">{{ $users->count() }}</h3>
                <p class="text-sm text-gray-600 font-medium">Total Akun Terdaftar</p>
            </div>

            <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-check text-xl text-green-600"></i>
                    </div>
                    <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">Aktif</span>
                </div>
                <h3 class="text-3xl font-extrabold text-green-600 mb-1">
                    {{ $users->where('detail_keagamaan.status', 'aktif')->count() }}
                </h3>
                <p class="text-sm text-gray-600 font-medium">Akun Aktif</p>
            </div>

            <div class="stat-card bg-white rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-slash text-xl text-red-600"></i>
                    </div>
                    <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full">Non-Aktif</span>
                </div>
                <h3 class="text-3xl font-extrabold text-red-600 mb-1">
                    {{ $users->where('detail_keagamaan.status', 'non-aktif')->count() }}
                </h3>
                <p class="text-sm text-gray-600 font-medium">Akun Non-Aktif</p>
            </div>
        </div>

        {{-- 3. Search and Add Button --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-8 reveal">
            <div class="p-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="relative w-full md:w-1/2">
                    <input type="text" id="searchBox" onkeyup="filterAccounts()"
                        placeholder="Cari nama atau username..."
                        class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <button onclick="openAddModal()"
                    class="w-full md:w-auto px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl font-bold hover:from-green-700 hover:to-green-800 transition-all shadow-lg flex items-center justify-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Akun
                </button>
            </div>

            {{-- 4. User Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="userTable">
                    <thead class="bg-gray-50 text-[11px] uppercase font-bold text-gray-400 tracking-widest">
                        <tr>
                            <th class="px-8 py-5">Nama Lengkap</th>
                            <th class="px-8 py-5">Username</th>
                            <th class="px-8 py-5">Agama</th>
                            <th class="px-8 py-5">Alamat</th>
                            <th class="px-8 py-5 text-center">Status</th>
                            <th class="px-8 py-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="accountTableBody" class="divide-y divide-gray-50">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-8 py-5 font-bold text-gray-700">{{ $user->name }}</td>
                                <td class="px-8 py-5 text-gray-500 italic">@ {{ $user->username }}</td>
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-bold uppercase">
                                        {{ $user->detail_keagamaan->jenis_keagamaan->nama_jenis_keagamaan ?? 'Umum' }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-gray-600 text-sm">{{ $user->detail_keagamaan->alamat ?? '-' }}</td>
                                <td class="px-8 py-5 text-center">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase {{ ($user->detail_keagamaan->status ?? 'aktif') === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ ($user->detail_keagamaan->status ?? 'aktif') === 'aktif' ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <button onclick="editAccount({{ json_encode($user->load('detail_keagamaan')) }})"
                                        class="px-4 py-2 bg-gray-100 text-blue-600 rounded-xl text-xs font-bold hover:bg-gray-200 hover:text-blue-700 transition-all">
                                        <i class="fas fa-edit mr-1"></i> Edit Akun
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 5. Religion Statistics --}}
        <div id="religionStats" class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-10 reveal">
            @php $religions = ['Islam', 'Kristen Protestan', 'Kristen Katolik', 'Hindu', 'Buddha', 'Konghucu']; @endphp
            @foreach($religions as $rel)
                        <div class="p-4 bg-white rounded-2xl border border-gray-100 shadow-sm text-center">
                            <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">{{ $rel }}</p>
                            <p class="text-xl font-black text-blue-600">
                                {{ $users->filter(function ($u) use ($rel) {
        return ($u->detail_keagamaan->jenis_keagamaan->nama_jenis_keagamaan ?? '') === $rel;
    })->count() }}
                            </p>
                        </div>
            @endforeach
        </div>

        {{-- 6. MODAL (ID: accountModal harus ada agar JS jalan) --}}
        <div id="accountModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 id="modalTitle" class="text-xl font-bold text-gray-800">Tambah Akun Baru</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>

                <form id="accountForm" action="{{ route('admin.manajemen-akun.store') }}" method="POST" class="p-8 space-y-5">
                    @csrf
                    <input type="hidden" name="accountId" id="accountId">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap *</label>
                            <input type="text" name="name" id="fullName" required class="w-full px-4 py-3 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Nama lengkap">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Username *</label>
                            <input type="text" name="username" id="username_field" required class="w-full px-4 py-3 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Username">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Agama *</label>
                        <select name="agama" id="agama_select" required class="w-full px-4 py-3 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                            <option value="" disabled selected>Pilih agama...</option>
                            @foreach($list_agama as $agama)
                                <option value="{{ $agama->jenis_keagamaan_id }}">{{ $agama->nama_jenis_keagamaan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Lengkap *</label>
                        <textarea name="alamat" id="alamat_field" required rows="2" class="w-full px-4 py-3 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-sm resize-none" placeholder="Alamat lengkap"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Password <span id="pwReq" class="text-red-500">*</span></label>
                            <input type="password" name="password" id="password_field" class="w-full px-4 py-3 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Min 8 karakter">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi <span id="pwConfReq" class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation_field" class="w-full px-4 py-3 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Ulangi password">
                        </div>
                    </div>

                    <div id="statusSection" class="hidden p-4 bg-gray-50 border rounded-xl">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Status Akun</label>
                        <select name="status" id="status_select" class="w-full px-4 py-2 border rounded-lg text-sm">
                            <option value="aktif">Aktif</option>
                            <option value="non-aktif">Non-Aktif</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-6 border-t">
                        <button type="button" onclick="closeModal()" class="w-full py-3 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200">
                            Batal
                        </button>
                        <button type="submit" id="saveAccountBtn" class="w-full py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl font-bold hover:from-green-700 hover:to-green-800 shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="saveAccountText"><i class="fas fa-save mr-2"></i> Simpan Akun</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
@endsection
@push('scripts')
    <script>
        // Fungsi untuk animasi reveal
        function reveal() {
            const reveals = document.querySelectorAll('.reveal');
            reveals.forEach(el => {
                const windowHeight = window.innerHeight;
                const elementTop = el.getBoundingClientRect().top;
                if (elementTop < windowHeight - 50) el.classList.add('active');
            });
        }
        window.addEventListener('scroll', reveal);
        window.addEventListener('load', reveal);

        // FUNGSI TAMBAH AKUN (CREATE)
       function openAddModal() {
            // 1. Reset Form
            const form = document.getElementById('accountForm');
            form.reset();

            // 2. PAKSA KOSONGKAN HIDDEN ID
            // Jika bagian ini terlewat, ID user sebelumnya akan terbawa dan menyebabkan "Update" bukan "Create"
            const accountIdInput = document.getElementById('accountId');
            accountIdInput.value = "";

            // 3. UI Reset
            document.getElementById('modalTitle').innerText = 'Tambah Akun Baru';
            document.getElementById('statusSection').classList.add('hidden');
            document.getElementById('password_field').required = true;
            document.getElementById('password_confirmation_field').required = true;
            document.getElementById('pwReq').classList.remove('hidden');
            document.getElementById('pwConfReq').classList.remove('hidden');

            // Tampilkan Modal
            document.getElementById('accountModal').classList.remove('hidden');
        }

        function editAccount(user) {
            // Isi ID untuk Update
            document.getElementById('accountId').value = user.id;
            document.getElementById('fullName').value = user.name;
            document.getElementById('username_field').value = user.username;

            if (user.detail_keagamaan) {
                document.getElementById('agama_select').value = user.detail_keagamaan.jenis_keagamaan_id;
                document.getElementById('alamat_field').value = user.detail_keagamaan.alamat;
                document.getElementById('status_select').value = user.detail_keagamaan.status;
            }

            document.getElementById('modalTitle').innerText = 'Edit Akun';
            document.getElementById('statusSection').classList.remove('hidden');
            document.getElementById('password_field').value = '';
            document.getElementById('password_confirmation_field').value = '';
            document.getElementById('password_field').required = false;
            document.getElementById('password_confirmation_field').required = false;
            document.getElementById('pwReq').classList.add('hidden');
            document.getElementById('pwConfReq').classList.add('hidden');

            document.getElementById('accountModal').classList.remove('hidden');
        }


        function closeModal() {
            document.getElementById('accountModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Filter Pencarian Table
        function filterAccounts() {
            const q = document.getElementById('searchBox').value.toLowerCase();
            const rows = document.querySelectorAll('#accountTableBody tr');
            rows.forEach(row => {
                const name = row.children[0].innerText.toLowerCase();
                const user = row.children[1].innerText.toLowerCase();
                row.style.display = (name.includes(q) || user.includes(q)) ? "" : "none";
            });
        }

        function showToast(type, message) {
            if (window.SwalHelper && typeof window.SwalHelper[type] === 'function') {
                window.SwalHelper[type](message);
                return;
            }

            if (window.Swal) {
                Swal.fire({
                    icon: type,
                    title: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: type === 'success' ? 3000 : 5000,
                    timerProgressBar: true,
                    backdrop: false
                });
            }
        }

        function getErrorMessage(payload) {
            if (payload && payload.errors) {
                return Object.values(payload.errors).flat().join('\n');
            }

            if (payload && payload.message) {
                return payload.detail ? `${payload.message}\n${payload.detail}` : payload.message;
            }

            return 'Terjadi kesalahan saat menyimpan akun.';
        }

        document.getElementById('accountForm').addEventListener('submit', function (event) {
            event.preventDefault();

            const form = event.currentTarget;
            const saveBtn = document.getElementById('saveAccountBtn');
            const saveText = document.getElementById('saveAccountText');
            const isEdit = Boolean(document.getElementById('accountId').value);

            saveBtn.disabled = true;
            saveText.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i>' + (isEdit ? 'Memperbarui...' : 'Menyimpan...');

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async function (response) {
                const payload = await response.json().catch(function () { return {}; });
                if (!response.ok) throw payload;
                return payload;
            })
            .then(function (payload) {
                if (!payload.success) throw payload;

                closeModal();
                showToast('success', payload.message || 'Akun berhasil disimpan.');
                setTimeout(function () {
                    window.location.reload();
                }, 900);
            })
            .catch(function (payload) {
                showToast('error', getErrorMessage(payload));
            })
            .finally(function () {
                saveBtn.disabled = false;
                saveText.innerHTML = '<i class="fas fa-save mr-2"></i> Simpan Akun';
            });
        });
    </script>
@endpush
