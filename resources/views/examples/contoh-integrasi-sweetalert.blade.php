{{-- 
    =====================================================
    CONTOH INTEGRASI SWEETALERT2 DENGAN PHP
    Disdukcapil Toba - Contoh Lengkap
    =====================================================
--}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contoh Integrasi SweetAlert2 - Disdukcapil Toba</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Disdukcapil Style Guide -->
    <link rel="stylesheet" href="{{ asset('css/style-guide.css') }}">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- SweetAlert Disdukcapil -->
    <script src="{{ asset('js/swal-final-fix.js') }}?v={{ filemtime(public_path('js/swal-final-fix.js')) }}"></script>

    <style>
        .swal2-loader {
            display: inline-block;
            width: 50px;
            height: 50px;
            border: 4px solid #e5e7eb;
            border-top: 4px solid #16a34a;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-8">

    <div class="max-w-4xl mx-auto">
        
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-bell text-green-600 mr-3"></i>
                Contoh Integrasi SweetAlert2 - Disdukcapil
            </h1>
            <p class="text-gray-600">
                Demonstration of notification system integration with PHP backend
            </p>
        </div>

        <!-- =====================================================
             CONTOH 1: FORM SIMPAN DATA PENDUDUK (KK/KTP)
             ===================================================== -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-3">
                <i class="fas fa-user-plus text-blue-600 mr-2"></i>
                1. Form Simpan Data Penduduk (KK/KTP)
            </h2>
            
            <form id="formSimpanPenduduk" class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">NIK <span class="text-red-500">*</span></label>
                        <input type="text" name="nik" id="inputNik" maxlength="16" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="16 digit NIK">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" id="inputNama"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Sesuai KTP">
                    </div>
                </div>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tempat Lahir <span class="text-red-500">*</span></label>
                        <input type="text" name="tempat_lahir" id="inputTempatLahir"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Lahir <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_lahir" id="inputTanggalLahir"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
                    <textarea name="alamat" id="inputAlamat" rows="3"
                              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                              placeholder="Alamat lengkap sesuai KTP"></textarea>
                </div>
                
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="resetFormSimpan()"
                            class="px-6 py-3 bg-gray-200 text-gray-800 rounded-xl font-semibold hover:bg-gray-300 transition">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit"
                            class="px-8 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition shadow-lg">
                        <i class="fas fa-save mr-2"></i>Simpan Data
                    </button>
                </div>
            </form>
            
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-700 mb-2"><i class="fas fa-code mr-2"></i>Kode PHP (simpan.php)</h4>
                <pre class="bg-gray-800 text-green-400 p-4 rounded-lg text-xs overflow-x-auto">// simpan.php - Endpoint untuk menyimpan data
&lt;?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

// Validasi sederhana
$errors = [];
if (empty($input['nik'])) {
    $errors[] = "NIK wajib diisi (16 digit)";
} elseif (strlen($input['nik']) !== 16 || !ctype_digit($input['nik'])) {
    $errors[] = "Format NIK tidak valid";
}
if (empty($input['nama'])) {
    $errors[] = "Nama lengkap wajib diisi";
}
if (empty($input['tempat_lahir'])) {
    $errors[] = "Tempat lahir wajib diisi";
}
if (empty($input['tanggal_lahir'])) {
    $errors[] = "Tanggal lahir wajib diisi";
}
if (empty($input['alamat'])) {
    $errors[] = "Alamat wajib diisi";
}

// Jika ada error validasi
if (!empty($errors)) {
    echo json_encode([
        'status' => 'validasi',
        'errors' => $errors
    ]);
    exit;
}

// Simulasi penyimpanan berhasil
$noReg = '#REG-' . date('Y') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

echo json_encode([
    'status' => 'success',
    'noReg' => $noReg,
    'message' => 'Data berhasil disimpan'
]);
</pre>
            </div>
        </div>

        <!-- =====================================================
             CONTOH 2: UPLOAD SCAN KTP/KK
             ===================================================== -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-3">
                <i class="fas fa-cloud-upload-alt text-blue-600 mr-2"></i>
                2. Upload Scan KTP/KK
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih File (PDF/JPG/PNG, maks 5MB)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-green-400 transition cursor-pointer"
                         onclick="document.getElementById('inputFileKTP').click()">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-600">Klik untuk pilih file atau drag & drop</p>
                        <p class="text-sm text-gray-400 mt-1">PDF, JPG, PNG - Maks 5MB</p>
                    </div>
                    <input type="file" id="inputFileKTP" name="file_ktp" accept=".pdf,.jpg,.jpeg,.png"
                           class="hidden" onchange="handleFileSelect(this)">
                    <p id="fileNameDisplay" class="mt-2 text-sm text-gray-600 hidden">
                        <i class="fas fa-file mr-2"></i><span></span>
                    </p>
                </div>
                
                <button type="button" id="btnUpload" onclick="uploadFileKTP()"
                        class="px-8 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    <i class="fas fa-upload mr-2"></i>Unggah File
                </button>
            </div>
            
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-700 mb-2"><i class="fas fa-code mr-2"></i>Kode PHP (upload.php)</h4>
                <pre class="bg-gray-800 text-green-400 p-4 rounded-lg text-xs overflow-x-auto">// upload.php - Endpoint untuk upload file
&lt;?php
header('Content-Type: application/json');

// Set timeout 30 detik
set_time_limit(30);

try {
    // Validasi method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan');
    }
    
    // Validasi file ada
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File gagal diunggah: ' . $_FILES['file']['error']);
    }
    
    $file = $_FILES['file'];
    
    // Validasi ukuran (5MB = 5 * 1024 * 1024)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('Ukuran file maksimal 5MB');
    }
    
    // Validasi tipe file
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Format file harus PDF, JPG, atau PNG');
    }
    
    // Generate nama file unik
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = 'ktp_' . time() . '_' . uniqid() . '.' . $extension;
    $uploadDir = 'uploads/';
    
    // Buat folder jika belum ada
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Pindahkan file
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
        throw new Exception('Gagal menyimpan file');
    }
    
    echo json_encode([
        'status' => 'success',
        'fileName' => $newName,
        'originalName' => $file['name'],
        'size' => $file['size']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
</pre>
            </div>
        </div>

        <!-- =====================================================
             CONTOH 3: TOMBOL HAPUS DATA
             ===================================================== -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-3">
                <i class="fas fa-trash-alt text-red-600 mr-2"></i>
                3. Tombol Hapus Data Penduduk
            </h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">No</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">NIK</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Nama</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Alamat</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-4 py-3">1</td>
                            <td class="px-4 py-3 font-mono">1201234567890123</td>
                            <td class="px-4 py-3 font-semibold">Budi Santoso</td>
                            <td class="px-4 py-3">Jl. Merdeka No. 1, Toba</td>
                            <td class="px-4 py-3 text-center">
                                <button onclick="konfirmasiHapus('Budi Santoso', 1)"
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-semibold">
                                    <i class="fas fa-trash-alt mr-1"></i>Hapus
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3">2</td>
                            <td class="px-4 py-3 font-mono">1209876543210987</td>
                            <td class="px-4 py-3 font-semibold">Siti Aminah</td>
                            <td class="px-4 py-3">Jl. Sudirman No. 5, Toba</td>
                            <td class="px-4 py-3 text-center">
                                <button onclick="konfirmasiHapus('Siti Aminah', 2)"
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-semibold">
                                    <i class="fas fa-trash-alt mr-1"></i>Hapus
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-700 mb-2"><i class="fas fa-code mr-2"></i>Kode PHP (hapus.php)</h4>
                <pre class="bg-gray-800 text-green-400 p-4 rounded-lg text-xs overflow-x-auto">// hapus.php - Endpoint untuk menghapus data
&lt;?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan'
    ]);
    exit;
}

