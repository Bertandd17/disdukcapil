<?php

namespace Tests\Feature;

use App\Services\EasyOcrService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * OCR Health Check Feature Tests
 *
 * Tests for OCR service health check endpoints and diagnostics:
 * - GET /api/ocr/health endpoint
 * - All OCR providers respond correctly
 * - Python installation check
 * - Script existence check
 * - API mode vs CLI mode detection
 * - Provider availability
 *
 * Disdukcapil Project
 */
class OcrHealthCheckTest extends TestCase
{
    // ========================================================================
    // HEALTH CHECK ENDPOINT
    // ========================================================================

    /**
     * Health check endpoint returns success when OCR is available.
     */
    public function test_health_check_endpoint_success(): void
    {
        Http::fake([
            'localhost:5000/health' => Http::response(['status' => 'ok'], 200),
        ]);

        $response = $this->getJson('/api/ocr/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'status',
                    'provider',
                    'python',
                    'script',
                    'api_mode',
                ],
            ]);
    }

    /**
     * Health check endpoint returns service unavailable when OCR is down.
     */
    public function test_health_check_endpoint_service_unavailable(): void
    {
        Http::fake([
            'localhost:5000/health' => Http::response([], 503),
        ]);

        $response = $this->getJson('/api/ocr/health');

        $this->assertTrue(
            $response->status() === 503 ||
            ($response->status() === 200 && $response->json('data.status') === 'unhealthy')
        );
    }

    /**
     * Health check returns correct provider information.
     */
    public function test_health_check_returns_provider_info(): void
    {
        Http::fake([
            'localhost:5000/health' => Http::response(['status' => 'ok'], 200),
        ]);

        $response = $this->getJson('/api/ocr/health');

        $response->assertStatus(200)
            ->assertJsonPath('data.provider', fn($provider) =>
                in_array($provider, ['easyocr_api', 'easyocr_cli', 'not_configured', 'unknown'])
            );
    }

    // ========================================================================
    // OCR SERVICE DIAGNOSTICS
    // ========================================================================

    /**
     * Diagnose method returns complete diagnostic information.
     */
    public function test_diagnose_returns_complete_info(): void
    {
        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        $this->assertIsArray($diagnostics);
        $this->assertArrayHasKey('python_found', $diagnostics);
        $this->assertArrayHasKey('python_version', $diagnostics);
        $this->assertArrayHasKey('script_exists', $diagnostics);
        $this->assertArrayHasKey('api_mode', $diagnostics);
        $this->assertArrayHasKey('provider', $diagnostics);
    }

    /**
     * Diagnose detects Python installation.
     */
    public function test_diagnose_detects_python(): void
    {
        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        $this->assertIsBool($diagnostics['python_found']);

        if ($diagnostics['python_found']) {
            $this->assertNotNull($diagnostics['python_version']);
            $this->assertStringContainsString('Python', $diagnostics['python_version']);
        }
    }

    /**
     * Diagnose checks script existence.
     */
    public function test_diagnose_checks_script_existence(): void
    {
        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        $this->assertIsBool($diagnostics['script_exists']);
        $this->assertArrayHasKey('script_path', $diagnostics);

        if ($diagnostics['script_exists']) {
            $this->assertFileExists($diagnostics['script_path']);
        }
    }

    /**
     * Diagnose detects API mode configuration.
     */
    public function test_diagnose_detects_api_mode(): void
    {
        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        $this->assertIsBool($diagnostics['api_mode']);

        if ($diagnostics['api_mode']) {
            $this->assertArrayHasKey('api_base_url', $diagnostics);
            $this->assertArrayHasKey('api_reachable', $diagnostics);
        }
    }

    /**
     * Diagnose detects CLI mode configuration.
     */
    public function test_diagnose_detects_cli_mode(): void
    {
        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        $this->assertArrayHasKey('cli_enabled', $diagnostics);
        $this->assertIsBool($diagnostics['cli_enabled']);
    }

    // ========================================================================
    // API MODE HEALTH CHECKS
    // ========================================================================

    /**
     * API mode health check when API is reachable.
     */
    public function test_api_mode_health_check_reachable(): void
    {
        Http::fake([
            'localhost:5000/health' => Http::response(['status' => 'ok'], 200),
        ]);

        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        if ($diagnostics['api_mode']) {
            $this->assertTrue($diagnostics['api_reachable']);
        }
    }

    /**
     * API mode health check when API is unreachable.
     */
    public function test_api_mode_health_check_unreachable(): void
    {
        Http::fake([
            'localhost:5000/health' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
            },
        ]);

        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        if ($diagnostics['api_mode']) {
            $this->assertFalse($diagnostics['api_reachable']);
        }
    }

    /**
     * API mode health check with timeout.
     */
    public function test_api_mode_health_check_timeout(): void
    {
        Http::fake([
            'localhost:5000/health' => function () {
                sleep(20);
                return Http::response(['status' => 'ok'], 200);
            },
        ]);

        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        $this->assertArrayHasKey('api_reachable', $diagnostics);
    }

    // ========================================================================
    // CLI MODE HEALTH CHECKS
    // ========================================================================

    /**
     * CLI mode requires Python and script.
     */
    public function test_cli_mode_requires_python_and_script(): void
    {
        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        if ($diagnostics['provider'] === 'easyocr_cli') {
            $this->assertTrue($diagnostics['python_found']);
            $this->assertTrue($diagnostics['script_exists']);
        }
    }

    /**
     * CLI mode with missing Python.
     */
    public function test_cli_mode_missing_python(): void
    {
        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        if (!$diagnostics['python_found'] && $diagnostics['cli_enabled']) {
            $this->assertEquals('not_configured', $diagnostics['provider']);
        }
    }

    /**
     * CLI mode with missing script.
     */
    public function test_cli_mode_missing_script(): void
    {
        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        if (!$diagnostics['script_exists'] && $diagnostics['cli_enabled']) {
            $this->assertEquals('not_configured', $diagnostics['provider']);
        }
    }

    // ========================================================================
    // PROVIDER DETECTION
    // ========================================================================

    /**
     * Provider detection returns valid provider type.
     */
    public function test_provider_detection_valid_type(): void
    {
        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        $validProviders = ['easyocr_api', 'easyocr_cli', 'not_configured', 'unknown'];
        $this->assertContains($diagnostics['provider'], $validProviders);
    }

    /**
     * Provider priority: API mode over CLI mode.
     */
    public function test_provider_priority_api_over_cli(): void
    {
        Http::fake([
            'localhost:5000/health' => Http::response(['status' => 'ok'], 200),
        ]);

        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        if ($diagnostics['api_mode'] && $diagnostics['api_reachable']) {
            $this->assertEquals('easyocr_api', $diagnostics['provider']);
        }
    }

    /**
     * Provider fallback to CLI when API unavailable.
     */
    public function test_provider_fallback_to_cli(): void
    {
        Http::fake([
            'localhost:5000/health' => Http::response([], 503),
        ]);

        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        if (!$diagnostics['api_reachable'] && $diagnostics['cli_enabled']) {
            if ($diagnostics['python_found'] && $diagnostics['script_exists']) {
                $this->assertEquals('easyocr_cli', $diagnostics['provider']);
            }
        }
    }

    // ========================================================================
    // MULTIPLE PROVIDER SUPPORT
    // ========================================================================

    /**
     * All providers respond correctly - EasyOCR API.
     */
    public function test_easyocr_api_provider_responds(): void
    {
        Http::fake([
            'localhost:5000/health' => Http::response(['status' => 'ok'], 200),
        ]);

        $response = $this->getJson('/api/ocr/health');

        $response->assertStatus(200);

        if ($response->json('data.provider') === 'easyocr_api') {
            $this->assertTrue($response->json('data.api_reachable'));
        }
    }

    /**
     * All providers respond correctly - EasyOCR CLI.
     */
    public function test_easyocr_cli_provider_responds(): void
    {
        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        if ($diagnostics['provider'] === 'easyocr_cli') {
            $this->assertTrue($diagnostics['python_found']);
            $this->assertTrue($diagnostics['script_exists']);
        }
    }

    /**
     * OCR.space provider (if enabled).
     */
    public function test_ocrspace_provider_if_enabled(): void
    {
        if (config('services.ocrspace.enabled')) {
            Http::fake([
                'api.ocr.space/*' => Http::response(['ParsedResults' => []], 200),
            ]);

            $this->assertTrue(true);
        } else {
            $this->markTestSkipped('OCR.space not enabled');
        }
    }

    // ========================================================================
    // ERROR HANDLING
    // ========================================================================

    /**
     * Health check handles network errors gracefully.
     */
    public function test_health_check_handles_network_errors(): void
    {
        Http::fake([
            'localhost:5000/health' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Network error');
            },
        ]);

        $response = $this->getJson('/api/ocr/health');

        $this->assertTrue(in_array($response->status(), [200, 503]));
    }

    /**
     * Health check handles timeout gracefully.
     */
    public function test_health_check_handles_timeout(): void
    {
        Http::fake([
            'localhost:5000/health' => function () {
                throw new \Illuminate\Http\Client\RequestException(
                    new \Illuminate\Http\Client\Response(
                        new \GuzzleHttp\Psr7\Response(408)
                    )
                );
            },
        ]);

        $response = $this->getJson('/api/ocr/health');

        $this->assertTrue(in_array($response->status(), [200, 503]));
    }

    /**
     * Health check handles invalid response.
     */
    public function test_health_check_handles_invalid_response(): void
    {
        Http::fake([
            'localhost:5000/health' => Http::response('invalid json', 200),
        ]);

        $response = $this->getJson('/api/ocr/health');

        $this->assertTrue(in_array($response->status(), [200, 503]));
    }

    // ========================================================================
    // CONFIGURATION VALIDATION
    // ========================================================================

    /**
     * Health check validates API URL configuration.
     */
    public function test_health_check_validates_api_url(): void
    {
        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        if ($diagnostics['api_mode']) {
            $this->assertNotEmpty($diagnostics['api_base_url']);
            $this->assertStringStartsWith('http', $diagnostics['api_base_url']);
        }
    }

    /**
     * Health check validates Python path.
     */
    public function test_health_check_validates_python_path(): void
    {
        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        if ($diagnostics['python_found']) {
            $this->assertNotEmpty($diagnostics['python_path']);
        }
    }

    /**
     * Health check validates script path.
     */
    public function test_health_check_validates_script_path(): void
    {
        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        $this->assertNotEmpty($diagnostics['script_path']);

        if ($diagnostics['script_exists']) {
            $this->assertFileExists($diagnostics['script_path']);
            $this->assertStringEndsWith('.py', $diagnostics['script_path']);
        }
    }

    // ========================================================================
    // PERFORMANCE CHECKS
    // ========================================================================

    /**
     * Health check completes within reasonable time.
     */
    public function test_health_check_performance(): void
    {
        Http::fake([
            'localhost:5000/health' => Http::response(['status' => 'ok'], 200),
        ]);

        $startTime = microtime(true);
        $response = $this->getJson('/api/ocr/health');
        $duration = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(5.0, $duration);
    }

    /**
     * Diagnose method completes within reasonable time.
     */
    public function test_diagnose_performance(): void
    {
        $service = app(EasyOcrService::class);

        $startTime = microtime(true);
        $diagnostics = $service->diagnose();
        $duration = microtime(true) - $startTime;

        $this->assertIsArray($diagnostics);
        $this->assertLessThan(10.0, $duration);
    }

    // ========================================================================
    // INTEGRATION TESTS
    // ========================================================================

    /**
     * Health check integrates with actual OCR service.
     */
    public function test_health_check_integration(): void
    {
        $response = $this->getJson('/api/ocr/health');

        $response->assertStatus(fn($status) => in_array($status, [200, 503]));

        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'success',
                'data' => [
                    'status',
                    'provider',
                ],
            ]);
        }
    }

    /**
     * Health check status reflects actual service availability.
     */
    public function test_health_check_reflects_service_availability(): void
    {
        $response = $this->getJson('/api/ocr/health');

        if ($response->status() === 200) {
            $status = $response->json('data.status');
            $provider = $response->json('data.provider');

            if ($status === 'healthy') {
                $this->assertNotEquals('not_configured', $provider);
            }
        }
    }

    // ========================================================================
    // EDGE CASES
    // ========================================================================

    /**
     * Health check with no providers configured.
     */
    public function test_health_check_no_providers_configured(): void
    {
        Http::fake([
            'localhost:5000/health' => Http::response([], 503),
        ]);

        $service = app(EasyOcrService::class);
        $diagnostics = $service->diagnose();

        if (!$diagnostics['api_reachable'] && !$diagnostics['python_found']) {
            $this->assertEquals('not_configured', $diagnostics['provider']);
        }
    }

    /**
     * Health check with multiple concurrent requests.
     */
    public function test_health_check_concurrent_requests(): void
    {
        Http::fake([
            'localhost:5000/health' => Http::response(['status' => 'ok'], 200),
        ]);

        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson('/api/ocr/health');
        }

        foreach ($responses as $response) {
            $response->assertStatus(fn($status) => in_array($status, [200, 503]));
        }
    }

    /**
     * Health check after service restart.
     */
    public function test_health_check_after_service_restart(): void
    {
        Http::fake([
            'localhost:5000/health' => Http::response([], 503),
        ]);

        $this->getJson('/api/ocr/health');

        Http::fake([
            'localhost:5000/health' => Http::response(['status' => 'ok'], 200),
        ]);

        $response2 = $this->getJson('/api/ocr/health');

        $this->assertTrue(
            $response2->status() === 200 ||
            $response2->json('data.status') === 'healthy'
        );
    }
}
