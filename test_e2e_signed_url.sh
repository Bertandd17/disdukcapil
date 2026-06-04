#!/bin/bash
set -e
cd "D:/Semester 6/PA 3/Project/PA3"

echo "=== A. Upload + encrypt + sign ==="
php artisan tinker --execute='
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use App\Services\FileEncryptionService;

$svc = new FileEncryptionService("secure");
$tmpFile = tempnam(sys_get_temp_dir(), "http_");
file_put_contents($tmpFile, "SECRET-PDF-CONTENT-" . str_repeat("X", 1000));
$uploadedFile = new UploadedFile($tmpFile, "rahasia.pdf", "application/pdf", null, true);
$enc = $svc->encryptAndStoreFile($uploadedFile, "http-e2e");

$valid = URL::temporarySignedRoute("secure-files.serve", now()->addMinutes(30), ["path" => $enc]);
$expired = URL::temporarySignedRoute("secure-files.serve", now()->subMinutes(1), ["path" => $enc]);
$tampered = preg_replace("/signature=[a-f0-9]+/", "signature=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa", $valid);
$raw = "/secure-files/" . $enc;

file_put_contents("url_valid.txt", $valid);
file_put_contents("url_expired.txt", $expired);
file_put_contents("url_tampered.txt", $tampered);
file_put_contents("url_raw.txt", $raw);
echo "ENC_LEN=" . strlen($enc) . "\n";
echo "ENC_PREFIX=" . substr($enc, 0, 40) . "\n";
' 2>&1 | head -10

VALID=$(cat url_valid.txt)
EXPIRED=$(cat url_expired.txt)
TAMPERED=$(cat url_tampered.txt)
RAW=$(cat url_raw.txt)

echo ""
echo "=== B. HTTP requests (server on 127.0.0.1:8765) ==="
echo "--- B1. Valid signed URL (no auth → expect 302 to login) ---"
curl -s -o /dev/null -w "  status=%{http_code}  size=%{size_download}\n" "$VALID"

echo "--- B2. Expired signed URL (expect 403) ---"
curl -s -o /dev/null -w "  status=%{http_code}  size=%{size_download}\n" "$EXPIRED"

echo "--- B3. Tampered signed URL (expect 403) ---"
curl -s -o /dev/null -w "  status=%{http_code}  size=%{size_download}\n" "$TAMPERED"

echo "--- B4. No signature (raw path, expect 302/403/404) ---"
curl -s -o /dev/null -w "  status=%{http_code}  size=%{size_download}\n" "http://127.0.0.1:8765${RAW}"

echo "--- B5. Encrypted path directly in URL (no sig, no auth) ---"
curl -s -o /dev/null -w "  status=%{http_code}  size=%{size_download}\n" "http://127.0.0.1:8765/secure-files/$(cat url_valid.txt | sed 's|.*path=||;s|&.*||')"

# Cleanup
rm -f url_valid.txt url_expired.txt url_tampered.txt url_raw.txt enc_path.txt
echo ""
echo "=== Done ==="