// Parse input
parse_str(file_get_contents('php://input'), $input);
$id = $input['id'] ?? $_GET['id'] ?? null;

if (!$id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID tidak ditemukan'
    ]);
    exit;
}

// Simulasi penghapusan berhasil
// Di production: $db->delete('penduduk', ['id' => $id]);

echo json_encode([
    'status' => 'success',
    'message' => 'Data berhasil dihapus',
    'deletedId' => $id
]);
</pre>
            </div>
        </div>

        <!-- =====================================================
             CONTOH 4: PENGGAJIAN NOMOR ANTRIAN
             ===================================================== -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-3">
                <i class="fas fa-ticket-alt text-blue-600 mr-2"></i>
                4. Pengajuan & Nomor Antrian
            </h2>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Layanan</label>
                    <select id="jenisLayanan" class="w-full px-4 py-2 border rounded-lg">
                        <option value="KTP">Kartu Tanda Penduduk (KTP)</option>
                        <option value="KK">Kartu Keluarga (KK)</option>
                        <option value="Akta Kelahiran">Akta Kelahiran</option>
                        <option value="Akta Kematian">Akta Kematian</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Pemohon</label>
                    <input type="text" id="namaPemohon" value="John Doe"
                           class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>
            
            <div class="mt-4">
                <button type="button" onclick="ajalPengajuan()"
                        class="px-8 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition shadow-lg">
                    <i class="fas fa-paper-plane mr-2"></i>Ajukan Sekarang
                </button>
            </div>
        </div>

        <!-- =====================================================
             CONTOH 5: PENCARIAN DATA
             ===================================================== -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-3">
                <i class="fas fa-search text-blue-600 mr-2"></i>
                5. Pencarian Data Penduduk
            </h2>
            
            <div class="flex gap-3">
                <input type="text" id="keywordCari" placeholder="Cari NIK atau nama..."
                       class="flex-1 px-4 py-3 border rounded-xl focus:ring-2 focus:ring-green-500">
                <button type="button" onclick="cariData()"
                        class="px-6 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </div>
        </div>

        <!-- =====================================================
             FUNGSI JAVASCRIPT
             ===================================================== -->
        <script>
        // ==========================================
        // CONTOH 1: FORM SIMPAN DATA
        // ==========================================
        
        document.getElementById('formSimpanPenduduk').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Ambil data form
            const formData = {
                nik: document.getElementById('inputNik').value,
                nama: document.getElementById('inputNama').value,
                tempat_lahir: document.getElementById('inputTempatLahir').value,
                tanggal_lahir: document.getElementById('inputTanggalLahir').value,
                alamat: document.getElementById('inputAlamat').value
            };
            
            // Validasi client-side
            const errors = [];
            if (!formData.nik) errors.push("NIK wajib diisi (16 digit)");
            if (formData.nik && formData.nik.length !== 16) errors.push("NIK harus 16 digit");
            if (!formData.nama) errors.push("Nama lengkap wajib diisi");
            if (!formData.tempat_lahir) errors.push("Tempat lahir wajib diisi");
            if (!formData.tanggal_lahir) errors.push("Tanggal lahir wajib diisi");
            if (!formData.alamat) errors.push("Alamat wajib diisi");
            
            if (errors.length > 0) {
                SwalHelper.error('Validasi Gagal', errors.join(', '));
                return;
            }
            
            // Tampilkan loading
            await Swal.fire({
                title: 'Menyimpan data...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                showDenyButton: false,
                showCancelButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            try {
                // Kirim ke server
                const response = await fetch('simpan.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                // Tutup loading
                Swal.close();
                
                // Handle response
                if (result.status === 'success') {
                    SwalHelper.success('Berhasil!', 'Data berhasil disimpan dengan No. Reg: ' + result.noReg);
                    resetFormSimpan();
                } else if (result.status === 'validasi') {
                    SwalHelper.error('Validasi Gagal', result.errors.join(', '));
                } else if (result.status === 'error') {
                    SwalHelper.error('Gagal!', result.message || 'Terjadi kesalahan');
                }
                
            } catch (error) {
                Swal.close();
                SwalHelper.error('Gagal!', 'Koneksi gagal. Periksa jaringan Anda.');
            }
        });
        
        function resetFormSimpan() {
            document.getElementById('formSimpanPenduduk').reset();
        }

        // ==========================================
        // CONTOH 2: UPLOAD FILE
        // ==========================================
        
        let selectedFile = null;
        
        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                selectedFile = input.files[0];
                const display = document.getElementById('fileNameDisplay');
                display.classList.remove('hidden');
                display.querySelector('span').textContent = selectedFile.name;
                document.getElementById('btnUpload').disabled = false;
            }
        }
        
        function uploadFileKTP() {
            if (!selectedFile) {
                SwalHelper.warning('Peringatan!', 'Pilih file terlebih dahulu');
                return;
            }
            
            // Wrapper Promise untuk fetch upload
            const uploadPromise = () => {
                return new Promise(async (resolve, reject) => {
                    const formData = new FormData();
                    formData.append('file', selectedFile);
                    
                    // Set timeout
                    const timeoutId = setTimeout(() => {
                        reject(new Error('Waktu upload habis. Coba lagi.'));
                    }, 30000);
                    
                    try {
                        const response = await fetch('upload.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        clearTimeout(timeoutId);
                        
                        if (!response.ok) {
                            throw new Error('Server error: ' + response.status);
                        }
                        
                        const result = await response.json();
                        
                        if (result.status === 'success') {
                            resolve(result);
                        } else {
                            reject(new Error(result.message || 'Upload gagal'));
                        }
                    } catch (error) {
                        clearTimeout(timeoutId);
                        reject(error);
                    }
                });
            };
            
            // Panggil notifikasi upload dengan promise
            Swal.fire({
                title: 'Mengunggah file...',
                text: selectedFile.name,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                showDenyButton: false,
                showCancelButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            uploadPromise()
                .then(result => {
                    Swal.close();
                    SwalHelper.success('Berhasil!', 'File ' + selectedFile.name + ' berhasil diunggah');
                })
                .catch(error => {
                    Swal.close();
                    SwalHelper.error('Gagal!', error.message || 'Upload gagal');
                });
        }

        // ==========================================
        // CONTOH 3: HAPUS DATA
        // ==========================================
        
        function konfirmasiHapus(nama, id) {
            SwalHelper.deleteConfirm(
                'Hapus Data Penduduk',
                'Apakah Anda yakin ingin menghapus data ' + nama + '?',
                () => {
                    // Callback hapus - jalankan fetch DELETE
                    fetch(`hapus.php?id=${id}`, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            SwalHelper.success('Berhasil!', 'Data ' + nama + ' berhasil dihapus');
                            // Refresh table atau remove row
                        } else {
                            SwalHelper.error('Gagal!', result.message);
                        }
                    })
                    .catch(error => {
                        SwalHelper.error('Gagal!', 'Koneksi gagal');
                    });
                }
            );
        }

        // ==========================================
        // CONTOH 4: PENGGAJIAN NOMOR ANTRIAN
        // ==========================================
        
        async functionajalPengajuan() {
            const jenis = document.getElementById('jenisLayanan').value;
            const nama = document.getElementById('namaPemohon').value;
            
            if (!nama) {
                SwalHelper.warning('Peringatan!', 'Mohon lengkapi nama pemohon');
                return;
            }
            
            // Simulasi proses pengajuan
            const prosesPengajuan = async () => {
                return new Promise((resolve) => {
                    setTimeout(() => {
                        resolve({
                            status: 'success',
                            noReg: '#REG-' + new Date().getFullYear() + '-' + 
                                   Math.floor(Math.random() * 999999).toString().padStart(6, '0')
                        });
                    }, 2000);
                });
            };
            
            SwalHelper.confirm(
                'Proses Pengajuan',
                'Apakah Anda ingin memroses pengajuan ' + jenis + ' - ' + nama + '?',
                () => {
                    // onSelesai
                    SwalHelper.success('Selesai!', 'Pengajuan berhasil diproses. Mengalihkan ke antrian...');
                    setTimeout(() => {
                        window.location.href = '/antrian-saya';
                    }, 2000);
                }
            );
        }

        // ==========================================
        // CONTOH 5: PENCARIAN DATA
        // ==========================================
        
        async function cariData() {
            const keyword = document.getElementById('keywordCari').value.trim();
            
            if (!keyword) {
                SwalHelper.warning('Peringatan!', 'Masukkan kata kunci pencarian');
                return;
            }
            
            // Simulasi pencarian
            const hasil = Math.floor(Math.random() * 10);
            
            if (hasil === 0) {
                SwalHelper.info('Tidak Ditemukan', 'Data untuk "' + keyword + '" tidak ditemukan dalam sistem');
            } else {
                SwalHelper.success('Ditemukan!', hasil + ' data ditemukan untuk "' + keyword + '"');
            }
        }
        </script>

        <!-- Footer -->
        <div class="text-center text-gray-500 text-sm py-4">
            <p>&copy; {{ date('Y') }} Disdukcapil Kabupaten Toba - Sistem Notifikasi SweetAlert2</p>
        </div>
    </div>

<script src="{{ asset('js/style-guide-enhancer.js') }}?v={{ filemtime(public_path('js/style-guide-enhancer.js')) }}"></script>
</body>

{{-- SweetAlert Final Fix --}}
<script src="{{ asset('js/swal-final-fix.js') }}"></script>
</body>
</html>
