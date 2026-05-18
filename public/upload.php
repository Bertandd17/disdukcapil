<?php
/**
 * =====================================================
 * UPLOAD.PHP - Endpoint Upload File Dokumen
 * Disdukcapil Toba
 * =====================================================
 * 
 * Endpoint untuk mengunggah file scan dokumen (KTP, KK, dll)
 * Method: POST (multipart/form-data)
 * 
 * @example Request (FormData):
 * file: [File]
 * jenis: "ktp" | "kk" | "akta_lahir" | "akta_kematian" | dll
 * nik: "1201234567890123"
 * 
 * @example Response Success:
 * {
 *     "status": "success",
 *     "fileName": "ktp_1709234567_abc123.pdf",
 *     "originalName": "KTP_Scan.pdf",
 *     "size": 1024000,
 *     "mimeType": "application/pdf",
 *     "path": "/uploads/ktp_1709234567_abc123.pdf"
 * }
 * 
 * @example Response Error:
 * {
 *     "status": "error",
 *     "message": "Ukuran file maksimal 5MB"
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set timeout 30 detik
set_time_limit(30);

// Set memory limit
ini_set('memory_limit', '64M');

/**
 * =====================================================
 * KONFIGURASI UPLOAD
 * =====================================================
 */
$config = [
    'uploadDir' => 'uploads/',
    'maxSize' => 5 * 1024 * 1024, // 5MB
    'allowedTypes' => [
        'application/pdf',
        'image/jpeg',
        'image/jpg',
        'image/png'
    ],
    'allowedExtensions' => ['pdf', 'jpg', 'jpeg', 'png'],
    'jenisDokumen' => ['ktp', 'kk', 'akta_lahir', 'akta_kematian', 'surat_nikah', 'dokumen_lain']
];

/**
 * =====================================================
 * VALIDASI REQUEST
 * =====================================================
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan. Gunakan POST.'
    ]);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Tidak ada file yang diunggah'
    ]);
    exit;
}

$file = $_FILES['file'];

/**
 * =====================================================
 * HANDLER ERROR UPLOAD
 * =====================================================
 */
$uploadErrors = [
    UPLOAD_ERR_INI_SIZE => 'File melebihi batas ukuran server',
    UPLOAD_ERR_FORM_SIZE => 'File melebihi batas ukuran formular',
    UPLOAD_ERR_PARTIAL => 'File hanya terunggah sebagian',
    UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
    UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
    UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi'
];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessage = isset($uploadErrors[$file['error']]) 
        ? $uploadErrors[$file['error']] 
        : 'Terjadi kesalahan saat upload';
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $errorMessage
    ]);
    exit;
}

/**
 * =====================================================
 * VALIDASI UKURAN FILE
 * =====================================================
 */
if ($file['size'] > $config['maxSize']) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Ukuran file maksimal 5MB'
    ]);
    exit;
}

if ($file['size'] === 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'File kosong'
    ]);
    exit;
}

/**
 * =====================================================
 * VALIDASI TIPE FILE (MIME TYPE)
 * =====================================================
 */

// Cek menggunakan finfo (lebih akurat)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $config['allowedTypes'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Format file tidak diizinkan. Gunakan PDF, JPG, atau PNG.'
    ]);
    exit;
}

/**
 * =====================================================
 * VALIDASI EKSTENSI FILE
 * =====================================================
 */
$originalName = $file['name'];
$fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($fileExtension, $config['allowedExtensions'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Ekstensi file tidak diizinkan'
    ]);
    exit;
}

/**
 * =====================================================
 * CEK JENIS DOKUMEN (OPSIONAL)
 * =====================================================
 */
$jenis = $_POST['jenis'] ?? 'dokumen';
if (!in_array($jenis, $config['jenisDokumen'])) {
    $jenis = 'dokumen';
}

/**
 * =====================================================
 * BUAT FOLDER UPLOAD JIKA BELUM ADA
 * =====================================================
 */
$uploadDir = $config['uploadDir'];
$jenisDir = $uploadDir . $jenis . '/';

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal membuat folder upload'
        ]);
        exit;
    }
}

if (!is_dir($jenisDir)) {
    if (!mkdir($jenisDir, 0755, true)) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal membuat folder jenis dokumen'
        ]);
        exit;
    }
}

/**
 * =====================================================
 * GENERATE NAMA FILE UNIK
 * =====================================================
 */
$timestamp = time();
$uniqueId = substr(md5(uniqid(mt_rand(), true)), 0, 8);
$newFileName = $jenis . '_' . $timestamp . '_' . $uniqueId . '.' . $fileExtension;
$targetPath = $jenisDir . $newFileName;
$fullPath = $targetPath;

/**
 * =====================================================
 * PINDAHKAN FILE
 * =====================================================
 */
if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menyimpan file ke server'
    ]);
    exit;
}

// Set permission
chmod($fullPath, 0644);

/**
 * =====================================================
 * SCAN MALWARE (OPSIONAL - GUNAKAN CLAMAV)
 * =====================================================
// Contoh integrasi ClamAV:
// $clamscan = '/usr/bin/clamscan';
// if (file_exists($clamscan)) {
//     $result = shell_exec("$clamscan --no-summary $fullPath 2>&1");
//     if (strpos($result, 'FOUND') !== false) {
//         unlink($fullPath);
//         http_response_code(400);
//         echo json_encode([
//             'status' => 'error',
//             'message' => 'File terdeteksi mengandung malware'
//         ]);
//         exit;
//     }
// }
 */

/**
 * =====================================================
 * RESPONSE SUKSES
 * =====================================================
 */
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'fileName' => $newFileName,
    'originalName' => $originalName,
    'size' => $file['size'],
    'sizeFormatted' => formatBytes($file['size']),
    'mimeType' => $mimeType,
    'extension' => $fileExtension,
    'jenis' => $jenis,
    'path' => '/' . $fullPath,
    'uploadedAt' => date('Y-m-d H:i:s')
]);

/**
 * =====================================================
 * HELPER FUNCTION
 * =====================================================
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}
