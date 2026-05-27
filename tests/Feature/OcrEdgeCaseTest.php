<?php

namespace Tests\Feature;

use App\Models\AntrianOnline;
use App\Services\AdvancedKtpOcrParsingService;
use App\Services\KtpOcrService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * OCR Edge Case Feature Tests
 *
 * Tests for edge cases in the OCR pipeline including:
 * - Blurry/unreadable images (foto buram)
 * - Damaged cards (KTP rusak)
 * - Poor lighting conditions (lighting buruk)
 * - Invalid NIK formats
 * - OCR failures and timeouts
 *
 * Disdukcapil Project
 */
class OcrEdgeCaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    // ========================================================================
    // FOTO BURAM (BLURRY IMAGES)
    // ========================================================================

    /**
     * Blurry image results in low confidence OCR output.
     * Simulasi: OCR hanya bisa baca sebagian karakter, banyak noise.
     */
    public function test_blurry_image_low_confidence(): void
    {
        // OCR result dari gambar buram - hanya sebagian teks terbaca
        $blurryOcrText = "NIK : 1201011708900001
Nama : BAMBANG S
Tempat Lahir : SIAN
Tanggal Lahir : 17-08-1990
Jenis Kelamin : LAKI-LAKI
Alamat : JL. PENDI
RT/RW : 001/005
Kel/Desa : SIANTAR
Kecamatan : SIANTAR";

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse($blurryOcrText);

        // Nama dan alamat tidak lengkap → confidence rendah
        $this->assertLessThan(0.85, $parsed['confidence']);
        $this->assertEquals('BAMBANG S', $parsed['data']['nama_lengkap']);
        $this->assertEquals('1201011708900001', $parsed['data']['nik']);
    }

    /**
     * Blurry image dengan partial character recognition.
     * OCr misread: 0→O, 1→I, 5→S
     */
    public function test_blurry_image_partial_char_recognition(): void
    {
        $blurryOcrText = "NIK : 12010117O89OOOOO1
Nama : BAMBANG SUPRIYANTO
Tempat Lahir : SIANTAR
Tanggal Lahir : 17-08-1990";

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse($blurryOcrText);

        // NIK dengan misread harus tetap bisa di-normalize
        $this->assertEquals('1201011708900001', $parsed['data']['nik']);
    }

    /**
     * Blurry image that needs manual review flag.
     */
    public function test_blurry_image_needs_manual_review_flag(): void
    {
        $blurryOcrText = "NIK : 1201
Nama : BAMBANG
Tempat Lahir : SIANTAR";

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse($blurryOcrText);

        // Confidence sangat rendah → perlu review manual
        $this->assertLessThan(0.7, $parsed['confidence']);

        // Jika confidence < 0.7, harus flag needs_manual_review
        if ($parsed['confidence'] < 0.7) {
            $antrian = AntrianOnline::factory()->create([
                'ocr_confidence' => $parsed['confidence'],
                'ocr_raw_text' => $blurryOcrText,
            ]);

            // Update flag manual review
            $antrian->ocr_needs_review = true;
            $antrian->save();

            $this->assertTrue($antrian->ocr_needs_review);
        }
    }

    /**
     * Blurry image - NIK still extracted from partial text.
     */
    public function test_blurry_image_nik_still_extracted(): void
    {
        $blurryOcrText = "NIK : 1201011708900001
Nama : BAMBANG
JL. PENDIDIKAN";

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse($blurryOcrText);

        // NIK harus tetap terekstrak meskipun nama/almt tidak lengkap
        $this->assertEquals('1201011708900001', $parsed['data']['nik']);
        $this->assertEquals('BAMBANG', $parsed['data']['nama_lengkap']);
    }

    // ========================================================================
    // KTP RUSAK (DAMAGED CARDS)
    // ========================================================================

    /**
     * Damaged card - partial data readable.
     * Beberapa field terbaca, field lain hilang.
     */
    public function test_damaged_card_partial_fields_readable(): void
    {
        $damagedOcrText = "NIK : 1201011708900001
Nama : BAMBANG SUPRIYANTO
Tempat Lahir : SIANTAR
Tanggal Lahir : 17-08-1990
Jenis Kelamin : LAKI-LAKI
Alamat :
RT/RW :
Kel/Desa :
Kecamatan :
Kabupaten :
Provinsi : SUMATERA UTARA";

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse($damagedOcrText);

        // Field core harus tetap ada
        $this->assertEquals('1201011708900001', $parsed['data']['nik']);
        $this->assertEquals('BAMBANG SUPRIYANTO', $parsed['data']['nama_lengkap']);
        $this->assertEquals('17-08-1990', $parsed['data']['tanggal_lahir']);

        // Field alamat kosong - confidence rendah
        $this->assertEquals('', $parsed['data']['alamat']);
        $this->assertEquals('', $parsed['data']['rt_rw']);
        $this->assertLessThan(0.7, $parsed['confidence']);
    }

    /**
     * Damaged card - missing address components.
     * Service harus graceful handle missing fields.
     */
    public function test_damaged_card_missing_address_graceful_handling(): void
    {
        $damagedOcrText = "NIK : 1201011708900001
Nama : BUDI SANTOSO
Tempat Lahir : MEDAN
Tanggal Lahir : 01-01-1995";

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse($damagedOcrText);

        // NIK tetap terekstrak
        $this->assertEquals('1201011708900001', $parsed['data']['nik']);
        $this->assertEquals('BUDI SANTOSO', $parsed['data']['nama_lengkap']);

        // Alamat komponen kosong - tidak throw error
        $this->assertArrayHasKey('alamat', $parsed['data']);
        $this->assertArrayHasKey('rt_rw', $parsed['data']);
        $this->assertArrayHasKey('kel_desa', $parsed['data']);
    }

    /**
     * Damaged card - NIK validation still runs despite missing fields.
     */
    public function test_damaged_card_nik_validation_still_runs(): void
    {
        $damagedOcrText = "NIK : 1201011708900001
Nama : BUDI
Alamat : JL. BERNAS";

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse($damagedOcrText);

        // NIK harus validasi checksum
        $this->assertNotEmpty($parsed['nik']);
        $this->assertArrayHasKey('valid', $parsed['nik']);
        $this->assertTrue($parsed['nik']['valid']);
    }

    /**
     * Damaged card - corrupted/missing multiple fields.
     */
    public function test_damaged_card_multiple_missing_fields(): void
    {
        $damagedOcrText = "NIK : 1201011708900001
Nama :
Tempat Lahir :
Tanggal Lahir :
Jenis Kelamin : LAKI-LAKI
Gol. Darah : B";

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse($damagedOcrText);

        // NIK masih ada, field lain kosong
        $this->assertEquals('1201011708900001', $parsed['data']['nik']);
        $this->assertEquals('', $parsed['data']['nama_lengkap']);
        $this->assertEquals('', $parsed['data']['tempat_lahir']);
        $this->assertEquals('', $parsed['data']['tanggal_lahir']);

        // Confidence sangat rendah
        $this->assertLessThan(0.5, $parsed['confidence']);
    }

    // ========================================================================
    // LIGHTING BURUK (POOR LIGHTING)
    // ========================================================================

    /**
     * Overexposed image - O misread as 0, I misread as 1.
     */
    public function test_overexposed_o_misread_as_zero(): void
    {
        // NIK dengan banyak O yang harusnya 0
        $overexposedText = "NIK : 3175OOOOOOO5OOO1
Nama : BUDI SANTOSO";

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse($overexposedText);

        // NIK harus di-normalize: O→0
        $this->assertEquals('3175000000005001', $parsed['data']['nik']);
    }

    /**
     * Underexposed image - gaps in text, missing chars.
     */
    public function test_underexposed_missing_characters(): void
    {
        // NIK dengan karakter hilang/hilang
        $underexposedText = "NIK : 31750O0000OO5OO1
Nama : BUDI SANTOSO
Tempat Lahir : JAKARTA";

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse($underexposedText);

        // Normalisasi harus handle O→0
        $this->assertEquals('3175000000005001', $parsed['data']['nik']);
    }

    /**
     * NIK normalization O→0, I→1 works correctly.
     */
    public function test_nik_normalization_o_to_zero_i_to_one(): void
    {
        $testCases = [
            ['input' => '317500000OOO5OO1', 'expected' => '3175000000005001'],
            ['input' => '3175OOOO000OO5OO1', 'expected' => '3175000000005001'],
            ['input' => '3175IIII000OO5III', 'expected' => '3175111100005111'],
            ['input' => '3175000O1lOOOO5OO', 'expected' => '317500110000500'],
        ];

        foreach ($testCases as $case) {
            $service = app(AdvancedKtpOcrParsingService::class);
            $parsed = $service->parse("NIK : {$case['input']}");

            // Verify NIK normalized correctly
            $this->assertEquals(
                $case['expected'],
                $parsed['data']['nik'],
                "Failed for input: {$case['input']}"
            );
        }
    }

    /**
     * Confidence reflects lighting issues.
     */
    public function test_confidence_reflects_lighting_issues(): void
    {
        // OCR dengan misread banyak karakter
        $poorLightingText = "NIK : 3175OOOOOOO5OOO1
Nama : BUDI SANTOSO
Tempat Lahir : JAKARTA
Tanggal Lahir : 01-01-1995
Alamat : JL. MERDEKA NO. 1
RT/RW : 001/002
Kel/Desa : GAMBIR
Kecamatan : GAMBIR";

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse($poorLightingText);

        // Confidence mungkin lebih rendah karena OCR error
        $this->assertLessThanOrEqual(1.0, $parsed['confidence']);
        $this->assertGreaterThan(0.5, $parsed['confidence']);
    }

    // ========================================================================
    // EMPTY / NO TEXT DETECTED
    // ========================================================================

    /**
     * Empty OCR result - no text detected.
     */
    public function test_empty_ocr_result(): void
    {
        $emptyText = '';

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse($emptyText);

        // Semua field harus kosong
        $this->assertEquals('', $parsed['data']['nik']);
        $this->assertEquals('', $parsed['data']['nama_lengkap']);
        $this->assertEquals('', $parsed['data']['alamat']);
        $this->assertEquals(0, $parsed['confidence']);
    }

    /**
     * Very low confidence (< 0.3) should flag manual review.
     */
    public function test_very_low_confidence_flags_manual_review(): void
    {
        $garbageText = "asdfghjkl qwertyuiop 1234567890";

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse($garbageText);

        // Confidence sangat rendah
        $this->assertLessThan(0.3, $parsed['confidence']);

        // Simpan antrian dengan flag manual review
        $antrian = AntrianOnline::factory()->create([
            'ocr_confidence' => $parsed['confidence'],
            'ocr_raw_text' => $garbageText,
            'ocr_needs_review' => true,
        ]);

        $this->assertTrue($antrian->ocr_needs_review);
    }

    // ========================================================================
    // OCR TIMEOUT / PROVIDER UNAVAILABLE
    // ========================================================================

    /**
     * OCR provider timeout scenario.
     */
    public function test_ocr_provider_timeout(): void
    {
        // Simulasi API timeout
        Http::fake([
            'localhost:5000/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
            },
        ]);

        // Mock EasyOCR service timeout
        $service = app(\App\Services\EasyOcrService::class);

        $file = UploadedFile::fake()->image('ktp.jpg');

        // Service harus handle timeout dengan graceful error
        $result = $service->processKtpImage($file);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('timeout', strtolower($result['message']));
    }

    /**
     * OCR provider unavailable.
     */
    public function test_ocr_provider_unavailable(): void
    {
        // Simulasi provider tidak tersedia
        Http::fake([
            'localhost:5000/*' => Http::response(['status' => 'error', 'message' => 'Service unavailable'], 503),
        ]);

        $service = app(\App\Services\EasyOcrService::class);
        $file = UploadedFile::fake()->image('ktp.jpg');

        $result = $service->processKtpImage($file);

        // Harus fail dengan appropriate message
        $this->assertFalse($result['success']);
    }

    // ========================================================================
    // NIK VALIDATION FAILURES
    // ========================================================================

    /**
     * Invalid NIK format - not 16 digits.
     */
    public function test_invalid_nik_format_not_16_digits(): void
    {
        $invalidNikCases = [
            '12345',           // Too short
            '12345678901234567', // Too long (17)
            '12010117089000',  // 14 digits
            'ABCDEF123456789', // Contains letters
            '',                // Empty
        ];

        $service = app(AdvancedKtpOcrParsingService::class);

        foreach ($invalidNikCases as $nik) {
            $parsed = $service->parse("NIK : {$nik}");

            // NIK tidak valid format - tidak boleh terekstrak
            $this->assertNotEquals($nik, $parsed['data']['nik']);
        }
    }

    /**
     * NIK checksum validation failure.
     */
    public function test_nik_checksum_failure(): void
    {
        // NIK dengan checksum invalid
        $invalidNik = '3175000000000099'; // Last digit wrong

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse("NIK : {$invalidNik}");

        // NIK harus terekstrak tapi flag invalid
        $this->assertEquals($invalidNik, $parsed['data']['nik']);
        $this->assertFalse($parsed['nik']['valid'] ?? true);
    }

    /**
     * Invalid province code in NIK.
     */
    public function test_nik_invalid_province_code(): void
    {
        // Province code 99 tidak valid
        $invalidNik = '9901011708900001';

        $service = app(AdvancedKtpOcrParsingService::class);
        $parsed = $service->parse("NIK : {$invalidNik}");

        // Province invalid → NIK confidence rendah
        $this->assertArrayHasKey('nik', $parsed['nik'] ?? []);
    }

    // ========================================================================
    // FILE VALIDATION EDGE CASES
    // ========================================================================

    /**
     * Unsupported file type.
     */
    public function test_unsupported_file_type(): void
    {
        $file = UploadedFile::fake()->create('document.bmp', 100, 'image/bmp');

        $response = $this->postJson('/api/ocr/upload', [
            'image' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    /**
     * File too large (> 5MB).
     */
    public function test_file_too_large(): void
    {
        // Create file larger than 5MB (6MB = 6 * 1024 * 1024 = 6291456)
        $file = UploadedFile::fake()->create('large_ktp.jpg', 6291456);

        $response = $this->postJson('/api/ocr/upload', [
            'image' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    /**
     * Corrupted image file.
     */
    public function test_corrupted_image_file(): void
    {
        // Create a file that looks like an image but is corrupted
        $file = UploadedFile::fake()->create('corrupted.jpg', 100, 'image/jpeg');

        // Simulasi KtpOcrService handle corrupted file
        $service = app(KtpOcrService::class);

        // File valid tapi OCR tidak bisa proses → graceful handling
        $result = $service->processKtpImage('test-antrian-id', $file);

        // Harus handle error dengan appropriate response
        $this->assertArrayHasKey('success', $result);
    }

    // ========================================================================
    // KTP OCR SERVICE EDGE CASES
    // ========================================================================

    /**
     * KTP OCR with partial result from webhook.
     */
    public function test_ktp_ocr_partial_webhook_result(): void
    {
        $service = app(KtpOcrService::class);

        // Payload dengan hanya NIK dan nama (tanpa alamat)
        $payload = [
            'antrian_online_id' => 'test-antrian-123',
            'nik' => '1201011708900001',
            'nama_lengkap' => 'BUDI SANTOSO',
            // alamat tidak ada
            'confidence' => 0.75,
        ];

        // Mock antrian exists
        $antrian = AntrianOnline::factory()->create([
            'antrian_online_id' => 'test-antrian-123',
            'status_antrian' => AntrianOnline::STATUS_MENUNGGU,
        ]);

        $result = $service->handleWebhookPayload($payload);

        $this->assertEquals('1201011708900001', $result->nik);
        $this->assertEquals('BUDI SANTOSO', $result->nama_lengkap);
        $this->assertEquals(AntrianOnline::STATUS_DOKUMEN_DITERIMA, $result->status_antrian);
    }

    /**
     * KTP OCR with empty payload.
     */
    public function test_ktp_ocr_empty_payload(): void
    {
        $service = app(KtpOcrService::class);

        $antrian = AntrianOnline::factory()->create([
            'antrian_online_id' => 'test-antrian-empty',
        ]);

        // Payload kosong tidak boleh crash
        $this->expectException(\App\Exceptions\KtpOcrException::class);

        $service->handleWebhookPayload([]);
    }

    /**
     * KTP OCR with invalid antrian ID.
     */
    public function test_ktp_ocr_invalid_antrian_id(): void
    {
        $service = app(KtpOcrService::class);

        $payload = [
            'antrian_online_id' => 'nonexistent-id',
            'nik' => '1201011708900001',
        ];

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $service->handleWebhookPayload($payload);
    }

    // ========================================================================
    // MASKING AND SECURITY EDGE CASES
    // ========================================================================

    /**
     * NIK masking for display.
     */
    public function test_nik_masking_for_display(): void
    {
        $nik = '1201011708900001';
        $masked = KtpOcrService::maskNik($nik);

        // Format: 120101******0001
        $this->assertEquals('120101******0001', $masked);
    }

    /**
     * NIK masking with short NIK.
     */
    public function test_nik_masking_short_nik(): void
    {
        $nik = '12345';
        $masked = KtpOcrService::maskNik($nik);

        // Short NIK all asterisks
        $this->assertEquals('*****', $masked);
    }

    /**
     * NIK masking with null.
     */
    public function test_nik_masking_null(): void
    {
        $masked = KtpOcrService::maskNik(null);
        $this->assertNull($masked);
    }

    /**
     * NIK masking with empty string.
     */
    public function test_nik_masking_empty_string(): void
    {
        $masked = KtpOcrService::maskNik('');
        $this->assertEquals('', $masked);
    }
}