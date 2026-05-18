<?php

namespace Tests\Feature;

use App\Jobs\ProcessOcrJob;
use App\Models\AntrianOnline;
use App\Services\EasyOcrService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * OCR API Feature Tests
 *
 * Tests for OCR endpoints and job processing
 * Disdukcapil Project - Anggota 5
 */
class OcrTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Queue::fake();
    }

    /**
     * Test health check endpoint
     */
    public function test_health_check(): void
    {
        $response = $this->getJson('/api/ocr/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'status',
                    'python',
                    'script',
                ],
            ]);
    }

    /**
     * Test upload without file returns validation error
     */
    public function test_upload_without_file_returns_error(): void
    {
        $response = $this->postJson('/api/ocr/upload');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    /**
     * Test upload with invalid file type returns error
     */
    public function test_upload_with_invalid_file_type_returns_error(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->postJson('/api/ocr/upload', [
            'image' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    /**
     * Test upload dispatches job when async is true
     */
    public function test_upload_dispatches_job_when_async(): void
    {
        $file = UploadedFile::fake()->image('ktp.jpg', 600, 400);

        $response = $this->postJson('/api/ocr/upload', [
            'image' => $file,
            'async' => true,
        ]);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'antrian_id',
                    'status',
                    'check_url',
                ],
            ]);

        Queue::assertPushed(ProcessOcrJob::class);
    }

    /**
     * Test status endpoint returns not found for invalid id
     */
    public function test_status_returns_not_found_for_invalid_id(): void
    {
        $response = $this->getJson('/api/ocr/status/nonexistent-id');

        $response->assertStatus(404);
    }

    /**
     * Test status endpoint returns data for valid antrian
     */
    public function test_status_returns_data_for_valid_antrian(): void
    {
        $antrian = AntrianOnline::factory()->create([
            'status' => 'Verifikasi Data',
            'ocr_raw_text' => 'Sample OCR text',
            'ocr_confidence' => 0.95,
        ]);

        $response = $this->getJson("/api/ocr/status/{$antrian->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'antrian_id' => $antrian->id,
                    'has_ocr_result' => true,
                ],
            ]);
    }

    /**
     * Test result endpoint returns parsed data
     */
    public function test_result_returns_parsed_data(): void
    {
        $antrian = AntrianOnline::factory()->create([
            'ocr_raw_text' => 'NIK : 1272013456780001
Nama : BUDI SANTOSO
Tempat Lahir : JAKARTA
Tanggal Lahir : 01-01-1990',
            'ocr_confidence' => 0.95,
        ]);

        $response = $this->getJson("/api/ocr/result/{$antrian->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'antrian_id',
                    'parsed_data',
                    'confidence',
                    'field_confidence',
                ],
            ]);
    }

    /**
     * Test batch processing endpoint
     */
    public function test_batch_processing(): void
    {
        $files = [
            UploadedFile::fake()->image('ktp1.jpg'),
            UploadedFile::fake()->image('ktp2.jpg'),
            UploadedFile::fake()->image('ktp3.jpg'),
        ];

        $response = $this->postJson('/api/ocr/batch', [
            'images' => $files,
        ]);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'batch_id',
                    'total_images',
                    'items',
                    'status_url',
                ],
            ]);

        Queue::assertPushed(ProcessOcrJob::class, 3);
    }

    /**
     * Test batch validation for too many images
     */
    public function test_batch_validates_max_images(): void
    {
        $files = [];
        for ($i = 0; $i < 11; $i++) {
            $files[] = UploadedFile::fake()->image("ktp{$i}.jpg");
        }

        $response = $this->postJson('/api/ocr/batch', [
            'images' => $files,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['images']);
    }

    /**
     * Test batch status endpoint
     */
    public function test_batch_status(): void
    {
        // Put batch data in cache
        cache()->put('ocr_batch:test-batch-id', [
            'batch_id' => 'test-batch-id',
            'total' => 3,
            'processed' => 1,
            'results' => [],
        ]);

        $response = $this->getJson('/api/ocr/batch/test-batch-id');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'batch_id' => 'test-batch-id',
                    'total' => 3,
                ],
            ]);
    }

    /**
     * Test reprocess endpoint
     */
    public function test_reprocess(): void
    {
        $antrian = AntrianOnline::factory()->create([
            'file_ktp_path' => 'ocr/uploads/test.jpg',
            'status' => 'Menunggu',
        ]);

        // Create fake file
        Storage::put('ocr/uploads/test.jpg', 'fake image content');

        $response = $this->postJson("/api/ocr/reprocess/{$antrian->id}");

        $response->assertStatus(202)
            ->assertJson([
                'success' => true,
                'message' => 'OCR reprocessing started',
            ]);

        Queue::assertPushed(ProcessOcrJob::class);
    }

    /**
     * Test NIK checksum validation
     */
    public function test_nik_checksum_validation(): void
    {
        $service = app(\App\Services\AdvancedKtpOcrParsingService::class);

        // Valid NIK (Jakarta code)
        $validNik = '3175000000000001';

        // Invalid province code
        $invalidNik = '9999000000000001';

        $parsed = $service->parse("NIK : {$validNik}");
        $this->assertNotEmpty($parsed['data']['nik']);

        // Note: This would require actual implementation of validateNikChecksum
        // which checks the province code and other validations
    }

    /**
     * Test date extraction and validation
     */
    public function test_date_extraction_and_validation(): void
    {
        $service = app(\App\Services\AdvancedKtpOcrParsingService::class);

        $ocrText = "Tanggal Lahir : 15-08-1990";

        $parsed = $service->parse($ocrText);

        $this->assertEquals('15-08-1990', $parsed['data']['tanggal_lahir']);
    }

    /**
     * Test address component extraction
     */
    public function test_address_extraction(): void
    {
        $service = app(\App\Services\AdvancedKtpOcrParsingService::class);

        $ocrText = "Alamat : Jalan Merdeka No. 1
RT/RW : 001/002
Kel/Desa : Pasar Baru
Kecamatan : Menteng
Kabupaten : Jakarta Pusat";

        $parsed = $service->parse($ocrText);

        $this->assertNotEmpty($parsed['data']['alamat']);
        $this->assertEquals('RT 001/RW 002', $parsed['data']['rt_rw']);
        $this->assertEquals('PASAR BARU', $parsed['data']['kel_desa']);
    }

    /**
     * Test confidence scoring
     */
    public function test_confidence_scoring(): void
    {
        $service = app(\App\Services\AdvancedKtpOcrParsingService::class);

        // High confidence scenario - all fields present
        $highConfidenceText = "NIK : 3175000000000001
Nama : BUDI SANTOSO
Tempat Lahir : JAKARTA
Tanggal Lahir : 15-08-1990";

        $parsed = $service->parse($highConfidenceText);

        $this->assertGreaterThan(0, $parsed['confidence']);
        $this->assertIsArray($parsed['field_confidence']);
    }

    /**
     * Test gender extraction variations
     */
    public function test_gender_extraction_variations(): void
    {
        $service = app(\App\Services\AdvancedKtpOcrParsingService::class);

        // Test variations
        $variations = [
            'Jenis Kelamin : LAKI-LAKI',
            'Jenis Kelamin : PEREMPUAN',
            'Jenis Kelamin : LAKI - LAKI',
        ];

        foreach ($variations as $text) {
            $parsed = $service->parse($text);
            $this->assertNotEmpty($parsed['data']['jenis_kelamin']);
        }
    }

    /**
     * Test religion extraction
     */
    public function test_religion_extraction(): void
    {
        $service = app(\App\Services\AdvancedKtpOcrParsingService::class);

        $religions = ['ISLAM', 'KRISTEN', 'KATOLIK', 'HINDU', 'BUDDHA', 'KHONGHUCU'];

        foreach ($religions as $religion) {
            $parsed = $service->parse("Agama : {$religion}");
            $this->assertEquals($religion, $parsed['data']['agama']);
        }
    }

    /**
     * Test marital status extraction
     */
    public function test_marital_status_extraction(): void
    {
        $service = app(\App\Services\AdvancedKtpOcrParsingService::class);

        $statuses = [
            'Status Perkawinan : BELUM KAWIN',
            'Status Perkawinan : KAWIN',
            'Status Perkawinan : CERAI HIDUP',
        ];

        foreach ($statuses as $text) {
            $parsed = $service->parse($text);
            $this->assertNotEmpty($parsed['data']['status_perkawinan']);
        }
    }
}
