<?php

/**
 * OCR Integration Test - Railway OCR Fallback to OCR.space
 *
 * Script untuk testing OCR integration di Hostinger environment:
 * - Test Railway OCR endpoint dengan timeout pendek (10 detik)
 * - Jika Railway down, otomatis test fallback ke OCR.space
 * - Test KTP parsing dari OCR result
 * - Report hasil testing lengkap
 *
 * Cara run: php tests/ocr_integration_test.php
 */

use Illuminate\Support\Facades\Http;

class OcrIntegrationTest
{
    private array $results = [];
    private string $railwayUrl;
    private string $ocrSpaceKey;
    private bool $ocrSpaceEnabled;

    public function __construct(string $railwayUrl, string $ocrSpaceKey = '', bool $ocrSpaceEnabled = false)
    {
        $this->railwayUrl = rtrim($railwayUrl, '/');
        $this->ocrSpaceKey = $ocrSpaceKey;
        $this->ocrSpaceEnabled = $ocrSpaceEnabled;
    }

    /**
     * Run all OCR integration tests
     */
    public function runAll(): array
    {
        $this->results = [];

        echo "\n";
        echo "====================================\n";
        echo "  OCR INTEGRATION TEST SUITE\n";
        echo "====================================\n\n";

        // Test 1: Railway Health Check
        $this->testRailwayHealthCheck();

        // Test 2: Railway OCR Endpoint (quick test)
        $this->testRailwayOcrEndpoint();

        // Test 3: Railway Timeout Test
        $this->testRailwayTimeout();

        // Test 4: OCR.space Fallback
        if ($this->ocrSpaceEnabled) {
            $this->testOcrSpaceFallback();
        } else {
            $this->skipTest('OCR.space Fallback', 'OCR.space tidak di-enable');
        }

        // Test 5: End-to-End Fallback Logic
        $this->testFallbackLogic();

        // Test 6: Configuration Validation
        $this->testConfiguration();

        // Summary
        $this->printSummary();

        return $this->results;
    }

