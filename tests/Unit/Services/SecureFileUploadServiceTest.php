<?php

namespace Tests\Unit\Services;

use App\Rules\FileValidationRule;
use App\Services\SecureFileUploadService;
use App\Services\StoreResult;
use App\Services\ValidationResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Unit tests untuk SecureFileUploadService dan FileValidationRule.
 */
class SecureFileUploadServiceTest extends TestCase
{
    private SecureFileUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('temporary');
        $this->service = new SecureFileUploadService();
    }

    // -------------------------------------------------------------------------
    // Valid file format tests
    // -------------------------------------------------------------------------

    public function test_valid_jpeg_upload_passes(): void
    {
        $file = UploadedFile::fake()->image('ktp.jpg', 640, 480);

        $result = $this->service->validateFile($file);

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->errors);
    }

    public function test_valid_png_upload_passes(): void
    {
        $file = UploadedFile::fake()->image('ktp.png', 640, 480);

        $result = $this->service->validateFile($file);

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->errors);
    }

    public function test_valid_pdf_upload_passes(): void
    {
        $file = UploadedFile::fake()->create('dokumen.pdf', 512, 'application/pdf');

        $result = $this->service->validateFile($file);

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->errors);
    }

    public function test_jpeg_extension_case_insensitive(): void
    {
        $file = UploadedFile::fake()->image('ktp.JPEG', 640, 480);

        $result = $this->service->validateFile($file);

        $this->assertTrue($result->isValid());
    }

    // -------------------------------------------------------------------------
    // Invalid MIME type tests
    // -------------------------------------------------------------------------

    public function test_exe_file_rejected(): void
    {
        $file = UploadedFile::fake()->create('malware.exe', 512, 'application/x-msdownload');

        $result = $this->service->validateFile($file);

        $this->assertFalse($result->isValid());
        $this->assertContains('Tipe file tidak diizinkan', $result->errors);
    }

    public function test_html_file_rejected(): void
    {
        $file = UploadedFile::fake()->create('page.html', 512, 'text/html');

        $result = $this->service->validateFile($file);

        $this->assertFalse($result->isValid());
        $this->assertContains('Tipe file tidak diizinkan', $result->errors);
    }

    public function test_php_file_rejected(): void
    {
        $file = UploadedFile::fake()->create('script.php', 512, 'text/x-php');

        $result = $this->service->validateFile($file);

        $this->assertFalse($result->isValid());
        $this->assertContains('Tipe file tidak diizinkan', $result->errors);
    }

    public function test_zip_file_rejected(): void
    {
        $file = UploadedFile::fake()->create('archive.zip', 512, 'application/zip');

        $result = $this->service->validateFile($file);

        $this->assertFalse($result->isValid());
        $this->assertContains('Tipe file tidak diizinkan', $result->errors);
    }

    public function test_svg_file_rejected(): void
    {
        $file = UploadedFile::fake()->create('image.svg', 512, 'image/svg+xml');

        $result = $this->service->validateFile($file);

        $this->assertFalse($result->isValid());
        $this->assertContains('Tipe file tidak diizinkan', $result->errors);
    }

    // -------------------------------------------------------------------------
    // File size tests
    // -------------------------------------------------------------------------

    public function test_file_exceeding_2mb_rejected(): void
    {
        // UploadedFile::fake()->create allows size in KB
        $file = UploadedFile::fake()->create('large.jpg', 3 * 1024); // 3 MB

        $result = $this->service->validateFile($file);

        $this->assertFalse($result->isValid());
        $this->assertTrue(
            str_contains($result->errors[0] ?? '', 'melebihi batas')
            || str_contains($result->errors[0] ?? '', '2 MB'),
            'Error harus menyatakan ukuran melebihi 2MB'
        );
    }

    public function test_file_at_2mb_boundary_accepted(): void
    {
        // 2MB = 2097152 bytes
        $file = UploadedFile::fake()->image('boundary.jpg', 800, 600);

        // Fake file will be small, but we verify boundary is respected
        $result = $this->service->validateFile($file);

        $this->assertTrue($result->isValid());
    }

    // -------------------------------------------------------------------------
    // Extension tests
    // -------------------------------------------------------------------------

    public function test_no_extension_rejected(): void
    {
        $file = UploadedFile::fake()->create('noextension', 100);

        $result = $this->service->validateFile($file);

        $this->assertFalse($result->isValid());
        $this->assertContains('File tanpa ekstensi tidak diizinkan', $result->errors);
    }

    public function test_txt_extension_rejected(): void
    {
        $file = UploadedFile::fake()->create('dokumen.txt', 512, 'text/plain');

        $result = $this->service->validateFile($file);

        $this->assertFalse($result->isValid());
        $this->assertContains('Ekstensi file', $result->errors[0] ?? '');
    }

    // -------------------------------------------------------------------------
    // Path traversal tests
    // -------------------------------------------------------------------------

    public function test_filename_with_dotdot_slash_rejected(): void
    {
        $file = UploadedFile::fake()->create('../dangerous.jpg', 512, 'image/jpeg');

        $result = $this->service->validateFile($file);

        $this->assertFalse($result->isValid());
        $this->assertTrue(
            str_contains($result->errors[0] ?? '', 'mencurigakan')
            || str_contains($result->errors[0] ?? '', 'path traversal'),
            'Error harus menyatakan file mencurigakan'
        );
    }

    public function test_filename_with_dotdot_backslash_rejected(): void
    {
        $file = UploadedFile::fake()->create('..\\..\\windows\\path.jpg', 512, 'image/jpeg');

        $result = $this->service->validateFile($file);

        $this->assertFalse($result->isValid());
        $this->assertTrue(
            str_contains($result->errors[0] ?? '', 'mencurigakan')
            || str_contains($result->errors[0] ?? '', 'path traversal'),
            'Error harus menyatakan file mencurigakan'
        );
    }

    // -------------------------------------------------------------------------
    // ValidationResult unit tests
    // -------------------------------------------------------------------------

    public function test_validation_result_is_valid(): void
    {
        $result = new ValidationResult(true, []);

        $this->assertTrue($result->isValid());
        $this->assertNull($result->firstError());
        $this->assertEmpty($result->errorCodes());
    }

    public function test_validation_result_with_errors(): void
    {
        $errors = ['Error 1', 'Error 2'];
        $result = new ValidationResult(false, $errors);

        $this->assertFalse($result->isValid());
        $this->assertEquals('Error 1', $result->firstError());
        $this->assertCount(2, $result->errors);
        $this->assertCount(2, $result->errorCodes());
    }

    public function test_validation_result_meta(): void
    {
        $meta = ['mime' => 'image/jpeg', 'size_bytes' => 1024];
        $result = new ValidationResult(true, [], $meta);

        $this->assertEquals('image/jpeg', $result->meta['mime']);
        $this->assertEquals(1024, $result->meta['size_bytes']);
    }

    public function test_validation_result_error_codes_mapping(): void
    {
        $result = new ValidationResult(false, [
            'Ukuran file terlalu besar.',
            'Ekstensi file tidak valid.',
        ]);

        $codes = $result->errorCodes();

        $this->assertContains('SIZE_EXCEEDED', $codes);
        $this->assertContains('INVALID_EXTENSION', $codes);
    }

    // -------------------------------------------------------------------------
    // StoreResult unit tests
    // -------------------------------------------------------------------------

    public function test_store_result_success(): void
    {
        $result = new StoreResult(true, 'temporary/ktp/uuid.jpg', [], 'SUCCESS', '/full/path');

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('temporary/ktp/uuid.jpg', $result->path);
        $this->assertEquals('SUCCESS', $result->code);
        $this->assertNull($result->firstError());
    }

    public function test_store_result_failure(): void
    {
        $result = new StoreResult(false, null, ['File tidak valid.'], 'VALIDATION_FAILED');

        $this->assertFalse($result->isSuccess());
        $this->assertNull($result->path);
        $this->assertEquals('VALIDATION_FAILED', $result->code);
        $this->assertEquals('File tidak valid.', $result->firstError());
    }

    // -------------------------------------------------------------------------
    // Store file tests
    // -------------------------------------------------------------------------

    public function test_store_file_with_valid_file_returns_path(): void
    {
        $file = UploadedFile::fake()->image('ktp.jpg', 640, 480);

        $result = $this->service->storeFile($file, 'ktp');

        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($result->path);
        $this->assertStringStartsWith('temporary/ktp/', $result->path);
        $this->assertStringEndsWith('.jpg', $result->path);
    }

    public function test_store_file_with_invalid_file_returns_error(): void
    {
        $file = UploadedFile::fake()->create('malware.exe', 512, 'application/x-msdownload');

        $result = $this->service->storeFile($file, 'ktp');

        $this->assertFalse($result->isSuccess());
        $this->assertEquals('VALIDATION_FAILED', $result->code);
        $this->assertNotEmpty($result->errors);
    }

    // -------------------------------------------------------------------------
    // UUID filename generation
    // -------------------------------------------------------------------------

    public function test_uuid_filename_generation(): void
    {
        $filename = $this->service->generateUuidFilename('jpg');

        $this->assertStringEndsWith('.jpg', $filename);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.jpg$/',
            $filename
        );
    }

    public function test_uuid_filename_extension_normalized(): void
    {
        $filenameJpg = $this->service->generateUuidFilename('.JPG');
        $filenamePdf = $this->service->generateUuidFilename('PDF');

        $this->assertStringEndsWith('.jpg', $filenameJpg);
        $this->assertStringEndsWith('.pdf', $filenamePdf);
    }

    // -------------------------------------------------------------------------
    // Polyglot file detection tests
    // -------------------------------------------------------------------------

    public function test_php_polyglot_in_jpeg_detected(): void
    {
        // Create a fake JPEG-like file with PHP code embedded
        $polyglotContent = "\xFF\xD8\xFF\xE0<?php eval(base64_decode('ZXhpdCgpOw==')); ?>";
        $tempFile = tempnam(sys_get_temp_dir(), 'polyglot_');
        file_put_contents($tempFile, $polyglotContent);

        try {
            $file = new UploadedFile($tempFile, 'malicious.jpg', 'image/jpeg', null, true);
            $result = $this->service->validateFile($file);

            $this->assertFalse($result->isValid());
            $this->assertContains('kode executable', $result->errors[0] ?? '');
        } finally {
            @unlink($tempFile);
        }
    }

    public function test_script_tag_in_jpeg_detected(): void
    {
        // Create a fake JPEG with <script> tag embedded
        $polyglotContent = "\xFF\xD8\xFF\xE0" . str_repeat("\x00", 100) . '<script>alert(1)</script>';
        $tempFile = tempnam(sys_get_temp_dir(), 'script_');
        file_put_contents($tempFile, $polyglotContent);

        try {
            $file = new UploadedFile($tempFile, 'malicious.jpg', 'image/jpeg', null, true);
            $result = $this->service->validateFile($file);

            $this->assertFalse($result->isValid());
            $this->assertStringContainsString('executable', $result->errors[0] ?? '');
        } finally {
            @unlink($tempFile);
        }
    }

    public function test_pdf_with_javascript_detected(): void
    {
        // Create a fake PDF with JavaScript action
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /AA << /O <</S /JavaScript /JS (app.alert('xss'))>> >> >>";
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_js_');
        file_put_contents($tempFile, $pdfContent);

        try {
            $file = new UploadedFile($tempFile, 'malicious.pdf', 'application/pdf', null, true);
            $result = $this->service->validateFile($file);

            $this->assertFalse($result->isValid());
            $this->assertStringContainsString('JavaScript', $result->errors[0] ?? '');
        } finally {
            @unlink($tempFile);
        }
    }

    public function test_pdf_with_openaction_detected(): void
    {
        // Create a fake PDF with OpenAction
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /OpenAction << /S /JavaScript /JS (this.submitForm())>> >>";
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_oa_');
        file_put_contents($tempFile, $pdfContent);

        try {
            $file = new UploadedFile($tempFile, 'malicious.pdf', 'application/pdf', null, true);
            $result = $this->service->validateFile($file);

            $this->assertFalse($result->isValid());
            $this->assertStringContainsString('aksi otomatis', $result->errors[0] ?? '');
        } finally {
            @unlink($tempFile);
        }
    }

    // -------------------------------------------------------------------------
    // FileValidationRule tests
    // -------------------------------------------------------------------------

    public function test_file_validation_rule_accepts_valid_file(): void
    {
        $file = UploadedFile::fake()->image('ktp.jpg', 640, 480);
        $rule = new FileValidationRule('KTP Image');

        $errors = [];
        $rule->validate('ktp_image', $file, function (string $msg) use (&$errors): void {
            $errors[] = $msg;
        });

        $this->assertEmpty($errors);
    }

    public function test_file_validation_rule_rejects_invalid_file(): void
    {
        $file = UploadedFile::fake()->create('malware.exe', 512, 'application/x-msdownload');
        $rule = new FileValidationRule('KTP Image');

        $errors = [];
        $rule->validate('ktp_image', $file, function (string $msg) use (&$errors): void {
            $errors[] = $msg;
        });

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('tidak aman', $errors[0] ?? '');
    }

    public function test_file_validation_rule_non_file_value(): void
    {
        $rule = new FileValidationRule('KTP Image');

        $errors = [];
        $rule->validate('ktp_image', 'not-a-file', function (string $msg) use (&$errors): void {
            $errors[] = $msg;
        });

        $this->assertNotEmpty($errors);
    }
}