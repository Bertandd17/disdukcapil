<?php
/**
 * End-to-End test skrip untuk verifikasi:
 * 1. Koneksi ke Railway OCR service
 * 2. Koneksi ke database Hostinger
 * 3. CORS / Origin validation ke Hostinger
 *
 * Cara pakai:
 *   php test-e2e-ocr.php
 *
 * Output:
 *   - PASS/FAIL untuk setiap check
 *   - Ringkasan di akhir
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

$pass = 0;
$fail = 0;
$warnings = 0;

function check(string $name, bool $result, string $detail = ''): void
{
    global $pass, $fail;
    if ($result) {
        $pass++;
        echo "  [PASS] {$name}";
        if ($detail) echo " — {$detail}";
        echo "\n";
    } else {
        $fail++;
        echo "  [FAIL] {$name}";
        if ($detail) echo " — {$detail}";
        echo "\n";
    }
}

function warn(string $name, string $detail): void
{
    global $warnings;
    $warnings++;
    echo "  [WARN] {$name} — {$detail}\n";
}

echo "=== E2E TEST: Disdukcapil Toba Deployment ===\n\n";

// 1. ENV checks
echo "[1] Environment Configuration\n";
$appUrl = env('APP_URL');
$appEnv = env('APP_ENV');
$ocrUrl = env('EASYOCR_API_URL');
$dbHost = env('DB_HOST');

check('APP_URL configured', !empty($appUrl) && str_contains($appUrl, 'http'), $appUrl ?: '(empty)');
check('APP_ENV=production', $appEnv === 'production', $appEnv ?: '(empty)');
check('Railway OCR URL configured', !empty($ocrUrl) && str_contains($ocrUrl, 'railway'), $ocrUrl ?: '(empty)');
check('DB_HOST configured', !empty($dbHost) && $dbHost !== '127.0.0.1', $dbHost ?: '(empty)');

// 2. Railway OCR health check
echo "\n[2] Railway OCR Service\n";
if (!empty($ocrUrl)) {
    $healthUrl = rtrim($ocrUrl, '/') . '/health';
    try {
        $start = microtime(true);
        $response = Http::timeout(15)->get($healthUrl);
        $latency = round((microtime(true) - $start) * 1000);

        check('OCR /health reachable', $response->successful(), "HTTP {$response->status()} | {$latency}ms");

        if ($response->successful()) {
            $body = $response->json();
            check('OCR service status OK', ($body['status'] ?? '') === 'ok', $body['status'] ?? 'unknown');
            check('CRNN model available', ($body['crnn']['available'] ?? false) === true, 'CRNN: ' . ($body['crnn']['available'] ? 'yes' : 'no'));
            check('Image libs available', ($body['image_libs'] ?? false) === true);
        }
    } catch (\Throwable $e) {
        check('OCR /health reachable', false, $e->getMessage());
    }
} else {
    warn('OCR URL', 'skipped — EASYOCR_API_URL not set');
}

// 3. OCR /api/ocr/ktp dengan test image (jika ada)
echo "\n[3] OCR Processing Test\n";
$testImage = __DIR__ . '/scripts/dataset_51.png';
if (file_exists($testImage) && !empty($ocrUrl)) {
    try {
        $response = Http::timeout(60)
            ->attach('image', file_get_contents($testImage), 'test.jpg')
            ->post(rtrim($ocrUrl, '/') . '/api/ocr/ktp');

        check('OCR /api/ocr/ktp reachable', $response->successful(), "HTTP {$response->status()}");

        if ($response->successful()) {
            $data = $response->json();
            $fields = ['nik', 'nama_lengkap', 'tanggal_lahir', 'alamat'];
            $filled = 0;
            foreach ($fields as $f) {
                if (!empty($data['data'][$f])) $filled++;
            }
            check("OCR extracted core fields ({$filled}/4)", $filled >= 2);
        }
    } catch (\Throwable $e) {
        check('OCR processing test', false, $e->getMessage());
    }
} else {
    warn('OCR processing test', 'no test image at scripts/dataset_51.png — test live from web form');
}

// 4. Database connectivity
echo "\n[4] Hostinger MySQL\n";
try {
    $start = microtime(true);
    DB::connection()->getPdo();
    $latency = round((microtime(true) - $start) * 1000);
    check('MySQL connection', true, $latency . 'ms');

    $tables = DB::select('SHOW TABLES');
    $count = count($tables);
    check('Tables present', $count > 5, "{$count} tables");

    // Check key tables
    $keyTables = ['users', 'pendaftaran', 'berkas_master'];
    foreach ($keyTables as $t) {
        try {
            $exists = DB::select("SHOW TABLES LIKE ?", [$t]);
            check("Table '{$t}'", count($exists) === 1);
        } catch (\Throwable $e) {
            check("Table '{$t}'", false, $e->getMessage());
        }
    }
} catch (\Throwable $e) {
    check('MySQL connection', false, $e->getMessage());
}

// 5. Application routes sanity
echo "\n[5] Laravel Application\n";
check('APP_KEY set', !empty(env('APP_KEY')) && strlen(env('APP_KEY')) > 20);
check('Storage writable', is_writable(__DIR__ . '/storage'), is_writable(__DIR__ . '/storage') ? 'yes' : 'NO');
check('Bootstrap cache writable', is_writable(__DIR__ . '/bootstrap/cache'));

// 6. CORS check (Hostinger <-> Railway)
echo "\n[6] CORS / Cross-Origin\n";
if (!empty($appUrl) && !empty($ocrUrl)) {
    $parsed = parse_url($appUrl);
    $origin = $parsed['scheme'] . '://' . $parsed['host'];

    try {
        $response = Http::timeout(10)
            ->withHeaders(['Origin' => $origin])
            ->options(rtrim($ocrUrl, '/') . '/api/ocr/ktp');

        $acao = $response->header('Access-Control-Allow-Origin');
        $corsOk = $acao === '*' || $acao === $origin || str_contains((string)$acao, $origin);

        if ($corsOk) {
            check('CORS allows Hostinger origin', true, "ACAO: {$acao}");
        } else {
            warn('CORS', "ACAO header: " . ($acao ?: '(none)') . " — mungkin perlu diset di Railway");
        }
    } catch (\Throwable $e) {
        warn('CORS check', 'gagal: ' . $e->getMessage());
    }
}

// Summary
echo "\n=== RINGKASAN ===\n";
echo "PASS: {$pass}\n";
echo "FAIL: {$fail}\n";
echo "WARN: {$warnings}\n\n";

if ($fail === 0) {
    echo "✓ Semua check kritis lulus. Deployment siap.\n";
    exit(0);
} else {
    echo "✗ Ada {$fail} check yang gagal. Perbaiki sebelum go-live.\n";
    exit(1);
}