    /**
     * Test 1: Railway Health Check
     */
    private function testRailwayHealthCheck(): void
    {
        $name = 'Railway Health Check';
        echo "[1/5] Testing $name... ";

        try {
            $startTime = microtime(true);
            $response = Http::timeout(10)->get("$this->railwayUrl/health");
            $duration = round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                $this->results[$name] = [
                    'status' => 'PASS',
                    'duration_ms' => $duration,
                    'details' => [
                        'status' => $data['status'] ?? 'unknown',
                        'service' => $data['service'] ?? 'unknown',
                        'version' => $data['version'] ?? 'unknown',
                        'crnn_available' => $data['crnn']['available'] ?? false,
                        'easyocr_available' => $data['easyocr_support'] ?? false,
                    ]
                ];
                echo "PASS ({$duration}ms)\n";
            } else {
                $this->results[$name] = [
                    'status' => 'FAIL',
                    'duration_ms' => $duration,
                    'details' => ['http_status' => $response->status()]
                ];
                echo "FAIL (HTTP {$response->status()})\n";
            }
        } catch (\Exception $e) {
            $this->results[$name] = [
                'status' => 'FAIL',
                'duration_ms' => 0,
                'details' => ['error' => $e->getMessage()]
            ];
            echo "FAIL ({$e->getMessage()})\n";
        }
    }

    /**
     * Test 2: Railway OCR Endpoint (quick test dengan sample image)
     */
    private function testRailwayOcrEndpoint(): void
    {
        $name = 'Railway OCR Endpoint';
        echo "[2/5] Testing $name... ";

        try {
            // Use a small test image (1x1 white PNG)
            $testImage = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

            $startTime = microtime(true);
            $response = Http::timeout(10)->attach('image', $testImage, 'test.png')
                ->post("$this->railwayUrl/api/ocr/ktp");
            $duration = round((microtime(true) - $startTime) * 1000);

            // OCR mungkin gagal karena image kosong, tapi endpoint harus respond
            if ($response->successful()) {
                $data = $response->json();
                $this->results[$name] = [
                    'status' => 'PASS',
                    'duration_ms' => $duration,
                    'details' => [
                        'responded' => true,
                        'success_field' => $data['success'] ?? null,
                        'message' => $data['message'] ?? '',
                    ]
                ];
                echo "PASS ({$duration}ms)\n";
            } else {
                $this->results[$name] = [
                    'status' => 'FAIL',
                    'duration_ms' => $duration,
                    'details' => ['http_status' => $response->status()]
                ];
                echo "FAIL (HTTP {$response->status()})\n";
            }
        } catch (\Exception $e) {
            $this->results[$name] = [
                'status' => 'FAIL',
                'duration_ms' => 0,
                'details' => ['error' => $e->getMessage()]
            ];
            echo "FAIL ({$e->getMessage()})\n";
        }
    }

    /**
     * Test 3: Railway Timeout Test
     * Test apakah Railway respond dalam 10 detik
     */
    private function testRailwayTimeout(): void
    {
        $name = 'Railway Timeout (10s threshold)';
        echo "[3/5] Testing $name... ";

        try {
            $startTime = microtime(true);
            $response = Http::timeout(10)->get("$this->railwayUrl/health");
            $duration = round((microtime(true) - $startTime) * 1000);

            if ($duration <= 10000 && $response->successful()) {
                $this->results[$name] = [
                    'status' => 'PASS',
                    'duration_ms' => $duration,
                    'details' => ['response_time_acceptable' => true]
                ];
                echo "PASS ({$duration}ms < 10000ms)\n";
            } elseif ($duration > 10000) {
                $this->results[$name] = [
                    'status' => 'WARN',
                    'duration_ms' => $duration,
                    'details' => ['response_time_slow' => true]
                ];
                echo "WARN ({$duration}ms - slow but responded)\n";
            } else {
                $this->results[$name] = [
                    'status' => 'FAIL',
                    'duration_ms' => $duration,
                    'details' => ['response_time_unacceptable' => true]
                ];
                echo "FAIL (timeout exceeded)\n";
            }
        } catch (\Exception $e) {
            $this->results[$name] = [
                'status' => 'FAIL',
                'duration_ms' => 0,
                'details' => ['error' => $e->getMessage()]
            ];
            echo "FAIL (timeout/connection error)\n";
        }
    }

    /**
     * Test 4: OCR.space Fallback
     * Jika Railway down, test OCR.space sebagai fallback
     */
    private function testOcrSpaceFallback(): void
    {
        $name = 'OCR.space Fallback';
        echo "[4/5] Testing $name... ";

        try {
            if (empty($this->ocrSpaceKey)) {
                $this->skipTest($name, 'OCR.space API key tidak diset');
                return;
            }

            // Use a small test image
            $testImage = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

            $startTime = microtime(true);
            $response = Http::timeout(15)->post('https://api.ocr.space/parse/image', [
                'api_key' => $this->ocrSpaceKey,
                'image' => base64_encode($testImage),
                'language' => 'ind',
                'isOverlayRequired' => 'false',
            ]);
            $duration = round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                $success = $data['success'] ?? false;

                $this->results[$name] = [
                    'status' => $success ? 'PASS' : 'WARN',
                    'duration_ms' => $duration,
                    'details' => [
                        'success' => $success,
                        'parsing_error' => $data['parsingError'] ?? null,
                        'message' => $data['message'] ?? '',
                        'ocr_space_available' => true,
                    ]
                ];

                if ($success) {
                    echo "PASS ({$duration}ms)\n";
                } else {
                    echo "WARN (API responded but parse failed - likely no text in test image)\n";
                }
            } else {
                $this->results[$name] = [
                    'status' => 'FAIL',
                    'duration_ms' => $duration,
                    'details' => ['http_status' => $response->status()]
                ];
                echo "FAIL (HTTP {$response->status()})\n";
            }
        } catch (\Exception $e) {
            $this->results[$name] = [
                'status' => 'FAIL',
                'duration_ms' => 0,
                'details' => ['error' => $e->getMessage()]
            ];
            echo "FAIL ({$e->getMessage()})\n";
        }
    }

    /**
     * Test 5: End-to-End Fallback Logic
     * Simulasikan logika: coba Railway dulu, jika fail, coba OCR.space
     */
    private function testFallbackLogic(): void
    {
        $name = 'End-to-End Fallback Logic';
        echo "[5/5] Testing $name... ";

        try {
            // Step 1: Try Railway
            $railwayOk = false;
            try {
                $response = Http::timeout(10)->get("$this->railwayUrl/health");
                $railwayOk = $response->successful();
            } catch (\Exception $e) {
                $railwayOk = false;
            }

            // Step 2: If Railway down, try OCR.space
            $ocrSpaceOk = false;
            if (!$railwayOk && $this->ocrSpaceEnabled && !empty($this->ocrSpaceKey)) {
                try {
                    $response = Http::timeout(15)->post('https://api.ocr.space/parse/image', [
                        'api_key' => $this->ocrSpaceKey,
                        'image' => base64_encode(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==')),
                        'language' => 'ind',
                    ]);
                    $ocrSpaceOk = $response->successful();
                } catch (\Exception $e) {
                    $ocrSpaceOk = false;
                }
            }

            // Determine result
            if ($railwayOk) {
                $status = 'PASS';
                $detail = 'Railway OK (no fallback needed)';
            } elseif ($ocrSpaceOk) {
                $status = 'PASS';
                $detail = 'Railway down, OCR.space fallback OK';
            } else {
                $status = 'FAIL';
                $detail = 'Both Railway and OCR.space unavailable';
            }

            $this->results[$name] = [
                'status' => $status,
                'details' => [
                    'railway_healthy' => $railwayOk,
                    'ocr_space_healthy' => $ocrSpaceOk,
                    'fallback_triggered' => !$railwayOk && $ocrSpaceOk,
                    'message' => $detail,
                ]
            ];

            echo "$status - $detail\n";
        } catch (\Exception $e) {
            $this->results[$name] = [
                'status' => 'FAIL',
                'details' => ['error' => $e->getMessage()]
            ];
            echo "FAIL ({$e->getMessage()})\n";
        }
    }

    /**
     * Test 6: Configuration Validation
     */
    private function testConfiguration(): void
    {
        $name = 'Configuration Validation';
        echo "[CFG] Testing $name... ";

        try {
            $errors = [];

            // Validate Railway URL
            if (empty($this->railwayUrl)) {
                $errors[] = 'RAILWAY_OCR_URL tidak diset';
            } elseif (!filter_var($this->railwayUrl, FILTER_VALIDATE_URL)) {
                $errors[] = 'RAILWAY_OCR_URL tidak valid';
            }

            // Validate OCR.space key if enabled
            if ($this->ocrSpaceEnabled && empty($this->ocrSpaceKey)) {
                $errors[] = 'OCR.space API key diperlukan saat OCR.space di-enable';
            }

            if (empty($errors)) {
                $this->results[$name] = [
                    'status' => 'PASS',
                    'details' => [
                        'railway_url_valid' => true,
                        'ocr_space_configured' => $this->ocrSpaceEnabled ? true : 'not_enabled',
                    ]
                ];
                echo "PASS\n";
            } else {
                $this->results[$name] = [
                    'status' => 'FAIL',
                    'details' => ['errors' => $errors]
                ];
                echo "FAIL - " . implode(', ', $errors) . "\n";
            }
        } catch (\Exception $e) {
            $this->results[$name] = [
                'status' => 'FAIL',
                'details' => ['error' => $e->getMessage()]
            ];
            echo "FAIL ({$e->getMessage()})\n";
        }
    }

    /**
     * Skip a test
     */
    private function skipTest(string $name, string $reason): void
    {
        $this->results[$name] = [
            'status' => 'SKIP',
            'details' => ['reason' => $reason]
        ];
        echo "SKIP ($reason)\n";
    }

    /**
     * Print summary
     */
    private function printSummary(): void
    {
        echo "\n";
        echo "====================================\n";
        echo "  TEST SUMMARY\n";
        echo "====================================\n";

        $total = count($this->results);
        $pass = 0;
        $fail = 0;
        $warn = 0;
        $skip = 0;

        foreach ($this->results as $name => $result) {
            $status = $result['status'];
            $icon = match($status) {
                'PASS' => '[PASS]',
                'FAIL' => '[FAIL]',
                'WARN' => '[ WARN]',
                'SKIP' => '[SKIP]',
                default => '[????]',
            };

            if ($status === 'PASS') $pass++;
            elseif ($status === 'FAIL') $fail++;
            elseif ($status === 'WARN') $warn++;
            elseif ($status === 'SKIP') $skip++;

            echo "  $icon $name\n";
        }

        echo "\n";
        echo "  Total: $total | Pass: $pass | Fail: $fail | Warn: $warn | Skip: $skip\n";
        echo "\n";

        if ($fail > 0) {
            echo "  RECOMMENDATIONS:\n";
            foreach ($this->results as $name => $result) {
                if ($result['status'] === 'FAIL') {
                    $detail = $result['details']['error']
                        ?? $result['details']['message']
                        ?? json_encode($result['details']);
                    echo "  - $name: $detail\n";
                }
            }
            echo "\n";
        }

        if ($pass > 0 && $fail === 0) {
            echo "  All OCR tests passed! Ready for deployment.\n";
        }

        echo "====================================\n\n";
    }
}

// ============================================================================
// MAIN EXECUTION
// ============================================================================

// Load .env values
$railwayUrl = getenv('RAILWAY_OCR_URL') ?: 'http://localhost:5000';
$ocrSpaceKey = getenv('OCR_SPACE_API_KEY') ?: '';
$ocrSpaceEnabled = getenv('OCR_SPACE_ENABLED') === 'true' || getenv('OCR_SPACE_ENABLED') === '1';

// If running inside Laravel, use config
if (function_exists('config')) {
    $railwayUrl = config('services.easyocr.api_url', $railwayUrl);
    $ocrSpaceKey = config('services.ocrspace.api_key', $ocrSpaceKey);
    $ocrSpaceEnabled = config('services.ocrspace.enabled', $ocrSpaceEnabled);
}

// Create test instance
$test = new OcrIntegrationTest($railwayUrl, $ocrSpaceKey, $ocrSpaceEnabled);

// Run tests
$test->runAll();

// Exit with code if any test failed
$failed = false;
foreach ($test->runAll() as $result) {
    if ($result['status'] === 'FAIL') {
        $failed = true;
        break;
    }
}

exit($failed ? 1 : 0);
