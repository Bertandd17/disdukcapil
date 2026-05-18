<?php
/**
 * =====================================================
 * HAPUS.PHP - Endpoint Hapus Data Penduduk
 * Disdukcapil Toba
 * =====================================================
 * 
 * Endpoint untuk menghapus data penduduk
 * Method: DELETE atau POST dengan _method=DELETE
 * 
 * @example Request (DELETE):
 * DELETE /hapus.php?id=123
 * 
 * @example Request (POST):
 * POST /hapus.php
 * Content-Type: application/x-www-form-urlencoded
 * _method=DELETE&id=123
 * 
 * @example Request (JSON):
 * DELETE /hapus.php?id=123
 * 
 * @example Response Success:
 * {
 *     "status": "success",
 *     "message": "Data berhasil dihapus",
 *     "deletedId": 123
 * }
 * 
 * @example Response Error:
 * {
 *     "status": "error",
 *     "message": "Data tidak ditemukan"
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * =====================================================
 * AMBIL ID DARI REQUEST
 * =====================================================
 */

// Cek method request
$method = $_SERVER['REQUEST_METHOD'];

// Untuk DELETE request
if ($method === 'DELETE') {
    // ID dari query string
    $id = $_GET['id'] ?? null;
    
    // Atau dari body JSON
    if (!$id) {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
    }
}
// Untuk POST request dengan _method=DELETE (Method Spoofing)
elseif ($method === 'POST') {
    // Cek _method field untuk method spoofing
    $spoofMethod = $_POST['_method'] ?? $_SERVER['HTTP_X_HTTP_METHOD'] ?? null;
    
    if (strtoupper($spoofMethod) === 'DELETE') {
        $id = $_POST['id'] ?? $_GET['id'] ?? null;
        
        if (!$id) {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;
        }
    } else {
        // POST biasa
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;
        }
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan. Gunakan DELETE atau POST.'
    ]);
    exit;
}

/**
 * =====================================================
 * VALIDASI ID
 * =====================================================
 */
if (!$id) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'ID tidak ditemukan. Parameter id wajib diisi.'
    ]);
    exit;
}

// Sanitize ID - hanya terima angka
if (!is_numeric($id)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Format ID tidak valid'
    ]);
    exit;
}

$id = (int) $id;

/**
 * =====================================================
 * SIMULASI PENGAMBILAN DATA (PRODUCTION: QUERY DB)
 * =====================================================
 */

// Contoh data yang ada di database
$dummyData = [
    1 => ['nik' => '1201234567890123', 'nama' => 'Budi Santoso', 'alamat' => 'Jl. Merdeka No. 1, Toba'],
    2 => ['nik' => '1209876543210987', 'nama' => 'Siti Aminah', 'alamat' => 'Jl. Sudirman No. 5, Toba'],
    3 => ['nik' => '1205555444433333', 'nama' => 'Ahmad Rizki', 'alamat' => 'Jl. Pematang Siantar No. 10']
];

// Cek apakah data dengan ID tersebut ada
if (!isset($dummyData[$id])) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => 'Data dengan ID ' . $id . ' tidak ditemukan'
    ]);
    exit;
}

$data = $dummyData[$id];

/**
 * =====================================================
 * SIMULASI PENGHAPUSAN DATA (PRODUCTION: DELETE DB)
 * =====================================================
 */

// Di production, lakukan:
// 1. Cek apakah user memiliki hak akses hapus
// 2. Backup data sebelum hapus (soft delete)
// 3. Hapus data dari database
// 4. Log aktivitas hapus

// Contoh dengan soft delete:
// $db->update('penduduk', [
//     'deleted_at' => date('Y-m-d H:i:s'),
//     'deleted_by' => $_SESSION['user_id']
// ], ['id' => $id]);

// Contoh hard delete:
// $db->where('id', $id)->delete('penduduk');

/**
 * =====================================================
 * VALIDASI Hak AKSES (CONTOH)
 * =====================================================
// session_start();
// if (!isset($_SESSION['user_id'])) {
//     http_response_code(401);
//     echo json_encode([
//         'status' => 'error',
//         'message' => 'Anda belum login'
//     ]);
//     exit;
// }

// if (!$user->can('delete', 'penduduk')) {
//     http_response_code(403);
//     echo json_encode([
//         'status' => 'error',
//         'message' => 'Anda tidak memiliki hak untuk menghapus data ini'
//     ]);
//     exit;
// }
 */

/**
 * =====================================================
 * LOG AKTIVITAS (OPSIONAL)
 * =====================================================
// activity_log([
//     'action' => 'delete',
//     'table' => 'penduduk',
//     'record_id' => $id,
//     'user_id' => $_SESSION['user_id'],
//     'ip_address' => $_SERVER['REMOTE_ADDR'],
//     'user_agent' => $_SERVER['HTTP_USER_AGENT'],
//     'data' => $data // Data yang dihapus (untuk audit trail)
// ]);
 */

/**
 * =====================================================
 * RESPONSE SUKSES
 * =====================================================
 */
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Data ' . $data['nama'] . ' berhasil dihapus',
    'deletedId' => $id,
    'deletedData' => [
        'nik' => $data['nik'],
        'nama' => $data['nama']
    ],
    'deletedAt' => date('Y-m-d H:i:s')
]);
